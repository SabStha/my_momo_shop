<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\QRCodeService;
use League\Csv\Writer;
use Carbon\Carbon;
use App\Models\Branch;
use App\Services\BranchContext;

class WalletController extends Controller
{
    protected $qrCodeService;
    protected $adminPassword = '333122';

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function index()
    {
        try {
            if (!session('wallet_authenticated')) {
                return redirect()->route('admin.wallet.topup.login')
                               ->with('error', 'Please authenticate to access wallet features.');
            }

            $branchId = session('current_branch_id');
            if (!$branchId) {
                return redirect()->route('admin.dashboard')
                               ->with('error', 'No branch selected. Please select a branch first.');
            }

            $currentBranch = Branch::findOrFail($branchId);
            
            // Get users with wallets for the current branch
            $users = User::with(['wallet' => function($query) use ($currentBranch) {
                $query->where('branch_id', $currentBranch->id);
            }])->get();
            
            // Calculate statistics for the current branch
            $totalBalance = $users->sum(function($user) {
                return $user->wallet->balance ?? 0;
            });
            
            $totalUsers = $users->count();
            
            $todayTransactions = WalletTransaction::where('branch_id', $currentBranch->id)
                ->whereDate('created_at', Carbon::today())
                ->count();
            
            return view('admin.wallet.index', compact('users', 'totalBalance', 'totalUsers', 'todayTransactions', 'currentBranch'));
        } catch (\Exception $e) {
            Log::error('Wallet index error: ' . $e->getMessage());
            return redirect()->route('admin.wallet.topup.login')
                           ->with('error', 'Please authenticate to access wallet features.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            $currentBranch = session('current_branch');
            $currentUser = auth()->user();

            // Create wallet if it doesn't exist
            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $request->user_id,
                    'branch_id' => $currentBranch->id
                ],
                ['balance' => 0, 'is_active' => true]
            );

            // If initial amount is provided, create a credit transaction
            if ($request->amount > 0) {
                $wallet->transactions()->create([
                    'type' => 'credit',
                    'amount' => $request->amount,
                    'description' => $request->description ?? 'Initial wallet creation',
                    'branch_id' => $currentBranch->id,
                    'performed_by' => $currentUser->id,
                    'performed_by_branch_id' => $currentBranch->id,
                    'status' => 'completed',
                    'reference_number' => 'INIT-' . uniqid()
                ]);

                $wallet->increment('balance', $request->amount);
            }

            DB::commit();
            return redirect()->route('admin.wallet.index')
                           ->with('success', 'Wallet created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create wallet. Please try again.');
        }
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            $currentBranch = session('current_branch');
            $currentUser = auth()->user();

            // Create wallet if it doesn't exist
            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $request->user_id,
                    'branch_id' => $currentBranch->id
                ],
                ['balance' => 0, 'is_active' => true]
            );

            $wallet->transactions()->create([
                'wallet_id' => $wallet->id,
                'user_id' => $request->user_id,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Wallet top-up',
                'branch_id' => $currentBranch->id,
                'performed_by' => $currentUser->id,
                'performed_by_branch_id' => $currentBranch->id,
                'status' => 'completed',
                'reference_number' => 'TOPUP-' . uniqid()
            ]);

            $wallet->increment('balance', $request->amount);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Wallet topped up successfully.',
                'new_balance' => $wallet->balance
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet top-up failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to top up wallet. Please try again.'
            ], 500);
        }
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            $currentBranch = session('current_branch');
            $currentUser = auth()->user();

            $wallet = Wallet::where('user_id', $request->user_id)
                          ->where('branch_id', $currentBranch->id)
                          ->firstOrFail();

            if ($wallet->balance < $request->amount) {
                throw new \Exception('Insufficient wallet balance.');
            }

            $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Wallet withdrawal',
                'branch_id' => $currentBranch->id,
                'performed_by' => $currentUser->id,
                'performed_by_branch_id' => $currentBranch->id,
                'status' => 'completed',
                'reference_number' => 'WITHDRAW-' . uniqid()
            ]);

            $wallet->decrement('balance', $request->amount);

            DB::commit();
            return redirect()->route('admin.wallet.index')
                           ->with('success', 'Amount withdrawn successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet withdrawal failed: ' . $e->getMessage());
            return back()->with('error', $e->getMessage() ?? 'Failed to withdraw amount. Please try again.');
        }
    }

    public function manage()
    {
        $currentBranch = session('current_branch');
        
        $wallets = Wallet::with(['user', 'transactions' => function($query) use ($currentBranch) {
            $query->where('branch_id', $currentBranch->id)->latest();
        }])
        ->where('branch_id', $currentBranch->id)
        ->get();

        $totalBalance = $wallets->sum('balance');
        $totalTransactions = $wallets->sum(function($wallet) {
            return $wallet->transactions->count();
        });

        return view('admin.wallet.manage', compact('wallets', 'totalBalance', 'totalTransactions'));
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('term', '');
            $currentBranch = session('current_branch');
            
            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'users' => []
                ]);
            }

            $users = User::with(['wallet' => function($q) use ($currentBranch) {
                $q->where('branch_id', $currentBranch->id);
            }])
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'wallet' => [
                        'balance' => $user->wallet ? $user->wallet->balance : 0
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            \Log::error('User search failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error searching users: ' . $e->getMessage()
            ], 500);
        }
    }

    public function qrGenerator()
    {
        if (!session()->has('wallet_authenticated')) {
            return redirect()->route('admin.wallet.topup.login');
        }

        return view('admin.wallet.qr-generator', [
            'currentBranch' => BranchContext::getCurrentBranch()
        ]);
    }

    public function generateQr(Request $request)
    {
        if (!session()->has('wallet_authenticated')) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'expires_at' => 'required|integer|in:5,15,30,60'
        ]);

        $amount = $request->amount;
        $expiresIn = $request->expires_at;
        $expiresAt = Carbon::now()->addMinutes($expiresIn);

        // Generate QR code data
        $qrData = json_encode([
            'amount' => $amount,
            'branch_id' => BranchContext::getCurrentBranch()->id,
            'expires_at' => $expiresAt->timestamp
        ]);

        // Generate QR code
        $qrCode = QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrData);

        // Convert QR code to base64
        $qrCodeBase64 = base64_encode($qrCode);

        return response()->json([
            'success' => true,
            'qr_code' => 'data:image/png;base64,' . $qrCodeBase64,
            'amount' => number_format($amount, 2),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ]);
    }

    public function generateTopUpQR(Request $request)
    {
        try {
            if (!auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $request->validate([
                'amount' => 'required|numeric|min:1|max:10000',
                'password' => 'required'
            ]);

            if ($request->password !== $this->adminPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid admin password'
                ], 401);
            }

            $qrData = [
                'type' => 'wallet_topup',
                'amount' => $request->amount,
                'timestamp' => time(),
                'expires_at' => time() + (15 * 60) // 15 minutes from now
            ];

            $qrCode = $this->qrCodeService->generateQRCode(json_encode($qrData), 'wallet');
            
            return response()->json([
                'success' => true,
                'qr_code' => $qrCode
            ]);
        } catch (\Exception $e) {
            \Log::error('Wallet QR Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate wallet QR code'
            ], 500);
        }
    }

    public function transactions()
    {
        $transactions = WalletTransaction::with(['wallet.user'])
            ->latest()
            ->paginate(20);

        return view('admin.wallet.transactions', compact('transactions'));
    }

    public function adminTopup(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $request->user_id],
                ['balance' => 0]
            );

            $wallet->transactions()->create([
                'user_id' => $request->user_id,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => $request->notes ?? 'Admin top-up'
            ]);

            $wallet->increment('balance', $request->amount);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Wallet topped up successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet top-up failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to top up wallet'
            ], 500);
        }
    }

    public function balance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $wallet = Wallet::where('user_id', $request->user_id)->first();
        
        return response()->json([
            'success' => true,
            'balance' => $wallet ? $wallet->balance : 0
        ]);
    }

    public function export()
    {
        $transactions = WalletTransaction::with(['wallet.user'])
            ->latest()
            ->get();

        $csv = Writer::createFromString('');
        $csv->insertOne(['Date', 'User', 'Type', 'Amount', 'Description']);

        foreach ($transactions as $transaction) {
            $csv->insertOne([
                $transaction->created_at->format('Y-m-d H:i:s'),
                $transaction->wallet->user->name,
                $transaction->type,
                $transaction->amount,
                $transaction->description
            ]);
        }

        return response($csv->getContent(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="wallet-transactions.csv"'
        ]);
    }

    public function scan()
    {
        return view('admin.wallet.scan');
    }

    public function getTransactions(User $user)
    {
        $transactions = $user->wallet->transactions()
            ->with(['performedBy', 'performedByBranch'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'reference_number' => $transaction->reference_number,
                    'created_at' => $transaction->created_at,
                    'performed_by_name' => $transaction->performedBy ? $transaction->performedBy->name : 'System',
                    'performed_by_branch_name' => $transaction->performedByBranch ? $transaction->performedByBranch->name : 'System'
                ];
            });

        return response()->json([
            'transactions' => $transactions
        ]);
    }

    public function topupLogin()
    {
        if (session('wallet_authenticated')) {
            return redirect()->route('admin.wallet.index');
        }
        return view('admin.wallet.topup-login');
    }

    public function processTopupLogin(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        if ($request->password === $this->adminPassword) {
            session(['wallet_authenticated' => true]);
            return redirect()->route('admin.wallet.index')
                           ->with('success', 'Successfully authenticated for wallet operations.');
        }

        return back()->with('error', 'Invalid password. Please try again.');
    }

    public function topupVerify()
    {
        if (!session('wallet_authenticated')) {
            return redirect()->route('admin.wallet.topup.login')
                           ->with('error', 'Please authenticate to access wallet features.');
        }

        return view('admin.wallet.topup-verify');
    }

    public function topupLogout()
    {
        session()->forget('wallet_authenticated');
        return redirect()->route('admin.wallet.topup.login');
    }
}

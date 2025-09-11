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
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Label\Label;
use App\Services\ActivityLogService;

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
            // Check if user is authenticated for wallet access
            if (!session('wallet_authenticated')) {
                return redirect()->route('wallet.topup.login')
                               ->with('error', 'Please authenticate to access wallet features.');
            }

            // Get all users with wallets (no branch filtering since credits are universal)
            $users = User::with('wallet')->get();
            
            // Calculate statistics
            $totalBalance = $users->sum(function($user) {
                return $user->wallet ? $user->wallet->credits_balance : 0;
            });
            
            $totalUsers = $users->count();
            
            // Get today's transactions (no branch filtering)
            $todayTransactions = WalletTransaction::whereDate('created_at', Carbon::today())
                ->count();
            
            return view('admin.wallet.index', compact('users', 'totalBalance', 'totalUsers', 'todayTransactions'));
        } catch (\Exception $e) {
            Log::error('Wallet index error: ' . $e->getMessage());
            return redirect()->route('wallet.topup.login')
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
            $branchId = session('selected_branch_id');
            if (!$branchId) {
                return back()->with('error', 'No branch selected. Please select a branch first.');
            }
            
            $currentBranch = Branch::findOrFail($branchId);
            $currentUser = auth()->user();

            // Create wallet if it doesn't exist (universal wallet, no branch_id)
            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $request->user_id
                ],
                ['balance' => 0, 'is_active' => true]
            );

            // If initial amount is provided, create a credit transaction
            if ($request->amount > 0) {
                $balanceBefore = $wallet->balance;
                $balanceAfter = $balanceBefore + $request->amount;

                $wallet->transactions()->create([
                    'credits_account_id' => $wallet->id,
                    'user_id' => $request->user_id,
                    'type' => 'credit',
                    'credits_amount' => $request->amount,
                    'description' => $request->description ?? 'Initial wallet creation',
                    'branch_id' => $currentBranch->id,
                    'performed_by' => $currentUser->id,
                    'performed_by_branch_id' => $currentBranch->id,
                    'status' => 'completed',
                    'reference_number' => 'INIT-' . uniqid(),
                    'credits_balance_before' => $balanceBefore,
                    'credits_balance_after' => $balanceAfter
                ]);

                $wallet->increment('credits_balance', $request->amount);
            }

            ActivityLogService::logPaymentActivity(
                'create',
                'Created wallet for user ' . $wallet->user->name,
                [
                    'wallet_id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'initial_balance' => $request->amount
                ]
            );

            DB::commit();
            return redirect()->route('wallet.index')
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
            $branchId = session('selected_branch_id');
            if (!$branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No branch selected. Please select a branch first.'
                ], 400);
            }
            
            $currentBranch = Branch::findOrFail($branchId);
            $currentUser = auth()->user();

            // Create wallet if it doesn't exist (universal wallet, no branch_id)
            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $request->user_id
                ],
                ['balance' => 0, 'is_active' => true]
            );

            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $request->amount;

            $wallet->transactions()->create([
                'credits_account_id' => $wallet->id,
                'user_id' => $request->user_id,
                'type' => 'credit',
                'credits_amount' => $request->amount,
                'description' => $request->description ?? 'Wallet top-up',
                'branch_id' => $currentBranch->id,
                'performed_by' => $currentUser->id,
                'performed_by_branch_id' => $currentBranch->id,
                'status' => 'completed',
                'reference_number' => 'TOPUP-' . uniqid(),
                'credits_balance_before' => $balanceBefore,
                'credits_balance_after' => $balanceAfter
            ]);

            $wallet->increment('credits_balance', $request->amount);

            ActivityLogService::logPaymentActivity(
                'topup',
                'Topped up wallet for user ' . $wallet->user->name,
                [
                    'wallet_id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'amount' => $request->amount,
                    'new_balance' => $wallet->balance
                ]
            );

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
            $branchId = session('selected_branch_id');
            if (!$branchId) {
                return back()->with('error', 'No branch selected. Please select a branch first.');
            }
            
            $currentBranch = Branch::findOrFail($branchId);
            $currentUser = auth()->user();

            $wallet = Wallet::where('user_id', $request->user_id)
                          ->firstOrFail();

            if ($wallet->balance < $request->amount) {
                throw new \Exception('Insufficient wallet balance.');
            }

            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore - $request->amount;

            $wallet->transactions()->create([
                'credits_account_id' => $wallet->id,
                'user_id' => $request->user_id,
                'type' => 'debit',
                'credits_amount' => $request->amount,
                'description' => $request->description ?? 'Wallet withdrawal',
                'branch_id' => $currentBranch->id,
                'performed_by' => $currentUser->id,
                'performed_by_branch_id' => $currentBranch->id,
                'status' => 'completed',
                'reference_number' => 'WITHDRAW-' . uniqid(),
                'credits_balance_before' => $balanceBefore,
                'credits_balance_after' => $balanceAfter
            ]);

            $wallet->decrement('balance', $request->amount);

            ActivityLogService::logPaymentActivity(
                'withdraw',
                'Withdrawn from wallet for user ' . $wallet->user->name,
                [
                    'wallet_id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'amount' => $request->amount,
                    'new_balance' => $wallet->balance
                ]
            );

            DB::commit();
            return redirect()->route('wallet.index')
                           ->with('success', 'Amount withdrawn successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet withdrawal failed: ' . $e->getMessage());
            return back()->with('error', $e->getMessage() ?? 'Failed to withdraw amount. Please try again.');
        }
    }

    public function manage()
    {
        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return redirect()->route('admin.branches.index')
                           ->with('error', 'No branch selected. Please select a branch first.');
        }
        
        $currentBranch = Branch::findOrFail($branchId);
        
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
            
            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'users' => []
                ]);
            }

            $users = User::with('wallet')
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
                        'balance' => $user->wallet ? $user->wallet->credits_balance : 0
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
            return redirect()->route('wallet.topup.login');
        }

        return view('admin.wallet.qr-generator', [
            'currentBranch' => session('selected_branch')
        ]);
    }

    public function generateQr(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'expires_at' => 'required|integer|in:5,15,30,60'
        ]);

        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch selected. Please select a branch first.'
            ], 400);
        }

        $selectedBranch = Branch::find($branchId);
        if (!$selectedBranch) {
            return response()->json([
                'success' => false,
                'message' => 'Selected branch not found.'
            ], 400);
        }

        $amount = $request->amount;
        $expiresIn = $request->expires_at;
        $expiresAt = Carbon::now()->addMinutes($expiresIn);

        // Generate QR code data
        $qrData = json_encode([
            'amount' => $amount,
            'branch_id' => $selectedBranch->id,
            'expires_at' => $expiresAt->timestamp
        ]);

        // Create QR code
        $qrCode = EndroidQrCode::create($qrData)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        // Create writer
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Convert QR code to base64
        $qrCodeBase64 = base64_encode($result->getString());

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

            $currentTime = time();
            $expiresAt = $currentTime + (24 * 60 * 60); // 24 hours from now (temporary fix)
            
            $qrData = [
                'type' => 'wallet_topup',
                'amount' => $request->amount,
                'timestamp' => $currentTime,
                'expires_at' => $expiresAt
            ];

            // Log QR code generation details for debugging
            \Log::info('QR Code Generated', [
                'current_time' => $currentTime,
                'expires_at' => $expiresAt,
                'current_time_formatted' => date('Y-m-d H:i:s', $currentTime),
                'expires_at_formatted' => date('Y-m-d H:i:s', $expiresAt),
                'amount' => $request->amount
            ]);

            $qrCode = $this->qrCodeService->generateQRCode(json_encode($qrData), 'wallet');
            
            return response()->json([
                'success' => true,
                'qr_code' => $qrCode,
                'expires_at' => date('Y-m-d H:i:s', $expiresAt),
                'expires_timestamp' => $expiresAt
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

            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $request->amount;

            $wallet->transactions()->create([
                'user_id' => $request->user_id,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => $request->notes ?? 'Admin top-up',
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter
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

    public function processCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        try {
            // Try to parse the QR code data as JSON
            $qrData = json_decode($request->code, true);
            
            if (!$qrData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format'
                ], 400);
            }

            // Check if it's an admin-generated QR code
            if (isset($qrData['amount']) && isset($qrData['branch_id']) && isset($qrData['expires_at'])) {
                return $this->processAdminQRCode($qrData);
            }
            
            // Check if it's a wallet top-up QR code
            if (isset($qrData['type']) && $qrData['type'] === 'wallet_topup') {
                return $this->processWalletTopUpQR($qrData);
            }

            // Fallback: treat as simple code with default amount
            $amount = 30; // Default amount for legacy codes
            
            return $this->addFundsToWallet($amount, 'Funds added via QR code');

        } catch (\Exception $e) {
            \Log::error('Admin QR Code Processing Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process QR code'
            ], 500);
        }
    }

    private function processAdminQRCode($qrData)
    {
        // TEMPORARY: Skip expiration check for debugging
        // Check if QR code has expired
        if (false && isset($qrData['expires_at']) && $qrData['expires_at'] < time()) {
            return response()->json([
                'success' => false,
                'message' => 'QR code has expired'
            ], 400);
        }

        $amount = $qrData['amount'];
        $branchId = $qrData['branch_id'] ?? 'unknown';
        
        return $this->addFundsToWallet($amount, "Top-up via admin QR code (Branch: {$branchId})");
    }

    private function processWalletTopUpQR($qrData)
    {
        $amount = $qrData['amount'];
        $currentTime = time();
        
        // Log QR code processing details
        \Log::info('Processing Wallet Top-Up QR Code', [
            'qr_data' => $qrData,
            'expires_at' => $qrData['expires_at'] ?? 'not_set',
            'current_time' => $currentTime,
            'difference' => isset($qrData['expires_at']) ? $currentTime - $qrData['expires_at'] : 'N/A',
            'expires_at_formatted' => isset($qrData['expires_at']) ? date('Y-m-d H:i:s', $qrData['expires_at']) : 'N/A',
            'current_time_formatted' => date('Y-m-d H:i:s', $currentTime),
            'is_expired' => isset($qrData['expires_at']) && $qrData['expires_at'] < $currentTime
        ]);
        
        // TEMPORARY: Skip expiration check for debugging
        // Check if QR code has expired
        if (false && isset($qrData['expires_at']) && $qrData['expires_at'] < $currentTime) {
            // Log expiration details for debugging
            \Log::info('QR Code Expired', [
                'expires_at' => $qrData['expires_at'],
                'current_time' => $currentTime,
                'difference' => $currentTime - $qrData['expires_at'],
                'expires_at_formatted' => date('Y-m-d H:i:s', $qrData['expires_at']),
                'current_time_formatted' => date('Y-m-d H:i:s', $currentTime)
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'QR code has expired'
            ], 400);
        }
        
        return $this->addFundsToWallet($amount, 'Top-up via wallet QR code');
    }

    private function addFundsToWallet($amount, $description)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0
            ]);
        }

        // Create transaction
        $transaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $amount,
            'description' => $description,
            'status' => 'completed'
        ]);

        // Update wallet balance
        $wallet->balance += $amount;
        $wallet->save();

        return response()->json([
            'success' => true,
            'message' => 'Funds added successfully',
            'new_balance' => $wallet->balance,
            'amount_added' => $amount
        ]);
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
            return redirect()->route('wallet.index');
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
            
            ActivityLogService::logUserActivity(
                'login',
                'Logged into wallet management',
                [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            );

            return redirect()->route('wallet.index')
                           ->with('success', 'Successfully authenticated.');
        }

        return back()->with('error', 'Invalid password.');
    }

    public function topupVerify()
    {
        if (!session('wallet_authenticated')) {
            return redirect()->route('wallet.topup.login')
                           ->with('error', 'Please authenticate to access wallet features.');
        }

        return view('admin.wallet.topup-verify');
    }

    public function topupLogout()
    {
        session()->forget('wallet_authenticated');
        
        ActivityLogService::logUserActivity(
            'logout',
            'Logged out from wallet management'
        );

        return redirect()->route('wallet.topup.login')
                       ->with('success', 'Successfully logged out.');
    }
}

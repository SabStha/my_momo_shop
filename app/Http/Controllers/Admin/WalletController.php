<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\QRCodeService;
use League\Csv\Writer;

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
        $users = User::with(['wallet', 'wallet.transactions' => function($query) {
            $query->latest();
        }])->get();

        return view('admin.wallet.index', compact('users'));
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

            // Create wallet if it doesn't exist
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $request->user_id],
                ['balance' => 0]
            );

            // If initial amount is provided, create a credit transaction
            if ($request->amount > 0) {
                $wallet->transactions()->create([
                    'type' => 'credit',
                    'amount' => $request->amount,
                    'description' => $request->description ?? 'Initial wallet creation'
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

            // Create wallet if it doesn't exist
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $request->user_id],
                ['balance' => 0]
            );

            $wallet->transactions()->create([
                'wallet_id' => $wallet->id,
                'user_id' => $request->user_id,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Wallet top-up'
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

            $wallet = Wallet::where('user_id', $request->user_id)->firstOrFail();

            if ($wallet->balance < $request->amount) {
                throw new \Exception('Insufficient wallet balance.');
            }

            $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Wallet withdrawal'
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
        return view('admin.wallet.manage');
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('query', '');
            
            if (empty($query)) {
                return response()->json(['users' => []]);
            }

            $users = User::with('wallet')
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->select('id', 'name', 'email') // Select only needed fields
                ->limit(10)
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
                'message' => 'Error searching users'
            ], 500);
        }
    }

    public function generateQr(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
        ]);

        $user = auth()->user(); // or manually inject user ID if unauthenticated flow

        $payload = json_encode([
            'user_id' => $user ? $user->id : null,
            'amount' => $request->amount,
            'timestamp' => now()->timestamp,
        ]);

        $qrImage = QrCode::format('png')
            ->size(300)
            ->generate($payload);

        $base64 = 'data:image/png;base64,' . base64_encode($qrImage);

        return response()->json([
            'success' => true,
            'qr_code' => $base64,
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
}

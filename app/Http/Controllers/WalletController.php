<?php

namespace App\Http\Controllers;

use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use League\Csv\Writer;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $qrCodeService;
    protected $adminPassword = '333122';

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->middleware(['auth', 'admin'])->only(['showTopUp', 'generateTopUpQR', 'generatePWAQR', 'generateProductQR', 'showQRGenerator']);
    }

    /**
     * Show the wallet dashboard
     */
    public function index()
    {
        $wallet = Auth::user()->wallet;
        $transactions = $wallet ? $wallet->transactions()->latest()->paginate(10) : collect();
        
        return view('admin.wallet.index', compact('wallet', 'transactions'));
    }

    /**
     * Show the wallet top-up page (admin only)
     */
    public function showTopUp()
    {
        return view('wallet.topup');
    }

    /**
     * Show the wallet scanner page
     */
    public function scan()
    {
        return view('admin.wallet.scan');
    }

    /**
     * Generate QR code for top-up (admin only)
     */
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

    /**
     * Process wallet top-up
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255'
        ]);

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
            'amount' => $request->amount,
            'description' => $request->description ?? 'Wallet top-up',
            'status' => 'completed'
        ]);

        // Update wallet balance
        $wallet->balance += $request->amount;
        $wallet->save();

        return response()->json([
            'success' => true,
            'message' => 'Wallet topped up successfully',
            'new_balance' => $wallet->balance
        ]);
    }

    /**
     * Show QR generator page
     */
    public function showQRGenerator()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access');
        }

        $products = Product::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();

        return view('wallet.qr-generator', compact('products', 'users'));
    }

    /**
     * Generate QR code for PWA installation
     */
    public function generatePWAQR()
    {
        try {
            if (!auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $pwaUrl = config('app.url');
            $qrCode = $this->qrCodeService->generateQRCode($pwaUrl, 'pwa');
            
            return response()->json([
                'success' => true,
                'qr_code' => $qrCode
            ]);
        } catch (\Exception $e) {
            \Log::error('PWA QR Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PWA QR code'
            ], 500);
        }
    }

    /**
     * Generate QR code for product information
     */
    public function generateProductQR(Request $request)
    {
        try {
            if (!auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $product = Product::findOrFail($request->product_id);
            $productUrl = route('products.show', $product->id);
            $qrCode = $this->qrCodeService->generateQRCode($productUrl, 'product');
            
            return response()->json([
                'success' => true,
                'qr_code' => $qrCode
            ]);
        } catch (\Exception $e) {
            \Log::error('Product QR Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate product QR code'
            ], 500);
        }
    }

    /**
     * Show order details for a user
     */
    public function showOrderDetails(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            $orders = Order::where('user_id', $request->user_id)
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            \Log::error('Order Details Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order details'
            ], 500);
        }
    }

    public function manage()
    {
        return view('admin.wallet.manage');
    }

    public function adminTopup(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255'
        ]);

        $user = User::findOrFail($request->user_id);
        
        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'type' => 'credit',
            'status' => 'completed',
            'notes' => $request->notes ?? 'Wallet top-up by admin',
            'created_by' => auth()->id()
        ]);

        // Update user's wallet balance
        $user->wallet_balance += $request->amount;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Wallet topped up successfully',
            'new_balance' => $user->wallet_balance
        ]);
    }

    public function transactions()
    {
        $wallet = Auth::user()->wallet;
        $transactions = $wallet ? $wallet->transactions()->latest()->paginate(20) : collect();
        
        return view('user.wallet.transactions', compact('transactions'));
    }

    public function balance()
    {
        $balance = User::sum('wallet_balance');
        return response()->json(['balance' => $balance]);
    }

    public function export()
    {
        $transactions = Transaction::with('user')
            ->latest()
            ->get();

        $csv = Writer::createFromString('');
        $csv->insertOne(['Date', 'User', 'Amount', 'Type', 'Status', 'Notes']);

        foreach ($transactions as $transaction) {
            $csv->insertOne([
                $transaction->created_at->format('Y-m-d H:i:s'),
                $transaction->user->name,
                $transaction->amount,
                $transaction->type,
                $transaction->status,
                $transaction->notes
            ]);
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv->getContent();
        }, 'wallet-transactions.csv');
    }

    public function processCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        // Process the code and add funds to wallet
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0
            ]);
        }

        // Add funds (example amount)
        $amount = 100;

        // Create transaction
        $transaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $amount,
            'description' => 'Funds added via code',
            'status' => 'completed'
        ]);

        // Update wallet balance
        $wallet->balance += $amount;
        $wallet->save();

        return response()->json([
            'success' => true,
            'message' => 'Funds added successfully',
            'new_balance' => $wallet->balance
        ]);
    }

    /**
     * Generate QR code for wallet top-up
     */
    public function generateQR(Request $request)
    {
        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            $qrCode = $wallet->generateQRCode();
            $base64 = base64_encode($qrCode);

            return response()->json([
                'success' => true,
                'qr_code' => $base64
            ]);
        } catch (\Exception $e) {
            \Log::error('QR Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code'
            ], 500);
        }
    }

    /**
     * Process a payment
     */
} 
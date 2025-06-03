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
        $user = auth()->user();
        $wallet = $user->wallet;
        $transactions = $wallet ? $wallet->transactions()->latest()->paginate(10) : collect();

        return view('desktop.wallet.index', compact('wallet', 'transactions'));
    }

    /**
     * Show the wallet top-up page (admin only)
     */
    public function showTopUp()
    {
        return view('desktop.wallet.topup');
    }

    /**
     * Show the wallet scanner page
     */
    public function showScanner()
    {
        return view('desktop.wallet.scan');
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
    public function processTopUp(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'qr_data' => 'required|string'
            ]);

            // Parse QR data
            $data = json_decode($request->qr_data, true);
            
            if (!$data) {
                \Log::error('Invalid QR data format: ' . $request->qr_data);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format'
                ], 400);
            }

            // Validate required fields
            if (!isset($data['type']) || $data['type'] !== 'wallet_topup') {
                \Log::error('Invalid QR code type: ' . ($data['type'] ?? 'missing'));
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code type'
                ], 400);
            }

            // Verify timestamp (15 minutes expiry)
            if (!isset($data['timestamp']) || time() - $data['timestamp'] > 900) {
                \Log::error('QR code expired or invalid timestamp: ' . ($data['timestamp'] ?? 'missing'));
                return response()->json([
                    'success' => false,
                    'message' => 'QR code has expired'
                ], 400);
            }

            // Verify amount
            if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] < 1 || $data['amount'] > 10000) {
                \Log::error('Invalid amount in QR code: ' . ($data['amount'] ?? 'missing'));
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid amount'
                ], 400);
            }

            // Process the top-up for the current user
            $user = auth()->user();
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
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $data['amount'],
                'description' => 'Wallet top-up via QR code',
                'status' => 'completed'
            ]);

            // Update wallet balance
            $wallet->increment('balance', $data['amount']);

            return response()->json([
                'success' => true,
                'message' => 'Wallet topped up successfully',
                'amount' => $data['amount'],
                'new_balance' => $wallet->balance
            ]);

        } catch (\Exception $e) {
            \Log::error('Wallet top-up error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process top-up: ' . $e->getMessage()
            ], 500);
        }
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

        return view('desktop.wallet.qr-generator', compact('products', 'users'));
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
} 
<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Label\Label;
use Illuminate\Support\Facades\URL;
use Endroid\QrCode\Writer\Result\ResultInterface;

class QRCodeService
{
    /**
     * Generate a QR code
     *
     * @param string $data
     * @param string $type
     * @return string
     */
    public function generateQRCode(string $data, string $type = 'default'): string
    {
        try {
            // Create QR code
            $qrCode = QrCode::create($data)
                ->setSize(300)
                ->setMargin(10)
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255))
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
                ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->setEncoding(new Encoding('UTF-8'));

            // Add label based on type
            $label = match($type) {
                'wallet' => 'Wallet Top-up QR Code',
                'pwa' => 'PWA Installation QR Code',
                'product' => 'Product Information QR Code',
                default => 'QR Code'
            };

            // Create label
            $label = Label::create($label)
                ->setTextColor(new Color(0, 0, 0));

            // Generate QR code with label
            $writer = new PngWriter();
            $result = $writer->write($qrCode, null, $label);

            // Get the QR code data
            $qrCodeData = $result->getString();
            
            if (empty($qrCodeData)) {
                throw new \Exception('Failed to generate QR code data');
            }

            // Return base64 encoded image
            return 'data:image/png;base64,' . base64_encode($qrCodeData);
        } catch (\Exception $e) {
            \Log::error('QR Code generation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Generate a QR code for wallet top-up
     *
     * @param float $amount
     * @param int $userId
     * @return string
     */
    public function generateTopUpQR(float $amount, int $userId): string
    {
        try {
            // Create QR code data
            $data = json_encode([
                'type' => 'wallet_topup',
                'amount' => $amount,
                'user_id' => $userId,
                'timestamp' => time()
            ]);

            return $this->generateQRCode($data, 'wallet');
        } catch (\Exception $e) {
            \Log::error('Wallet QR Generation Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Generate a QR code URL for wallet top-up
     *
     * @param float $amount
     * @param int $userId
     * @return string
     */
    public function generateTopUpQRUrl(float $amount, int $userId): string
    {
        return URL::temporarySignedRoute(
            'wallet.topup.qr',
            now()->addMinutes(15),
            ['amount' => $amount, 'user_id' => $userId]
        );
    }

    /**
     * Generate a generic QR code
     */
    public function generateQR($data, $label = '')
    {
        try {
            $qrCode = QrCode::create($data)
                ->setSize(300)
                ->setMargin(10)
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255))
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
                ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->setEncoding(new Encoding('UTF-8'));

            // Generate QR code
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            return $result->getString();
        } catch (\Exception $e) {
            \Log::error('QR Code generation error: ' . $e->getMessage());
            return null;
        }
    }
} 
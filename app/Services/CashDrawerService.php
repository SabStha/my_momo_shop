<?php

namespace App\Services;

use App\Models\CashDrawerLog;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

class CashDrawerService
{
    protected $printer;
    protected $connector;

    public function __construct()
    {
        // Do not initialize printer connection in constructor
    }

    public function openDrawer($userId, $branchId, $reason = 'cash_payment')
    {
        try {
            // Initialize printer connection only when needed
            $this->connector = new FilePrintConnector("/dev/usb/lp0"); // Adjust path based on your printer
            $this->printer = new Printer($this->connector);

            Log::info('Attempting to open cash drawer', [
                'user_id' => $userId,
                'branch_id' => $branchId,
                'reason' => $reason,
                'printer_path' => config('printer.path'),
                'printer_type' => config('printer.type')
            ]);

            // Pulse the drawer
            $this->printer->pulse();
            
            Log::info('Cash drawer pulse command sent successfully');
            
            // Log the drawer open event
            $log = CashDrawerLog::create([
                'user_id' => $userId,
                'branch_id' => $branchId,
                'action' => 'open',
                'reason' => $reason,
                'status' => 'success'
            ]);

            Log::info('Cash drawer log created', ['log_id' => $log->id]);

            return true;
        } catch (\Exception $e) {
            // Log the error with more details
            Log::error('Failed to open cash drawer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'branch_id' => $branchId,
                'reason' => $reason,
                'printer_path' => config('printer.path'),
                'printer_type' => config('printer.type')
            ]);
            
            // Log the failed attempt
            $log = CashDrawerLog::create([
                'user_id' => $userId,
                'branch_id' => $branchId,
                'action' => 'open',
                'reason' => $reason,
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            Log::info('Failed cash drawer log created', ['log_id' => $log->id]);

            throw $e;
        } finally {
            // Always close the printer connection
            if ($this->printer) {
                try {
                    $this->printer->close();
                    Log::info('Printer connection closed successfully');
                } catch (\Exception $e) {
                    Log::error('Error closing printer connection', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    public function __destruct()
    {
        // Ensure printer connection is closed
        if ($this->printer) {
            $this->printer->close();
        }
    }
} 
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\InventoryOrder;

class SupplierReceiptConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $receiptData;
    public $isFullReceipt;
    public $missingItems;

    /**
     * Create a new message instance.
     */
    public function __construct(InventoryOrder $order, array $receiptData)
    {
        $this->order = $order;
        $this->receiptData = $receiptData;
        
        // Determine if this is a full receipt
        $this->isFullReceipt = $this->checkIfFullReceipt();
        
        // Calculate missing items for partial receipts
        $this->missingItems = $this->calculateMissingItems();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isFullReceipt 
            ? "Full Order Received - {$this->order->order_number}"
            : "Partial Order Received - {$this->order->order_number}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.supplier.receipt-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Check if this is a full receipt (all items received as ordered)
     */
    private function checkIfFullReceipt(): bool
    {
        foreach ($this->order->items as $item) {
            $receivedQty = $this->receiptData['received_quantities'][$item->id] ?? 0;
            $orderedQty = $item->original_quantity ?? $item->quantity;
            
            if ($receivedQty < $orderedQty) {
                return false;
            }
        }
        return true;
    }

    /**
     * Calculate missing items for partial receipts
     */
    private function calculateMissingItems(): array
    {
        $missingItems = [];
        
        foreach ($this->order->items as $item) {
            $receivedQty = $this->receiptData['received_quantities'][$item->id] ?? 0;
            $orderedQty = $item->original_quantity ?? $item->quantity;
            $supplierConfirmedQty = $item->quantity;
            
            if ($receivedQty < $orderedQty) {
                $missingQty = $orderedQty - $receivedQty;
                $missingItems[] = [
                    'name' => $item->item->name,
                    'sku' => $item->item->sku,
                    'ordered_qty' => $orderedQty,
                    'supplier_confirmed_qty' => $supplierConfirmedQty,
                    'received_qty' => $receivedQty,
                    'missing_qty' => $missingQty,
                    'unit' => $item->item->unit,
                    'unit_price' => $item->unit_price,
                    'total_missing_value' => $missingQty * $item->unit_price
                ];
            }
        }
        
        return $missingItems;
    }
}

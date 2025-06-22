<?php

namespace App\Mail;

use App\Models\InventoryOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupplierOrderConfirmationToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $isFullConfirmation;
    public $partialItems;

    public function __construct(InventoryOrder $order)
    {
        $this->order = $order;
        $this->isFullConfirmation = $this->checkIfFullConfirmation();
        $this->partialItems = $this->getPartialItems();
    }

    public function build()
    {
        $subject = $this->isFullConfirmation
            ? 'Supplier Confirmed Full Order - ' . $this->order->order_number
            : 'Supplier Confirmed Partial Order - ' . $this->order->order_number;

        return $this->subject($subject)
            ->view('emails.admin.supplier-confirmation');
    }

    private function checkIfFullConfirmation()
    {
        foreach ($this->order->items as $item) {
            $orderedQty = $item->original_quantity ?? $item->quantity;
            if ($item->quantity < $orderedQty) {
                return false;
            }
        }
        return true;
    }

    private function getPartialItems()
    {
        $partial = [];
        foreach ($this->order->items as $item) {
            $orderedQty = $item->original_quantity ?? $item->quantity;
            if ($item->quantity < $orderedQty) {
                $partial[] = [
                    'name' => $item->item->name,
                    'sku' => $item->item->sku,
                    'ordered_qty' => $orderedQty,
                    'confirmed_qty' => $item->quantity,
                    'unit' => $item->item->unit,
                ];
            }
        }
        return $partial;
    }
} 
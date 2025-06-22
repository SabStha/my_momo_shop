<?php

namespace App\Mail;

use App\Models\InventoryOrder;
use App\Models\InventorySupplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupplierOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $supplier;
    public $confirmationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(InventoryOrder $order, InventorySupplier $supplier)
    {
        $this->order = $order;
        $this->supplier = $supplier;
        $this->confirmationUrl = route('supplier.order.confirm', [
            'order' => $order->id,
            'token' => $this->generateConfirmationToken($order)
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Inventory Order #{$this->order->order_number} - {$this->order->branch->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.supplier.order-notification',
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
     * Generate a secure confirmation token for the order
     */
    private function generateConfirmationToken(InventoryOrder $order): string
    {
        return hash('sha256', $order->id . $order->created_at . config('app.key'));
    }
}

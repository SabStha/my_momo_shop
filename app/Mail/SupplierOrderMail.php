<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\InventoryOrder;
use PDF;

class SupplierOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $type;
    public $additionalData;

    /**
     * Create a new message instance.
     */
    public function __construct(InventoryOrder $order, string $type, array $additionalData)
    {
        $this->order = $order;
        $this->type = $type;
        $this->additionalData = $additionalData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Supplier Order Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.supplier.order',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdf = PDF::loadView('pdf.supply_order', ['order' => $this->order]);
        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $pdf->output(), 'order_'.$this->order->order_number.'.pdf', [
                'mime' => 'application/pdf',
            ])
        ];
    }

    public function build()
    {
        $subject = match($this->type) {
            'received' => "Order #{$this->order->order_number} Received",
            default => "New Order #{$this->order->order_number}"
        };

        return $this->subject($subject)
                    ->view('emails.supplier-order')
                    ->with([
                        'order' => $this->order,
                        'type' => $this->type,
                        'additionalData' => $this->additionalData
                    ]);
    }
}

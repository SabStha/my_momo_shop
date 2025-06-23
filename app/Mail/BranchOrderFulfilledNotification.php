<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\InventoryOrder;
use App\Models\Branch;

class BranchOrderFulfilledNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $requestingBranch;

    public function __construct(InventoryOrder $order, Branch $requestingBranch)
    {
        $this->order = $order;
        $this->requestingBranch = $requestingBranch;
    }

    public function build()
    {
        return $this->subject('Your Supply Order Has Been Fulfilled - ' . $this->order->order_number)
            ->view('emails.branch.order-fulfilled')
            ->with([
                'order' => $this->order,
                'branch' => $this->requestingBranch
            ]);
    }
} 
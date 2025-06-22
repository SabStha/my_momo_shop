<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\InventoryOrder;
use App\Models\Branch;

class NewBranchOrderNotification extends Mailable
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
        return $this->subject('New Supply Order from ' . $this->requestingBranch->name)
            ->view('emails.main_branch.new-branch-order')
            ->with([
                'order' => $this->order,
                'branch' => $this->requestingBranch
            ]);
    }
} 
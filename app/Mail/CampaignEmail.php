<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $template;
    public $user;
    public $variables;

    public function __construct($template, User $user)
    {
        $this->template = $template;
        $this->user = $user;
        $this->variables = $this->prepareVariables();
    }

    public function build()
    {
        return $this->subject($this->replaceVariables($this->template))
                    ->view('emails.campaign')
                    ->with([
                        'content' => $this->replaceVariables($this->template),
                        'user' => $this->user
                    ]);
    }

    protected function prepareVariables()
    {
        $lastOrder = $this->user->orders()->latest()->first();
        
        return [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'last_order_date' => $lastOrder ? $lastOrder->created_at->format('M d, Y') : 'Never',
            'total_orders' => $this->user->orders()->count(),
            'total_spent' => number_format($this->user->orders()->sum('total'), 2)
        ];
    }

    protected function replaceVariables($content)
    {
        foreach ($this->variables as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }
        
        return $content;
    }
} 
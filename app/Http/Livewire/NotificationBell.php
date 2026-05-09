<?php
namespace App\Http\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    public $count = 0;
    public $notifications = [];

    protected $listeners = ['refreshNotifications' => 'loadNotifications'];

    public function mount() { $this->loadNotifications(); }

    public function loadNotifications()
    {
        if (Auth::check()) {
            $this->count = Auth::user()->unreadNotifications()->count();
            $this->notifications = Auth::user()->notifications()->latest()->take(5)->get()->toArray();
        }
    }

    public function markAllRead()
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
            $this->loadNotifications();
        }
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}

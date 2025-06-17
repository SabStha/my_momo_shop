<?php

namespace App\Http\Livewire\Admin;

use App\Models\Payment;
use App\Models\PaymentMethod;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentManager extends Component
{
    use WithPagination;

    public $filterMethod = '';
    public $filterDate = '';
    public $search = '';
    public $selectedPayment = null;

    protected $queryString = [
        'filterMethod' => ['except' => ''],
        'filterDate' => ['except' => ''],
        'search' => ['except' => '']
    ];

    public function mount()
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function viewPayment($paymentId)
    {
        $this->selectedPayment = Payment::with(['order', 'method'])->find($paymentId);
    }

    public function cancelPayment($paymentId)
    {
        $payment = Payment::find($paymentId);
        if ($payment && $payment->status === 'pending') {
            $payment->update(['status' => 'cancelled']);
            session()->flash('message', 'Payment cancelled successfully.');
        }
    }

    public function getTotalPaymentsProperty()
    {
        return Payment::count();
    }

    public function getTodayRevenueProperty()
    {
        return Payment::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getPendingPaymentsProperty()
    {
        return Payment::where('status', 'pending')->count();
    }

    public function getFailedPaymentsProperty()
    {
        return Payment::where('status', 'failed')->count();
    }

    public function getPaymentMethodsProperty()
    {
        return PaymentMethod::active()->ordered()->get();
    }

    public function render()
    {
        $query = Payment::with(['order', 'method'])
            ->when($this->filterMethod, function ($query) {
                return $query->whereHas('method', function ($q) {
                    $q->where('code', $this->filterMethod);
                });
            })
            ->when($this->filterDate, function ($query) {
                return $query->whereDate('created_at', $this->filterDate);
            })
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('id', 'like', '%' . $this->search . '%')
                        ->orWhereHas('order', function ($q) {
                            $q->where('order_number', 'like', '%' . $this->search . '%');
                        });
                });
            });

        $payments = $query->latest()->paginate(10);
        $paymentMethods = PaymentMethod::all();

        return view('admin.payments.index', [
            'payments' => $payments,
            'paymentMethods' => $paymentMethods,
            'totalPayments' => $this->totalPayments,
            'todayRevenue' => $this->todayRevenue,
            'pendingPayments' => $this->pendingPayments,
            'failedPayments' => $this->failedPayments
        ]);
    }
} 
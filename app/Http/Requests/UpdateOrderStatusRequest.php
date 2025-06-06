<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');
        return $this->user()?->can('update', $order) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'preparing', 'prepared', 'completed', 'cancelled']),
                function ($attribute, $value, $fail) {
                    $order = $this->route('order');
                    
                    // Business logic validation
                    if ($order->status === 'completed' && $value !== 'completed') {
                        $fail('Cannot change status of a completed order.');
                    }
                    
                    if ($order->status === 'cancelled' && $value !== 'cancelled') {
                        $fail('Cannot change status of a cancelled order.');
                    }
                    
                    // Status progression validation
                    $validTransitions = [
                        'pending' => ['preparing', 'cancelled'],
                        'preparing' => ['prepared', 'cancelled'],
                        'prepared' => ['completed', 'cancelled'],
                        'completed' => [],
                        'cancelled' => [],
                    ];
                    
                    if (!in_array($value, $validTransitions[$order->status] ?? [])) {
                        $fail("Invalid status transition from {$order->status} to {$value}.");
                    }
                }
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: pending, preparing, prepared, completed, cancelled.',
        ];
    }
}
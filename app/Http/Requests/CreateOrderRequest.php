<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'cashier', 'employee']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['dine-in', 'takeaway', 'online'])],
            'table_id' => [
                'nullable',
                'integer',
                'exists:tables,id',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') === 'dine-in' && !$value) {
                        $fail('Table ID is required for dine-in orders.');
                    }
                }
            ],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
                function ($attribute, $value, $fail) {
                    $product = \App\Models\Product::find($value);
                    if ($product && !$product->active) {
                        $fail('The selected product is not available.');
                    }
                }
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'guest_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'guest_email' => ['nullable', 'email:rfc,dns', 'max:255'],
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
            'type.in' => 'Order type must be dine-in, takeaway, or online.',
            'table_id.exists' => 'The selected table does not exist.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.max' => 'Maximum 50 items allowed per order.',
            'items.*.product_id.exists' => 'One or more selected products do not exist.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Maximum quantity per item is 99.',
            'guest_name.regex' => 'Guest name should only contain letters and spaces.',
            'guest_email.email' => 'Please provide a valid email address.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->all();
        
        \Log::warning('Order creation validation failed', [
            'user_id' => auth()->id(),
            'errors' => $errors,
            'input' => $this->safe()->except(['items']), // Don't log items for brevity
        ]);

        parent::failedValidation($validator);
    }
}
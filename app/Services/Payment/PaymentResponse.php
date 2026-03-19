<?php

namespace App\Services\Payment;

/**
 * Standard response object for all payment processors.
 */
class PaymentResponse
{
    public bool $success;
    public string $status;
    public ?string $redirectUrl;
    public ?string $message;
    public ?array $data;

    /**
     * @param bool $success
     * @param string $status
     * @param string|null $redirectUrl
     * @param string|null $message
     * @param array|null $data
     */
    public function __construct(
        bool $success,
        string $status,
        ?string $redirectUrl = null,
        ?string $message = null,
        ?array $data = []
    ) {
        $this->success = $success;
        $this->status = $status;
        $this->redirectUrl = $redirectUrl;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Factory method for success response
     */
    public static function success(string $status = 'completed', ?string $message = null, ?array $data = []): self
    {
        return new self(true, $status, null, $message, $data);
    }

    /**
     * Factory method for failure response
     */
    public static function failure(string $message, string $status = 'failed', ?array $data = []): self
    {
        return new self(false, $status, null, $message, $data);
    }

    /**
     * Factory method for redirect response
     */
    public static function redirect(string $url, string $status = 'pending', ?string $message = null, ?array $data = []): self
    {
        return new self(true, $status, $url, $message, $data);
    }

    /**
     * Convert the response to an array for backward compatibility
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'message' => $this->message,
            'data' => array_merge($this->data ?? [], [
                'payment_url' => $this->redirectUrl,
                'redirect_required' => !is_null($this->redirectUrl),
            ]),
        ];
    }
}

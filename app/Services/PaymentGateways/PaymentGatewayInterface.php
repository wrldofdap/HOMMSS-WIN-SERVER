<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Process payment through the gateway
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    public function processPayment(Request $request, Order $order): array;

    /**
     * Validate payment data
     *
     * @param Request $request
     * @return array
     */
    public function validatePaymentData(Request $request): array;

    /**
     * Get payment method name
     *
     * @return string
     */
    public function getPaymentMethodName(): string;

    /**
     * Check if gateway is available
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get client configuration for frontend
     *
     * @return array
     */
    public function getClientConfig(): array;

    /**
     * Handle webhook from payment provider
     *
     * @param Request $request
     * @return array
     */
    public function handleWebhook(Request $request): array;
}

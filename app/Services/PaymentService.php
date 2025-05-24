<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use App\Services\PaymentGateways\StripePaymentGateway;
use App\Services\PaymentGateways\PayMongoGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentService
{
    /**
     * Process payment based on method
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    public function processPayment(Request $request, Order $order): array
    {
        $paymentMethod = $request->input('payment_method');
        $gateway = $request->input('gateway', 'paymongo'); // Default to PayMongo for PH

        // Log payment attempt
        Log::info('Payment processing started', [
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
            'gateway' => $gateway,
            'amount' => $order->total,
            'user_id' => $order->user_id
        ]);

        // Route to appropriate gateway
        switch ($gateway) {
            case 'stripe':
                return $this->processStripePayment($request, $order);
            case 'paymongo':
                return $this->processPayMongoPayment($request, $order);
            case 'cod':
                return $this->processCodPayment($request, $order);
            default:
                // Fallback to simulation for unsupported gateways
                return $this->processLegacyPayment($request, $order);
        }
    }

    /**
     * Process payment through Stripe
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    private function processStripePayment(Request $request, Order $order): array
    {
        $gateway = new StripePaymentGateway();

        if (!$gateway->isAvailable()) {
            return [
                'success' => false,
                'message' => 'Stripe payment gateway is not configured'
            ];
        }

        $result = $gateway->processPayment($request, $order);

        if ($result['success']) {
            $this->updateTransactionStatus($order, 'approved', $result['transaction_id']);
        } else {
            $this->updateTransactionStatus($order, 'declined');
        }

        return $result;
    }

    /**
     * Process payment through PayMongo
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    private function processPayMongoPayment(Request $request, Order $order): array
    {
        $gateway = new PayMongoGateway();

        if (!$gateway->isAvailable()) {
            return [
                'success' => false,
                'message' => 'PayMongo payment gateway is not configured'
            ];
        }

        $result = $gateway->processPayment($request, $order);

        if ($result['success']) {
            $this->updateTransactionStatus($order, 'approved', $result['transaction_id'] ?? null);
        } else {
            $this->updateTransactionStatus($order, 'declined');
        }

        return $result;
    }

    /**
     * Process legacy payment (simulation for backward compatibility)
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    private function processLegacyPayment(Request $request, Order $order): array
    {
        // Validate card details
        $cardValidation = $this->validateCardDetails($request);
        if (!$cardValidation['valid']) {
            return [
                'success' => false,
                'message' => $cardValidation['message']
            ];
        }

        try {
            // In a real implementation, you would integrate with a payment gateway
            // like Stripe, PayPal, or a local payment processor

            // For demonstration, we'll simulate a payment gateway call
            $paymentResult = $this->simulatePaymentGateway([
                'amount' => $order->total,
                'currency' => 'PHP',
                'card_number' => $request->input('card_number'),
                'order_id' => $order->id
            ]);

            if ($paymentResult['success']) {
                $this->updateTransactionStatus($order, 'approved', $paymentResult['transaction_id']);

                Log::info('Card payment successful', [
                    'order_id' => $order->id,
                    'transaction_id' => $paymentResult['transaction_id']
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'transaction_id' => $paymentResult['transaction_id']
                ];
            } else {
                $this->updateTransactionStatus($order, 'declined');

                Log::warning('Card payment failed', [
                    'order_id' => $order->id,
                    'reason' => $paymentResult['message']
                ]);

                return [
                    'success' => false,
                    'message' => 'Payment failed: ' . $paymentResult['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Card payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Payment processing error. Please try again.'
            ];
        }
    }

    /**
     * Process GCash payment
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    private function processGcashPayment(Request $request, Order $order): array
    {
        try {
            // In a real implementation, integrate with GCash API
            // This is a simulation

            $gcashResult = $this->simulateGcashPayment([
                'amount' => $order->total,
                'mobile' => $request->input('gcash_mobile'),
                'order_id' => $order->id
            ]);

            if ($gcashResult['success']) {
                $this->updateTransactionStatus($order, 'approved', $gcashResult['reference_number']);

                return [
                    'success' => true,
                    'message' => 'GCash payment successful',
                    'reference_number' => $gcashResult['reference_number']
                ];
            } else {
                $this->updateTransactionStatus($order, 'declined');

                return [
                    'success' => false,
                    'message' => 'GCash payment failed: ' . $gcashResult['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('GCash payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'GCash payment processing error. Please try again.'
            ];
        }
    }

    /**
     * Process Cash on Delivery
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    private function processCodPayment(Request $request, Order $order): array
    {
        // COD is always successful at this stage
        $this->updateTransactionStatus($order, 'pending');

        Log::info('COD order created', [
            'order_id' => $order->id,
            'amount' => $order->total
        ]);

        return [
            'success' => true,
            'message' => 'Cash on Delivery order confirmed'
        ];
    }

    /**
     * Validate card details
     *
     * @param Request $request
     * @return array
     */
    private function validateCardDetails(Request $request): array
    {
        $cardNumber = $request->input('card_number');
        $expiryMonth = $request->input('expiry_month');
        $expiryYear = $request->input('expiry_year');
        $cvv = $request->input('cvv');

        // Basic validation
        if (empty($cardNumber) || strlen(preg_replace('/\D/', '', $cardNumber)) < 13) {
            return ['valid' => false, 'message' => 'Invalid card number'];
        }

        if (empty($expiryMonth) || empty($expiryYear)) {
            return ['valid' => false, 'message' => 'Invalid expiry date'];
        }

        if (empty($cvv) || strlen($cvv) < 3) {
            return ['valid' => false, 'message' => 'Invalid CVV'];
        }

        // Check if card is expired
        $currentYear = date('Y');
        $currentMonth = date('m');

        if ($expiryYear < $currentYear || ($expiryYear == $currentYear && $expiryMonth < $currentMonth)) {
            return ['valid' => false, 'message' => 'Card has expired'];
        }

        return ['valid' => true, 'message' => 'Valid'];
    }

    /**
     * Simulate payment gateway (replace with real implementation)
     *
     * @param array $data
     * @return array
     */
    private function simulatePaymentGateway(array $data): array
    {
        // This is a simulation - replace with actual payment gateway integration

        // Simulate random success/failure for demonstration
        $success = rand(1, 10) > 2; // 80% success rate for simulation

        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'TXN_' . time() . '_' . rand(1000, 9999),
                'message' => 'Payment successful'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Insufficient funds or card declined'
            ];
        }
    }

    /**
     * Simulate GCash payment (replace with real implementation)
     *
     * @param array $data
     * @return array
     */
    private function simulateGcashPayment(array $data): array
    {
        // This is a simulation - replace with actual GCash API integration

        $success = rand(1, 10) > 1; // 90% success rate for simulation

        if ($success) {
            return [
                'success' => true,
                'reference_number' => 'GC_' . time() . '_' . rand(100000, 999999),
                'message' => 'GCash payment successful'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Insufficient GCash balance or transaction failed'
            ];
        }
    }

    /**
     * Update transaction status
     *
     * @param Order $order
     * @param string $status
     * @param string|null $transactionId
     * @return void
     */
    private function updateTransactionStatus(Order $order, string $status, ?string $transactionId = null): void
    {
        $transaction = Transaction::where('order_id', $order->id)->first();

        if ($transaction) {
            $transaction->status = $status;
            if ($transactionId) {
                $transaction->transaction_id = $transactionId;
            }
            $transaction->save();
        }
    }
}

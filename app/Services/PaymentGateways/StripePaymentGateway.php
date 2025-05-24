<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\CardException;
use Stripe\Exception\ApiErrorException;

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Process payment through Stripe
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    public function processPayment(Request $request, Order $order): array
    {
        try {
            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $order->total * 100, // Convert to cents
                'currency' => 'php',
                'payment_method' => $request->input('payment_method_id'),
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('payment.success'),
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'user_email' => $order->user->email ?? 'unknown'
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                Log::info('Stripe payment successful', [
                    'order_id' => $order->id,
                    'payment_intent_id' => $paymentIntent->id,
                    'amount' => $order->total
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'transaction_id' => $paymentIntent->id,
                    'payment_method' => 'stripe_card'
                ];
            } elseif ($paymentIntent->status === 'requires_action') {
                // 3D Secure authentication required
                return [
                    'success' => false,
                    'requires_action' => true,
                    'payment_intent' => $paymentIntent,
                    'message' => '3D Secure authentication required'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Payment failed: ' . $paymentIntent->status
                ];
            }
        } catch (CardException $e) {
            Log::warning('Stripe card error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'decline_code' => $e->getDeclineCode()
            ]);

            return [
                'success' => false,
                'message' => 'Card declined: ' . $e->getMessage()
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Payment processing error. Please try again.'
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.'
            ];
        }
    }

    /**
     * Validate payment data
     *
     * @param Request $request
     * @return array
     */
    public function validatePaymentData(Request $request): array
    {
        $rules = [
            'payment_method_id' => 'required|string',
        ];

        return $rules;
    }

    /**
     * Get payment method name
     *
     * @return string
     */
    public function getPaymentMethodName(): string
    {
        return 'stripe_card';
    }

    /**
     * Check if gateway is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !empty(config('services.stripe.secret')) && 
               !empty(config('services.stripe.key'));
    }

    /**
     * Get client configuration for frontend
     *
     * @return array
     */
    public function getClientConfig(): array
    {
        return [
            'publishable_key' => config('services.stripe.key'),
            'currency' => 'php',
            'country' => 'PH'
        ];
    }

    /**
     * Handle webhook from Stripe
     *
     * @param Request $request
     * @return array
     */
    public function handleWebhook(Request $request): array
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );

            // Handle the event
            switch ($event['type']) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event['data']['object'];
                    $this->handleSuccessfulPayment($paymentIntent);
                    break;
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event['data']['object'];
                    $this->handleFailedPayment($paymentIntent);
                    break;
                default:
                    Log::info('Unhandled Stripe webhook event', ['type' => $event['type']]);
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle successful payment webhook
     *
     * @param object $paymentIntent
     * @return void
     */
    private function handleSuccessfulPayment($paymentIntent): void
    {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        
        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $order->update(['status' => 'paid']);
                
                Log::info('Order marked as paid via webhook', [
                    'order_id' => $orderId,
                    'payment_intent_id' => $paymentIntent['id']
                ]);
            }
        }
    }

    /**
     * Handle failed payment webhook
     *
     * @param object $paymentIntent
     * @return void
     */
    private function handleFailedPayment($paymentIntent): void
    {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        
        if ($orderId) {
            Log::warning('Payment failed via webhook', [
                'order_id' => $orderId,
                'payment_intent_id' => $paymentIntent['id'],
                'failure_reason' => $paymentIntent['last_payment_error']['message'] ?? 'Unknown'
            ]);
        }
    }
}

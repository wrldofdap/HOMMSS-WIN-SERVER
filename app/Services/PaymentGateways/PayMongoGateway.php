<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoGateway implements PaymentGatewayInterface
{
    private $baseUrl = 'https://api.paymongo.com/v1';
    private $secretKey;
    private $publicKey;

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key');
        $this->publicKey = config('services.paymongo.public_key');
    }

    /**
     * Process payment through PayMongo
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    public function processPayment(Request $request, Order $order): array
    {
        $paymentMethod = $request->input('paymongo_method', 'card');

        switch ($paymentMethod) {
            case 'card':
                return $this->processCardPayment($request, $order);
            case 'gcash':
                return $this->processGcashPayment($request, $order);
            case 'grab_pay':
                return $this->processGrabPayPayment($request, $order);
            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported payment method'
                ];
        }
    }

    /**
     * Process card payment
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    private function processCardPayment(Request $request, Order $order): array
    {
        try {
            // Create payment method
            $paymentMethodResponse = $this->createPaymentMethod($request, 'card');
            
            if (!$paymentMethodResponse['success']) {
                return $paymentMethodResponse;
            }

            // Create payment intent
            $paymentIntentResponse = $this->createPaymentIntent($order, $paymentMethodResponse['payment_method_id']);
            
            if (!$paymentIntentResponse['success']) {
                return $paymentIntentResponse;
            }

            // Attach payment method and confirm
            $confirmResponse = $this->confirmPaymentIntent(
                $paymentIntentResponse['payment_intent_id'],
                $paymentMethodResponse['payment_method_id']
            );

            return $confirmResponse;

        } catch (\Exception $e) {
            Log::error('PayMongo card payment error', [
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
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post($this->baseUrl . '/sources', [
                    'data' => [
                        'attributes' => [
                            'amount' => $order->total * 100, // Convert to centavos
                            'currency' => 'PHP',
                            'type' => 'gcash',
                            'redirect' => [
                                'success' => route('payment.success'),
                                'failed' => route('payment.failed')
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('GCash payment source created', [
                    'order_id' => $order->id,
                    'source_id' => $data['data']['id']
                ]);

                return [
                    'success' => true,
                    'redirect_url' => $data['data']['attributes']['redirect']['checkout_url'],
                    'source_id' => $data['data']['id'],
                    'message' => 'Redirecting to GCash...'
                ];
            } else {
                Log::error('PayMongo GCash error', [
                    'order_id' => $order->id,
                    'response' => $response->json()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to create GCash payment. Please try again.'
                ];
            }
        } catch (\Exception $e) {
            Log::error('PayMongo GCash payment error', [
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
     * Process GrabPay payment
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    private function processGrabPayPayment(Request $request, Order $order): array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post($this->baseUrl . '/sources', [
                    'data' => [
                        'attributes' => [
                            'amount' => $order->total * 100,
                            'currency' => 'PHP',
                            'type' => 'grab_pay',
                            'redirect' => [
                                'success' => route('payment.success'),
                                'failed' => route('payment.failed')
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'redirect_url' => $data['data']['attributes']['redirect']['checkout_url'],
                    'source_id' => $data['data']['id'],
                    'message' => 'Redirecting to GrabPay...'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create GrabPay payment. Please try again.'
                ];
            }
        } catch (\Exception $e) {
            Log::error('PayMongo GrabPay payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'GrabPay payment processing error. Please try again.'
            ];
        }
    }

    /**
     * Create payment method
     *
     * @param Request $request
     * @param string $type
     * @return array
     */
    private function createPaymentMethod(Request $request, string $type): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post($this->baseUrl . '/payment_methods', [
                'data' => [
                    'attributes' => [
                        'type' => $type,
                        'details' => [
                            'card_number' => $request->input('card_number'),
                            'exp_month' => (int) $request->input('expiry_month'),
                            'exp_year' => (int) $request->input('expiry_year'),
                            'cvc' => $request->input('cvv')
                        ]
                    ]
                ]
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'payment_method_id' => $data['data']['id']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid card details'
            ];
        }
    }

    /**
     * Create payment intent
     *
     * @param Order $order
     * @param string $paymentMethodId
     * @return array
     */
    private function createPaymentIntent(Order $order, string $paymentMethodId): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post($this->baseUrl . '/payment_intents', [
                'data' => [
                    'attributes' => [
                        'amount' => $order->total * 100,
                        'currency' => 'PHP',
                        'payment_method_allowed' => ['card'],
                        'payment_method_options' => [
                            'card' => [
                                'request_three_d_secure' => 'automatic'
                            ]
                        ],
                        'description' => 'Order #' . $order->id,
                        'statement_descriptor' => 'HOMMSS Store'
                    ]
                ]
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'payment_intent_id' => $data['data']['id']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create payment intent'
            ];
        }
    }

    /**
     * Confirm payment intent
     *
     * @param string $paymentIntentId
     * @param string $paymentMethodId
     * @return array
     */
    private function confirmPaymentIntent(string $paymentIntentId, string $paymentMethodId): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post($this->baseUrl . "/payment_intents/{$paymentIntentId}/attach", [
                'data' => [
                    'attributes' => [
                        'payment_method' => $paymentMethodId
                    ]
                ]
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $status = $data['data']['attributes']['status'];

            if ($status === 'succeeded') {
                return [
                    'success' => true,
                    'transaction_id' => $paymentIntentId,
                    'message' => 'Payment successful'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Payment failed: ' . $status
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Payment confirmation failed'
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
        $method = $request->input('paymongo_method', 'card');
        
        if ($method === 'card') {
            return [
                'card_number' => 'required|string|min:13|max:19',
                'expiry_month' => 'required|integer|min:1|max:12',
                'expiry_year' => 'required|integer|min:' . date('Y'),
                'cvv' => 'required|string|min:3|max:4',
            ];
        }

        return [];
    }

    /**
     * Get payment method name
     *
     * @return string
     */
    public function getPaymentMethodName(): string
    {
        return 'paymongo';
    }

    /**
     * Check if gateway is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !empty($this->secretKey) && !empty($this->publicKey);
    }

    /**
     * Get client configuration for frontend
     *
     * @return array
     */
    public function getClientConfig(): array
    {
        return [
            'public_key' => $this->publicKey,
            'currency' => 'PHP',
            'country' => 'PH'
        ];
    }

    /**
     * Handle webhook from PayMongo
     *
     * @param Request $request
     * @return array
     */
    public function handleWebhook(Request $request): array
    {
        // PayMongo webhook handling
        $payload = $request->all();
        
        Log::info('PayMongo webhook received', $payload);

        // Handle different webhook events
        $eventType = $payload['data']['attributes']['type'] ?? null;
        
        switch ($eventType) {
            case 'payment.paid':
                $this->handlePaymentPaid($payload);
                break;
            case 'payment.failed':
                $this->handlePaymentFailed($payload);
                break;
        }

        return ['success' => true];
    }

    /**
     * Handle payment paid webhook
     *
     * @param array $payload
     * @return void
     */
    private function handlePaymentPaid(array $payload): void
    {
        // Extract order information and update status
        Log::info('Payment paid webhook processed', $payload);
    }

    /**
     * Handle payment failed webhook
     *
     * @param array $payload
     * @return void
     */
    private function handlePaymentFailed(array $payload): void
    {
        // Handle failed payment
        Log::warning('Payment failed webhook processed', $payload);
    }
}

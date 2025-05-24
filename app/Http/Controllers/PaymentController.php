<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Show payment form
     */
    public function showPaymentForm($order_id)
    {
        $order = Order::findOrFail($order_id);

        // Check if order belongs to authenticated user
        if ($order->user_id != Auth::id()) {
            return redirect()->route('home')->with('error', 'Unauthorized access');
        }

        return view('payment.form', compact('order'));
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request, PaymentService $paymentService)
    {
        // Validate payment data
        $validationRules = [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:card,gcash,cod',
        ];

        // Add specific validation based on payment method
        if ($request->payment_method === 'card') {
            $validationRules = array_merge($validationRules, [
                'card_number' => 'required|string|min:13|max:19',
                'expiry_month' => 'required|integer|min:1|max:12',
                'expiry_year' => 'required|integer|min:' . date('Y'),
                'cvv' => 'required|string|min:3|max:4',
                'cardholder_name' => 'required|string|max:255'
            ]);
        } elseif ($request->payment_method === 'gcash') {
            $validationRules = array_merge($validationRules, [
                'gcash_mobile' => 'required|string|regex:/^09\d{9}$/'
            ]);
        }

        $request->validate($validationRules);

        $order_id = $request->order_id;
        $order = Order::findOrFail($order_id);

        // Check if order belongs to authenticated user
        if ($order->user_id != Auth::id()) {
            Log::warning('Unauthorized payment attempt', [
                'user_id' => Auth::id(),
                'order_id' => $order_id,
                'order_user_id' => $order->user_id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('home')->with('error', 'Unauthorized access');
        }

        // Check if order is already paid
        $transaction = Transaction::where('order_id', $order_id)->first();
        if ($transaction && $transaction->status === 'approved') {
            return redirect()->route('cart.order.confirmation')->with('info', 'This order has already been paid.');
        }

        try {
            // Process payment using the secure payment service
            $result = $paymentService->processPayment($request, $order);

            if ($result['success']) {
                // Store order ID in session for confirmation page
                Session::put('order_id', $order_id);

                return redirect()->route('cart.order.confirmation')
                    ->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->with('error', $result['message'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'order_id' => $order_id,
                'payment_method' => $request->payment_method,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred during payment processing. Please try again later.')
                ->withInput();
        }
    }

    /**
     * Process card payment
     */
    private function processCardPayment(Request $request, Order $order)
    {
        // Implement card payment processing logic
        // This would integrate with your payment gateway like Stripe, PayStack, etc.

        // For demonstration purposes, we'll return true
        return true;
    }

    /**
     * Process Gcash payment
     */
    private function processGcashPayment(Request $request, Order $order)
    {
        // Implement Gcash payment processing logic

        // For demonstration purposes, we'll return true
        return true;
    }

    /**
     * Handle payment callback from payment gateway
     */
    public function handlePaymentCallback(Request $request)
    {
        // Process callback data from payment gateway
        $paymentSuccessful = true; // Determine this based on callback data

        if ($paymentSuccessful) {
            // Update order and transaction status
            $order_id = $request->input('order_id');
            $transaction = Transaction::where('order_id', $order_id)->first();

            if ($transaction) {
                $transaction->status = 'approved';
                $transaction->save();
            }

            // Store order ID in session for confirmation page
            Session::put('order_id', $order_id);

            return redirect()->route('cart.order.confirmation');
        } else {
            return redirect()->route('payment.failed');
        }
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess()
    {
        return view('payment.success');
    }

    /**
     * Show payment failed page
     */
    public function paymentFailed()
    {
        return view('payment.failed');
    }

    /**
     * Handle Stripe webhook
     */
    public function stripeWebhook(Request $request)
    {
        $gateway = new \App\Services\PaymentGateways\StripePaymentGateway();
        $result = $gateway->handleWebhook($request);

        return response()->json($result);
    }

    /**
     * Handle PayMongo webhook
     */
    public function paymongoWebhook(Request $request)
    {
        $gateway = new \App\Services\PaymentGateways\PayMongoGateway();
        $result = $gateway->handleWebhook($request);

        return response()->json($result);
    }
}


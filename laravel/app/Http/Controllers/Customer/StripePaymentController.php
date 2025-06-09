<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\CustomerCard;
use App\Models\Order;
use App\Models\Payment;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe PaymentIntent (for one-time card entry via Stripe.js)
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100', // amount in cents
        ]);

        $customer = $request->user();
        $amountInDollars = $request->amount / 100;

        // 1. Create an order
        $order = Order::create([
            'customer_id' => $customer->id,
            'total_price' => $amountInDollars,
            'status' => 'pending',
        ]);

        // 2. Create payment intent
        $paymentIntent = PaymentIntent::create([
            'amount' => (int) $request->amount,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);

        // 3. Save to payments table
        Payment::create([
            'order_id' => $order->id,
            'amount' => $amountInDollars,
            'status' => 'pending', // updated later by webhook or confirmation step
            'type' => 'stripe',
            'created_by' => $customer->id,
        ]);

        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
            'order_id' => $order->id,
        ]);
    }
    // in PaymentController or StripeController
    public function updateStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:payments,order_id',
            'status' => 'required|string|in:succeeded,failed',
        ]);

        Payment::where('order_id', $request->order_id)->update([
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Payment status updated']);
    }


    /**
     * Charge a saved card (off-session) + auto log to `payments` table
     */
    public function payWithSavedCard(Request $request)
    {
        $request->validate([
            'card_id'   => 'required|integer',
            'amount'    => 'required|numeric|min:100',
            'order_id'  => 'required|exists:orders,id',
        ]);

        $customer = $request->user();

        // 🔍 Find saved card that belongs to the authenticated customer
        $card = CustomerCard::where('id', $request->card_id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        // 💳 Create a Stripe payment
        $paymentIntent = PaymentIntent::create([
            'amount'         => (int) $request->amount,
            'currency'       => 'usd',
            'customer'       => $customer->stripe_customer_id,
            'payment_method' => $card->stripe_card_id,
            'off_session'    => true, // charge without user interaction
            'confirm'        => true, // confirm immediately
        ]);

        // 💾 Log payment to database
        Payment::create([
            'order_id'   => $request->order_id,
            'amount'     => $request->amount / 100, // convert cents to dollars
            'status'     => $paymentIntent->status,
            'type'       => 'stripe',
            'created_by' => $customer->id,
        ]);

        return response()->json([
            'status' => $paymentIntent->status,
        ]);
    }
}

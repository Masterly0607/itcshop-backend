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
     * 1️⃣ Create a Stripe PaymentIntent (one-time card entry via Stripe.js)
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100', // Amount in cents (e.g., 100 = $1.00)
        ]);

        $customer = $request->user();
        $amountInDollars = $request->amount / 100;

        // Step 1: Create the Order
        $order = Order::create([
            'customer_id'  => $customer->id,
            'total_price'  => $amountInDollars,
            'status'       => 'pending',
            'code'         => $request->input('coupon_code', null),
        ]);

        // Step 2: Create Stripe PaymentIntent
        $paymentIntent = PaymentIntent::create([
            'amount'   => (int) $request->amount,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);

        // Step 3: Log Payment (Pending)
        Payment::create([
            'order_id'   => $order->id,
            'amount'     => $amountInDollars,
            'status'     => 'pending',
            'type'       => 'stripe',
            'created_by' => $customer->id,
        ]);

        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
            'order_id'      => $order->id,
        ]);
    }

    /**
     * 2️⃣ Update Stripe payment status (used in webhook or after confirmation)
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:payments,order_id',
            'status'   => 'required|string|in:succeeded,failed',
        ]);

        Payment::where('order_id', $request->order_id)->update([
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Payment status updated']);
    }

    /**
     * 3️⃣ Charge a saved card (off-session payment)
     */
    public function payWithSavedCard(Request $request)
    {
        $request->validate([
            'card_id'   => 'required|integer',
            'amount'    => 'required|numeric|min:100',
            'order_id'  => 'required|exists:orders,id',
        ]);

        $customer = $request->user();

        // Step 1: Get saved card for this customer
        $card = CustomerCard::where('id', $request->card_id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        // Step 2: Charge card using Stripe
        $paymentIntent = PaymentIntent::create([
            'amount'         => (int) $request->amount,
            'currency'       => 'usd',
            'customer'       => $customer->stripe_customer_id,
            'payment_method' => $card->stripe_card_id,
            'off_session'    => true,
            'confirm'        => true,
        ]);

        // Step 3: Log payment
        Payment::create([
            'order_id'   => $request->order_id,
            'amount'     => $request->amount / 100,
            'status'     => $paymentIntent->status,
            'type'       => 'stripe',
            'created_by' => $customer->id,
        ]);

        return response()->json([
            'status' => $paymentIntent->status,
        ]);
    }
}

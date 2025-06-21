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
     * 1️ Create a Stripe PaymentIntent (one-time card entry via Stripe.js)
     */
    public function createPaymentIntent(Request $request) // PaymentIntent = The order / recipe in Stripe
{
    $customer = $request->user();

    //  Get cart items for this customer
    $cartItems = $customer->carts()->with('product')->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Cart is empty'], 400);
    }

    //  Calculate total
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item->product->price * $item->quantity;
    }

    //  Convert to cents
    $amountInCents = (int) ($total * 100);

    //  Create Order
    $order = Order::create([
        'customer_id' => $customer->id,
        'total_price' => $total,
        'status'      => 'pending',
        'code'        => $request->input('coupon_code', null),
    ]);

    //  Create Stripe PaymentIntent
    $paymentIntent = PaymentIntent::create([
        'amount'               => $amountInCents,
        'currency'             => 'usd',
        'payment_method_types' => ['card'],
    ]);

    //  Log Payment (Pending)
    Payment::create([
        'order_id'   => $order->id,
        'amount'     => $total,
        'status'     => 'pending',
        'type'       => 'stripe',
        'created_by' => $customer->id,
    ]);

    return response()->json([
        'client_secret' => $paymentIntent->client_secret, // client_secret = The permission to pay it from frontend
        'order_id'      => $order->id,
    ]);
}


    /**
     * 2️ Update Stripe payment status (used in webhook or after confirmation)
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
     * 3️ Charge a saved card (off-session payment)
     */
public function payWithSavedCard(Request $request)
{
    $request->validate([
        'card_id'   => 'required|integer',
        'order_id'  => 'required|exists:orders,id',
    ]);

    $customer = $request->user();

    //  Get saved card
    $card = CustomerCard::where('id', $request->card_id)
        ->where('customer_id', $customer->id)
        ->firstOrFail();

    //  Get order and calculate amount in cents
    $order = Order::findOrFail($request->order_id);
    $amount = (int) ($order->total_price * 100); // Convert dollars to cents

    //  Charge card using Stripe
    $paymentIntent = PaymentIntent::create([
        'amount'         => $amount,
        'currency'       => 'usd',
        'customer'       => $customer->stripe_customer_id,
        'payment_method' => $card->stripe_card_id,
        'off_session'    => true,
        'confirm'        => true,
    ]);

    // Log payment
    Payment::create([
        'order_id'   => $order->id,
        'amount'     => $order->total_price,
        'status'     => $paymentIntent->status,
        'type'       => 'stripe',
        'created_by' => $customer->id,
    ]);

    // Also update order status to 'completed'
    if ($paymentIntent->status === 'succeeded') {
        $order->update(['status' => 'completed']);
    }

    return response()->json([
        'status' => $paymentIntent->status,
    ]);
}


}

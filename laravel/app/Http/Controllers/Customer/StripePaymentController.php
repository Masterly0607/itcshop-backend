<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\CustomerCard;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // Use for Stripe.js frontend flow (not saved card)
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100'
        ]);

        $paymentIntent = PaymentIntent::create([
            'amount' => (int) $request->amount,
            'currency' => 'usd',
            'payment_method_types' => ['card'], // one-time card entry
        ]);

        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }

    // 🔹 Use to charge a saved card (off-session)
    public function payWithSavedCard(Request $request)
    {
        $request->validate([
            'card_id' => 'required|integer',
            'amount' => 'required|numeric|min:100'
        ]);

        $customer = $request->user();

        $card = CustomerCard::where('id', $request->card_id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $paymentIntent = PaymentIntent::create([
            'amount' => (int) $request->amount,
            'currency' => 'usd',
            'customer' => $customer->stripe_customer_id,
            'payment_method' => $card->stripe_card_id,
            'off_session' => true,
            'confirm' => true,
        ]);

        return response()->json([
            'status' => $paymentIntent->status
        ]);
    }
}

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerCard;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function addCard(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $customer = $request->user();

        // Create Stripe customer if not exists
        if (!$customer->stripe_customer_id) {
            $stripeCustomer = Customer::create([
                'email' => $customer->email,
            ]); // Creates a customer on Stripe and return with your email and stripe id to identify customer in stripe
            $customer->stripe_customer_id = $stripeCustomer->id;
            $customer->save();
        }

        // Create PaymentMethod from token(Convert card number to token because stipe not store your card number)
        $paymentMethod = PaymentMethod::create([
            'type' => 'card',
            'card' => ['token' => $request->token],
        ]);

        $paymentMethod->attach(['customer' => $customer->stripe_customer_id]);

        return CustomerCard::create([
            'customer_id' => $customer->id,
            'stripe_card_id' => $paymentMethod->id,
            'brand' => $paymentMethod->card->brand,
            'last4' => $paymentMethod->card->last4,
            'exp_month' => $paymentMethod->card->exp_month,
            'exp_year' => $paymentMethod->card->exp_year,
        ]);
    }

    public function index(Request $request)
    {
        return CustomerCard::where('customer_id', $request->user()->id)->get();
    }

    public function destroy(Request $request, $id)
    {
        $card = CustomerCard::where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        PaymentMethod::retrieve($card->stripe_card_id)->detach();

        $card->delete();

        return response()->json(['message' => 'Card deleted successfully']);
    }
}

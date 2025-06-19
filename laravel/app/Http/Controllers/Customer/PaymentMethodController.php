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

    /**
     * Add a new Stripe card using tokenized card data
     */
    public function addCard(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->user();

        try {
            // 1. Create Stripe customer if not already exists
            if (!$user->stripe_customer_id) {
                $stripeCustomer = Customer::create([
                    'email' => $user->email,
                ]);

                $user->stripe_customer_id = $stripeCustomer->id;
                $user->save();
            }

            // 2. Create a payment method from token
            $paymentMethod = PaymentMethod::create([
                'type' => 'card',
                'card' => ['token' => $request->token],
            ]);

            // 3. Attach payment method to the customer
            $paymentMethod->attach([
                'customer' => $user->stripe_customer_id,
            ]);

            // 4. Save card info locally
            $card = CustomerCard::create([
                'customer_id'     => $user->id,
                'stripe_card_id'  => $paymentMethod->id,
                'brand'           => $paymentMethod->card->brand,
                'last4'           => $paymentMethod->card->last4,
                'exp_month'       => $paymentMethod->card->exp_month,
                'exp_year'        => $paymentMethod->card->exp_year,
            ]);

            return response()->json([
                'message' => 'Card added successfully',
                'card'    => $card,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Card addition failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of saved Stripe cards
     */
    public function index(Request $request)
    {
        $cards = CustomerCard::where('customer_id', $request->user()->id)->get();

        return response()->json($cards);
    }

    /**
     * Delete a saved Stripe card
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            $card = CustomerCard::where('id', $id)
                ->where('customer_id', $user->id)
                ->firstOrFail();

            // Detach from Stripe
            PaymentMethod::retrieve($card->stripe_card_id)->detach();

            // Delete from local DB
            $card->delete();

            return response()->json(['message' => 'Card deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Card deletion failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

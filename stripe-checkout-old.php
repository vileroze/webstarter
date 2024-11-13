<?php

session_start();
require_once("../../../wp-load.php");
require_once './stripe-php/init.php';
require_once './stripe-payment/stripe-secrets.php';

$stripe = new \Stripe\StripeClient($stripeSecretKey);


function calculateOrderAmount()
{
    $total = 0;
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => [$payment_option, $installment_duration]) {
            $product = get_post($product_id);
            if ($payment_option == 'installment') {
                $total += (int)(get_wstr_price_value($product->ID) / $installment_duration);
            } else {
                $total += (int)get_wstr_price_value($product->ID);
            }
        }
    }

    return $total;
}


header('Content-Type: application/json');

try {

    // Create a PaymentIntent with amount and currency
    $paymentIntent = $stripe->paymentIntents->create([
        'amount' => calculateOrderAmount(),
        'currency' => 'usd',
    ]);

    $output = [
        'clientSecret' => $paymentIntent->client_secret,
        // [DEV]: For demo purposes only, you should avoid exposing the PaymentIntent ID in the client-side code.
        'dpmCheckerLink' => "https://dashboard.stripe.com/settings/payment_methods/review?transaction_id={$paymentIntent->id}",
    ];

    echo json_encode($output);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

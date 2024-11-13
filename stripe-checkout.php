<?php
session_start();
require_once("../../../wp-load.php");
require_once './stripe-php/init.php';
require_once './stripe-payment/stripe-secrets.php';

$stripe = new \Stripe\StripeClient($stripeSecretKey);

function calculateOrderAmount($items)
{
    $total = 0;
    foreach ($items as $product_id => [$payment_option, $installment_duration]) {
        $product = get_post($product_id);
        $price = get_wstr_price_value($product->ID);

        if ($payment_option === 'installment') {
            $total += (int)(($price / $installment_duration) * 100);
        } else {
            $total += (int)($price * 100);
        }
    }
    return $total;
}

function createCustomer($stripe, $email = null)
{
    try {
        return $stripe->customers->create([
            'email' => $email ?: wp_get_current_user()->user_email,
            'metadata' => [
                'wordpress_user_id' => get_current_user_id()
            ]
        ]);
    } catch (Exception $e) {
        error_log('Error creating customer: ' . $e->getMessage());
        throw $e;
    }
}

function handleSubscriptionItems($stripe, $customerId) {
    try {
        $subscriptionItems = [];
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $maxInstallmentDuration = 0;

        // Debug logging
        error_log('Starting subscription creation with cart: ' . json_encode($cart));

        // First pass: collect all subscription items and find max duration
        foreach ($cart as $product_id => [$payment_option, $installment_duration]) {
            if ($payment_option === 'installment') {
                $product = get_post($product_id);
                $price = get_wstr_price_value($product->ID);
                
                error_log("Processing product ID: {$product_id} with price: {$price} and duration: {$installment_duration}");

                // Create product
                $stripeProduct = $stripe->products->create([
                    'name' => $product->post_title,
                    'metadata' => [
                        'wordpress_product_id' => $product_id
                    ]
                ]);
                
                error_log("Created Stripe product: " . $stripeProduct->id);

                // Calculate price amount in cents
                $monthlyAmount = ceil(($price / $installment_duration) * 100);
                
                // Create price
                $stripePrice = $stripe->prices->create([
                    'unit_amount' => $monthlyAmount,
                    'currency' => 'usd',
                    'recurring' => [
                        'interval' => 'month',
                        'interval_count' => 1
                    ],
                    'product' => $stripeProduct->id
                ]);
                
                error_log("Created Stripe price: " . $stripePrice->id);

                $subscriptionItems[] = [
                    'price' => $stripePrice->id,
                    'quantity' => 1
                ];

                $maxInstallmentDuration = max($maxInstallmentDuration, (int)$installment_duration);
            }
        }

        if (empty($subscriptionItems)) {
            throw new Exception('No subscription items to process');
        }

        error_log("Creating subscription with items: " . json_encode($subscriptionItems));

        // Get payment methods using the correct service
        $paymentMethods = $stripe->paymentMethods->all([
            'customer' => $customerId,
            'type' => 'card'
        ]);

        // Create subscription with immediate confirmation
        $subscription = $stripe->subscriptions->create([
            'customer' => $customerId,
            'items' => $subscriptionItems,
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'payment_method_types' => ['card'],
                'save_default_payment_method' => 'on_subscription'
            ],
            'expand' => ['latest_invoice.payment_intent'],
            'metadata' => [
                'wordpress_user_id' => get_current_user_id()
            ]
        ]);

        error_log("Created subscription: " . json_encode($subscription));

        // Verify subscription and payment intent
        if (!$subscription) {
            throw new Exception('Failed to create subscription');
        }

        if (!isset($subscription->latest_invoice) || 
            !isset($subscription->latest_invoice->payment_intent)) {
            throw new Exception('Subscription created but payment intent is missing');
        }

        // Create a new payment intent if not present
        if (!isset($subscription->latest_invoice->payment_intent->client_secret)) {
            error_log("Payment intent missing, creating new one");
            
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $subscription->items->data[0]->price->unit_amount,
                'currency' => 'usd',
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'setup_future_usage' => 'off_session',
                'metadata' => [
                    'subscription_id' => $subscription->id
                ]
            ]);

            // Update subscription with new payment intent
            $stripe->subscriptions->update($subscription->id, [
                'default_payment_method' => $paymentIntent->payment_method
            ]);

            $subscription->latest_invoice->payment_intent = $paymentIntent;
        }

        return $subscription;

    } catch (Exception $e) {
        error_log('Subscription creation error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        throw $e;
    }
}


// Add webhook handling for subscription events
add_action('rest_api_init', function () {
    register_rest_route('stripe/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'handle_stripe_webhook',
        'permission_callback' => '__return_true'
    ));
});

function handle_stripe_webhook(WP_REST_Request $request)
{
    $payload = $request->get_body();
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $endpoint_secret = 'your_webhook_signing_secret'; // Set this in your WordPress configuration

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sig_header,
            $endpoint_secret
        );
    } catch (Exception $e) {
        return new WP_Error('webhook_error', $e->getMessage(), array('status' => 400));
    }

    // Handle specific events
    switch ($event->type) {
        case 'invoice.payment_failed':
            $invoice = $event->data->object;
            // Send email to customer about failed payment
            wp_mail(
                $invoice->customer_email,
                'Payment Failed',
                'Your payment for the recent installment has failed. Please update your payment method.'
            );
            break;

        case 'customer.subscription.deleted':
            $subscription = $event->data->object;
            // Handle subscription completion
            update_user_meta(
                $subscription->metadata->wordpress_user_id,
                'subscription_completed_' . $subscription->id,
                current_time('mysql')
            );
            break;
    }

    return new WP_REST_Response(null, 200);
}


function handleOneTimePayment($stripe, $customerId, $amount)
{
    try {
        return $stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $customerId,
            'metadata' => [
                'cart_id' => session_id(),
                'wordpress_user_id' => get_current_user_id()
            ]
        ]);
    } catch (Exception $e) {
        error_log('Error creating payment intent: ' . $e->getMessage());
        throw $e;
    }
}

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        throw new Exception('Cart is empty');
    }

    $hasSubscription = false;
    $hasOneTime = false;
    $cart = $_SESSION['cart'];

    // Analyze cart contents
    foreach ($cart as $item) {
        if ($item[0] === 'installment') {
            $hasSubscription = true;
        } else {
            $hasOneTime = true;
        }
    }

    // Create or retrieve customer
    $customer = createCustomer($stripe);

    if ($hasSubscription) {
        try {
            $subscription = handleSubscriptionItems($stripe, $customer->id);
    
            if (!$subscription) {
                throw new Exception('No subscription created');
            }
    
            // Log the full subscription object for debugging
            error_log('Full subscription object: '. json_encode($subscription));
    
            // Check if we need to create a payment intent
            if (!isset($subscription->latest_invoice) || !isset($subscription->latest_invoice->payment_intent) || !isset($subscription->latest_invoice->payment_intent->client_secret)) {
                error_log('Missing required fields in subscription response');
                
                // Create a payment intent for the first payment after trial
                $paymentIntent = $stripe->paymentIntents->create([
                    'amount' => isset($subscription->items->data[0]->price->unit_amount) ? $subscription->items->data[0]->price->unit_amount : 0,
                    'currency' => isset($subscription->items->data[0]->price->currency) ? $subscription->items->data[0]->price->currency : 'usd',
                    'setup_future_usage' => 'off_session',
                    'metadata' => [
                        'subscription_id' => $subscription->id,
                        'customer_id' => $customer->id
                    ]
                ]);
    
                error_log('Created payment intent: '. json_encode($paymentIntent));
    
                echo json_encode([
                    'clientSecret' => $paymentIntent->client_secret,
                    'subscriptionId' => $subscription->id,
                    'isSubscription' => true,
                    'customerId' => $customer->id
                ]);
            } else {
                error_log('Using existing payment intent');
                echo json_encode([
                    'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret,
                    'subscriptionId' => $subscription->id,
                    'isSubscription' => true,
                    'customerId' => $customer->id
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            error_log('Error creating subscription: '. $e->getMessage());
            echo json_encode([
                'error' => $e->getMessage()
            ]);
        }
    } else {
        $amount = calculateOrderAmount($cart);
        $paymentIntent = handleOneTimePayment($stripe, $customer->id, $amount);

        echo json_encode([
            'clientSecret' => $paymentIntent->client_secret,
            'isSubscription' => false,
            'customerId' => $customer->id
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('Stripe error: ' . $e->getMessage());
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

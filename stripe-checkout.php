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

function getProductMetadata($cart)
{
    $metadata = [];
    $products = [];
    $total_amount = 0;

    foreach ($cart as $product_id => [$payment_option, $installment_duration]) {
        $product = get_post($product_id);
        $price = get_wstr_price_value($product->ID);

        if ($payment_option !== 'installment') {
            $total_amount += (int)($price * 100);
        }

        $products[] = [
            'id' => $product_id,
            'name' => $product->post_title,
            'price' => $price,
            'payment_type' => $payment_option,
            'installment_duration' => $installment_duration
        ];
    }

    // Convert product data to metadata format
    $metadata['products_count'] = count($products);
    $metadata['total_amount'] = $total_amount;

    foreach ($products as $index => $product) {
        $prefix = "product_{$index}_";
        $metadata[$prefix . 'id'] = $product['id'];
        $metadata[$prefix . 'name'] = $product['name'];
        $metadata[$prefix . 'price'] = $product['price'];
        $metadata[$prefix . 'payment_type'] = $product['payment_type'];
        if ($product['payment_type'] === 'installment') {
            $metadata[$prefix . 'installment_duration'] = $product['installment_duration'];
        }
    }

    return $metadata;
}


function handleOneTimePayment($stripe, $customerId, $amount)
{
    try {
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $line_items = [];
        $metadata = [];
        $total_amount = 0;
        $product_index = 0;

        // Process each product individually
        foreach ($cart as $product_id => [$payment_option, $installment_duration]) {
            if ($payment_option !== 'installment') {
                $product = get_post($product_id);
                $price = get_wstr_price_value($product->ID);
                $amount_in_cents = (int)($price * 100);

                // Add to total amount
                $total_amount += $amount_in_cents;

                // Create metadata for this product
                $prefix = "product_{$product_index}_";
                $metadata[$prefix . 'id'] = $product_id;
                $metadata[$prefix . 'name'] = $product->post_title;
                $metadata[$prefix . 'price'] = $price;
                $metadata[$prefix . 'payment_type'] = 'single';

                // Create a Stripe Product and Price for each item
                $stripeProduct = $stripe->products->create([
                    'name' => $product->post_title,
                    'metadata' => [
                        'wordpress_product_id' => $product_id
                    ]
                ]);

                $stripePrice = $stripe->prices->create([
                    'unit_amount' => $amount_in_cents,
                    'currency' => 'usd',
                    'product' => $stripeProduct->id
                ]);

                $line_items[] = [
                    'price' => $stripePrice->id,
                    'quantity' => 1
                ];

                $product_index++;
            }
        }

        // Add count of products to metadata
        $metadata['products_count'] = $product_index;
        $metadata['total_amount'] = $total_amount;

        // Create payment intent with all products
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $total_amount,
            'currency' => 'usd',
            'customer' => $customerId,
            'metadata' => array_merge([
                'cart_id' => session_id(),
                'wordpress_user_id' => get_current_user_id(),
                'payment_type' => 'single'
            ], $metadata),
            'description' => "Order with {$product_index} products"
        ]);

        // Log the payment intent creation
        error_log('Created payment intent with metadata: ' . json_encode($metadata));

        return $paymentIntent;
    } catch (Exception $e) {
        error_log('Error creating payment intent: ' . $e->getMessage());
        throw $e;
    }
}


function handleSubscriptionItems($stripe, $customerId) {
    try {
        $subscriptionItems = [];
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $metadata = getProductMetadata($cart);
        
        // Process subscription items
        foreach ($cart as $product_id => [$payment_option, $installment_duration]) {
            if ($payment_option === 'installment') {
                $product = get_post($product_id);
                $price = get_wstr_price_value($product->ID);
                
                error_log("Processing subscription for product ID: {$product_id} with duration: {$installment_duration}");
                
                // Create product in Stripe
                $stripeProduct = $stripe->products->create([
                    'name' => $product->post_title,
                    'metadata' => [
                        'wordpress_product_id' => $product_id,
                        'product_name' => $product->post_title,
                        'payment_type' => 'installment',
                        'total_installments' => $installment_duration,
                        'original_price' => $price
                    ]
                ]);
                
                // Calculate monthly amount in cents
                $monthlyAmount = ceil(($price / $installment_duration) * 100);
                
                // Create price with recurring payments
                $stripePrice = $stripe->prices->create([
                    'unit_amount' => $monthlyAmount,
                    'currency' => 'usd',
                    'recurring' => [
                        'interval' => 'month',
                        'interval_count' => 1
                    ],
                    'product' => $stripeProduct->id,
                    'metadata' => [
                        'total_installments' => $installment_duration,
                        'monthly_amount' => $monthlyAmount
                    ]
                ]);

                $subscriptionItems[] = [
                    'price' => $stripePrice->id,
                    'quantity' => 1
                ];
            }
        }

        if (empty($subscriptionItems)) {
            throw new Exception('No subscription items to process');
        }

        // Get the current date for cancellation calculation
        $currentDate = new DateTime();
        $endDate = clone $currentDate;
        $endDate->modify("+{$installment_duration} months");
        
        // Create subscription that will automatically cancel
        $subscription = $stripe->subscriptions->create([
            'customer' => $customerId,
            'items' => $subscriptionItems,
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'payment_method_types' => ['card'],
                'save_default_payment_method' => 'on_subscription'
            ],
            'metadata' => array_merge([
                'wordpress_user_id' => get_current_user_id(),
                'total_installments' => $installment_duration,
                'start_date' => $currentDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'installments_completed' => '0'
            ], $metadata),
            'expand' => ['latest_invoice.payment_intent'],
            // Cancel subscription at end of installment period
            'cancel_at' => $endDate->getTimestamp(),
            // Trial period until first payment is confirmed
            'trial_end' => 'now'
        ]);

        // Store subscription details in WordPress for tracking
        update_user_meta(get_current_user_id(), 'stripe_subscription_' . $subscription->id, [
            'total_installments' => $installment_duration,
            'start_date' => $currentDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'installments_completed' => 0
        ]);

        return $subscription;

    } catch (Exception $e) {
        error_log('Subscription creation error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        throw $e;
    }
}



function handle_stripe_webhook(WP_REST_Request $request)
{
    $payload = $request->get_body();
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $endpoint_secret = 'your_webhook_signing_secret';

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sig_header,
            $endpoint_secret
        );
    } catch (Exception $e) {
        return new WP_Error('webhook_error', $e->getMessage(), array('status' => 400));
    }

    global $stripe;

    switch ($event->type) {
        case 'invoice.paid':
            $invoice = $event->data->object;
            $subscription_id = $invoice->subscription;

            if ($subscription_id) {
                try {
                    // Get subscription details
                    $subscription = $stripe->subscriptions->retrieve($subscription_id);
                    $metadata = $subscription->metadata;

                    // Update installments count
                    $installments_completed = isset($metadata->installments_completed)
                        ? ((int)$metadata->installments_completed + 1)
                        : 1;

                    // Update subscription metadata
                    $stripe->subscriptions->update($subscription_id, [
                        'metadata' => array_merge($metadata->toArray(), [
                            'installments_completed' => $installments_completed
                        ])
                    ]);

                    // Update WordPress metadata
                    $wordpress_user_id = $metadata->wordpress_user_id;
                    $subscription_meta = get_user_meta($wordpress_user_id, 'stripe_subscription_' . $subscription_id, true);
                    if ($subscription_meta) {
                        $subscription_meta['installments_completed'] = $installments_completed;
                        update_user_meta($wordpress_user_id, 'stripe_subscription_' . $subscription_id, $subscription_meta);
                    }

                    // Check if all installments are completed
                    if ($installments_completed >= (int)$metadata->total_installments) {
                        // Mark products as out of stock
                        $products_count = isset($metadata->products_count) ? (int)$metadata->products_count : 0;

                        for ($i = 0; $i < $products_count; $i++) {
                            $prefix = "product_{$i}_";
                            $product_id = $metadata->{$prefix . 'id'};

                            if ($product_id) {
                                update_post_meta($product_id, '_stock_status', 'outofstock');
                                update_post_meta($product_id, '_stock', '0');
                            }
                        }

                        // Cancel subscription if not already cancelled
                        if ($subscription->status !== 'canceled') {
                            $stripe->subscriptions->cancel($subscription_id);
                        }
                    }
                } catch (Exception $e) {
                    error_log('Error processing subscription payment: ' . $e->getMessage());
                }
            }
            break;

        case 'invoice.payment_failed':
            $invoice = $event->data->object;
            // Send email to customer
            wp_mail(
                $invoice->customer_email,
                'Payment Failed',
                'Your installment payment has failed. Please update your payment method to continue with your purchase.'
            );
            break;

        case 'customer.subscription.deleted':
            $subscription = $event->data->object;
            $metadata = $subscription->metadata;

            // Final cleanup if needed
            if ($metadata && isset($metadata->wordpress_user_id)) {
                delete_user_meta($metadata->wordpress_user_id, 'stripe_subscription_' . $subscription->id);
            }
            break;
    }

    return new WP_REST_Response(null, 200);
}


// Register webhook endpoint
add_action('rest_api_init', function () {
    register_rest_route('stripe/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'handle_stripe_webhook',
        'permission_callback' => '__return_true'
    ));
});





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
            error_log('Full subscription object: ' . json_encode($subscription));

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

                error_log('Created payment intent: ' . json_encode($paymentIntent));

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
            error_log('Error creating subscription: ' . $e->getMessage());
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

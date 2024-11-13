<?php

require_once get_template_directory() . '/stripe-php/init.php';
require_once get_template_directory() . '/stripe-payment/stripe-secrets.php';



$stripe = new \Stripe\StripeClient('sk_test_51QD2KcRu9HD10dVmDkweGKbZd9TqRLwsKff0TO3pwgQ3ql3mybiXpkEgIM5BqpbvI5W6RRAmYmyb29k5E2UMzGyl005yUkIvyD');

function handle_payment_success()
{
    global $stripe;
    $output = [];

    try {
        // Retrieve the payment/subscription status from URL parameters
        $payment_intent_id = isset($_GET['payment_intent']) ? sanitize_text_field($_GET['payment_intent']) : null;
        $subscription_id = isset($_GET['subscription']) ? sanitize_text_field($_GET['subscription']) : null;

        if ($payment_intent_id) {
            // Handle one-time payment
            $payment_intent = $stripe->paymentIntents->retrieve($payment_intent_id);

            if ($payment_intent->status === 'succeeded') {
                // Create order in WordPress
                $order_data = [
                    'payment_id' => $payment_intent_id,
                    'amount' => $payment_intent->amount / 100, // Convert from cents
                    'currency' => $payment_intent->currency,
                    'payment_type' => 'one_time',
                    'status' => 'completed',
                    'customer_email' => $payment_intent->receipt_email,
                    'cart_items' => isset($_SESSION['cart']) ? $_SESSION['cart'] : []
                ];

                // create_woocommerce_order($order_data);

                $output = [
                    'status' => 'success',
                    'type' => 'payment',
                    'message' => 'Your payment was successful!',
                    'amount' => number_format($payment_intent->amount / 100, 2),
                    'currency' => strtoupper($payment_intent->currency)
                ];

                // Clear the cart
                unset($_SESSION['cart']);
            } else {
                $output = [
                    'status' => 'error',
                    'message' => 'Payment was not successful. Please try again.'
                ];
            }
        } elseif ($subscription_id) {
            // Handle subscription
            $subscription = $stripe->subscriptions->retrieve($subscription_id);

            if ($subscription->status === 'active' || $subscription->status === 'trialing') {
                // Create subscription order in WordPress
                $subscription_data = [
                    'subscription_id' => $subscription_id,
                    'payment_type' => 'subscription',
                    'status' => 'active',
                    'customer_id' => $subscription->customer,
                    'amount' => $subscription->items->data[0]->price->unit_amount / 100,
                    'currency' => $subscription->currency,
                    'interval' => $subscription->items->data[0]->price->recurring->interval,
                    'interval_count' => $subscription->items->data[0]->price->recurring->interval_count,
                    'cart_items' => isset($_SESSION['cart']) ? $_SESSION['cart'] : []
                ];

                // create_subscription_order($subscription_data);

                $output = [
                    'status' => 'success',
                    'type' => 'subscription',
                    'message' => 'Your subscription was set up successfully!',
                    'amount' => number_format($subscription->items->data[0]->price->unit_amount / 100, 2),
                    'currency' => strtoupper($subscription->currency),
                    'interval' => $subscription->items->data[0]->price->recurring->interval
                ];

                // Clear the cart
                unset($_SESSION['cart']);
            } else {
                $output = [
                    'status' => 'error',
                    'message' => 'Subscription setup failed. Please try again.'
                ];
            }
        } else {
            $output = [
                'status' => 'error',
                'message' => 'Invalid payment response.'
            ];
        }
    } catch (Exception $e) {
        $output = [
            'status' => 'error',
            'message' => 'An error occurred: ' . $e->getMessage()
        ];
    }

    return $output;
}

// // Helper function to create WooCommerce order
// function create_woocommerce_order($order_data)
// {
//     if (!function_exists('wc_create_order')) {
//         return false;
//     }

//     $order = wc_create_order();

//     foreach ($order_data['cart_items'] as $product_id => $details) {
//         $product = wc_get_product($product_id);
//         if ($product) {
//             $order->add_product($product, 1);
//         }
//     }

//     $order->set_payment_method('stripe');
//     $order->set_payment_method_title('Stripe');
//     $order->set_total($order_data['amount']);

//     // Add payment meta data
//     $order->update_meta_data('_stripe_payment_id', $order_data['payment_id']);
//     $order->update_meta_data('_payment_type', $order_data['payment_type']);

//     $order->set_status('wc-completed', 'Order completed via Stripe payment');
//     $order->save();

//     return $order->get_id();
// }

// // Helper function to create subscription order
// function create_subscription_order($subscription_data)
// {
//     if (!function_exists('wc_create_order')) {
//         return false;
//     }

//     $order = wc_create_order();

//     foreach ($subscription_data['cart_items'] as $product_id => $details) {
//         $product = wc_get_product($product_id);
//         if ($product) {
//             $order->add_product($product, 1);
//         }
//     }

//     $order->set_payment_method('stripe');
//     $order->set_payment_method_title('Stripe Subscription');
//     $order->set_total($subscription_data['amount']);

//     // Add subscription meta data
//     $order->update_meta_data('_stripe_subscription_id', $subscription_data['subscription_id']);
//     $order->update_meta_data('_payment_type', $subscription_data['payment_type']);
//     $order->update_meta_data('_subscription_interval', $subscription_data['interval']);
//     $order->update_meta_data('_subscription_interval_count', $subscription_data['interval_count']);

//     $order->set_status('wc-processing', 'Subscription order created via Stripe');
//     $order->save();

//     return $order->get_id();
// }

// Get the payment result
$payment_result = handle_payment_success();
?>

<div class="payment-success-container">
    <?php if ($payment_result['status'] === 'success'): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <h1>Thank You!</h1>
            <p><?php echo esc_html($payment_result['message']); ?></p>

            <div class="payment-details">
                <h2>Payment Details</h2>
                <p>Amount: <?php echo esc_html($payment_result['currency']); ?> <?php echo esc_html($payment_result['amount']); ?></p>
                <?php if ($payment_result['type'] === 'subscription'): ?>
                    <p>Billing: Monthly</p>
                <?php endif; ?>
            </div>

            <div class="next-steps">
                <h3>Next Steps</h3>
                <p>You will receive a confirmation email shortly.</p>
                <?php if ($payment_result['type'] === 'subscription'): ?>
                    <p>You can manage your subscription from your account dashboard.</p>
                <?php endif; ?>
            </div>

            <div class="action-buttons">
                <a href="<?php echo esc_url(home_url('/my-account')); ?>" class="button">Go to My Account</a>
                <a href="<?php echo esc_url(home_url()); ?>" class="button secondary">Continue Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <h1>Oops!</h1>
            <p><?php echo esc_html($payment_result['message']); ?></p>

            <div class="action-buttons">
                <a href="<?php echo esc_url(home_url('/cart')); ?>" class="button">Return to Cart</a>
                <a href="<?php echo esc_url(home_url('/contact')); ?>" class="button secondary">Contact Support</a>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php
 require_once('/www/wwwroot/new-webstarter.codepixelz.tech/wp-load.php');

 function get_curreny_rates()
 {
    global $wpdb;
    $access_key = 'cur_live_RFDFd4STzeV5MnBBE3MFokvZmnaKEWpfAB1wT1iP';

    // Retrieve the saved currencies from the options table
    $saved_currencies = get_option('wstr_currency_codes'); // Assuming you store the currencies like ['USD', 'EUR', 'JPY']
    if ($saved_currencies) {
        // Build the symbols query for the API request
        $symbols = implode(',', $saved_currencies);

        $response = wp_remote_get('https://api.currencyapi.com/v3/latest?apikey=' . $access_key . '&currencies=' . $symbols);

        if (is_wp_error($response)) {
            // Handle the error
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['data'])) {
                // Prepare an array to store the exchange rates
                $currency_rates = [];

                // Loop through each currency and get the rate
                foreach ($saved_currencies as $currency) {
                    if (isset($data['data'][$currency])) {
                        $currency_rates[$currency] = $data['data'][$currency]['value'];
                    }
                }
           
                // Save the updated rates to the options table
                update_option('wstr_currency_rates', $currency_rates);

                send_currency_rates_email($currency_rates);

                echo 'Updated successfully';
            } else {
                echo 'Failed to retrieve currency data.';
            }
        }
    }
 }

 get_curreny_rates();


 /**
 * Function to send currency rates via email.
 */
function send_currency_rates_email($currency_rates) {
    // Set the email subject
    $subject = 'Updated Currency Rates';

    // Prepare the email content
    $message = "The latest currency rates are:\n\n";
    foreach ($currency_rates as $currency => $rate) {
        $message .= $currency . ': ' . $rate . "\n";
    }

    // Set the email recipient
    $to = 'dev@codepixelzmedia.com.np';  // Replace with the actual recipient's email address

    // Set email headers
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    // Send the email
    wp_mail($to, $subject, $message, $headers);
}

 
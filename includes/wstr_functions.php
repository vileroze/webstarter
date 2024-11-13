<?php

/**
 * function for returning price 
 * @param mixed $domain_id required
 * @return mixed
 */
function get_wstr_price($domain_id)
{
    if (!$domain_id) {
        return 0;
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $currency = $_SESSION['currency'] ?? '';
    $currency_rates = get_option('wstr_currency_rates', []);
    $currency_rate = $currency_rates[$currency] ?? 1;
    $regular_price = (float) get_post_meta($domain_id, '_regular_price', true);
    $sale_price = (float) get_post_meta($domain_id, '_sale_price', true);

    if ($currency && $currency != 'USD') {
        $currency_rate = wstr_truncate_number((float) $currency_rate);
        //  $currency_rate;
        $price = $sale_price > 0 ? $sale_price * $currency_rate : $regular_price * $currency_rate;
    } else {
        $currency = 'USD';
        $price = $sale_price > 0 ? $sale_price : $regular_price;
    }
    // $price_html = '<div class="wstr-price_html">
    // <span class="wstr-currency">' . get_wstr_currency_symbol($currency) . '</span>
    // <span class="wstr-price">' . wstr_truncate_number($price) . '<span> </div>';

    if ($sale_price) {
        $price_html = '<div class="ws_card_price_wrapper ws_flex gap_10"><p class="regular_price">' . get_wstr_currency() . '' . get_wstr_regular_price($domain_id) . '</p><p class="sale_price">' . get_wstr_currency() . '' . get_wstr_sale_price($domain_id) . '</p></div>';
    } else {
        $price_html = '<div class="single_domain_price ws_card_price_wrapper ws_flex gap_10"><p class="sale_price">' . get_wstr_currency() . '' . get_wstr_regular_price($domain_id) . '</p></div>';
    }
    return $price_html;
}

function get_wstr_price_value($domain_id)
{
    if (!$domain_id) {
        return 0;
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $currency = $_SESSION['currency'] ?? '';
    $currency_rates = get_option('wstr_currency_rates', []);
    $currency_rate = $currency_rates[$currency] ?? 1;
    $regular_price = (float) get_post_meta($domain_id, '_regular_price', true);
    $sale_price = (float) get_post_meta($domain_id, '_sale_price', true);

    if ($currency && $currency != 'USD') {
        $currency_rate = wstr_truncate_number((float) $currency_rate);
        //  $currency_rate;
        $price = $sale_price > 0 ? $sale_price * $currency_rate : $regular_price * $currency_rate;
    } else {
        $currency = 'USD';
        $price = $sale_price > 0 ? $sale_price : $regular_price;
    }
    // $price_html = '<div class="wstr-price_html">
    // <span class="wstr-currency">' . get_wstr_currency_symbol($currency) . '</span>
    // <span class="wstr-price">' . wstr_truncate_number($price) . '<span> </div>';
    return $price;
}

/**
 * Function for getting regular price of domain
 * @param mixed $domain_id
 * @return mixed
 */
function get_wstr_regular_price($domain_id)
{
    $currency = $_SESSION['currency'] ?? '';
    $regular_price = (float) get_post_meta($domain_id, '_regular_price', true);
    $currency_rates = get_option('wstr_currency_rates', []);
    $currency_rate = $currency_rates[$currency] ?? 1;
    if ($currency && $currency != 'USD') {
        $currency_rate = wstr_truncate_number((float) $currency_rate);

        // Calculate the prices in the specified currency
        $regular_price = $regular_price > 0 ? $regular_price * $currency_rate : 0;
    }
    return wstr_truncate_number($regular_price);
}

/**
 * Function for getting regular price of domain
 * @param mixed $domain_id
 * @return mixed
 */
function get_wstr_sale_price($domain_id)
{
    $currency = $_SESSION['currency'] ?? '';
    $sale_price = (float) get_post_meta($domain_id, '_sale_price', true);
    $currency_rates = get_option('wstr_currency_rates', []);
    $currency_rate = $currency_rates[$currency] ?? 1;
    if ($currency && $currency != 'USD') {
        $currency_rate = wstr_truncate_number((float) $currency_rate);

        // Calculate the prices in the specified currency
        $sale_price = $sale_price > 0 ? $sale_price * $currency_rate : 0;
    }
    return wstr_truncate_number($sale_price);
}

/**
 * Function for getting currecy value according to the currency selected
 * @return mixed
 */
function wstr_get_updated_price($price)
{
    $currency = $_SESSION['currency'] ?? '';
    $currency_rates = get_option('wstr_currency_rates', []);
    $currency_rate = $currency_rates[$currency] ?? 1;
    if ($currency && $currency != 'USD') {
        $currency_rate = wstr_truncate_number((float) $currency_rate);

        // Calculate the prices in the specified currency
        $price = $price > 0 ? $price * $currency_rate : 0;
    }
    return wstr_truncate_number($price);
}

/**
 * Function for percetage of price differnce
 * @param mixed $domain_id ID of the domain
 * @return mixed
 */
function get_wstr_price_percentage($domain_id)
{
    $regular_price = get_wstr_regular_price($domain_id);
    $sale_price = get_wstr_sale_price($domain_id);

    $percentage_discount = 0;

    if (!empty($regular_price) && !empty($sale_price) && $regular_price > $sale_price) {
        // Calculate the discount percentage
        $percentage_discount = (($regular_price - $sale_price) / $regular_price) * 100;
        $percentage_discount = round($percentage_discount); // Round to 2 decimal places for readability  
    }

    $output = ' <div class="ws_discount_percent">' . $percentage_discount . '%</div>';

    if ($percentage_discount > 0) {
        return $output;
    }
}

/**
 * Function for getting currency symbol
 * @return string
 */
function get_wstr_currency()
{

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $currency = $_SESSION['currency'] ? $_SESSION['currency'] : 'USD';
    return get_wstr_currency_symbol($currency);
}


/**
 * Fuction for getting currency symbol 
 * @param mixed $string ex: $string = 'USD'
 * @return string
 */
function get_wstr_currency_symbol($string, $for_api = false)
{
    // $locale = 'en-US'; //browser or user locale
    // $fmt = new NumberFormatter($locale . "@currency=$string", NumberFormatter::CURRENCY);
    // $symbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    // header("Content-Type: text/html; charset=UTF-8;");
    // return $symbol;

    $locale = 'en-US'; //browser or user locale
    $fmt = new NumberFormatter($locale . "@currency=$string", NumberFormatter::CURRENCY);
    $symbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

    if ($for_api) {
        // Convert Unicode character to escaped representation
        $escaped_symbol = json_encode($symbol);
        // Remove quotes around the symbol
        $escaped_symbol = substr($escaped_symbol, 1, -1);
        return $escaped_symbol;
    } else {
        header("Content-Type: text/html; charset=UTF-8;");
        return $symbol;
    }
}

/**
 * Fuction for check if product is on sale
 * @param mixed $domain_id 
 * @return bool
 */
function wstr_on_sale($domain_id)
{
    if ($domain_id) {
        $context = false;
        $sale_price = get_post_meta($domain_id, '_sale_price', true);
        if ($sale_price) {
            $context = true;
        }
    }
    return $context;
}


/**
 * Truncate a number to a specified number of decimal places without rounding.
 * 
 * This function will truncate a number to the given precision without rounding
 * and will handle both positive and negative numbers.
 *
 * @param float|int $number The number to be truncated.
 * @param int $precision The number of decimal places to keep. Default is 3.
 * @return float|int The truncated number.
 */
function wstr_truncate_number($number, $precision = 2)
{

    // Zero causes issues, and no need to truncate
    if (0 == (int) $number) {
        return $number;
    }

    // Determine if the number is negative
    $negative = $number < 0 ? -1 : 1;

    // Cast the number to positive to solve rounding
    $number = abs($number);

    // Calculate precision number for dividing / multiplying
    $precisionFactor = pow(10, $precision);

    // Run the math, re-applying the negative value to ensure
    // returns correctly negative / positive
    return floor($number * $precisionFactor) / $precisionFactor * $negative;
}

/**
 * Function to check if a term exists in a taxonomy for a specific domain
 * @param mixed $domain_id
 * @param mixed $taxonomy
 * @param mixed $term_slug
 * @return bool
 */
function wstr_check_existing_term($domain_id, $taxonomy, $term_slug)
{
    // Get terms associated with the post (domain_id)
    $terms = wp_get_post_terms($domain_id, $taxonomy);

    if (!is_wp_error($terms) && !empty($terms)) {
        $term_exists = false;

        // Loop through terms to see if the specific term exists
        foreach ($terms as $term) {
            if ($term->slug == $term_slug || $term->name == $term_slug || $term->term_id == $term_slug) {
                $term_exists = true;
                break;
            }
        }

        if ($term_exists) {
            // echo "The term '$term_slug' exists for post ID: $domain_id.";
            return true;
        } else {
            // echo "The term '$term_slug' does not exist for post ID: $domain_id.";
            return false;
        }
    } else {
        // echo "No terms found for post ID: $domain_id in the '$taxonomy' taxonomy.";
        return false;
    }
}

/**
 * Function for getting favourite count for specfic domain
 * @param mixed $domain_id
 * @return int|string
 */
function wstr_get_favourite_count($domain_id)
{
    $favourite_count = get_post_meta($domain_id, '_favourite_count', true);
    $favourite_count = (int) $favourite_count; // Ensure it's an integer

    // Check if the count is above 1000 and format it
    if ($favourite_count >= 1000) {
        // Format the number to display with "K" (rounded to 1 decimal place)
        $favourite_count = round($favourite_count / 1000, 1) . 'K';
    }

    return $favourite_count;
}
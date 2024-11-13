<?php

/**
 * Function for home page premium domains via REST API
 */
function wstr_premium_domains_api($request)
{

    $params = $request->get_params();
    if (isset($params['type']) && $params['type'] === 'premium') {
        $query_args = array(
            'posts_per_page' => 8,
            'post_type' => 'domain',
            'orderby' => 'rand',
            'order' => 'DESC',
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => '!='
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'domain_cat',
                    'field' => 'term_id',
                    'terms' => 57, // Example category ID
                ),
            ),
        );

        $premium_domains = get_posts($query_args);

        // Prepare data to return as JSON
        $premium_domains_data = array();

        if ($premium_domains) {
            foreach ($premium_domains as $premium_domain) {
                // Get the basic domain details
                $domain_title = get_the_title($premium_domain);
                $domain_permalink = get_permalink($premium_domain);
                $domain_image = get_the_post_thumbnail_url($premium_domain, 'medium_large');

                if (!$domain_image) {
                    $domain_image = get_stylesheet_directory_uri() . '/assets/images/alternate-domain.png';
                }

                $logo = get_post_meta($premium_domain, '_logo_image', true);
                $logo_url = wp_get_attachment_url($logo);

                $sale_price = get_post_meta($premium_domain, '_sale_price', true);
                $regular_price = get_post_meta($premium_domain, '_regular_price', true);
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                $currency = $_SESSION['currency'] ?? '';

                $regular_price = get_wstr_regular_price($premium_domain);
                $sale_price = get_wstr_sale_price($premium_domain);

                $percentage_discount = 0;

                if (!empty($regular_price) && !empty($sale_price) && $regular_price > $sale_price) {
                    // Calculate the discount percentage
                    $percentage_discount = (($regular_price - $sale_price) / $regular_price) * 100;
                    $percentage_discount = round($percentage_discount, 2); // Round to 2 decimal places for readability  
                }
                // Get the price using custom function (assuming it exists)
                $domain_price = get_wstr_price($premium_domain);
                $currency = get_wstr_currency();
                // Get DA / PA Ranking
                $da_pa = get_post_meta($premium_domain, '_da_pa', true);
                $da = $pa = '';
                if ($da_pa) {
                    $da_pa_split = explode('/', $da_pa);
                    $da = $da_pa_split[0];
                    $pa = $da_pa_split[1];
                }

                // Add to the response array
                $premium_domains_data[] = array(
                    'id' => $premium_domain,
                    'title' => $domain_title,
                    'permalink' => $domain_permalink,
                    'featured_image' => $domain_image,
                    'logo' => $logo_url,
                    'price' => $domain_price,
                    'da' => $da,
                    'pa' => $pa,
                    'currency' => $currency,
                    'sale_price' => $sale_price,
                    'regular_price' => $regular_price,
                    'precentage_discount' => $percentage_discount,
                );
            }
        }

        // Return the data in JSON format
        return new WP_REST_Response($premium_domains_data, 200);
    } else if (isset($params['type']) && $params['type'] === 'new') {
        $query_args = array(
            'posts_per_page' => 8,
            'post_type' => 'domain',
            'orderby' => 'rand', //rand
            'order' => 'DESC',
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => '!='
                )
            ),
        );

        $domains = get_posts($query_args);

        // Prepare data to return as JSON
        $domains_data = array();

        if ($domains) {
            foreach ($domains as $domain) {
                // Get the basic domain details
                $domain_title = get_the_title($domain);
                $domain_permalink = get_permalink($domain);
                $domain_image = get_the_post_thumbnail_url($domain, 'medium_large');

                if (!$domain_image) {
                    $domain_image = get_stylesheet_directory_uri() . '/assets/images/alternate-domain.png';
                }

                $logo = get_post_meta($domain, '_logo_image', true);
                $logo_url = wp_get_attachment_url($logo);


                $regular_price = get_wstr_regular_price($domain);
                $sale_price = get_wstr_sale_price($domain);

                $percentage_discount = 0;

                if (!empty($regular_price) && !empty($sale_price) && $regular_price > $sale_price) {
                    // Calculate the discount percentage
                    $percentage_discount = (($regular_price - $sale_price) / $regular_price) * 100;
                    $percentage_discount = round($percentage_discount, 2); // Round to 2 decimal places for readability  
                }
                // Get the price using custom function (assuming it exists)
                $domain_price = get_wstr_price($domain);
                $currency = get_wstr_currency();
                // Get DA / PA Ranking
                $da_pa = get_post_meta($domain, '_da_pa', true);
                $da = $pa = '';
                if ($da_pa) {
                    $da_pa_split = explode('/', $da_pa);
                    $da = $da_pa_split[0];
                    $pa = $da_pa_split[1];
                }

                $term_exist = wstr_check_existing_term($domain, 'domain_cat', 'premium-names');

                // Add to the response array
                $domains_data[] = array(
                    'id' => $domain,
                    'title' => $domain_title,
                    'permalink' => $domain_permalink,
                    'featured_image' => $domain_image,
                    'logo' => $logo_url,
                    'price' => $domain_price,
                    'da' => $da,
                    'pa' => $pa,
                    'currency' => $currency,
                    'sale_price' => $sale_price,
                    'regular_price' => $regular_price,
                    'precentage_discount' => $percentage_discount,
                    'term_exist' => $term_exist,
                );
            }
        }

        // Return the data in JSON format
        return new WP_REST_Response($domains_data, 200);
    } else if (isset($params['type']) && $params['type'] === 'recents') {

        $args = array(
            'post_type' => 'domain_order',     // Custom post type
            'post_status' => 'publish',        // Post status
            'posts_per_page' => -1,                 // Get all posts
            'orderby' => 'date',             // Order by date
            'order' => 'DESC',             // Descending order
            'meta_query' => array(              // Meta query for custom fields
                array(
                    'key' => '_order_status',   // Meta key
                    'value' => 'completed',       // Meta value
                    'compare' => '=',               // Comparison operator
                ),
            ),
        );
        $latest_solds = get_posts($args);
        $product_data = array();
        foreach ($latest_solds as $latest_sold) {
            $order_total = get_post_meta($latest_sold->ID, '_order_total', true);
            $update_total = wstr_get_updated_price($order_total);

            $domains = get_post_meta($latest_sold->ID, '_domain_ids', true);
            foreach ($domains as $domain_id) {

                $term =  get_the_terms($domain_id, 'domain_industry');
                $term_name = $term[0]->name;

                $da_pa = get_post_meta($domain_id, '_da_pa', true);
                $da = $pa = '';
                if ($da_pa) {
                    $da_pa_split = explode('/', $da_pa);
                    $da = $da_pa_split[0];
                    $pa = $da_pa_split[1];
                }
                $logo = get_post_meta($domain_id, '_logo_image', true);
                $logo_url = wp_get_attachment_url($logo);

                $product_thumbnail = get_the_post_thumbnail_url($domain_id, 'medium_large');
                if (!$product_thumbnail) {
                    $product_thumbnail = get_stylesheet_directory_uri() . '/assets/images/alternate-domain.png';
                }
                $currency = get_wstr_currency();
                $term_exist = wstr_check_existing_term($domain_id, 'domain_cat', 'premium-names');

                $product_data[] = array(
                    'id' => $domain_id,
                    'title' => get_the_title($domain_id),
                    'permalink' => get_permalink($domain_id),
                    'featured_image' => $product_thumbnail,
                    'logo' => $logo_url,
                    'da' => $da,
                    'pa' => $pa,
                    'term_name' => $term_name,
                    'term_exist' => $term_exist,
                );
            }
        }
        // Return the data in JSON format
        return new WP_REST_Response($product_data, 200);
    } else if (isset($params['type']) && $params['type'] === 'trending') {

        $query_args = array(
            'posts_per_page' => 20,                  // Get all posts
            'post_type' => 'domain',            // Custom post type
            'orderby' => 'meta_value_num',    // Order by numeric meta value
            'order' => 'DESC',              // Descending order
            'meta_key' => 'ws_product_view_count', // Meta key to order by
            'fields' => 'ids',               // Only return post IDs
            'meta_query' => array(               // Meta query conditions
                array(
                    'key' => '_stock_status',    // Meta key for stock status
                    'value' => 'instock',       // Exclude posts with 'outofstock' status
                    'compare' => '=',               // Not equal to 'outofstock'
                )
            ),
        );
        // Prepare data to return as JSON
        $domains = get_posts($query_args);
        $domains_data = array();

        if ($domains) {
            foreach ($domains as $domain) {
                // Get the basic domain details
                $domain_title = get_the_title($domain);
                $domain_permalink = get_permalink($domain);
                $domain_image = get_the_post_thumbnail_url($domain, 'medium_large');

                if (!$domain_image) {
                    $domain_image = get_stylesheet_directory_uri() . '/assets/images/alternate-domain.png';
                }

                $logo = get_post_meta($domain, '_logo_image', true);
                $logo_url = wp_get_attachment_url($logo);


                $regular_price = get_wstr_regular_price($domain);
                $sale_price = get_wstr_sale_price($domain);

                $percentage_discount = 0;

                if (!empty($regular_price) && !empty($sale_price) && $regular_price > $sale_price) {
                    // Calculate the discount percentage
                    $percentage_discount = (($regular_price - $sale_price) / $regular_price) * 100;
                    $percentage_discount = round($percentage_discount, 2); // Round to 2 decimal places for readability  
                }
                // Get the price using custom function (assuming it exists)
                $domain_price = get_wstr_price($domain);
                $currency = get_wstr_currency();
                // Get DA / PA Ranking
                $da_pa = get_post_meta($domain, '_da_pa', true);
                $da = $pa = '';
                if ($da_pa) {
                    $da_pa_split = explode('/', $da_pa);
                    $da = $da_pa_split[0];
                    $pa = $da_pa_split[1];
                }

                $term_exist = wstr_check_existing_term($domain, 'domain_cat', 'premium-names');

                // Add to the response array
                $domains_data[] = array(
                    'id' => $domain,
                    'title' => $domain_title,
                    'permalink' => $domain_permalink,
                    'featured_image' => $domain_image,
                    'logo' => $logo_url,
                    'price' => $domain_price,
                    'da' => $da,
                    'pa' => $pa,
                    'currency' => $currency,
                    'sale_price' => $sale_price,
                    'regular_price' => $regular_price,
                    'precentage_discount' => $percentage_discount,
                    'term_exist' => $term_exist,
                );
            }
        }
        // Return the data in JSON format
        return new WP_REST_Response($domains_data, 200);
    }
}

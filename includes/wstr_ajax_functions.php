<?php
class Wstr_ajax_functions
{
    function __construct()
    {
        add_action('wp_ajax_get_users', array($this, 'get_users'));
        add_action('wp_ajax_get_domains_list', array($this, 'get_domains_list'));
        add_action('wp_ajax_get_domain_details', array($this, 'get_domain_details'));
        add_action('wp_ajax_remove_domain_from_order', array($this, 'remove_domain_from_order'));
        add_action('wp_ajax_add_domain_order_notes', array($this, 'add_domain_order_notes'));
        add_action('wp_ajax_delete_domain_order_note', array($this, 'delete_domain_order_note'));

        add_action('wp_ajax_set_currency_session', array($this, 'set_currency_session'));
        add_action('wp_ajax_nopriv_set_currency_session', array($this, 'set_currency_session'));

        add_action('wp_ajax_wstr_favourite', array($this, 'wstr_favourite'));
    }

    /**
     * 
     * Function for getting user on select option lists in backend order page 
     * @return void
     */
    public function get_users()
    {
        if (isset($_POST['search'])) {
            $search_term = sanitize_text_field($_POST['search']);

            // Query for users
            $user_query = new WP_User_Query(array(
                'search' => '*' . esc_attr($search_term) . '*',
                'search_columns' => array('user_login', 'user_email', 'display_name'),
            ));

            $users = $user_query->get_results();

            // Prepare the response data
            $response = array();
            foreach ($users as $user) {
                $response[] = array(
                    'id' => $user->ID,
                    'username' => $user->user_login,
                    'email' => $user->user_email,
                );
            }

            // Send the response in JSON format
            wp_send_json($response);
        }
        wp_die();
    }

    /**
     * 
     * Function for getting domains on select option lists in backend order page
     * @return void
     */
    public function get_domains_list()
    {
        if (isset($_POST['search'])) {
            $search_term = sanitize_text_field($_POST['search']);

            // Query for domains
            $domain_query = new WP_Query(array(
                'post_type' => 'domain',
                's' => $search_term,
                'posts_per_page' => -1, // Adjust this if you want to limit the number of results
                'fields' => 'ids', // Only retrieve post IDs
            ));

            $domains = $domain_query->get_posts();

            // Prepare the response data
            $response = array();
            foreach ($domains as $domain_id) {
                $response[] = array(
                    'id' => $domain_id,
                    'name' => get_the_title($domain_id),
                );
            }

            // Send the response in JSON format
            wp_send_json($response);
        }
        wp_die();
    }

    public function get_domain_details()
    {
        if (isset($_POST['domain_id'])) {
            $domain_id = sanitize_text_field($_POST['domain_id']);
            $order_id = sanitize_text_field($_POST['order_id']);

            $domain_post = get_post($domain_id);
            if ($domain_post && $domain_post->post_type === 'domain') {

                if ($order_id) {
                    $saved_domains = get_post_meta($order_id, '_domain_ids', true);
                    $saved_domains = is_array($saved_domains) ? $saved_domains : array();

                    // Add the new domain ID to the array
                    if (!in_array($domain_id, $saved_domains)) {
                        $saved_domains[] = $domain_id;
                        update_post_meta($order_id, '_domain_ids', $saved_domains);

                        // Recalculate subtotal and total
                        $subtotal = 0;
                        foreach ($saved_domains as $domain) {
                            $domain_post = get_post($domain);
                            if ($domain_post && $domain_post->post_type === 'domain') {
                                $price = get_post_meta($domain_post->ID, '_sale_price', true);
                                if (!$price) {
                                    $price = get_post_meta($domain_post->ID, '_regular_price', true);
                                }
                                $subtotal += (float) $price;
                            }
                        }

                        // Optionally, calculate the total if different from subtotal
                        $total = $subtotal; // Adjust if you have additional calculations

                        // Update order meta with subtotal and total
                        update_post_meta($order_id, '_order_subtotal', $subtotal);
                        update_post_meta($order_id, '_order_total', $total);
                    }
                }
                $image_url = get_the_post_thumbnail_url($domain_post->ID, 'full');
                // Get the amount 
                $price = get_post_meta($domain_post->ID, '_sale_price', true);
                if (!$price) {
                    $price = get_post_meta($domain_post->ID, '_regular_price', true);
                }

                // Prepare the response data
                $response = array(
                    'id' => $domain_post->ID,
                    'name' => $domain_post->post_title,
                    'image' => $image_url ? $image_url : '', // Use empty string if no image
                    'amount' => $price ? $price : '0.00', // Use '0.00' if no amount is set
                    'order_id' => $order_id ? $order_id : '',
                    'subtotal' => number_format($subtotal, 2), // Add subtotal to response
                    'total' => number_format($total, 2), // Add total to response
                );

                // Send the response in JSON format
                wp_send_json($response);
            }
        }
        wp_die();
    }

    /**
     * Function for removing domains from order meta
     */
    public function remove_domain_from_order()
    {

        if (isset($_POST['domain_id'])) {
            $domain_id = sanitize_text_field($_POST['domain_id']);
            $order_id = sanitize_text_field($_POST['order_id']);

            // Get the current saved domains
            $saved_domains = get_post_meta($order_id, '_domain_ids', true);
            $saved_domains = is_array($saved_domains) ? $saved_domains : array();

            // // Check if the domain ID exists in the array and remove it
            if (($key = array_search($domain_id, $saved_domains)) !== false) {
                unset($saved_domains[$key]);

                // Re-index the array to ensure no gaps in the array keys
                $saved_domains = array_values($saved_domains);

                // Update the post meta with the new array
                update_post_meta($order_id, '_domain_ids', $saved_domains);

                // Recalculate subtotal and total
                $subtotal = 0;
                foreach ($saved_domains as $domain) {
                    $domain_post = get_post($domain);
                    if ($domain_post && $domain_post->post_type === 'domain') {
                        $price = get_post_meta($domain_post->ID, '_sale_price', true);
                        if (!$price) {
                            $price = get_post_meta($domain_post->ID, '_regular_price', true);
                        }
                        $subtotal += (float) $price;
                    }
                }

                $total = $subtotal; // Adjust if needed
                update_post_meta($order_id, '_order_subtotal', $subtotal);
                update_post_meta($order_id, '_order_total', $total);
            }

            wp_send_json_success(array(
                'id' => $domain_id,
                'subtotal' => number_format($subtotal, 2),
                'total' => number_format($total, 2),
                'message' => 'Domain removed successfully.',
            ));
        }
    }


    /**
     * Function for adding order notes to the order_notes table
     * @return never
     */
    public function add_domain_order_notes()
    {
        global $wpdb;
        $order_id = sanitize_text_field($_POST['order_id']);
        $order_note_type = sanitize_text_field($_POST['order_note_type']);
        $order_note = sanitize_text_field($_POST['order_note']);

        $table = $wpdb->prefix . 'order_notes';

        // Prepare the data for insertion
        $data = array(
            'order_id' => $order_id,
            'note' => $order_note,
            'note_type' => $order_note_type,
            'note_date' => current_time('mysql') // Current date and time
        );

        // Specify the data types for each field
        $format = array(
            '%d',   // order_id (integer)
            '%s',   // note (string)
            '%s',   // note_type (string)
            '%s'    // note_date (string, MySQL format)
        );

        // Insert data into the database
        $wpdb->insert($table, $data, $format);

        // Optionally, you can check if the insert was successful
        if ($wpdb->insert_id) {
            wp_send_json_success(array(
                'id' => $wpdb->insert_id,
                'note' => $order_note,
                'note_date' => date('F j, Y \a\t g:i a', strtotime(current_time('mysql')))
            ));
        } else {
            wp_send_json_error('Failed to add order note.');
        }
        die();
    }

    /**
     * Function for deleting order note from custom table -> order_notes
     * @return never
     */
    public function delete_domain_order_note()
    {
        global $wpdb;

        $note_id = intval($_POST['note_id']);
        $table = $wpdb->prefix . 'order_notes';

        $deleted = $wpdb->delete($table, array('id' => $note_id), array('%d'));
        if ($deleted) {
            wp_send_json_success('Note deleted successfully.');
        } else {
            wp_send_json_error('Failed to delete note.');
        }

        die();
    }

    /**
     * Function for add currency value to the session
     */

    public function set_currency_session()
    {
        $currency = sanitize_text_field($_POST['currency']);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['currency'] = $currency;

        // Return the session value in response
        $get_session_value = isset($_SESSION['currency']) ? $_SESSION['currency'] : '';
        wp_send_json_success($get_session_value);
        wp_die();
    }

    /**
     *  Function for favourite section
     */
    public function wstr_favourite()
    {
        $domain_id = sanitize_text_field($_POST['domain_id']);

        $favourite_data = get_user_meta(get_current_user_id(), '_favourite', true);

        // Ensure $favourite_data is an array
        if (!is_array($favourite_data)) {
            $favourite_data = [];
        }

        // Get the current favorite count from post meta
        $favourite_count = get_post_meta($domain_id, '_favourite_count', true);
        $favourite_count = (int) $favourite_count; // Ensure it's an integer

        // Check if the domain ID already exists
        if (($key = array_search($domain_id, $favourite_data)) !== false) {
            // Remove domain ID if it exists
            unset($favourite_data[$key]);

            // Decrease the favorite count if the domain is removed
            $favourite_count = max(0, $favourite_count - 1); // Prevent negative count
            $count = 'deduct';
        } else {
            // Add domain ID if it doesn't exist
            $favourite_data[] = $domain_id;

            // Increase the favorite count if the domain is added
            $favourite_count++;
            $count = 'add';
        }
        // Update the favorite count in post meta
        update_post_meta($domain_id, '_favourite_count', $favourite_count);
        update_user_meta(get_current_user_id(), '_favourite', $favourite_data);
        wp_send_json_success(array(
            'count' => $count,
        ));
        wp_die();
    }
}

new Wstr_ajax_functions();

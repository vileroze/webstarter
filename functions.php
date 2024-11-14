<?php

/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package webstarter
 * @since 1.0.0
 */


/**
 * Enqueue the CSS files.
 *
 * @since 1.0.0
 *
 * @return void
 */

add_action('admin_enqueue_scripts', 'wstr_enqueue_admin_scripts');
function wstr_enqueue_admin_scripts()
{
    // Enqueue admin CSS
    wp_enqueue_style('wstr-admin-css', get_template_directory_uri() . '/assets/admin/css/wstr_style.css', array(), true, 'all');

    // Enqueue admin JS
    wp_enqueue_script('wstr-admin-js', get_template_directory_uri() . '/assets/admin/js/wstr_script.js', array('jquery'), time(), true);



    // localize ajax
    wp_localize_script('wstr-admin-js', 'cpmAjax', array('ajax_url' => admin_url('admin-ajax.php')));

    wp_enqueue_script('wstr-js', get_template_directory_uri() . '/script.js', array('jquery'), time(), true);

    if (function_exists('wp_enqueue_media')) {
        wp_enqueue_media();
    }

    // select 2 js  
    wp_enqueue_style('wstr-select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), true, 'all');

    wp_enqueue_script('wstr-select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), time(), true);

    // font awesome
    wp_enqueue_style('wstr-font-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css', array(), true, 'all');
    wp_enqueue_script('wstr-font-js', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js', array('jquery'), time(), true);
}

add_action('wp_enqueue_scripts', 'wstr_enqueue_scripts');
function wstr_enqueue_scripts()
{
    // Enqueue public CSS
    wp_enqueue_style('wstr-public-css', get_template_directory_uri() . '/assets/public/css/wstr_style.css', array(), true, 'all');

    wp_enqueue_style('wstr-public-mobile-css', get_template_directory_uri() . '/assets/public/css/wstr_style_mobile.css', array(), true, 'all');

    wp_enqueue_style('wstr-public-card-block-css', get_template_directory_uri() . '/assets/public/css/wstr_card_block_style.css', array(), true, 'all');

    // Enqueue public JS
    wp_enqueue_script('wstr-public-js', get_template_directory_uri() . '/assets/public/js/wstr_script.js', array('jquery'), time(), true);

    wp_enqueue_script('wstr-js', get_template_directory_uri() . '/script.js', array('jquery'), time(), true);

    //Localize ajax
    wp_localize_script('wstr-public-js', 'cpmAjax', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('cart_nonce')));

    // select 2 js  
    wp_enqueue_style('wstr-select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), true, 'all');

    wp_enqueue_script('wstr-select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), time(), true);

    // Enqueue public media
    if (function_exists('wp_enqueue_media')) {
        wp_enqueue_media();
    }

    // // react app 
    // wp_enqueue_script(
    //     'my-react-app',
    //     WP_PLUGIN_DIR.'/react-plugin/my-account/build/static/main.1d965476.js', // Path to your React build JS file
    //     ['wp-element'], // This depends on the WordPress `wp-element` library
    //     filemtime(plugin_dir_path(__FILE__) . 'build/index.js'), // Cache busting
    //     true
    // );
    // wp_enqueue_style(
    //     'my-react-app-style',
    //     WP_PLUGIN_DIR.'/react-plugin/my-account/build/static/main.f855e6bc.css', // Path to your React build CSS file (if any)
    //     [],
    //     filemtime(plugin_dir_path(__FILE__) . 'build/index.css')
    // );

    wp_enqueue_style('react-style', get_template_directory_uri() . '/js/static/css/main.e44d8d4b.css', array(), true, 'all');

    // Enqueue public JS
    wp_enqueue_script('react-script', get_template_directory_uri() . '/js/static/js/main.123c64bb.js', array('jquery'), time(), true);
}

include(get_stylesheet_directory() . '/includes/wstr_post_type.php');
include(get_stylesheet_directory() . '/includes/wstr_post_meta_boxes.php');
include(get_stylesheet_directory() . '/includes/wstr_api.php');
include(get_stylesheet_directory() . '/includes/wstr_ajax_functions.php');
include(get_stylesheet_directory() . '/includes/wstr_shortcodes.php');
include(get_stylesheet_directory() . '/includes/wstr_filters_hooks.php');
include(get_stylesheet_directory() . '/includes/wstr_functions.php');
include(get_stylesheet_directory() . '/includes/wstr_admin_menu.php');


// font awesome
function enqueue_font_awesome()
{
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome');

// megamenu block

function enqueue_webstarter_mega_menu_assets()
{
    wp_enqueue_script(
        'mega-menu-block-editor',
        get_template_directory_uri() . '/blocks/mega-menu/index.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-block-editor'),
        filemtime(get_template_directory() . '/blocks/mega-menu/index.js'),
        true
    );

    wp_enqueue_style(
        'mega-menu-block-style',
        get_template_directory_uri() . '/blocks/mega-menu/style.css',
        array(),
        filemtime(get_template_directory() . '/blocks/mega-menu/style.css')
    );

    wp_enqueue_style(
        'mega-menu-block-editor-style',
        get_template_directory_uri() . '/blocks/mega-menu/editor.css',
        array(),
        filemtime(get_template_directory() . '/blocks/mega-menu/editor.css')
    );
}
add_action('enqueue_block_assets', 'enqueue_webstarter_mega_menu_assets');


/**
 * 
 * Ceating an custom table for order notes
 * @return void
 */
function create_order_notes_table_on_theme_activation()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'order_notes';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            note TEXT NOT NULL,
            note_type TEXT NOT NULL,
            note_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (order_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
add_action('after_setup_theme', 'create_order_notes_table_on_theme_activation');


// add_action('wp_footer', function () {


//     $user_details = get_user_by('id',$GLOBALS['user_id']);
//     echo '<pre>';
//     // var_dump($user_details);
//     // var_dump($user_details->data->ID);

//     $data[] = [
//         'id' => $user_details->data->ID ? $user_details->data->ID : '',
//         'user_login' => $user_details->data->user_login ? $user_details->data->user_login : '',
//         'user_email' => $user_details->data->user_email ? $user_details->data->user_email : '',
//         'cap_key' =>$user_details->caps ? $user_details->caps : '',
//         'roles' => $user_details->roles ? $user_details->roles : '',
//     ];
//     // var_dump($data);
//     // $text = 'hello.com';
//     // $apiKey = "sk_6f97cb3d8e487984ffa46daebf483dab8815a02ba7204d8b"; // Replace with your

//     // // The API key for authentication
//     // // $XI_API_KEY = "<xi-api-key>";

//     // // The URL of the API endpoint
//     // $url = "https://api.elevenlabs.io/v1/voices";

//     // // Set up headers for the HTTP request
//     // $headers = [
//     //     "Accept: application/json",
//     //     "xi-api-key: $XI_API_KEY",
//     //     "Content-Type: application/json"
//     // ];

//     // // Initialize cURL session
//     // $ch = curl_init($url);

//     // // Set cURL options
//     // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
//     // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Set the headers for the request

//     // // Execute the GET request
//     // $response = curl_exec($ch);

//     // // Check if there was an error with the request
//     // if (curl_errno($ch)) {
//     //     echo 'Request Error:' . curl_error($ch);
//     // } else {
//     //     // Parse the JSON response into a PHP associative array
//     //     $data = json_decode($response, true);

//     //     // Loop through the 'voices' array and print 'name' and 'voice_id'
//     //     foreach ($data['voices'] as $voice) {
//     //         echo $voice['name'] . "; " . $voice['voice_id'] . "\n";
//     //     }
//     // }

//     // // Close the cURL session
//     // curl_close($ch);

//     // $curl = curl_init();

//     //    $request_payload = [
//     //     "text" => $text,
//     //     "voice_settings" => [
//     //         "similarity_boost" => 0.5,
//     //         "stability" => 0.5,
//     //         "style" => 0.5,
//     //         "use_speaker_boost" => true
//     //     ]
//     // ];

//     // curl_setopt_array($curl, [
//     //     CURLOPT_URL => "https://api.elevenlabs.io/v1/text-to-speech/onwK4e9ZLuTAKqWW03F9",
//     //     CURLOPT_RETURNTRANSFER => true,
//     //     CURLOPT_ENCODING => "",
//     //     CURLOPT_MAXREDIRS => 10,
//     //     CURLOPT_TIMEOUT => 30,
//     //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//     //     CURLOPT_CUSTOMREQUEST => "POST",
//     //       CURLOPT_POSTFIELDS => json_encode($request_payload),
//     //     CURLOPT_HTTPHEADER => [
//     //         "Content-Type: application/json",
//     //         "xi-api-key: " . $apiKey,
//     //     ],
//     // ]);

//     // $response = curl_exec($curl);
//     // $err = curl_error($curl);

//     // curl_close($curl);

//     // if ($err) {
//     //     echo "cURL Error #:" . $err;
//     // } else {
//     //     echo $response;
//     // }


//     // $text = 'hello.com';
//     // $api_url = "https://api.elevenlabs.io/v1/text-to-speech/onwK4e9ZLuTAKqWW03F9"; // Adjust the output format as needed
//     // $request_payload = [
//     //     "text" => $text,
//     //     "voice_settings" => [
//     //         "similarity_boost" => 0.5,
//     //         "stability" => 0.5,
//     //         "style" => 0.5,
//     //         "use_speaker_boost" => true
//     //     ]
//     // ];

//     // $apiKey = "sk_eedc67f1e1f786064f584e497acbe18c37cdb860905ca325"; // Replace with your actual API key

//     // $curl = curl_init();

//     // curl_setopt_array($curl, [
//     //     CURLOPT_URL => $api_url,
//     //     CURLOPT_RETURNTRANSFER => true,
//     //     CURLOPT_ENCODING => "",
//     //     CURLOPT_MAXREDIRS => 10,
//     //     CURLOPT_TIMEOUT => 30,
//     //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//     //     CURLOPT_CUSTOMREQUEST => "POST",
//     //     CURLOPT_POSTFIELDS => json_encode($request_payload),
//     //     CURLOPT_HTTPHEADER => [
//     //         "Content-Type: application/json",
//     //         "xi-api-key: " . $apiKey,
//     //     ],
//     // ]);

//     // $response = curl_exec($curl);
//     // $err = curl_error($curl);

//     // curl_close($curl);

//     // // if ($err) {
//     // //     return "Error #:" . $err;
//     // // } else {
//     // //     // var_dump($response);
//     // //     return $response;
//     // // }
//     // var_dump($response);
//     // // Define file name and path
//     // $upload_dir = wp_upload_dir();
//     // $file_name = $text . '.wav';
//     // $file_path = $upload_dir['path'] . '/' . $file_name;
//     // $file_url = $upload_dir['url'] . '/' . $file_name;

//     // // Save audio data to the file
//     // file_put_contents($file_path, $response);

//     // // Display HTML audio player with file path
//     // $audio_player = '<audio controls>';
//     // $audio_player .= '<source src="' . $file_url . '" type="audio/wav">';
//     // $audio_player .= 'Your browser does not support the audio tag.';
//     // $audio_player .= '</audio>';

//     // // var_dump($audio_player);
//     // echo $audio_player;
// });



// register shortcode
add_shortcode('wstr_register', 'wstr_register');
function wstr_register()
{
    ob_start();
?>
    <form action="#" method="POST" class="wstr_signup">
        <label for="username">Username*</label>
        <input type="text" id="username" name="username" placeholder="Your Username" required>

        <label for="full-name">First Name, Last Name</label>
        <input type="text" id="full-name" name="full_name" placeholder="Enter first and last name" required>

        <label for="email">Email Address*</label>
        <input type="email" id="email" name="email" placeholder="@Email address " required>

        <label for="password">Password*</label>
        <input type="password" id="password" name="password" placeholder="Password" required>

        <label for="confirm-password">Confirm Password*</label>
        <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password " required>

        <div class="checkbox-group">
            <input type="checkbox" id="become-seller" name="become_seller">
            <label for="become-seller">Become a Seller</label>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">I have read and accepted the <a href="#">terms and conditions</a></label>
        </div>

        <button type="submit">Register</button>

        <div class="login-link">
            <p>Already registered? <a href="#">Login</a></p>
        </div>
    </form>
<?php
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

add_shortcode('dashboard', 'dashboard');
function dashboard()
{
    ob_start();
    if (!is_user_logged_in()) {
        return;
    }
?>
    <div id="root"></div>
<?php
    return ob_get_clean();
}





























//=============================
//=======STRIPE PAYMENT========
//=============================

//add stirpe scirpt to wp_head
add_action('wp_head', 'wstr_stripe_script', 99);
function wstr_stripe_script()
{
    echo '<script src="https://js.stripe.com/v3/"></script>';
    echo '<script src="' . get_template_directory_uri() . '/stripe-payment/stripe-checkout.js" defer></script>';
}



//=============================
//=======ADD TO CART===========
//=============================

add_action('init', 'start_session', 1);
function start_session()
{
    if (!session_id()) {
        session_start();
    }
}


add_action('wp_ajax_wstr_retrieve_cart_items', 'wstr_retrieve_cart_items');
add_action('wp_ajax_nopriv_wstr_retrieve_cart_items', 'wstr_retrieve_cart_items');
function wstr_retrieve_cart_items()
{
    $is_ajax_call = defined('DOING_AJAX') && DOING_AJAX;
    $cart_arr = [];
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => [$payment_option, $installment_duration]) {
            $product = get_post($product_id);
            array_push(
                $cart_arr,
                [
                    'id' => $product_id,
                    'image' => get_the_post_thumbnail_url($product_id),
                    'title' => $product->post_title,
                    'price' =>  get_wstr_price_value($product->ID),
                    'currency' => get_wstr_currency(),
                    'payment_option' => $payment_option,
                    'installment_duration' => $installment_duration,
                    'installment_price' => get_wstr_price_value($product->ID) / (int)$installment_duration
                ]
            );
        }
    }

    if ($is_ajax_call) {
        wp_send_json_success($cart_arr);
        wp_die();
        return;
    }

    return $cart_arr;
}


add_action('wp_ajax_wstr_add_item_to_cart', 'wstr_add_item_to_cart');
add_action('wp_ajax_nopriv_wstr_add_item_to_cart', 'wstr_add_item_to_cart');
function wstr_add_item_to_cart()
{

    // Check for nonce security      
    if (! wp_verify_nonce($_POST['nonce'], 'cart_nonce')) {
        wp_send_json_success(['busted']);
        wp_die();
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    foreach ($_SESSION['cart'] as $product_id => [$payment_option, $installment_duration]) {
        if ($payment_option == 'installment') {
            wp_send_json_success(wstr_retrieve_cart_items());
            wp_die();
        }
    }

    $product_id = intval($_POST['product_id']);
    $payment_option = sanitize_text_field($_POST['payment_option']);
    $payment_duration = sanitize_text_field($_POST['installment_duration']);

    $all_prodcut_ids = array_keys($_SESSION['cart']);
    if (!in_array($product_id, $all_prodcut_ids)) {
        $_SESSION['cart'] += [$product_id => [$payment_option, $payment_duration]];
    }

    wp_send_json_success(wstr_retrieve_cart_items());
    wp_die();
}


add_action('wp_ajax_wstr_remove_item_from_cart', 'wstr_remove_item_from_cart');
add_action('wp_ajax_nopriv_wstr_remove_item_from_cart', 'wstr_remove_item_from_cart');
function wstr_remove_item_from_cart()
{
    // Check for nonce security      
    if (! wp_verify_nonce($_POST['nonce'], 'cart_nonce')) {
        wp_send_json_success(['busted']);
        wp_die();
    }

    $product_id = intval($_POST['product_id']);
    if (isset($_SESSION['cart']) && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        // $_SESSION['cart'] = array_diff($_SESSION['cart'], [$product_id]);
    }
    wp_send_json_success(wstr_retrieve_cart_items());
    wp_die();
}


add_action('wp_logout', 'save_cart_to_user_meta');
function save_cart_to_user_meta()
{
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        update_user_meta($user_id, 'user_cart', $_SESSION['cart']);
    }
}

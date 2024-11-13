<?php

/**
 * Classes used for creating meta boxes to the custom post type.
 */

/**
 * For creating meta boxes to the domain post type
 */
class wstr_domain_meta_boxes
{
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_domain_meta_boxes'));
        add_action('save_post', array($this, 'save_domain_meta_boxes'));
    }

    public function add_domain_meta_boxes()
    {

        add_meta_box(
            'highlight_section',
            __('Highlight Section', 'webstarter'),
            array($this, 'render_highlight_section_meta_box'),
            'domain', // The custom post type slug
            'normal',
            'default'
        );

        add_meta_box(
            'domain_fields',
            __('Domain Fields', 'webstarter'),
            array($this, 'render_domain_fields_meta_box'),
            'domain', // The custom post type slug
            'normal',
            'default'
        );

        add_meta_box(
            'media_section',
            __('Media Section', 'webstarter'),
            array($this, 'render_media_section_meta_box'),
            'domain', // The custom post type slug
            'normal',
            'default'
        );

        add_meta_box(
            'domain_data',
            __('Domain Data', 'webstarter'),
            array($this, 'render_domain_data_fields_meta_box'),
            'domain', // The custom post type slug
            'side',
            'core'
        );

        add_meta_box(
            'what_you_get_meta_box',  // Unique ID for the meta box
            'What you get section.',           // Meta box title
            array($this, 'render_what_you_get_meta_box'),  // Callback function
            'domain', // The custom post type slug
            'normal',
            'default'                      // Priority (high, low)
        );
    }
    public function render_highlight_section_meta_box($post)
    {
        $highlight_title = get_post_meta($post->ID, '_highlight_title', true);
        $highlight_content = get_post_meta($post->ID, '_highlight_content', true);
?>
        <div class="domainHighlightTitle">
            <label><?php _e('Highlight Title'); ?></label>
            <input type="text" name="highlight_title" class="widefat" value="<?php echo esc_attr($highlight_title); ?>">
        </div>
        <div class="domainHighlightContent">
            <label><?php _e('Highlight Content'); ?></label>
            <textarea name="highlight_content" class="widefat" rows="4"><?php echo esc_textarea($highlight_content); ?></textarea>
        </div>
    <?php
    }
    public function render_media_section_meta_box($post)
    {
        $pronounce_audio_id = get_post_meta($post->ID, '_pronounce_audio', true);
        $pronounce_audio_url = wp_get_attachment_url($pronounce_audio_id);

        $logo_image_id = get_post_meta($post->ID, '_logo_image', true);
        $logo_image_url = wp_get_attachment_url($logo_image_id);
    ?>
        <div class="domainPronounce">
            <label><?php _e('How to Pronounce'); ?></label>
            <input type="hidden" id="pronounce_audio_url" name="pronounce_audio" value="<?php echo esc_attr($pronounce_audio_id); ?>">
            <button type="button" class="button" id="upload_pronounce_audio"><?php _e('Add File'); ?></button>
            <button type="button" class="button remove-button" id="remove_pronounce_audio"><?php _e('Remove File'); ?></button>
            <p class="description"><?php echo $pronounce_audio_url ? '<audio controls src="' . esc_url($pronounce_audio_url) . '"></audio>' : __('No file selected'); ?></p>
        </div>

        <div class="domainLogo">
            <label><?php _e('Logo/Document'); ?></label>
            <input type="hidden" id="logo_image_url" name="logo_image" value="<?php echo esc_attr($logo_image_id); ?>">
            <button type="button" class="button" id="upload_logo_image"><?php _e('Add Image'); ?></button>
            <button type="button" class="button remove-button" id="remove_logo_image"><?php _e('Remove Image'); ?></button>
            <p class="description"><?php echo $logo_image_url ? '<img src="' . esc_url($logo_image_url) . '" style="max-width: 150px; height: auto;" />' : __('No image selected'); ?></p>
        </div>

    <?php
    }
    public function render_domain_fields_meta_box($post)
    {
        // Add nonce for security and authentication
        wp_nonce_field('domain_fields_nonce_action', 'domain_fields_nonce');

        // Retrieve existing value from the database if available
        $age = get_post_meta($post->ID, '_age', true);
        $length = get_post_meta($post->ID, '_length', true);
        $da_pa = get_post_meta($post->ID, '_da_pa', true);
        $seo_rating = get_post_meta($post->ID, '_seo_rating', true);

        // Retrieve TLD selection if available
        $domain_tld = get_post_meta($post->ID, '_tld', true);
    ?>
        <div class="domainFields widefat">
            <div class="domainAge">
                <label><?php _e('Age'); ?></label>
                <input type="text" name="age" id="domainAge" class="widefat" value="<?php echo esc_attr($age); ?>">
            </div>
            <div class="domainLength">
                <label class="wstr-mandatory"><?php _e('Length'); ?></label>
                <input type="number" name="length" id="domainLength" class="widefat" value="<?php echo esc_attr($length); ?>">
            </div>
            <div id="domainTld">
                <label><?php _e('TLD'); ?></label>
                <select name="tld" class="widefat">
                    <option value=".com" <?php selected($domain_tld, '.com'); ?>><?php _e('.com'); ?></option>
                    <option value=".net" <?php selected($domain_tld, '.net'); ?>><?php _e('.net'); ?></option>
                    <option value=".org" <?php selected($domain_tld, '.org'); ?>><?php _e('.org'); ?></option>
                    <option value=".io" <?php selected($domain_tld, '.io'); ?>><?php _e('.io'); ?></option>
                    <option value=".ai" <?php selected($domain_tld, '.ai'); ?>><?php _e('.ai'); ?></option>
                    <option value=".dev" <?php selected($domain_tld, '.dev'); ?>><?php _e('.dev'); ?></option>
                    <option value=".pics" <?php selected($domain_tld, '.pics'); ?>><?php _e('.pics'); ?></option>
                    <option value=".life" <?php selected($domain_tld, '.life'); ?>><?php _e('.life'); ?></option>
                </select>
            </div>
            <div class="domainDaPa">
                <label><?php _e('DA / PA Ranking (optional)'); ?></label>
                <input type="text" name="da_pa" class="widefat" id="domainDaPa" value="<?php echo esc_attr($da_pa); ?>">
            </div>
            <div class="domainSeo">
                <label><?php _e('SEO Rating (optional)'); ?></label>
                <input type="number" name="seo_rating" min="1" max="5" class="widefat" value="<?php echo esc_attr($seo_rating); ?>">
                <div class="wstr-error-msg"></div>
            </div>


        </div>
    <?php
    }

    public function render_domain_data_fields_meta_box($post)
    {
        wp_nonce_field('domain_fields_nonce_action', 'domain_fields_nonce');
        // Retrieve existing values from the database if available
        $regular_price = get_post_meta($post->ID, '_regular_price', true);
        $sale_price = get_post_meta($post->ID, '_sale_price', true);
        $stock_status = get_post_meta($post->ID, '_stock_status', true);
        $enable_offers = get_post_meta($post->ID, '_enable_offers', true);

    ?>
        <div class="domainDataFields">
            <div class="domainPrice">
                <div class="domainRegularPrice">
                    <div class="wstr-error-msg"></div>
                    <label><?php _e('Regular price ($)', 'webstarter'); ?></label>
                    <input type="text" name="regular_price" class="widefat" value="<?php echo esc_attr($regular_price); ?>">
                </div>
                <div class="domainSalePrice">
                    <label><?php _e('Sale price ($)', 'webstarter'); ?></label>
                    <input type="text" name="sale_price" class="widefat" value="<?php echo esc_attr($sale_price); ?>">
                </div>
            </div>
            <div class="domainStatus">
                <label><?php _e('Stock status', 'webstarter'); ?></label>
                <select name="stock_status" class="widefat">
                    <option value="instock" <?php selected($stock_status, 'instock'); ?>><?php _e('In Stock', 'webstarter'); ?></option>
                    <option value="outofstock" <?php selected($stock_status, 'outofstock'); ?>><?php _e('Out of Stock', 'webstarter'); ?></option>
                </select>
            </div>
            <div class="domainOffers">
                <label><?php _e('Enable Offers', 'webstarter'); ?></label>
                <input type="checkbox" name="enable_offers" class="widefat" <?php checked($enable_offers, 'yes'); ?>>
                <span class="wstr-help-tip" tabindex="0" aria-label="Enable this option to enable the 'Make Offer' buttons and form display in the shop."></span>
            </div>
        </div>
    <?php
    }

    public function render_what_you_get_meta_box($post)
    {
    ?>
    <?php
    }

    public function save_domain_meta_boxes($post_id)
    {
        // Verify nonce
        if (!isset($_POST['domain_fields_nonce']) || !wp_verify_nonce($_POST['domain_fields_nonce'], 'domain_fields_nonce_action')) {
            return;
        }

        // Check if this is an autosave and return if it is
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Retrieve fields
        $regular_price = isset($_POST['regular_price']) ? sanitize_text_field($_POST['regular_price']) : '';
        $sale_price = isset($_POST['sale_price']) ? sanitize_text_field($_POST['sale_price']) : '';
        $seo_rating = isset($_POST['seo_rating']) ? sanitize_text_field($_POST['seo_rating']) : '';

        if (!$regular_price) {
            $sale_price = '';
        }
        // Validate sale price is not greater than regular price
        if (!empty($regular_price) && !empty($sale_price) && floatval($sale_price) > floatval($regular_price)) {
            // Set an error message
            $sale_price = '';
            // Redirect to avoid resubmission on refresh
        }

        // Save/Update custom fields
        $fields = [
            'highlight_title',
            'highlight_content',
            'age',
            'length',
            'da_pa',
            'seo_rating',
            'pronounce_audio',
            'logo_image',
            'regular_price',
            // 'sale_price',
            'stock_status',
            'enable_offers',
            'tld' // Added TLD field
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                // Handle sanitization based on field type
                if ($field === 'enable_offers') {
                    $value = isset($_POST[$field]) ? 'yes' : 'no';
                } elseif ($field === 'logo_image') {
                    $value = intval($_POST[$field]);
                } else {
                    $value = sanitize_text_field($_POST[$field]);
                }
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
        update_post_meta($post_id, '_sale_price', $sale_price);
    }
}

new wstr_domain_meta_boxes();

/**
 * For adding meta field to the post type domain order
 */
class wstr_domain_order_meta_boxes
{

    function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_domain_order_meta_boxes'));
        add_action('save_post', array($this, 'save_domain_order_meta_box'));

        add_action('add_meta_boxes', array($this, 'add_domain_meta_box'));
        add_action('add_meta_boxes', array($this, 'add_order_notes_meta_box'));
        add_action('add_meta_boxes', array($this, 'add_payment_info_meta_box'));
        add_action('add_meta_boxes', array($this, 'add_billing_info_meta_box'));
        add_action('add_meta_boxes', array($this, 'add_shipping_info_meta_box'));
    }
    public function add_domain_order_meta_boxes()
    {
        add_meta_box(
            'domain_order_details',
            __('Domain Order Details', 'webstarter'),
            array($this, 'render_domain_order_meta_box'),
            'domain_order',
            'normal',
            'high'
        );
    }

    function add_domain_meta_box()
    {
        add_meta_box(
            'add_domain_meta_box',       // Unique ID
            'Add Domain',          // Box title
            array($this, 'render_domain_meta_box'), // Content callback
            'domain_order',             // Post type
            'normal',                   // Context (side, normal, advanced)
            'default'                 // Priority (default, low, high)
        );
    }
    function add_shipping_info_meta_box()
    {
        add_meta_box(
            'add_shipping_info_meta_box',       // Unique ID
            'Add Shipping Information',          // Box title
            array($this, 'render_shipping_info_meta_box'), // Content callback
            'domain_order',             // Post type
            'normal',                   // Context (side, normal, advanced)
            'default'                 // Priority (default, low, high)
        );
    }
    function add_billing_info_meta_box()
    {
        add_meta_box(
            'add_billing_info_meta_box',       // Unique ID
            'Add Billing Information',          // Box title
            array($this, 'render_billing_info_meta_box'), // Content callback
            'domain_order',             // Post type
            'normal',                   // Context (side, normal, advanced)
            'default'                 // Priority (default, low, high)
        );
    }

    function add_payment_info_meta_box()
    {
        add_meta_box(
            'add_payment_info_meta_box',       // Unique ID
            'Add Payment Details',          // Box title
            array($this, 'render_payment_info_meta_box'), // Content callback
            'domain_order',             // Post type
            'normal',                   // Context (side, normal, advanced)
            'default'                 // Priority (default, low, high)
        );
    }
    // meta box for order notes
    function add_order_notes_meta_box()
    {
        add_meta_box(
            'order_notes_meta_box',  // Unique ID for the meta box
            'Order Notes',           // Meta box title
            array($this, 'render_order_notes_meta_box'),  // Callback function
            'domain_order',                 // Post type where the meta box will appear
            'side',                // Context (normal, side, advanced)
            'high'                   // Priority (high, low)
        );
    }


    /**
     *  For adding meta box
     * @param mixed $post
     * @return void
     */
    public function render_domain_order_meta_box($post)
    {
        // Add nonce for security and authentication.
        wp_nonce_field('domain_order_nonce_action', 'domain_order_nonce');

        // Retrieve current data if exists
        $customer_id = get_post_meta($post->ID, '_customer', true);
        $order_status = get_post_meta($post->ID, '_order_status', true);
        $date_created = get_post_meta($post->ID, '_date_created', true);
        $transfer_to = get_post_meta($post->ID, '_transfer_to', true);

        // HTML for meta box
    ?>
        <div class="orderGeneralInfo">
            <h4><?php _e(' General', 'webstarter'); ?></h4>
            <p>
                <label for="date_created"><?php _e('Date Created', 'webstarter'); ?></label>
                <input type="datetime-local" id="date_created" name="date_created" value="<?php echo esc_attr($date_created); ?>" class="widefat">
            </p>

            <?php
            $users = get_users(array(
                'orderby' => 'id',
                'order' => 'ASC'
            ));

            // Get the saved customer ID from post meta
            ?>

            <p>
                <label for="customerName"><?php _e('Customer Name', 'webstarter'); ?></label>
                <select name="customer" id="customerName" class="widefat">
                    <option></option> <!-- Default empty option -->
                    <?php foreach ($users as $user) : ?>
                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($user->ID, $customer_id); ?>>
                            <?php echo esc_html($user->user_login . ' (' . $user->user_email . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="order_status"><?php _e('Order Status', 'webstarter'); ?></label>
                <select id="orderStatus" name="order_status" class="widefat">
                    <option value="pending" <?php selected($order_status, 'pending'); ?>><?php _e('Pending', 'webstarter'); ?></option>
                    <option value="processing" <?php selected($order_status, 'processing'); ?>><?php _e('Processing', 'webstarter'); ?></option>
                    <option value="completed" <?php selected($order_status, 'completed'); ?>><?php _e('Completed', 'webstarter'); ?></option>
                    <option value="cancelled" <?php selected($order_status, 'cancelled'); ?>><?php _e('Cancelled', 'webstarter'); ?></option>
                    <option value="onhold" <?php selected($order_status, 'onhold'); ?>><?php _e('On hold', 'webstarter'); ?></option>
                    <option value="refunded" <?php selected($order_status, 'refunded'); ?>><?php _e('Refunded', 'webstarter'); ?></option>
                </select>
            </p>

            <p>
                <label for="transferTo"><?php _e('Transfer this domain to', 'webstarter'); ?></label>

                <select name="transfer_to" id="transferTo" class="widefat">
                    <option value="">Select domain provider</option>
                    <option value="godaddy" <?php selected($transfer_to, 'godaddy'); ?>>GoDaddy</option>
                    <option value="cloudflare" <?php selected($transfer_to, 'cloudflare'); ?>>CloudFlare</option>
                    <option value="dreamhost" <?php selected($transfer_to, 'dreamhost'); ?>>DreamHost</option>
                    <option value="mailchimp" <?php selected($transfer_to, 'mailchimp'); ?>>MailChimp</option>
                    <option value="hostgator" <?php selected($transfer_to, 'hostgator'); ?>>HostGator</option>
                    <option value="other" <?php selected($transfer_to, 'other'); ?>>Other</option>
                </select>
            </p>
        </div>

    <?php
    }

    public function render_billing_info_meta_box($post)
    {
        wp_nonce_field('domain_order_nonce_action', 'domain_order_nonce');
        // Retrieve current data if exists
        $billing_first_name = get_post_meta($post->ID, '_billing_first_name', true);
        $billing_last_name = get_post_meta($post->ID, '_billing_last_name', true);
        $billing_company = get_post_meta($post->ID, '_billing_company', true);
        $billing_address_1 = get_post_meta($post->ID, '_billing_address_1', true);
        $billing_address_2 = get_post_meta($post->ID, '_billing_address_2', true);
        $billing_city = get_post_meta($post->ID, '_billing_city', true);
        $billing_postcode = get_post_meta($post->ID, '_billing_postcode', true);
        $billing_country = get_post_meta($post->ID, '_billing_country', true);
        $billing_state = get_post_meta($post->ID, '_billing_state', true);
        $billing_email = get_post_meta($post->ID, '_billing_email', true);
        $billing_phone = get_post_meta($post->ID, '_billing_phone', true);
    ?>
        <div class="orderBilling_info">
            <h4><?php _e('Billing Information', 'webstarter'); ?></h4>
            <p>
                <label for="billing_first_name"><?php _e('First Name', 'webstarter'); ?></label>
                <input type="text" id="billing_first_name" name="billing_first_name" value="<?php echo esc_attr($billing_first_name); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_last_name"><?php _e('Last Name', 'webstarter'); ?></label>
                <input type="text" id="billing_last_name" name="billing_last_name" value="<?php echo esc_attr($billing_last_name); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_company"><?php _e('Company', 'webstarter'); ?></label>
                <input type="text" id="billing_company" name="billing_company" value="<?php echo esc_attr($billing_company); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_address_1"><?php _e('Address Line 1', 'webstarter'); ?></label>
                <input type="text" id="billing_address_1" name="billing_address_1" value="<?php echo esc_attr($billing_address_1); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_address_2"><?php _e('Address Line 2', 'webstarter'); ?></label>
                <input type="text" id="billing_address_2" name="billing_address_2" value="<?php echo esc_attr($billing_address_2); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_city"><?php _e('City', 'webstarter'); ?></label>
                <input type="text" id="billing_city" name="billing_city" value="<?php echo esc_attr($billing_city); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_postcode"><?php _e('Postcode', 'webstarter'); ?></label>
                <input type="text" id="billing_postcode" name="billing_postcode" value="<?php echo esc_attr($billing_postcode); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_country"><?php _e('Country', 'webstarter'); ?></label>
                <input type="text" id="billing_country" name="billing_country" value="<?php echo esc_attr($billing_country); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_state"><?php _e('State', 'webstarter'); ?></label>
                <input type="text" id="billing_state" name="billing_state" value="<?php echo esc_attr($billing_state); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_email"><?php _e('Email', 'webstarter'); ?></label>
                <input type="email" id="billing_email" name="billing_email" value="<?php echo esc_attr($billing_email); ?>" class="widefat">
            </p>
            <p>
                <label for="billing_phone"><?php _e('Phone', 'webstarter'); ?></label>
                <input type="text" id="billing_phone" name="billing_phone" value="<?php echo esc_attr($billing_phone); ?>" class="widefat">
            </p>

        </div>
    <?php
    }
    public function render_shipping_info_meta_box($post)
    {
        wp_nonce_field('domain_order_nonce_action', 'domain_order_nonce');

        $shipping_first_name = get_post_meta($post->ID, '_shipping_first_name', true);
        $shipping_last_name = get_post_meta($post->ID, '_shipping_last_name', true);
        $shipping_company = get_post_meta($post->ID, '_shipping_company', true);
        $shipping_address_1 = get_post_meta($post->ID, '_shipping_address_1', true);
        $shipping_address_2 = get_post_meta($post->ID, '_shipping_address_2', true);
        $shipping_city = get_post_meta($post->ID, '_shipping_city', true);
        $shipping_postcode = get_post_meta($post->ID, '_shipping_postcode', true);
        $shipping_country = get_post_meta($post->ID, '_shipping_country', true);
        $shipping_state = get_post_meta($post->ID, '_shipping_state', true);
        $shipping_email = get_post_meta($post->ID, '_shipping_email', true);
        $shipping_phone = get_post_meta($post->ID, '_shipping_phone', true);
    ?>
        <div class="orderShippingInfo">
            <h4><?php _e('Shipping Information', 'webstarter'); ?></h4>
            <p>
                <label for="shipping_first_name"><?php _e('First Name', 'webstarter'); ?></label>
                <input type="text" id="shipping_first_name" name="shipping_first_name" value="<?php echo esc_attr($shipping_first_name); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_last_name"><?php _e('Last Name', 'webstarter'); ?></label>
                <input type="text" id="shipping_last_name" name="shipping_last_name" value="<?php echo esc_attr($shipping_last_name); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_company"><?php _e('Company', 'webstarter'); ?></label>
                <input type="text" id="shipping_company" name="shipping_company" value="<?php echo esc_attr($shipping_company); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_address_1"><?php _e('Address Line 1', 'webstarter'); ?></label>
                <input type="text" id="shipping_address_1" name="shipping_address_1" value="<?php echo esc_attr($shipping_address_1); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_address_2"><?php _e('Address Line 2', 'webstarter'); ?></label>
                <input type="text" id="shipping_address_2" name="shipping_address_2" value="<?php echo esc_attr($shipping_address_2); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_city"><?php _e('City', 'webstarter'); ?></label>
                <input type="text" id="shipping_city" name="shipping_city" value="<?php echo esc_attr($shipping_city); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_postcode"><?php _e('Postcode', 'webstarter'); ?></label>
                <input type="text" id="shipping_postcode" name="shipping_postcode" value="<?php echo esc_attr($shipping_postcode); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_country"><?php _e('Country', 'webstarter'); ?></label>
                <input type="text" id="shipping_country" name="shipping_country" value="<?php echo esc_attr($shipping_country); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_state"><?php _e('State', 'webstarter'); ?></label>
                <input type="text" id="shipping_state" name="shipping_state" value="<?php echo esc_attr($shipping_state); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_email"><?php _e('Email', 'webstarter'); ?></label>
                <input type="email" id="shipping_email" name="shipping_email" value="<?php echo esc_attr($shipping_email); ?>" class="widefat">
            </p>
            <p>
                <label for="shipping_phone"><?php _e('Phone', 'webstarter'); ?></label>
                <input type="text" id="shipping_phone" name="shipping_phone" value="<?php echo esc_attr($shipping_phone); ?>" class="widefat">
            </p>

        </div>
    <?php
    }
    public function render_payment_info_meta_box($post)
    {
        wp_nonce_field('domain_order_nonce_action', 'domain_order_nonce');

        $payment_method = get_post_meta($post->ID, '_payment_method', true);
        $transaction_id = get_post_meta($post->ID, '_transaction_id', true);
        $customer_note = get_post_meta($post->ID, '_customer_note', true);

    ?>
        <div class="orderPaymentInfo">
            <h4><?php _e('Payment Information', 'webstarter'); ?></h4>
            <p>
                <label for="payment_method"><?php _e('Payment Method', 'webstarter'); ?></label>
                <input type="text" id="payment_method" name="payment_method" value="<?php echo esc_attr($payment_method); ?>" class="widefat">
            </p>
            <p>
                <label for="transaction_id"><?php _e('Transaction ID', 'webstarter'); ?></label>
                <input type="text" id="transaction_id" name="transaction_id" value="<?php echo esc_attr($transaction_id); ?>" class="widefat">
            </p>
            <p>
                <label for="customer_note"><?php _e('Customer Note', 'webstarter'); ?></label>
                <textarea id="customer_note" name="customer_note" class="widefat"><?php echo esc_textarea($customer_note); ?></textarea>
            </p>
        </div>
        <?php
    }
    /**
     * 
     * For addig add domain meta box
     * @param mixed $post
     * @return void
     */
    public function render_domain_meta_box($post)
    {
        // Get the saved customer ID from post meta
        $order_status = get_post_meta($post->ID, '_order_status', true);
        if ($order_status == 'pending' || $order_status == 'onhold' || $order_status == '') {
        ?>
            <!-- pending / on-hold -->

            <div class="addDomainMain">
                <p>
                    <label for="Domain"><?php _e('Domanis', 'webstarter'); ?></label>
                    <select name="domain_id" id="domainId" class="widefat">
                        <option></option> <!-- Default empty option -->
                    </select>
                </p>
                <input type="button" class="addDomain" id="<?php echo $post->ID; ?>" value="Add domain">
            </div>

        <?php
        } else {
        ?>
            <p>This Order is no longer editable</p>
        <?php
        }
        $saved_domains = get_post_meta($post->ID, '_domain_ids', true);
        $saved_domains = is_array($saved_domains) ? $saved_domains : array();
        $subtotal = (float)get_post_meta($post->ID, '_order_subtotal', true);
        $total = (float)get_post_meta($post->ID, '_order_total', true);

        ?>
        <div class="domainDetails">
            <table class="widefat">
                <thead>
                    <th>Image </th>
                    <th> Name </th>
                    <th>Total</th>
                </thead>
                <tbody>
                    <?php foreach ($saved_domains as $domain_id): ?>
                        <?php $domain_post = get_post($domain_id); ?>
                        <?php if ($domain_post):
                            $price = get_post_meta($domain_post->ID, '_sale_price', true);
                            if (!$price) {
                                $price = get_post_meta($domain_post->ID, '_regular_price', true);
                            }
                            // $subtotal += (float) $price; // Add the price to the subtotal
                        ?>

                            <tr class="domainDetail" data-id="<?php echo esc_attr($domain_id); ?>">
                                <td>
                                    <?php
                                    $image_url = get_the_post_thumbnail_url($domain_id, 'thumbnail');
                                    if ($image_url): ?>
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($domain_post->post_title); ?>" style="max-width: 50px;">

                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($domain_post->post_title); ?></td>
                                <td><?php echo $price; ?></td>
                                <td class="deleteOrderItem"> <a href="javascript:void(0);" id="<?php echo $domain_post->ID ?>"><i class="fa fa-times" aria-hidden="true"></i></a></td>

                                <input type="hidden" name="domain_ids[]" value="<?php echo esc_attr($domain_id); ?>">
                                <!-- You could add more actions here like edit or remove if needed -->

                                <input type="hidden" class="orderId" value="<?php echo $post->ID ?>">

                            </tr>

                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
        <div class="orderAmountDetails">
            <div class="orderSubtotal">
                <label>Subtotal:</label>
                <input type="text" name="order_subtotal" value=" <?php echo esc_html(number_format($subtotal, 2)); ?>" readonly>
            </div>
            <div class="orderTotal">
                <label>Total:</label>
                <input type="text" name="order_total" value=" <?php echo esc_html(number_format($total, 2)); ?>" readonly>
            </div>
            <!-- <p><strong>Subtotal:</strong> <?php //echo esc_html(number_format($subtotal, 2)); 
                                                ?></p>
            <p><strong>Total:</strong> <?php //echo esc_html(number_format($total, 2)); 
                                        ?></p> -->
        </div>

        <?php
    }

    public function render_order_notes_meta_box($post)
    {

        // Display existing notes

        global $wpdb;
        $table = $wpdb->prefix . 'order_notes';

        $order_notes = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE order_id = %d ORDER BY note_date DESC", $post->ID));

        echo '<div class="orderNotesMain">';
        if ($order_notes) {
            foreach ($order_notes as $note) {
        ?>
                <div class="eachNoteSection">
                    <p><?php echo $note->note ?> </p>
                    <em><?php echo esc_html(date('F j, Y \a\t g:i a', strtotime($note->note_date))) ?></em>
                    <a href="javascript:void(0);" class="deleteNoteButton" data-note-id="<?php echo  esc_attr($note->id) ?>">Delete</a>
                </div>
        <?php
            }
        } else {
            echo '<p>No notes found for this order.</p>';
        }
        echo '</div>';

        ?>

        <div class="orderNotesAdd">
            <textarea class="widefat" id="orderNote" name="order_note" rows="3"></textarea>
            <select name="order_note_type" id="orderNoteType" class="widefat">
                <option value="private">Private</option>
                <option value="public">Note to customer</option>
            </select>
            <input type="button" class="addOrderNotesButton" id="<?php echo $post->ID ?>" value="Add Note">
        </div>
        <!-- 
           -->
<?php
    }

    /**
     * For saving meta data to the data base 
     * @param mixed $post_id
     * @return mixed
     */
    public function save_domain_order_meta_box($post_id)
    {
        // Check if nonce is set
        if (!isset($_POST['domain_order_nonce'])) {
            return $post_id;
        }

        // Verify nonce
        $nonce = $_POST['domain_order_nonce'];
        if (!wp_verify_nonce($nonce, 'domain_order_nonce_action')) {
            return $post_id;
        }

        // Autosave check
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
        // Check user's permissions.
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        if (isset($_POST['order_status'])) {
            $order_status = sanitize_text_field($_POST['order_status']);
            update_post_meta($post_id, '_order_status', $order_status);
        }


        $customer = sanitize_text_field($_POST['customer']);
        update_post_meta($post_id, '_customer', $customer);

        // Sanitize and save the data
        $date_created = sanitize_text_field($_POST['date_created']);
        update_post_meta($post_id, '_date_created', $date_created);

        $transfer_to = sanitize_text_field($_POST['transfer_to']);
        update_post_meta($post_id, '_transfer_to', $transfer_to);

        // for billing details 
        $billing_first_name = sanitize_text_field($_POST['billing_first_name']);
        update_post_meta($post_id, '_billing_first_name', $billing_first_name);

        $billing_last_name = sanitize_text_field($_POST['billing_last_name']);
        update_post_meta($post_id, '_billing_last_name', $billing_last_name);

        $billing_company = sanitize_text_field($_POST['billing_company']);
        update_post_meta($post_id, '_billing_company', $billing_company);

        $billing_address_1 = sanitize_text_field($_POST['billing_address_1']);
        update_post_meta($post_id, '_billing_address_1', $billing_address_1);

        $billing_address_2 = sanitize_text_field($_POST['billing_address_2']);
        update_post_meta($post_id, '_billing_address_2', $billing_address_2);

        $billing_city = sanitize_text_field($_POST['billing_city']);
        update_post_meta($post_id, '_billing_city', $billing_city);

        $billing_postcode = sanitize_text_field($_POST['billing_postcode']);
        update_post_meta($post_id, '_billing_postcode', $billing_postcode);

        $billing_country = sanitize_text_field($_POST['billing_country']);
        update_post_meta($post_id, '_billing_country', $billing_country);

        $billing_state = sanitize_text_field($_POST['billing_state']);
        update_post_meta($post_id, '_billing_state', $billing_state);

        $billing_email = sanitize_email($_POST['billing_email']);
        update_post_meta($post_id, '_billing_email', $billing_email);

        $billing_phone = sanitize_text_field($_POST['billing_phone']);
        update_post_meta($post_id, '_billing_phone', $billing_phone);

        // for shipping details 
        $shipping_first_name = sanitize_text_field($_POST['shipping_first_name']);
        update_post_meta($post_id, '_shipping_first_name', $shipping_first_name);

        $shipping_last_name = sanitize_text_field($_POST['shipping_last_name']);
        update_post_meta($post_id, '_shipping_last_name', $shipping_last_name);

        $shipping_company = sanitize_text_field($_POST['shipping_company']);
        update_post_meta($post_id, '_shipping_company', $shipping_company);

        $shipping_address_1 = sanitize_text_field($_POST['shipping_address_1']);
        update_post_meta($post_id, '_shipping_address_1', $shipping_address_1);

        $shipping_address_2 = sanitize_text_field($_POST['shipping_address_2']);
        update_post_meta($post_id, '_shipping_address_2', $shipping_address_2);

        $shipping_city = sanitize_text_field($_POST['shipping_city']);
        update_post_meta($post_id, '_shipping_city', $shipping_city);

        $shipping_postcode = sanitize_text_field($_POST['shipping_postcode']);
        update_post_meta($post_id, '_shipping_postcode', $shipping_postcode);

        $shipping_country = sanitize_text_field($_POST['shipping_country']);
        update_post_meta($post_id, '_shipping_country', $shipping_country);

        $shipping_state = sanitize_text_field($_POST['shipping_state']);
        update_post_meta($post_id, '_shipping_state', $shipping_state);

        $shipping_email = sanitize_email($_POST['shipping_email']);
        update_post_meta($post_id, '_shipping_email', $shipping_email);

        $shipping_phone = sanitize_text_field($_POST['shipping_phone']);
        update_post_meta($post_id, '_shipping_phone', $shipping_phone);

        $payment_method = sanitize_text_field($_POST['payment_method']);
        update_post_meta($post_id, '_payment_method', $payment_method);

        $transaction_id = sanitize_text_field($_POST['transaction_id']);
        update_post_meta($post_id, '_transaction_id', $transaction_id);

        $customer_note = sanitize_textarea_field($_POST['customer_note']);
        update_post_meta($post_id, '_customer_note', $customer_note);

        // for saving product 
        if (isset($_POST['domain_ids'])) {

            $domain_ids = array_map('sanitize_text_field', $_POST['domain_ids']);
            update_post_meta($post_id, '_domain_ids', $domain_ids);
        }

        $get_domain_ids = get_post_meta($post_id, '_domain_ids', true);

        $get_order_status = get_post_meta($post_id, '_order_status', true);

        if ($get_order_status == 'completed' || $get_order_status == 'processing') {
            // Loop through each domain ID
            foreach ($get_domain_ids as $domain_id) {
                // Update the stock status to 'instock'
                update_post_meta($domain_id, '_stock_status', 'outofstock');
            }
        } else if ($get_order_status == 'refunded' || $get_order_status == 'cancelled') {
            // Loop through each domain ID
            foreach ($get_domain_ids as $domain_id) {
                // Update the stock status to 'outofstock'
                update_post_meta($domain_id, '_stock_status', 'instock');
            }
        }

        // for order notes
        $order_note = sanitize_text_field($_POST['order_note']);
        $order_note_type = sanitize_text_field($_POST['order_note_type']);

        if ($order_note) {
            global $wpdb;

            $table = $wpdb->prefix . 'order_notes';
            $data = array(
                'order_id' => $post_id,
                'note' => $order_note,
                'note_type' => $order_note_type,
                'note_date' => current_time('mysql')
            );

            $wpdb->insert($table, $data);
        }
    }
}
new wstr_domain_order_meta_boxes();

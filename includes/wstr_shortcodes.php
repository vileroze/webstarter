<?php
class wstr_shortcodes
{
    
    public function __construct()
    {
        add_shortcode('wstr_banner_reviews', array($this, 'wstr_banner_reviews_function'));
        add_shortcode('wstr-multicurrency', array($this, 'wstr_multicurrency'));
        add_shortcode('wstr-browse-industry', array($this, 'wstr_browse_industry'));
        add_shortcode('wstr-popular-category', array($this, 'wstr_popular_category'));
        add_shortcode('wstr-domain-count', array($this, 'wstr_domain_count'));
        add_shortcode('wstr-footer-average-cost', array($this, 'wstr_footer_average_cost_calculation'));
        add_shortcode('wstr-you-may-like', array($this, 'wstr_you_may_like'));
        add_shortcode('wstr_estimation', array($this, 'wstr_estimation'));
        add_shortcode('wstr-single-domain', array($this, 'wstr_single_domain_page'));
        add_shortcode('wstr-similar-industry-name', [$this, 'wstr_similar_industry_name']);
        add_shortcode('wstr-buy-domain', [$this, 'wstr_buy_domain']);
        add_shortcode('wstr-login', [$this, 'wstr_login']);
        add_shortcode('wstr_register', [$this, 'wstr_register']);
        add_shortcode('wstr_cart_page', [$this, 'wstr_cart_page']);
        add_shortcode('wstr_payment_success_page', [$this, 'wstr_payment_success_page']);
    }

    public function wstr_banner_reviews_function()
    {
        ob_start();
?>
        <!-- reviews banner -->
        <div class="banner-reviews ws_min_container ws_flex gap_20 jc_center margin_v_30 fd_mob_col">
            <div class=" reviews_images_lists ws_flex jc_center ai_center">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/clients-1.jpeg" alt="Client Image" />
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/clients-2.jpg" alt="Client Image" />
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/clients-3.jpeg" alt="Client Image" />
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/clients-4.jpeg" alt="Client Image" />

                <i class="fa-solid fa-circle-plus"></i>
            </div>
            <div class="reviews-contents ws_text_center ">
                <div class="reviews-total ws_flex">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <p>4.9 Excellent</p>
                </div>
                <div>
                    <p>
                        <span>1,500+ </span>clients trust WebStarter. <a href="#">Join them today!</a>
                    </p>
                </div>
            </div>
        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
    /**
     * Shortcode for getting currecy symbol for frontend
     * @return mixed
     */
    function wstr_multicurrency()
    {
        ob_start();
        // Start the session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Get the selected currency from session, default to 'USD' if not set
        $selected_currency = isset($_SESSION['currency']) ? $_SESSION['currency'] : 'USD';

        // Get the list of currency codes from the options table
        $currency_codes = get_option('wstr_currency_codes');
        if ($currency_codes) {
            // Remove 'USD' from the list if it exists
            if (($key = array_search('USD', $currency_codes)) !== false) {
                unset($currency_codes[$key]);
            }

            // Output the select box
        ?>
            <select id="wstr-mulitcurrency">
                <!-- USD option -->
                <option value="USD" <?php selected($selected_currency, 'USD'); ?>>$</option>
                <?php
                // Loop through the remaining currency codes and add them as options
                foreach ($currency_codes as $currency_code) {
                    // Assuming get_wstr_currency_symbol() fetches the appropriate symbol for each currency code
                    $currency_symbol = get_wstr_currency_symbol($currency_code);
                ?>
                    <option value="<?php echo esc_attr($currency_code); ?>" <?php selected($selected_currency, $currency_code); ?>>
                        <?php echo esc_html($currency_symbol); ?>
                    </option>
                <?php
                }
                ?>
            </select>
        <?php
        }
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * function for home page browse industry
     */
    public function wstr_browse_industry()
    {

        ob_start();

        $args = array(
            'hide_empty' => false,
            'number' => 17,
            'taxonomy' => 'domain_industry',
        );

        $industries = get_terms($args);
        $domains_list_page = get_page_link(get_option('ws_domain_list_page')); // getting product page link
        if (!$domains_list_page) {
            $domains_list_page = get_home_url() . '/domain-list/';
        }
        ?>
        <div class="ws-industry-wrapper">
            <?php
            if ($industries) {

                foreach ($industries as $industry) {

            ?>
                    <div class="ws-industry_details">
                        <?php
                        // Query domains for each industry (term)
                        $args_domains = array(
                            'post_type' => 'domain', // Assuming 'domain' is your custom post type
                            'posts_per_page' => -1, // Fetch all domains for this industry
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'domain_industry',
                                    'field' => 'slug',
                                    'terms' => $industry->slug, // Get domains for the current term (industry)
                                ),
                            ),
                            'date_query' => array(
                                'after' => array(
                                    'year' => date("Y"),
                                    'month' => date("m"),
                                    'day' => date("d") - 17,
                                ),
                            ),
                        );

                        $domains_query = new WP_Query($args_domains);

                        if ($domains_query->have_posts()) {

                        ?>
                            <span>New</span>
                        <?php
                        }

                        $term_image_id = get_term_meta($industry->term_id, 'taxonomy-image-id', true);

                        if ($term_image_id) {
                            $term_image_url = wp_get_attachment_url($term_image_id);
                        ?>
                            <img src="<?php echo $term_image_url ? $term_image_url : '' ?>">
                        <?php
                        }
                        ?>

                        <a href="<?php echo $domains_list_page . '?industry=' . $industry->slug ?>"><?php echo $industry->name; ?></a>
                    </div>
                <?php
                }
                ?>
                <div class="ws-industry_details">

                    <a href="<?php echo $domains_list_page; ?>">Browse All</a>
                </div>
            <?php

            }
            ?>

        </div>
    <?php
        return ob_get_clean();
    }

    /**
     * Shortcode for getting no of domain of each popular category
     * @param mixed $args
     * @return bool|string
     */
    public function wstr_popular_category($args)
    {
        ob_start();
        $term_id = $args['term_id'];
        $term_details = get_term($term_id);
        echo $term_details->count;
        return ob_get_clean();
    }

    /**
     * Shortcode for getting total of domains
     */
    public function wstr_domain_count()
    {
        ob_start();
        $query_args = array(
            'posts_per_page' => -1,
            'post_type' => 'domain',
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => '!='
                )
            )
        );
        $domain_products = get_posts($query_args);
        $count = count($domain_products);

        // if ($count > 1200) {
        //     echo '<p class="get_average_price">120+ </p>';
        // } else {
        echo '<p class="get_average_price">' . $count . '</p>';
        // }

        return ob_get_clean();
    }

    public function wstr_footer_average_cost_calculation()
    {
        ob_start();
        $total_prices = 0;
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'domain',
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => '!='
                )
            )
        );
        $domains = get_posts($args);
        if ($domains) {
            $price = 0;
            foreach ($domains as $domain) {
                $price += get_wstr_price_value($domain->ID);
            }
        }
        $average_price = (float) $price / count($domains);

        $output = '<p class="get_average_price">' . get_wstr_currency() . '' . wstr_truncate_number($average_price) . ' </p>';

        echo $output;
        return ob_get_clean();
    }

    public function wstr_estimation()
    {
    ?>
        <div class="wstr_estimate_domain_wrapper ws_home_banner" id="wstr_domain_estimate">
            <div class="reviews_images_lists ws_flex jc_center ai_center">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/clients-1.jpeg" alt="Client Image" />
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/clients-2.jpg" alt="Client Image" />
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/clients-3.jpeg" alt="Client Image" />
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/clients-4.jpeg" alt="Client Image" />
            </div>
            <h4 class="ws_text_center">Become a seller</h4>
            <p class="ws_text_center">Verify your Domain Value Estimation!</p>
            <div class="wp-block-search p_relative">
                <input type="text" name="domain" id="domain" placeholder="Enter your domain" class="w_100" />
                <button type="submit" value="Estimate">Estmate
            </div>
        </div>
    <?php
    }

    /**
     * shortcode for you may like section in single page
     * @param mixed $atts
     * @return mixed
     */
    public function wstr_you_may_like($atts)
    {
        global $post;
        ob_start();
        $args = shortcode_atts(array(
            'count' => 12,
        ), $atts);

        $query_args = array(
            'posts_per_page' => $args['count'],
            'post_type' => 'domain',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'meta_key' => 'ws_product_view_count',
            'post__not_in' => array($post->ID),
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => '!='
                )
            )
        );

    ?>
        <div class="you-may-like-main">
            <div class="you_may_like_heading_wrapper ws_flex">
                <h4 class="ws_flex ai_center gap_5">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/love-emoji.png" alt="You May Like">
                    You May Like
                </h4>
                <a href="#">All Domains</a>
            </div>
            <div class="ws-cards-container-wrapper ws_cards_xl you_may_like_card_wrapper margin_v_35">
                <?php
                $domains = get_posts($query_args);
                foreach ($domains as $domain) {
                    $domain_id = $domain->ID;
                    $featured_image = get_the_post_thumbnail_url($domain->ID);
                    $logo_image_id = get_post_meta($domain->ID, '_logo_image', true);
                    $logo_image = wp_get_attachment_url($logo_image_id);

                    $da_pa = get_post_meta($domain->ID, '_da_pa', true);
                    $da = $pa = '';
                    if ($da_pa) {
                        $da_pa_split = explode('/', $da_pa);
                        $da = $da_pa_split[0];
                        $pa = $da_pa_split[1];
                    }

                ?>


                    <div class="ws-cards-container">
                        <div class="ws_card_hover_charts ws_flex">
                            <div class="circular-progress page-trust">
                                <div class="progress-text">
                                    <div role="progressbar" aria-valuenow="<?php echo (int) $pa; ?>" aria-valuemin="0"
                                        aria-valuemax="100" style="--value: <?php echo (int) $pa; ?>"></div>
                                </div>
                                <div class="progress-title">
                                    <h6>Page Trust</h6>
                                </div>
                            </div>
                            <div class="circular-progress domain-trust">
                                <div class="progress-text">
                                    <div role="progressbar" aria-valuenow="<?php echo (int) $da; ?> " aria-valuemin="0"
                                        aria-valuemax="100" style="--value:<?php echo (int) $da; ?> "></div>
                                </div>
                                <div class="progress-title">
                                    <h6>Domain Trust</h6>
                                </div>
                            </div>
                        </div>
                        <div class="ws-card-img">
                            <img decoding="async"
                                src="<?php echo $featured_image ? $featured_image : get_stylesheet_directory_uri() . '/assets/images/alternate-domain.png' ?>"
                                alt="<?php echo get_the_title($domain->ID); ?>">
                        </div>
                        <div class="ws-card-contents ws-flex">
                            <img decoding="async" src="<?php echo $logo_image ? $logo_image : $featured_image ?>" alt="zanabism.com"
                                title="<?php echo get_the_title($domain->ID); ?>" class="card_logo_img">
                            <span class="ws-card-inner-contents">
                                <h5>
                                    <a
                                        href="<?php echo get_permalink($domain->ID); ?>"><?php echo get_the_title($domain->ID); ?></a>
                                </h5>

                                <?php echo get_wstr_price($domain->ID); ?>
                            </span>
                            <div class="ws-card-likes">
                                <h6>
                                    <span>2k</span>
                                    <i class="fa-solid fa-heart"></i>
                                </h6>
                            </div>
                        </div>
                    </div>
                <?php
                }

                // $domains_list_page = get_page_link(get_option('ws_domain_list_page'));
                ?>
            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    /**
     * Shortocode for single domain page
     * @return bool|string
     */
    public function wstr_single_domain_page()
    {
        ob_start();
    ?>
        <div class="single-container ws-container">
            <?php
            // Start the Loop.
            while (have_posts()):
                the_post();

                // Get the featured image URL
                $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
                // Get custom fields or post meta
                $logo = get_post_meta(get_the_ID(), 'logo', true);
                $title = get_the_title();
                $regular_price = get_post_meta(get_the_ID(), '_regular_price', true);
                $sale_price = get_post_meta(get_the_ID(), '_sale_price', true);
                $domain_length = get_post_meta(get_the_ID(), '_length', true);
                $domain_age = get_post_meta(get_the_ID(), '_age', true);
                $da_pa = get_post_meta(get_the_ID(), '_da_pa', true);
                $highlights_title = get_post_meta(get_the_ID(), '_highlight_title', true);
                $highlights_content = get_post_meta(get_the_ID(), '_highlight_content', true);
                $currency = get_wstr_currency();
                $category = get_the_terms(get_the_ID(), 'domain_cat');
                shuffle($category);
                $category_name = $category[0]->name;
                $category_id = $category[0]->term_id;
                $cat_image_id = get_term_meta($category_id, 'taxonomy-image-id', true);
                $cat_image_url = wp_get_attachment_url($cat_image_id);
                $tags = get_the_terms(get_the_ID(), 'domain_tag');
                $term_exist = wstr_check_existing_term(get_the_ID(), 'domain_cat', 'premium-names');

                $da = $pa = '';
                if ($da_pa) {
                    $da_pa_split = explode('/', $da_pa);
                    $da = $da_pa_split[0];
                    $pa = $da_pa_split[1];
                }
                $post_count = (int) get_post_meta(get_the_ID(), 'ws_product_view_count', true);
                $new_post_count = $post_count + 1;

                update_post_meta(get_the_ID(), 'ws_product_view_count', $new_post_count);

            ?>
                <div class="single_domain_details ws_flex fd_mob_col ">
                    <!-- Featured Image -->
                    <div class="featured-image p_relative img_producto_container" data-scale="1.6">

                        <img src="<?php echo $featured_image ? esc_url($featured_image) : get_stylesheet_directory_uri() . '/assets/images/alternate-domain.png' ?>"
                            alt="<?php echo esc_attr($title); ?>" class="img_producto">

                        <div class="single_featured_image_footer ws_flex">
                            <span class="domain_online ws_flex gap_10 ai_center online">
                                <i class="fa-solid fa-comments"></i>
                                <p>Online
                                    <i class="fa-solid fa-circle"></i>
                                </p>
                            </span>
                            <a href="#">
                                <p>Message</p>
                            </a>
                        </div><?php
                                if ($term_exist) {
                                ?>
                            <div class="premium_icon">
                                <img src="/wp-content/plugins/card-block/images/diamond.png" alt="Diamond Icon" />
                            </div> <?php
                                }
                                    ?>
                        <div class="ws_flex ai_center single_domain_meta_search">
                            <div class="single_domain_search">

                                <i class="fa-solid fa-magnifying-glass" id="enlarge-icon"></i>

                            </div>
                            <div class="ws-card-likes">
                                <h6><span>2k</span><i class="fa-solid fa-heart"></i></h6>
                            </div>
                        </div>
                    </div>
                    <div id="imageModal" class="modal">
                        <!-- Modal content -->
                        <span class="close">&times;</span>
                        <img id="modalImage" src="" alt="Full-Width Image" />
                    </div>

                    <!-- Details Section -->
                    <div class="domain-details">
                        <div class="ws_flex gap_20 ai_center p_relative">
                            <div>
                                <?php if (!$logo && !$featured_image) {
                                ?>
                                    <img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/alternate-domain.png' ?>"
                                        alt="<?php echo $title ?>">
                                <?php
                                } else {

                                ?>
                                    <img src="<?php echo $logo ? $logo : $featured_image ?>" alt="<?php echo $title; ?> ">
                                <?php }
                                ?>

                            </div>

                            <?php
                            $discount_percent = get_wstr_price_percentage(get_the_ID());
                            echo $discount_percent;
                            ?>
                            <div>
                                <h2 class="fw-600"><?php echo esc_html($title); ?></h2>
                                <div class="single_domain_price ws_flex gap_10 ai_center">
                                    <?php
                                    if (!empty($regular_price)) { ?>
                                        <p class="regular_price">
                                            <?php
                                            echo get_wstr_currency();
                                            echo get_wstr_regular_price(get_the_ID());
                                            ?>
                                        </p>
                                    <?php
                                    }
                                    if (!empty($sale_price)) { ?>
                                        <p class="sale_price">
                                            <?php
                                            echo get_wstr_currency();
                                            echo get_wstr_sale_price(get_the_ID());
                                            ?>
                                        </p>
                                    <?php
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <div class="single_domain_short_desc">
                            <?php
                            the_excerpt();
                            ?>
                        </div>
                        <div class="wstr_payments">
                            <h6>PAYMENT OPTIONS</h6>

                            <?php

                            $in_cart = false;
                            //check if product already in cart and show remove from cart button
                            if (isset($_SESSION['cart'])) {
                                $all_prodcut_ids = array_keys($_SESSION['cart']);
                                if (in_array(get_the_ID(), $all_prodcut_ids)) {
                                    $in_cart = true;
                                }
                            }

                            echo '<button class="add-to-cart-btn" data-product-id="' . get_the_ID() . '" style="display: ' . ($in_cart ? 'none' : 'block') . '">ADD TO CART</button>';
                            echo '<button class="remove-from-cart-btn" data-product-id="' . get_the_ID() . '" style="display: ' . ($in_cart ? 'block' : 'none') . '">REMOVE FROM CART</button>';
                            ?>

                            <!-- create radio buttons for payment options -->
                            <input type="radio" name="payment_option" class="payment-option" value="full" id="full_payment" checked>
                            <label for="full_payment">Full Payment</label>
                            <br>
                            <br>
                            <input type="radio" name="payment_option" class="payment-option" value="installment" id="installment_payment">
                            <label for="installment_payment">Installment Payment</label>
                            <br>
                            <br>

                            <div id="installment_duration_options" style="display: none;">
                                <input type="radio" name="installment_duration" class="installment_duration" value="3" id="three_months" checked>
                                <label for="installment_duration">3 months</label>
                                <input type="radio" name="installment_duration" class="installment_duration" value="6" id="six_months">
                                <label for="installment_duration">6 months</label>
                                <input type="radio" name="installment_duration" class="installment_duration" value="12" id="twelve_months">
                                <label for="twelve_months">12 months</label>
                            </div>
                        </div>
                        <?php
                        // the_content();
                        ?>
                    </div>
                </div>
                <div class="single_domain_reviews_information ws_flex fd_mob_col">
                    <div class="single_domain_tabs_container">
                        <ul class="tabs">
                            <li class="tab current" data-tab="tab-1">Domain</li>
                            <li class="tab" data-tab="tab-2">Seller Reviews</li>
                        </ul>

                        <div id="tab-1" class="tab-content current">
                            <h2 class="fw-600 margin_v_35 mt_center">Domain Information</h2>
                            <div class="single_domain_progress_wrapper br_15">
                                <h4 class="fw-600"><?php echo esc_html($title); ?></h4>
                                <div class="ws_flex gap_20">
                                    <div class="circular-progress page-trust">
                                        <div class="progress-text">
                                            <div role="progressbar" aria-valuenow="<?php echo (int) esc_html($pa); ?>"
                                                aria-valuemin="0" aria-valuemax="100"
                                                style="--value:<?php echo (int) esc_html($pa); ?>">
                                                <p>of 100</p>
                                            </div>
                                        </div>
                                        <div class="progress-title">
                                            <h6>PAGE TRUST</h6>
                                        </div>
                                    </div>

                                    <div class="circular-progress domain-trust">
                                        <div class="progress-text">
                                            <div role="progressbar" aria-valuenow="<?php echo (int) esc_html($da); ?>"
                                                aria-valuemin="0" aria-valuemax="100"
                                                style="--value:<?php echo (int) esc_html($da); ?>">
                                                <p>of 100</p>
                                            </div>
                                        </div>
                                        <div class="progress-title">
                                            <h6>DOMAIN TRUST</h6>

                                        </div>
                                    </div>
                                    <div class="circular-progress domain-trust domain-length">
                                        <div class="progress-text">
                                            <div role="progressbar" aria-valuenow="<?php echo (int) esc_html($domain_length); ?>"
                                                aria-valuemin="0" aria-valuemax="100"
                                                style="--value:<?php echo (int) esc_html($domain_length); ?>">
                                                <p>Letters</p>
                                            </div>
                                        </div>
                                        <div class="progress-title">
                                            <h6>DOMAIN LENGTH</h6>

                                        </div>
                                    </div>
                                    <div class="circular-progress domain-trust domain-age">
                                        <div class="progress-text">
                                            <div role="progressbar" aria-valuenow="<?php echo (int) esc_html($domain_age); ?>"
                                                aria-valuemin="0" aria-valuemax="100"
                                                style="--value:<?php echo (int) esc_html($domain_age); ?>">
                                                <p>Years</p>
                                            </div>
                                        </div>
                                        <div class="progress-title">
                                            <h6>DOMAIN AGE</h6>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="single_domain_info">
                                <?php
                                the_content();
                                ?>
                            </div>
                            <div class="single_domain_features ws_trending_cards section_gap_mobile">
                                <h2>What You Get</h2>
                                <div class="ws_flex gap_10 fd_mob_col">
                                    <div class="similar-industry-names ws-card-contents ws_flex single_domain_feature">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/domain-name.png"
                                            alt="Feature Image">
                                        <div class="ws-card-inner-contents">
                                            <h5 class="fw-600"><?php echo $title ?></h5>
                                            <div class="ws_card_price_wrapper ws_flex gap_10">
                                                <p>Domain Name</p>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="similar-industry-names ws-card-contents ws_flex single_domain_feature">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/svg-file-icon.png"
                                            alt="SVG Icon">
                                        <div class="ws-card-inner-contents">
                                            <h5 class="fw-600">SVG File & Copyright</h5>
                                            <div class="ws_card_price_wrapper ws_flex gap_10">
                                                <p class="">Logo Design</p>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="similar-industry-names ws-card-contents ws_flex single_domain_feature">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/technical-support.png"
                                            alt="SVG Icon">
                                        <div class="ws-card-inner-contents">
                                            <h5 class="fw-600">Technical Support</h5>
                                            <div class="ws_card_price_wrapper ws_flex gap_10">
                                                <p>Free Technical Support</p>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="domain-tag">
                                <h2>Related tags</h2>
                                <ul class="related_tag_list_wrapper ws_flex gap_10">
                                    <?php
                                    foreach ($tags as $tag) {
                                    ?>
                                        <li>
                                            <?php
                                            $tag_name = $tag->name;
                                            echo $tag_name;
                                            ?>
                                        </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                        <div id="tab-2" class="tab-content">
                            <h2>Tab Two</h2>
                            <!-- Your content here -->
                        </div>
                    </div>

                    <div class="single_domain_highlights">
                        <h2 class="fw-600"><?php echo ($highlights_title) ?></h2>
                        <div class="single_domain_highlights_cards">
                            <div class="single_domain_highlights_card">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/highlight-1.png"
                                    alt="Feature Image">
                                <h5>Fast and Secure Transfer</h5>
                            </div>
                            <div class="single_domain_highlights_card">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/highlight-2.png"
                                    alt="Feature Image">
                                <h5>Free Technical Support</h5>
                            </div>
                            <div class="single_domain_highlights_card">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/highlight-3.png"
                                    alt="Feature Image">
                                <h5>Professionally Crafter Logo</h5>
                            </div>
                            <div class="single_domain_highlights_card">
                                <img src="<?php echo $cat_image_url ? $cat_image_url : get_stylesheet_directory_uri() . '/assets/images/highlight-alternate.png'; ?> "
                                    alt="<?php echo $category_name ?>">
                                <h5><?php echo $category_name ?></h5>
                            </div>
                        </div>
                        <?php
                        echo do_shortcode('[wstr_estimation]');
                        echo do_shortcode('[wstr-similar-industry-name category_id =' . $category_id . ']')
                        ?>
                    </div>
                </div>

                <div class="single_domain_you_may_like">
                    <?php
                    echo do_shortcode('[wstr-you-may-like]');
                    ?>
                </div>

            <?php
            endwhile; // End the Loop.
            ?>
        </div>
    <?php

        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * Function for getting similar category domain
     * @param mixed $atts
     * @return bool|string
     */
    public function wstr_similar_industry_name($atts)
    {
        ob_start();
        $category_id = $atts['category_id'];
        $similar_domain_args = array(
            'post_type' => 'domain',
            'fields' => 'ids',
            'posts_per_page' => 3,
            'orderby' => 'rand',
            'tax_query' => array(
                array(
                    'taxonomy' => 'domain_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                    'operator' => 'IN',
                )
            )
        );
    ?>
        <div class="similar-industry-names-main ws_trending_cards margin_v_35">
            <h5>Similar Industry Names</h5>
            <?php
            $similar_domain_ids = get_posts($similar_domain_args);
            foreach ($similar_domain_ids as $similar_domain_id) {
                $similar_domain_title = get_the_title($similar_domain_id);
                $featured_image_id = get_post_thumbnail_id($similar_domain_id);
                $featured_image_url = wp_get_attachment_url($featured_image_id);
                $logo_image_id = get_post_meta($similar_domain_id, '_logo_image', true);
                $logo_image_url = wp_get_attachment_url($logo_image_id);
                $permalink = get_permalink($similar_domain_id);
            ?>
                <a href="<?php echo $permalink ?>">
                    <div class="similar-industry-names ws-card-contents ws_flex">

                        <?php if (!$logo_image_url && !$featured_image_url) {
                        ?>
                            <img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/alternate-domain.png' ?>"
                                alt="<?php echo $similar_domain_title ?>">
                        <?php
                        } else {

                        ?>
                            <img src="<?php echo $logo_image_url ? $logo_image_url : $featured_image_url ?>"
                                alt="<?php echo $similar_domain_title; ?> ">
                        <?php }
                        ?>
                        <div class="ws-card-inner-contents">
                            <h5><?php echo $similar_domain_title ?></h5>
                            <?php echo get_wstr_price($similar_domain_id); ?>
                        </div>
                    </div>
                </a>
            <?php
            }
            ?>
        </div>
    <?php
        return ob_get_clean();
    }

    public function wstr_buy_domain()
    {
        ob_start();
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = [
            'posts_per_page' => 20,
            'post_type' => 'domain',
            'paged' => $paged,
            'meta_query' => [
                [
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => '!='
                ]
            ],

        ];
        query_posts($args); ?>
        <!-- the loop -->
        <?php if (have_posts()):
            while (have_posts()):
                the_post();
        ?>

                <h5><?php echo get_the_title(); ?></h5>

            <?php endwhile;
            // <!-- pagination -->

            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => __('<', 'webstarter'),
                'next_text' => __('>', 'webstarter'),
            ));

        //  else : 
        // <!-- No posts found -->
        endif;
        return ob_get_clean();
    }

    public function wstr_login()
    {
        ob_start();
        if (isset($_POST['register_user'])) {
            // Process the registration
            $username = sanitize_user($_POST['username']);
            $email = sanitize_email($_POST['email']);
            $password = sanitize_text_field($_POST['password']);
            $confirm_password = sanitize_text_field($_POST['confirm_password']);
            $full_name = sanitize_text_field($_POST['full_name']);
            $become_seller = isset($_POST['become_seller']) ? true : false;

            $errors = [];

            // Validate required fields
            if (empty($username)) {
                $errors[] = 'Username is required.';
            }

            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!is_email($email)) {
                $errors[] = 'Invalid email address.';
            }

            if (empty($password) || empty($confirm_password)) {
                $errors[] = 'Password and confirm password are required.';
            } elseif ($password !== $confirm_password) {
                $errors[] = 'Passwords do not match.';
            }

            // Check if username or email already exists
            if (username_exists($username)) {
                $errors[] = 'Username already exists.';
            }

            if (email_exists($email)) {
                $errors[] = 'Email already exists.';
            }

            // If no errors, proceed with user registration
            if (empty($errors)) {
                $user_data = [
                    'user_login' => $username,
                    'user_email' => $email,
                    'user_pass' => $password,
                    'first_name' => explode(' ', $full_name)[0], // First name
                    'last_name' => explode(' ', $full_name)[1] ?? '', // Last name if available
                ];

                $user_id = wp_insert_user($user_data);

                if (!is_wp_error($user_id)) {
                    // Optionally, assign the user role as seller if checkbox is checked
                    if ($become_seller) {
                        $user = new WP_User($user_id);
                        $user->set_role('seller');
                    } else {
                        $user = new WP_User($user_id);
                        $user->set_role('buyer');
                    }

                    // Redirect after successful registration
                    wp_redirect(home_url('/my-account?new_user=yes'));
                    exit;
                } else {
                    $errors[] = $user_id->get_error_message(); // Display any WP error
                }
            }
        }


        $register = $_GET['register'];
        if ($register == true) {
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<p class="error">' . esc_html($error) . '</p>';
                }
            }
            ?>
            <div class="register-page-wrapper">
                <div class="login-form-details">
                    <div>
                        <form action="#" method="POST" class="wstr_signup" id="wstr_signup">
                            <label for="username">Username*</label>
                            <input type="text" id="username" name="username" placeholder="Your Username" required>

                            <label for="full-name">First Name, Last Name</label>
                            <input type="text" id="full-name" name="full_name" placeholder="Enter first and last name" required>

                            <label for="email">Email Address*</label>
                            <input type="email" id="email" name="email" placeholder="@Email address " required>
                            <p id="error-msg"></p>
                            <label for="password">Password*</label>
                            <input type="password" id="password" name="password" placeholder="Password" required>

                            <label for="confirm-password">Confirm Password*</label>
                            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password "
                                required>

                            <div class="checkbox-group">
                                <input type="checkbox" id="become-seller" name="become_seller">
                                <label for="become-seller">Become a Seller</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label for="terms">I have read and accepted the <a href="#">terms and conditions</a></label>
                            </div>

                            <button type="submit" name="register_user">Register</button>

                            <div class="login-link">
                                <p>Already registered? <a href="<?php echo get_home_url() . '/my-account/' ?>">Login</a></p>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="login-img">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/login-img.png" alt="login Image">
                </div>
            </div>

        <?php
        } else {
            // Check if the user is not logged in
            if (is_user_logged_in()) {
                // Redirect them to the wp-admin login page
                // $user_id = get_current_user_id();
                // // $author_url = get_author_posts_url($user_id);
                // wp_redirect(get_home_url());
                // exit;
                return;
            }
        ?>
            <div class="login-page-wrapper">
                <div class="user-details login-form-details forms_container wstr_login_column" id="login-form">
                    <div>
                        <?php
                        if (isset($_GET['new_user']) && $_GET['new_user'] == 'yes') {
                            echo ' <span class=" sg_success_msg d-flex gap-10 mb-2"><i class="bi bi-exclamation-circle  " ></i> User has been successfully created. Please login.
         </span>';
                        }
                        if (isset($_GET['reason'])) {
                            $login_err_msg = '';
                            switch ($_GET['reason']) {
                                case 'invalid_username':
                                    $login_err_msg = 'Invalid username';
                                    break;

                                case 'empty_password':
                                    $login_err_msg = 'Password is empty';
                                    break;

                                case 'empty_username':
                                    $login_err_msg = 'Username is Empty';
                                    break;

                                case 'incorrect_password':
                                    $login_err_msg = 'Incorrect Password';
                                    break;
                            }

                            echo '<span class="text-danger fw-bold">' . $login_err_msg . '</span>';
                        }
                        ?>
                        <h2 class="m-0">Welcome Back</h2>
                        <div class="col-lg-12 mb-4 login-redirect-to-register">
                            <p>Don't have an account yet. <span><a href="<?php echo home_url('/my-account?register=true') ?>">Sign
                                        up </a></span>
                            </p>
                        </div>
                        <?php
                        echo wp_login_form(
                            array(
                                'redirect' => esc_url($_SERVER['REQUEST_URI']),
                                'form_id' => 'loginform',
                                'label_username' => '',
                                'label_password' => '',
                                //  'label_username' => __('Username', 'stat-genius'),
                                //  'label_password' => __('Password', 'stat-genius'),
                                'label_remember' => __('Remember Me', 'stat-genius'),
                                'label_log_in' => __('Login', 'stat-genius'),
                                'id_username' => 'sg-username',
                                'id_password' => 'sg-password',
                                'id_remember' => 'sg-rememberme',
                                'id_submit' => 'sg-submit',
                                'remember' => true,
                                'value_username' => '',
                                'value_remember' => false,
                                'before' => '',
                                'after' => '<p><input type="checkbox" id="show-password"> ' . __('Show Password', 'stat-genius') . '</p>'

                            )
                        );
                        ?>
                        <a href="<?php echo esc_url(wp_lostpassword_url()) ?>" class="login-forgot-password">
                            <p class="text-center mt-4">Forgot password?</p>
                        </a>
                    </div>
                </div>
                <div class="login-img">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/login-img.png" alt="login Image">
                </div>
            </div>

        <?
        }
        return ob_get_clean();
    }

    public function wstr_cart_page()
    {
        ob_start();
        ?>
        <!-- Display a payment form -->
        <form id="payment-form">
            <div class="shopping-cart">
                <div id="cart-page-wrapper">
                    <?php
                    $cart_items = wstr_retrieve_cart_items();

                    if (!empty($cart_items)) {

                        foreach ($cart_items as $item) {
                            $installment_duration = '';
                            if ($item['payment_option'] == 'installment') {
                                $installment_duration = '/' . $item['installment_duration'] . ' months';
                            }
                            echo '
                            <div class="cart-item">
                                <img src="' . $item['image'] . '" alt="' . $item['title'] . '">
                                <div class="cart-item-details">
                                    <p>' . $item['title'] . '</p>
                                    <p>' . $item['currency'] . $item['price'] . '</p>
                                    <p>' . $item['payment_option'] . $installment_duration . '</p>
                                </div>
                                <button class="remove-from-cart-btn" data-product-id="' . $item['id'] . '">REMOVE</button>
                            </div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div id="payment-element">
                <!--Stripe.js injects the Payment Element-->
            </div>
            <button id="submit">
                <div class="spinner hidden" id="spinner"></div>
                <span id="button-text">Pay now</span>
            </button>
            <div id="payment-message" class="hidden"></div>
        </form>
        <!-- [DEV]: For demo purposes only, display dynamic payment methods annotation and integration checker -->
        <div id="dpm-annotation">
            <p>
                Payment methods are dynamically displayed based on customer location, order amount, and currency.&nbsp;
                <a href="#" target="_blank" rel="noopener noreferrer" id="dpm-integration-checker">Preview payment methods by transaction</a>
            </p>
        </div>
    <?php
        return ob_get_clean();
    }
















































    public function wstr_payment_success_page()
    {
        ob_start();
        return ob_get_clean();
    }
}
new wstr_shortcodes();

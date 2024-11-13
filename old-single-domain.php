<!-- <header class="wp-block-template-part site-header">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php // echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->'); ?>
</header>
<?php // wp_head(); ?> -->

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<header class="wp-block-template-part site-header">
    <?php echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->'); ?>
</header>

<div class="single-container ws-container">
    <?php
    // echo do_shortcode('[wstr-multicurrency]');
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
        $similar_domain_args = array(
            'post_type' => 'domain',
            'fields' => 'ids',
            'posts_per_page' => 3,
            'tax_query' => array(
                array(
                    'taxonomy' => 'domain_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                    'operator' => 'IN',
                )
            )
        );

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
                <?php if ($featured_image): ?>
                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($title); ?>"
                        class="img_producto">
                <?php endif; ?>
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
                    <div><?php

                    if ($logo): ?>
                            <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($title); ?>" class="logo">
                        <?php elseif ($featured_image): ?>
                            <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($title); ?>"
                                class="logo">
                        <?php endif; ?>
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
                                <p class="regular_price"><?php
                                echo get_wstr_currency();
                                echo get_wstr_regular_price(get_the_ID()); ?></p><?php
                            }
                            if (!empty($sale_price)) { ?>
                                <p class="sale_price"><?php
                                echo get_wstr_currency();
                                echo get_wstr_sale_price(get_the_ID()); ?></p><?php
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
                    <h2 class="fw-600 margin_v_35 mt_center">Domain Informationsss</h2>
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
                    <div class="single_domain_features ws_trending_cards">
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
                        <ul class="related_tag_list_wrapper ws_flex gap_10"> <?php
                        foreach ($tags as $tag) {
                            ?>
                                <li><?php
                                $tag_id = $tag->term_id;
                                $tag_name = $tag->name;
                                echo $tag_name;
                                ?>
                                </li> <?php
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
                        <img src="<?php echo $cat_image_url ?> ">
                        <h5><?php echo $category_name ?></h5>
                    </div>
                </div>
                <?php echo do_shortcode('[wstr_estimation]'); ?>
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


<footer class="wp-block-template-part site-footer">
    <?php echo do_blocks('<!-- wp:template-part {"slug":"footer","tagName":"footer","className":"site-footer"} /-->'); ?>
</footer>

<!-- </div> -->

<?php wp_footer(); ?>
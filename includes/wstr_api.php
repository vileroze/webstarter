<?php
$user_id = '';
if (!class_exists('wstr_rest_api')) {
    class wstr_rest_api
    {
        function __construct()
        {
            add_action('wp_ajax_get_domain_age', [$this, 'get_domain_age']);
            add_action('wp_ajax_get_domain_da_pa', [$this, 'get_domain_da_pa']);

            add_action('rest_api_init', array($this, 'create_rest_api_endpoint'));
        }

        private $WHOIS_SERVERS = array(
            "com" => array("whois.verisign-grs.com", "/Creation Date:(.*)/"),
            "net" => array("whois.verisign-grs.com", "/Creation Date:(.*)/"),
            "org" => array("whois.pir.org", "/Creation Date:(.*)/"),
            "info" => array("whois.afilias.info", "/Created On:(.*)/"),
            "biz" => array("whois.neulevel.biz", "/Domain Registration Date:(.*)/"),
            "us" => array("whois.nic.us", "/Domain Registration Date:(.*)/"),
            "uk" => array("whois.nic.uk", "/Registered on:(.*)/"),
            "ca" => array("whois.cira.ca", "/Creation date:(.*)/"),
            "tel" => array("whois.nic.tel", "/Domain Registration Date:(.*)/"),
            "ie" => array("whois.iedr.ie", "/registration:(.*)/"),
            "it" => array("whois.nic.it", "/Created:(.*)/"),
            "cc" => array("whois.nic.cc", "/Creation Date:(.*)/"),
            "ws" => array("whois.nic.ws", "/Domain Created:(.*)/"),
            "sc" => array("whois2.afilias-grs.net", "/Created On:(.*)/"),
            "mobi" => array("whois.dotmobiregistry.net", "/Created On:(.*)/"),
            "pro" => array("whois.registrypro.pro", "/Created On:(.*)/"),
            "edu" => array("whois.educause.net", "/Domain record activated:(.*)/"),
            "tv" => array("whois.nic.tv", "/Creation Date:(.*)/"),
            "travel" => array("whois.nic.travel", "/Domain Registration Date:(.*)/"),
            "in" => array("whois.inregistry.net", "/Created On:(.*)/"),
            "me" => array("whois.nic.me", "/Domain Create Date:(.*)/"),
            "cn" => array("whois.cnnic.cn", "/Registration Date:(.*)/"),
            "asia" => array("whois.nic.asia", "/Domain Create Date:(.*)/"),
            "ro" => array("whois.rotld.ro", "/Registered On:(.*)/"),
            "io" => array("whois.nic.io", "/Creation Date:(.*)/"),
            "co" => array("whois.nic.co", "/Creation Date:(.*)/"),
            "ai" => array("whois.nic.ai", "/Creation Date:(.*)/"),
            "tv" => array("whois.nic.tv", "/Creation Date:(.*)/"),
            "dev" => array("whois.nic.dev", "/Creation Date:(.*)/"),
            "nu" => array("whois.nic.nu", "/created:(.*)/")
        );

        private function QueryWhoisServer($whoisserver, $domain)
        {
            $port    = 43;
            $timeout = 10;
            $fp = @fsockopen($whoisserver, $port, $errno, $errstr, $timeout) or die("Socket Error " . $errno . " - " . $errstr);
            //if($whoisserver == "whois.verisign-grs.com") $domain = "=".$domain; // whois.verisign-grs.com requires the equals sign ("=") or it returns any result containing the searched string.
            fputs($fp, $domain . "\r\n");
            $out = "";
            while (!feof($fp)) {
                $out .= fgets($fp);
            }
            fclose($fp);

            $res = "";
            if ((strpos(strtolower($out), "error") === FALSE) && (strpos(strtolower($out), "not allocated") === FALSE)) {
                $rows = explode("\n", $out);
                foreach ($rows as $row) {
                    $row = trim($row);
                    if (($row != '') && ($row[0] != '#') && ($row[0] != '%')) {
                        $res .= $row . "\n";
                    }
                }
            }
            return $res;
        }
        public function get_domain_age($domain)
        {
            if (wp_doing_ajax()) {
                $domain = sanitize_text_field($_POST['domain_name']);
            }

            // $domain = sanitize_text_field($_POST['domain_name']);
            $domain = trim($domain); //remove space from start and end of domain
            if (substr(strtolower($domain), 0, 7) == "http://")
                $domain = substr($domain, 7); // remove http:// if included
            if (substr(strtolower($domain), 0, 8) == "https://")
                $domain = substr($domain, 8); // remove https:// if included
            if (substr(strtolower($domain), 0, 4) == "www.")
                $domain = substr($domain, 4); //remove www from domain
            if (preg_match("/^([-a-z0-9]{2,100}).([a-z.]{2,8})$/i", $domain)) {
                $domain_parts = explode(".", $domain);
                $tld          = strtolower(array_pop($domain_parts));
                if (!$server = $this->WHOIS_SERVERS[$tld][0]) {
                    return false;
                }
                $res = $this->QueryWhoisServer($server, $domain);
                if (preg_match($this->WHOIS_SERVERS[$tld][1], $res, $match)) {
                    date_default_timezone_set('UTC');
                    $time  = time() - strtotime($match[1]);
                    $years = floor($time / 31556926);
                    $days  = floor(($time % 31556926) / 86400);
                    if ($years == "1") {
                        $y = "1 year";
                    } else {
                        $y = $years . " years";
                    }
                    if ($days == "1") {
                        $d = "1 day";
                    } else {
                        $d = $days . " days";
                    }
                    // Handle AJAX response
                    if (wp_doing_ajax()) {
                        wp_send_json_success($y . ' ' . $d);
                    }

                    // Return value for non-AJAX call (like in footer)
                    return $y . ' ' . $d;
                } else
                    return false;
            } else
                return false;
        }

        public function get_domain_da_pa($objectURL)
        {

            if (wp_doing_ajax()) {
                $objectURL = sanitize_text_field($_POST['domain_name']); //getting domain url 
            }

            $accessID = "mozscape-749dc5236c";
            $secretKey = "1ba09be0fea28f66f04fbe3779447219";
            $expires = time() + 300;
            $stringToSign = $accessID . "\n" . $expires;
            $binarySignature = hash_hmac('sha1', $stringToSign, $secretKey, true);
            $urlSafeSignature = urlencode(base64_encode($binarySignature));
            $cols = "103079215108"; // Bit flag for Domain Authority
            $requestUrl = "http://lsapi.seomoz.com/linkscape/url-metrics/" . urlencode($objectURL) . "?Cols=" . $cols . "&AccessID=" . $accessID . "&Expires=" . $expires . "&Signature=" . $urlSafeSignature;
            $ch = curl_init($requestUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $content = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($content, true);
            $domainAuthority = $data['pda'];
            $pageAuthority = $data['upa'];
            if (wp_doing_ajax()) {
                wp_send_json_success($domainAuthority . '/' . $pageAuthority);
            }
            return $domainAuthority . '/' . $pageAuthority;
            wp_die();
        }

        public function get_desc($domain, $domain_length)
        {
            // require_once get_stylesheet_directory_uri().'/vendor/autoload.php';
            require_once __DIR__ . '/vendor/autoload.php';

            // $domain = $_POST['domain'];
            // $domain_length = $_POST['domain_length'];
            $client = new \GeminiAPI\Client('AIzaSyALuyApXEjTswAuXKB5iw-g3P_UBE6wMCw');
            $response = $client->geminiPro()->generateContent(
                // new \GeminiAPI\Resources\Parts\TextPart('explain domain name -> ' . $domain)
                // new \GeminiAPI\Resources\Parts\TextPart('Generate a detailed description of the domain name '.$domain.'. Include possible creative uses for the domain name, explaining why it is a good name and why its '.$domain_length.'-character length is advantageous. Provide specific examples and use a friendly and informative tone.')
                new \GeminiAPI\Resources\Parts\TextPart('Generate a detailed description of the domain name ' . $domain . 'What are some possible creative uses for this ' . $domain . '?  Explain the benefits of ' . $domain_length . 'character domain in paragraph.Explain, why it is a good domain name?
                    ')
            );

            $desc = $response->text();
            return $desc;
            // wp_send_json_success($desc);
            wp_die();
        }

        public function get_text_to_speech($text)
        {
            $api_url = "https://api.elevenlabs.io/v1/text-to-speech/pNInz6obpgDQGcFmaJgB"; // Adjust the output format as needed
            $request_payload = [
                "text" => $text,
                "voice_settings" => [
                    "similarity_boost" => 0.5,
                    "stability" => 0.5,
                    "style" => 0.5,
                    "use_speaker_boost" => true
                ]
            ];

            $apiKey = "sk_eedc67f1e1f786064f584e497acbe18c37cdb860905ca325"; // Replace with your actual API key
            // $apiKey = "e33b60806d6db73e934768417380b324"; // Replace with your actual API key

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($request_payload),
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "xi-api-key: " . $apiKey,
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            // if ($err) {
            //     return "Error #:" . $err;
            // } else {
            //     // var_dump($response);
            //     return $response;
            // }
            // Define file name and path
            $upload_dir = wp_upload_dir();
            $file_name = $text . '.wav';
            $file_path = $upload_dir['path'] . '/' . $file_name;
            $file_url = $upload_dir['url'] . '/' . $file_name;

            // Save audio data to the file
            file_put_contents($file_path, $response);

            // Display HTML audio player with file path
            $audio_player = '<audio controls>';
            $audio_player .= '<source src="' . $file_url . '" type="audio/wav">';
            $audio_player .= 'Your browser does not support the audio tag.';
            $audio_player .= '</audio>';

            return $audio_player;
        }
        /**
         * Function for getting current currency rate 
         * @return void
         */
        public function get_curreny_rates()
        {
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
                    } else {
                        echo 'Failed to retrieve currency data.';
                    }
                }
            }
        }

        /**
         *  Function for registering api endpoint
         * @return void
         */
        public function create_rest_api_endpoint()
        {
            //<- add this

            // for domain
            register_rest_route('wstr/v1', '/domains/', array(
                'methods' => 'GET',
                'callback' => [$this, 'wstr_premium_domains_api'],
                'permission_callback' => '__return_true', // If you want to restrict it, use a custom permission callback
            ));

            register_rest_route('wstr/v1', '/domain_fields/', array(
                'methods' => 'GET',
                'callback' => [$this, 'wstr_api_fields_callback'],
                'permission_callback' => '__return_true'
            ));

            register_rest_route('wstr/v1', '/login/', array(
                'methods' => 'GET',
                'callback' => [$this, 'wstr_logged_in_user_callback'],
                // 'permission_callback' => function() {
                //     return is_user_logged_in(); // Allow access only for logged-in users
                // }
                'permission_callback' => '__return_true'
            ));

            // for updating users 
            register_rest_route('wstr/v1', '/update-user/(?P<user_id>\d+)', array(
                'methods' => 'POST',
                'callback' => [$this, 'custom_update_user_profile'],
                // 'permission_callback' => function () {
                //     return is_user_logged_in(); // Check if the user is logged in
                // },
                'permission_callback' => '__return_true'
            ));
        }

        /**
         * Function for home page premium domains via REST API
         */
        public function wstr_premium_domains_api($request)
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
                            $percentage_discount = round($percentage_discount); // Round to 2 decimal places for readability  
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
                            $percentage_discount = round($percentage_discount); // Round to 2 decimal places for readability  
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

                        $term = get_the_terms($domain_id, 'domain_industry');
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
                            $percentage_discount = round($percentage_discount); // Round to 2 decimal places for readability  
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

        public function wstr_api_fields_callback($request)
        {

            $params = $request->get_params();
            if (isset($params['domain_name']) && $params['domain_name']) {
                $domain_name = trim(sanitize_text_field($params['domain_name']));
                $domain_age = $this->get_domain_age($domain_name);
                $da_pa = $this->get_domain_da_pa($domain_name);

                $domain_explode = explode('.', $domain_name);
                $domain_length = strlen($domain_explode[0]);
                $tld = $domain_explode[1];

                $domain_desc = $this->get_desc($domain_name, $domain_length);

                $audio = $this->get_text_to_speech($domain_name);

                $data[] = [
                    'age' => $domain_age,
                    'da_pa' => $da_pa ? $da_pa : '',
                    'length' => $domain_length ? $domain_length : '',
                    'tld' => $tld ? $tld : '',
                    // 'description' => $domain_desc ? $domain_desc : '',
                    'audio' => $audio ? $audio : '',
                ];
            }


            // Return the data in JSON formatzz
            return new WP_REST_Response($data, 200);
        }

        public function wstr_logged_in_user_callback($request)
        {
            // return get_current_user_id();
            $user_details = get_user_by('id', $GLOBALS['user_id']);
            $user_image_id = (int) get_user_meta($GLOBALS['user_id'], 'ws_profile_pic', true);
            $user_image = '';
            if ($user_image_id) {
                $user_image =  wp_get_attachment_url($user_image_id);
            }
            $data[] = [
                'id' => $user_details->data->ID ? $user_details->data->ID : '',
                'display_name' => $user_details->data->display_name ? $user_details->data->display_name : '',
                'user_email' => $user_details->data->user_email ? $user_details->data->user_email : '',
                'cap_key' => $user_details->caps ? $user_details->caps : '',
                'roles' => $user_details->roles ? $user_details->roles : '',
                'first_name' => $user_details->first_name ? $user_details->first_name : '',
                'last_name' => $user_details->last_name ? $user_details->last_name : '',
                'user_image' => $user_image,
            ];

            // Return the data in JSON formatzz
            return new WP_REST_Response($data, 200);
        }

        /**
         * Function for updating user details 
         * @param mixed $user_id
         * @return mixed
         */
        function custom_update_user_profile($request)
        {
            $current_user_id = $GLOBALS['user_id']; // Get the current logged-in user ID
            $user_id = (int) $request->get_param('user_id'); // Get the user ID from the API request
            // Check if the passed user ID matches the logged-in user ID

            if ($current_user_id !== $user_id) {
                return new WP_Error('not_allowed', 'You can only update your own profile.', array('status' => 403));
            }

            $user_data = get_userdata($user_id);
            if (!$user_data) {
                return new WP_Error('user_not_found', 'User not found.', array('status' => 404));
            }

            // Fetch the user info from the request
            // $params = $request->get_json_params();
            $params = $_POST;
            $first_name = sanitize_text_field($params['first_name']);
            $last_name = sanitize_text_field($params['last_name']);
            $display_name = sanitize_text_field($params['display_name']);
            $current_password = $params['current_password'];
            $new_password = $params['new_password'];

            // Verify the current password if provided
            if ($current_password && !wp_check_password($current_password, $user_data->user_pass, $user_id)) {
                return new WP_Error('incorrect_password', 'The current password you entered is incorrect.', array('status' => 400));
            }

            // Update user data fields
            $user_update_data = array(
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $display_name,
            );

            // Handle password update if new password is provided
            if (!empty($new_password)) {
                $user_update_data['user_pass'] = $new_password;
            }

            // Attempt to update the user data
            $updated_user_id = wp_update_user($user_update_data);

            if (is_wp_error($updated_user_id)) {
                return new WP_Error('update_failed', 'Failed to update user details.', array('status' => 500));
            }

            // Handle profile image upload (optional, handle separately)

            if (!empty($_FILES['profile_image']['name'])) {
                // Process the file upload
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                $attachment_id = media_handle_upload('profile_image', 0); // Use the name of the input

                if (is_wp_error($attachment_id)) {
                    return new WP_Error('upload_failed', 'Failed to upload profile image.', array('status' => 500));
                }

                // Set user meta for profile image
                update_user_meta($user_id, 'ws_profile_pic', $attachment_id);
            }

            return new WP_REST_Response(array('message' => 'User profile updated successfully'), 200);
        }
    }
}
new wstr_rest_api();

<?php
if (!class_exists('get_data_using_api')) {
    class get_data_using_api
    {
        function __construct()
        {
            add_action('wp_ajax_get_domain_age', [$this, 'get_domain_age']);
            add_action('wp_ajax_get_domain_da_pa', [$this, 'get_domain_da_pa']);
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
        public function get_domain_age()
        {

            $domain = $_POST['domain_name'];
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
                    wp_send_json_success($y . ' ' . $d);
                    // return "$y, $d";
                } else
                    return false;
            } else
                return false;
        }

        public function get_domain_da_pa()
        {
            $objectURL = $_POST['domain_name']; //getting domain url 
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
            wp_send_json_success($domainAuthority . '/' . $pageAuthority);
            wp_die();
        }
    }
}
new get_data_using_api();

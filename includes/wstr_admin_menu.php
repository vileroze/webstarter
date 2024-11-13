<?php

/**
 * For creating menu setting on wordpress backend
 */
class Wstr_admin_menu
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'wstr_menu'));
    }

    /** Step 1. */
    function wstr_menu()
    {
        add_menu_page('Webstarter Menu', 'Webstarter Menu', 'manage_options', 'wstr-menu', array($this, 'wstr_menu_options'));
    }

    /** Step 3. */
    function wstr_menu_options()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        if (isset($_POST['currencies'])) {
            $currencies = array_map('sanitize_text_field', $_POST['currencies']);
            update_option('wstr_currency_codes', $currencies);
        }
?>
        <div class="">
            <button class="" onclick="openCity('wstrCurrency')">Muti Currency</button>
            <button class="" onclick="openCity('Paris')">Paris</button>
            <button class="" onclick="openCity('Tokyo')">Tokyo</button>
        </div>
        <div id="wstrCurrency" class="wstr-menu">
            <form method="post">
                <h4>Multi Currency Settings</h4>
                <div id="selectCurrency">
                    <label for="currency">Select Currency</label>
                    <select id="currencyList" name="currencies[]" multiple="multiple">

                        <?php
                        $saved_currencies = get_option('wstr_currency_codes', []);
                        $currencies_list = [
                            "USD" => "US dollar",
                            "EUR" => "Euro",
                            "JPY" => "Japanese yen",
                            "GBP" => "Pound sterling",
                            "AED" => "United Arab Emirates dirham",
                            "AFN" => "Afghan afghani",
                            "ALL" => "Albanian lek",
                            "AMD" => "Armenian dram",
                            "ANG" => "Netherlands Antillean guilder",
                            "AOA" => "Angolan kwanza",
                            "ARS" => "Argentine peso",
                            "AUD" => "Australian dollar",
                            "AWG" => "Aruban florin",
                            "AZN" => "Azerbaijani manat",
                            "BAM" => "Bosnia and Herzegovina convertible mark",
                            "BBD" => "Barbadian dollar",
                            "BDT" => "Bangladeshi taka",
                            "BGN" => "Bulgarian lev",
                            "BHD" => "Bahraini dinar",
                            "BIF" => "Burundian franc",
                            "BMD" => "Bermudian dollar",
                            "BND" => "Brunei dollar",
                            "BOB" => "Bolivian boliviano",
                            "BRL" => "Brazilian real",
                            "BSD" => "Bahamian dollar",
                            "BTN" => "Bhutanese ngultrum",
                            "BWP" => "Botswana pula",
                            "BYN" => "Belarusian ruble",
                            "BZD" => "Belize dollar",
                            "CAD" => "Canadian dollar",
                            "CDF" => "Congolese franc",
                            "CHF" => "Swiss franc",
                            "CLP" => "Chilean peso",
                            "CNY" => "Chinese yuan",
                            "COP" => "Colombian peso",
                            "CRC" => "Costa Rican colón",
                            "CUP" => "Cuban peso",
                            "CVE" => "Cape Verdean escudo",
                            "CZK" => "Czech koruna",
                            "DJF" => "Djiboutian franc",
                            "DKK" => "Danish krone",
                            "DOP" => "Dominican peso",
                            "DZD" => "Algerian dinar",
                            "EGP" => "Egyptian pound",
                            "ERN" => "Eritrean nakfa",
                            "ETB" => "Ethiopian birr",
                            "FJD" => "Fijian dollar",
                            "FKP" => "Falkland Islands pound",
                            "FOK" => "Faroese króna",
                            "GEL" => "Georgian lari",
                            "GGP" => "Guernsey pound",
                            "GHS" => "Ghanaian cedi",
                            "GIP" => "Gibraltar pound",
                            "GMD" => "Gambian dalasi",
                            "GNF" => "Guinean franc",
                            "GTQ" => "Guatemalan quetzal",
                            "GYD" => "Guyanese dollar",
                            "HKD" => "Hong Kong dollar",
                            "HNL" => "Honduran lempira",
                            "HRK" => "Croatian kuna",
                            "HTG" => "Haitian gourde",
                            "HUF" => "Hungarian forint",
                            "IDR" => "Indonesian rupiah",
                            "ILS" => "Israeli new shekel",
                            "IMP" => "Isle of Man pound",
                            "INR" => "Indian rupee",
                            "IQD" => "Iraqi dinar",
                            "IRR" => "Iranian rial",
                            "ISK" => "Icelandic króna",
                            "JEP" => "Jersey pound",
                            "JMD" => "Jamaican dollar",
                            "JOD" => "Jordanian dinar",
                            "KES" => "Kenyan shilling",
                            "KGS" => "Kyrgyzstani som",
                            "KHR" => "Cambodian riel",
                            "KID" => "Kiribati dollar",
                            "KMF" => "Comorian franc",
                            "KRW" => "South Korean won",
                            "KWD" => "Kuwaiti dinar",
                            "KYD" => "Cayman Islands dollar",
                            "KZT" => "Kazakhstani tenge",
                            "LAK" => "Lao kip",
                            "LBP" => "Lebanese pound",
                            "LKR" => "Sri Lankan rupee",
                            "LRD" => "Liberian dollar",
                            "LSL" => "Lesotho loti",
                            "LYD" => "Libyan dinar",
                            "MAD" => "Moroccan dirham",
                            "MDL" => "Moldovan leu",
                            "MGA" => "Malagasy ariary",
                            "MKD" => "Macedonian denar",
                            "MMK" => "Burmese kyat",
                            "MNT" => "Mongolian tögrög",
                            "MOP" => "Macanese pataca",
                            "MRU" => "Mauritanian ouguiya",
                            "MUR" => "Mauritian rupee",
                            "MVR" => "Maldivian rufiyaa",
                            "MWK" => "Malawian kwacha",
                            "MXN" => "Mexican peso",
                            "MYR" => "Malaysian ringgit",
                            "MZN" => "Mozambican metical",
                            "NAD" => "Namibian dollar",
                            "NGN" => "Nigerian naira",
                            "NIO" => "Nicaraguan córdoba",
                            "NOK" => "Norwegian krone",
                            "NPR" => "Nepalese rupee",
                            "NZD" => "New Zealand dollar",
                            "OMR" => "Omani rial",
                            "PAB" => "Panamanian balboa",
                            "PEN" => "Peruvian sol",
                            "PGK" => "Papua New Guinean kina",
                            "PHP" => "Philippine peso",
                            "PKR" => "Pakistani rupee",
                            "PLN" => "Polish złoty",
                            "PYG" => "Paraguayan guaraní",
                            "QAR" => "Qatari riyal",
                            "RON" => "Romanian leu",
                            "RSD" => "Serbian dinar",
                            "RUB" => "Russian ruble",
                            "RWF" => "Rwandan franc",
                            "SAR" => "Saudi riyal",
                            "SBD" => "Solomon Islands dollar",
                            "SCR" => "Seychellois rupee",
                            "SDG" => "Sudanese pound",
                            "SEK" => "Swedish krona",
                            "SGD" => "Singapore dollar",
                            "SHP" => "Saint Helena pound",
                            "SLL" => "Sierra Leonean leone",
                            "SOS" => "Somali shilling",
                            "SRD" => "Surinamese dollar",
                            "SSP" => "South Sudanese pound",
                            "STN" => "São Tomé and Príncipe dobra",
                            "SYP" => "Syrian pound",
                            "SZL" => "Eswatini lilangeni",
                            "THB" => "Thai baht",
                            "TJS" => "Tajikistani somoni",
                            "TMT" => "Turkmenistan manat",
                            "TND" => "Tunisian dinar",
                            "TOP" => "Tongan paʻanga",
                            "TRY" => "Turkish lira",
                            "TTD" => "Trinidad and Tobago dollar",
                            "TVD" => "Tuvaluan dollar",
                            "TZS" => "Tanzanian shilling",
                            "UAH" => "Ukrainian hryvnia",
                            "UGX" => "Ugandan shilling",
                            "UYU" => "Uruguayan peso",
                            "UZS" => "Uzbekistani som",
                            "VES" => "Venezuelan bolívar",
                            "VND" => "Vietnamese đồng",
                            "VUV" => "Vanuatu vatu",
                            "WST" => "Samoan tālā",
                            "XAF" => "Central African CFA franc",
                            "XCD" => "East Caribbean dollar",
                            "XOF" => "West African CFA franc",
                            "XPF" => "CFP franc",
                            "YER" => "Yemeni rial",
                            "ZAR" => "South African rand",
                            "ZMW" => "Zambian kwacha",
                            "ZWL" => "Zimbabwean dollar"
                        ];

                        foreach ($currencies_list as $value => $label) {
                            // Check if the current option is in the saved currencies array
                            $selected = in_array($value, $saved_currencies) ? 'selected="selected"' : '';
                            echo "<option value='{$value}' {$selected} label='{$label}'>{$value}</option>";
                        }
                        ?>
                    </select>
                    <?php
                    if ($saved_currencies) {
                        $access_key = 'cur_live_RFDFd4STzeV5MnBBE3MFokvZmnaKEWpfAB1wT1iP';
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
                                error_log('Currency rates updated successfully.');
                            } else {
                                error_log('Failed to retrieve currency data.');
                            }
                        }
                    }
                    ?>
                </div>
                <input type="submit" value="Add Currency">
            </form>
        </div>

        <div id="Paris" class="wstr-menu" style="display:none">
            <h2>Paris</h2>
            <p>Paris is the capital of France.</p>
        </div>

        <div id="Tokyo" class="wstr-menu" style="display:none">
            <h2>Tokyo</h2>
            <p>Tokyo is the capital of Japan.</p>
        </div>


<?php


    }
}

new Wstr_admin_menu();

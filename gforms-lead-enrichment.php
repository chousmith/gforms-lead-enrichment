<?php
/*
Plugin Name: Gravity Forms Lead Enrichment Add-On
Plugin URI: http://www.ninthlink.com
Description: Integrates Gravity Forms with the Ninthlink Enrichment System ("nes") allowing form submissions to be automatically sent for further enrichment from TowerData and beyond.
Version: 1.0
Author: Ninthlink, Inc.
Author URI: http://www.ninthlink.com
Documentation: http://www.gravityhelp.com/documentation/page/GFAddOn

*/

//exit if accessed directly
if(!defined('ABSPATH')) exit;

//------------------------------------------
if (class_exists("GFForms")) {
    GFForms::include_feed_addon_framework();

    class GFLeadEnrichmentAddOn extends GFFeedAddOn {

        protected $_version = "1.0";
        protected $_min_gravityforms_version = "1.7.9999";
        protected $_slug = "gforms-lead-enrichment";
        protected $_path = "gforms-lead-enrichment/gforms-lead-enrichment.php";
        protected $_full_path = __FILE__;
        protected $_title = "Gravity Forms Lead Enrichment";
        protected $_short_title = "Lead Enrichment";
        protected $_nes_api_endpt = "gravityformsapi/forms/1/submissions";

        // custom data vars for use outside class?
        public $_avala_result = array();

        public $_debug;

        // constructor to assign plugin setting data to custom vars above
        // public function __construct() {
        //     parent::__construct();
        //     $this->_custom_product_id_list = $this->get_plugin_setting('avala_customProductIdList');
        //     $this->_default_country = $this->get_plugin_setting('avala_defaultCountry');
        // }

        // outputs the info page :: Gravity Forms -> Lead Enrichment
        public function plugin_page() {
            ?>
            <p>Lead Enrichment API Settings are handled within Gravity Forms settings page at:<br />
                <b>Forms</b> -> <b>Settings</b> -> <b><a href="<?php echo admin_url( 'admin.php?page=gf_settings&subview=gforms-lead-enrichment' ); ?>">Lead Enrichment</a></b>
            </p>
            <h2>How to use this plugin</h2>
            <h3>A step-by-step guide</h3>
            <ol>
                <li>Update Plugin Settings by going to "Forms -> Settings -> Lead Enrichment"<br>
                    You will need the following:
                    <ol style="margin-left: 20px;">
                        <li>Ninthlink Enrichment System API Endpoint URL</li>
                        <li>Ninthlink Enrichment System Public API Key</li>
                        <li>Ninthlink Enrichment System Private API Key</li>
                        <li>Ninthlink Enrichment System Site ID</li>
                        <li>Whether to run Enrichment right away or not (can be overriden in individual form feed settings)</li>
                        <!-- <li>Any custom lead categories, sources, and types not included in this plugin defaults</li>
                        <li>Your product ID list - this can be exported directly from Aimbase</li>
                        <li>Any opt-in list ID(s)</li> -->
                    </ol>
                </li>
                <li>Create your Gravity Form(s)</li>
                <li>Add the custom Feed to your form<br />
                    From the form edit/view page, go to "My Form -> Form Settings -> Lead Enrichment"</li>
                <li>Click "Add New" to create a new Feed</li>
                <li>Update your feed settings per your requirements</li>
                <li>Map necessary form fields to be submitted for Lead Enrichment.<br />
                    Hidden fields can be used to pass data not entered by the customer (ie: Brand)</li>
                <li>Set up a feed submit condition as necessary</li>
                <li>Save your changes! You are all set</li>
            </ol>
            <h3>When would I need conditions for my feeds?</h3>
            <p>A new feed must be created for every variation of form submit. Conditionals allow you to pick and choose which feed will be used at which time, for example if you are changing lead source based on entry.</p>
            <?php
        }

        /**
         *  Feed Settings Fields
         *
         *  Each form uses unique feed settings to connect with Avala. This allows extra refining for each circumstance
         *
         **/
        public function feed_settings_fields() {

            // array of settings fields
            $a = array(
                array(
                    "title"  => "Lead Enrichment Settings",
                    "fields" => array(
                        array(
                            "label"   => "Ad or Action",
                            "type"    => "text",
                            "name"    => "nesAd",
                            "tooltip" => "Ad or Action to attribute this Lead to",
                            "class"   => "medium"
                        ),
                        array(
                            "label"   => "Run Lead Enrichment",
                            "type"    => "radio",
                            "name"    => "nesRun",
                            "tooltip" => "Process Enrichment right away, or at some later point",
                            "choices" => array(
                                array("label" => "Now", "value" => 1),
                                array("label" => "Later", "value" => 2),
                                array("label" => "Never", "value" => 0),
                            )
                        ),
                        array(
                            "name" => "nesMappedFields_Contact",
                            "label" => "Map Contact Fields",
                            "type" => "field_map",
                            "tooltip" => "Map Lead Enrichment fields to Gravity Forms fields",
                            "field_map" => array(
                                array("name" => "FirstName","label" => "First Name","required" => 0),
                                array("name" => "LastName","label" => "Last Name","required" => 0),
                                array("name" => "Email","label" => "Email Address","required" => 0),
                                array("name" => "Phone","label" => "Phone","required" => 0),
                            )
                        ),
                        array(
                            "name" => "nesMappedFields_Address",
                            "label" => "Map Address Fields",
                            "type" => "field_map",
                            "tooltip" => "Map Lead Enrichment fields to Gravity Forms fields",
                            "field_map" => array(
                                array("name" => "Address1","label" => "Address","required" => 0),
                                array("name" => "Address2","label" => "Address (line 2)","required" => 0),
                                array("name" => "City","label" => "City","required" => 0),
                                array("name" => "State","label" => "State","required" => 0),
                                array("name" => "Country","label" => "Country","required" => 0),
                                array("name" => "PostalCode","label" => "Zip / Postal Code","required" => 0),
                                array("name" => "AddressInput","label" => "Address Input","required" => 0),
                            )
                        ),
                        array(
                            "name" => "nesMappedFields_AddlData",
                            "label" => "Map Additional Fields",
                            "type" => "field_map",
                            "tooltip" => "Map Lead Enrichment fields to Gravity Forms fields",
                            "field_map" => array(
                                array("name" => "SourceURL","label" => "Source URL","required" => 0),
                                array("name" => "RefURL","label" => "Referer URL","required" => 0),
                                array("name" => "UserIP","label" => "User IP","required" => 0),
                                array("name" => "UserAgent","label" => "User Agent","required" => 0),
                            ),
                        ),
                    )
                ),
                array(
                    "title"  => "Feed Settings",
                    "fields" => array(
                        array(
                            "name" => "nesCondition",
                            "label" => __("Conditional", "gforms-lead-enrichment"),
                            "type" => "feed_condition",
                            "checkbox_label" => __('Enable Feed Condition', 'gforms-lead-enrichment'),
                            "instructions" => __("Process this Avala feed if", "gforms-lead-enrichment")
                        ),
                    )
                )
            );

            // add Custom Lead Source plugin settings field to feed settings field array
            if ( $this->get_plugin_setting('avala_customLeadSource') ) {
                $custom_lead_source = explode( "\r\n", $this->get_plugin_setting('avala_customLeadSource') );
                $a[0]['fields'][2]['choices'][] = array("label" => '--- Custom Lead Source(s) ---', "value" => '');
                foreach ($custom_lead_source as $key => $value) {
                    $a[0]['fields'][2]['choices'][] = array("label" => $value, "value" => $value);
                }
            }

            // add Custom Lead Category plugin settings field to feed settings field array
            if ( $this->get_plugin_setting('avala_customLeadCategory') ) {
                $custom_lead_category = explode( "\r\n", $this->get_plugin_setting('avala_customLeadCategory') );
                $a[0]['fields'][3]['choices'][] = array("label" => '--- Custom Lead Category(s) ---', "value" => '');
                foreach ($custom_lead_category as $key => $value) {
                    $a[0]['fields'][3]['choices'][] = array("label" => $value, "value" => $value);
                }
            }

            // add Custom Lead Type plugin settings field to feed settings field array
            if ( $this->get_plugin_setting('avala_customLeadType') ) {
                $custom_lead_type = explode( "\r\n", $this->get_plugin_setting('avala_customLeadType') );
                $a[0]['fields'][4]['choices'][] = array("label" => '--- Custom Lead Type(s) ---', "value" => '');
                foreach ($custom_lead_type as $key => $value) {
                    $a[0]['fields'][4]['choices'][] = array("label" => $value, "value" => $value);
                }
            }

            return $a;
        }

        /**
         *  Columns displayed on Feed overview / list page
         *
         **/
        public function feed_list_columns() {
            // #todo
            return array(
                'nesAd' => __('Ad/Action', 'gforms-lead-enrichment'),
                'nesRun' => __('Enrich', 'gforms-lead-enrichment'),
                // 'avalaLeadsourcename' => __('Lead Source', 'gforms-lead-enrichment'),
                // 'avalaLeadcategoryname' => __('Lead Category', 'gforms-lead-enrichment'),
                // 'avalaLeadtypename' => __('Lead Type', 'gforms-lead-enrichment'),
                'nesCondition' => __('Condition(s)', 'gforms-lead-enrichment'),
            );
        }
        // customize the value of mytext before it is rendered to the list
        public function get_column_value_nesCondition( $feed ){
            $output = 'N/A';
            $rules = array();
            if ( $feed['meta']['feed_condition_conditional_logic'] == 1 ) {
                foreach ( $feed['meta']['feed_condition_conditional_logic_object']['conditionalLogic']['rules'] as $key => $value ) {
                    $rules[] = sprintf( 'field_%d %s %s' , $value['fieldId'], ( $value['operator'] === 'is' ? 'is' : 'is not' ), $value['value'] );
                }
                $andor = $feed['meta']['feed_condition_conditional_logic_object']['conditionalLogic']['logicType'] === 'any' ? 'or' : 'and';
                $output = implode(', ' . $andor . ' ', $rules);
            }
            return $output;
        }

        /**
         *  Change numeric field to textual output on overview page for human readability
         *
         **/
        public function get_column_value_nesRun($feed) {
            $output = ( $feed["meta"]["nesRun"] == 1 ) ? 'Now' : ( ( $feed["meta"]["nesRun"] == 2 ) ? 'Later' : 'None' );
            return "<b>" . $output ."</b>";
        }

        /**
         *  Plugin Settings Fields
         *
         *  These setting apply to entire plugin, not just individual feeds
         *
         *
         **/
        public function plugin_settings_fields() {
            return array(
                array(
                    "title"  => "Ninthlink Enrichment System API Settings",
                    "fields" => array(
                        array(
                            "name"    => "nes_apiUrl",
                            "tooltip" => "Base URL to connect to for Enrichment",
                            "label"   => "API Endpoint Base URL",
                            "type"    => "text",
                            "class"   => "medium"
                        ),
                        array(
                            "name"    => "nes_pubKey",
                            "tooltip" => "Ninthlink Enrichment System Public API Key",
                            "label"   => "Public API Key",
                            "type"    => "text",
                            "class"   => "medium"
                        ),
                        array(
                            "name"    => "nes_privKey",
                            "tooltip" => "Ninthlink Enrichment System Private API Key",
                            "label"   => "Private API Key",
                            "type"    => "text",
                            "class"   => "medium"
                        ),
                        array(
                            "name"    => "nes_siteID",
                            "tooltip" => "The particular Site ID for this WordPress site ". esc_url( site_url() ),
                            "label"   => "Site ID",
                            "type"    => "text",
                            "class"   => "medium"
                        ),
                        array(
                            "name"    => "nes_defaultEnrichment",
                            "tooltip" => "Whether to run Enrichment right away or not (can be overriden in individual form feed settings)",
                            "label"   => "Default Enrichment",
                            "type"    => "radio",
                            "class"   => "small",
                            "choices" => array(
                                array("label" => "Now", "value" => 1),
                                array("label" => "Later", "value" => 2),
                                array("label" => "Never", "value" => 0),
                            )
                        ),
                        array(
                            "name"    => "nes_debugMode",
                            "tooltip" => "Show debug arrays on all form submits",
                            "label"   => "Debug Mode",
                            "type"    => "radio",
                            "class"   => "small",
                            "choices" => array(
                                array("label" => "On", "value" => "1"),
                                array("label" => "Off", "value" => "0"),
                            ),
                        ),
                    ),
                ),
            );
        }

        /**
         *  Feed Processor
         *
         *  This is the nuts and bolts: all actions to happen on form submit happen here
         *  Feed processing happens after submit, but before page redirect/thanks message
         *
         **/
        public function process_feed($feed, $entry, $form){

            // working vars
            $avalaApiFeedSubmit = $feed['meta']['avalaApiFeedSubmit'];
            $url = null;

      			// current user info
      			global $current_user;
      			get_currentuserinfo();

            // get submit to location, and exit if none
            $url = $this->get_plugin_setting('nes_apiUrl');
            if ( $url == '' ) :
              return false; // do nothing - GForm submits as normal
            endif;

            // we will use Google Analytics cookies for some data if available
            if ( isset($_COOKIE['__utmz']) && !empty($_COOKIE['__utmz']) )
                $ga_cookie = $this->parse_ga_cookie( $_COOKIE['__utmz'] );

            // full data array for Lead Enrichment, with some default values
            $jsonArray = array(
                'LeadSourceName'                => $feed['meta']['avalaLeadsourcename'],
                'LeadTypeName'                  => $feed['meta']['avalaLeadtypename'],
                'LeadCategoryName'              => $feed['meta']['avalaLeadcategoryname'],
                //mapped fields - contact
                'FirstName'                     => is_user_logged_in() ? $current_user->user_firstname : '',
                'LastName'                      => is_user_logged_in() ? $current_user->user_lastname : '',
                'EmailAddress'                  => is_user_logged_in() ? $current_user->user_email : '',
                'HomePhone'                     => '',
                'MobilePhone'                   => '',
                'WorkPhone'                     => '',
                'Comments'                      => '',
                //mapped fields - address
                'Address1'                      => '',
                'Address2'                      => '',
                'City'                          => '',
                'State'                         => '',
                'County'                        => '',
                'District'                      => '',
                'CountryCode'                   => $this->get_plugin_setting('avala_defaultCountry'),
                'PostalCode'                    => ( $this->get_plugin_setting('avala_defaultPostalCode') != '' ) ? $this->get_plugin_setting('avala_defaultPostalCode') : '00000',
                //mapped fields - subscription
                'RecieveEmailCampaigns'         => '',
                'ReceiveNewsletter'             => '',
                'ReceiveSmsCampaigns'           => '',
                //mapped fields - addl data
                'AccountId'                     => '',
                'Brand'                         => '',
                'Campaign'                      => '',
                'CampaignId'                    => '',
                'DealerId'                      => '',
                'DealerNumber'                  => '',
                'ExactTargetOptInListIds'       => ( $this->get_plugin_setting('avala_defaultOptInListId') ) ? $this->get_plugin_setting('avala_defaultOptInListId') : '',
                'ExactTargetCustomAttributes'   => '',
                'LeadDate'                      => '',
                'ProductCode'                   => '',
                'ProductIdList'                 => '',
                'TriggeredSend'                 => '',
                //mapped fields - custom data
                'CustomData'                    => array(
                    'BuyTimeFrame'              => '',
                    'Condition'                 => '',
                    'CurrentlyOwn'              => '',
                    'HomeOwner'                 => '',
                    'InterestedInOwning'        => '',
                    'PayoffLeft'                => '',
                    'ProductUse'                => '',
                    'TradeInMake'               => '',
                    'TradeInYear'               => '',
                    'PromoCode'                 => '',
                    'Event'                     => '',
                    ),
                /*mapped fields - websession data
                'WebSessionData'                => array(
                    'DeliveryMethod'            => '',
                    'FormPage'                  => $entry['source_url'],
                    'IPaddress'                 => $entry['ip'],
                    'KeyWords'                  => ( isset($ga_cookie['keyword']) && !empty($ga_cookie['keyword']) ) ? $ga_cookie['keyword'] : '',
                    'Medium'                    => ( isset($feed['meta']['avalaMediumSource']) ? $feed['meta']['avalaMediumSource'] : ( ( isset($ga_cookie['medium']) && !empty($ga_cookie['medium']) ) ? $ga_cookie['medium'] : '' ) ),
                    'PagesViewed'               => $this->get_pages_viewed(),
                    'PageViews'                 => $this->get_page_views(),
                    'TimeOnSite'                => $this->get_time_on_site(),
                    'Useragent'                 => $entry['user_agent'],
                    'VisitCount'                => ( isset($ga_cookie['visits']) && !empty($ga_cookie['visits']) ) ? $ga_cookie['visits'] : 1,
                    ),
                */
            );

            // iterate over meta data mapped fields (from feed fields) and apply to the big array above
            foreach ($feed['meta'] as $k => $v) {
                $l = explode("_", $k);
                if ( $l[0] == 'avalaMappedFields' ) {
                    if ( $l[1] == 'CustomData' && array_key_exists( $l[2], $jsonArray['CustomData'] ) && !empty( $v ) ) :
                        $jsonArray['CustomData'][ $l[2] ] = $entry[ $v ];
                    elseif ( $l[1] == 'WebSession' && array_key_exists( $l[2], $jsonArray['WebSessionData'] ) && !empty( $v ) ) :
                        $jsonArray['WebSessionData'][ $l[2] ] = $entry[ $v ];
                    elseif ( array_key_exists( $l[2], $jsonArray ) && !empty( $v ) ) :
                        $jsonArray[ $l[2] ] = $entry[ $v ];
                    endif;
                }
            }

            // Remove empty ARRAY fields so we do not submit blank data
            $jsonArray['CustomData'] = array_filter( $jsonArray['CustomData'] );
            $jsonArray['WebSessionData'] = array_filter( $jsonArray['WebSessionData'] );
            $jsonArray = array_filter( $jsonArray );

            // wrap string in [ ] per Avala API requirements
            $jsonString = '[' . json_encode( $jsonArray ) . ']';

            // cURL :: this sends off the data to Avala
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
            curl_setopt($ch, CURLOPT_PROXY, null);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Content-Length: ' . strlen($jsonString) ) );
            $apiResult = curl_exec($ch);
            $httpResult = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $result = array( 0 => $httpResult, 1 => $apiResult );

            // debug things
            if ( $this->get_plugin_setting('nes_debugMode') == 1 )
            {
                $this->_avala_result['cURL'] = $result;
                $this->_avala_result['JSON'] = $jsonArray;
                $this->_avala_result['FEED'] = $feed;
                $this->_avala_result['ENTRY'] = $entry;
                add_action('wp_footer', array( $this, 'nes_debug') );
                add_filter("gform_confirmation", "nes_debug_confirm", 10, 4);
            }

        }

        /**
         *  helper functions
         *
         *  Useful functions for parsing data, formatting, etc.
         *
         **/

        // Debug builder
        public function nes_debug()
        {
            $arrays = $this->_avala_result;
            $o = '<div id="avala-gform-debug" class=""><h3>Lead Enrichment Debug Details</h3><hr>';
            foreach ($arrays as $array => $value)
            {
                $o .='<h4>'.$array.'</h4><pre>'.print_r($value, true).'</pre><hr>';
            }
            $o .= '</div>';
            if ( current_user_can( 'activate_plugins' ) )
                print($o);
        }
        public function nes_debug_confirm($confirmation, $form, $lead, $ajax)
        {
            $arrays = $this->_avala_result;
            $o = '<div id="avala-gform-debug" class="avala_confirm"><h3>Lead Enrichment Debug Details</h3><hr>';
            foreach ($arrays as $array => $value)
            {
                $o .='<h4>'.$array.'</h4><pre>'.print_r($value, true).'</pre><hr>';
            }
            $o .= '</div>';
            if ( current_user_can( 'activate_plugins' ) )
                return $o;
            return false;
        }

        // Google Analytics cookie parser
        public function parse_ga_cookie($cookie)
        {
            $values = sscanf( $cookie, "%d.%d.%d.%d.utmcsr=%[^|]|utmccn=%[^|]|utmcmd=%[^|]|utmctr=%[^|]");
            $keys = array('domain', 'timestamp', 'visits', 'sources', 'campaign', 'source', 'medium', 'keyword');
            return array_combine($keys, $values);
        }
        /*
        // get pages viewed from cookie
        public function get_pages_viewed( $pages = true )
        {
            // Custom cookie reader :: requires "nlk-custom-shortcodes" plugin to generate these tracking cookies
            if ( ! function_exists( 'is_plugin_active') )
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            if ( is_plugin_active( 'nlk-custom-shortcodes/nlk-custom-shortcodes.php' ) )
            {
                if ( !empty( $_COOKIE['__nlkpv'] ) )
                {
                    $nlkc =  json_decode( str_replace('\"', '"', $_COOKIE['__nlkpv'] ), true );
                    $i = 0;
                    $li = '';
                    foreach ($nlkc as $k => $v)
                    {
                        $li .= '<li><a href="'.$v['url'].'">'.$v['title'].'</a></li>';
                        $i++;
                    }
                    if( $pages )
                    {
                        return sprintf( '<ul>%s</ul>', $li ); // pages viewed as unordered list
                    }
                    else
                    {
                        return $i; // page views count
                    }
                }
            }
            return false; // if nlk-custom-shortcodes plugin not installed OR cookie is empty
        }

        public function get_page_views()
        {
            return $this->get_pages_viewed(false);
        }

        // get time on site from cookie
        public function get_time_on_site()
        {
            if ( !empty( $_COOKIE['__nlken'] ) )
            {
                $then = $_COOKIE['__nlken'];
                $now = time();
                $tos = $now - $then;
                return gmdate("H:i:s", $tos);
            }
            return false;
        }
        */

        // Phone number formatter
        public function format_phone( $phone = '', $format='standard', $convert = true, $trim = true )
        {
            if ( empty( $phone ) ) {
                return false;
            }
            // Strip out non alphanumeric
            $phone = preg_replace( "/[^0-9A-Za-z]/", "", $phone );
            // Keep original phone in case of problems later on but without special characters
            $originalPhone = $phone;
            // If we have a number longer than 11 digits cut the string down to only 11
            // This is also only ran if we want to limit only to 11 characters
            if ( $trim == true && strlen( $phone ) > 11 ) {
                $phone = substr( $phone, 0, 11 );
            }
            // letters to their number equivalent
            if ( $convert == true && !is_numeric( $phone ) ) {
                $replace = array(
                    '2'=>array('a','b','c'),
                    '3'=>array('d','e','f'),
                    '4'=>array('g','h','i'),
                    '5'=>array('j','k','l'),
                    '6'=>array('m','n','o'),
                    '7'=>array('p','q','r','s'),
                    '8'=>array('t','u','v'),
                    '9'=>array('w','x','y','z'),
                    );
                foreach ( $replace as $digit => $letters ) {
                    $phone = str_ireplace( $letters, $digit, $phone );
                }
            }
            $a = $b = $c = $d = null;
            switch ( $format ) {
                case 'decimal':
                case 'period':
                    $a = '';
                    $b = '.';
                    $c = '.';
                    $d = '.';
                    break;
                case 'hypen':
                case 'dash':
                    $a = '';
                    $b = '-';
                    $c = '-';
                    $d = '-';
                    break;
                case 'space':
                    $a = '';
                    $b = ' ';
                    $c = ' ';
                    $d = ' ';
                    break;
                case 'standard':
                default:
                    $a = '(';
                    $b = ') ';
                    $c = '-';
                    $d = '(';
                    break;
            }
            $length = strlen( $phone );
            // Perform phone number formatting here
            switch ( $length ) {
                case 7:
                    // Format: xxx-xxxx / xxx.xxxx / xxx-xxxx / xxx xxxx
                    return preg_replace( "/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1$c$2", $phone );
                case 10:
                    // Format: (xxx) xxx-xxxx / xxx.xxx.xxxx / xxx-xxx-xxxx / xxx xxx xxxx
                    return preg_replace( "/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$a$1$b$2$c$3", $phone );
                case 11:
                    // Format: x(xxx) xxx-xxxx / x.xxx.xxx.xxxx / x-xxx-xxx-xxxx / x xxx xxx xxxx
                    return preg_replace( "/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1$d$2$b$3$c$4", $phone );
                default:
                    // Return original phone if not 7, 10 or 11 digits long
                    return $originalPhone;
            }
        }

        /**
         *  END of AVAL API ADD-ON CLASS
         *
         **/
    }

    // Instantiate the class - this triggers everything, makes the magic happen
    $gfa = new GFLeadEnrichmentAddOn();
}

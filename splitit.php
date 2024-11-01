<?php

/*
Plugin Name: Splitit
Plugin URI: http://wordpress.org/plugins/splitit/
Description: Integrates Splitit payment method into your WooCommerce installation.
Version: 2.4.19
Author: Splitit
Text Domain: splitit
Author URI: https://www.splitit.com/
 */
add_action('plugins_loaded', 'init_splitit_method', 0);

function splitit_add_notice_function() {
	if (is_checkout() == false && is_cart() == false) {
		wc_print_notices();
	}
}

$isCustomPLugin = false;

if($isCustomPLugin){
	function splitit_prefix_plugin_update_message( $data, $response ) {
		printf(
			'<div class="update-message"><p>%s <strong style="color:red;">%s</strong></p></div>',
			__('Dear user, your Splitit plugin version has been customized specifically for your needs.', 'splitit' ),__('Please do not update the plugin version without consulting first with your Splitit Customer Success manager. The consequences might be LOSING the modification done specifically for you!', 'splitit' )
		);
	}
	add_action( 'in_plugin_update_message-splitit-installment-payments-enabler/splitit.php', 'splitit_prefix_plugin_update_message', 10, 2 );
}

add_filter('woocommerce_locate_template', 'splitit_woo_adon_plugin_template', 1, 3);
function splitit_woo_adon_plugin_template($template, $template_name, $template_path) {
	global $woocommerce;
	$_template = $template;
	if (!$template_path) {
		$template_path = $woocommerce->template_url;
	}

	$plugin_path = untrailingslashit(plugin_dir_path(__FILE__)) . '/template/woocommerce/';

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			$template_path . $template_name,
			$template_name,
		)
	);

	if (!$template && file_exists($plugin_path . $template_name)) {
		$template = $plugin_path . $template_name;
	}

	if (!$template) {
		$template = $_template;
	}

	return $template;
}

/*code to create new table and maintain IPN logss for Async*/
function splitit_create_plugin_database_table() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'splitit_logs';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE " . $table_name . " (
             `id` int(11) NOT NULL AUTO_INCREMENT,
             `user_id` int(11) DEFAULT 0,
             `order_id` int(11) unsigned NULL,
              `shipping_method_cost` varchar(255) DEFAULT NULL,
              `shipping_method_title` varchar(255) DEFAULT NULL,
              `shipping_method_id` varchar(255) DEFAULT NULL,
              `coupon_amount` varchar(255) DEFAULT NULL,
              `coupon_code` varchar(255) DEFAULT NULL,
              `tax_amount` varchar(255) DEFAULT NULL,
              `set_shipping_total` varchar(255) DEFAULT NULL,
              `set_discount_total` varchar(255) DEFAULT NULL,
              `set_discount_tax` varchar(255) DEFAULT NULL,
              `set_cart_tax` varchar(255) DEFAULT NULL,
              `set_shipping_tax` varchar(255) DEFAULT NULL,
              `set_total` varchar(255) DEFAULT NULL,
              `wc_cart` longtext,
              `get_packages` longtext,
              `chosen_shipping_methods_data` longtext,
              `ipn` varchar(255) DEFAULT NULL,
              `session_id` varchar(255) DEFAULT NULL,
              `user_data` longtext,
              `cart_items` longtext,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
        ) $charset_collate;";

		//  echo $sql;die;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	} else {
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'shipping_method_cost'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `shipping_method_cost` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'shipping_method_title'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `shipping_method_title` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'shipping_method_id'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `shipping_method_id` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'coupon_amount'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `coupon_amount` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'coupon_code'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `coupon_code` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'tax_amount'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `tax_amount` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'set_shipping_total'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `set_shipping_total` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'set_discount_total'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `set_discount_total` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'set_discount_tax'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `set_discount_tax` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'set_cart_tax'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `set_cart_tax` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'set_shipping_tax'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `set_shipping_tax` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'set_total'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `set_total` varchar(255) DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'wc_cart'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `wc_cart` longtext DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'get_packages'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `get_packages` longtext DEFAULT NULL");
		}

		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'chosen_shipping_methods_data'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `chosen_shipping_methods_data` longtext DEFAULT NULL");
		}
		$row = $wpdb->get_results("SHOW columns from " . $table_name . " like 'order_id'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . $table_name . " ADD COLUMN `order_id` int(11) unsigned NULL AFTER `user_id`");
		}
	}

}

//register_activation_hook( __FILE__, 'splitit_create_plugin_database_table' );
add_action("admin_init", 'splitit_create_plugin_database_table');
/*end*/

function init_splitit_method() {

	add_action('wp_head', 'splitit_add_notice_function');

	if (!class_exists('WC_Payment_Gateway')) {return;}

	define('Splitit_VERSION', '2.4.19');
	define('Splitit_logo_source_local', plugin_dir_url(__FILE__) . 'assets/images/Offical_Splitit_Logo.png');

	// Import helper classes
	require_once 'classes/splitit-log.php';
	require_once 'classes/splitit-settings.php';
	require_once 'classes/splitit-helper.php';
	require_once 'classes/splitit-api.php';
	require_once 'classes/splitit-checkout.php';

	// Main class
	class SplitIt extends WC_Payment_Gateway {

		/**
		 * $_instance
		 * @var mixed
		 * @access public
		 * @static
		 */
		public static $_instance = NULL;
		private static $_maxInstallments = NULL;
		private static $_API = null;

		/**
		 * Returns a new instance of self, if it does not already exist.
		 *
		 * @access public
		 * @static
		 * @return object SplitIt
		 */
		public static function get_instance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public $log;

		/**
		 * The class construct
		 *
		 * @access public
		 */
		public function __construct() {
			$this->id = 'splitit';
			$this->method_title = 'Splitit'; //checkout payment method tab title
			$this->icon = '';
			$this->order_button_text = __(' Proceed to Monthly Payment', 'woocommerce');
			$this->has_fields = false; //Can be set to true if you want payment fields to show on the checkout (if doing a direct integration).
			$this->test_item = "testing";
			$this->supports = array(
				'products',
				'refunds',
			);

			$this->log = new SplitIt_Log();

			// Load the form fields and settings
			$this->init_form_fields();
			$this->init_settings();

			// Get gateway variables: displayed as payment method title and description on front

			$learnmoreImage = '<span class="tell-me-more-image-wrapper"><img class="tell-me-more-image" src="' . plugin_dir_url(__FILE__) . 'assets/images/learn_more.png" ></span>';
            $splititLogoText = $this->settings['splitit_installment_text'] ?? '0% INTEREST MONTHLY PAYMENTS';
			$textToDisplay = "Splitit <span class=\"payment-title-checkout\">".__($splititLogoText)." <a href=\"" . $this->getHelpMeLink() . "\" id=\"tell-me-more\">" . $learnmoreImage . "</a></span>";
			$descriptionImage = '<span class="description_image"><img class="tell-me-more-image" src="' . plugin_dir_url(__FILE__) . 'assets/images/description.png" ></span>';
			$this->title = "Splitit";
			$this->description = "<script>
				(function(i,s,o,g,r,a,m){i['SplititObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window, document, 'script', '//upstream.production.splitit.com/v1/dist/upstream-messaging.js?v='+(Math.ceil(new Date().getTime()/100000)), 'splitit');
			
				splitit('init', { apiKey: '{$this->s('splitit_api_terminal_key')}', lang: 'en', currency: 'USD', currencySymbol: '$', debug: false });
			</script>
			<div data-splitit='true' data-splitit-amount='100' data-splitit-num-installments='3' data-splitit-type='product-description'></div>";
			$this->description = "<script>
				jQuery('.wc_payment_method.payment_method_splitit label').html(jQuery('.wc_payment_method.payment_method_splitit label').html().replace('Splitit','$textToDisplay'));
			</script>".
			$descriptionImage;
			$this->instructions = $this->s('instructions');

			self::$_maxInstallments = $this->s('splitit_max_installments_limit'); //set maximum installments //number by default
		}

        /**
         * @param $settings
         * @return SplitIt_API
         */
		public static function getApi($settings): SplitIt_API
        {
		    if (!self::$_API) {
                self::$_API = new SplitIt_API($settings);
            }

		    return self::$_API;
        }

        /**
         * @param SplitIt_API $api
         */
        public static function setApi(SplitIt_API $api)
        {
            self::$_API = $api;
        }

		/**
		 * Return the title for Checkout page and Admin
		 *
		 * @access public
		 * @return string
		 */
		public function get_title() {
			if (is_admin()) {
				return "SplitIt 0% INTEREST MONTHLY PAYMENTS";
			} else {
				return $this->title;
			}
		}

		/**
		 * Initiates the plugin settings form fields
		 *
		 * @access public
		 * @return array
		 */
		public function init_form_fields() {
			$this->form_fields = SplitIt_Settings::get_fields();
		}

		/**
		 * Can the order be refunded via PayPal?
		 *
		 * @param  WC_Order $order Order object.
		 * @return bool
		 */
		public function can_refund_order($order) {

			$has_api_creds = isset($this->settings['splitit_api_terminal_key']) && $this->settings['splitit_api_terminal_key'] && isset($this->settings['splitit_api_username']) && $this->settings['splitit_api_username'] && isset($this->settings['splitit_api_password']) && $this->settings['splitit_api_password'];

			return $order && get_post_meta($order->get_id(), 'installment_plan_number', true) && $has_api_creds;
		}

		/**
		 * Applies plugin hooks and filters
		 *
		 * @access public
		 * @return string
		 */
		public function hooks_and_filters() {
			//TODO: Translation?

			if (is_admin() || defined('SPLITIT_TESTING')) {
				add_action('admin_enqueue_scripts', 'SplitIt_Helper::admin_js');
				add_action('wp_ajax_splitit_check_api_credentials', array($this, 'splitit_check_api_credentials'));
				add_action('wp_ajax_fetch_prods', array($this, 'splitit_fetch_prods'));
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
				add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'splitit_add_installment_plan_number_data'), 10, 1);

				//order shipped logic
				add_action('init', array($this, 'splitit_register_shipped_order_status'));
				if ($this->s('splitit_payment_action') == 'shipped') {
					add_action('woocommerce_order_actions', array($this, 'splitit_add_order_meta_box_actions'));
				}
				add_filter('wc_order_statuses', array($this, 'splitit_add_shipped_to_order_statuses'));
				//Add callback if Shipped action called
				add_action('woocommerce_order_action_charge', array($this, 'splitit_customer_charge_callback'), 10, 1);
				//Add callback if Status changed to Shipping
				add_action('woocommerce_order_status_shipped', array($this, 'splitit_order_status_shipped_callback'), 10, 1);
			}

			//API request handlers
			add_action('woocommerce_api_splitit_payment_cancel', array($this, 'splitit_payment_cancel'));
			add_action('woocommerce_api_splitit_payment_success', array($this, 'splitit_payment_success'));
			add_action('woocommerce_api_splitit_payment_error', array($this, 'splitit_payment_error'));
			add_action('woocommerce_api_splitit_payment_success_async', array($this, 'splitit_payment_success_async'));

			add_action('woocommerce_api_splitit_checkout_validate', array($this, 'splitit_checkout_validate'));
			add_action('woocommerce_api_splitit_help', array($this, 'splitit_help'));

			//SplitIt session init and button inserting, gateway cc icons
			add_action('wp_enqueue_scripts', 'SplitIt_Helper::checkout_js');
			add_action('woocommerce_after_checkout_form', array($this, 'splitit_pass_cdn_urls'));
			add_action('woocommerce_api_splitit_scripts_on_checkout', array($this, 'splitit_scripts_on_checkout'));

			/* woocommerce cancel order hook */
			add_action('woocommerce_order_status_cancelled', array($this, 'splitit_cancel_order'), 10, 1);
			/* END woocommerce cancel order hook */
			add_filter('woocommerce_gateway_icon', array($this, 'splitit_gateway_icons'), 2, 3);
			add_action('woocommerce_email_before_order_table', array($this, 'splitit_add_content_specific_email'), 20, 4);
			if ($this->s('splitit_discount_type') == 'depending_on_cart_total') {
				add_filter('woocommerce_available_payment_gateways', array($this, 'change_payment_gateway'), 20, 1);
			}
			if ($this->s('splitit_product_option')) {
				add_filter('woocommerce_available_payment_gateways', array($this, 'product_specific_payment_gateway'), 20, 1);
			}

			//Installment price functionality init
			if ($this->s('splitit_enable_installment_price') == 'yes') {
				add_filter('woocommerce_get_price_html', array($this, 'splitit_installment_price'), 1000, 3);
				add_filter('woocommerce_product_get_regular_price', array($this, 'splitit_installment_price'), 1000, 3);
				add_filter('woocommerce_product_get_sale_price', array($this, 'splitit_installment_price'), 1000, 3);
				add_filter('woocommerce_order_amount_item_subtotal', array($this, 'splitit_installment_price'), 1000, 3);
				add_filter('woocommerce_cart_product_price', array($this, 'splitit_installment_price'), 1000, 3); //cart price column
				add_filter('woocommerce_cart_total', array($this, 'splitit_installment_total_price'), 1000, 3); //cart and checkout totals
			}

			//Debug mode init
			if ($this->s('splitit_mode_debug') == 'yes') {
				add_action('http_api_debug', array($this, 'splitit_api_request_debug'), 10, 5);
			}

			//front styles
			add_action('wp_enqueue_scripts', 'SplitIt_Helper::front_css');

		}
		/**
		 * Adds action links inside the plugin overview
		 *
		 * @access public static
		 * @return array
		 */
		public static function add_action_links($links) {
			$links = array_merge(array(
				'<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=splitit') . '">' . __('Settings', 'splitit') . '</a>',
			), $links);

			return $links;
		}

		/**
		 * Prints the admin settings form
		 *
		 * @access public
		 * @return string
		 */
		public function admin_options() {
			echo "<h3>Splitit, v" . Splitit_VERSION . "</h3>";
			echo '<a target="_blank" href="https://www.splitit.com/register?source=woo_plugin">' . __('Click here to sign up for a Splitit account.', 'splitit') . '</a>';

			do_action('splitit_settings_table_before');

			echo "<table class=\"form-table\">";
			$this->generate_settings_html();
			echo "</table";

			do_action('splitit_settings_table_after');
		}

		public function generate_settings_html($form_fields = array(), $echo = true) {
			if (empty($form_fields)) {
				$form_fields = $this->get_form_fields();
			}

			$html = '';
			foreach ($form_fields as $k => $v) {
				if ($k == 'splitit_doct') {
					$html .= '<tr valign="top" class="custom_settings" id="main_ct_container">
                            <th>Depending on cart total</th>
                            <td>
                                <table id = "tier_price_container">
                                    <tr>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Installment</th>
                                        <th>Currency</th>
                                    </tr>
                                ';
					foreach ($v as $k1 => $v1) {
						$i = 0;

						if (count((array) $v1) == 4) {
							if ($k1 == 0) {

							}
							foreach ((array) $v1 as $k2 => $v2) {
								if ($i == 0) {
									$html .= '<tr class="ct_tr">';
								}
								$type = $this->get_field_type($v2);

								if (method_exists($this, 'generate_' . $type . '_html')) {
									$html .= $this->{'generate_' . $type . '_html'}($k2, $v2, 'cart_total', $k1);
								} else {
									$html .= $this->generate_text_html($k2, $v2, 'cart_total', $k1);
								}

								if ($i == 3) {
									$html .= '</tr>';
								}
								$i++;
							}
						}
					}
					$html .= '</table>
                            </td>
                            </tr>';
				} else {
					$type = $this->get_field_type($v);

					if (method_exists($this, 'generate_' . $type . '_html')) {
						$html .= $this->{'generate_' . $type . '_html'}($k, $v);
					} else {
						$html .= $this->generate_text_html($k, $v, '', '');
					}
				}
			}

			if ($echo) {
				echo $html;
			} else {
				return $html;
			}
		}

		public function generate_text_html($key, $data, $ct = '', $i = '') {

			if ($ct == 'cart_total') {
				$field_key = $this->get_field_key($key);

				$defaults = array(
					'title' => '',
					'disabled' => false,
					'class' => '',
					'css' => '',
					'placeholder' => '',
					'type' => 'text',
					'desc_tip' => false,
					'description' => '',
					'custom_attributes' => array(),
				);

				$data = wp_parse_args($data, $defaults);

				ob_start();
				$readonly = "";
				if ($key == 'ct_currency') {
					$txtValue = get_woocommerce_currency();
					$readonly = "readonly";
				} else {
					if (isset($this->settings['splitit_doct'][$key][$i])) {
						$txtValue = $this->settings['splitit_doct'][$key][$i];
					} else {
						$txtValue = $data['default'];
					}
				}

				?>
                <td style="padding:0;">
                    <fieldset>
                        <input width="5" class="input-text <?php echo esc_attr($data['class']); ?>" type="<?php echo esc_attr($data['type']); ?>" <?php echo $readonly; ?> name="<?php echo esc_attr($field_key); ?>[]" id="<?php echo esc_attr($field_key); ?>" style="<?php echo esc_attr($data['css']); ?>" value="<?php echo $txtValue; ?>" placeholder="<?php echo esc_attr($data['placeholder']); ?>" <?php disabled($data['disabled'], true);?> <?php echo $this->get_custom_attribute_html($data); ?> />
                        <?php echo $this->get_description_html($data); ?>
                    </fieldset>
                </td>
                <?php

				return ob_get_clean();
			} else {
				$field_key = $this->get_field_key($key);
				$defaults = array(
					'title' => '',
					'disabled' => false,
					'class' => '',
					'css' => '',
					'placeholder' => '',
					'type' => 'text',
					'desc_tip' => false,
					'description' => '',
					'custom_attributes' => array(),
				);

				$data = wp_parse_args($data, $defaults);

				ob_start();
				?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
                        <?php echo $this->get_tooltip_html($data); ?>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
                            <?php
                $textValue = esc_attr($this->get_option($key));
				if ($key == 'splitit_product_sku_list' && $textValue == "Array") {
					/*print_r($this->get_option( $key,array() ));*/
					$textOptions = $this->get_option($key, array());
					$textOptionIds = array();
					if (is_array($textOptions)) {
						foreach ($textOptions as $textOption) {
							array_push($textOptionIds, wc_get_product_id_by_sku($textOption));
						}
						$textValue = implode(',', $textOptionIds);
					}
				} elseif ($key == 'splitit_product_sku_list' && strstr($textValue, "Array") !== false) {
					$textValue = str_replace("Array", "", $textValue);
					$textValue = implode(',', array_filter(explode(',', $textValue)));
				}
				?>
                            <input class="input-text regular-input <?php echo esc_attr($data['class']); ?>" type="<?php echo esc_attr($data['type']); ?>" name="<?php echo esc_attr($field_key); ?>" id="<?php echo esc_attr($field_key); ?>" style="<?php echo esc_attr($data['css']); ?>" value="<?php echo $textValue; ?>" placeholder="<?php echo esc_attr($data['placeholder']); ?>" <?php disabled($data['disabled'], true);?> <?php echo $this->get_custom_attribute_html($data); ?> />
                            <?php echo $this->get_description_html($data); ?>
                        </fieldset>
                    </td>
                </tr>
                <?php

				return ob_get_clean();
			}
		}

		public function generate_select_html($key, $data, $ct = '', $i = '') {
			if ($ct == 'cart_total') {
				$field_key = $this->get_field_key($key);
				$defaults = array(
					'title' => '',
					'disabled' => false,
					'class' => '',
					'css' => '',
					'placeholder' => '',
					'type' => 'text',
					'desc_tip' => false,
					'description' => '',
					'custom_attributes' => array(),
					'options' => array(),
				);

				$data = wp_parse_args($data, $defaults);

				ob_start();
				?>
                <td style="padding:0;">
                    <fieldset>
                        <select class="select <?php echo esc_attr($data['class']); ?>" name="<?php echo esc_attr($field_key); ?>[]" id="<?php echo esc_attr($field_key); ?>" style="<?php echo esc_attr($data['css']); ?>" <?php disabled($data['disabled'], true);?> <?php echo $this->get_custom_attribute_html($data); ?>>
                            <?php foreach ((array) $data['options'] as $option_key => $option_value): ?>
                                <option value="<?php echo esc_attr($option_key); ?>" <?php selected($option_key, esc_attr($this->settings['splitit_doct'][$key][$i]));?>><?php echo esc_attr($option_value); ?></option>
                            <?php endforeach;?>
                        </select>
                        <?php echo $this->get_description_html($data); ?>
                    </fieldset>
                </td>
                <?php

				return ob_get_clean();
			} else {
				$field_key = $this->get_field_key($key);
				$defaults = array(
					'title' => '',
					'disabled' => false,
					'class' => '',
					'css' => '',
					'placeholder' => '',
					'type' => 'text',
					'desc_tip' => false,
					'description' => '',
					'custom_attributes' => array(),
					'options' => array(),
				);

				$data = wp_parse_args($data, $defaults);

				ob_start();
				?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
                        <?php echo $this->get_tooltip_html($data); ?>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
                            <select class="select <?php echo esc_attr($data['class']); ?>" name="<?php echo esc_attr($field_key); ?>" id="<?php echo esc_attr($field_key); ?>" style="<?php echo esc_attr($data['css']); ?>" <?php disabled($data['disabled'], true);?> <?php echo $this->get_custom_attribute_html($data); ?>>
                                <?php foreach ((array) $data['options'] as $option_key => $option_value): ?>
                                    <option value="<?php echo esc_attr($option_key); ?>" <?php selected($option_key, esc_attr($this->get_option($key)));?>><?php echo esc_attr($option_value); ?></option>
                                <?php endforeach;?>
                            </select>
                            <?php echo $this->get_description_html($data); ?>
                        </fieldset>
                    </td>
                </tr>
                <?php

				return ob_get_clean();
			}
		}

		public function generate_multiselect_html($key, $data, $ct = '', $i = '') {
			if ($ct == 'cart_total') {
				$field_key = $this->get_field_key($key);
				$defaults = array(
					'title' => '',
					'disabled' => false,
					'class' => '',
					'css' => '',
					'placeholder' => '',
					'type' => 'text',
					'desc_tip' => false,
					'description' => '',
					'custom_attributes' => array(),
					'select_buttons' => false,
					'options' => array(),
				);
				$data = wp_parse_args($data, $defaults);
				$value = (array) $this->get_option($key, array());
				ob_start();
				if ($key == 'ct_instllment') {
					if (isset($this->settings['splitit_doct'][$key][$i])) {
						$mulSelValue = $this->settings['splitit_doct'][$key][$i];
					} else {
						$mulSelValue = $data['default'];
					}
				} else {
					$mulSelValue = $this->settings['splitit_doct'][$key][$i];
				}
				?>
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
                            <select multiple="multiple" class="multiselect <?php echo esc_attr($data['class']); ?>" name="<?php echo esc_attr($field_key) . '_' . $i; ?>[]" id="<?php echo esc_attr($field_key); ?>" style="<?php echo esc_attr($data['css']); ?>" <?php disabled($data['disabled'], true);?> <?php echo $this->get_custom_attribute_html($data); ?>>
                                <?php if (isset($data)) {
					foreach ((array) $data['options'] as $option_key => $option_value): ?>
                                    <option value="<?php echo esc_attr($option_key); ?>" <?php if (count($this->settings['splitit_doct'][$key][$i]) > 0) {selected(in_array($option_key, $mulSelValue), true);}?>><?php echo esc_attr($option_value); ?></option>
                                <?php endforeach;}?>
                            </select>
                            <?php echo $this->get_description_html($data); ?>
                            <?php if ($data['select_buttons']): ?>
                                <br/><a class="select_all button" href="#"><?php _e('Select all', 'woocommerce');?></a> <a class="select_none button" href="#"><?php _e('Select none', 'woocommerce');?></a>
                            <?php endif;?>
                        </fieldset>
                    </td>
                <?php

				return ob_get_clean();
			} else {

				$field_key = $this->get_field_key($key);
				$defaults = array(
					'title' => '',
					'disabled' => false,
					'class' => '',
					'css' => '',
					'placeholder' => '',
					'type' => 'text',
					'desc_tip' => false,
					'description' => '',
					'custom_attributes' => array(),
					'select_buttons' => false,
					'options' => array(),
				);

				$data = wp_parse_args($data, $defaults);
				$value = (array) $this->get_option($key, array());
				if ($key == 'splitit_cc' && count($value) <= 0) {
					$value = $data['default'];
				}

				ob_start();
				?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
                        <?php echo $this->get_tooltip_html($data); ?>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
                            <select multiple="multiple" class="multiselect <?php echo esc_attr($data['class']); ?>" name="<?php echo esc_attr($field_key); ?>[]" id="<?php echo esc_attr($field_key); ?>" style="<?php echo esc_attr($data['css']); ?>" <?php disabled($data['disabled'], true);?> <?php echo $this->get_custom_attribute_html($data); ?>>
                                <?php foreach ((array) $data['options'] as $option_key => $option_value): ?>
                                    <option value="<?php echo esc_attr($option_key); ?>" <?php selected(in_array($option_key, $value), true);?>><?php echo esc_attr($option_value); ?></option>
                                <?php endforeach;?>
                            </select>
                            <?php echo $this->get_description_html($data); ?>
                            <?php if ($data['select_buttons']): ?>
                                <br/><a class="select_all button" href="#"><?php _e('Select all', 'woocommerce');?></a> <a class="select_none button" href="#"><?php _e('Select none', 'woocommerce');?></a>
                            <?php endif;?>
                        </fieldset>
                    </td>
                </tr>
                <?php

				return ob_get_clean();
			}
		}

		public function process_admin_options() {
			$this->init_settings();

			$post_data = $this->get_post_data();

			foreach ($this->get_form_fields() as $key => $field) {

				if ('title' !== $this->get_field_type($field)) {
					try {
						if ($key == 'splitit_doct') {
							if (isset($post_data['woocommerce_splitit_ct_instllment_0'])) {
								$instArr[] = $post_data['woocommerce_splitit_ct_instllment_0'];
							} else {
								$instArr[] = array();
							}
							if (isset($post_data['woocommerce_splitit_ct_instllment_1'])) {
								$instArr[] = $post_data['woocommerce_splitit_ct_instllment_1'];
							} else {
								$instArr[] = array();
							}
							if (isset($post_data['woocommerce_splitit_ct_instllment_2'])) {
								$instArr[] = $post_data['woocommerce_splitit_ct_instllment_2'];
							} else {
								$instArr[] = array();
							}
							if (isset($post_data['woocommerce_splitit_ct_instllment_3'])) {
								$instArr[] = $post_data['woocommerce_splitit_ct_instllment_3'];
							} else {
								$instArr[] = array();
							}
							if (isset($post_data['woocommerce_splitit_ct_instllment_4'])) {
								$instArr[] = $post_data['woocommerce_splitit_ct_instllment_4'];
							} else {
								$instArr[] = array();
							}
							$newArr = array(
								'ct_from' => $post_data['woocommerce_splitit_ct_from'],
								'ct_to' => $post_data['woocommerce_splitit_ct_to'],
								'ct_instllment' => $instArr,
								'ct_currency' => $post_data['woocommerce_splitit_ct_currency'],
							);
							$this->settings[$key] = $newArr;
						} else {
							$this->settings[$key] = $this->get_field_value($key, $field, $post_data);
						}
					} catch (Exception $e) {
						$this->add_error($e->getMessage());
					}
				}
			}
			//echo '<pre>';print_r($this->settings);die;
			return update_option($this->get_option_key(), apply_filters('woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings));
		}

		//----------------------------------
		//START FOR WOOCOMMERCE VERION 2.5.3
		//----------------------------------
		/*
	        public function get_field_type( $field ) {
	            return empty( $field['type'] ) ? 'text' : $field['type'];
	        }

	        public function get_post_data() {
	            if ( ! empty( $this->data ) && is_array( $this->data ) ) {
	                return $this->data;
	            }
	            return $_POST;
	        }

	        public function get_field_value( $key, $field, $post_data = array() ) {
	            $type      = $this->get_field_type( $field );
	            $field_key = $this->get_field_key( $key );
	            $post_data = empty( $post_data ) ? stripslashes_deep($_POST) : $post_data;
	            $value     = isset( $post_data[ $field_key ] ) ? $post_data[ $field_key ] : null;

	            // Look for a validate_FIELDID_field method for special handling
	            if ( is_callable( array( $this, 'validate_' . $key . '_field' ) ) ) {
	                return $this->{'validate_' . $key . '_field'}( $key, $value );
	            }

	            // Look for a validate_FIELDTYPE_field method
	            if ( is_callable( array( $this, 'validate_' . $type . '_field' ) ) ) {
	                return $this->{'validate_' . $type . '_field'}( $key, $value );
	            }

	            // Fallback to text
	            return $this->validate_text_field( $key, $value );
	        }

	        public function get_option_key() {
	            return $this->plugin_id . $this->id . '_settings';
*/
		//---------------------------------
		//ENDS FOR WOOCOMMERCE VERION 2.5.3
		//---------------------------------

		/**
		 * Prints out the description of the gateway. Also adds two checkboxes for viaBill/creditcard for customers to choose how to pay.
		 *
		 * @access public
		 * @return void
		 */
		public function payment_fields() {
			if ($this->description) {
				echo wptexturize($this->description);
			}

			echo '<div class="powered-by-splitit"><img width="192px" src="data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNzIgMzEuNSI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmb250LXNpemU6OS44MXB4O2ZvbnQtZmFtaWx5Ok9wZW5TYW5zLVNlbWlCb2xkLCBPcGVuIFNhbnM7Zm9udC13ZWlnaHQ6NzAwO30uY2xzLTEsLmNscy0ye2ZpbGw6Izk1OGRjNDt9PC9zdHlsZT48L2RlZnM+PHRpdGxlPnNwbGl0aXRfbW9udGhseV9wYXltZW50c19iYW5uZXI8L3RpdGxlPjxwYXRoIGQ9Ik0xODguNTQsMjYuMTlhMy41NSwzLjU1LDAsMCwxLTEuNDksMy4wOEE2LjgzLDYuODMsMCwwLDEsMTgzLDMwLjM0YTcuNjMsNy42MywwLDAsMS0zLjM4LS43LDUuNyw1LjcsMCwwLDEtMi4yMS0xLjlsLS45MSwxYTcuNjEsNy42MSwwLDAsMCw2LjIyLDIuNywxMC42MiwxMC42MiwwLDAsMCwyLjg4LS4zN0E2Ljg4LDYuODgsMCwwLDAsMTg3LjksMzBhNSw1LDAsMCwwLDEuNTItMS43OCw1LjM0LDUuMzQsMCwwLDAsLjU0LTIuNDQsNC40LDQuNCwwLDAsMC0uMDUtLjY1aC0xLjU3QTIuNTksMi41OSwwLDAsMSwxODguNTQsMjYuMTlaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNjkuNSAtNS4yNSkiLz48cGF0aCBkPSJNMTg4LjU3LDIzLjFhNS45LDUuOSwwLDAsMC0xLjYxLS43OWMtLjYzLS4yMi0xLjMzLS40MS0yLjA5LS42cy0xLjMyLS4zNC0xLjg1LS41MWE1Ljc0LDUuNzQsMCwwLDEtMS4zMy0uNTksMi4zLDIuMywwLDAsMS0uOC0uNzgsMi4xOSwyLjE5LDAsMCwxLS4yNy0xLjEyLDQuNzgsNC43OCwwLDAsMSwuMzItMS43NSwzLjQ1LDMuNDUsMCwwLDEsMS0xLjM4LDQuOTIsNC45MiwwLDAsMSwxLjczLS45Myw4LjIyLDguMjIsMCwwLDEsMi41NS0uMzVjMiwwLDMuNDQuNjYsNC4yNSwybC44OC0xYTUsNSwwLDAsMC0yLTEuNTksNy4zMiw3LjMyLDAsMCwwLTIuODctLjUyLDkuNzYsOS43NiwwLDAsMC0yLjg3LjQxLDYuNzksNi43OSwwLDAsMC0yLjI5LDEuMTcsNS40Myw1LjQzLDAsMCwwLTIuMDgsNC4zOCwzLjA2LDMuMDYsMCwwLDAsLjMyLDEuNDQsMi45NCwyLjk0LDAsMCwwLC45MiwxLDYuNTgsNi41OCwwLDAsMCwxLjQ4LjczLDE5LjQzLDE5LjQzLDAsMCwwLDIsLjU2QTExLjkzLDExLjkzLDAsMCwxLDE4Ny4yOCwyNGgyLjE4QTMuNDksMy40OSwwLDAsMCwxODguNTcsMjMuMVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC02OS41IC01LjI1KSIvPjxwYXRoIGQ9Ik0xOTUsMjIuNTdhMy40OCwzLjQ4LDAsMCwxLC44Mi0xLjM0LDcuMjgsNy4yOCwwLDAsMSwxLjMzLTEuMTQsNy41Miw3LjUyLDAsMCwxLDEuNTEtLjc5LDQuMzQsNC4zNCwwLDAsMSwxLjQ1LS4yOSwzLjMzLDMuMzMsMCwwLDEsMS41OS4zNywzLjgyLDMuODIsMCwwLDEsMS4yMiwxLDQuOTEsNC45MSwwLDAsMSwuNzgsMS40Nyw1LjgyLDUuODIsMCwwLDEsLjI4LDEuNzhjMCwuMTMsMCwuMjUsMCwuMzdoMS4zYzAtLjE4LDAtLjM2LDAtLjU1YTcuMTQsNy4xNCwwLDAsMC0uMzEtMi4xMSw1LjgxLDUuODEsMCwwLDAtLjkxLTEuNzksNC41MSw0LjUxLDAsMCwwLTEuNDctMS4yNCw0LjI5LDQuMjksMCwwLDAtMi0uNDUsNS43NSw1Ljc1LDAsMCwwLTIuODcuOCw4LjYxLDguNjEsMCwwLDAtMi40NywyLjA3bC41Ni0yLjY1aC0xLjE0TDE5My40NCwyNGgxLjI1WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTY5LjUgLTUuMjUpIi8+PHBhdGggZD0iTTIwMy41LDI2LjA3YTcuNTcsNy41NywwLDAsMS0xLjMyLDIuMTgsNy4zMSw3LjMxLDAsMCwxLTEuOTMsMS41OCw0Ljg2LDQuODYsMCwwLDEtMi4zNC42MSwzLjU0LDMuNTQsMCwwLDEtMS4zOC0uMjcsMy42NCwzLjY0LDAsMCwxLTEuMTQtLjc1LDQuNjksNC42OSwwLDAsMS0uODQtMS4xMiw1LjE5LDUuMTksMCwwLDEtLjQ5LTEuMzdsLjM4LTEuNzdIMTkzLjJsLTIuNDYsMTEuNTlIMTkybDEuNzUtOC4yN2E1LjIyLDUuMjIsMCwwLDAsMS41NCwyLjIsNCw0LDAsMCwwLDIuNjYuOSw2LjIxLDYuMjEsMCwwLDAsMi44My0uNjgsNy44NSw3Ljg1LDAsMCwwLDIuMzQtMS44NCw4LjkyLDguOTIsMCwwLDAsMS41OC0yLjYsOS4yMyw5LjIzLDAsMCwwLC40LTEuM2gtMS4zMkE2LjM5LDYuMzksMCwwLDEsMjAzLjUsMjYuMDdaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNjkuNSAtNS4yNSkiLz48cG9seWdvbiBwb2ludHM9IjE0Mi42OCA3LjUgMTQxLjQxIDcuNSAxMzkuMDQgMTguNzYgMTQwLjMgMTguNzYgMTQyLjY4IDcuNSIvPjxwYXRoIGQ9Ik0yMDcuNDcsMjkuMDlhMy4xOSwzLjE5LDAsMCwwLS4wOC42NCwxLjcsMS43LDAsMCwwLC40OCwxLjI4LDEuODIsMS44MiwwLDAsMCwxLjM1LjQ3LDcsNywwLDAsMCwxLjA3LS4xMSw1LjQ5LDUuNDksMCwwLDAsMS4wOC0uM3YtMWEzLjg5LDMuODksMCwwLDEtLjcxLjE5LDQuNDYsNC40NiwwLDAsMS0uNjYuMDYsMS4yNywxLjI3LDAsMCwxLS45Mi0uMywxLjEsMS4xLDAsMCwxLS4zMi0uODQsMS41NSwxLjU1LDAsMCwxLDAtLjIzLDIuMjMsMi4yMywwLDAsMSwwLS4yM2wuNzUtMy41NUgyMDguM1oiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC02OS41IC01LjI1KSIvPjxwb2x5Z29uIHBvaW50cz0iMTQzLjg5IDI2LjA4IDE0NS4xNiAyNi4wOCAxNDYuNDUgMTkuOTEgMTQ1LjE5IDE5LjkxIDE0My44OSAyNi4wOCIvPjxwb2x5Z29uIHBvaW50cz0iMTQ5LjA4IDcuNSAxNDcuODEgNy41IDE0Ny4zMyA5Ljg0IDE0OC42IDkuODQgMTQ5LjA4IDcuNSIvPjxwb2x5Z29uIHBvaW50cz0iMTQ3Ljk0IDEyLjg1IDE0Ni42NyAxMi44NSAxNDUuNDMgMTguNzYgMTQ2LjY5IDE4Ljc2IDE0Ny45NCAxMi44NSIvPjxwb2x5Z29uIHBvaW50cz0iMTUzLjUgMTMuODkgMTU2LjQxIDEzLjg5IDE1Ni42NCAxMi44NSAxNTMuNzMgMTIuODUgMTU0LjY3IDguMzEgMTUzLjQgOC4zMSAxNTIuNDcgMTIuODUgMTUwLjcgMTIuODUgMTUwLjQ3IDEzLjg5IDE1Mi4yNCAxMy44OSAxNTEuMiAxOC43NiAxNTIuNDcgMTguNzYgMTUzLjUgMTMuODkiLz48cGF0aCBkPSJNMjE5LjYxLDI5LjExYzAsLjExLDAsLjIsMCwuM2EyLjQ2LDIuNDYsMCwwLDAsMCwuMjYsMS42LDEuNiwwLDAsMCwuNTksMS4zNSwyLjI0LDIuMjQsMCwwLDAsMS40Ni40Niw0Ljg5LDQuODksMCwwLDAsMS0uMDksNC42NSw0LjY1LDAsMCwwLC44NC0uMjNjLjI1LS4wOS40Ny0uMTguNjQtLjI2YTMsMywwLDAsMCwuMzctLjIxbC0uMTUtMWExLjA3LDEuMDcsMCwwLDEtLjI3LjE0bC0uNDYuMjFhNSw1LDAsMCwxLS42Mi4xOSwzLjQsMy40LDAsMCwxLS43My4wOCwxLjM1LDEuMzUsMCwwLDEtLjg5LS4zLDEsMSwwLDAsMS0uMzctLjg4LDYuODcsNi44NywwLDAsMSwuMTgtMWwuNjItMi45NGgtMS4yN1oiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC02OS41IC01LjI1KSIvPjxwb2x5Z29uIHBvaW50cz0iMTYzLjQ3IDcuNSAxNjAuNjkgNy41IDE2MC4wNiAxMC41OCAxNjIuODQgMTAuNTggMTYzLjQ3IDcuNSIvPjxwb2x5Z29uIHBvaW50cz0iMTYyLjM4IDEyLjc3IDE1OS42IDEyLjc3IDE1OC4zMyAxOC43NiAxNjEuMTEgMTguNzYgMTYyLjM4IDEyLjc3Ii8+PHBvbHlnb24gcG9pbnRzPSIxNTYuNzcgMjYuMDggMTU5LjU1IDI2LjA4IDE2MC44NyAxOS45MSAxNTguMDggMTkuOTEgMTU2Ljc3IDI2LjA4Ii8+PHBhdGggZD0iTTIzNy4yOSwyNS4xNkgyMzQuNWwtLjcyLDMuMzRhNCw0LDAsMCwwLS4xLjg0LDEuOTEsMS45MSwwLDAsMCwuNzQsMS42NywzLDMsMCwwLDAsMS44Mi41Miw3Ljg0LDcuODQsMCwwLDAsMi0uMjYsNi4xMiw2LjEyLDAsMCwwLDEuNTQtLjYzbC0uMS0yLjIxLS44MS4zM2EzLjI1LDMuMjUsMCwwLDEtMS4xMS4yLDEuMTUsMS4xNSwwLDAsMS0uNzQtLjIzLjg2Ljg2LDAsMCwxLS4zLS43MywxLjcxLDEuNzEsMCwwLDEsLjA1LS4zOVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC02OS41IC01LjI1KSIvPjxwb2x5Z29uIHBvaW50cz0iMTY4Ljg0IDE0LjkzIDE3MS41NCAxNC45MyAxNzIgMTIuNzcgMTY5LjI3IDEyLjc3IDE3MC4yIDguMzkgMTY3LjQyIDguMzkgMTY2LjQ4IDEyLjc3IDE2NC44NCAxMi43NyAxNjQuMzYgMTQuOTMgMTY2LjA1IDE0LjkzIDE2NS4yNCAxOC43NiAxNjguMDMgMTguNzYgMTY4Ljg0IDE0LjkzIi8+PHRleHQgY2xhc3M9ImNscy0xIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLjE3IDIyLjMyKSI+TW9udGhseSBQYXltZW50cyBCeSA8L3RleHQ+PHJlY3QgY2xhc3M9ImNscy0yIiB3aWR0aD0iMTcyIiBoZWlnaHQ9IjIiLz48cG9seWxpbmUgY2xhc3M9ImNscy0yIiBwb2ludHM9IjkzIDEuNSA4NiA4LjUgNzkgMS41Ii8+PC9zdmc+"/></div>';
		}

		/***************************************************************************************************************
			         * Purchase on shipped logic
		*/

		/**
		 * Add Order action to Order action meta box
		 */

		public function splitit_add_order_meta_box_actions($actions) {
			$actions['charge'] = __('[Splitit] Charge customer', 'splitit');
			return $actions;
		}

		/**
		 * Register new status
		 */
		public function splitit_register_shipped_order_status() {
			register_post_status('wc-shipped', array(
				'label' => __('Shipped', 'splitit'),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop('Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>'),
			));
		}

		/**
		 * Adds new Order status - Shipped in Order statuses
		 */
		public function splitit_add_shipped_to_order_statuses($order_statuses) {
			$new_order_statuses = array();
			// add new order status after Completed
			foreach ($order_statuses as $key => $status) {
				$new_order_statuses[$key] = $status;
				if ('wc-completed' === $key) {
					$new_order_statuses['wc-shipped'] = __('Shipped', 'splitit');
				}
			}
			return $new_order_statuses;
		}

		public function splitit_customer_charge_callback($order) {

			if ($this->settings['splitit_payment_action'] == 'shipped') {
				//Here order id is sent as parameter
				$order_meta = get_post_custom($order->id);
				//echo '<pre>'; print_r($order_meta); die;
				if (isset($order_meta['installment_plan_number']) && !empty($order_meta['installment_plan_number'][0])) {

                    $api = self::getApi($this->settings);
					$session = $api->login();
					$result = $api->capture($order_meta['installment_plan_number'][0], $session);
					if (is_array($result) && isset($result['error'])) {
						//error
						$order->add_order_note('[Splitit] ' . $result['error']);
					} else {
						$order->add_order_note('[Splitit] Order successfully captured!');
					}
				}
			}

		}

		public function splitit_order_status_shipped_callback($order_id) {
			if ($this->s('splitit_payment_action') == 'shipped') {
				//Here order id is sent as parameter
				$order_meta = get_post_custom($order_id);
				if (isset($order_meta['installment_plan_number']) && !empty($order_meta['installment_plan_number'][0])) {
					$api = self::getApi($this->settings);
					$session = $api->login();
                    $api->capture($order_meta['installment_plan_number'][0], $session);
				}
			}
		}

		/***************************************************************************************************************
			         * API/AJAX call handlers section
		*/

		/**
		 * Called by ajax from checkout.
		 * Initialize SplitIt scripts via ajax request
		 *
		 * @access public
		 */
		public function splitit_scripts_on_checkout() {
            WC()->cart->calculate_totals();
			$checkout_fields_post = stripslashes_deep($_POST);

			//trying to receive checkout fields data from post
			if (count($checkout_fields_post)) {
				$checkout_fields = array();

				//add billing data to shipping if shipping is same as billing
				$skip_shipping = (isset($checkout_fields_post['ship-to-different-address']) && isset($checkout_fields_post['ship-to-different-address'][1]) && ($checkout_fields_post['ship-to-different-address'][1] == 1)) ? false : true;
				unset($checkout_fields['ship-to-different-address']);
				if ($skip_shipping) {
					foreach ($checkout_fields_post as $f => $d) {
						$type = explode('_', $f);
						if ($type[0] == 'shipping') {
							$billing_field = str_replace('shipping', 'billing', $f);
							$checkout_fields_post[$f] = $checkout_fields_post[$billing_field];
						}
					}
				}

				foreach ($checkout_fields_post as $field_name => $label_value) {
					$checkout_fields[$field_name] = isset($label_value[1]) ? $label_value[1] : $label_value;
				}
				//echo "<pre>"; print_r($checkout_fields);die;
				$order_data = array(
					'Address' => trim($checkout_fields['billing_address_1_field']),
					'Address2' => trim(isset($checkout_fields['billing_address_2_field']) ? $checkout_fields['billing_address_2_field'] : ''),
					'Zip' => (isset($checkout_fields['billing_postcode_field'])&&$checkout_fields['billing_postcode_field'])?trim($checkout_fields['billing_postcode_field']):(WC()->customer->get_shipping_country() == 'IE'?'00000':''),
					'AmountBeforeFees' => WC()->cart->total,
					'ConsumerFullName' => trim($checkout_fields['billing_first_name_field'] . ' ' . $checkout_fields['billing_last_name_field']),
					'Email' => trim($checkout_fields['billing_email_field']),
					'City' => trim($checkout_fields['billing_city_field']),
					'State' => trim(isset($checkout_fields['billing_state_field']) ? $checkout_fields['billing_state_field'] : ''),
					'Country' => trim($checkout_fields['billing_country_field']),
					'Phone' => trim($checkout_fields['billing_phone_field']),
				);

				//this shouldn`t happen, but in case of: no post data contained (user flushed cookie?)
				// create empty address data, so user will be able to fill it on Splitit popup
			} else {
				$order_data = array(
					'Address' => '',
					'Address2' => '',
					'Zip' => '',
					'AmountBeforeFees' => WC()->cart->total,
				);
			}

			$api = self::getApi($this->settings); //passing settings to API
			$session = $api->login();

			if (!is_array($session)) {
				$ec_session_id = $api->getEcSession($order_data);
				if (isset($ec_session_id->{'EcSessionId'})) {
					return wp_send_json(array('ec_session_id' => $ec_session_id->{'EcSessionId'}, 'sandbox_mode' => $this->settings['splitit_mode_sandbox']));
				} else {
					$this->log->info(__FILE__, __LINE__, __METHOD__);
					$this->log->add($ec_session_id);
					return wp_send_json($ec_session_id);
				}
			}
			return wp_send_json($session['error']);
		}

		/**
		 * Called by ajax from checkout.
		 * Validates checkout fields.
		 *
		 * @access public
		 */
		public function splitit_checkout_validate() {
			// Get posted checkout_fields and do validation
			if (isset($_POST)) {

				unset($_POST['account_password_field']); //not needed field
				$checkout_fields = stripslashes_deep($_POST);
				$validate_errors = array();

				if (!is_user_logged_in() && isset($checkout_fields['billing_email_field']) && $checkout_fields['billing_email_field'][1] != "") {
					//echo "entered";
					if (email_exists($checkout_fields['billing_email_field'][1])) {
						//echo "condition";
						$validate_errors[] = '<li>An account is already registered with your email address. Please login. </li>';
					}

				}
				//add billing data to shipping if shipping is same as billing
				$skip_shipping = (isset($checkout_fields['ship-to-different-address']) && isset($checkout_fields['ship-to-different-address'][1]) && ($checkout_fields['ship-to-different-address'][1] == 1)) ? false : true;
				unset($checkout_fields['ship-to-different-address']);
				if ($skip_shipping) {
					foreach ($checkout_fields as $f => $d) {
						$type = 'shipping';
						$pos = strpos($f, 'ship');
						if ($pos === false) {
							$type = 'billing';
						}
						if ($type == 'shipping') {
							$billing_field = str_replace('shipping', 'billing', $f);
							$checkout_fields[$f] = isset($checkout_fields[$billing_field]) ? $checkout_fields[$billing_field] : '';
						}
					}
				}

				foreach ($checkout_fields as $field => $data) {

					// Validation: Required fields
					if ((!isset($data[1]) || $data[1] == '') && ($field != 'terms-field' && $field != 'terms')) {
						$type = 'shipping';
						$pos = strpos($field, 'ship');
						if ($pos === false) {
							$type = 'billing';
						}

						if ($skip_shipping && $type == 'shipping') {
							//we don`t validate shipping fields as they are skipped
						} else {
							$validate_errors[] = '<li>' . ucfirst($type) . ' <strong>' . $data[0] . '</strong> ' . __('is a required field.', 'woocommerce') . '</li>';
						}
					}

					if (count($validate_errors) == 0 && WC()->customer->get_shipping_country() != 'IE') {
						// Validation rules

						switch ($field) {
						case 'shipping_postcode_field':
							$checkout_fields[$field][1] = strtoupper(str_replace(' ', '', $data[1]));
							if (!WC_Validation::is_postcode($checkout_fields[$field][1], $checkout_fields['shipping_country_field'][1])):
								$validate_errors[] = '<li><strong>Shipping ' . $data[0] . '</strong> ' . __('is not valid.', 'woocommerce') . '</li>';
							else:
								$checkout_fields[$field][1] = wc_format_postcode($checkout_fields[$field][1], $checkout_fields['shipping_country_field'][1]);
							endif;
							break;

						case 'billing_postcode_field':
							$checkout_fields[$field][1] = strtoupper(str_replace(' ', '', $data[1]));
							if (!WC_Validation::is_postcode($checkout_fields[$field][1], $checkout_fields['billing_country_field'][1])):
								$validate_errors[] = '<li><strong>Billing ' . $data[0] . '</strong> ' . __('is not valid.', 'woocommerce') . '</li>';
							else:
								$checkout_fields[$field][1] = wc_format_postcode($checkout_fields[$field][1], $checkout_fields['billing_country_field'][1]);
							endif;
							break;
						default:

							break;
						}

						$field_type = str_replace(array('shipping_', 'billing_'), '', $field);
						switch ($field_type) {

						case 'phone_field':
							$checkout_fields[$field][1] = wc_format_phone_number($data[1]);
							if (!WC_Validation::is_phone($checkout_fields[$field][1])) {
								$validate_errors[] = '<li><strong>' . $data[0] . '</strong> ' . __('is not a valid phone number.', 'woocommerce') . '</li>';
							}
							if (strlen($data[1]) < 5 || strlen($data[1]) > 14) {
								$validate_errors[] = '<li><strong>' . $data[0] . '</strong> ' . __('should be greater than 5 and less than 14 digits', 'woocommerce') . '</li>';
							}

							break;
						case 'email_field':
							$checkout_fields[$field][1] = strtolower($data[1]);
							if (!is_email($checkout_fields[$field][1])) {
								$validate_errors[] = '<li><strong>' . $data[0] . '</strong> ' . __('is not a valid email address.', 'woocommerce') . '</li>';
							}

							break;
						case 'address_1_field':
							$checkout_fields[$field][1] = strtolower($data[1]);
							if (!isset($checkout_fields[$field][1]) || strlen(trim($checkout_fields[$field][1]))<=0) {
								$validate_errors[] = '<li><strong>' . $data[0] . '</strong> ' . __('is a required field.', 'woocommerce') . '</li>';
							}

							break;
						case 'state_field':
							// Get valid states
							$valid_states = WC()->countries->get_states(WC()->customer->get_billing_country());
							// Only validate if the country has specific state options
							if (!empty($valid_states) && is_array($valid_states) && sizeof($valid_states) > 0) {
								if (!in_array($checkout_fields[$field][1], array_keys($valid_states))) {
									$validate_errors[] = '<li><strong>' . $data[0] . '</strong> ' . __('is not valid. Please enter one of the following:', 'woocommerce') . ' ' . implode(', ', $valid_states) . '</li>';
								}
							}
							break;
						}
					}
				}
				WC()->cart->check_customer_coupons(array('billing_email' => $checkout_fields['billing_email_field'][1]));
				$notices = wc_get_notices();
				if (isset($notices['error']) && !empty($notices['error'])) {
					foreach ($notices['error'] as $noticeErr) {
						$validate_errors[] = '<li>' . __($noticeErr, 'woocommerce') . '</li>';
					}
				}
				if (isset($checkout_fields['terms-field']) && $checkout_fields['terms-field']) {
					if (!isset($checkout_fields['terms']) || !$checkout_fields['terms']) {
//                        if($checkout_fields['terms'][1] == 0) {
						$validate_errors[] = '<li>' . __('You must accept our Terms &amp; Conditions.', 'woocommerce') . '</li>';
//                        }
					}
				}

				//$checkout_fields now contain validated data

				if (is_array($validate_errors) && count($validate_errors)) {
					$validate_errors = array_unique($validate_errors);
					$response = array(
						'result' => 'failure',
						'messages' => implode('', $validate_errors),
					);
				} else {
					$response = array(
						'result' => 'success',
					);
				}

			} else {
				$response = array(
					'result' => 'failure',
					'messages' => 'No data has been sent from form',
				);
			}

			wp_send_json($response);
		}

		public function splitit_payment_error() {
			$ipn = isset($_GET['InstallmentPlanNumber']) ? wc_clean($_GET['InstallmentPlanNumber']) : false;
			$esi = isset($_COOKIE["splitit_checkout_session_id_data"]) ? wc_clean($_COOKIE["splitit_checkout_session_id_data"]) : false;

			$api = self::getApi($this->settings); //passing settings to API

			if (!isset($this->settings['splitit_cancel_url']) || $this->settings['splitit_cancel_url'] == '') {
				$this->settings['splitit_cancel_url'] = 'checkout/';
			}


			setcookie('splitit_checkout', null, strtotime('-1 day'));
			setcookie('splitit_checkout_session_id_data', null, strtotime('-1 day'));
			wp_redirect(SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_cancel_url']));
			exit;

			setcookie('splitit_checkout', null, strtotime('-1 day'));
			setcookie('splitit_checkout_session_id_data', null, strtotime('-1 day'));

		}

		public function get_post_id_by_meta_value($value) {
			global $wpdb;
			$meta = $wpdb->get_results("SELECT * FROM `" . $wpdb->postmeta . "` WHERE meta_key='" . $wpdb->escape("installment_plan_number") . "' AND meta_value='" . $wpdb->escape($value) . "'");
			return $meta;
		}

		/**
		 * Api success redirect handler
		 * captures order in merchant account if necessary, and creates new order in WP
		 *
		 * @access public
		 */

		public function splitit_payment_success($flag = NULL) {
			global $wpdb;
			$ipn = isset($_GET['InstallmentPlanNumber']) ? wc_clean($_GET['InstallmentPlanNumber']) : false;

            if ($ipn && ($this->get_post_id_by_meta_value('lock-' . $ipn) || $this->get_post_id_by_meta_value($ipn))) {   // if such request is already performing, then stop second and other requests
                die;
            } elseif ($ipn) {    // otherwise make a lock that payment success is already in progress and allow to complete it
                $wpdb->insert($wpdb->postmeta, ['meta_key' => 'installment_plan_number', 'meta_value' => 'lock-' . $ipn]);
                register_shutdown_function([$this, 'remove_payment_success_lock']);
            }

			// $esi = isset($_COOKIE["splitit_checkout_session_id_data"]) ? wc_clean($_COOKIE["splitit_checkout_session_id_data"]) : false;
			$esi = (WC()->session->get('splitit_checkout_session_id_data')) ? WC()->session->get('splitit_checkout_session_id_data') : false;
			if(!$esi){
				$api = self::getApi($this->settings);
				$esi = $api->login();
			}

			$exists_data_array = $this->get_post_id_by_meta_value($ipn);
			if (empty($exists_data_array)) {
				$api = self::getApi($this->settings); //passing settings to API
				
				/* Fix for avatax */
				$cart = WC()->cart;
				if (!function_exists('is_plugin_active')) {
					include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				}
				if (is_plugin_active('woocommerce-avatax/woocommerce-avatax.php')) {     // if avatax enabled, recalculate taxes
					define(WOOCOMMERCE_CHECKOUT, true);
					$api->removeTaxCache();
					new WC_Cart_Totals($cart);
					WC()->cart->calculate_totals();
				}
				/* Fix for avatax */
				
				if (!isset($this->settings['splitit_cancel_url']) || $this->settings['splitit_cancel_url'] == '') {
					$this->settings['splitit_cancel_url'] = 'checkout/';
				}
				$criteria = array('InstallmentPlanNumber' => $ipn);
				$installment_data = $api->get($esi, $criteria);
				$verifyData = $api->verifyPayment($esi, $ipn);
				$this->log->info(__FILE__, __LINE__, __METHOD__);
				$this->log->add('installment_data=='.var_export($installment_data,true));
				$this->log->add('verifyData=='.var_export($verifyData,true));
				if(!$verifyData->{'IsPaid'}){
					wc_clear_notices();
					$this->log->add('Sorry, there was no actual payment received to create the order! So order was not placed. Please try to order again.');
					wc_add_notice('Sorry, there was no actual payment received to create the order! So order was not placed. Please try to order again.', 'error');
					wp_redirect(SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_cancel_url']));
					exit;
				}
				$total_amount_on_cart = WC()->cart->get_total(false);
				if($total_amount_on_cart != $verifyData->{'OriginalAmountPaid'}){
					wc_clear_notices();
					$this->log->add('Sorry, there\'s an amount mismatch between cart amount and paid amount! So order was not placed. If any amount was deducted it will be credited back. Please try to order again.');
					wc_add_notice('Sorry, there\'s an amount mismatch between cart amount and paid amount! So order was not placed. If any amount was deducted it will be credited back. Please try to order again.', 'error');
					wp_redirect(SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_cancel_url']));
					exit;
				}
				$planStatus = $installment_data->{'PlansList'}[0]->{'InstallmentPlanStatus'}->{'Code'};
				if (!(($planStatus == "PendingMerchantShipmentNotice" || $planStatus == "InProgress")||($installment_data->{'PlansList'}[0]->{'NumberOfInstallments'}==1 && $planStatus == "Cleared"))) {
					wc_clear_notices();
					$this->log->add('Sorry, the payment was denied by the gateway! So order was not placed. If any amount was deducted it will be credited back. Please try to order again.');
					wc_add_notice('Sorry, the payment was denied by the gateway! So order was not placed. If any amount was deducted it will be credited back. Please try to order again.', 'error');
					wp_redirect(SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_cancel_url']));
					exit;
				}
				$this->log->add('--------valid order-------payment made--------');

				$table_name = $wpdb->prefix . 'splitit_logs';
				$fetch_items = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE ipn =" . $ipn, array()), ARRAY_A);
				//checking for user entered data
				if (isset($fetch_items['user_data']) && $fetch_items['user_data'] != "") {
					$checkout_fields_array = explode('&', $fetch_items['user_data']);
					$checkout_fields = array();
					foreach ($checkout_fields_array as $row) {
						$key_value = explode('=', $row);
						$checkout_fields[$key_value[0]] = $key_value[1];
					}
					$checkout_fields['payment_method'] = 'splitit';
					
					$checkout = new SplitIt_Checkout();
					$checkout->process_splitit_checkout($checkout_fields, $this, $installment_data, $ipn, $esi, $this->settings);
                    SplitIt_Helper::setCookie('splitit_checkout', null, strtotime('-1 day'));
                    SplitIt_Helper::setCookie('splitit_checkout_session_id_data', null, strtotime('-1 day'));
					wc_clear_notices();
					wp_redirect(wc_get_checkout_url() . '?wc-api=splitit_payment_success_async&InstallmentPlanNumber=' . $ipn);
                    SplitIt_Helper::exit_safely();
				} else {
					wc_clear_notices();
					wc_add_notice('Sorry, there was no checkout data received to create order! It was not placed. Please try to order again.', 'error');
					wp_redirect(SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_cancel_url']));
                    SplitIt_Helper::exit_safely();

				}
			} else {
				if (isset($exists_data_array[0]->post_id)) {
					$orderId = $exists_data_array[0]->post_id;
					$last_order = new WC_Order($orderId);
					$last_order_key = $last_order->order_key;

                    $checkout_url = wc_get_checkout_url();
                    $redirect = sprintf('%s%s/%s/?key=%s', $checkout_url, get_option('woocommerce_checkout_order_received_endpoint'), $orderId, $last_order_key);

					wp_redirect($redirect);
                    SplitIt_Helper::exit_safely();
				}

			}

		}

		/**
		 *
		 * Check if Payment was success but order was not created
		 *
		 * @access public
		 */
		public function splitit_payment_success_async() {

			global $wpdb;
			$ipn = isset($_GET['InstallmentPlanNumber']) ? wc_clean($_GET['InstallmentPlanNumber']) : false;

            if ($ipn && ($this->get_post_id_by_meta_value('lock-' . $ipn) || $this->get_post_id_by_meta_value($ipn))) {   // if such request is already performing, then stop second and other requests
                die;
            } elseif ($ipn) {
                $wpdb->insert($wpdb->postmeta, ['meta_key' => 'installment_plan_number', 'meta_value' => 'lock-' . $ipn]);
                register_shutdown_function([$this, 'remove_payment_success_lock']);
            }

			$exists_data_array = $this->get_post_id_by_meta_value($ipn);
			$this->log->info(__FILE__, __LINE__, __METHOD__);
			$this->log->add('exists_data_array=='.var_export($exists_data_array,true));
			// do something if the meta-key-value-pair exists in another post
			if (empty($exists_data_array)) {

				$table_name = $wpdb->prefix . 'splitit_logs';
				$fetch_items = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE ipn = %s ", $ipn), ARRAY_A);

				if (!empty($fetch_items)) {
					$user_data = $fetch_items['user_data'];
					$user_id = $fetch_items['user_id'];
					$cart_items = $fetch_items['cart_items'];
					$shipping_method = $fetch_items['shipping_method_id'];
					$shipping_cost = $fetch_items['shipping_method_cost'];
					$shipping_title = $fetch_items['shipping_method_title'];
					$coupon_amount = $fetch_items['coupon_amount'];
					$coupon_code = $fetch_items['coupon_code'];
					$cart_items = json_decode($fetch_items['cart_items'], true);
					//print_r($cart_items);die;
					$api = self::getApi($this->settings); //passing settings to API
					$session = $api->login();
					if (!isset($this->settings['splitit_cancel_url']) || $this->settings['splitit_cancel_url'] == '') {
						$this->settings['splitit_cancel_url'] = 'checkout/';
					}

					$criteria = array('InstallmentPlanNumber' => $ipn);
					$installment_data = $api->get($session, $criteria);
					$verifyData = $api->verifyPayment($session, $ipn);
					$this->log->info(__FILE__, __LINE__, __METHOD__);
					$this->log->add('installment_data=='.var_export($installment_data,true));
					$this->log->add('verifyData=='.var_export($verifyData,true));
					if(!$verifyData->{'IsPaid'}){
						wc_clear_notices();
						$this->log->add('Sorry, there was no actual payment received to create the order! So order was not placed. Please try to order again.');
						wc_add_notice('Sorry, there was no actual payment received to create the order! So order was not placed. Please try to order again.', 'error');
						wp_redirect(SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_cancel_url']));
						exit;
					}

					$planStatus = $installment_data->{'PlansList'}[0]->{'InstallmentPlanStatus'}->{'Code'};
					if (!(($planStatus == "PendingMerchantShipmentNotice" || $planStatus == "InProgress")||($installment_data->{'PlansList'}[0]->{'NumberOfInstallments'}==1 && $planStatus == "Cleared"))) {
						wc_clear_notices();
						$this->log->add('Sorry, the payment was denied by the gateway! So order was not placed. If any amount was deducted it will be credited back. Please try to order again.');
						wc_add_notice('Sorry, the payment was denied by the gateway! So order was not placed. If any amount was deducted it will be credited back. Please try to order again.', 'error');
						wp_redirect(SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_cancel_url']));
						exit;
					}
					$this->log->add('--------valid order-------payment made--------');

					if ($user_data != "") {
						$checkout_fields_array = explode('&', $user_data);
						$checkout_fields = array();
						foreach ($checkout_fields_array as $row) {
							$key_value = explode('=', $row);
							$checkout_fields[$key_value[0]] = $key_value[1];
						}
						$checkout_fields['payment_method'] = 'splitit'; //override default method as it is not correct
						$criteria = array('InstallmentPlanNumber' => $ipn);
						$installment_data = $api->get($session, $criteria);
						$checkout = new SplitIt_Checkout();
						$checkout->async_process_splitit_checkout($checkout_fields, $this, $installment_data, $ipn, $session, $this->settings, $user_id, $cart_items, $shipping_method, $shipping_cost, $shipping_title, $coupon_amount, $coupon_code);
						wc_clear_notices();

					}

				}

			} else {
				$this->log->info(__FILE__, __LINE__, __METHOD__);
				$this->log->add('SplitIt===Order has been already created');
				echo "Order has been already created";die;
			}
			return true;

		}

		/**
		 * Called from admin settings, clicking on "Check API credentials" link
		 * Check if API credentials are correct
		 *
		 * @access public
		 */
		public function splitit_check_api_credentials() {

			if (!$this->s('splitit_api_terminal_key') || !$this->s('splitit_api_username') || !$this->s('splitit_api_password')) {
				$message = "Please enter the credentials and save settings";
			} else {

			    $api = self::getApi($this->settings);
				$session = $api->login();

				$message = ($this->s('splitit_mode_sandbox') == 'yes') ? '[Sandbox Mode] ' : '[Production mode] ';
				if (!isset($session['error'])) {
					$message .= 'Successfully login! API available!';
				} else {
					$message .= 'code: ' . $session['error']['code'] . ' - ERROR: ' . $session['error']['message'];
				}
			}
			echo $message;
			wp_die();
		}

		/***************************************************************************************************************
			         * Payment process logic
		*/

		/**
		 * Process the payment and return the result
		 */
		public function process_payment($order_id) {
			global $woocommerce;
			$this->log->info(__FILE__, __LINE__, __METHOD__);
			try {
				$this->log->add('$order_id==='.$order_id);

				$order = wc_get_order($order_id);
				// Get redirect
				$return_url = SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_success_url']);
				if (!$return_url) {
					$return_url = $order->get_checkout_order_received_url();
				}
				$this->log->add('$return_url==='.$return_url);
				$woocommerce->cart->empty_cart();
				// Redirect to success/confirmation/payment page
				if (is_ajax()) {
					wp_send_json(array(
						'result' => 'success',
						'redirect' => apply_filters('woocommerce_checkout_no_payment_needed_redirect', $return_url, $order),
					));
				} else {
					global $wp;
					// redirect to checkout success page.
					if ($order_id) {

						$order = new WC_Order($order_id);
						$order_key = $order->order_key;

						/**
						 * Replace {PAGE_ID} with the ID of your page
						 */

						global $woocommerce;
						$checkout_url = wc_get_checkout_url();
						$this->log->info(__FILE__, __LINE__, __METHOD__);
						$this->log->add('splitit_thankyou_page==='.$this->s('splitit_thankyou_page'));

                        $redirect = sprintf('%s%s/%s/?key=%s', $checkout_url, get_option('woocommerce_checkout_order_received_endpoint'), $order_id, $order_key);

						if($this->s('splitit_thankyou_page') == 'yes'){
                            $redirect = ($order->get_checkout_order_received_url()) ? $order->get_checkout_order_received_url() : $redirect;
						}

						$this->log->add('$redirect==='.$redirect);

						wp_redirect($redirect);
                        SplitIt_Helper::exit_safely();
					}

					wp_safe_redirect(
						apply_filters('woocommerce_checkout_no_payment_needed_redirect', $return_url, $order)
					);
                    SplitIt_Helper::exit_safely();
				}
			} catch (Exception $e) {
				$this->log->info(__FILE__, __LINE__, __METHOD__);
				$this->log->add($e->getMessage());
			}
		}

		/***************************************************************************************************************
			         * Installment price logic
		*/

		/**
		 * Split price functionality wrapper
		 *
		 * @param $price
		 * @param $product
		 * @return string
		 */
		public function splitit_installment_price($price, $product) {
			if (isset($this->settings['splitit_installment_price_sections'])  && $this->settings['enabled'] == 'yes') {
				$sections = $this->settings['splitit_installment_price_sections'];
				//checking if any options selected in admin
				if (is_array($sections)) {
					if (is_product() && in_array('product', $sections)) {
						if($this->isSplititTextVisibleOnProduct($product->get_id())){
							return $price . '</p><p class="splitprice">' .$this->get_formatted_installment_price($product);
						} else {
							return $price;
						}
					}
					if (is_shop() && in_array('category', $sections)) {
						return $price . $this->get_formatted_installment_price($product);
					}
					if (is_cart() && in_array('cart', $sections)) {
						return $price;
					}
					if (is_checkout() && in_array('checkout', $sections)) {
                        if (method_exists($product, 'get_price')) {
                            return $price . $this->get_formatted_installment_price($product);
                        }
                        return $price;
                    }
				}
			}
			return $price;
		}

		/**
		 * Split total functionality wrapper
		 *
		 * @param $price
		 * @return string
		 */
		public function splitit_installment_total_price($price) {
			global $woocommerce;
			$gateways = WC()->payment_gateways->get_available_payment_gateways();

			$enabledGateways = $this->change_payment_gateway($gateways);
			if (isset($enabledGateways['splitit'])) {
				$enabledGateways = $this->product_specific_payment_gateway($gateways);
			}
			if (($this->settings['enabled'] == 'yes' && isset($enabledGateways['splitit'])) && is_array($this->settings['splitit_installment_price_sections'])) {
				$sections = $this->settings['splitit_installment_price_sections'];
                $numInstallments = self::$_maxInstallments;

				if ((is_cart() && in_array('cart', $sections)) || is_checkout() && in_array('checkout', $sections)) {
					$split_price = round($woocommerce->cart->total / self::$_maxInstallments, 3);
                    $learnmoreImage = '<span class="tell-me-more-image-wrapper"><img class="tell-me-more-image" src="' . plugin_dir_url(__FILE__) . 'assets/images/learn_more.png"></span>';
					if (isset($this->settings['splitit_logo_src_local']) && $this->settings['splitit_logo_src_local']) {
                        $learnmoreImage = "<img  class='logoWidthSrc' src='" . $this->settings['splitit_logo_src_local'] . "' alt='SPLITIT'/>{$learnmoreImage}";
					}

					$learnmore = " <a href='javascript:void(0)' data-splitit-custom='learn-more' class='no-lightbox'>" . $learnmoreImage . "</a>";
                    $resultPrice =  wc_price($split_price, array('decimals' => 2));
                    $learnMoreBlock = "<div class='splitit-cj' data-splitit='true' data-splitit-amount='{$woocommerce->cart->total}' data-splitit-num-installments='{$numInstallments}' data-splitit-type='product-description' data-splitit-content='custom'>or  {$numInstallments} interest-free payments of  {$resultPrice}  with {$learnmore}</div>";

					$textToDisplay = '<span style="display:block;" class="splitit-installment-price-checkout">'. $learnMoreBlock . '</span>';
					return $price . "<br/>" . $textToDisplay.$this->get_learn_more_script();
				}
			}
			return $price;
		}

		public function get_learn_more_script()
        {
            $culture = str_replace('_', '-', get_locale());
            $culture = $culture != 'pt-BR' ? $culture : 'pt-PT';
            $currencyCode = get_woocommerce_currency() ? get_woocommerce_currency() : 'USD';
            $apiKey = $this->get_option('splitit_api_terminal_key');

            $learnMoreScript = "<script>
                    (function(i,s,o,g,r,a,m){i['SplititObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                    })(window, document, 'script', '//upstream.production.splitit.com/v1/dist/upstream-messaging.js?v=<?= round(microtime(true)/100) ?>', 'splitit');
                    splitit('init', { apiKey: '{$apiKey}', lang: '{$culture}', currency: '{$currencyCode}', currencySymbol: '{$currencyCode}', debug: false });
                </script>";
            return $learnMoreScript;
        }
		/**
		 * Returns formatted installment price
		 *
		 * @param $price
		 * @param $return
		 * @return string
		 */
		public function get_formatted_installment_price($product) {
			$gateways = WC()->payment_gateways->get_available_payment_gateways();
			$enabledGateways = $this->change_payment_gateway($gateways,true);
			if (isset($enabledGateways['splitit'])) {
				$enabledGateways = $this->product_specific_payment_gateway($gateways);
			}
			if ($this->settings['enabled'] == 'no' || !isset($enabledGateways['splitit'])) {
				return;
			}
			$textToDisplay = isset($this->settings['splitit_without_interest'])?$this->settings['splitit_without_interest']:'';
            $learnmoreImage = "<span class='tell-me-more-image-wrapper'><img class='tell-me-more-image' src='".plugin_dir_url(__FILE__)."assets/images/learn_more.png'></span>";

			if (isset($this->settings['splitit_logo_src_local']) && $this->settings['splitit_logo_src_local']) {
                $learnmoreImage = "<a data-splitit-custom='learn-more' href='javascript:void(0)'><img  class='logoWidthSrc' src='{$this->settings['splitit_logo_src_local']}' alt='SPLITIT'/>{$learnmoreImage}</a>";
                $learnmoreImage = str_replace('SPLITIT', isset($this->settings['splitit_without_interest'])?$this->settings['splitit_without_interest']:'',$learnmoreImage);
			}

            $numInstallments = self::$_maxInstallments;
            $split_price = round($product->get_price() / self::$_maxInstallments, 3);
            $resultPrice = str_replace('woocommerce-Price-amount', '', wc_price($split_price, array('decimals' => 2)));

			$learnMoreBlock = "<div class='splitit-cj' data-splitit='true' data-splitit-amount='{$product->get_price()}' data-splitit-num-installments='{$numInstallments}' data-splitit-type='product-description' data-splitit-content='custom'>or  {$numInstallments} interest-free payments of  {$resultPrice}  with {$learnmoreImage}</div>";
            $learnMoreScript = $this->get_learn_more_script();

			return '<span style="display:block;" class="splitit-installment-price splitit-installment-price-product">'.$learnMoreBlock.'</span>'.$learnMoreScript;
		}

		/***************************************************************************************************************
			         * Helper functions
		*/

		/**
		 * s function.
		 *
		 * Returns a setting if set. Introduced to prevent undefined key when introducing new settings.
		 *
		 * @access public
		 * @return string
		 */
		public function s($key) {
			if (isset($this->settings[$key])) {
				return $this->settings[$key];
			}

			return '';
		}

		/**
		 * Passing cdn urls to splitit-checkout.js script
		 *
		 * @access public
		 * @return array
		 */
		public function splitit_pass_cdn_urls() {
			$params = array('prod' => rtrim($this->s('splitit_cdn_prod_url'), '/') . '/', 'sand' => rtrim($this->s('splitit_cdn_sand_url'), '/') . '/');
			wp_localize_script('splitit-checkout', 'cdn_urls', $params);
		}

		/**
		 * FILTER: splitit_gateway_icons function.
		 *
		 * Sets gateway icons on frontend
		 *
		 * @access public
		 * @return void
		 */
		public function splitit_gateway_icons($icon, $id) {
			if ($id == $this->id) {
				$icon = '';
				$icons = $this->s('splitit_cc');

				if (is_array($icons) && count($icons)) {
					foreach ($icons as $key => $item) {
						$icon .= $this->gateway_icon_create($item, '30');
					}
				}
			}

			return $icon;
		}

		/**
		 * remove splitit gateway if cart total > max or < min
		 * @param $gateways
		 * @return mixed
		 */
		public function change_payment_gateway($gateways) {

			if (empty(WC()->cart) || is_product()) {
				return $gateways;
			}

			if (!isset($this->settings['splitit_doct']['ct_from'])) {
				$this->settings['splitit_doct']['ct_from'] = array();
			}
			if (!isset($this->settings['splitit_doct']['ct_to'])) {
				$this->settings['splitit_doct']['ct_to'] = array();
			}
			foreach ($this->settings['splitit_doct']['ct_from'] as $key => $value) {
				//                                if (empty($value)) {
				if (trim($value) == '') {
					unset($this->settings['splitit_doct']['ct_from'][$key]);
				}
			}
			foreach ($this->settings['splitit_doct']['ct_to'] as $key1 => $value1) {
				//                                if (empty($value1)) {
				if (trim($value1) == '') {
					unset($this->settings['splitit_doct']['ct_to'][$key1]);
				}
			}
			$min = !empty($this->settings['splitit_doct']['ct_from']) ? min($this->settings['splitit_doct']['ct_from']) : 0;
			$max = !empty($this->settings['splitit_doct']['ct_to']) ? max($this->settings['splitit_doct']['ct_to']) : 0;

			// Compare cart subtotal (without shipment fees)
			if (WC()->cart->total > $max || WC()->cart->total < $min) {
				unset($gateways['splitit']);
			}
			return $gateways;
		}

		/**
		 * remove splitit gateway if product conditions met
		 * @param $gateways
		 * @return mixed
		 */
		public function product_specific_payment_gateway($gateways) {
			if (isset($this->settings['splitit_product_option']) && $this->settings['splitit_product_option'] && !empty(WC()->cart)) {
				$items = WC()->cart->get_cart();
				$prodSKUs = $this->settings['splitit_product_sku_list'];
				$prodSKUs = explode(',', $prodSKUs);
				$skus = array();
				foreach ($items as $item => $values) {

					array_push($skus, $values['product_id']);
				}
				if (!is_array($prodSKUs)) {
					$prodSKUs = array();
				}
				sort($prodSKUs);
				sort($skus);
				if ($this->settings['splitit_product_option'] == 1 && count(array_intersect($prodSKUs, $skus)) != count($skus)) {
					unset($gateways['splitit']);
				}
				if ($this->settings['splitit_product_option'] == 2 && count(array_intersect($prodSKUs, $skus)) < 1) {
					unset($gateways['splitit']);
				}
			}
			return $gateways;
		}

		/**
		 * remove splitit gateway if product conditions met
		 * @param $gateways
		 * @return mixed
		 */
		public function isSplititTextVisibleOnProduct($productId) {
			$show = true;
			if (isset($this->settings['splitit_product_option']) && $this->settings['splitit_product_option']!=0) {
				$show = false;
				$prodSKUs = $this->settings['splitit_product_sku_list'];
				$prodSKUs = explode(',', $prodSKUs);
				if (in_array($productId, $prodSKUs)) {
					$show = TRUE;
				}
			}
			return $show;
		}

		/**
		 * Helper to get the a gateway icon image tag
		 *
		 * @access protected
		 * @return string
		 */
		protected function gateway_icon_create($icon, $max_height) {
			$icon_url = WC_HTTPS::force_https_url(plugin_dir_url(__FILE__) . 'assets/images/cards/' . $icon . '.png');
			return '<img src="' . str_replace('http:','https:',$icon_url) . '" alt="' . esc_attr($icon) . '" style="max-height:' . $max_height . 'px; "/>';
		}

		/**
		 * Tell me more link
		 *
		 * @access public
		 * @return string
		 */
		public function splitit_help() {
			return wp_send_json(plugin_dir_url(__FILE__) . 'assets/images/spl_tell_more.png');
		}

		/**
		 * Adds installment_plan_number value to order edit page
		 *
		 * @param $order
		 */
		public function splitit_add_installment_plan_number_data($order) {
			echo '<p><strong>' . __('Installment plan number') . ':</strong> ' . get_post_meta($order->get_id(), 'installment_plan_number', true) . '</p>';
			echo '<p><strong>' . __('Number of installments') . ':</strong> ' . get_post_meta($order->get_id(), 'number_of_installments', true) . '</p>';
		}

		/**
		 * Enable request logging
		 *
		 * @param $response array
		 * @param $type string
		 * @param $class obj
		 * @param $args array
		 * @param $url string
		 */
		public function splitit_api_request_debug($response, $type, $class, $args, $url) {
			if (strpos($url, 'splitit') !== false) {
				$this->log->info(__FILE__, __LINE__, __METHOD__);
				$this->log->add('Request URL: ' . var_export($url, true));
				$this->log->add('Request Args: ' . var_export($args, true));
				$this->log->add('Response: ' . var_export($response, true));
				$this->log->separator();
			}
		}

		/**
		 * Process a refund if supported.
		 *
		 * @param  int    $order_id Order ID.
		 * @param  float  $amount Refund amount.
		 * @param  string $reason Refund reason.
		 * @return bool|WP_Error
		 */
		public function process_refund($order_id, $amount = null, $reason = '') {
			$order = wc_get_order($order_id);
			if ($order->get_payment_method() == 'splitit') {

				if(!intval($amount)){
					return new WP_Error('error', __('Cannot refund 0.00', 'woocommerce'));
				}

				if (!$this->can_refund_order($order)) {
					return new WP_Error('error', __('Refund failed.', 'woocommerce'));
				}

				$ipn = get_post_meta($order->get_id(), 'installment_plan_number', true);

				$api = self::getApi($this->settings);

				$result = $api->refund($ipn, $amount, $reason);
				$error = $this->getAPIerrorJSON($result);
				$this->log->info(__FILE__, __LINE__, __METHOD__);

				if (is_wp_error($result)) {
					$this->log->add('ERROR : ' . var_export($result, true));
					$this->log->separator();
					return new WP_Error('error', $error);
				}

				$this->log->add('Refund Result: ' . wc_print_r($result, true));
				$this->log->separator();

				if (isset($result->ResponseHeader->Succeeded) && $result->ResponseHeader->Succeeded) {
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
					$order->add_order_note(
						/* translators: 1: Refund amount, 2: Refund ID */
						sprintf(__('Refunded %1$s - Refund ID: %2$s', 'woocommerce'), $result->CurrentRefundAmount->Value . ' ' . $result->CurrentRefundAmount->Currency->Code, "same as IPN") // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
					);
					return true;
				}

				if(is_object($result)){
					$result = json_decode(json_encode($result), true);
				}

				return isset($result['code']) ? new WP_Error('error', $result['message']) : false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			}
		}

		public function splitit_cancel_order($order_get_id) {
			global $woocommerce;
			$chosen_gateway = get_post_meta( $order_get_id, '_payment_method', true );
			if ($chosen_gateway == 'splitit') {
				$ipn = get_post_meta($order_get_id, 'installment_plan_number', true);
				$api = self::getApi($this->settings);
				$result = $api->cancel($ipn);
				$error = $this->getAPIerrorJSON($result);
				$this->log->info(__FILE__, __LINE__, __METHOD__);
				if (is_wp_error($result) || $error) {
					$this->log->add('ERROR : ' . var_export($result, true));
					$this->log->separator();
					return new WP_Error('error', $error);
				}

				$this->log->add('Refund Result: ' . wc_print_r($result, true));
				$this->log->separator();
			}
			return true;
		}

		public function splitit_add_content_specific_email( $order, $sent_to_admin, $plain_text, $email ) { ?>
			<style type="text/css">.paymentlogoWidthSrc{width:52px;} .tell-me-more-image{width:12px;}</style>
		  <?php
		}
		
		public function getAPIerrorJSON($json) {
			$result = $json;
			if (is_string($json)) {
				$result = json_decode($json, true);
			} elseif (is_object($json)) {
				$result = json_decode(json_encode($json), true);
			}
			$errorMsg = "";
			if (isset($result["ResponseHeader"]) && isset($result["ResponseHeader"]["Errors"]) && !empty($result["ResponseHeader"]["Errors"])) {

				foreach ($result["ResponseHeader"]["Errors"] as $key => $value) {
					$errorMsg .= $value["ErrorCode"] . " : " . $value["Message"];
				}

			} elseif (isset($result["serverError"])) {
				$errorMsg = $result["serverError"];
			}
			return $errorMsg;
		}

		/**
		 * Called from admin settings when a product is searched
		 * provide list of searched products and also saved products
		 *
		 * @access public
		 */
		public function splitit_fetch_prods() {

			$prodSKUs = array();
			$where = "post_type='product' and post_status = 'publish' and meta_key='_sku'";
			if (isset($_GET['term']) && $_GET['term']) {
				$_GET['term'] = wc_clean($_GET['term']);
				$where .= " AND (post_title like '%{$_GET['term']}%' OR meta_value like '%{$_GET['term']}%') ORDER BY post_title";
			} elseif (isset($_POST['prodIds']) && $_POST['prodIds']) {
				// $args['include'] = explode(',', $_POST['prodIds']);
				$postProdIds = wc_clean($_POST['prodIds']);
				$where .= " AND ID IN($postProdIds)";
			} else {
				echo json_encode(array());exit;
			}
			global $wpdb;

			$wcProductsArray = $wpdb->get_results("SELECT ID,post_title,post_content,post_author,post_date_gmt,`" . $wpdb->prefix . "postmeta`.meta_value as sku FROM `" . $wpdb->prefix . "posts` JOIN `" . $wpdb->prefix . "postmeta` ON `" . $wpdb->prefix . "postmeta`.post_id=`" . $wpdb->prefix . "posts`.ID where $where");

			if (count($wcProductsArray)) {
				foreach ($wcProductsArray as $productPost) {
					$productSKU = ($productPost->sku) ? $productPost->sku : '-SKU not defined-';
					$prodSKUs[] = array('value' => $productPost->ID, 'label' => $productPost->post_title . ' (' . $productSKU . ')');
				}
			}
			echo json_encode($prodSKUs);
			wp_die();
		}

        public function getHelpMeLink($amount = null)
        {
            $apiKey = $this->get_option('splitit_api_terminal_key');
            $culture = str_replace('_', '-', get_locale());
            $culture = $culture != 'pt-BR' ? $culture : 'pt-PT';
            $currencyCode = "USD";
            if (get_woocommerce_currency() != "") {
                $currencyCode = get_woocommerce_currency();
            }
            $numInstallments = self::$_maxInstallments;

            if ($this->get_option('splitit_mode_sandbox') == 'yes') {
                $url = 'https://documents.sandbox.splitit.com/LearnMore?apiKey=' . $apiKey;
            } else {
                $url = 'https://documents.production.splitit.com/LearnMore?apiKey=' . $apiKey;
            }
            if ($amount) {
                $url = $url . '&amount=' . $amount;
            }
            $url = $url . '&culture=' . $culture
                . '&currencyCode=' . $currencyCode
                . '&numInstallments=' . $numInstallments;


            return $url;
        }

        // remove locking payment success after payment success action is finished
        public function remove_payment_success_lock()
        {
            global $wpdb;
            $ipn = isset($_GET['InstallmentPlanNumber']) ? wc_clean($_GET['InstallmentPlanNumber']) : false;

            if ($ipn && isset($_GET['wc-api']) && $_GET['wc-api'] == 'splitit_payment_success') {
                $wpdb->delete($wpdb->postmeta, ['meta_key' => 'installment_plan_number', 'meta_value' => 'lock-' . $ipn]);
            }
        }
    }

	// Make the object available for later use
	function SplitIt() {
		return SplitIt::get_instance();
	}

	SplitIt();
	SplitIt()->hooks_and_filters(); //saves admin settings data

	// Add the gateway to WooCommerce
	function add_splitit_gateway($methods) {
		$methods[] = 'SplitIt';
		return apply_filters('splitit_load_instances', $methods);
	}

	add_filter('woocommerce_payment_gateways', 'add_splitit_gateway');
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'SplitIt::add_action_links');
}

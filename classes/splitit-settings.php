<?php
/**
 * SplitIt_Settings class
 *
 * @class       SplitIt_Settings
 * @version     0.2.9
 * @package     SplitIt/Classes
 * @category    Settings
 * @author      By Splitit
 */
class SplitIt_Settings {

	/**
	 * Returns an array of available admin settings fields
	 *
	 * @access public static
	 * @return array
	 */
	public static function get_fields() {
		$noOfIns = 36;
		/**
		 * this method will only fetch simple products, and will not include product variations. If you want to grab those too, you'll need to update the $args to 'post_type' => array('product', 'product_variation')
		 */

		$insArr = array();
		for ($i = 1; $i <= $noOfIns; $i++) {
			$insArr[$i] = $i . ' Installments';
		}

		$fields =
		array(
			'_General_settings' => array(
				'type' => 'title',
				'title' => __('General settings', 'splitit'),
				'description' => __('Api and debug settings'),
			),

            'enabled' => array(
                'title' => __('Enable/Disable', 'splitit'),
                'type' => 'checkbox',
                'label' => __('Enable Splitit Payment', 'splitit'),
                'default' => 'yes',
            ),

			'splitit_api_terminal_key' => array(
				'title' => __('Terminal API key', 'splitit'),
				'type' => 'text',
			),
			'splitit_api_username' => array(
				'title' => __('API Username', 'splitit'),
				'type' => 'text',
			),
			'splitit_api_password' => array(
				'title' => __('API Password', 'splitit'),
				'type' => 'text',
			),
			'splitit_mode_sandbox' => array(
				'title' => __('Sandbox Mode', 'splitit'),
				'description' => __('Sandbox Mode for testing purposes (uses API Sandbox URL).', 'splitit'),
				'desc_tip' => true,
				'type' => 'select',
				'options' => array(
					'no' => 'No',
					'yes' => 'Yes',
				),
			),
			'splitit_mode_debug' => array(
				'title' => __('Debug Mode', 'splitit'),
				'description' => __('Enables Splitit request data logging.', 'splitit'),
				'desc_tip' => true,
				'type' => 'select',
				'options' => array(
					'no' => 'No',
					'yes' => 'Yes',
				),
			),
			'splitit_thankyou_page' => array(
				'title' => __('Are you using a custom \'Thank You\' page?', 'splitit'),
				'description' => __('using a custom thank you page', 'splitit'),
				'desc_tip' => false,
				'type' => 'select',
				'options' => array(
					'no' => 'No',
					'yes' => 'Yes',
				),
				'default' => 'no',
			),
			'splitit_test_api' => array(
				'title' => '<a href="" id="checkApiCredentials">Verify API Credentials</a>',
				'css' => 'display:none;',
			),
			'splitit_api_prod_url' => array(
				'title' => __('API Production URL', 'splitit'),
				'type' => 'text',
				'default' => 'https://webapi.production.splitit.com/',
			),
			'splitit_cdn_prod_url' => array(
				'title' => __('CDN Production URL', 'splitit'),
				'type' => 'text',
				'default' => 'https://cdn.splitit.com/',
			),
			'splitit_api_sand_url' => array(
				'title' => __('API Sandbox URL', 'splitit'),
				'type' => 'text',
				'default' => 'https://webapi.sandbox.splitit.com/',
			),
			'splitit_cdn_sand_url' => array(
				'title' => __('CDN Sandbox URL', 'splitit'),
				'type' => 'text',
				'default' => 'https://cdn-sandbox.splitit.com/',
			),
			'custom_urls' => array(
				'title' => __('Define default/custom URL', 'splitit'),
				'desc_tip' => true,
				'type' => 'select',
				'options' => array(
					'default' => 'Default',
					'custom' => 'Custom',
				),
			),
			'splitit_async_enable' => array(
				'title' => __('Enable Async Call', 'splitit'),
				'type' => 'select',
				'options' => array(
					'yes' => 'Yes',
					'no' => 'No',
				),
				'default' => 'yes',
			),

			'_Payment Setup' => array(
				'type' => 'title',
				'title' => __('Payment Setup', 'splitit'),
				'description' => __('Payment settings'),
			),

			'splitit_cc' => array(
				'title' => __('Card types', 'splitit'),
				'type' => 'multiselect',
				'description' => __('Choose the card icons you wish to show next to the Splitit payment option in your shop.', 'splitit'),
				'desc_tip' => true,
				'class' => 'wc-enhanced-select',
				'css' => 'width: 450px;',
				'custom_attributes' => array(
					'data-placeholder' => __('Choose credit cards', 'splitit'),
				),
				'options' => array(
					'visa' => 'Visa',
					'mastercard' => 'Mastercard',
					'unionpay' => 'Union Pay',
				),
				'default' => array('visa', 'mastercard', 'unionpay'),
			),

			'splitit_payment_action' => array(
				'title' => __('Payment action', 'splitit'),
				'type' => 'select',
				'class' => 'wc-enhanced-select',
				'css' => 'width: 450px;',
				'default' => '',
				'options' => array(
					'purchase' => 'Charge my consumer at the time of purchase',
					'shipped' => 'Charge my consumer when the shipment is ready',
				),
			),

			'_Installment Setup' => array(
				'type' => 'title',
				'title' => __('Installment Setup', 'splitit'),
				'description' => __('Installment settings'),
			),

			'splitit_discount_type' => array(
				'title' => __('Select installment setup', 'splitit'),
				'desc_tip' => true,
				'type' => 'select',
				'options' => array(
					'fixed' => 'Set number of installments',
					'depending_on_cart_total' => 'Depending on cart total',
				),
			),
			'splitit_discount_type_fixed' => array(
				'title' => __('Select Installment options', 'splitit'),
				'desc_tip' => true,
				'type' => 'multiselect',
				'css' => 'width: 350px; height: 185px;',
				'options' => $insArr,
			),
			'splitit_doct' => array(
				array(
					'ct_from' => array(
						'title' => __('test1', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_from',
						'default' => '0',
					),
					'ct_to' => array(
						'title' => __('test2', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_to',
						'default' => '300',
					),
					'ct_instllment' => array(
						'title' => __('test3', 'splitit'),
						'type' => 'multiselect',
						'class' => 'doctv_installments',
						'options' => $insArr,
						'default' => array('2', '3'),
					),
					'ct_currency' => array(
						'title' => __('test4', 'splitit'),
						'type' => 'text',
						'default' => get_woocommerce_currency(),
					),
				),
				array(
					'ct_from' => array(
						'title' => __('test12', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_from',
						'default' => '301',
					),
					'ct_to' => array(
						'title' => __('test22', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_to',
						'default' => '500',
					),
					'ct_instllment' => array(
						'title' => __('test32', 'splitit'),
						'type' => 'multiselect',
						'class' => 'doctv_installments',
						'options' => $insArr,
						'default' => array('2', '3', '4'),
					),
					'ct_currency' => array(
						'title' => __('test42', 'splitit'),
						'type' => 'text',
						'default' => get_woocommerce_currency(),
					),
				),
				array(
					'ct_from' => array(
						'title' => __('test12', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_from',
						'default' => '501',
					),
					'ct_to' => array(
						'title' => __('test22', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_to',
						'default' => '700',
					),
					'ct_instllment' => array(
						'title' => __('test32', 'splitit'),
						'type' => 'multiselect',
						'class' => 'doctv_installments',
						'options' => $insArr,
						'default' => array('2', '3', '4', '5'),
					),
					'ct_currency' => array(
						'title' => __('test42', 'splitit'),
						'type' => 'text',
						'default' => get_woocommerce_currency(),
					),
				),
				array(
					'ct_from' => array(
						'title' => __('test12', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_from',
						'default' => '701',
					),
					'ct_to' => array(
						'title' => __('test22', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_to',
						'default' => '1000',
					),
					'ct_instllment' => array(
						'title' => __('test32', 'splitit'),
						'type' => 'multiselect',
						'class' => 'doctv_installments',
						'options' => $insArr,
						'default' => array('2', '3', '4', '5', '6', '7'),

					),
					'ct_currency' => array(
						'title' => __('test42', 'splitit'),
						'type' => 'text',
						'default' => get_woocommerce_currency(),
					),
				),
				array(
					'ct_from' => array(
						'title' => __('test12', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_from',
						'default' => '1001',
					),
					'ct_to' => array(
						'title' => __('test22', 'splitit'),
						'type' => 'text_money',
						'class' => 'doctv_to',
						'default' => '10000',
					),
					'ct_instllment' => array(
						'title' => __('test32', 'splitit'),
						'type' => 'multiselect',
						'class' => 'doctv_installments',
						'options' => $insArr,
						'default' => array('2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'),
					),
					'ct_currency' => array(
						'title' => __('test42', 'splitit'),
						'type' => 'text',
						'default' => get_woocommerce_currency(),
					),
				),
			),
			'splitit_async_enable' => array(
				'title' => __('Enable Async Call', 'splitit'),
				'type' => 'select',
				'options' => array(
					'yes' => 'Yes',
					'no' => 'No',
				),
				'default' => 'yes',
			),

			'splitit_default_selected_installment' => array(
				'title' => __('Default Selected Installment', 'splitit'),
				'desc_tip' => 'Selected installment will be by default selected on Splitit Payment page',
				'type' => 'select',
				'options' => $insArr,
				'default' => '10',
			),

			'splitit_first_installment' => array(
				'title' => __('First Payment', 'splitit'),
				'type' => 'select',
				'options' => array(
					'monthly' => 'Equal to Monthly Payment',
					'shipping' => 'Only Shipping',
					'shipping_taxes' => 'Only Shipping and Taxes',
					'percent' => 'Equal to percentage of the order [X]',
				),
			),
			'splitit_first_installment_percent' => array(
				'title' => __('Percentage Of Order  %', 'splitit'),
				'type' => 'number',
			),

			'_Enable Splitit Per Product' => array(
				'type' => 'title',
				'title' => __('Enable Splitit Per Product', 'splitit'),
				'description' => __('Splitit Per Product settings'),
			),

			'splitit_product_option' => array(
				'title' => __('Enable Splitit per product', 'splitit'),
				'type' => 'select',
				'class' => 'wc-enhanced-select',
				'css' => 'width: 450px;',
				'default' => '',
				'options' => array(
					'0' => 'Disabled',
					'1' => 'Enable Splitit if the cart consists only of products from the list below',
					'2' => 'Enable Splitit if the cart consists of at least one of the products from the list below',
				),
			),
			'splitit_product_sku_list' => array(
				'title' => __('List of product SKUs', 'splitit'),
				'type' => 'text',
			),

			'_Display Setup ' => array(
				'type' => 'title',
				'title' => __('Display Setup ', 'splitit'),
				'description' => __('Display settings'),
			),

			'splitit_enable_installment_price' => array(
				'title' => __('Enable/Disable', 'splitit'),
				'description' => __(''),
				'type' => 'checkbox',
				'label' => __('Advertise Splitit installment option throughout my store', 'splitit'),
				'default' => 'yes',
			),

			'splitit_max_installments_limit' => array(
				'title' => __('Number of installment for display', 'splitit'),
				'type' => 'select',
				'default' => 6,
				'class' => 'wc-enhanced-select',
				'css' => 'width: 450px;',
				'options' => self::get_available_installments(),
			),

			'splitit_installment_price_sections' => array(
				'title' => __('Sections/pages where to display installment price', 'splitit'),
				'description' => __('Select pages to show installment prices.', 'splitit'),
				'desc_tip' => true,
				'type' => 'multiselect',
				'css' => 'width: 350px; height: 185px;',
				'options' => self::get_installment_price_sections(),
			),

			'splitit_installment_text' => array(
				'title' => __('Splitit Installment Text', 'splitit'),
				'description' => __('Installment text to be displayed with SplitIt logo'),
				'type' => 'text',
				'default' => '0% INTEREST MONTHLY PAYMENTS',
			),
			'_Checkout ' => array(
				'type' => 'title',
				'class' => 'hidden',
			),
			'splitit_logo_src_local' => array(
				'type' => 'text',
				'class' => 'hidden',
				'default' => Splitit_logo_source_local
			),
			'_3dSecure ' => array(
				'type' => 'title',
				'title' => __('3D Secure ', 'splitit'),
				/*'description' => __('Checkout settings'),*/
			),

			'splitit_3d_secure' => array(
				'title' => __('Enable 3D Secure', 'splitit'),
				'type' => 'select',
				'options' => array(
					'no' => 'No',
					'yes' => 'Yes',
				),
			),
			'splitit_3d_secure_min_amount' => array(
				'title' => __('Minimal amount for 3D attempt', 'splitit'),
				'type' => 'text_money',
				'default' => 0,
			),
			'splitit_cancel_url' => array(
				'title' => __('Cancel payment url', 'splitit'),
				'type' => 'text',
				'default' => 'checkout/',
				'class' => 'custom_urls',
				'placeholder' => __('Default url is "checkout/"', 'splitit'),
				'description' => __('Enter url (without domain) which will be used for redirect on Splitit cancel payment action.', 'splitit'),
				'desc_tip' => true,
			),
			'splitit_error_url' => array(
				'title' => __('Error payment url', 'splitit'),
				'type' => 'text',
				'default' => 'checkout/',
				'class' => 'custom_urls',
				'placeholder' => __('Default url is "checkout/"', 'splitit'),
				'description' => __('Enter url (without domain) which will be used for redirect on Splitit error payment action.', 'splitit'),
				'desc_tip' => true,
			),
			'splitit_success_url' => array(
				'title' => __('Success payment url', 'splitit'),
				'type' => 'text',
				'class' => 'custom_urls',
				'default' => 'checkout/',
				'placeholder' => __('Default url is "checkout/"', 'splitit'),
				'description' => __('Enter url (without domain) which will be used for redirect on Splitit success payment action.', 'splitit'),
				'desc_tip' => true,
			),
		);

		return $fields;
	}

	/**
	 * Provides a list of installments
	 *
	 * @access private
	 * @return array
	 */
	private static function get_available_installments() {
		$installments_left_limit = 2;
		$installments_right_limit = 36;
		$installments = array();
		for ($i = $installments_left_limit; $i <= $installments_right_limit; $i++) {
			$installments[$i] = $i . ' Installments';
		}
		return $installments;
	}

	/**
	 * Provides a list of countires
	 *
	 * @access private
	 * @return array
	 */
	private static function get_countries() {
		$countries = new WC_Countries;
		return $countries->get_countries();
	}

	/**
	 * Avaliable sections to show installment price
	 *
	 * @return array
	 */
	private static function get_installment_price_sections() {
		return array(
			'product' => 'Product page',
			'cart' => 'Shopping cart',
			'checkout' => 'Checkout',
		);
	}
}

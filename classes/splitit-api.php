<?php
/**
 * SplitIt_API class
 *
 * @class       SplitIt_API
 * @version     2.0.8
 * @package     SplitIt/Classes
 * @category    API
 * @author      By Splitit
 */
class SplitIt_API {

	const ERROR_HTTP_STATUS_CODE = -2;
	const ERROR_HTTP_REQUEST = -4;
	const ERROR_JSON_RESPONSE = -8;
	const ERROR_UNKNOWN_GW_RESULT_CODE = -16;
	const ERROR_UNKNOWN_GW_RESULT_MSG = 'Unknown result from gateway.';
	const ERROR_UNKNOWN = -32;
	protected $_error = array();
	protected $_API = array();
	protected $_username = null;
	protected $_password = null;
	protected $_settings = null;
	protected $_log = null;

	public function __construct($settings) {
		$this->_API['terminal_key'] = $settings['splitit_api_terminal_key'];
		$this->_username = $settings['splitit_api_username'];
		$this->_password = $settings['splitit_api_password'];
		if ($settings['splitit_mode_sandbox'] == 'yes') {
			$this->_API['url'] = $settings['splitit_api_sand_url'];
		} else {
			$this->_API['url'] = $settings['splitit_api_prod_url'];
		}

		if ($settings['splitit_mode_debug'] == 'yes') {
			$this->_log = new SplitIt_Log;
		}

		$this->_settings = $settings;
	}

	/**
	 * PayItSimple login method
	 *
	 * @param $url
	 * @return bool|string
	 */
	public function login($url = null) {
		if (is_null($url)) {
			$url = $this->_API['url'];
		}
		$params = array('UserName' => $this->_username,
			'Password' => $this->_password,
			'TouchPoint' => array("Code" => "WooCommercePlugin", "Version" => Splitit_VERSION),
		);

		try {
			$result = $this->make_request($url, ucfirst(__FUNCTION__), $params);
			$this->_API['session_id'] = (isset($result->{'SessionId'}) && $result->{'SessionId'} != '') ? $result->{'SessionId'} : null;

			if (is_null($this->_API['session_id'])) {
				// print_r($result);
				//$this->setError(self::ERROR_UNKNOWN, $this->getError());
				if ($this->_log) {
					$this->_log->info(__FILE__, __LINE__, __METHOD__);
					$this->_log->add($this->getError());
				}
				return array('error' => $this->getError());
			}

			$error = $this->getError();
			if (count((array) $error)) {
				return array('error' => $this->getError());
			}

			$apiDetails = $this->_API;

			WC()->session->set('splitit_checkout_session_id_data', $apiDetails['session_id']);


			return $apiDetails['session_id'];
		} catch (Exception $e) {
			if ($this->_log) {
				$this->_log->info(__FILE__, __LINE__, __METHOD__);
				$this->_log->add($e);}
			return array('error' => $e->getMessage());
		}
	}

	/**
	 * PayItSimple method, that creates an installment plan wizard session from the PayItSimple platform in order for you to enable the button
	 *
	 * @return array|bool
	 * @throws Exception
	 */
	public function getEcSession($order_data) {
		global $woocommerce;
		global $wpdb;
		if (!$this->is_logged_in()) {
			$this->setError(self::ERROR_UNKNOWN, 'SessionId is not exist. Login first.');
			if ($this->_log) {
				$this->_log->info(__FILE__, __LINE__, __METHOD__);
				$this->_log->add($this->getError());
			}
			return false;
		} else {

			if ($this->_log) {$this->_log->info(__FILE__, __LINE__, __METHOD__);}


			$cancel_url = SplitIt_Helper::sanitize_redirect_url($this->_settings['splitit_cancel_url']);
			$error_url = SplitIt_Helper::sanitize_redirect_url($this->_settings['splitit_error_url']);
			$success_url = SplitIt_Helper::sanitize_redirect_url($this->_settings['splitit_success_url']);
			$custom_urls = $this->_settings['custom_urls'];
			if ($custom_urls == "custom") {
				$redirect_cancel_url = ($cancel_url == false) ? wc_get_checkout_url() : $cancel_url;
				$redirect_error_url = ($error_url == false) ? wc_get_checkout_url() : $error_url;
				$redirect_success_url = ($success_url == false) ? wc_get_checkout_url() : $success_url;
			} else {
				$redirect_cancel_url = $redirect_error_url = $redirect_success_url = wc_get_checkout_url();
			}

			$params = array();
			$site_url = site_url();

			$total_amount_in_cart = $order_data['AmountBeforeFees'];
			$depend_upon_cart_total = $this->_settings['splitit_doct'];
			$flag = 0;
			$splitit_discount_type = $this->_settings['splitit_discount_type'];
			$instOptions = "2,3,4,5,6,7,8,9,10,11,12";
			$AutoCapture = $this->_settings['splitit_payment_action'];
			if ($AutoCapture == "purchase") {
				$acpature = "true";
			} else {
				$acpature = "false";
			}

			$all_installments = "";
			if ($splitit_discount_type == "fixed") {
				$instOptions = implode(",", $this->_settings['splitit_discount_type_fixed']);
			} else {
				if (!empty($depend_upon_cart_total['ct_from'])) {
					foreach ($depend_upon_cart_total['ct_from'] as $k => $v) {
						if ($flag != 1) {
							if ($total_amount_in_cart == $v || $total_amount_in_cart == $depend_upon_cart_total['ct_to'][$k] || $total_amount_in_cart <= $v || $total_amount_in_cart <= $depend_upon_cart_total['ct_to'][$k]) {
								$flag = 1;
								$all_installments = $depend_upon_cart_total['ct_instllment'][$k];
								$instOptions = implode(",", $all_installments);

							}
						}

					}
				}

			}

			$installmentOptionsArr = explode(',', $instOptions);
			$splitit_default_selected_installment = isset($this->_settings['splitit_default_selected_installment'])?$this->_settings['splitit_default_selected_installment']:array_rand($installmentOptionsArr);

			if (!$splitit_default_selected_installment || !in_array($splitit_default_selected_installment, $installmentOptionsArr)) {
				$index = floor(count($installmentOptionsArr) / 2);
				$splitit_default_selected_installment = $installmentOptionsArr[$index];
			}

			$CurrencyCode = "USD";
			if (get_woocommerce_currency() != "") {
				$CurrencyCode = get_woocommerce_currency();
			}

			$firstInstallmentAmount = 0;
			$firstPayment = $this->_settings['splitit_first_installment'];


			if ($firstPayment == "percent") {
				$percentageOfOrder = $this->_settings['splitit_first_installment_percent'];
				if ($percentageOfOrder) {
					if ($percentageOfOrder > 50) {
						$percentageOfOrder = 50;
					}
					$firstInstallmentAmount = round((($order_data['AmountBeforeFees'] * $percentageOfOrder) / 100), 2);
				}
			} elseif ($firstPayment == "shipping") {
				$firstInstallmentAmount = round(WC()->cart->shipping_total, 2);
			} elseif ($firstPayment == "shipping_taxes") {
				$firstInstallmentAmount = round(WC()->cart->shipping_total, 2) + round(WC()->cart->tax_total, 2);
			}

			$params['RequestHeader'] = array(
				"SessionId" => $this->_API['session_id'],
				"ApiKey" => $this->_API['terminal_key'],
			);
			$params['PlanData'] = array(
				"Amount" => array(
					"Value" => $order_data['AmountBeforeFees'],
					"CurrencyCode" => $CurrencyCode,
				),
				"NumberOfInstallments" => $splitit_default_selected_installment,
				"RefOrderNumber" => "",
				"AutoCapture" => $acpature,
			);
			$params['BillingAddress'] = array(
				"AddressLine" => $order_data['Address'],
				"AddressLine2" => $order_data['Address2'],
				"City" => $order_data['City'],
				"State" => $order_data['State'],
				"Country" => $order_data['Country'],
				"Zip" => $order_data['Zip'],
			);
			$params['ConsumerData'] = array(
				"FullName" => $order_data['ConsumerFullName'],
				"Email" => $order_data['Email'],
				"PhoneNumber" => $order_data['Phone'],
                "CultureName" => $culture = str_replace('_', '-', get_locale()),
			);
			$params['PaymentWizardData'] = array(

				"RequestedNumberOfInstallments" => $instOptions,

				"SuccessExitURL" => $redirect_success_url . '?wc-api=splitit_payment_success',

				"CancelExitURL" => $redirect_cancel_url . '?wc-api=splitit_payment_error',

			);
			// var_dump($firstInstallmentAmount);die;
			if ($firstInstallmentAmount > 0) {
				$params['PlanData']['FirstInstallmentAmount'] = array(
					"Value" => $firstInstallmentAmount,
					"CurrencyCode" => $CurrencyCode,
				);
			}

            $cart = WC()->cart;
			$items = $cart->get_cart();
			if (!function_exists('is_plugin_active')) {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}
            if (is_plugin_active('woocommerce-avatax/woocommerce-avatax.php')) {     // if avatax enabled, recalculate taxes
                define(WOOCOMMERCE_CHECKOUT, true);
                $this->removeTaxCache();
				new WC_Cart_Totals($cart);
				WC()->cart->calculate_totals();
            }
			$itemsArr = array();
			foreach ($items as $item => $values) {
				array_push($itemsArr, array(
					'Name' => $values['data']->get_name(),
					'SKU' => $values['data']->get_sku(),
					'Price' => array('Value' => $values['data']->get_price(), 'CurrencyCode' => $CurrencyCode),
					'Quantity' => $values['quantity'],
					'Description' => strip_tags($values['data']->get_short_description()),
				));
			}
			$params['PlanData']['Amount']['Value']=WC()->cart->get_total(false);
			$params['CartData'] = array(
				"Items" => $itemsArr,
				"AmountDetails" => array(
					"Subtotal" => round(WC()->cart->get_subtotal(), 2),
					"Tax" => round(WC()->cart->get_total_tax(), 2),
					"Shipping" => round(WC()->cart->get_shipping_total(), 2),
				),
			);

			if (isset($this->_settings['splitit_async_enable']) && $this->_settings['splitit_async_enable'] == 'yes') {
				$params['PaymentWizardData']["SuccessAsyncURL"] = $site_url . '?wc-api=splitit_payment_success_async';
				$params["EventsEndpoints"] = array(
						"CreateSucceeded" => $site_url . '?wc-api=splitit_payment_success_async'
					);
			}

			if (isset($this->_settings['splitit_3d_secure']) && $this->_settings['splitit_3d_secure'] != "" && $this->_settings['splitit_3d_secure'] == "yes") {
				if (isset($this->_settings['splitit_3d_secure_min_amount']) && ($this->_settings['splitit_3d_secure_min_amount'] == "")) {
					$this->_settings['splitit_3d_secure_min_amount'] = 0;
				}
				if (floatval($params['PlanData']['Amount']['Value']) >= floatval($this->_settings['splitit_3d_secure_min_amount'])) {
					$params['PlanData']["Attempt3DSecure"] = true;
					$params["RedirectUrls"] = array(
						"Succeeded" => $redirect_success_url . '?wc-api=splitit_payment_success',
						"Failed" => $redirect_cancel_url . '?wc-api=splitit_payment_error',
						"Canceled" => $redirect_cancel_url . '?wc-api=splitit_payment_error',
					);
					
					unset($params['PaymentWizardData']['SuccessExitURL']);
					unset($params['PaymentWizardData']['CancelExitURL']);
					unset($params['PaymentWizardData']['SuccessAsyncURL']);
				}
			}

			define('WOOCOMMERCE_CHECKOUT', true);
			define('WOOCOMMERCE_CART', true);
			$fetch_session_item = WC()->session->get('chosen_shipping_methods');
			$shipping_method_cost = "";
			$shipping_method_cost = WC()->cart->shipping_total;
			$shipping_methods = WC()->shipping->get_shipping_methods();
			$shipping_method_id = "";
			if (!empty($fetch_session_item)) {
				$explode_items = explode(":", $fetch_session_item[0]);
				$shipping_method_id = $explode_items[0];
			} else {
				$shipping_method_id = "";
			}
			$shipping_method_title = "";
			$coupon_code = "";
			$coupon_amount = "";
			$applied_coupon_array = $woocommerce->cart->get_applied_coupons();
			if (!empty($applied_coupon_array)) {
				$discount_array = $woocommerce->cart->coupon_discount_amounts;
				foreach ($discount_array as $key => $value) {
					$coupon_code = $key;
					$coupon_amount = wc_format_decimal(number_format($discount_array[$key], 2));
				}
			}

			/*total variables*/
			$set_shipping_total = WC()->cart->shipping_total;
			$set_discount_total = WC()->cart->get_cart_discount_total();
			$set_discount_tax = WC()->cart->get_cart_discount_tax_total();
			$set_cart_tax = WC()->cart->tax_total;
			$set_shipping_tax = WC()->cart->shipping_tax_total;
			$set_total = WC()->cart->total;
			$wc_cart = json_encode(WC()->cart);
			$get_packages = json_encode(WC()->shipping->get_packages());
			$chosen_shipping_methods_data = json_encode(WC()->session->get('chosen_shipping_methods'));

			/*end*/

			$total_tax_amount = "";
			$total_taxes_array = WC()->cart->get_taxes();
			if (!empty($total_taxes_array)) {
				$total_tax_amount = array_sum($total_taxes_array);
				$total_tax_amount = wc_format_decimal(number_format($total_tax_amount, 2));

			}

			if ($shipping_method_id != "" && !empty($shipping_methods[$shipping_method_id])) {
				$shipping_method_title = $shipping_methods[$shipping_method_id]->method_title;
			}

			try {
                if ($this->_log) {
                    $this->_log->info(__FILE__, __LINE__, __METHOD__);
                    $this->_log->add($params);
                }
				$result = $this->make_request($this->_API['url'], "InstallmentPlan/Initiate", $params);
				$userid = "0";
				if (is_user_logged_in()) {
					$userid = get_current_user_id();
				}
				// print_r($result);die;
				if (isset($result) && isset($result->InstallmentPlan) && isset($result->InstallmentPlan->InstallmentPlanNumber)) {
					$table_name = $wpdb->prefix . 'splitit_logs';
					$ipn = $result->InstallmentPlan->InstallmentPlanNumber;

					$user_data = "";
					if (isset($_COOKIE['splitit_checkout'])) {
						$user_data = rawurldecode(wc_clean($_COOKIE['splitit_checkout']));
					}

					if ($ipn != "" && $user_data != "") {
						$wpdb->insert(
							$table_name,
							array(
								'ipn' => $ipn,
								'user_id' => $userid,
								'cart_items' => json_encode(WC()->cart->get_cart()),
								'shipping_method_cost' => $shipping_method_cost,
								'shipping_method_title' => $shipping_method_title,
								'shipping_method_id' => $shipping_method_id,
								'coupon_amount' => $coupon_amount,
								'coupon_code' => $coupon_code,
								'tax_amount' => $total_tax_amount,
								'user_data' => $user_data,
								'set_shipping_total' => $set_shipping_total,
								'set_discount_total' => $set_discount_total,
								'set_discount_tax' => $set_discount_tax,
								'set_cart_tax' => $set_cart_tax,
								'set_shipping_tax' => $set_shipping_tax,
								'set_total' => $set_total,
								'wc_cart' => $wc_cart,
								'get_packages' => $get_packages,
								'chosen_shipping_methods_data' => $chosen_shipping_methods_data,
								'updated_at' => date('Y-m-d H:i:s'),
							)
						);
					}

				}
				return $result;
			} catch (Exception $e) {
				if ($this->_log) {
					$this->_log->info(__FILE__, __LINE__, __METHOD__);
					$this->_log->add($e);}
				return json_encode(array('error' => $e->getMessage()));
			}
		}
	}

	/**
	 * Charge customer by transaction id ($InstallmentPlanNumber)
	 *
	 * @param $InstallmentPlanNumber
	 * @param $SessionId
	 * @return array|bool
	 */
	public function capture($InstallmentPlanNumber, $SessionId) {
		$params = array(
			'RequestHeader' => array('SessionId' => $SessionId),
			'InstallmentPlanNumber' => $InstallmentPlanNumber,
		);
		$result = $this->make_request($this->_API['url'], 'InstallmentPlan/StartInstallments', $params);
		if (count($this->getError())) {
			if (!is_admin()) {
				wc_clear_notices();
				wc_add_notice(SplitIt_Helper::format_error($this->getError()), 'error');
				return false;
			}
		}
		return $result;
	}

	/**
	 * Cancel order by transaction id ($InstallmentPlanNumber)
	 *
	 * @param $InstallmentPlanNumber
	 * @return array|bool
	 */
	public function cancel($InstallmentPlanNumber) {
		$params = array(
			"RequestHeader" => array(
				"SessionId" => $this->login(),
			),
			"InstallmentPlanNumber" => $InstallmentPlanNumber,
			"RefundUnderCancelation" => "OnlyIfAFullRefundIsPossible",
		);
		$result = $this->make_request($this->_API['url'], 'InstallmentPlan/Cancel', $params);
		if (count($this->getError())) {
			if (!is_admin()) {
				wc_clear_notices();
				wc_add_notice(SplitIt_Helper::format_error($this->getError()), 'error');
				return false;
			}
		}
		return $result;
	}

	/**
	 * Refund order by transaction id ($InstallmentPlanNumber)
	 *
	 * @param $InstallmentPlanNumber
	 * @param $amount
	 * @param $reason
	 * @return array|bool
	 */
	public function refund($InstallmentPlanNumber, $amount, $reason) {
		$params = array(
			"RequestHeader" => array(
				"SessionId" => $this->login(),
				/*"ApiKey" => $this->_API['terminal_key']*/
			),
			"InstallmentPlanNumber" => $InstallmentPlanNumber,
			"Amount" => array("Value" => $amount),
			"_RefundStrategy" => "FutureInstallmentsFirst",
		);
		/*print_r($params);*/
		$result = $this->make_request($this->_API['url'], 'InstallmentPlan/Refund', $params);
		/*print_r($result);*/
		if (count($this->getError())) {
			if (!is_admin()) {
				wc_clear_notices();
				wc_add_notice(SplitIt_Helper::format_error($this->getError()), 'error');
				return false;
			}
		}
		return $result;
	}

	/**
	 * Installment plan update
	 *
	 * @param $OrderID
	 * @param $SessionId
	 * @return array|bool
	 */
	public function installment_plan_update($order_id, $SessionId, $InstallmentPlanNumber) {

		$params = array(
			'RequestHeader' => array('SessionId' => $SessionId),
			'InstallmentPlanNumber' => $InstallmentPlanNumber,
			'PlanData' => array('RefOrderNumber' => $order_id),
		);
		$result = $this->make_request($this->_API['url'], 'InstallmentPlan/Update', $params);
		if (count($this->getError())) {
			if (!is_admin()) {
				wc_clear_notices();
				wc_add_notice(SplitIt_Helper::format_error($this->getError()), 'error');
				return false;
			} else {
				$error_id = $result->{'InstallmentPlan'}->{'InstallmentPlanStatus'}->{'Id'};
				if ($error_id) {
					$e['error'] = 'Something went wrong.Please contact to the Administrator.';
				} else {
					$e['error'] = SplitIt_Helper::format_error($this->getError());
				}
				return $e;
			}
		}
		return $result;
	}

	/**
	 * Get installment plan data
	 *
	 * @param $criteria
	 * @param $SessionId
	 * @return array|bool
	 */
	public function get($SessionId, $criteria) {
		$params = array(
			'RequestHeader' => array('SessionId' => $SessionId),
			'QueryCriteria' => $criteria,
			'LoadRelated' => array('Installments' => 'ALL', 'SecureAuthorizations' => 'ALL'),
			'PagingRequest' => array('Skip' => 0, 'Take' => 1),
		);
		$result = $this->make_request($this->_API['url'], 'InstallmentPlan/Get', $params);
		if (count($this->getError())) {
			if (!is_admin()) {
				wc_clear_notices();
				wc_add_notice(SplitIt_Helper::format_error($this->getError()), 'error');
				return false;
			}
		}
		return $result;
	}

	/**
	 * verify payment
	 *
	 * @param $IPN
	 * @param $SessionId
	 * @return array|bool
	 */
	public function verifyPayment($SessionId, $IPN) {
		$params = array(
			'RequestHeader' => array('SessionId' => $SessionId),
			'InstallmentPlanNumber' => $IPN
		);
		$result = $this->make_request($this->_API['url'], 'InstallmentPlan/Get/VerifyPayment', $params);
		if (count($this->getError())) {
			if (!is_admin()) {
				wc_clear_notices();
				wc_add_notice(SplitIt_Helper::format_error($this->getError()), 'error');
				return false;
			}
		}
		return $result;
	}

	/**
	 * @return bool
	 */
	public function is_logged_in() {
		if (is_null($this->_API['session_id'])) {
			$this->login();
		}
		return (!is_null($this->_API['session_id']));
	}

	/**

	 * @param $gwUrl string
	 * @param $method string
	 * @param $params array
	 *
	 * @return bool|array
	 */
	protected function make_request($url, $method, $params) {
		$request = new WP_Http;
		$args = array(
			'method' => 'POST',
			'timeout' => 300,
			'blocking' => true,
			'headers' => array('Content-Type' => 'application/json;charset=utf-8'),
			'user-agent' => $_SERVER['HTTP_USER_AGENT'],
			'body' => json_encode($params),
		);

		$response = $request->request(trim($url, '/') . '/api/' . $method . '?format=JSON', $args);
		$result = json_decode($response['body']);
		if ('InstallmentPlan/Update' == $method) {
			//setcookie("mk_method1", $result);
		}
		if ('InstallmentPlan/StartInstallments' != $method &&
			'InstallmentPlan/Get' != $method &&
			'InstallmentPlan/Get/VerifyPayment' != $method &&
			'InstallmentPlan/Cancel' != $method &&
			'Login' != $method &&
			'InstallmentPlan/Initiate' != $method &&
			'InstallmentPlan/Refund' != $method &&
			'InstallmentPlan/Update' != $method) {

			if ($this->_log) {
				$this->_log->info(__FILE__, __LINE__, __METHOD__);
				$this->_log->add(self::ERROR_UNKNOWN_GW_RESULT_MSG);
			}
			$this->setError(self::ERROR_UNKNOWN_GW_RESULT_CODE, self::ERROR_UNKNOWN_GW_RESULT_MSG);

		} else {
			if (isset($result->{'ResponseHeader'}->{'Errors'}) && !empty($result->{'ResponseHeader'}->{'Errors'}) && $result->{'ResponseHeader'}->{'Errors'}[0]->{'ErrorCode'} != "") {
				if ($this->_log) {
					$this->_log->info(__FILE__, __LINE__, __METHOD__);
					$this->_log->add($this->setError((int) $result->{'ResponseHeader'}->{'Errors'}[0]->{'ErrorCode'}, $result->{'ResponseHeader'}->{'Errors'}[0]->{'Message'}));
				}
				$this->setError((int) $result->{'ResponseHeader'}->{'Errors'}[0]->{'ErrorCode'}, $result->{'ResponseHeader'}->{'Errors'}[0]->{'Message'});
			}

		}

		if (count($this->getError())) {
			return $this->getError();
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getError() {
		return $this->_error;
	}
	/**
	 * @param $errorCode int
	 * @param $errorMsg string
	 */
	protected function setError($errorCode, $errorMsg) {
		$this->_error = array('code' => $errorCode, 'message' => $errorMsg);
	}

    /**
     * Remove tax cache to allow recalculate tax when avatax is installed
     */
    public function removeTaxCache()
    {
        global $wp_object_cache;
        $cache = $wp_object_cache->cache;
        unset($cache['taxes']);
        $wp_object_cache->cache = $cache;
    }
}

<?php
/**
 * SplitIt_Helper class
 *
 * @class       SplitIt_Helper
 * @version     0.2.9
 * @package     SplitIt/Classes
 * @category    Helper
 * @author      By Splitit
 */

class SplitIt_Helper {

    private static $exitSafelyFlag = false;
    protected static $allowSetCookie = true;
	/**
	 * Ajax handler for check api settings
	 */
	public static function admin_js() {
		if (isset($_GET['section']) && $_GET['section'] == 'splitit') {
			wp_enqueue_style('splitit_admin_css', plugins_url('/assets/css/jquery-ui.css', dirname(__FILE__)));
			wp_enqueue_style('splitit_prodlist_admin_css', plugins_url('/assets/css/prodlist.css', dirname(__FILE__)));
			wp_enqueue_script('splitit-admin', plugins_url('/assets/javascript/splitit-admin.js', dirname(__FILE__)), array('jquery'));
		}
		if(isset($_GET['post_type']) && $_GET['post_type']=='shop_order'){
			wp_enqueue_style('splitit_order_admin_css', plugins_url('/assets/css/splitit-admin-order.css', dirname(__FILE__)));
			wp_enqueue_script('splitit-admin-order', plugins_url('/assets/javascript/splitit-admin-order.js', dirname(__FILE__)), array('jquery'));			
		}
		if (isset($_GET['page']) && $_GET['page'] == 'wc-settings' && isset($_GET['tab']) && $_GET['tab'] == 'checkout') {
			wp_enqueue_style('splitit_prodlist_admin_css', plugins_url('/assets/css/splitit-admin.css', dirname(__FILE__)));
		}
		/* support multi currency plugin */
		if (isset($_GET['page']) && $_GET['page'] == 'woocommerce-multi-currency') {
			wp_enqueue_style('splitit_order_admin_css', plugins_url('/assets/css/splitit-admin-order.css', dirname(__FILE__)));
		}
	}

	/**
	 * Checkout ajax and js scripts
	 */
	public static function checkout_js() {
		wp_enqueue_script('splitit-checkout', plugins_url('/assets/javascript/splitit-checkout.js', dirname(__FILE__)), array('jquery'));
	}

	/**
	 * Styles to hide checkout subtotal installment price
	 */
	public static function front_css() {
		wp_enqueue_style('splitit-front', plugins_url('/assets/css/splitit-front.css', dirname(__FILE__)));
	}

	/**
	 * Error formatting function
	 *
	 * @param $error
	 * @return string
	 */
	public static function format_error($error) {
		return 'Error ' . $error['code'] . ': ' . $error['message'];
	}

	/**
	 * Sanitize redirect url string
	 */
	public static function sanitize_redirect_url($url) {
		if ($url != '') {
			$checkout_url = explode('checkout', wc_get_checkout_url()); //using this way to get index.php if needed
			$base_url = rtrim($checkout_url[0], '/');
			if (strpos($url, '.') !== false) {
				//url contain file extension, like .php/.html etc.
				$url = strip_tags(trim($url, '/'));
			} else {
				$url = strip_tags(trim($url, '/')) . '/';
			}

			return $base_url . '/' . $url;
		}
		return false;
	}

    /**
     * @param bool $flag
     */
	public static function set_exit_safely_flag(bool $flag) {
	    self::$exitSafelyFlag = $flag;
    }

    /**
     * @throws Exception
     */
	public static function exit_safely() {
	    if (self::$exitSafelyFlag) {
	        throw new Exception('Exit');
        }

	    exit;
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     */
    public static function setCookie($name, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        if (self::$allowSetCookie) {
            setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }
    }

    /**
     * @param bool $flag
     */
    public static function setCookieFlag(bool $flag)
    {
        self::$allowSetCookie = $flag;
    }
}
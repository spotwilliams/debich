<?php
/*
Plugin Name: Call for Price for WooCommerce
Plugin URI: https://wpfactory.com/item/woocommerce-call-for-price-plugin/
Description: Plugin extends WooCommerce by outputting "Call for Price" when price field for product is left empty.
Version: 3.2.2
Author: Algoritmika Ltd
Author URI: http://www.algoritmika.com
Text Domain: woocommerce-call-for-price
Domain Path: /langs
Copyright: � 2018 Algoritmika Ltd.
WC tested up to: 3.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'woocommerce-call-for-price.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'woocommerce-call-for-price-pro/woocommerce-call-for-price-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_Woocommerce_Call_For_Price' ) ) :

/**
 * Main Alg_Woocommerce_Call_For_Price Class
 *
 * @class   Alg_Woocommerce_Call_For_Price
 * @version 3.2.2
 */
final class Alg_Woocommerce_Call_For_Price {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 3.0.0
	 */
	public $version = '3.2.2';

	/**
	 * @var Alg_Woocommerce_Call_For_Price The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_Woocommerce_Call_For_Price Instance
	 *
	 * Ensures only one instance of Alg_Woocommerce_Call_For_Price is loaded or can be loaded.
	 *
	 * @static
	 * @return Alg_Woocommerce_Call_For_Price - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_Woocommerce_Call_For_Price Constructor.
	 *
	 * @access  public
	 * @version 3.0.0
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'woocommerce-call-for-price', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Include required files
		$this->includes();

		// Settings
		if ( is_admin() ) {
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		}
	}

	/**
	 * Show action links on the plugin screen
	 *
	 * @param   mixed $links
	 * @return  array
	 * @version 3.1.1
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_call_for_price' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'woocommerce-call-for-price.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a target="_blank" href="https://wpfactory.com/item/woocommerce-call-for-price-plugin/">' . __( 'Unlock All', 'woocommerce-call-for-price' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 3.2.2
	 */
	private function includes() {
		$this->settings = array();
		$this->settings['general']       = require_once( 'includes/admin/class-wc-call-for-price-settings-general.php' );
		$this->settings['product-types'] = require_once( 'includes/admin/class-wc-call-for-price-settings-product-types.php' );
		if ( is_admin() && get_option( 'alg_wc_call_for_price_version', '' ) !== $this->version ) {
			foreach ( $this->settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? ( bool ) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
			$this->handle_deprecated_options();
			update_option( 'alg_wc_call_for_price_version', $this->version );
		}
		require_once( 'includes/class-wc-call-for-price.php' );
	}

	/**
	 * handle_deprecated_options.
	 *
	 * @version 3.0.2
	 * @since   3.0.0
	 */
	function handle_deprecated_options() {
		$deprecated_settings = array(
			// v3.0.0
			'woocommerce_call_for_price_enabled'         => 'alg_wc_call_for_price_enabled',
			'woocommerce_call_for_price_text'            => 'alg_wc_call_for_price_text_simple_single',
			'woocommerce_call_for_price_text_on_archive' => 'alg_wc_call_for_price_text_simple_archive',
			'woocommerce_call_for_price_text_on_home'    => 'alg_wc_call_for_price_text_simple_home',
			'woocommerce_call_for_price_text_on_related' => 'alg_wc_call_for_price_text_simple_related',
			'woocommerce_call_for_price_hide_sale_sign'  => 'alg_wc_call_for_price_hide_sale_sign',
		);
		foreach ( $deprecated_settings as $old => $new ) {
			if ( false !== ( $old_value = get_option( $old ) ) ) {
				update_option( $new, $old_value );
				delete_option( $old );
			}
		}
	}

	/**
	 * Add Woocommerce settings tab to WooCommerce settings.
	 *
	 * @version 3.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = include( 'includes/admin/class-wc-settings-call-for-price.php' );
		return $settings;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}
}

endif;

if ( ! function_exists( 'alg_woocommerce_call_for_price' ) ) {
	/**
	 * Returns the main instance of Alg_Woocommerce_Call_For_Price to prevent the need to use globals.
	 *
	 * @return  Alg_Woocommerce_Call_For_Price
	 * @version 3.0.0
	 */
	function alg_woocommerce_call_for_price() {
		return Alg_Woocommerce_Call_For_Price::instance();
	}
}

alg_woocommerce_call_for_price();

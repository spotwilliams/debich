<?php
/**
 * WooCommerce Call for Price
 *
 * @version 3.2.2
 * @since   2.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Call_For_Price' ) ) :

class Alg_WC_Call_For_Price {

	/**
	 * Constructor.
	 *
	 * @version 3.2.2
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_call_for_price_enabled', 'yes' ) ) {
			// Class properties
			$this->is_wc_below_3_0_0 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );
			// Empty price hooks
			add_action( 'init', array( $this, 'add_hooks' ), PHP_INT_MAX );
			// Sale flash
			add_filter( 'woocommerce_sale_flash', array( $this, 'hide_sales_flash' ), PHP_INT_MAX, 3 );
			// Variable products
			if ( 'yes' === get_option( 'alg_wc_call_for_price_' . 'variable' . '_enabled', 'yes' ) ) {
				if ( 'yes' === get_option( 'alg_wc_call_for_price_' . 'variable' . '_' . 'variation' . '_enabled', 'yes' ) ) {
					add_filter( 'woocommerce_variation_is_visible', array( $this, 'make_variation_visible_with_empty_price' ), PHP_INT_MAX, 4 );
					add_action( 'admin_head', array( $this, 'hide_variation_price_required_placeholder' ), PHP_INT_MAX );
				}
				if ( 'yes' === get_option( 'alg_wc_call_for_price_hide_variations_add_to_cart_button', 'yes' ) ) {
					add_action( 'wp_head', array( $this, 'hide_disabled_variation_add_to_cart_button' ) );
				}
			}
			// Per product meta box
			if ( 'yes' === apply_filters( 'alg_call_for_price', 'no', 'per_product' ) ) {
				require_once( 'admin/class-wc-call-for-price-settings-per-product.php' );
			}
			// Force "Call for Price" for all products
			if ( 'yes' === get_option( 'alg_call_for_price_make_all_empty', 'no' ) ) {
				$this->hook_price_filters( 'make_empty_price' );
			}
			// Out of stock products
			if ( 'yes' === apply_filters( 'alg_call_for_price', 'no', 'out_of_stock' ) ) {
				$this->hook_price_filters( 'make_empty_price_out_of_stock' );
			}
			// "Call for Price" per product taxonomy
			if ( 'yes' === get_option( 'alg_call_for_price_make_empty_price_per_taxonomy', 'no' ) ) {
				$this->hook_price_filters( 'make_empty_price_per_taxonomy' );
			}
			// "Call for Price" by product price
			if ( 'yes' === get_option( 'alg_call_for_price_make_empty_price_by_product_price', 'no' ) ) {
				$this->hook_price_filters( 'make_empty_price_by_product_price' );
			}
			// Variation hash (for forcing "Call for Price")
			add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'get_variation_prices_hash' ), PHP_INT_MAX, 3 );
			// Button label (archives)
			if ( 'yes' === get_option( 'alg_call_for_price_change_button_text', 'no' ) ) {
				add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'change_button_text' ), PHP_INT_MAX, 2 );
			}
			// Hide button
			if ( 'yes' === get_option( 'alg_call_for_price_hide_button', 'no' ) ) {
				add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'remove_button_on_archives' ), PHP_INT_MAX, 2 );
			}
		}
	}

	/**
	 * remove_button_on_archives.
	 *
	 * @version 3.2.2
	 * @since   3.2.2
	 */
	function remove_button_on_archives( $link, $_product ) {
		return ( '' === $_product->get_price() ? '' : $link );
	}

	/**
	 * hook_price_filters.
	 *
	 * @version 3.2.0
	 * @since   3.2.0
	 */
	function hook_price_filters( $function_name ) {
		add_filter( ( $this->is_wc_below_3_0_0 ? 'woocommerce_get_price' : 'woocommerce_product_get_price' ), array( $this, $function_name ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_variation_prices_price', array( $this, $function_name ), PHP_INT_MAX, 2 );
		if ( ! $this->is_wc_below_3_0_0 ) {
			add_filter( 'woocommerce_product_variation_get_price', array( $this, $function_name ), PHP_INT_MAX, 2 );
		}
	}

	/**
	 * change_button_text.
	 *
	 * @version 3.2.0
	 * @since   3.2.0
	 */
	function change_button_text( $text, $_product ) {
		return ( '' === $_product->get_price() ? apply_filters( 'alg_call_for_price', __( 'Call for Price', 'woocommerce-call-for-price' ), 'button_text' ) : $text );
	}

	/**
	 * make_empty_price_by_product_price.
	 *
	 * @version 3.2.1
	 * @since   3.2.1
	 */
	function make_empty_price_by_product_price( $price, $_product ) {
		$min_price = get_option( 'alg_call_for_price_make_empty_price_min_price', 0 );
		$max_price = get_option( 'alg_call_for_price_make_empty_price_max_price', 0 );
		if ( 0 == $min_price && 0 == $max_price ) {
			return $price;
		}
		if ( 0 == $max_price ) {
			$max_price = PHP_INT_MAX;
		}
		return ( $price >= $min_price && $price <= $max_price ? '' : $price );
	}

	/**
	 * make_empty_price_per_taxonomy.
	 *
	 * @version 3.2.0
	 * @since   3.2.0
	 */
	function make_empty_price_per_taxonomy( $price, $_product ) {
		foreach ( array( 'product_cat', 'product_tag' ) as $taxonomy ) {
			$term_ids = get_option( 'alg_call_for_price_make_empty_price_' . $taxonomy, '' );
			if ( ! empty( $term_ids ) ) {
				$product_id    = ( $this->is_wc_below_3_0_0 ? $_product->id : ( $_product->is_type( 'variation' ) ? $_product->get_parent_id() : $_product->get_id() ) );
				$product_terms = get_the_terms( $product_id, $taxonomy );
				if ( ! empty( $product_terms ) ) {
					foreach ( $product_terms as $product_term ) {
						if ( in_array( $product_term->term_id, $term_ids ) ) {
							return '';
						}
					}
				}
			}
		}
		return $price;
	}

	/**
	 * make_empty_price_out_of_stock.
	 *
	 * @version 3.2.0
	 * @since   3.2.0
	 */
	function make_empty_price_out_of_stock( $price, $_product ) {
		return ( ! $_product->is_in_stock() ? '' : $price );
	}

	/**
	 * get_variation_prices_hash.
	 *
	 * @version 3.2.1
	 * @since   3.1.0
	 */
	function get_variation_prices_hash( $price_hash, $_product, $display ) {
		$price_hash['alg_call_for_price'] = array(
			'force_all'               => get_option( 'alg_call_for_price_make_all_empty', 'no' ),
			'force_out_of_stock'      => apply_filters( 'alg_call_for_price', 'no', 'out_of_stock' ),
			'force_per_taxonomy'      => get_option( 'alg_call_for_price_make_empty_price_per_taxonomy', 'no' ),
			'force_per_taxonomy_cats' => get_option( 'alg_call_for_price_make_empty_price_product_cat', '' ),
			'force_per_taxonomy_tags' => get_option( 'alg_call_for_price_make_empty_price_product_tag', '' ),
			'force_by_price'          => get_option( 'alg_call_for_price_make_empty_price_by_product_price', 'no' ),
			'force_by_price_min'      => get_option( 'alg_call_for_price_make_empty_price_min_price', 0 ),
			'force_by_price_max'      => get_option( 'alg_call_for_price_make_empty_price_max_price', 0 ),
		);
		return $price_hash;
	}

	/**
	 * hide_variation_price_required_placeholder.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function hide_variation_price_required_placeholder() {
		echo '<style>
			div.variable_pricing input.wc_input_price::-webkit-input-placeholder { /* WebKit browsers */
				color: transparent;
			}
			div.variable_pricing input.wc_input_price:-moz-placeholder { /* Mozilla Firefox 4 to 18 */
				color: transparent;
			}
			div.variable_pricing input.wc_input_price::-moz-placeholder { /* Mozilla Firefox 19+ */
				color: transparent;
			}
			div.variable_pricing input.wc_input_price:-ms-input-placeholder { /* Internet Explorer 10+ */
				color: transparent;
			}
		</style>';
	}

	/**
	 * make_empty_price.
	 *
	 * @version 3.0.3
	 * @since   3.0.3
	 */
	function make_empty_price( $price, $_product ) {
		return '';
	}

	/**
	 * make_variation_visible_with_empty_price.
	 *
	 * @return  bool
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function make_variation_visible_with_empty_price( $visible, $_variation_id, $_id, $_product ) {
		if ( '' === $_product->get_price() ) {
			$visible = true;
			// Published == enabled checkbox
			if ( get_post_status( $_variation_id ) != 'publish' ) {
				$visible = false;
			}
		}
		return $visible;
	}

	/**
	 * hide_disabled_variation_add_to_cart_button.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function hide_disabled_variation_add_to_cart_button() {
		echo '<style>div.woocommerce-variation-add-to-cart-disabled { display: none ! important; }</style>';
	}

	/**
	 * add_hooks.
	 *
	 * @version 3.1.0
	 */
	function add_hooks() {

		add_filter( 'woocommerce_empty_price_html',           array( $this, 'on_empty_price' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_variable_empty_price_html',  array( $this, 'on_empty_price' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_grouped_empty_price_html',   array( $this, 'on_empty_price' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_variation_empty_price_html', array( $this, 'on_empty_price' ), PHP_INT_MAX, 2 ); // Only in < WC3

		require_once( 'class-wc-call-for-price-compatibility.php' );
	}

	/**
	 * Hide "sales" icon for empty price products.
	 *
	 * @version 3.0.0
	 */
	function hide_sales_flash( $onsale_html, $post, $_product ) {
		if ( 'yes' === get_option( 'alg_wc_call_for_price_hide_sale_sign', 'yes' ) && '' === $_product->get_price() ) {
			return '';
		}
		return $onsale_html; // No changes
	}

	/**
	 * is_enabled_per_product.
	 *
	 * @version 3.1.1
	 * @since   3.1.1
	 */
	function is_enabled_per_product( $_product_id ) {
		return ( apply_filters( 'alg_call_for_price', 'no', 'per_product' ) && 'yes' === get_post_meta( $_product_id, '_'. 'alg_wc_call_for_price_enabled', true ) );
	}

	/**
	 * On empty price filter - return the label.
	 *
	 * @version 3.2.0
	 */
	function on_empty_price( $price, $_product ) {

		// Get product type, product id and current filter
		$current_filter = current_filter();
		if ( $this->is_wc_below_3_0_0 ) {
			$_product_id = $_product->id;
			if ( $this->is_enabled_per_product( $_product_id ) ) {
				$product_type = 'per_product';
			} else {
				$product_type = 'simple'; // default
				switch ( $current_filter ) {
					case 'woocommerce_variable_empty_price_html':
					case 'woocommerce_variation_empty_price_html':
						$product_type = 'variable';
						break;
					case 'woocommerce_grouped_empty_price_html':
						$product_type = 'grouped';
						break;
					default: // 'woocommerce_empty_price_html'
						$product_type = ( $_product->is_type( 'external' ) ) ? 'external' : 'simple';
				}
			}
		} else {
			$_product_id = ( $_product->is_type( 'variation' ) ) ? $_product->get_parent_id() : $_product->get_id();
			if ( $this->is_enabled_per_product( $_product_id ) ) {
				$product_type = 'per_product';
			} else {
				if ( $_product->is_type( 'variation' ) ) {
					$current_filter = 'woocommerce_variation_empty_price_html';
					$product_type = 'variable';
				} else {
					$product_type = $_product->get_type();
				}
			}
		}

		// Check if enabled for current product type
		if ( 'per_product' != $product_type && 'yes' !== get_option( 'alg_wc_call_for_price_' . $product_type . '_enabled', 'yes' ) ) {
			return $price;
		}

		// Get view
		if ( 'per_product' === $product_type ) {
			$view = 'all_views';
		} else {
			$view = 'single'; // default
			if ( 'woocommerce_variation_empty_price_html' === $current_filter ) {
				$view = 'variation';
			} elseif ( is_single( $_product_id ) ) {
				$view = 'single';
			} elseif ( is_single() ) {
				$view = 'related';
			} elseif ( is_front_page() ) {
				$view = 'home';
			} elseif ( is_page() ) {
				$view = 'page';
			} elseif ( is_archive() ) {
				$view = 'archive';
			}

			// Check if enabled for current view
			if ( 'yes' !== get_option( 'alg_wc_call_for_price_' . $product_type . '_' . $view . '_enabled', 'yes' ) ) {
				return $price;
			}
		}

		// Apply the label
		$label = apply_filters( 'alg_call_for_price', '<strong>' . __( 'Call for Price', 'woocommerce-call-for-price' ) . '</strong>', 'value',
			$product_type, $view, array( 'product_id' => $_product_id ) );
		return do_shortcode( $label );
	}
}

endif;

return new Alg_WC_Call_For_Price();

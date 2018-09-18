<?php
/**
 * WooCommerce Call for Price - Product Types Sections Settings
 *
 * @version 3.2.1
 * @since   3.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Call_For_Price_Settings_Product_Types' ) ) :

class Alg_WC_Call_For_Price_Settings_Product_Types {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_call_for_price', array( $this, 'settings_section' ) );
		$this->product_types = array(
			'simple'   => __( 'Simple Products', 'woocommerce-call-for-price' ),
			'variable' => __( 'Variable Products', 'woocommerce-call-for-price' ),
			'grouped'  => __( 'Grouped Products', 'woocommerce-call-for-price' ),
			'external' => __( 'External Products', 'woocommerce-call-for-price' ),
		);
		foreach ( $this->product_types as $product_type_id => $product_type_desc ) {
			add_filter( 'woocommerce_get_settings_alg_call_for_price_' . $product_type_id, array( $this, 'get_settings' ), PHP_INT_MAX );
		}
		add_action( 'woocommerce_admin_field_alg_wc_call_for_price_textarea', array( $this, 'output_custom_textarea' ) );
		add_filter( 'woocommerce_admin_settings_sanitize_option',             array( $this, 'unclean_custom_textarea' ), PHP_INT_MAX, 3 );
	}

	/**
	 * unclean_custom_textarea.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function unclean_custom_textarea( $value, $option, $raw_value ) {
		return ( 'alg_wc_call_for_price_textarea' === $option['type'] ) ? $raw_value : $value;
	}

	/**
	 * output_custom_textarea.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function output_custom_textarea( $value ) {
		$option_value      = get_option( $value['id'], $value['default'] );
		$custom_attributes = ( isset( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) ? $value['custom_attributes'] : array();
		$description       = ' <p class="description">' . $value['desc'] . '</p>';
		$tooltip_html      = ( isset( $value['desc_tip'] ) && '' != $value['desc_tip'] ) ? '<span class="woocommerce-help-tip" data-tip="' . $value['desc_tip'] . '"></span>' : '';
		?><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				<?php echo $tooltip_html; ?>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<?php echo $description; ?>
				<textarea
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
					<?php echo implode( ' ', $custom_attributes ); ?>
					><?php echo esc_textarea( $option_value );  ?></textarea>
			</td>
		</tr><?php
	}

	/**
	 * settings_section.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function settings_section( $sections ) {
		foreach ( $this->product_types as $product_type_id => $product_type_desc ) {
			$sections[ $product_type_id ] = $product_type_desc;
		}
		return $sections;
	}

	/**
	 * generate_settings_section.
	 *
	 * @version 3.2.1
	 * @since   3.0.0
	 */
	function generate_settings_section( $product_type ) {
		$views = array(
			'single'  => __( 'Single product page', 'woocommerce-call-for-price' ),
			'related' => __( 'Related products', 'woocommerce-call-for-price' ),
			'home'    => __( 'Homepage', 'woocommerce-call-for-price' ),
			'page'    => __( 'Pages (e.g. shortcodes)', 'woocommerce-call-for-price' ),
			'archive' => __( 'Archives', 'woocommerce-call-for-price' ),
		);
		if ( 'variable' === $product_type ) {
			$views['variation'] = __( 'Variations', 'woocommerce-call-for-price' );
		}
		$settings = array(
			array(
				'title'     => $this->product_types[ $product_type ],
				'type'      => 'title',
				'id'        => 'alg_wc_call_for_price_' . $product_type . '_options',
				'desc'      => apply_filters(
					'alg_call_for_price',
					sprintf(
						__( 'You will need <a target="_blank" href="%s">Call for Price for WooCommerce Pro</a> plugin to change default texts.', 'woocommerce-call-for-price' ),
						'https://wpfactory.com/item/woocommerce-call-for-price-plugin/'
					),
					'settings',
					$product_type,
					'all'
				),
			),
			array(
				'title'     => __( 'Enable/Disable', 'woocommerce-call-for-price' ),
				'desc'      => '<strong>' . __( 'Enable', 'woocommerce-call-for-price' ). ' - ' . $this->product_types[ $product_type ] . '</strong>',
				'id'        => 'alg_wc_call_for_price_' . $product_type . '_enabled',
				'default'   => 'yes',
				'type'      => 'checkbox',
			),
		);
		foreach ( $views as $view => $view_desc ) {
			$settings = array_merge( $settings, array(
				array(
					'title'     => $view_desc,
					'desc'      => __( 'Enable', 'woocommerce-call-for-price' ),
					'id'        => 'alg_wc_call_for_price_' . $product_type . '_' . $view . '_enabled',
					'default'   => 'yes',
					'type'      => 'checkbox',
				),
				array(
					'desc_tip'  => __( 'This sets the html to output on empty price. Leave blank to disable.', 'woocommerce-call-for-price' ),
					'id'        => 'alg_wc_call_for_price_text' . '_' . $product_type . '_' . $view,
					'default'   => '<strong>' . __( 'Call for Price', 'woocommerce-call-for-price' ) . '</strong>',
					'type'      => 'alg_wc_call_for_price_textarea',
					'css'       => 'width:100%',
					'custom_attributes' => apply_filters( 'alg_call_for_price', array( 'readonly' => 'readonly' ), 'settings', $product_type, $view ),
				),
			) );
		}
		$settings = array_merge( $settings, array(
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_call_for_price_' . $product_type . '_options',
			),
		) );
		return $settings;
	}

	/**
	 * get_settings.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function get_settings() {
		return ( isset( $_GET['section'] ) ) ? $this->generate_settings_section( $_GET['section'] ) : array();
	}

}

endif;

return new Alg_WC_Call_For_Price_Settings_Product_Types();

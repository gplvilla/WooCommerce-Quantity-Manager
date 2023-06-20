<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Handlers;

use Barn2\WQM_Lib\Registerable,
	Barn2\WQM_Lib\Service,
	Barn2\Plugin\WC_Quantity_Manager\Util\Cart as Cart_Util,
	Barn2\Plugin\WC_Quantity_Manager\Util\Util,
	Barn2\Plugin\WC_Quantity_Manager\Notice_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Add to Cart Handler
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Add_To_Cart implements Registerable, Service {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'handle_validation' ], 10, 5 );
	}

	/**
	 * Handles validation on add to cart.
	 * A test cart is cloned to determine which rules the product would break if
	 * it is added to the cart.
	 *
	 * @param bool $passed
	 * @param int $product_id
	 * @param int $quantity
	 * @param int|null $variation_id
	 * @param WC_Product_Variation $variation
	 * @return bool $passed
	 */
	public function handle_validation( $passed, $product_id, $quantity, $variation_id = null, $variation = null ) {
		if ( ! apply_filters( 'wc_quantity_manager_handle_add_to_cart_validation', true, $passed, $product_id, $quantity, $variation_id, $variation ) ) {
			return $passed;
		}

		if ( ! Util::user_has_rules() ) {
			return $passed;
		}

		$object_id = Util::is_integer( $variation_id ) ? $variation_id : $product_id;
		$product   = wc_get_product( $object_id );

		$allowed_product_types = apply_filters( 'wc_quantity_manager_allowed_product_types', [ 'simple', 'variable', 'variation', 'subscription', 'variable-subscription', 'subscription_variation' ] );

		if ( is_null( $product ) || $product === false ) {
			return $passed;
		}

		if ( ! in_array( $product->get_type(), $allowed_product_types, true ) ) {
			return $passed;
		}

		// clone cart to test our validations
		$test_cart = clone WC()->cart;

		// add the item(s) and get the cart item key
		$cart_item_key = $test_cart->add_to_cart( $object_id, $quantity );

		// calculates new totals for validations
		$test_cart->calculate_totals();

		// clear any notices that might have been generated by add_to_cart (e.g stock limit, not purchasable...)
		if ( ! $cart_item_key ) {
			wc_clear_notices();
		}

		// get the validatiosn
		$test_cart_validations = Cart_Util::get_cart_validations( $test_cart );

		// destroy the test cart shipping cache
		$packages = $test_cart->get_shipping_packages();

		foreach ( $packages as $key => $value ) {
			$shipping_session = "shipping_for_package_$key";
			unset( WC()->session->$shipping_session );
		}

		// destroy the test cart
		$test_cart = null;
		unset( $test_cart );

		// gets the cart validations specific to the product being added
		$add_to_cart_validations = Cart_Util::get_add_to_cart_validations( $test_cart_validations, $cart_item_key, $object_id );

		if ( ! empty( $add_to_cart_validations ) ) {
			$passed = false;

			foreach ( $add_to_cart_validations as $validation ) {
				if ( ! wc_has_notice( Notice_Helper::get_add_to_cart_notice( $validation, $quantity ), 'error' ) ) {
					wc_add_notice( Notice_Helper::get_add_to_cart_notice( $validation, $quantity ), 'error' );
				}
			}
		}

		return $passed;
	}
}
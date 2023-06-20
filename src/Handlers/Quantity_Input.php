<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Handlers;

use Barn2\WQM_Lib\Registerable,
	Barn2\WQM_Lib\Service,
	Barn2\Plugin\WC_Quantity_Manager\Util\Util,
	Barn2\Plugin\WC_Quantity_Manager\Util\Quantity as Quantity_Util,
	WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Quantity Input Handler
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Quantity_Input implements Registerable, Service {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_filter( 'woocommerce_quantity_input_args', [ $this, 'quantity_input_args' ], 999, 2 );
		add_filter( 'woocommerce_available_variation', [ $this, 'available_variations' ], 20, 1 );

		add_filter( 'woocommerce_loop_add_to_cart_args', [ $this, 'loop_input_args' ], 10, 2 );
	}

	/**
	 * Filter the WC quantity input args
	 *
	 * @param   array       $args
	 * @param   WC_Product  $product
	 * @return  $args
	 */
	public function quantity_input_args( $args, WC_Product $product ) {
		if ( ! Util::user_has_rules() ) {
			return $args;
		}

		$input_args = Quantity_Util::get_calculated_quantity_restrictions( $product );

		if ( isset( $input_args['input_value'] ) ) {
			$args['input_value'] = $input_args['input_value'];
		}

		if ( isset( $input_args['step'] ) ) {
			$args['step'] = $input_args['step'];
		}

		if ( isset( $input_args['min'] ) ) {
			$args['min_value'] = $input_args['min'];
		}

		if ( isset( $input_args['max'] ) ) {
			$args['max_value'] = $input_args['max'];
		}

		return $args;
	}

	/**
	 * Filters the variation input args.
	 * These are applied to the variation qty input in wqm-frontend.js
	 *
	 * @param array $data
	 * @return array $data
	 */
	public function available_variations( $data ) {
		if ( ! Util::user_has_rules() ) {
			return $data;
		}

		$product = wc_get_product( $data['variation_id'] );

		if ( ! $product ) {
			return $data;
		}

		$input_args = Quantity_Util::get_calculated_quantity_restrictions( $product );

		if ( isset( $input_args['input_value'] ) ) {
			$data['input_value'] = $input_args['input_value'];
		}

		if ( isset( $input_args['step'] ) ) {
			$data['step'] = $input_args['step'];
		}

		if ( isset( $input_args['min'] ) ) {
			$data['min_qty'] = $input_args['min'];
		}

		if ( isset( $input_args['max'] ) ) {
			$data['max_qty'] = $input_args['max'];
		}

		return $data;
	}

	/**
	 * Filter the WC loop add to carts input args
	 *
	 * @param   array         $input_args
	 * @param   WC_Product    $product
	 *
	 * @return  array         $input_args
	 */
	public function loop_input_args( $input_args, WC_Product $product ) {
		if ( ! Util::user_has_rules() ) {
			return $input_args;
		}

		$input_args = array_merge( $input_args, [ 'quantity' => Quantity_Util::get_min_purchasable_quantity( $product ) ] );

		return $input_args;
	}

}

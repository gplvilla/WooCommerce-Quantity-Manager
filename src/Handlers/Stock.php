<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Handlers;

use Barn2\WQM_Lib\Registerable,
	Barn2\WQM_Lib\Service,
	Barn2\Plugin\WC_Quantity_Manager\Util\Util,
	Barn2\Plugin\WC_Quantity_Manager\Util\Quantity as Quantity_Util;

defined( 'ABSPATH' ) || exit;

/**
 * Stock Handler
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Stock implements Registerable, Service {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_filter( 'woocommerce_product_is_in_stock', [ $this, 'is_in_stock' ], 999, 2 );
	}

	/**
	 * Consider as out of stock if the minimum purchasable quantity
	 *
	 * @param mixed $in_stock
	 * @param mixed $product
	 * @return mixed
	 */
	public function is_in_stock( $in_stock, $product ) {
		if ( ! apply_filters( 'wc_quantity_manager_handle_stock_status', true ) ) {
			return $in_stock;
		}

		if ( ! Util::user_has_rules() || ! $product->managing_stock() ) {
			return $in_stock;
		}

		$allowed_product_types = apply_filters( 'wc_quantity_manager_allowed_product_types', [ 'simple', 'variation' ] );

		if ( ! in_array( $product->get_type(), $allowed_product_types, true ) ) {
			return $in_stock;
		}

		$restrictions = Quantity_Util::get_calculated_quantity_restrictions( $product );

		if ( isset( $restrictions['min'] ) && Util::is_integer( $restrictions['min'] ) && ! $product->has_enough_stock( $restrictions['min'] ) ) {
			$in_stock = false;
		}

		return $in_stock;
	}

}

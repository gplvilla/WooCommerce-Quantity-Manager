<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Rules;

use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Default Quantity
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Default_Quantity extends Abstract_Rule {

	/**
	 * Constructor.
	 *
	 * @param WC_Product $product
	 */
	public function __construct( WC_Product $product ) {
		$this->type        = 'default_quantity';
		$this->data_key    = 'default_quantity';
		$this->sanitize_cb = 'absint';

		parent::__construct( $product );
	}

	/**
	 * Calculate and set the rule value
	 */
	public function calculate_rules() {
		parent::calculate_rules();

		$this->rule_value = apply_filters_deprecated(
			'woocommerce_default_quantity_value',
			[ $this->rule_value, $this->product ],
			'2.0',
			'wc_quantity_manager_default_quantity_rule_value'
		);
	}

	/**
	 * Check the qualifying total meets the rule conditions,
	 *
	 * @param float $qualifying_total
	 */
	public function check_cart_validation( $qualifying_total ) {}

	/**
	 * Get a Cart_Validation object.
	 *
	 * @param Barn2\Plugin\WC_Quantity_Manager\Rules\WC_Cart $cart
	 * @param string $cart_item_key
	 */
	public function get_cart_validation( $cart, $cart_item_key ) {}
}

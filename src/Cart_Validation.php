<?php

namespace Barn2\Plugin\WC_Quantity_Manager;

use Barn2\Plugin\WC_Quantity_Manager\Rules\Abstract_Rule;

defined( 'ABSPATH' ) || exit;

/**
 * Cart Validation Object
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Cart_Validation {

	public $passed;
	public $cart_item_key;
	public $qualifying_total;
	public $rule;

	/**
	 * Constructor.
	 *
	 * @param string $cart_item_key
	 * @param mixed $qualifying_total
	 * @param Abstract_Rule $rule
	 */
	public function __construct( string $cart_item_key, $qualifying_total, Abstract_Rule $rule ) {
		$this->cart_item_key    = $cart_item_key;
		$this->rule             = $rule;
		$this->qualifying_total = $qualifying_total;
		$this->passed           = $this->rule->check_cart_validation( $this->qualifying_total );
	}

	/**
	 * Has the validation passeed.
	 *
	 * @return bool
	 */
	public function has_passed() {
		return $this->passed;
	}

	/**
	 * Get rule type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->rule->get_type();
	}

	/**
	 * Get rule value.
	 *
	 * @return mixed
	 */
	public function get_rule_value() {
		return $this->rule->get_value();
	}

	/**
	 * Get the product name.
	 *
	 * @return string
	 */
	public function get_product_name() {
		return $this->rule->product->get_name();
	}

	/**
	 * Get the qualifying total.
	 *
	 * @return mixed
	 */
	public function get_qualifying_total() {
		return $this->qualifying_total;
	}

	/**
	 * Get the cart item key.
	 *
	 * @return string
	 */
	public function get_cart_item_key() {
		return $this->cart_item_key;
	}

	/**
	 * Get the rule level.
	 *
	 * @return string|null
	 */
	public function get_rule_level() {
		return $this->rule->get_level();
	}

	/**
	 * Get the category ID.
	 *
	 * @return int|null
	 */
	public function get_category_id() {
		return $this->rule->get_category_id();
	}

	/**
	 * Check if this fails a maximum based rule.
	 *
	 * @return bool
	 */
	public function failed_max() {
		if ( ! in_array( $this->get_type(), [ 'quantity_rules', 'value_rules' ], true ) ) {
			return false;
		}

		if ( ! $this->rule->get_max() ) {
			return false;
		}

		if ( $this->qualifying_total <= $this->rule->get_max() ) {
			return false;
		}

		return true;
	}
}

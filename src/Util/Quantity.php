<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Util;

use Barn2\Plugin\WC_Quantity_Manager\Rules,
	Barn2\Plugin\WC_Quantity_Manager\Util\Field as Field_Util;

defined( 'ABSPATH' ) || exit;

/**
 * Quantity Utilities
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
final class Quantity {

	/**
	 * Get the calculated min, max, step and default value.
	 *
	 * @param \WC_Product $product
	 * @return array
	 */
	public static function get_calculated_quantity_restrictions( \WC_Product $product ) {
		$input_args = self::include_quantity_step_calculation( $product ) ?
			self::determine_restrictions( $product ) : self::determine_restrictions_without_step( $product );

		return $input_args;
	}

	/**
	 * Get the minimum purchasable quantity
	 *
	 * @param \WC_Product $product
	 * @return int
	 */
	public static function get_min_purchasable_quantity( \WC_Product $product ) {
		$quantity = self::include_quantity_step_calculation( $product ) ?
			self::determine_min_purchasable_quantity( $product ) : self::determine_min_purchasable_quantity_without_step( $product );

		return $quantity;
	}

	/**
	 * Determines the minimum purchasable quantity (used for loop input args).
	 *
	 * @param \WC_Product $product
	 * @return int $quantity
	 */
	private static function determine_min_purchasable_quantity( \WC_Product $product ) {
		$quantity               = 1;
		$default_quantity_rule  = new Rules\Default_Quantity( $product );
		$default_quantity_value = $default_quantity_rule->get_value();

		if ( $default_quantity_value !== false ) {
			$quantity = $default_quantity_value;
		}

		if ( $quantity === 0 ) {
			$quantity = 1;
		}

		// Quantity Step
		$quantity_step_rule  = new Rules\Quantity_Step( $product );
		$quantity_step_value = $quantity_step_rule->get_value();

		if ( is_numeric( $quantity_step_value ) && $quantity_step_value > 0 ) {
			// change quantity to step
			$quantity = $quantity % $quantity_step_value === 0 ? $quantity : $quantity_step_value;
		}

		$min_max_quantity_rule = new Rules\Min_Max_Quantity( $product );

		// Max Quantity
		if ( $min_max_quantity_rule->get_max() && $min_max_quantity_rule->get_max() < $quantity ) {
			$max_value = $min_max_quantity_rule->get_max();

			// If the max value is not a multiple of step get the closest valid multiple
			if ( $quantity_step_value !== false && $max_value % $quantity_step_value !== 0 ) {
				$max_value = floor( $max_value / $quantity_step_value ) * $quantity_step_value;
			}

			if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $max_value ) {
				$max_value = $product->get_max_purchase_quantity();

				if ( $quantity_step_value !== false && $max_value % $quantity_step_value !== 0 ) {
					$max_value = floor( $max_value / $quantity_step_value ) * $quantity_step_value;
				}
			}

			$quantity = $max_value;
		}

		// Min Quantity
		if (
			in_array( $min_max_quantity_rule->get_level(), [ 'product-simple', 'product-variation' ], true )
			&& $min_max_quantity_rule->get_min()
			&& $min_max_quantity_rule->get_min() > $quantity
		) {
			$min_value = $min_max_quantity_rule->get_min();

			// If the min value is not a multiple of step get the closest valid multiple
			if ( $quantity_step_value !== false && $min_value % $quantity_step_value !== 0 ) {
				$min_value = ceil( $min_value / $quantity_step_value ) * $quantity_step_value;
			}

			$quantity = $min_value;
		}

		return $quantity;
	}

	/**
	 * Determines minimum purchasable quantity without step calculation.
	 *
	 * @param \WC_Product $product
	 * @return int $quantity
	 */
	private static function determine_min_purchasable_quantity_without_step( \WC_Product $product ) {
		$quantity               = 1;
		$default_quantity_rule  = new Rules\Default_Quantity( $product );
		$default_quantity_value = $default_quantity_rule->get_value();

		if ( $default_quantity_value !== false ) {
			$quantity = $default_quantity_value;
		}

		if ( $quantity === 0 ) {
			$quantity = 1;
		}

		$min_max_quantity_rule = new Rules\Min_Max_Quantity( $product );

		// Max Quantity
		if ( $min_max_quantity_rule->get_max() && $min_max_quantity_rule->get_max() < $quantity ) {
			$max_value = $min_max_quantity_rule->get_max();

			if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $max_value ) {
				$max_value = $product->get_max_purchase_quantity();
			}

			$quantity = $max_value;
		}

		// Min Quantity
		if (
			in_array( $min_max_quantity_rule->get_level(), [ 'product-simple', 'product-variation' ], true )
			&& $min_max_quantity_rule->get_min()
			&& $min_max_quantity_rule->get_min() > $quantity
		) {
			$min_value = $min_max_quantity_rule->get_min();

			$quantity = $min_value;
		}

		return $quantity;
	}

	/**
	 * Determines the quantity restrictions based on the configured rules.
	 *
	 * @param \WC_Product $product
	 * @return array
	 */
	private static function determine_restrictions( \WC_Product $product ) {
		$args = [];

		// Default Quantity
		$default_quantity_rule  = new Rules\Default_Quantity( $product );
		$default_quantity_value = $default_quantity_rule->get_value();

		if ( self::change_default_quantity_input_value( $product ) && $default_quantity_value !== false ) {
			$args['input_value'] = $default_quantity_value;

			if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $default_quantity_value ) {
				$args['input_value'] = $product->get_max_purchase_quantity();
			}
		}

		// Quantity Step
		$quantity_step_rule  = new Rules\Quantity_Step( $product );
		$quantity_step_value = $quantity_step_rule->get_value();

		if ( $quantity_step_value !== false ) {
			// Set step
			$args['step'] = $quantity_step_value;
		}

		$min_max_quantity_rule = new Rules\Min_Max_Quantity( $product );

		// Max Quantity
		if ( $min_max_quantity_rule->get_max() ) {
			$max_value = $min_max_quantity_rule->get_max();

			// If the max value is not a multiple of step get the closest valid multiple
			if ( $quantity_step_value !== false && $max_value % $quantity_step_value !== 0 ) {
				$max_value = floor( $max_value / $quantity_step_value ) * $quantity_step_value;
			}

			if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $max_value ) {
				$max_value = $product->get_max_purchase_quantity();

				if ( $quantity_step_value !== false && $max_value % $quantity_step_value !== 0 ) {
					$max_value = floor( $max_value / $quantity_step_value ) * $quantity_step_value;
				}
			}

			// Set Max
			$args['max'] = $max_value;
		}

		// Min Quantity
		if ( in_array( $min_max_quantity_rule->get_level(), [ 'product-simple', 'product-variation' ], true ) && $min_max_quantity_rule->get_min() ) {
			$min_value = $min_max_quantity_rule->get_min();

			// If the min value is not a multiple of step get the closest valid multiple
			if ( $quantity_step_value !== false && $min_value % $quantity_step_value !== 0 ) {
				$min_value = ceil( $min_value / $quantity_step_value ) * $quantity_step_value;
			}

			// Set Min
			$args['min'] = $min_value;
		}

		// Change min to 0 if we have default quantity of 0
		if ( $default_quantity_value === 0 ) {
			$args['min'] = 0;
		}

		// Don't allow min to be higher than max
		if ( isset( $args['min'] ) && isset( $args['max'] ) && $args['min'] > $args['max'] ) {
			$args['min'] = $args['max'];
		}

		// If we don't have a min and we have a step set min to step
		if ( ! isset( $args['min'] ) && isset( $args['step'] ) && ( ! isset( $args['input_value'] ) || $args['input_value'] !== 0 ) ) {
			$args['min'] = $args['step'];
		}

		if ( self::change_default_quantity_input_value( $product ) ) {
			// Set input value to closest multiple if is not a multiple of step. (ignore default quantity 0)
			if ( isset( $args['input_value'] ) && isset( $args['step'] ) && $args['input_value'] !== 0 ) {
				$args['input_value'] = $args['input_value'] % $args['step'] === 0 && $args['input_value'] >= $args['step'] ? $args['input_value'] : ceil( $args['input_value'] / $args['step'] ) * $args['step'];
			}

			// If we still don't have an input value set it to the step if we have one
			if ( isset( $args['step'] ) && ! isset( $args['input_value'] ) ) {
				$args['input_value'] = $args['step'];
			}

			// If we still don't have an input value set it to the min if we have one
			if ( isset( $args['min'] ) && ! isset( $args['input_value'] ) ) {
				$args['input_value'] = $args['min'];
			}

			// If we still don't have an input value set it to 1
			if ( ! isset( $args['input_value'] ) ) {
				$args['input_value'] = 1;
			}

			// Don't allow max to be less than input value
			if ( isset( $args['max'] ) && isset( $args['input_value'] ) && $args['max'] < $args['input_value'] ) {
				$args['input_value'] = $args['max'];
			}

			// Don't allow min to be more than input value
			if ( isset( $args['min'] ) && isset( $args['input_value'] ) && $args['min'] > $args['input_value'] ) {
				$args['input_value'] = $args['min'];
			}
		}

		return $args;
	}

	/**
	 * Determines the quantity input attributes based on the configured rules (without quantity step).
	 *
	 * @param \WC_Product $product
	 * @return array
	 */
	private static function determine_restrictions_without_step( \WC_Product $product ) {
		$args = [];

		// Default Quantity
		$default_quantity_rule  = new Rules\Default_Quantity( $product );
		$default_quantity_value = $default_quantity_rule->get_value();

		if ( self::change_default_quantity_input_value( $product ) && $default_quantity_value !== false ) {
			$args['input_value'] = $default_quantity_value;

			if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $default_quantity_value ) {
				$args['input_value'] = $product->get_max_purchase_quantity();
			}
		}

		$min_max_quantity_rule = new Rules\Min_Max_Quantity( $product );

		// Max Quantity
		if ( $min_max_quantity_rule->get_max() ) {
			$max_value = $min_max_quantity_rule->get_max();

			if ( $product->get_max_purchase_quantity() !== -1 && $product->get_max_purchase_quantity() < $max_value ) {
				$max_value = $product->get_max_purchase_quantity();
			}

			// Set Max
			$args['max'] = $max_value;
		}

		// Min Quantity
		if ( in_array( $min_max_quantity_rule->get_level(), [ 'product-simple', 'product-variation' ], true ) && $min_max_quantity_rule->get_min() ) {
			$min_value = $min_max_quantity_rule->get_min();

			// Set Min
			$args['min'] = $min_value;
		}

		// Change min to 0 if we have default quantity of 0
		if ( $default_quantity_value === 0 ) {
			$args['min'] = 0;
		}

		// Don't allow min to be higher than max
		if ( isset( $args['min'] ) && isset( $args['max'] ) && $args['min'] > $args['max'] ) {
			$args['min'] = $args['max'];
		}

		if ( self::change_default_quantity_input_value( $product ) ) {
			// If we still don't have an input value set it to the min if we have one
			if ( isset( $args['min'] ) && ! isset( $args['input_value'] ) ) {
				$args['input_value'] = $args['min'];
			}

			// If we still don't have an input value set it to 1
			if ( ! isset( $args['input_value'] ) ) {
				$args['input_value'] = 1;
			}

			// Don't allow max to be less than input value
			if ( isset( $args['max'] ) && isset( $args['input_value'] ) && $args['max'] < $args['input_value'] ) {
				$args['input_value'] = $args['max'];
			}

			// Don't allow min to be more than input value
			if ( isset( $args['min'] ) && isset( $args['input_value'] ) && $args['min'] > $args['input_value'] ) {
				$args['input_value'] = $args['min'];
			}
		}

		return $args;
	}

	/**
	 * Determine if we should include quantity step into the calculation.
	 *
	 * @param \WC_Product $product
	 * @return bool
	 */
	private static function include_quantity_step_calculation( $product ) {
		if ( ! Field_Util::shared_quantity_step_calulation() ) {
			return true;
		}

		$shared_rule = new Rules\Quantity_Step_Shared( $product );

		if ( in_array( $shared_rule->get_level(), [ 'product-simple', 'product-variation' ], true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if we should change the default quantity input values.
	 *
	 * @param \WC_Product $product
	 * @return bool
	 */
	private static function change_default_quantity_input_value( $product ) {
		$change = ! is_cart();

		return apply_filters( 'wc_quantity_manager_change_default_quantity_input_value', $change, $product );
	}
}

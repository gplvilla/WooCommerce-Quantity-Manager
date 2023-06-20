<?php
namespace Barn2\Plugin\WC_Quantity_Manager\Integration;

use Barn2\WQM_Lib\Registerable,
	Barn2\WQM_Lib\Conditional;

/**
 * WooCommerce Quick View Pro integration.
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Flux_Checkout implements Registerable, Conditional {

	/**
	 * {@inheritdoc}
	 */
	public function is_required() {
		return defined( 'FLUX_PLUGIN_VERSION' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_filter( 'wc_quantity_manager_change_default_quantity_input_value', [ $this, 'disable_quantity_default_change' ], 10, 2 );
	}

	/**
	 * Disable quantity default change on checkout page.
	 *
	 * @param  bool   $change Whether to change the default quantities or not.
	 * @param  object $product Product to change the quantities.
	 * @return bool
	 */
	public function disable_quantity_default_change( $change, $product ) {
		if ( is_checkout() ) {
			$change = false;
		}
		return $change;
	}

}

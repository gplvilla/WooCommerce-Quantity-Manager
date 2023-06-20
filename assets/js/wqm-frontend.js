jQuery( function( $ ) {
	/**
	 * Frontend JS
	 */
	const wqmFrontendJS = function() {
		// Variation Change
        $( 'form.variations_form, form.wpt_variations_form' ).on( 'found_variation', this.handleVariationQtyInput );
    };

    /**
	 * Handle variation qty input
	 */
	wqmFrontendJS.prototype.handleVariationQtyInput = function( event, variation ) {
        const $qtyInput = $( event.target ).find( '.single_variation_wrap .quantity input[name="quantity"]' );

        if ( variation.input_value !== undefined ) {
            $qtyInput.val( variation.input_value );
        } else {
			$qtyInput.val( 1 );
		}

		if ( variation.step !== undefined ) {
            $qtyInput.attr( 'step', parseInt( variation.step ) );
        } else {
			$qtyInput.attr( 'step', 1 );
		}

		if ( variation.min_qty !== undefined && variation.min_qty !== '' ) {
            $qtyInput.attr( 'min', parseInt( variation.min_qty ) );
        } else {
			$qtyInput.attr( 'min', null );
		}

		if ( variation.max_qty !== undefined && variation.max_qty !== '' ) {
            $qtyInput.attr( 'max', parseInt( variation.max_qty ) );
        } else {
			$qtyInput.attr( 'max', null );
		}
    };

	/**
	 * Init wqmFrontendJS.
	 */
	new wqmFrontendJS();
} );
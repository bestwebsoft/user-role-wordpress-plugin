(function($) {
	$(document).ready( function() {
		var all_checkbox     = $( 'input[name="srrl_all_capabilities"]' ),
			group_checkboxes = $( '.hndle .srrl_group_cap' ),
			single_checkbox  = $( '.srrl_check_cap' );

		/* Show/Hide lists of capabilities */
		$( '#normal-sortables .hndle, #normal-sortables .handlediv' ).click( function() {
			$( this ).closest( '.postbox' ).toggleClass( 'closed' )
		} );
		$( '.srrl_group_label' ).click( function( e ) {
			e.stopPropagation();
		} );

		/* Hide lists of capabilities if all checkboxes are not active */
		single_checkbox.each( function() {
			var class_list     = $( this ).attr( 'class' ).split( /\s+/ ),
				group          = $( '.' + class_list[1] );

			if ( ! group.is( ':checked' ) &&  'srrl_menus' !== class_list[1] ) {
				$( this ).closest( '.postbox' ).addClass( 'closed' );
			}
		} );

		/*
		 * Disables the group checkbox if all single checkboxes are disabled
		 * Also activates the group checkbox if all single checkboxes are checked
		 */
		group_checkboxes.each( function() {
			var group_length = $( '.' + $( this ).val() ).length
			if ( $( '.' + $( this ).val() + ':disabled' ).length === group_length ) {
				$( this ).prop( 'disabled', true );
			}
			if ( $( '.' + $( this ).val() + ':checked' ).length === group_length ) {
				$( this ).prop( 'checked', true );
			}
		} );

		/* Activates the "All" checkbox if all groups checkbox are checked */
		if ( group_checkboxes.filter( ":checked" ).length === group_checkboxes.length ) {
			all_checkbox.prop( 'checked', true );
		}

		/* Check/Uncheck all group checkboxes */
		all_checkbox.click( function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.postbox:not(#postbox-menu)' ).removeClass( 'closed' );
				$( '.srrl_group_cap:not(#srrl_menu_checkbox), .srrl_check_cap:not(.srrl_menus)' ).prop( 'checked', true );
			} else {
				$( '.postbox:not(#postbox-menu)' ).addClass( 'closed' );
				$( '.srrl_group_cap:not(#srrl_menu_checkbox), .srrl_check_cap:not(.srrl_menus)' ).prop( 'checked', false );
			}
		} );

		/* Check/Uncheck some group checkboxes */
		group_checkboxes.click( function() {
			var children = $( '.' + $( this ).val() + ':not(:disabled)' );
			if ( $( this ).is( ':checked' ) ) {
				children.prop( 'checked', true );
				$( this ).closest( '.postbox' ).removeClass( 'closed' );
			} else {
				children.prop( 'checked', false );
				$( this ).closest( '.postbox' ).addClass( 'closed' );
			}
			if ( all_checkbox.is( ':checked' ) ) {
				all_checkbox.prop( 'checked', false );
			}
		} );

		/* Uncheck "All" or "Group" if one of the checkboxes isn't active */
		single_checkbox.click( function() {
			var class_list     = $( this ).prop( 'class' ).split( /\s+/ ),
				group_checkbox = $( 'input[value="' + class_list[1] + '"]' );
			if ( group_checkbox.is( ':checked' ) ) {
				group_checkbox.prop( 'checked', false );
			}
			if ( all_checkbox.is( ':checked' ) ) {
				all_checkbox.prop( 'checked', false );
			}
		} );

		$( 'a[data-confirm]' ).click( function() {
			if ( 'recover' === $( this ).attr( 'data-confirm' ) ) {
				return confirm( srrl_translation.confirm_recover );
			}
		} );

	} );
} )( jQuery );

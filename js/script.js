(function($) {
	$(document).ready( function() {
		/*
		 * some necessary variables
		 */
		var all_checkbox     = $( 'input[name="srrl_all_capabilities"]' ),
			group_checkboxes = $( '.srrl_group_cap' );
			single_checkbox  = $( '.srrl_check_cap' );

		/*
		 * show/hide lists of capabilities
		 */
		$('.hndle, .handlediv').click( function( event ) {
			if( event.target.className == 'hndle' || event.target.className == 'handlediv' ) {
				var this_parent = $( this ).parent( '.postbox' );
				$('.postbox').not( this_parent ).not( '#postbox-list-of-blogs' ).addClass( 'closed' );
				$( this_parent ).toggleClass( 'closed' );
			}
		});

		/*
		 * check/uncheck "group"- and "all"-checkboxes and
		 * close meta_boxes in which all "capabilities"-checkboxes are unchecked
		 * after page loading
		 */
		single_checkbox.each( function() {
			var class_list     = $( this ).attr( 'class' ).split( /\s+/ ),
				group_checkbox = $( 'input[value="' + class_list[1] + '"]' ),
				group          = $( '.' + class_list[1] );
			if ( $( this ).is( ':checked' ) ) {
				group_checkbox.attr( 'checked', group.not(':checked').length > 0 ? false : true );
				all_checkbox.attr( 'checked', group_checkboxes.not(':checked').length > 0 ? false : true );
			} else {
				all_checkbox.attr( 'checked', false );
				group_checkbox.attr( 'checked', false );
			}
			if ( group.not(':checked').length == group.length )
				$( this ).closest( '.postbox' ).addClass( 'closed' );
		});
		group_checkboxes.each( function() {
			$( this ).attr( 'disabled', $( '.' + $( this ).val() + ':disabled' ).length == $( '.' + $( this ).val() ).length ? true : false );
		});

		/*
		 * check/uncheck "group"- and "capabilities"-checkboxes
		 * if we click on "all"-checkbox
		 */
		all_checkbox.click( function() {
			$( '.srrl_group_cap, .srrl_check_cap' ).not( ':disabled' ).attr( 'checked', $( this ).is( ':checked' ) ? true : false );
			$( '.srrl_group_cap' ).each( function() {
				$( this ).attr( 'checked', $( '.' + $( this ).val() ).not(':checked').length > 0 ? false : true );
			});
		});

		/*
		 * check/uncheck  "group"-, "all"- and current "capabilities"-checkboxes
		 * if we click on "group"-checkbox
		 */
		group_checkboxes.click( function() {
			var children = $( '.' + $( this ).val() );
			children.not( ':disabled' ).attr( 'checked', $( this ).is( ':checked' ) ? true : false );
			$( this ).attr( 'checked', children.not( ':checked' ).length > 0 ? false : true );
			all_checkbox.attr( 'checked', group_checkboxes.not( ':checked' ).length > 0 ? false : true );
		});

		/*
		 * check/uncheck  "group"- and "all"-checkboxes
		 * if we click on "capability"-checkbox
		 */
		single_checkbox.not( ':disabled' ).click( function() {
			var class_list     = $( this ).attr( 'class' ).split( /\s+/ ),
				group_checkbox = $( 'input[value="' + class_list[1] + '"]' );
			if ( $( this ).is( ':checked' ) ) {
				group_checkbox.attr( 'checked', $( '.' + class_list[1] ).not(':checked').length > 0 ? false : true );
				all_checkbox.attr( 'checked', group_checkboxes.not(':checked').length > 0 ? false : true );
			} else {
				all_checkbox.attr( 'checked', false );
				group_checkbox.attr( 'checked', false );
			}
		});

		/*
		 * check/uncheck "blog"-checkboxes
		 * if we click on "all-blogs"-checkbox
		 */
		$( '#all_blogs_checkbox' ).click( function() {
			$( '.srrl_blog' ).not( ':disabled' ).attr( 'checked', $( this ).is( ':checked' ) ? true : false );
		});

		/*
		 * check/uncheck "all"-checkbox
		 * if we click on "blog"-checkbox
		 */
		$( '.srrl_blog' ).click( function() {
			$( '#all_blogs_checkbox' ).attr( 'checked', $( '.srrl_blog' ).not(':checked').length > 0 ? false : true );
		});
	});
})( jQuery );

(function($) {
	$(document).ready( function() {
		/* For accordeon prepare ITS NECESSARY TO RUN IT IN A FIRST WAY! */
		$( '.srrl_accordeon_row' ).each( function() {
			var each_id = $( this ).attr( 'class' ).split( ' ' )[1];
			var to_del_not_first = $( this ).parent().find( '.' + each_id );
			$( to_del_not_first ).not( ':first' ).not( '#' + each_id).not( ':checkbox' ).each( function() {
				$( this ).remove();
			});
		});

		srrl_left_column_check();
		srrl_header_row_check();
		srrl_block_check();
		srrl_category_check();
		srrl_section_column_check();

		if ( $.isFunction( $.fn.masonry ) ) {
			$( '#srrl_action' ).masonry({
				itemSelector : '.srrl-box',
				columnWidth : 240
			});
		}

		/* For matrix column & rows highlighting */
		var allCells = $( 'td#srrl_matrix_cell, td.srrl_role_column, th.srrl_matrix_head_cell' );
		allCells.on( 'mouseover', function() {
				var el = $( this ),
					pos = el.index();
				srrl_class_hover = $( this ).attr( 'class' );
				el.parent().addClass( 'srrl_hover' );
				if ( srrl_class_hover != 'srrl_role_column' ) {
					$( 'thead' ).find( 'input#' + srrl_class_hover).closest( 'th' ).addClass( 'srrl_hover' );
					allCells.filter( ':nth-child( ' + ( pos + 1 ) + ' )' ).addClass( 'srrl_hover' );
				}
				if( $( 'tr.srrl_matrix_head_row' ).hasClass( 'srrl_hover' ) ) {
					$( 'tr.srrl_matrix_head_row' ).removeClass( 'srrl_hover' );}
		})
		.on( 'mouseout', function() {
			allCells.parent().removeClass( 'srrl_hover' );
			$( 'thead' ).find( 'input#' + srrl_class_hover ).closest( 'th' ).removeClass( 'srrl_hover' );
			allCells.removeClass( 'srrl_hover' );
		});

		/* Recover button text parsing and form submit */
		$( 'button#srrl_recover' ).on( 'click', function() {
			if ( confirm( $( '#srrl_string_confirm_recover' ).text() ) ) {
				$( 'span.srrl_loader').css( 'display', 'inline-block' );
				/* It's because the recover button doesn't work as a submit button because of confirmation popup */
				$( 'input#srrl_recover_if_confirm' ).attr( 'name', 'srrl_recover' );
				$( 'input#srrl_recover_if_confirm' ).attr( 'value', 'srrl_recover' );
				$( '#srrl_form' ).submit();
			}
			return false;
		});

		$( '#srrl_roles' ).on( 'change', function() {
			$( '#srrl_form' ).submit();
			$( 'span.srrl_loader').css( 'display', 'inline-block' );
		});

		/* Change status messages unhide */
		$( '.srrl-check-cap, .srrl_checkall2, .srrl_checkall' ).on( 'change', function() {
			$( '#srrl_action_change_log' ).removeClass( 'hidden' );
		});

		$( '.srrl_table' ).find( ':checkbox' ).on( 'change', function() {
			$( '#srrl_action_change_log' ).removeClass( 'hidden' );
		});

		/* For ie styles */
		var b = document.documentElement;
		b.setAttribute( 'data-useragent', navigator.userAgent );
		a = navigator.userAgent;
		my_index_of_true = a.indexOf( 'MSIE 10.0' );
		if ( my_index_of_true !== -1 ) {
			$( '.srrl_rotate' ).css( 'display', 'none' );
		}

		/* All checkboxes functions */
		/* Interface flat view action boxes select all click function */
		$( '.srrl_checkall' ).on( 'click', function() {
			$( this ).closest( 'div.srrl-box' ).find( ':checkbox' ).prop( 'checked', this.checked );
		});

		/* Interface flat view blogs div select all click function */
		$( '.srrl_checkall2' ).on( 'click', function () {
			$( this ).closest( 'div.srrl_blogs_box' ).find( ':checkbox' ).prop( 'checked', this.checked );
		});

		/* For capabilitie name on checkbox click function Interface v2 */
		$( '.srrl_checkall_v2' ).on( 'click', function () {
			var srrl_class = $( this ).prop( 'class' ).split( ' ' )[2];
			var count_section_checkboxes = $( 'tbody' ).find( 'input.' + srrl_class ).not( '.srrl_category_check' ).length;
			var count_section_checked_checkboxes = $( 'tbody' ).find( 'input.' + srrl_class + ':checked' ).not( '.srrl_category_check' ).length;
			$( this ).closest( 'tr' ).find( ':checkbox' ).prop( 'checked', this.checked );
			srrl_header_row_check();
			srrl_category_check();
			srrl_section_column_check();
		});

		/* For capabilitie each block site checkbox select all function */
		$( '#srrl_select_all' ).click( function( event ) {
			event.preventDefault();
			$( '#srrl_form input:checkbox:not(:disabled)' ).attr( 'checked', 'checked' );
		});

		/* Onbutton ckick select none each interfaces */
		$( '#srrl_select_none' ).click( function( event ) {
			event.preventDefault();
			$( '#srrl_form input:checkbox:not(:disabled)' ).removeAttr( 'checked' );
		});

		/* For site name onclick check column */
		var checkbox = $( 'label.srrl_site_select' ).find( ':checkbox' );
		checkbox.on( 'click', function () {
			var srrl_class = $( this ).attr( 'id' );
			if ( $( this ).prop( 'checked' ) == false ) {
				var cap_checkboxes = $( this ).closest( 'table' ).find( '.srrl_checkall_v2' );
				$( cap_checkboxes ).each( function() {
					$( this ).prop( 'checked', '' );
				});
			}
			/* After prop( 'checked', '' ) click doesnt appear, so we need to check all chkboxess again in every function */
			$( this ).closest( 'table' ).find( 'td.'+srrl_class ).find( ':checkbox' ).prop( 'checked', this.checked );
			srrl_left_column_check();
			srrl_category_check();
			srrl_section_column_check();
		});

		/* For site name onclick check category block */
		var checkbox = $( '.srrl_category_check' );
		checkbox.on( 'click', function (event) {
			event.stopPropagation();
			var srrl_class = $( this ).attr( 'class' ).split( ' ' )[1];
			if ( $( this ).prop( 'checked' ) == false ) {
				var cap_checkboxes = $( this ).closest( 'table' ).find( '.srrl_checkall_v2' );
				$( cap_checkboxes ).each( function() {
					$( this ).prop( 'checked', '' );
				});
			}
			$( this ).closest( 'table' ).find( '.' + srrl_class ).prop( 'checked', this.checked );
			srrl_left_column_check();
			srrl_header_row_check();
			srrl_section_column_check();
		});

		/* For capabilitie category check site chkbxss */
		var checkbox = $( '.srrl_check_col_section' );
		checkbox.on( 'click', function (event) {
			event.stopPropagation();
			var srrl_class_site = $( this ).attr( 'class' ).split( ' ' )[1];
			var srrl_class_category = $( this ).closest( 'tr' ).attr( 'class' ).split( ' ' )[1];
			if ( $( this ).prop( 'checked' ) == false ) {
				var cap_checkboxes = $( this ).closest( 'table' ).find( '.' + srrl_class_category).find( '#' + srrl_class_site );
				$( cap_checkboxes ).each( function() {
					$( this ).prop( 'checked', '' );
				});
			}
			$( this ).closest( 'table' ).find( '.' + srrl_class_category).find( '#' + srrl_class_site ).prop( 'checked', this.checked );
			srrl_category_check();
			srrl_header_row_check();
			srrl_left_column_check();
		});

		/* Function applyes checked state in capabilitie name if all row checkboxes are selected */
		function srrl_left_column_check() {
			$( 'tbody.srrl_matrix_tbody tr' ).not( 'srrl_accordeon_row' ).each( function() {
				var srrl_row_checkboxes = $( this ).find( ':checkbox' ).not( '.srrl_checkall_v2' ).length === $( this ).find( ':checkbox:checked' ).not( '.srrl_checkall_v2' ).length;
				if ( srrl_row_checkboxes == true ) {
					$( this ).closest( 'tr' ).find( 'td.srrl_role_column' ).find( ':checkbox' ).prop( 'checked', 'checked' );
				} else {
					$( this ).closest( 'tr' ).find( 'td.srrl_role_column' ).find( ':checkbox' ).prop( 'checked', '' );
				}
			});
		}

		/* Function applys checked state in select all checkbox in all blocks with capabilities interface v1 */
		function srrl_block_check() {
			$( 'div.srrl-box' ).each( function() {
				var srrl_box_checkboxes = $( this ).find( ':checkbox' ).not( '.srrl_checkall' ).length === $( this ).find( ':checkbox:checked' ).not( '.srrl_checkall' ).length;
				if ( srrl_box_checkboxes == true ) {
					$( this ).find( 'b' ).find( 'input.srrl_checkall' ).prop( 'checked', 'checked' );
				} else {
					$( this ).find( 'b' ).find( 'input.srrl_checkall' ).prop( 'checked', '' );
				}
			});
		}
		
		/* Checked status blog box section checker interface v1 */
		function srrl_blogs_blog_check() {
			$( 'div.srrl_blogs_box' ).each( function() {
				var srrl_box_checkboxes = $( this ).find( 'tr' ).find( ':checkbox' ).not( '.srrl_checkall2' ).length === $( this ).find( 'tr' ).not( 'first' ).find( ':checkbox:checked' ).not( '.srrl_checkall2' ).length
				if ( srrl_box_checkboxes == true ) {
					$( this ).closest( 'div' ).find( 'label.srrl_checkall2' ).find( ':checkbox' ).prop( 'checked', 'checked' );
				} else {
					$( this ).closest( 'div' ).find( 'label.srrl_checkall2' ).find( ':checkbox' ).prop( 'checked', '' );
				}
			});
		}

		/* For cap name onclick check all others checkers launcher */
		$( '.srrl-check-cap' ).on( 'change', function() {
			srrl_block_check();

			/* Condition for non-multisite */
			if ( $( 'div.srrl_blogs_box' ).length != 0 ) {
				srrl_blogs_blog_check();
			} else {
				return;
			}
		});

		/* Checks if header checkboxes have to be checked */
		function srrl_header_row_check() {
			$( 'th.srrl_matrix_head_cell' ).each( function() {
				var srrl_row_class = $( this ).find( ':checkbox' ).attr( 'id' );
				var srrl_column_checkboxes = $( this ).closest( 'table' ).find( 'tbody' ).find( 'input#'+srrl_row_class );
				var srrl_check_counter = 0
				/* have to create value to compare */
				$( srrl_column_checkboxes ).each( function() {
					if ( $( this ).prop( 'checked' ) != false ) {
						srrl_check_counter = srrl_check_counter + 1;
					}
				});
				/* Compare all checked counter with all capabilities in a row checkboxes */
				if ( srrl_check_counter == $( this ).closest( 'table' ).find( 'tbody' ).children( 'tr' ).length - 6) {
					$( this ).find( 'input#'+srrl_row_class ).prop( 'checked', 'checked' );
				} else {
					$( this ).find( 'input#'+srrl_row_class ).prop( 'checked', '' );
				}
			});
		}

		/* Checks if section column have to be checked */
		function srrl_section_column_check() {
			$( 'tr.srrl_accordeon_row' ).find( '.srrl_check_col_section' ).each( function() {
				var srrl_category_class 	= $( this ).closest( 'tr.srrl_accordeon_row' ).attr( 'class' ).split( ' ' )[1];
				var srrl_site_class			= $( this ).attr( 'class' ).split( ' ' )[1];
				var srrl_category_col_checkboxes 	= $( this ).closest( 'tbody' ).find( '.' + srrl_category_class ).find( 'input#' + srrl_site_class ).not( '.srrl_check_col_section' );
				var srrl_category_col_check_counter = 0;

					/* have to create value to compare */
				$( srrl_category_col_checkboxes ).each( function() {
					var chkd_col_section = $( this ).prop( 'checked' );
					if ( chkd_col_section != false ) {
						srrl_category_col_check_counter = srrl_category_col_check_counter + 1;
					}
				});
				if ( srrl_category_col_check_counter == srrl_category_col_checkboxes.length ) {
					$( 'tr.srrl_accordeon_row.' + srrl_category_class ).find( '.srrl_check_col_section.' + srrl_site_class ).prop( 'checked', true );
				} else {
					$( 'tr.srrl_accordeon_row.' + srrl_category_class ).find( '.srrl_check_col_section.' + srrl_site_class ).prop( 'checked', false );
				}
			});
		}

		/* Checks if category checkboxes have to be checked ondocumentready */
		function srrl_category_check() {
			$( '.srrl_category_check' ).each( function() {
				var srrl_category_class 		= $( this ).prop( 'class' ).split( ' ' )[1];
				var srrl_category_checkboxes 	= $( this ).closest( 'tbody' ).find( 'input.' + srrl_category_class ).not( '.srrl_category_check' );
				var srrl_check_counter = 0;
				$( srrl_category_checkboxes ).each( function() {
					var chkd = $( this ).prop( 'checked' );
					if ( chkd != false ) {
						srrl_check_counter = srrl_check_counter + 1;
					}
				});
				if ( srrl_check_counter == srrl_category_checkboxes.length ) {
					$( this ).prop( 'checked', true );
				} else {
					$( this ).prop( 'checked', false );
				}
			});
		};

		/* Trigger function on a main chekboxes */
		$( 'td#srrl_matrix_cell' ).find( ':checkbox' ).on( 'change', function() {
			var srrl_category_class 			= $( this ).prop( 'class' );
			var srrl_row_checkboxes 			= $( this ).closest( 'tr' ).find( ':checkbox' ).length - 1 === $( this ).closest( 'tr' ).find( ':checkbox:checked' ).length;
			var srrl_row_class 					= $( this ).attr( 'id' );
			var srrl_column_checkboxes 			= $( this ).closest( 'tbody' ).find( 'input#' + srrl_row_class );
			var srrl_category_col_checkboxes 	= $( this ).closest( 'tbody' ).find( '.' + srrl_category_class ).find( 'input#' + srrl_row_class ).not( '.srrl_check_col_section' );
			var srrl_category_checkboxes 		= $( this ).closest( 'tbody' ).find( 'input.' + srrl_category_class ).not( '.srrl_category_check' );
			var srrl_column_check_counter 		= 0;
			var srrl_category_check_counter 	= 0;
			var srrl_category_col_check_counter = 0;
			/* For column checker */
			$( srrl_column_checkboxes ).each( function() {
				var chkd_col = $( this ).prop( 'checked' );
				if ( chkd_col != false ) {
					srrl_column_check_counter = srrl_column_check_counter + 1;
				}
			});

			/* For column section checker */
			$( srrl_category_col_checkboxes ).each( function() {
				var chkd_col_section = $( this ).prop( 'checked' );
				if ( chkd_col_section != false ) {
					srrl_category_col_check_counter = srrl_category_col_check_counter + 1;
				}
			});

			/* For capabilities category checker */
			$( srrl_category_checkboxes ).each( function() {
				var chkd_cat = $( this ).prop( 'checked' );
				if ( chkd_cat != false ) {
					srrl_category_check_counter = srrl_category_check_counter + 1;
				}
			});

			/* Finds checked checkboxes for whole cap. category section */
			if ( srrl_category_check_counter == srrl_category_checkboxes.length ) {
				$( '.srrl_category_check.' + srrl_category_class ).prop( 'checked', true );
			} else {
				$( '.srrl_category_check.' + srrl_category_class ).prop( 'checked', false );
			}

			/* Finds checked checkboxes for cap. column category section */
			if ( srrl_category_col_check_counter == srrl_category_col_checkboxes.length ) {
				$( 'tr.srrl_accordeon_row.' + srrl_category_class ).find( '.srrl_check_col_section.' + srrl_row_class ).prop( 'checked', true );
			} else {
				$( 'tr.srrl_accordeon_row.' + srrl_category_class ).find( '.srrl_check_col_section.' + srrl_row_class ).prop( 'checked', false );
			}

			/* for wholw column Length - 6 is for correction of tr with section label */
			if ( srrl_column_check_counter == $( this ).closest( 'tbody' ).children( 'tr' ).length - 6 ) {
				$( this ).closest( 'table' ).find( 'thead' ).find( 'input#'+srrl_row_class ).prop( 'checked', 'checked' );
			} else {
				$( this ).closest( 'table' ).find( 'thead' ).find( 'input#'+srrl_row_class ).prop( 'checked', '' );
			}

			/* Condition for row checkbox */
			if ( srrl_row_checkboxes == true ) {
				$( this ).closest( 'tr' ).find( 'td.srrl_role_column' ).find( ':checkbox' ).prop( 'checked', 'checked' );
			} else {
				$( this ).closest( 'tr' ).find( 'td.srrl_role_column' ).find( ':checkbox' ).prop( 'checked', '' );
			}
			if ( $( this ).prop( 'checked' ) == false ) {
				$( this ).closest( 'tr' ).find( 'td.srrl_role_column' ).find( ':checkbox' ).prop( 'checked', '' );
			}
		});

		/* For tooltip */
		if ( $.isFunction( $.fn.tooltip ) ) {
			$( '.srrl_table' ).tooltip( {
				position: { my: "left+10 center+35", at: "right center" },
				track: true,
				content: function() {
					return ( $( this ).prop( 'title' ).replace( '|', '<br />' ) );
				}
			});
		}

		/* Accordion functions */
		$( '.srrl_matrix_tbody' ).find( "tr" ).not( '.srrl_accordeon_row' ).each( function() {
			$( this ).fadeOut(100);
		});

		$( '.srrl_table' ).find( "tr.srrl_accordeon td" ).click( function() {
			var arrow = $( this ).find( 'span' );
			if ( $( arrow ).hasClass( 'srrl_closed' ) ) {
				$( arrow ).removeClass( 'srrl_closed' );
			} else {
				$( arrow ).addClass( 'srrl_closed' );
			}
			var each_id = $( this ).closest( 'tr' ).attr( 'class' ).split( ' ' )[1];
			$( this ).closest( 'table' ).find( 'tr.' + each_id ).not( 'tr.srrl_accordeon_row' ).fadeToggle(500);
		}).eq(500).trigger( 'click' );
	});
})( jQuery );
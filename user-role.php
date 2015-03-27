<?php
/*
Plugin Name: User Role by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: The plugin allows to change wordpress user role capabilities.
Author: BestWebSoft
Version: 1.4.6
Author URI: http://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! function_exists( 'srrl_add_pages' ) ) {
	function srrl_add_pages() {
		global $blog_id;
		
		if ( ! is_main_site( $blog_id ) )
			return;
		
		bws_add_general_menu( plugin_basename( __FILE__ ) );
		add_submenu_page( 'bws_plugins', 'User Role', 'User Role', 'administrator', "user-role.php", 'srrl_main_page' );
	}
}

/* Plugin init function */
if ( ! function_exists( 'srrl_init' ) ) {
	function srrl_init() {
		global $srrl_plugin_info;
		/* localization */
		load_plugin_textdomain( 'user_role', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_functions.php' );
		
		if ( empty( $srrl_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$srrl_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version  */
		bws_wp_version_check( plugin_basename( __FILE__ ), $srrl_plugin_info, "3.6" );
	}
}

/* Plugin init function */
if ( ! function_exists( 'srrl_admin_init' ) ) {
	function srrl_admin_init() {
		global $bws_plugin_info, $srrl_plugin_info;
		
		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) ) {
			$bws_plugin_info 	= array( 'id' => '132', 'version' => $srrl_plugin_info["Version"] );
		}		

		if ( isset( $_REQUEST['page'] ) && 'user-role.php' == $_REQUEST['page'] ) {			
			srrl_lib();
			srrl_create_backup_capability();
		}
	}
}

/* Begin of main funcional */
/* Adds necessary variables */
if ( ! function_exists( 'srrl_lib' ) ) {
	function srrl_lib() {
		global $srrl_defaultrole, $wpdb, $srrl_roles_name, $srrl_dict_action, $srrl_roles;
		
		$srrl_defaultrole 	= get_option( 'default_role' );
		srrl_receive_roles();
		$srrl_roles_name	= array_keys( $srrl_roles );
		$srrl_dict_action 	= array(
			'Action with Posts' 	=> 'posts',
			'Action with themes'	=> 'themes',
			'Action with users'		=> 'users',
			'Action with pages'		=> 'pages',
			'Action with plugins'	=> 'plugins',
			'Other action' 			=> ' '
		);
	}
}

/* Create backup record roles */
if ( ! function_exists( 'srrl_create_backup_capability' ) ) {
	function srrl_create_backup_capability () {
		global $wpdb;
		$srrl_original_setup = get_option( $wpdb->get_blog_prefix(1) . 'user_roles' );
		if ( is_multisite() ) {
			if ( ! get_site_option( 'srrl_backup_option_capabilities' ) ) {
				add_site_option( 'srrl_backup_option_capabilities', $srrl_original_setup );
			}
		} else {
			if ( ! get_option( 'srrl_backup_option_capabilities' ) ) {
				add_option( 'srrl_backup_option_capabilities', $srrl_original_setup );
			}
		}
	}
}

/* Saves changes */
if ( ! function_exists( 'srrl_save' ) ) {
	function srrl_save() {
		global $srrl_groups, $wpdb, $srrl_roles;
		$check = array();
		$srrl_groups = srrl_array_on_groups();
		$role_name = $_POST['srrl_select_role'];
		if ( ( isset( $_GET['interface-action'] ) && $_GET['interface-action'] == 'interface2' ) || ( false != stripos( $_SERVER['HTTP_REFERER'], 'wp-admin/network/' ) && 'v2' == get_option( 'srrl_interface_version' ) ) ) {
			switch_to_blog( '1' );
			$check = array();
			foreach ( $srrl_groups as $key => $value ) {
				foreach ( $value as $key => $val ) {
					if ( isset( $_POST['srrl_options'][ '1' ][ $val ] ) ) {
						$check[ $val ] = true;
					}
				}
			}
			$new = $srrl_roles;
			$new[ $role_name ]["capabilities"] = $check;
			update_option( $wpdb->get_blog_prefix(1) . 'user_roles', $new );			
			switch_to_blog( '1' );
		} else {
			foreach ( $srrl_groups as $key => $value ) {
				foreach ( $value as $key => $val ) {
					if ( isset( $_POST[ $val ] ) ) {
						$check[ $val ] = true;
					}
				}
			}
			$new = $srrl_roles;
			$new[ $role_name ]["capabilities"] = $check;
			update_option( $wpdb->get_blog_prefix(1) . 'user_roles', $new );
		}
	}
}

/* Recovers capabilities from option formed in create_backup_capabilities function */
if ( ! function_exists( 'srrl_repair' ) ) {
	function srrl_repair() {
		global $wpdb;
		srrl_receive_roles();
		$repair_role = $_POST['srrl_roles'];
		if ( is_multisite() )
			$srrl_repair_roles = get_site_option( 'srrl_backup_option_capabilities' );
		else
			$srrl_repair_roles = get_option( 'srrl_backup_option_capabilities' );

		$user_roles = get_option( $wpdb->get_blog_prefix(1) . 'user_roles' );
		$user_roles[ $repair_role ] = array_replace( $user_roles[ $repair_role ], $srrl_repair_roles[ $repair_role ] );
		
		if ( isset( $_POST['srrl_recover'] ) ) {
			if ( 'srrl_recover_one' == $_POST['srrl_recover_radio'] ) {
				if ( ! is_multisite() ) {

					/* If it's not a multisite do recover work and return from repair function */
					update_option( $wpdb->prefix .'user_roles', $user_roles );
					return;
				} else {
					$user_roles[ $repair_role ] = array_replace( $user_roles[ $repair_role ], $srrl_repair_roles[ $repair_role ] );
					switch_to_blog( '1' );
					update_option( $wpdb->get_blog_prefix(1) . 'user_roles', $user_roles );
				}
			} elseif ( 'srrl_recover_all' == $_POST['srrl_recover_radio'] ) {
				update_option( $wpdb->get_blog_prefix(1) . 'user_roles', $srrl_repair_roles );
			}
		}
	}
}

/* Groups capabilities into the groups */
if ( ! function_exists( 'srrl_array_on_groups' ) ) {
	function srrl_array_on_groups() {
		global $srrl_var, $srrl_dict_action, $srrl_roles_for_template;
		srrl_receive_roles();
		$roles 				= $srrl_roles_for_template;
		$srrl_var			= array_keys( $roles['administrator']['capabilities'] );
		$srrl_dict_action 	= srrl_action();
		$srrl_roles_action	= array( 'other function' => array() );
		foreach ( $srrl_dict_action as $value ) {
			foreach ( $srrl_var as $key => $val ) {
				if ( stristr( $val, $value ) ) {
					$srrl_roles_action[ $value ][] = $val;
					unset( $srrl_var[ $key ] );
				}
			}
		}
		foreach ( $srrl_var as $value ) {
			array_push ( $srrl_roles_action["other function"], $value );
		}
		return $srrl_roles_action;
	}
}

/* Forms the role capabilities */
if ( ! function_exists( 'srrl_receive_roles' ) ) {
	function srrl_receive_roles() {
		global $wp_roles, $srrl_roles, $srrl_roles_for_template;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		if ( is_multisite() ) {
			$srrl_roles_backup = get_site_option( 'srrl_backup_option_capabilities' );
		} else {
			$srrl_roles_backup = get_option( 'srrl_backup_option_capabilities' );
		}		
		$srrl_roles_for_template = $srrl_roles = $wp_roles->roles;
		if ( ! empty( $srrl_roles_backup ) ) {
			/* add capabilities from backup*/
			foreach ( $srrl_roles_backup['administrator']['capabilities'] as $cap_name => $cap_value ) {
				if ( ! array_key_exists( $cap_name, $srrl_roles_for_template['administrator']['capabilities'] ) ) {
					$srrl_roles_for_template['administrator']['capabilities'][ $cap_name ] = $cap_value;
				}
			}
		}
		/* sort capabilities alphabeticly */
		ksort( $srrl_roles_for_template['administrator']['capabilities'] );
	}
}

/*  Creates the list of roles (Select Role:) */
if ( ! function_exists( 'srrl_select_roles' ) ) {
	function srrl_select_roles() {
		global $srrl_roles_name,  $srrl_defaultrole;
		$select = isset( $_POST['srrl_roles'] ) ? $_POST['srrl_roles']: $srrl_defaultrole;
		$srrl_roles_select = '<select id="srrl_roles" name="srrl_roles">';
		foreach ( $srrl_roles_name as $value ) {
			$selected = '';
			if ( strcasecmp( $value, $select ) == 0 ) {
				$selected = ' selected="selected"';
			}
			$srrl_roles_select .= '<option value="' . $value . '"' . $selected . '>' . $value . "</option>";
		}
		$srrl_roles_select .= "</select>";
		return $srrl_roles_select;
	}
}

/* Replaces default capabilitie category */
if ( ! function_exists( 'srrl_action' ) ) {
	function srrl_action() {
		$srrl_dict_action = array(
			'Action with Posts' 	=> 'posts',
			'Action with themes'	=> 'themes',
			'Action with users'		=> 'users',
			'Action with pages'		=> 'pages',
			'Action with plugins'	=> 'plugins',
			'Other action' 			=> ' ' );
		return $srrl_dict_action;
	}
}

/* Renders interface v1 */
if ( ! function_exists( 'srrl_render_interface1' ) ) {
	function srrl_render_interface1() {
		global $srrl_defaultrole, $srrl_plugin_info, $wp_version;
		$select_role		= isset( $_POST['srrl_roles'] ) ? $_POST['srrl_roles'] : $srrl_defaultrole ;

		/* Need to check if we have 2 options of interface */
		$srrl_http_referer 	= $_SERVER['HTTP_REFERER'];
		$srrl_true 			= stripos( $srrl_http_referer, 'wp-admin/network/' );
		if ( is_multisite() && $srrl_true != false ) {
			if ( get_blog_count() != 1 ) {
				update_option( 'srrl_interface_version', 'v1' );
			}
		} ?>
		<form name="srrl_form" id="srrl_form" method="post" action="<?php get_admin_url(); ?>?page=user-role.php">
			<div id="srrl_action_change_log" class="hidden updated fade below-h2">
				<p><strong><?php _e( 'Notice:', 'user_role' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'user_role' ); ?></p>
			</div>
			<?php srrl_print_messages(); ?>
			<div class="srrl_v1_content">
				<div class="srrl_select_role">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<strong><?php _e( 'Select Role:', 'user_role' ); ?></strong>
								</th>
								<td>
									<div id="srrl_string_confirm_recover" class="hidden"><?php _e( 'Are you sure you want to recover settings by default?', 'user_role' ); ?></div>
									<input id="srrl_recover_if_confirm" class="hidden" name="" type="text" value=""/>
									<?php echo srrl_select_roles(); ?>
								</td>
								<td>
									<span class="srrl_loader hide-if-no-js" style="display: none;"></span>
									<button id="confirm" class="button-secondary hide-if-js" name="select">
										<?php _e( 'Show', 'user_role' ); ?>
									</button>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<strong><?php _e( 'Recover:', 'user_role' ) ?></strong>
								</th>
								<td>
									<select name="srrl_recover_radio" class="button_recover">
										<option id="radio1" value="srrl_recover_one"><?php _e( 'Chosen role', 'user_role' ); ?></option>
										<option id="radio2" value="srrl_recover_all"><?php _e( 'All roles', 'user_role' ); ?></option>
									</select>
									<input type="hidden" name="srrl_select_role" value="<?php echo $select_role ; ?>" />
								</td>
								<td>
									<button id="srrl_recover" class="button-secondary" name="srrl_recover" type="submit" value="srrl_recover">
										<?php _e( 'Recover', 'user_role' ); ?>
									</button>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">	
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tbody>
									<tr>	
										<th scope="row"><strong><?php _e( 'Reset', 'user_role' ); ?>:</strong></th>
										<td>
											<select name="srrl_reset_radio" class="button_reset" disabled="disabled">
												<option id="radio3" value="srrlpr_reset_one"><?php _e( 'Chosen role', 'user_role' ); ?></option>
											</select>
										</td>
										<td>
											<button id="srrl_reset" class="button-secondary" name="srrlpr_reset" type="submit" value="srrlpr_reset" disabled="disabled" >
												<?php _e( 'Reset', 'user_role' ); ?>
											</button>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><strong><?php _e( 'Add Role:', 'user_role' ); ?></strong></th>
										<td>
											<input type="text" id="srrlpr_add_role" name="srrlpr_add_role" class="button_recover" disabled="disabled" />
										</td>
										<td>
											<button disabled="disabled" id="srrlpr_add" class="button-secondary" name="srrlpr_add" type="submit" value="srrlpr_add" title="<?php _e( 'Add new role', 'user_role' ); ?>">
												<?php _e( 'Add', 'user_role' ); ?>
											</button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="bws_pro_version_tooltip">
							<div class="bws_info">
								<?php _e( 'Unlock premium options by upgrading to a PRO version.', 'user_role' ); ?> 
								<a href="http://bestwebsoft.com/products/user-role/?k=0e8fa1e4abf7647412878a5570d4977a&pn=132&v=<?php echo $srrl_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="User Role Pro Plugin"><?php _e( 'Learn More', 'user_role' ); ?></a>				
							</div>
							<a class="bws_button" href="http://bestwebsoft.com/products/user-role/buy/?k=0e8fa1e4abf7647412878a5570d4977a&pn=132&v=<?php echo $srrl_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="User Role Pro Plugin">
								<?php _e( 'Go', 'user_role' ); ?> <strong>PRO</strong>
							</a>	
							<div class="clear"></div>					
						</div>
					</div>
				</div><!-- .srrl_select_role -->				
				<div class="inside">
					<?php srrl_print_blogs(); ?>
					<div class="srrl_descr_table">
						<span><?php _e( 'Display the list of permissions for:', 'user_role' ); ?></span>
						<?php echo '<b><i>"';
						echo $select_role;
						echo '"</i></b>'; ?>
						<div class="alignright hide-if-no-js" style="margin: 3px">
							<button id="srrl_select_all" class="button-secondary">
								<span><?php _e( 'Select all', 'user_role' ); ?></span>
							</button>
							<button id="srrl_select_none" class="button-secondary">
								<span><?php _e( 'Select none', 'user_role' ); ?></span>
							</button>
						</div><!-- .alignright -->
					</div>
					<div id="srrl_action">
						<?php srrl_print_capabilities(); ?>
					</div><!-- .srrl_action -->
					<div class="clear"></div>
					<div class="srrl_buttons">
						<div class="srrl_but">
							<button type="submit" id="srrl_save" class="button-primary" name="srrl_save"  value="srrl_save"><?php _e( 'Save Changes', 'user_role' ); ?></button>							
						</div><!-- .but -->
					</div><!-- .buttons -->
				</div><!--.inside -->
			</div><!-- .srrl_v1_content-->
			<?php wp_nonce_field( plugin_basename( __FILE__ ), 'srrl_nonce_name' ); ?>
		</form><!-- #srrl_form-->
		<?php bws_plugin_reviews_block( $srrl_plugin_info['Name'], 'user-role' );
	}
}

	/* Renders interface v2 */
if ( ! function_exists( 'srrl_render_interface2' ) ) {
	function srrl_render_interface2() {
		global $srrl_defaultrole, $srrl_plugin_info, $wp_version;
		$select_role = isset( $_POST['srrl_roles'] ) ? $_POST['srrl_roles'] : $srrl_defaultrole ;
			/* Need to chek if we have 2 versions of interface */
		$srrl_http_referer 	= $_SERVER['HTTP_REFERER'];
		$srrl_true = stripos( $srrl_http_referer, 'wp-admin/network/' );
		if ( is_multisite() && $srrl_true != false ) {
			if ( get_blog_count() != 1 ) {
				update_option( 'srrl_interface_version', 'v2' );
			}
		} ?>
		<form id="srrl_form" method="post" action="<?php get_admin_url(); ?>?page=user-role.php" >
			<div id="srrl_action_change_log" class="hidden updated fade below-h2">
				<p><strong><?php _e( 'Notice: ', 'user_role' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'user_role' ); ?></p>
			</div>
			<?php srrl_print_messages(); ?>
			<div class="srrl_v2_content">
				<div class="srrl_wrap"><!-- this div must be here -->
					<div class="srrl_select_role">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<strong><?php _e( 'Select Role:', 'user_role' ); ?></strong>
									</th>
									<td>
										<div id="srrl_string_confirm_recover" class="hidden"><?php _e( 'Are you sure you want to recover settings by default?', 'user_role' ); ?></div>
										<input id="srrl_recover_if_confirm" class="hidden" name="" type="text" value=""/>
										<?php echo srrl_select_roles(); ?>
									</td>
									<td>
										<span class="srrl_loader hide-if-no-js" style="display: none;"></span>
										<button id="confirm" class="button-secondary hide-if-js" name="select">
											<?php _e( 'Show', 'user_role' ); ?>
										</button>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<strong><?php _e( 'Recover:', 'user_role' ) ?></strong>
									</th>
									<td>
										<select name="srrl_recover_radio" class="button_recover">
											<option id="radio1" value="srrl_recover_one"><?php _e( 'Chosen role', 'user_role' ); ?></option>
											<option id="radio2" value="srrl_recover_all"><?php _e( 'All roles', 'user_role' ); ?></option>
										</select>
										<input type="hidden" name="srrl_select_role" value="<?php echo $select_role ; ?>" />
									</td>
									<td>
										<button id="srrl_recover" class="button-secondary" name="srrl_recover" type="submit" value="srrl_recover">
											<?php _e( 'Recover', 'user_role' ); ?>
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div> <!-- .srrl_select_role -->
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">	
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tbody>
									<tr>
										<th scope="row"><strong><?php _e( 'Reset', 'user_role' ); ?>:</strong></th>
										<td>
											<select name="srrl_reset_radio" class="button_reset" disabled="disabled">
												<option id="radio3" value="srrlpr_reset_one"><?php _e( 'Chosen role', 'user_role' ); ?></option>
											</select>
										</td>
										<td>
											<button id="srrl_reset" class="button-secondary" name="srrlpr_reset" type="submit" value="srrlpr_reset" disabled="disabled" >
												<?php _e( 'Reset', 'user_role' ); ?>
											</button>
										</td>
									</tr>
									<tr>
										<th scope="row"><strong><?php _e( 'Add Role:', 'user_role' ); ?></strong></th>
										<td>
											<input type="text" id="srrlpr_add_role" name="srrlpr_add_role" class="button_recover" disabled="disabled" />
										</td>
										<td>
											<button disabled="disabled" id="srrlpr_add" class="button-secondary" name="srrlpr_add" type="submit" value="srrlpr_add" title="<?php _e( 'Add new role', 'user_role' ); ?>">
												<?php _e( 'Add', 'user_role' ); ?>
											</button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="bws_pro_version_tooltip">
							<div class="bws_info">
								<?php _e( 'Unlock premium options by upgrading to a PRO version.', 'user_role' ); ?> 
								<a href="http://bestwebsoft.com/products/user-role/?k=0e8fa1e4abf7647412878a5570d4977a&pn=132&v=<?php echo $srrl_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="User Role Pro Plugin"><?php _e( 'Learn More', 'user_role' ); ?></a>				
							</div>
							<a class="bws_button" href="http://bestwebsoft.com/products/user-role/buy/?k=0e8fa1e4abf7647412878a5570d4977a&pn=132&v=<?php echo $srrl_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="User Role Pro Plugin">
								<?php _e( 'Go', 'user_role' ); ?> <strong>PRO</strong>
							</a>	
							<div class="clear"></div>					
						</div>
					</div>	
					<div class="inside">
						<div class="srrl_descr_table">
							<span><?php _e( 'Display the list of permissions for:', 'user_role' ); ?></span>
							<?php echo '<b><i>"' . $select_role . '"</i></b>'; ?>
							<div class="alignright hide-if-no-js" style="margin: 3px">
								<button id="srrl_select_all" class="button-secondary">
									<span><?php _e( 'Select all', 'user_role' ); ?></span>
								</button>
								<button id="srrl_select_none" class="button-secondary">
									<span><?php _e( 'Select none', 'user_role' ); ?></span>
								</button>
							</div>
						</div> <!-- .srrl_descr_table -->
						<div class="srrl_but">
							<button type="submit" id="srrl_save" class="button-primary" name="srrl_save"  value="srrl_save" >
								<?php _e( 'Save Changes', 'user_role' ); ?>
							</button>
						</div><!-- .srrl_but -->
						<div class="clear"></div>
						<div class="srrl_matrix">
							<table class="srrl_table wp-list-table widefat">
								<?php srrl_print_matrix(); ?>
							</table> <!-- .srrl_table .wp-list-table .widefat -->
						</div> <!-- .srrl_matrix -->
						<div class="clear"></div>
						<div class="srrl_buttons">
							<div class="srrl_but">
								<button type="submit" id="srrl_save" class="button-primary" name="srrl_save"  value="srrl_save" ><?php _e( 'Save Changes', 'user_role' ); ?></button>								
							</div><!-- .but -->
						</div><!-- .buttons -->
						<div class="clear"></div><br>
					</div><!--.inside -->
				</div><!-- .srrl_wrap-->
			</div><!-- .srrl_v1_content-->
			<?php wp_nonce_field( plugin_basename( __FILE__ ), 'srrl_nonce_name' ); ?>
		</form><!-- #srrl_form-->
		<div class="clear"></div>
		<?php bws_plugin_reviews_block( $srrl_plugin_info['Name'], 'user-role' );
	}
}

/* Renders mesage div if save or restore button was clicked */
if ( ! function_exists( 'srrl_print_messages' ) ) {
	function srrl_print_messages() {
		if ( isset( $_POST['srrl_save'] ) ) { ?>
			<div id="srrl_action_change_log" class="updated fade below-h2" style="display: block;">
				<p><?php _e( 'The changes has been saved', 'user_role' ); ?></p>
			</div> <?php
		} elseif ( isset( $_POST['srrl_recover'] ) ) {
			if ( $_POST['srrl_recover_radio'] == 'srrl_recover_one' ) { ?>
				<div id="srrl_action_change_log" class="updated fade below-h2" style="display: block;">
					<p><?php _e( 'The settings have been restored for current role', 'user_role' ); ?></p>
				</div> <?php
			} elseif ( $_POST['srrl_recover_radio'] == 'srrl_recover_all' ) { ?>
				<div id="srrl_action_change_log" class="updated fade below-h2" style="display: block;">
					<p><?php _e( 'The settings have been restored for all roles', 'user_role' ); ?></p>
				</div> <?php
			}
		}
	}
}

/* Prints cap/site matrix on interface v2 */
if ( ! function_exists( 'srrl_print_matrix' ) ) {
	function srrl_print_matrix() {
		global $srrl_defaultrole, $wpdb, $srrl_roles, $srrl_roles_for_template;

		/* Strings for jQuery Ui Title in checkboxes and main variables */
		$cap_title 				= __( 'Capability:', 'user_role' ) . ' ';
		$category_cap_site		= __( 'Group capabilities', 'user_role' ) . ' ';
		$site_title 			= __( 'For site:', 'user_role' ) . ' ';
		$table_prefix			= $wpdb->get_blog_prefix(1);
		$current_blog_details 	= get_blog_details();

		/* Its for forming srrl_blog[] array in save function */
		switch_to_blog( '1' );
		srrl_receive_roles();
		echo '<input class="hidden" type="text" name="srrl_blog" value="1" />';
		$role 					= $srrl_roles;
		$global_array['1'] 		= $role;
		
		krsort( $srrl_roles_for_template['administrator']['capabilities'] );
		$global_roles_array['1'] = $srrl_roles_for_template;

		/* Create array of all privilegies from all sites of the network. srrl_get_roles was not good in this case */
		$all_privilegies = array();
		foreach ( $global_roles_array as $site_id ) {
			$roles = array_keys( $site_id );
			foreach ( $roles as $id ) {
				$role_and_caps = $site_id[ $id ];
				unset( $role_and_caps['name'] );
				foreach ( $role_and_caps as $cap ) {
					$all_privilegies = array_merge( $all_privilegies, $cap );
				}
			}
		} ?>
		<thead id="srrl_matrix_head">
			<tr class="srrl_matrix_head_row">
				<th id="srrl_corner_th">
					<h4><?php _e( 'Capabitity\Site', 'user_role' ); ?></h4>
				</th><?php
				/* Theader output uses svg object, so its potentially long load place */
				/* $blogname_length_px variables needed to provde object height so that text fits the <th> section */
				$blogname_length = strlen( 'Your_blog_name1' );
				if ( $blogname_length < strlen( $current_blog_details->blogname ) ) {
					$blogname_length = strlen( $current_blog_details->blogname  );
				}
				if ( $blogname_length > 20 ) {
					$blogname_length_px = $blogname_length * 7.5;
				} elseif ( $blogname_length < 20 ) {
					$blogname_length_px = $blogname_length * 9.5;
				} ?>
				<th class="srrl_matrix_head_cell" style="height: <?php echo $blogname_length_px; ?>px;">
					<div class="srrl_matrix_head_cell_container" style="height: <?php echo $blogname_length_px; ?>px;">
						<div class="srrl_rotate" style="height: <?php echo $blogname_length_px; ?>px;">
							<object style="height: <?php echo $blogname_length_px; ?>px;" type="image/svg+xml" data="data:image/svg+xml; charset=utf-8, <svg xmlns='http://www.w3.org/2000/svg'	  xmlns:xlink='http://www.w3.org/1999/xlink'>
								<text x='-<?php echo $blogname_length_px - 20; ?>' y='17' font-family='Open Sans' font-size='14' transform='rotate(-90)' text-rendering='optimizeSpeed'><?php echo $current_blog_details->blogname; ?></text></svg>"></object>
						</div>
						<label id="srrl_ie10_label" class="srrl_site_select">
							<span style="width: <?php echo $blogname_length_px; ?>px; left: -<?php echo $blogname_length_px / 2 - 10 ; ?>px; top: <?php echo $blogname_length_px / 2 - 35 ; ?>px;"><?php echo $current_blog_details->blogname; ?></span>
							<input id="srrl-siteid-1" class="hide-if-no-js" type="checkbox"/>
						</label><!-- this label hides if ie.css enqueued -->
						<label class="srrl_site_select srrl_ie9">
							<span style="width: <?php echo $blogname_length_px; ?>px;"><?php echo $current_blog_details->blogname; ?></span>
							<input id="srrl-siteid-1" class="hide-if-no-js" type="checkbox"/>
						</label>
					</div>
				</th>	
				<td class="srrl_matrix_head_cell bws_pro_version" style="height: <?php echo $blogname_length_px; ?>px; border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
					<div class="srrl_matrix_head_cell_container" style="height: <?php echo $blogname_length_px; ?>px;">
						<div class="srrl_rotate" style="height: <?php echo $blogname_length_px; ?>px;">
							<object style="height: <?php echo $blogname_length_px; ?>px;" type="image/svg+xml" data="data:image/svg+xml; charset=utf-8, <svg xmlns='http://www.w3.org/2000/svg'	  xmlns:xlink='http://www.w3.org/1999/xlink'>
							<text x='-<?php echo $blogname_length_px - 20; ?>' y='17' font-family='Open Sans' font-size='14' transform='rotate(-90)' text-rendering='optimizeSpeed'>Your_blog_name1</text></svg>"></object>
						</div>
						<label id="srrl_ie10_label" class="srrl_site_select">
							<span style="width: <?php echo $blogname_length_px; ?>px; left: -<?php echo $blogname_length_px / 2 - 10 ; ?>px; top: <?php echo $blogname_length_px / 2 - 35 ; ?>px;">Your_blog_name1</span>
							<input disabled="disabled" class="hide-if-no-js" type="checkbox"/>
						</label><!-- this label hides if ie.css enqueued -->
						<label class="srrl_site_select srrl_ie9">
							<span style="width: <?php echo $blogname_length_px; ?>px;">Your_blog_name1</span>
							<input disabled="disabled" class="hide-if-no-js" type="checkbox"/>
						</label>
					</div>
				</td>
				<td class="srrl_matrix_head_cell bws_pro_version" style="height: <?php echo $blogname_length_px; ?>px; border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
					<div class="srrl_matrix_head_cell_container" style="height: <?php echo $blogname_length_px; ?>px;">
						<div class="srrl_rotate" style="height: <?php echo $blogname_length_px; ?>px;">
							<object style="height: <?php echo $blogname_length_px; ?>px;" type="image/svg+xml" data="data:image/svg+xml; charset=utf-8, <svg xmlns='http://www.w3.org/2000/svg'	  xmlns:xlink='http://www.w3.org/1999/xlink'>
							<text x='-<?php echo $blogname_length_px - 20; ?>' y='17' font-family='Open Sans' font-size='14' transform='rotate(-90)' text-rendering='optimizeSpeed'>Your_blog_name2</text></svg>"></object>
						</div>
						<label>
							<span style="width: <?php echo $blogname_length_px; ?>px; left: -<?php echo $blogname_length_px / 2 - 10 ; ?>px; top: <?php echo $blogname_length_px / 2 - 35 ; ?>px;">Your_blog_name2</span>
							<input disabled="disabled" class="hide-if-no-js" type="checkbox"/>
						</label><!-- this label hides if ie.css enqueued -->
						<label>
							<span style="width: <?php echo $blogname_length_px; ?>px;">Your_blog_name2</span>
							<input disabled="disabled" class="hide-if-no-js" type="checkbox"/>
						</label>
					</div>
				</td>
			</tr>
		</thead>
		<tbody class="srrl_matrix_tbody"><?php
			/* That block of code is to provide sorting privilegies in a proper way. Creates $srrl_temp_capabil_array */
			if ( isset( $_POST['srrl_roles'] ) ) {
				$srrl_current_role = $_POST['srrl_roles'];
			} else {
				$srrl_current_role = $srrl_defaultrole;
			}
			$srrl_var			= array_keys( $all_privilegies );
			$srrl_dict_action 	= srrl_action();
			$srrl_roles_action	= array( 'other function' => array() );
			foreach ( $srrl_dict_action as $value ) {
				foreach ( $srrl_var as $key => $val ) {
					if ( stristr( $val, $value ) ) {
						$srrl_roles_action[ $value ][] = $val;
						unset( $srrl_var[ $key ] );
					}
				}
			}
			foreach ( $srrl_var as $value ) {
				array_push ( $srrl_roles_action["other function"], $value );
			}

			/* Making flat array like $all_privilegies with needed sort */
			$srrl_temp_copabil_array = array();
			foreach ( $srrl_roles_action as $value  ) {
				foreach ( $value as $key ) {
					array_unshift( $srrl_temp_copabil_array, $key );
				}
			}

			/* Makes the same array structure as in $all_privilegies */
			array_flip( $srrl_temp_copabil_array );
			foreach ( $srrl_temp_copabil_array as $value => $key ) {
				$srrl_temp_copabil_array[ $key ] = true;
				unset ( $srrl_temp_copabil_array[ $value ] );
			}

			/* Here $all_privilegies becomes array of all capabilities with needed sort */
			$all_privilegies = $srrl_temp_copabil_array;
			foreach ( $srrl_roles_action as $value => $key ) {
				foreach ( $all_privilegies as $privilegies => $key ) {

					/* Its neccessary that class="srrl_accordeon_row srrl_' . $value . '" srrl_' . $value . ' - is second class: hardcoded in js.
					It outputs another <tr> with capabilitie category above row woth capabilitie. Futher deleting :not(':first') row
					Here we check if cap_name has string of a group name */
					if ( stristr( $privilegies, $value ) ) { ?>
						<tr class="srrl_accordeon_row srrl_<?php echo $value ?> srrl_accordeon hide-if-no-js">
							<td style="cursor: pointer">
								<label>
									<b><?php echo __( 'Capabilities category: ', 'user_role' ) . $value; ?></b>
								</label>
								<span class="srrl_accordeon_icon"></span>
							</td>
							<td id="srrl_matrix_cell" class="srrl-siteid-1">
								<input title="<?php echo $category_cap_site; ?>" class="srrl_check_col_section srrl-siteid-1 srrl_category_' . $value . '"  type="checkbox"/>
							</td>
							<td id="srrl_matrix_cell" class="bws_pro_version" style="border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
								<input title="<?php echo $category_cap_site; ?>" class="" disabled="disabled" type="checkbox" name="pro_setting[]" />
							</td>
							<td id="srrl_matrix_cell" class="bws_pro_version" style="border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
								<input title="<?php echo $category_cap_site; ?>" class="" disabled="disabled" type="checkbox" name="pro_setting[]" />
							</td>
						</tr>
						<tr class="srrl_<?php echo $value ?>" id="srrl_<?php echo $value ?>">
							<td class="srrl_role_column">
								<label><?php echo $privilegies; ?></label>
							</td>
							<?php /* Outputs main matrix checkboxes. Var_dump($global_array) to figure out what is this */
							$checked = ( isset( $global_array['1'][ $srrl_current_role ]['capabilities'][ $privilegies ] ) ) ? ' checked="checked"' : '';
							$name_of_blog_title = $current_blog_details->blogname; ?>
							<td id="srrl_matrix_cell" class="srrl-siteid-1">
								<input class="srrl_<?php echo $value; ?>" id="srrl-siteid-1" title="<?php echo $cap_title . $privilegies .'|'. $site_title . $name_of_blog_title; ?>" type="checkbox"<?php echo $checked; ?> name="srrl_options[1][<?php echo $privilegies; ?>]" value="ON"/>
							</td>
							<td id="srrl_matrix_cell" class="bws_pro_version" style="border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
								<input disabled="disabled" type="checkbox" name="pro_setting[]" value=""/>
							</td>
							<td id="srrl_matrix_cell" class="bws_pro_version" style="border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
								<input disabled="disabled" type="checkbox" name="pro_setting[]" value=""/>
							</td>
						</tr>
						<?php /* Its for print capabilities without twins */
						unset( $all_privilegies[ $privilegies ] );
						continue;
					}
				}
			}
			/* After unset( $all_privilegies[ $privilegies ] ) in $all_privilegies array only not categorized capabilities left, so output them in the loop */
			foreach ( $all_privilegies as $privilegies => $key ) { ?>
				<tr class="srrl_accordeon_row srrl_other_actions srrl_accordeon hide-if-no-js">
					<td style="cursor: pointer">
						<label><b><?php echo __( 'Capabilities without category', 'user_role' ); ?></b></label>
						<span class="srrl_accordeon_icon"></span>
					</td>
					<td id="srrl_matrix_cell" class="srrl-siteid-1">
						<input class="srrl_check_col_section srrl-siteid-1 srrl_category_other_actions"  type="checkbox"/>
					</td>
					<td id="srrl_matrix_cell" class="bws_pro_version" style="border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
						<input disabled="disabled" type="checkbox" name="pro_setting[]" value=""/>
					</td>
					<td id="srrl_matrix_cell" class="bws_pro_version" style="border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
						<input disabled="disabled" type="checkbox" name="pro_setting[]" value=""/>
					</td>
				</tr>
				<tr class="srrl_other_actions" id="srrl_other_actions">
					<td class="srrl_role_column">
						<label><?php echo $privilegies; ?></label>
					</td>
					<?php $checked = ( isset( $global_array['1'][ $srrl_current_role ]['capabilities'][ $privilegies ] ) ) ? ' checked="checked"' : '';
					$name_of_blog_title = $current_blog_details->blogname; ?>
					<td id="srrl_matrix_cell" class="srrl-siteid-1">
						<input class="srrl_other_actions" id="srrl-siteid-1" title="<?php echo $cap_title . $privilegies . '|' . $site_title . $name_of_blog_title; ?>" type="checkbox"<?php echo $checked ?> name="srrl_options[1][<?php echo $privilegies;  ?>]" value="ON"/>
					</td>
					<td id="srrl_matrix_cell" class="bws_pro_version" style="border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
						<input disabled="disabled" type="checkbox" name="pro_setting[]" value=""/>
					</td>
					<td id="srrl_matrix_cell" class="bws_pro_version" style="border-color: #E1E1E1;" title="<?php _e( 'This setting is available in Pro version', 'user_role' ); ?>">
						<input disabled="disabled" type="checkbox" name="pro_setting[]" value=""/>
					</td>
				</tr>
		<?php }
		echo '</tbody>';
	}
}

	/* Prints capabilities on interface v1 */
if ( ! function_exists( 'srrl_print_capabilities' ) ) {
	function srrl_print_capabilities() {
		global $srrl_current_role, $wp_roles, $srrl_defaultrole, $srrl_groups;
		$srrl_groups 					= srrl_array_on_groups();
		$wp_roles 						= new WP_Roles();
		$roles 							= $wp_roles->roles;
		$srrl_capabil_name 				= array_keys( $roles['administrator']['capabilities'] );
		$srrl_current_role				= isset( $_POST['srrl_roles'] ) ? $_POST['srrl_roles'] : $srrl_defaultrole ;
		$srrl_current_role_capabilities =  array_keys( $roles[ $srrl_current_role ]['capabilities'] );

		foreach ( $srrl_groups as $key => $val ) { ?>
			<div class="srrl-box">
				<div style="padding-bottom: 10px" class="hide-if-js">
					<b style="cursor:default;">
						<label class="srrl-label-cap srrl_checkall" >
							<?php echo __( 'Action with', 'user_role' ) . ' ' .  $key; ?></span>
						</label>
					</b>
				</div>
				<div style="padding-bottom: 10px" class="hide-if-no-js">
					<b style="cursor:default;">
						<label class="srrl-label-cap srrl_checkall" >
							<input class="srrl_checkall" type="checkbox" />
							<?php echo __( 'Action with', 'user_role' ) . ' ' . $key; ?></span>
						</label>
					</b>
				</div>
				<div>
					<?php foreach ( $val as $key => $value ) {
						$checked = ( in_array( $value, $srrl_current_role_capabilities ) ) ? ' checked="checked"' : '';
						echo '<input class="srrl-check-cap" type="checkbox" name="' . $value . '" id="' . $value . '" value="ON"' . $checked . ' />';
						echo '<label class="srrl-label-cap" for="' . $value . '" title="' . $value . '" >  ' . $value . '</label> ' . '<br/>';
					} ?>
				</div>
			</div>
	 	<?php }
	}
}

/* Prints blogs list */
if ( ! function_exists( 'srrl_print_blogs' ) ) {
	function srrl_print_blogs() {
		global $wpdb, $wp_version, $srrl_plugin_info;
		$table_prefix 		= $wpdb->get_blog_prefix(1);
		$srrl_http_referer 	= $_SERVER['HTTP_REFERER'];
		$srrl_true 			= stripos( $srrl_http_referer, 'wp-admin/network/' );
		if ( is_multisite() && $srrl_true != false  ) { ?>	
			<div class="srrl_blogs_box">
				<span><?php echo __( 'Changes will be applied to the site:', 'user_role' ) . ' <b>' . get_bloginfo( 'name' ) . '</b>' ; ?></span>
			</div>
			<div class="bws_pro_version_bloc">
				<div class="bws_pro_version_table_bloc">	
					<div class="bws_table_bg"></div>											
					<b style="cursor:default; margin: 5px;"><span><?php _e( 'Changes will be applied to the selected blogs:', 'user_role' ); ?></span></b>
					<table style="width: 100%; padding-top: 5px;">
						<tbody>
							<tr style="line-height: 30px">
								<td>
									<label class="srrl-label-cap srrl_checkall2 hide-if-no-js" >
										<input type="checkbox" /><?php _e( 'Select all', 'user_role' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<td class="srrl_blog_table">
									<label class="srrl-label-cap" title="<?php echo home_url(); ?>" >
										<input dissbled="dissbled" class="srrl-check-cap" type="checkbox" name="srrl_blog[]" value="1"/><?php echo get_bloginfo( 'name' ); ?>
									</label>
								</td>
								<td class="srrl_blog_table">
									<label class="srrl-label-cap" title="Your_blog_name" >
										<input dissbled="dissbled" class="srrl-check-cap" type="checkbox" name="srrl_blog[]" value="1"/>Your_blog_name1
									</label>
								</td>
								<td class="srrl_blog_table">
									<label class="srrl-label-cap" title="Your_blog_name" >
										<input dissbled="dissbled" class="srrl-check-cap" type="checkbox" name="srrl_blog[]" value="1"/>Your_blog_name2
									</label>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="bws_pro_version_tooltip">
					<div class="bws_info">
						<?php _e( 'Unlock premium options by upgrading to a PRO version.', 'user_role' ); ?> 
						<a href="http://bestwebsoft.com/products/user-role/?k=0e8fa1e4abf7647412878a5570d4977a&pn=132&v=<?php echo $srrl_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="User Role Pro Plugin"><?php _e( 'Learn More', 'user_role' ); ?></a>				
					</div>
					<a class="bws_button" href="http://bestwebsoft.com/products/user-role/buy/?k=0e8fa1e4abf7647412878a5570d4977a&pn=132&v=<?php echo $srrl_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="User Role Pro Plugin">
						<?php _e( 'Go', 'user_role' ); ?> <strong>PRO</strong>
					</a>	
					<div class="clear"></div>					
				</div>
			</div>
		<?php } else {
			return;
		}
	}
}

/* Add main page */
if ( ! function_exists( 'srrl_main_page' ) ) {
	function srrl_main_page() {
		global $srrl_plugin_info, $wp_version;
		$error = $message = '';
		$plugin_basename = plugin_basename(__FILE__);
		if ( ( isset( $_POST['srrl_recover'] ) || isset( $_POST['srrl_save'] ) ) && check_admin_referer( $plugin_basename, 'srrl_nonce_name' ) ) {
			if ( isset( $_POST['srrl_recover'] ) ) {
				srrl_repair();
			} else if ( isset( $_POST['srrl_save'] ) ) {
				srrl_save();
			}
		}
		$srrl_http_referer 	= $_SERVER['HTTP_REFERER'];
		$srrl_true 			= stripos( $srrl_http_referer, 'wp-admin/network/' );
		if ( ! current_user_can( 'administrator' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'user_role' ) );
		}

		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
		} ?>
		<div class="wrap">
			<div class="icon32  icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'User Role', 'user_role' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=user-role.php"><?php _e( 'Settings', 'user_role' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/user-role/faq/" target="_blank"><?php _e( 'FAQ', 'user_role' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=user-role.php&amp;action=go_pro"><?php _e( 'Go PRO', 'user_role' ); ?></a>
			</h2>
			<div class="error" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>>
				<p><?php echo $error; ?></p>
			</div>
			<?php if ( ! isset( $_GET['action'] ) ) {
				if ( is_multisite() && $srrl_true != false ) {				
					if ( get_blog_count() != 1 ) {
						if ( ! get_option( 'srrl_interface_version' ) ) {
							add_option( 'srrl_interface_version', 'v1' );
						}
						if ( ! isset( $_GET['interface-action'] ) && 'v2' != get_option( 'srrl_interface_version' ) || ( isset( $_GET['interface-action'] ) && 'interface1' == $_GET['interface-action'] ) ) { ?>
							<p><a class="button-primary"  href="admin.php?page=user-role.php&amp;interface-action=interface2"><?php _e( 'Table View', 'user_role' ); ?></a></p>
						<?php } elseif ( isset( $_GET['interface-action'] ) && 'interface2' == $_GET['interface-action'] || 'v2' == get_option( 'srrl_interface_version' ) ) { ?>
							<p><a class="button-primary" href="admin.php?page=user-role.php&amp;interface-action=interface1"><?php _e( 'Flat View', 'user_role' ); ?></a></p>
						<?php }
					} ?>
					<div class="clear"></div>
					<?php if ( ! isset( $_GET['interface-action'] ) && 'v2' != get_option( 'srrl_interface_version' ) || ( isset( $_GET['interface-action'] ) && 'interface1' == $_GET['interface-action'] ) ) {
						srrl_render_interface1();
					} elseif ( ! isset( $_GET['interface-action'] ) || 'v2' == get_option( 'srrl_interface_version' ) ) {
						srrl_render_interface2();
					} elseif ( 'interface2' == $_GET['interface-action']  || 'v2' == get_option( 'srrl_interface_version' ) ) {
						srrl_render_interface2();
					}
				} else {
					srrl_render_interface1();
				}
			} elseif ( 'go_pro' == $_GET['action'] ) { 
				bws_go_pro_tab( $srrl_plugin_info, $plugin_basename, 'user-role.php', 'user-role-pro.php', 'user-role-pro/user-role-pro.php', 'user-role', '0e8fa1e4abf7647412878a5570d4977a', '132', isset( $go_pro_result['pro_plugin_is_activated'] ) ); 			
			} ?>
		</div><!--end wrap-->
	<?php }
}

/* End of main functional */
/* Style & js on */
if ( ! function_exists( 'srrl_admin_head' ) ) {
	function srrl_admin_head() {		
		if ( isset( $_REQUEST['page'] ) && 'user-role.php' == $_REQUEST['page'] ) {
			global $wp_version;
			if ( 3.8 > $wp_version ) {
				wp_enqueue_style( 'srrl_stylesheet', plugins_url( 'css/style_wp_before_3.8.css', __FILE__ ) );
			} else {
				wp_enqueue_style( 'srrl_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			}
			wp_enqueue_style( 'srrl_ie', plugins_url( 'css/ie.css', __FILE__ ), array( 'srrl_stylesheet' ) );
			wp_style_add_data( 'srrl_ie', 'conditional', 'lte IE 9' );
			wp_enqueue_style( 'srrl_ui', 'http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css' );
			wp_enqueue_script( 'jquery-ui-dialog', array( 'jquery' ) );
			wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );
			wp_enqueue_script( 'jquery-ui-accordion', array( 'jquery' ) );
			wp_enqueue_script( 'jquery-ui-tooltip', array( 'jquery' ) );
			wp_enqueue_script( 'srrl_script', plugins_url( '/js/script.js', __FILE__ ), false );
		}
	}
}

/* Action_links */
if ( ! function_exists( 'srrl_plugin_action_links' ) ) {
	function srrl_plugin_action_links( $links, $file ) {
		/* Static so we don't call plugin_basename on every plugin row. */
		if ( is_main_site() ) {
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

			if ( $file == $this_plugin ){
				$settings_link = '<a href="admin.php?page=user-role.php">' . __( 'Settings', 'user_role' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}


if ( ! function_exists( 'srrl_register_plugin_links' ) ) {
	function srrl_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( is_main_site() )
				$links[]	=	'<a href="admin.php?page=user-role.php">' . __( 'Settings', 'user_role' ) . '</a>';
			$links[]	=	'<a href="http://wordpress.org/plugins/user-role/faq/" target="_blank">' . __( 'FAQ', 'user_role' ) . '</a>';
			$links[]	=	'<a href="http://support.bestwebsoft.com">' . __( 'Support', 'user_role' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'srrl_plugin_banner' ) ) {
	function srrl_plugin_banner() {
		global $hook_suffix;	
		if ( 'plugins.php' == $hook_suffix ) {
		global $srrl_plugin_info;
			bws_plugin_banner( $srrl_plugin_info, 'srrl', 'user-role', 'a2f27e2893147873133fe67d81fa274d', '132', plugins_url( 'images/banner.png', __FILE__ ) );
		}	 
	}
}

/* Plugin delete options */
if ( ! function_exists ( 'srrl_delete_options' ) ) {
	function srrl_delete_options() {
		global $wpdb;
		/* recover all caps to the ones in the backup if no PRO version*/
		if ( is_plugin_active( 'user-role-pro/user-role-pro.php' ) ) {			
			if ( is_multisite() ) {
				$srrl_repair_roles = get_site_option( 'srrl_backup_option_capabilities' );
				if ( is_array( $srrl_repair_roles ) && ! empty( $srrl_repair_roles ) ) {
					switch_to_blog( '1' );
					update_option( $wpdb->prefix . 'user_roles', $srrl_repair_roles );
				}
			} else {
				$srrl_repair_roles = get_option( 'srrl_backup_option_capabilities' );
				if ( is_array( $srrl_repair_roles ) && ! empty( $srrl_repair_roles ) ) {
					update_option( $wpdb->prefix . 'user_roles', $srrl_repair_roles );
				}
			}
		} 
		/* delete backup options after recover */
		delete_option( 'srrl_backup_option_capabilities' );
		delete_site_option( 'srrl_backup_option_capabilities' );
		/* delete option of interface version */
		delete_option( 'srrl_interface_version' );
	}
}

/* Adds "Settings" link to the plugin action page */
add_filter( 'plugin_action_links', 'srrl_plugin_action_links', 10, 2 );

/* Additional links on the plugin page */
add_filter( 'plugin_row_meta', 'srrl_register_plugin_links', 10, 2 );
add_action( 'admin_menu', 'srrl_add_pages' );
add_action( 'network_admin_menu', 'srrl_add_pages' );
add_action( 'init', 'srrl_init' );
add_action( 'admin_init', 'srrl_admin_init' );

/* Calling a function add administrative menu. */
add_action( 'admin_enqueue_scripts', 'srrl_admin_head' );

add_action( 'admin_notices', 'srrl_plugin_banner' );

register_uninstall_hook( __FILE__, 'srrl_delete_options' );
?>
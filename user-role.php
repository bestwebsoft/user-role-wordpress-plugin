<?php
/*
Plugin Name: User Role by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/user-role/
Description: Powerful user role management plugin for WordPress website. Create, edit, copy, and delete user roles.
Author: BestWebSoft
Text Domain: user-role
Domain Path: /languages
Version: 1.5.8
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2017  BestWebSoft  ( https://support.bestwebsoft.com )

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

		$settings = add_menu_page( __( 'User Role Settings', 'user-role' ), 'User Role', 'manage_options', 'user-role.php', 'srrl_main_page', 'none' );
		add_submenu_page( 'user-role.php', __( 'User Role Settings', 'user-role' ), __( 'Settings', 'user-role' ), 'manage_options', 'user-role.php', 'srrl_main_page' );
		add_submenu_page( 'user-role.php', 'BWS Panel', 'BWS Panel', 'manage_options', 'srrl-bws-panel', 'bws_add_menu_render' );

		add_action( 'load-' . $settings, 'srrl_add_tabs' );
	}
}

if ( ! function_exists( 'srrl_plugins_loaded' ) ) {
	function srrl_plugins_loaded() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'user-role', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/* Plugin init function */
if ( ! function_exists( 'srrl_init' ) ) {
	function srrl_init() {
		global $srrl_plugin_info;

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $srrl_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$srrl_plugin_info = get_plugin_data( dirname(__FILE__) . '/user-role.php' );
		}

		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $srrl_plugin_info, '3.9' );
	}
}

/* Plugin admin init function */
if ( ! function_exists( 'srrl_admin_init' ) ) {
	function srrl_admin_init() {
		global $bws_plugin_info, $srrl_plugin_info;

		if ( empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '132', 'version' => $srrl_plugin_info["Version"] );
	}
}

/* Style & js on */
if ( ! function_exists( 'srrl_admin_head' ) ) {
	function srrl_admin_head() {
		wp_enqueue_style( 'srrl_icon', plugins_url( 'css/icon.css', __FILE__ ) );
		if ( isset( $_REQUEST['page'] ) && 'user-role.php' == $_REQUEST['page'] ) {
			wp_enqueue_style( 'srrl_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'srrl_script', plugins_url( '/js/script.js', __FILE__ ), array( 'jquery' ) );

			bws_enqueue_settings_scripts();
		}
	}
}

/*Plugin activate*/
if ( ! function_exists( 'srrl_plugin_activate' ) ) {
	function srrl_plugin_activate() {
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'srrl_delete_options' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'srrl_delete_options' );
		}
	}
}

/**
 * Create plugin options
 * @return void
 */
if ( ! function_exists( 'srrl_default_options' ) ) {
	function srrl_default_options() {
		global $srrl_options, $srrl_plugin_info;
		$srrl_default_options = array(
			'plugin_option_version' => $srrl_plugin_info["Version"],
			'first_install'         => strtotime( "now" ),
			'suggest_feature_banner'=> 1,
		);

		$srrl_options = get_option( 'srrl_options' );
		if ( ! $srrl_options ) {
			$srrl_options = $srrl_default_options;
			add_option( 'srrl_options', $srrl_options );
		}

		if ( ! isset( $srrl_options['plugin_option_version'] ) || $srrl_plugin_info["Version"] != $srrl_options['plugin_option_version'] ) {
			srrl_plugin_activate();
			$srrl_options['plugin_option_version'] = $srrl_plugin_info["Version"];
			$srrl_options['hide_premium_options']  = array();
			update_option( 'srrl_options', $srrl_options );
		}
	}
}

/**
 * Create backup-options
 * when we go to the plugin settings page
 * @return void
 */
if ( ! function_exists( 'srrl_create_backup' ) ) {
	function srrl_create_backup () {
		global $wpdb;
		$restore_option = get_option( 'srrl_backup_option_capabilities' );
		$flag = false;
		if ( ! $restore_option ) {
			add_option( 'srrl_backup_option_capabilities', get_option( $wpdb->prefix . 'user_roles' ) );
		} else {
			$roles = get_option( $wpdb->prefix . 'user_roles' );
			foreach( $roles as $key => $value ) {
				if ( ! array_key_exists( $key, $restore_option ) ) {
					$restore_option[ $key ] = $value;
					$flag = true;
				}
			}
			if ( $flag )
				update_option( 'srrl_backup_option_capabilities', $restore_option );
		}
	}
}

/**
 * Display plugin settings page
 * @return void
 */
if ( ! function_exists( 'srrl_main_page' ) ) {
	function srrl_main_page() {
		global $srrl_options, $srrl_plugin_info, $wpdb;
		$message = $error = '';
		$plugin_basename = plugin_basename( __FILE__ );
		$is_network = is_multisite() && is_network_admin() ? true : false;

		/* create plugin options */
		srrl_default_options();
		/* create 'restore'-options */
		srrl_create_backup();
		/* hide pro blocks */
		if ( isset( $_POST['bws_hide_premium_options'] ) && check_admin_referer( $plugin_basename, 'srrl_nonce_name' ) ) {
			$hide_result  = bws_hide_premium_options( $srrl_options );
			$srrl_options = $hide_result['options'];
			$message      = $hide_result['message'];
			update_option( 'srrl_options', $srrl_options );
		}

		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename, 'srrl_options' );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
			elseif ( ! empty( $go_pro_result['message'] ) )
				$message = $go_pro_result['message'];
		} /* Display form on the setting page */ ?>
		<div class="wrap">
			<h1 class="srrl_page_title srrl_row"><?php _e( 'User Role Settings', 'user-role' ); ?></h1>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=user-role.php"><?php _e( 'Settings', 'user-role' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=user-role.php&amp;action=go_pro"><?php _e( 'Go PRO', 'user-role' ); ?></a>
			</h2>
			<div class="updated below-h2" <?php if ( empty( $message ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error below-h2" <?php if ( empty( $error ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>
			<?php if ( isset( $_REQUEST['srrl_action'] ) && in_array( $_REQUEST['srrl_action'], array( 'edit', 'update' ) ) ) {
				/* display add/edit role page */
				$file = dirname( __FILE__ ) . '/includes/edit-role-page.php';
				if ( file_exists( $file ) )
					require_once( $file );
			} elseif( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
				$show = bws_hide_premium_options_check( $srrl_options ) ? true : false;
				bws_go_pro_tab_show(
					$show,
					$srrl_plugin_info,
					$plugin_basename,
					'user-role.php',
					'user-role-pro.php',
					'user-role-pro/user-role-pro.php',
					'user-role',
					'0e8fa1e4abf7647412878a5570d4977a',
					'132',
					isset( $go_pro_result['pro_plugin_is_activated'] )
				);
			} else {
				$action = isset( $_POST['action'] ) && '-1' != $_POST['action'] ? $_POST['action'] : '';
				$action = empty( $action ) && isset( $_POST['action2'] ) && '-1' != $_POST['action2'] ? $_POST['action2'] : $action;
				$action = empty( $action ) && isset( $_REQUEST['srrl_action'] ) && ! in_array( $_REQUEST['srrl_action'], array( 'add', 'edit', 'update', 'new' ) ) ? $_REQUEST['srrl_action'] : $action;
				if (
						! empty( $action ) &&
						! isset( $_REQUEST['srrl_confirm_action'] ) &&
						isset( $_REQUEST['srrl_slug'] ) &&
						! empty( $_REQUEST['srrl_slug'] )
				) {
					switch ( $action ) {
						case 'recover':
							$question      = __( 'Are you sure, you want to recover selected role(s)?', 'user-role' );
							$confirn_title = __( 'Yes, recover role(s)', 'user-role' );
							break;
						default:
							$question      = __( 'Are you sure, you want to do selected action?', 'user-role' );
							$confirn_title = __( 'Yes, do it', 'user-role' );
							break;
					}
					/* display confirm form */ ?>
					<p><?php echo $question; ?></p>
					<form method="post" action="<?php get_admin_url(); ?>admin.php?page=user-role.php" style="margin-bottom: 20px;">
						<?php if ( ! empty( $_REQUEST['srrl_slug'] ) ) {
							foreach ( (array)$_REQUEST['srrl_slug'] as $role_slug ) { ?>
								<input type="hidden" name="srrl_slug[]" value="<?php echo esc_attr( $role_slug ); ?>"/>
							<?php }
						} ?>
						<input type="submit" class="button" name="srrl_confirm_action" value="<?php echo $confirn_title; ?>"/>
						<?php $admin_link = is_network_admin() ? network_admin_url() . 'admin.php?page=user-role.php' : get_admin_url() . 'admin.php?page=user-role.php';
						if ( isset( $_REQUEST['srrl_blog_id'] ) )
							$admin_link .= '&srrl_blog_id=' . intval( $_REQUEST['srrl_blog_id'] ); ?>
						<a class="button" href="<?php echo esc_url( $admin_link ); ?>"><?php _e( 'No, go back to the roles list', 'user-role' ); ?></a>
						<?php if ( isset( $_REQUEST['srrl_blog_id'] ) ) { ?>
							<input type="hidden" name="srrl_blog_id" value="<?php echo intval( $_REQUEST['srrl_blog_id'] ); ?>"/>
						<?php } ?>
						<input type="hidden" name="srrl_action" value="<?php echo esc_attr( $action ); ?>"/>
						<?php wp_nonce_field( $plugin_basename, 'srrl_nonce_name' ); ?>
					</form>
				<?php } else {
					/* display list of roles */
					$file = dirname( __FILE__ ) . '/includes/class-user-role.php';
					if ( file_exists( $file ) )
					require_once( $file );
					if ( class_exists( 'Srrl_Roles_List' ) )
					$srrl_list = new Srrl_Roles_List( $plugin_basename );

				}
			}
			bws_plugin_reviews_block( $srrl_plugin_info['Name'], 'user-role' ); ?>
		</div><!--end wrap-->
	<?php }
}

/**
 * Add new or update an existing one role ( from "edit_role"-page )
 * @param      string    $name       role display name
 * @param      string    $slug       role name
 * @param      array     $caps       allowed capabilities
 * @return     array     $result     result of action
 */
if ( ! function_exists( 'srrl_single_handle_role' ) ) {
	function srrl_single_handle_role( $name, $slug, $caps ) {
		global $wp_roles;
		$result = array( 'error' => '', 'message' => '' );
		if ( empty( $name ) )
			$result['error'][] = __( 'Please enter role name', 'user-role' );
		if ( empty( $slug ) )
			$result['error'][] = __( 'Please enter role slug', 'user-role' );
		if( empty( $caps ) || ! is_array( $caps ) )
			$result['error'][] = __( 'Please select some capabilities for role', 'user-role' );
		if ( ! empty( $result['error'] ) ) {
			$result['error'] = implode( '.<br/>', $result['error'] );
		} else {
			$slug = strtolower( trim( stripslashes( esc_html( $slug ) ) ) );
			$name = trim( stripslashes( esc_html( $name ) ) );
			if (
				! preg_match( "/^[a-zA-Z0-9]+([-_\s]?[a-zA-Z0-9])*$/", $slug ) || /* not only latin, numbers, '-' or '_' */
				preg_match( "/^([-_\s]*?)$/", $slug ) || /* only '-' or '_' */
				is_numeric( $slug ) /* only numbers */
			 ) {
				$result['error'] = __( 'Wrong slug. See allowed symbols', 'user-role' );
			} else {
				if ( 0 === srrl_update_role( $slug, $name, $caps ) )
					$result['error'] = __( 'Some error occured', 'user-role' );
				else
					$result['message'] = __( 'Role was successfully updated', 'user-role' );
			}
		}

		if ( method_exists( $wp_roles, 'for_site' ) ){
			$wp_roles->for_site();
		} else {
			$wp_roles->reinit();
		}

		return $result;
	}
}

/**
 * Getting capabilities of the selected role
 * @return     array    $result    result message and capabilities of the selected role
 */
if ( ! function_exists( 'srrl_copy_role' ) ) {
	function srrl_copy_role() {
		$result = array( 'error' => '', 'message' => '', 'caps' => array() );
		if ( isset( $_REQUEST['srrl_select_role'] ) && '-1' != $_REQUEST['srrl_select_role'] ) {
			global $wpdb;
			$prefix     = $wpdb->get_blog_prefix();
			$blog_roles = get_option( "{$prefix}user_roles" );
			if ( empty( $blog_roles ) ) {
				$result['error'] = __( 'Can not get capabilities of the selected role', 'user-role' );
			} else {
				if ( array_key_exists( $_REQUEST['srrl_select_role'], $blog_roles ) ) {
					$result['caps']    = $blog_roles[ $_REQUEST['srrl_select_role'] ]['capabilities'];
					$result['message'] = __( 'Capabilities were successfully loaded', 'user-role' ) . '.&nbsp;<a class="bws_save_anchor" href="#bws-submit-button">' . __( 'Save Changes', 'user-role' ) . '</a>';
				} else {
					$result['error'] = __( 'Selected role is not exists. Capabilities not loaded', 'user-role' );
				}
			}
		}
		return $result;
	}
}

/**
 * Update of the role
 * @return   mixed      an integer (1 or 0) or WP_Role object
 */
if ( ! function_exists( 'srrl_update_role' ) ) {
	function srrl_update_role( $slug, $name, $caps ) {
		global $wpdb;
		$blog_roles = get_option( "{$wpdb->prefix}user_roles" );
		if ( 'administrator' == $slug ) {
			$caps['activate_plugins']   = true;
			$caps['create_users']       = true;
			$caps['delete_plugins']     = true;
			$caps['delete_themes']      = true;
			$caps['delete_users']       = true;
			$caps['edit_files']         = true;
			$caps['edit_plugins']       = true;
			$caps['edit_theme_options'] = true;
			$caps['edit_themes']        = true;
			$caps['edit_users']         = true;
			$caps['export']             = true;
			$caps['import']             = true;
			$caps['install_plugins']    = true;
			$caps['install_themes']     = true;
			$caps['list_users']         = true;
			$caps['manage_options']     = true;
			$caps['promote_users']      = true;
			$caps['remove_users']       = true;
			$caps['switch_themes']      = true;
			$caps['update_core']        = true;
			$caps['update_plugins']     = true;
			$caps['update_themes']      = true;
			$caps['edit_dashboard']     = true;
			$caps['add_users']          = true;
		}
		if ( array_key_exists( $slug, $blog_roles ) ) {
			$blog_roles[ $slug ] = array( 'name' => $name, 'capabilities' => $caps );
			update_option( "{$wpdb->prefix}user_roles", $blog_roles );
			return 1;
		} else {
			return 0;
		}
	}
}

/**
 * Recovers or resets of capabilities
 * @param      array/string  $slug_array   roles slugs
 * @return     void
 */
if ( ! function_exists( 'srrl_recover_role' ) ) {
	function srrl_recover_role( $slug_array ) {
		global $wpdb;
		$result      = array( 'error' => '', 'notice' => '', 'message' => '' );
		$action_info = get_option( 'srrl_backup_option_capabilities' );
		$slugs       = array();
		if ( empty( $action_info ) ) {
			$result['error'] = __( "Can not recover selected roles", 'user-role' );
		} else {
			$blog_roles = get_option( "{$wpdb->prefix}user_roles" );
			foreach ( (array)$slug_array as $slug ) {
				if ( array_key_exists( $slug, $action_info ) )
					$blog_roles[ $slug ] = $action_info[ $slug ];
				else
					$slugs[] = $slug;
			}
			if ( empty( $slugs ) )
				$result['message'] = __( "Selected roles were recovered successfully", 'user-role' );
			else
				$result['error'] = __( 'Can not recover next roles', 'user-role' ) . ':<br />' . implode( ',<br/>', $slugs );
			update_option( "{$wpdb->prefix}user_roles", $blog_roles );
		}
		return $result;
	}
}

/**
 * Forming html-structure of metabox on Add/ Edit role page
 * @param    object      $post       WP_Post object
 * @param    array       $metabox    parameters, which was passed thru add_meta_box() action
 * @return   void
 */
if ( ! function_exists( 'srrl_metabox_content' ) ) {
	function srrl_metabox_content( $post, $metabox ) {
		$slug = isset( $_REQUEST['srrl_slug'] ) ? stripslashes( trim( esc_html( $_REQUEST['srrl_slug'] ) ) ) : '';
		$slug = empty( $slug ) && isset( $_REQUEST['srrl_role_slug'] ) ? stripslashes( trim( esc_html( $_REQUEST['srrl_role_slug'] ) ) ) : $slug;
		$admin_capabilities =
				'administrator' == $slug
			?
				array(
					'activate_plugins', 'create_users', 'delete_plugins', 'delete_themes',
					'delete_users', 'edit_files', 'edit_plugins', 'edit_theme_options',
					'edit_themes', 'edit_users', 'export', 'import', 'install_plugins',
					'install_themes', 'list_users', 'manage_options', 'promote_users',
					'remove_users', 'switch_themes', 'update_core', 'update_plugins',
					'update_themes', 'edit_dashboard', 'add_users'
				)
			:
				array();
		foreach ( $metabox['args'][0] as $key => $value ) {
			$checked  = ( array_key_exists( $value, $metabox['args'][1] ) ) ? ' checked="checked"' : '';
			$readonly = in_array( $value, $admin_capabilities ) ? ' disabled="disabled"' : '';
			echo '<label class="srrl_label_cap" for="srrl_' . $value . '"><input class="srrl_check_cap ' . $metabox['args'][2] . '" type="checkbox" name="srrl_role_caps[]" id="srrl_' . $value . '" value="' . $value . '"' . $checked . $readonly .' />'. $value . '</label>';
		}
	}
}

/**
 * Display list of Blogs
 * @param    object      $post       WP_Post object
 * @param    array       $metabox    parameters, which was passed thru add_meta_box() action
 * @return   void
 */
if ( ! function_exists( 'srrl_list_of_blogs' ) ) {
	function srrl_list_of_blogs( $post, $metabox ) {
		echo $metabox['args'][0];
	}
}

/* Action_links */
if ( ! function_exists( 'srrl_plugin_action_links' ) ) {
	function srrl_plugin_action_links( $links, $file ) {
		static $this_plugin;
		if ( ! $this_plugin )
			$this_plugin = plugin_basename(__FILE__);
		if ( $file == $this_plugin ) {
			$settings_link = '<a href="admin.php?page=user-role.php">' . __( 'Settings', 'user-role' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}

if ( ! function_exists ( 'srrl_plugin_banner' ) ) {
	function srrl_plugin_banner() {
		global $hook_suffix, $srrl_plugin_info, $srrl_options;

		if ( 'plugins.php' == $hook_suffix ) {
			if ( empty( $srrl_options ) )
				$srrl_options = get_option( 'srrl_options' );

			if ( isset( $srrl_options['first_install'] ) && strtotime( '-1 week' ) > $srrl_options['first_install'] )
				bws_plugin_banner( $srrl_plugin_info, 'srrl', 'user-role', 'a2f27e2893147873133fe67d81fa274d', '132', '//ps.w.org/user-role/assets/icon-128x128.png' );
			bws_plugin_banner_to_settings( $srrl_plugin_info, 'srrl_options', 'user-role', 'admin.php?page=user-role.php' );
		}

		if ( isset( $_GET['page'] ) && 'user-role.php' == $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $srrl_plugin_info, 'srrl_options', 'user-role' );
		}
	}
}

if ( ! function_exists( 'srrl_register_plugin_links' ) ) {
	function srrl_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="admin.php?page=user-role.php">' . __( 'Settings', 'user-role' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538799" target="_blank">' . __( 'FAQ', 'user-role' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'user-role' ) . '</a>';
		}
		return $links;
	}
}

/**
 * Add help tab on settings page
 * @return void
 */
if ( ! function_exists( 'srrl_add_tabs' ) ) {
	function srrl_add_tabs() {
		$args = array(
			'id'      => 'srrl',
			'section' => '200538799'
		);
		bws_help_tab( get_current_screen(), $args );
	}
}

/**
 * Show ads for PRO
 * @return void
 */
if ( ! function_exists( 'srrl_pro_block' ) ) {
	function srrl_pro_block( $class = "", $func, $show_link = true, $show_cross = true ) {
		global $srrl_plugin_info, $wp_version, $srrl_options;
		if ( ! bws_hide_premium_options_check( $srrl_options ) ) { ?>
			<div class="bws_pro_version_bloc <?php echo $class;?>" title="<?php _e( 'This option is available in Pro version of plugin', 'user-role' ); ?>">
				<div class="bws_pro_version_table_bloc">
					<?php if ( $show_cross ) { ?>
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'user-role' ); ?>"></button>
					<?php } ?>
					<div class="bws_table_bg"></div>
					<?php call_user_func( $func ); ?>
				</div>
				<?php if ( $show_link ) { ?>
					<div class="bws_pro_version_tooltip">
						<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/user-role/buy/?k=0e8fa1e4abf7647412878a5570d4977a&pn=132&v=<?php echo $srrl_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="User Role Pro Plugin"><?php _e( 'Learn More', 'user-role' ); ?></a>
					</div>
				<?php } ?>
			</div>
		<?php }
	}
}

if ( ! function_exists( 'srrl_blog_switcher' ) ) {
	function srrl_blog_switcher() { ?>
		<div class="srrl_blog_switcher bws_pro_version">
			<select name="srrl_blog_id" disabled="disabled">
				<option value="off"><?php echo get_bloginfo() . '&nbsp;(' . get_bloginfo( 'url' ) . ')'; ?></option>
			</select>
			<input type="submit" class="button-primary" name="srrl_switch_to_blog" value="<?php _e( 'Switch to Blog', 'user-role' ); ?>" disabled="disabled" />
		</div>
	<?php }
}

if ( ! function_exists( 'srrl_blog_list' ) ) {
	function srrl_blog_list() { ?>
		<div class="metabox-holder srrl_metabox">
			<table class="form-table"><tr><th><?php _e( 'Allow for blogs', 'user-role' ); ?></th></tr></table>
			<?php do_meta_boxes( 'user-role-blog.php', 'normal', null ); ?>
		</div>
	<?php }
}

if ( ! function_exists( 'srrl_select' ) ) {
	function srrl_select() { ?>
		<div class="bws_pro_version">
			<select name="srrl_select" disabled="disabled">
				<option value="-1"><?php _e( 'Select blog', 'user-role' ); ?> </option>
			</select>
		</div>
	<?php }
}

if ( ! function_exists( 'srrl_add_new' ) ) {
	function srrl_add_new() { ?>
		<div class="bws_pro_version">
			<button class="button-primary" disabled="disabled"><?php _e( 'Add New Role', 'user-role' ); ?></button>
		</div>
	<?php }
}

/* Plugin delete options */
if ( ! function_exists ( 'srrl_delete_options' ) ) {
	function srrl_delete_options() {
		/* recover all options on every blog to the ones in the backup */
		global $wpdb;

		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$all_plugins = get_plugins();

		if ( ! array_key_exists( 'user-role-pro/user-role-pro.php', $all_plugins ) ) {
			if ( is_multisite() ) {
				$all_blogs = $wpdb->get_col( "SELECT `blog_id` FROM `" . $wpdb->prefix . "blogs`" );
				foreach ( $all_blogs as $blog_id ) {
					$srrl_repair_roles = get_blog_option( $blog_id, 'srrl_backup_option_capabilities' );
					if ( is_array( $srrl_repair_roles ) && ! empty( $srrl_repair_roles ) )
						update_blog_option( $blog_id, $wpdb->get_blog_prefix( $blog_id ) . 'user_roles', $srrl_repair_roles );
					delete_blog_option( $blog_id, 'srrl_backup_option_capabilities' );
					delete_blog_option( $blog_id, 'srrl_options' );
				}
				delete_site_option( 'srrl_options' );
			} else {
				$srrl_repair_roles = get_option( 'srrl_backup_option_capabilities' );
				if ( is_array( $srrl_repair_roles ) && ! empty( $srrl_repair_roles ) )
					update_option( $wpdb->prefix . 'user_roles', $srrl_repair_roles );
				delete_option( 'srrl_backup_option_capabilities' );
				delete_option( 'srrl_options' );
			}
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

register_activation_hook( __FILE__, 'srrl_plugin_activate' );

add_action( 'admin_menu', 'srrl_add_pages' );
add_action( 'network_admin_menu', 'srrl_add_pages' );
add_action( 'plugins_loaded', 'srrl_plugins_loaded' );
add_action( 'init', 'srrl_init' );
add_action( 'admin_init', 'srrl_admin_init' );
/* Calling a function add administrative menu. */
add_action( 'admin_enqueue_scripts', 'srrl_admin_head' );
/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'srrl_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'srrl_register_plugin_links', 10, 2 );
/* add notice about plugin license timeout */
add_action( 'admin_notices', 'srrl_plugin_banner' );
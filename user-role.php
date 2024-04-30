<?php
/**
Plugin Name: User Role by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/user-role/
Description: Powerful user role management plugin for WordPress website. Create, edit, copy, and delete user roles.
Author: BestWebSoft
Text Domain: user-role
Domain Path: /languages
Version: 1.6.9
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
 */

/*
  © Copyright 2023  BestWebSoft  ( https://support.bestwebsoft.com )

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
	/**
	 * Add admin menu
	 */
	function srrl_add_pages() {
		global $submenu, $srrl_plugin_info, $wp_version;

		$settings = add_menu_page( __( 'Roles', 'user-role' ), 'User Role', 'manage_options', 'user-role.php', 'srrl_main_page', 'none' );
		add_submenu_page( 'user-role.php', __( 'Roles', 'user-role' ), __( 'Roles', 'user-role' ), 'manage_options', 'user-role.php', 'srrl_main_page' );
		if ( isset( $_REQUEST['srrl_action'] ) && in_array( $_REQUEST['srrl_action'], array( 'edit', 'update' ) ) ) {
			add_submenu_page( 'user-role.php', __( 'Edit Role', 'user-role' ), __( 'Add New', 'user-role' ), 'manage_options', 'srrl_add_new_roles', 'srrl_add_new_roles' );
		} else {
			add_submenu_page( 'user-role.php', __( 'Add New', 'user-role' ), __( 'Add New', 'user-role' ), 'manage_options', 'srrl_add_new_roles', 'srrl_add_new_roles' );
		}
		add_submenu_page( 'user-role.php', __( 'User Role Settings', 'user-role' ), __( 'Settings', 'user-role' ), 'manage_options', 'srrl_settings', 'srrl_settings_page' );
		add_submenu_page( 'user-role.php', 'BWS Panel', 'BWS Panel', 'manage_options', 'srrl-bws-panel', 'bws_add_menu_render' );

		if ( isset( $submenu['user-role.php'] ) ) {
			$submenu['user-role.php'][] = array(
				'<span style="color:#d86463"> ' . __( 'Upgrade to Pro', 'user-role' ) . '</span>',
				'manage_options',
				'https://bestwebsoft.com/products/wordpress/plugins/user-role/?k=0e8fa1e4abf7647412878a5570d4977a&pn=132&v=' . $srrl_plugin_info['Version'] . '&wp_v=' . $wp_version,
			);
		}

		add_action( 'load-' . $settings, 'srrl_add_tabs' );
	}
}

if ( ! function_exists( 'srrl_plugins_loaded' ) ) {
	/**
	 * Load textdomain
	 */
	function srrl_plugins_loaded() {
		load_plugin_textdomain( 'user-role', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists( 'srrl_init' ) ) {
	/**
	 * Plugin init function
	 */
	function srrl_init() {
		global $srrl_plugin_info;

		require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $srrl_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$srrl_plugin_info = get_plugin_data( dirname( __FILE__ ) . '/user-role.php' );
		}

		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $srrl_plugin_info, '4.5' );
	}
}

if ( ! function_exists( 'srrl_admin_init' ) ) {
	/**
	 * Plugin admin init function
	 */
	function srrl_admin_init() {
		global $bws_plugin_info, $srrl_plugin_info, $pagenow, $srrl_options;

		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array(
				'id'      => '132',
				'version' => $srrl_plugin_info['Version'],
			);
		}

		/* Call register settings function */
		$plugin_pages = array(
			'user-role.php',
			'srrl_add_new_roles',
			'srrl_settings',
		);
		if ( isset( $_GET['page'] ) && in_array( sanitize_text_field( $_GET['page'] ), $plugin_pages ) ) {
			srrl_register_settings();
		}

		if ( 'plugins.php' === $pagenow ) {
			/* Install the option defaults */
			if ( function_exists( 'bws_plugin_banner_go_pro' ) ) {
				srrl_register_settings();
				bws_plugin_banner_go_pro( $srrl_options, $srrl_plugin_info, 'srrl', 'user-role', 'a2f27e2893147873133fe67d81fa274d', '132', 'user-role' );

			}
		}
	}
}

if ( ! function_exists( 'srrl_admin_head' ) ) {
	/**
	 * Style & js on
	 */
	function srrl_admin_head() {
		global $srrl_plugin_info;
		wp_enqueue_style( 'srrl_icon', plugins_url( 'css/icon.css', __FILE__ ), array(), $srrl_plugin_info['Version'] );

		$plugin_pages = array(
			'user-role.php',
			'srrl_add_new_roles',
			'srrl_settings',
		);
		if ( isset( $_GET['page'] ) && in_array( sanitize_text_field( $_GET['page'] ), $plugin_pages ) ) {
			wp_enqueue_style( 'srrl_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), $srrl_plugin_info['Version'] );
			wp_enqueue_script( 'srrl_script', plugins_url( '/js/script.js', __FILE__ ), array( 'jquery' ), $srrl_plugin_info['Version'], true );
			$srrl_translation_array = array(
				'confirm_recover' => __( 'Are you sure, you want to recover selected role(s)?', 'user-role' ),
			);
			wp_localize_script( 'srrl_script', 'srrl_translation', $srrl_translation_array );

			bws_enqueue_settings_scripts();
		}
	}
}

if ( ! function_exists( 'srrl_plugin_activate' ) ) {
	/**
	 * Plugin activate
	 */
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

if ( ! function_exists( 'srrl_get_options_default' ) ) {
	/**
	 * Fetch plugin default options
	 *
	 * @return array
	 */
	function srrl_get_options_default() {
		global $srrl_plugin_info;

		return array(
			'plugin_option_version'  => $srrl_plugin_info['Version'],
			'first_install'          => strtotime( 'now' ),
			'suggest_feature_banner' => 1,
		);
	}
}

if ( ! function_exists( 'srrl_register_settings' ) ) {
	/**
	 * Create plugin options
	 *
	 * @return void
	 */
	function srrl_register_settings() {
		global $srrl_options, $srrl_plugin_info, $wpdb;

		if ( is_multisite() && is_network_admin() ) {
			if ( ! get_site_option( 'srrl_options' ) ) {
				add_site_option( 'srrl_options', srrl_get_options_default() );
			}

			$srrl_options = get_site_option( 'srrl_options' );
		} else {
			if ( ! get_option( 'srrl_options' ) ) {
				add_option( 'srrl_options', srrl_get_options_default() );
			}

			$srrl_options = get_option( 'srrl_options' );
		}

		if ( ! isset( $srrl_options['plugin_option_version'] ) || $srrl_plugin_info['Version'] !== $srrl_options['plugin_option_version'] ) {
			$srrl_options['plugin_option_version'] = $srrl_plugin_info['Version'];
			$srrl_options['hide_premium_options']  = array();
			if ( is_multisite() ) {
				$all_blogs = $wpdb->get_col( 'SELECT `blog_id` FROM `' . $wpdb->base_prefix . 'blogs`' );
				foreach ( $all_blogs as $blog_id ) {
					update_blog_option( $blog_id, 'srrl_options', $srrl_options );
				}
				update_site_option( 'srrl_options', $srrl_options );
				switch_to_blog( 1 );
				register_uninstall_hook( __FILE__, 'srrl_delete_options' );
				restore_current_blog();
			} else {
				update_option( 'srrl_options', $srrl_options );
				register_uninstall_hook( __FILE__, 'srrl_delete_options' );
			}
		}
	}
}

if ( ! function_exists( 'srrl_settings_page' ) ) {
	/**
	 * Settings page
	 */
	function srrl_settings_page() {
		if ( ! class_exists( 'Bws_Settings_Tabs' ) ) {
			require_once dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php';
		}
		require_once dirname( __FILE__ ) . '/includes/class-srrl-settings.php';
		$page = new Srrl_Settings_Tabs( plugin_basename( __FILE__ ) );
		if ( method_exists( $page, 'add_request_feature' ) ) {
			$page->add_request_feature();
		} ?>	
		<div class="wrap">
			<h1 class="srrl-title"><?php esc_html_e( 'User Role Settings', 'user-role' ); ?></h1>
			<?php $page->display_content(); ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'srrl_create_backup' ) ) {
	/**
	 * Create backup-options
	 * when we go to the plugin settings page
	 *
	 * @return void
	 */
	function srrl_create_backup() {
		global $wpdb;
		$restore_option = get_option( 'srrl_backup_option_capabilities' );
		$flag           = false;
		if ( ! $restore_option ) {
			add_option( 'srrl_backup_option_capabilities', get_option( $wpdb->prefix . 'user_roles' ) );
		} else {
			$roles = get_option( $wpdb->prefix . 'user_roles' );
			foreach ( $roles as $key => $value ) {
				if ( ! array_key_exists( $key, $restore_option ) ) {
					$restore_option[ $key ] = $value;
					$flag                   = true;
				}
			}
			if ( $flag ) {
				update_option( 'srrl_backup_option_capabilities', $restore_option );
			}
		}
	}
}

if ( ! function_exists( 'srrl_main_page' ) ) {
	/**
	 * Display plugin roles page
	 *
	 * @return void
	 */
	function srrl_main_page() {
		?>
		<div class="wrap">
			<h1>
				<?php
				esc_html_e( 'Roles', 'user-role' );
				srrl_pro_block( 'srrl_add_new', 'srrl_add_new', false );
				?>
			</h1>
			<?php
			require_once dirname( __FILE__ ) . '/includes/class-user-role.php';

			/* create 'restore'-options */
			srrl_create_backup();

			$roles_list = new Srrl_Roles_List( plugin_basename( __FILE__ ) );
			$roles_list->display_list();
			?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'srrl_add_new_roles' ) ) {
	/**
	 * Display plugin add new role page
	 *
	 * @return void
	 */
	function srrl_add_new_roles() {
		global $title;
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php require_once dirname( __FILE__ ) . '/includes/edit-role-page.php'; ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'srrl_single_handle_role' ) ) {
	/**
	 * Add new or update an existing one role ( from "edit_role"-page )
	 *
	 * @param string $name Role display name.
	 * @param string $slug Role name.
	 * @param array  $caps Allowed capabilities.
	 * @return array $result Result of action.
	 */
	function srrl_single_handle_role( $name, $slug, $caps ) {
		global $wp_roles;
		$result = array(
			'error'   => '',
			'message' => '',
		);
		if ( empty( $name ) ) {
			$result['error'][] = __( 'Please enter role name', 'user-role' );
		}
		if ( empty( $slug ) ) {
			$result['error'][] = __( 'Please enter role slug', 'user-role' );
		}
		if ( empty( $caps ) || ! is_array( $caps ) ) {
			$result['error'][] = __( 'Please select some capabilities for role', 'user-role' );
		}
		if ( ! empty( $result['error'] ) ) {
			$result['error'] = implode( '.<br/>', $result['error'] );
		} else {
			$slug = strtolower( trim( stripslashes( esc_html( $slug ) ) ) );
			$name = trim( stripslashes( esc_html( $name ) ) );
			if (
				! preg_match( '/^[a-zA-Z0-9]+([-_\s]?[a-zA-Z0-9])*$/', $slug ) || /* not only latin, numbers, '-' or '_' */
				preg_match( '/^([-_\s]*?)$/', $slug ) || /* only '-' or '_' */
				is_numeric( $slug ) /* only numbers */
			 ) {
				$result['error'] = __( 'Wrong slug. See allowed symbols', 'user-role' );
			} else {
				if ( 0 === srrl_update_role( $slug, $name, $caps ) ) {
					$result['error'] = __( 'Some error occured', 'user-role' );
				} else {
					$result['message'] = __( 'Role was successfully updated', 'user-role' );
				}
			}
		}

		if ( method_exists( $wp_roles, 'for_site' ) ) {
			$wp_roles->for_site();
		} else {
			$wp_roles->reinit();
		}

		return $result;
	}
}

if ( ! function_exists( 'srrl_copy_role' ) ) {
	/**
	 * Getting capabilities of the selected role
	 *
	 * @return array $result Result message and capabilities of the selected role.
	 */
	function srrl_copy_role() {
		$result = array(
			'error'   => '',
			'message' => '',
			'caps'    => array(),
		);
		if ( isset( $_REQUEST['srrl_select_role'] ) && '-1' !== sanitize_text_field( wp_unslash( $_REQUEST['srrl_select_role'] ) ) ) {
			global $wpdb;
			$prefix     = $wpdb->get_blog_prefix();
			$blog_roles = get_option( "{$prefix}user_roles" );
			if ( empty( $blog_roles ) ) {
				$result['error'] = __( 'Can not get capabilities of the selected role', 'user-role' );
			} else {
				if ( array_key_exists( sanitize_text_field( wp_unslash( $_REQUEST['srrl_select_role'] ) ), $blog_roles ) ) {
					$result['caps']    = $blog_roles[ sanitize_text_field( wp_unslash( $_REQUEST['srrl_select_role'] ) ) ]['capabilities'];
					$result['message'] = __( 'Capabilities were successfully loaded', 'user-role' ) . '.&nbsp;<a class="bws_save_anchor" href="#bws-submit-button">' . __( 'Save Changes', 'user-role' ) . '</a>';
				} else {
					$result['error'] = __( 'Selected role is not exists. Capabilities not loaded', 'user-role' );
				}
			}
		}
		return $result;
	}
}

if ( ! function_exists( 'srrl_update_role' ) ) {
	/**
	 * Update of the role
	 *
	 * @param string $slug Slug.
	 * @param string $name Name.
	 * @param array  $caps Capapibilities array.
	 * @return mixed an integer (1 or 0) or WP_Role object
	 */
	function srrl_update_role( $slug, $name, $caps ) {
		global $wpdb;
		$blog_roles = get_option( "{$wpdb->prefix}user_roles" );
		if ( 'administrator' === $slug ) {
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
			$blog_roles[ $slug ] = array(
				'name'         => $name,
				'capabilities' => $caps,
			);
			update_option( "{$wpdb->prefix}user_roles", $blog_roles );
			return 1;
		} else {
			return 0;
		}
	}
}

if ( ! function_exists( 'srrl_recover_role' ) ) {
	/**
	 * Recovers or resets of capabilities
	 *
	 * @param array/string $slug_array Roles slugs.
	 */
	function srrl_recover_role( $slug_array ) {
		global $wpdb;
		$result      = array(
			'error'   => '',
			'notice'  => '',
			'message' => '',
		);
		$action_info = get_option( 'srrl_backup_option_capabilities' );
		$slugs       = array();
		if ( empty( $action_info ) ) {
			$result['error'] = __( 'Can not recover selected roles', 'user-role' );
		} else {
			$blog_roles = get_option( "{$wpdb->prefix}user_roles" );
			foreach ( (array) $slug_array as $slug ) {
				if ( array_key_exists( $slug, $action_info ) ) {
					$blog_roles[ $slug ] = $action_info[ $slug ];
				} else {
					$slugs[] = $slug;
				}
			}
			if ( empty( $slugs ) ) {
				$result['message'] = __( 'Selected roles were recovered successfully', 'user-role' );
			} else {
				$result['error'] = __( 'Can not recover next roles', 'user-role' ) . ':<br />' . implode( ',<br/>', $slugs );
			}
			update_option( "{$wpdb->prefix}user_roles", $blog_roles );
		}
		return $result;
	}
}

if ( ! function_exists( 'srrl_metabox_content' ) ) {
	/**
	 * Forming html-structure of metabox on Add/ Edit role page
	 *
	 * @param object $post     WP_Post object.
	 * @param array  $metabox  Parameters, which was passed thru add_meta_box() action.
	 */
	function srrl_metabox_content( $post, $metabox ) {
		$slug               = isset( $_REQUEST['srrl_slug'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['srrl_slug'] ) ) ) : '';
		$slug               = empty( $slug ) && isset( $_REQUEST['srrl_role_slug'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['srrl_role_slug'] ) ) ) : $slug;
		$admin_capabilities =
				'administrator' === $slug
			?
				array(
					'activate_plugins',
					'create_users',
					'delete_plugins',
					'delete_themes',
					'delete_users',
					'edit_files',
					'edit_plugins',
					'edit_theme_options',
					'edit_themes',
					'edit_users',
					'export',
					'import',
					'install_plugins',
					'install_themes',
					'list_users',
					'manage_options',
					'promote_users',
					'remove_users',
					'switch_themes',
					'update_core',
					'update_plugins',
					'update_themes',
					'edit_dashboard',
					'add_users',
				)
			:
				array();
		foreach ( $metabox['args'][0] as $key => $value ) {
			$checked  = ( array_key_exists( $value, $metabox['args'][1] ) ) ? ' checked="checked"' : '';
			$readonly = in_array( $value, $admin_capabilities ) ? ' disabled="disabled"' : '';
			echo '<label class="srrl_label_cap" for="srrl_' . esc_attr( $value ) . '"><input class="srrl_check_cap ' . esc_attr( $metabox['args'][2] ) . '" type="checkbox" name="srrl_role_caps[]" id="srrl_' . esc_attr( $value ) . '" value="' . esc_attr( $value ) . '"' . esc_html( $checked ) . esc_html( $readonly ) . ' />' . esc_attr( $value ) . '</label>';
		}
	}
}

if ( ! function_exists( 'srrl_list_of_blogs' ) ) {
	/**
	 * Display list of Blogs
	 *
	 * @param object $post    WP_Post object.
	 * @param array  $metabox Parameters, which was passed thru add_meta_box() action.
	 */
	function srrl_list_of_blogs( $post, $metabox ) {
		echo esc_attr( $metabox['args'][0] );
	}
}

if ( ! function_exists( 'srrl_plugin_action_links' ) ) {
	/**
	 * Add Settings link
	 *
	 * @param array  $links Links array.
	 * @param string $file  Plugin file.
	 * @return array  $links.
	 */
	function srrl_plugin_action_links( $links, $file ) {
		static $this_plugin;
		if ( ! $this_plugin ) {
			$this_plugin = plugin_basename( __FILE__ );
		}
		if ( $file === $this_plugin ) {
			$settings_link = '<a href="admin.php?page=srrl_settings">' . __( 'Settings', 'user-role' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}

if ( ! function_exists( 'srrl_plugin_banner' ) ) {
	/**
	 * Display banner
	 */
	function srrl_plugin_banner() {
		global $hook_suffix, $srrl_plugin_info;

		if ( 'plugins.php' === $hook_suffix ) {
			bws_plugin_banner_to_settings( $srrl_plugin_info, 'srrl_options', 'user-role', 'admin.php?page=srrl_settings' );
		}

		if ( isset( $_GET['page'] ) && 'user-role.php' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			bws_plugin_suggest_feature_banner( $srrl_plugin_info, 'srrl_options', 'user-role' );
		}
	}
}

if ( ! function_exists( 'srrl_register_plugin_links' ) ) {
	/**
	 * Add Settings, FAQ and Support links
	 *
	 * @param array  $links Links array.
	 * @param string $file  Plugin file.
	 * @return array $links.
	 */
	function srrl_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file === $base ) {
			$links[] = '<a href="admin.php?page=srrl_settings">' . __( 'Settings', 'user-role' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538799" target="_blank">' . __( 'FAQ', 'user-role' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'user-role' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists( 'srrl_add_tabs' ) ) {
	/**
	 * Add help tab on settings page
	 */
	function srrl_add_tabs() {
		$args = array(
			'id'      => 'srrl',
			'section' => '200538799',
		);
		bws_help_tab( get_current_screen(), $args );
	}
}

if ( ! function_exists( 'srrl_pro_block' ) ) {
	/**
	 * Show ads for PRO
	 *
	 * @param string $func       Function name.
	 * @param string $class      Classes for block.
	 * @param bool   $show_cross Flag for show cross.
	 * @param bool   $show_link  Flag for show link.
	 */
	function srrl_pro_block( $func, $class = '', $show_cross = true, $show_link = true ) {
		global $srrl_plugin_info, $wp_version, $srrl_options;
		if ( ! bws_hide_premium_options_check( $srrl_options ) ) {
			?>
			<div class="bws_pro_version_bloc <?php echo esc_attr( $class ); ?>">
				<div class="bws_pro_version_table_bloc">
					<?php if ( $show_cross ) { ?>
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php esc_html_e( 'Close', 'user-role' ); ?>"></button>
					<?php } ?>
					<div class="bws_table_bg"></div>
					<?php call_user_func( $func ); ?>
				</div>
				<?php if ( $show_link ) { ?>
					<div class="bws_pro_version_tooltip">
						<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/user-role/?k=0e8fa1e4abf7647412878a5570d4977a&pn=132&v=<?php echo esc_attr( $srrl_plugin_info['Version'] ); ?>&wp_v=<?php echo esc_attr( $wp_version ); ?>" target="_blank" title="User Role Pro Plugin"><?php esc_html_e( 'Upgrade to Pro', 'user-role' ); ?></a>
						<div class="clear"></div>
					</div>
				<?php } ?>
			</div>
			<?php
		}
	}
}

if ( ! function_exists( 'srrl_blog_switcher' ) ) {
	/**
	 * Switch to blog
	 */
	function srrl_blog_switcher() {
		?>
		<div class="srrl_blog_switcher bws_pro_version">
			<select name="srrl_blog_id" disabled="disabled">
				<option value="off"><?php echo esc_html( get_bloginfo() ) . '&nbsp;(' . esc_url( parse_url( get_bloginfo( 'url' ), PHP_URL_HOST ) ) . ')'; ?></option>
			</select>
			<input type="submit" class="button-primary" name="srrl_switch_to_blog" value="<?php esc_html_e( 'Switch to Blog', 'user-role' ); ?>" disabled="disabled" />
		</div>
		<?php
	}
}

if ( ! function_exists( 'srrl_blog_list' ) ) {
	/**
	 * List for blogs
	 */
	function srrl_blog_list() {
		?>
		<div class="metabox-holder srrl_metabox">
			<table class="form-table"><tr><th><?php esc_html_e( 'Allow for blogs', 'user-role' ); ?></th></tr></table>
			<?php do_meta_boxes( 'user-role-blog.php', 'normal', null ); ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'srrl_menu_list' ) ) {
	/**
	 * Menu list
	 */
	function srrl_menu_list() {
		global $menu;
		?>
		<div id="postbox-menu" class="postbox">
			<h2 class="hndle" style="position: unset;"><span><label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" id="srrl_menu_checkbox" type="checkbox" value="srrl_plugins"><?php esc_html_e( 'Access to plugins (menu items)', 'user-role' ); ?></label></span></h2>
			<div class="inside">
				<?php
				$menu_array = $menu;
				foreach ( $menu_array as $single_menu ) {
					if ( '' === $single_menu[0] ) {
						continue;
					}
					if ( strpos( $single_menu[0], ' <' ) ) {
						$single_menu[0] = substr( $single_menu[0], 0, strpos( $single_menu[0], ' <' ) );
					}
					?>
					<label class="srrl_label_cap" ><input class="srrl_check_cap srrl_menus" type="checkbox" name="" value="0"><?php echo esc_attr( $single_menu[0] ); ?></label>
				<?php } ?>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'srrl_select' ) ) {
	/**
	 * Select blog
	 */
	function srrl_select() {
		?>
		<div class="bws_pro_version">
			<select name="srrl_select" disabled="disabled">
				<option value="-1"><?php esc_html_e( 'Select blog', 'user-role' ); ?> </option>
			</select>
		</div>
		<?php
	}
}

if ( ! function_exists( 'srrl_add_new' ) ) {
	/**
	 * Add new role
	 */
	function srrl_add_new() {
		?>
		<div class="bws_pro_version">
			<button class="button-primary" disabled="disabled"><?php esc_html_e( 'Add New', 'user-role' ); ?></button>
		</div>
		<?php
	}
}

if ( ! function_exists( 'srrl_delete_options' ) ) {
	/**
	 * Plugin delete options
	 */
	function srrl_delete_options() {
		/* recover all options on every blog to the ones in the backup */
		global $wpdb;

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		if ( ! array_key_exists( 'user-role-pro/user-role-pro.php', $all_plugins ) ) {
			if ( is_multisite() ) {
				$all_blogs = $wpdb->get_col( 'SELECT `blog_id` FROM `' . $wpdb->prefix . 'blogs`' );
				foreach ( $all_blogs as $blog_id ) {
					$srrl_repair_roles = get_blog_option( $blog_id, 'srrl_backup_option_capabilities' );
					if ( is_array( $srrl_repair_roles ) && ! empty( $srrl_repair_roles ) ) {
						update_blog_option( $blog_id, $wpdb->get_blog_prefix( $blog_id ) . 'user_roles', $srrl_repair_roles );
					}
					delete_blog_option( $blog_id, 'srrl_backup_option_capabilities' );
					delete_blog_option( $blog_id, 'srrl_options' );
				}
				delete_site_option( 'srrl_options' );
			} else {
				$srrl_repair_roles = get_option( 'srrl_backup_option_capabilities' );
				if ( is_array( $srrl_repair_roles ) && ! empty( $srrl_repair_roles ) ) {
					update_option( $wpdb->prefix . 'user_roles', $srrl_repair_roles );
				}
				delete_option( 'srrl_backup_option_capabilities' );
				delete_option( 'srrl_options' );
			}
		}

		require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
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

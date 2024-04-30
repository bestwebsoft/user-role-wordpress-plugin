<?php
/**
 * Display Content of Add/Edit Role Page
 *
 * @package User Role
 * @since 1.4.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Geting of the necessary data
 */
global $wp_roles, $srrl_options;
$roles           = $wp_roles->roles;
$default         = array(
	'manage_links',
	'export',
	'manage_options',
	'unfiltered_html',
	'import',
	'moderate_comments',
	'unfiltered_upload',
	'edit_dashboard',
	'manage_categories',
	'update_core',
	'manage_categories',
	'edit_files',
	'read',
	'upload_files',
);
$temp            = array();
$caps_array      = array();
$result          = array(
	'error'   => '',
	'message' => '',
);
$select_roles    = '';
$edit_role_error = '';
$message         = '';
$labels_array    = array(
	'posts'   => __( 'Actions with posts', 'user-role' ),
	'pages'   => __( 'Actions with pages', 'user-role' ),
	'themes'  => __( 'Actions with themes', 'user-role' ),
	'users'   => __( 'Actions with users', 'user-role' ),
	'plugins' => __( 'Actions with plugins', 'user-role' ),
	'default' => __( 'Other default actions', 'user-role' ),
	'custom'  => __( 'Other custom actions', 'user-role' ),
);
$submit_title    = __( 'Update Role', 'user-role' );
$plugin_basename = 'user-role/user-role.php';
$is_network      = is_multisite() && is_network_admin();

/**
 * Perform the necessary actions
 * and forming of the result
 */
if ( ( isset( $_POST['srrl_add_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['srrl_add_nonce_field'] ) ), 'srrl_add_action' ) ) || ( isset( $_REQUEST['_wpnonce'] ) && check_admin_referer( 'srrl_nonce_action' ) ) ) {
	if ( isset( $_REQUEST['srrl_action'] ) ) {
		switch ( $_REQUEST['srrl_action'] ) {
			case 'update':
				$role_name    = isset( $_REQUEST['srrl_role_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['srrl_role_name'] ) ) : '';
				$role_slug    = isset( $_REQUEST['srrl_role_slug'] ) ? sanitize_title( wp_unslash( $_REQUEST['srrl_role_slug'] ) ) : '';
				$allowed_caps = array();
				if ( isset( $_POST['srrl_role_caps'] ) && ! empty( $_POST['srrl_role_caps'] ) ) {
					foreach ( $_POST['srrl_role_caps'] as $capability ) {
						$allowed_caps[ sanitize_text_field( wp_unslash( $capability ) ) ] = true;
					}
				}
				if ( 'administrator' === $role_slug ) {
					$allowed_caps['activate_plugins']   = true;
					$allowed_caps['create_users']       = true;
					$allowed_caps['delete_plugins']     = true;
					$allowed_caps['delete_themes']      = true;
					$allowed_caps['delete_users']       = true;
					$allowed_caps['edit_files']         = true;
					$allowed_caps['edit_plugins']       = true;
					$allowed_caps['edit_theme_options'] = true;
					$allowed_caps['edit_themes']        = true;
					$allowed_caps['edit_users']         = true;
					$allowed_caps['export']             = true;
					$allowed_caps['import']             = true;
					$allowed_caps['install_plugins']    = true;
					$allowed_caps['install_themes']     = true;
					$allowed_caps['list_users']         = true;
					$allowed_caps['manage_options']     = true;
					$allowed_caps['promote_users']      = true;
					$allowed_caps['remove_users']       = true;
					$allowed_caps['switch_themes']      = true;
					$allowed_caps['update_core']        = true;
					$allowed_caps['update_plugins']     = true;
					$allowed_caps['update_themes']      = true;
					$allowed_caps['edit_dashboard']     = true;
					$allowed_caps['add_users']          = true;
				}
				/* save changes */
				if ( isset( $_REQUEST['srrl_save'] ) ) {
					$result = srrl_single_handle_role( $role_name, $role_slug, $allowed_caps );
					/* copy capabilities from another role */
				} elseif ( isset( $_REQUEST['srrl_copy_role'] ) && isset( $_REQUEST['srrl_select_role'] ) && '-1' !== sanitize_text_field( wp_unslash( $_REQUEST['srrl_select_role'] ) ) ) {
					$result       = srrl_copy_role();
					$allowed_caps = $result['caps'];
				}
				$edit_role_error   = $result['error'];
				$message = $result['message'];
				break;
			case 'edit':
				$role_slug    = isset( $_REQUEST['srrl_slug'] ) ? sanitize_title( wp_unslash( $_REQUEST['srrl_slug'] ) ) : '';
				$role_name    = $wp_roles->roles[ $role_slug ]['name'];
				$allowed_caps = $wp_roles->roles[ $role_slug ]['capabilities'];
				break;
			default:
				break;
		}
	}
}

if ( ! empty( $role_slug ) ) {
	/**
	 * Getting array of registered capabilities
	 */
	foreach ( $roles as $key => $data_value ) {
		$select_roles .= '<option value="' . esc_attr( $key ) . '">' . esc_attr( $data_value['name'] ) . '</option>';
		if ( ! empty( $data_value['capabilities'] ) ) {
			foreach ( $data_value['capabilities'] as $capability => $value ) {
				if ( in_array( $capability, $temp ) ) {
					continue;
				} else {
					$temp[] = $capability;
					if ( preg_match( '/level_/', $capability ) ) {
						continue;
					} elseif ( preg_match( '/posts/', $capability ) ) {
						$caps_array['posts'][ $capability ] = $capability;
					} elseif ( preg_match( '/theme/', $capability ) ) {
						$caps_array['themes'][ $capability ] = $capability;
					} elseif ( preg_match( '/users/', $capability ) ) {
						$caps_array['users'][ $capability ] = $capability;
					} elseif ( preg_match( '/pages/', $capability ) ) {
						$caps_array['pages'][ $capability ] = $capability;
					} elseif ( preg_match( '/plugins/', $capability ) ) {
						$caps_array['plugins'][ $capability ] = $capability;
					} elseif ( in_array( $capability, $default ) ) {
						$caps_array['default'][ $capability ] = $capability;
					} else {
						$caps_array['custom'][ $capability ] = $capability;
					}
				}
			}
		}
	}
	foreach ( $caps_array as $key => $value ) {
		asort( $value );
		$caps_array[ $key ] = $value;
	}
	asort( $caps_array );

	/*
	 * Forming html-structure of the list of capabilities via metaboxes
	 */
	foreach ( $caps_array as $key => $value ) {
		add_meta_box(
			"postbox-{$key}",
			'<label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_' . esc_attr( $key ) . '" />' . esc_html( $labels_array[ $key ] ) . '</label>',
			'srrl_metabox_content',
			'user-role.php',
			'normal',
			'default',
			array( $value, $allowed_caps, 'srrl_' . $key )
		);
	}

	/**
	 * Forming html-structure of additional settings on network
	 */
	if ( $is_network ) {
		global $wpdb;
		$blogs         = $wpdb->get_results( "SELECT `blog_id`, `domain` FROM `{$wpdb->base_prefix}blogs`;" );
		$selected_blog = isset( $_REQUEST['srrl_blog_id'] ) ? intval( $_REQUEST['srrl_blog_id'] ) : 1;
		$checkboxes    = '';
		foreach ( $blogs as $blog ) {
			$prefix    = 1 === absint( $blog->blog_id ) ? $wpdb->base_prefix : $wpdb->base_prefix . $blog->blog_id . '_';
			$blog_name = $wpdb->get_var( 'SELECT `option_value` FROM `' . $prefix . 'options` WHERE `option_name` = "blogname"' );
			/* check if role is already exists for current blog */
			if ( in_array( sanitize_text_field( $_REQUEST['srrl_action'] ), array( 'update', 'edit' ) ) ) {
				$role_exists = array_key_exists( $role_slug, get_blog_option( $blog->blog_id, $prefix . 'user_roles' ) ) ? '' : __( 'role doesn\'t exists', 'user-role' );
			} else {
				$role_exists = '';
			}
			$checkboxes .=
				'<label class="srrl_blogs_list">
					<span class="srrl_blog_checkbox"><input class="srrl_blog" type="checkbox" disabled="disabled" /></span>
					<span class="srrl_blog_info">' . esc_html( $blog_name ) . '&nbsp;<br />(' . esc_attr( $blog->domain ) . ')<br /></span>
				</label>';
		}
		$blog_list_title =
			'<label>
				<input class="hide-if-no-js" type="checkbox" disabled="disabled" />' . __( 'All', 'user-role' ) .
			'</label>' .
			bws_add_help_box( __( 'The role will be created automatically for blogs where it does not exist', 'user-role' ) );

		add_meta_box(
			'postbox-list-of-blogs',
			$blog_list_title,
			'srrl_list_of_blogs',
			'user-role-blog.php',
			'normal',
			'default',
			array( $checkboxes )
		);
	} else {
		$checkboxes = '';
	}

	/**
	 * Display warning-message
	 */
	if ( in_array( sanitize_text_field( wp_unslash( $_REQUEST['srrl_action'] ) ), array( 'edit', 'update' ) ) ) {
		global $current_user;
		$user_roles = $current_user->roles;
		$user_role  = array_shift( $user_roles );
		if ( $user_role === $role_slug ) { ?>
			<div class="error inline"><p><strong><?php esc_html_e( 'Warning:', 'user-role' ); ?></strong>&nbsp;<?php esc_html_e( 'You are trying to edit your own role. Please read before', 'user-role' ); ?>&nbsp;<a href="https://codex.wordpress.org/Roles_and_Capabilities" target="_blank"><?php esc_html_e( 'Roles and Capabilities', 'user-role' ); ?></a>.<br/>
			<?php esc_html_e( 'Changes will be applied immediately after saving. If you are not sure - do not click "Update Role" button.', 'user-role' ); ?></p></div>
			<?php
		}
	}

	/**
	 * Display page
	 */
	if ( ! empty( $message ) ) {
		?>
		<div class="updated"><p><strong><?php echo esc_html( $message ); ?>.</strong></p></div>
		<?php
	}
	if ( ! empty( $edit_role_error ) ) {
		?>
		<div class="error"><p><strong><?php echo esc_html( $edit_role_error ); ?>.</strong></p></div>
	<?php } ?>
	<form class="bws_form" id="srrl_form" method="post" action="<?php get_admin_url(); ?>?page=srrl_add_new_roles">
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Role Name', 'user-role' ); ?></th>
				<td><input type="text" name="srrl_role_name" value="<?php echo esc_attr( $role_name ); ?>" maxlength="150" /></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Role Slug', 'user-role' ); ?></th>
				<td>
					<input type="text" name="srrl_role_slug" value="<?php echo esc_attr( $role_slug ); ?>" maxlength="150" readonly="readonly" /><br />
					<span class="bws_info"><?php esc_html_e( 'Slug must contain only latin letters. Also You can add numbers and symbols "-" or "_"', 'user-role' ); ?>.</span>
				</td>
			</tr>
		</table>
		<div class="metabox-holder srrl_metabox">
			<table class="form-table"><tr><th><?php esc_html_e( 'Capabilities', 'user-role' ); ?></th></tr></table>
			<div class="postbox srrl_postbox">
				<div class="hndle">
					<div class="<?php echo is_rtl() ? 'alignright' : 'alignleft'; ?>">
						<label>
							<input class="hide-if-no-js" type="checkbox" name="srrl_all_capabilities" value="1" />
							<strong><?php esc_html_e( 'All', 'user-role' ); ?></strong>
						</label>
					</div>
					<div class="srrl_row <?php echo is_rtl() ? 'alignleft' : 'alignright'; ?>">
						<div class="srrl_cell"><strong><?php esc_html_e( 'Copy from', 'user-role' ); ?></strong>&nbsp;</div>
						<?php if ( $is_network ) { ?>
							<div class="srrl_cell">
								<?php srrl_pro_block( 'srrl_select', 'srrl_select', false, false ); ?>
							</div>
						<?php } ?>
						<div class="srrl_cell">
							<select name="srrl_select_role">
								<option value="-1"><?php esc_html_e( 'Select role', 'user-role' ); ?></option>
								<?php echo $select_roles; ?>
							</select>
							<input type="submit" class="button-primary" name="srrl_copy_role" value="<?php esc_html_e( 'Apply', 'user-role' ); ?>" />
							<input type="hidden" name="srrl_action" value="update" />
						</div>
					</div>
				</div>
			</div>
			<?php
			srrl_pro_block( 'srrl_menu_list', '', false );
			do_meta_boxes( 'user-role.php', 'normal', null );
			?>
		</div>
		<?php
		if ( $is_network ) {
			srrl_pro_block( 'srrl_blog_list', 'srrl_blog_list', false );}
		?>
		<p>
			<input id="bws-submit-button" type="submit" class="button-primary" name="srrl_save" value="<?php echo esc_attr( $submit_title ); ?>" />
			<input type="hidden" name="srrl_action" value="update" />
			<?php wp_nonce_field( 'srrl_add_action', 'srrl_add_nonce_field' ); ?>
		</p>
	</form>
	<?php
} else {
	$bws_hide_premium = bws_hide_premium_options_check( $srrl_options );

	if ( $bws_hide_premium ) {
		?>
		<p>
			<?php
			esc_html_e( 'This tab contains Pro options only.', 'pdf-print' );
			echo ' ' . sprintf(
				esc_html__( '%1$sChange the settings%2$s to view the Pro options.', 'pdf-print' ),
				'<a href="admin.php?page=srrl_settings&bws_active_tab=misc">',
				'</a>'
			);
			?>
		</p>
	<?php } else {
		require_once dirname( __FILE__ ) . '/pro-tabs.php';
		srrl_pro_block( 'srrl_pro_add_new_block', '', false );
	}
} ?>

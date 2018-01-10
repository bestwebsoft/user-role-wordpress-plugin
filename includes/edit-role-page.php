<?php
/**
 * Display Content of Add/Edit Role Page
 * @package User Role
 * @since 1.4.9
 */

/**
 * geting of the necessary data
 */

global $wp_roles;
$roles   = $wp_roles->roles;
$default = array(
	"manage_links", "export", "manage_options", "unfiltered_html", "import", "moderate_comments",
	"unfiltered_upload", "edit_dashboard", "manage_categories", "update_core", "manage_categories",
	"edit_files", "read", "upload_files" );
$temp = $caps_array = array();
$result = array( 'error' => '', 'message' => '' );
$select_roles = $error = $message = '';
$labels_array = array(
	'posts'   => __( 'Actions with posts', 'user-role' ),
	'pages'   => __( 'Actions with pages', 'user-role' ),
	'themes'  => __( 'Actions with themes', 'user-role' ),
	'users'   => __( 'Actions with users', 'user-role' ),
	'plugins' => __( 'Actions with plugins', 'user-role' ),
	'default' => __( 'Other default actions', 'user-role' ),
	'custom'  => __( 'Other custom actions', 'user-role' )
);
$submit_title = __( 'Update Role', 'user-role' );

/**
 * perform the necessary actions
 * and forming of the result
 */
switch ( $_REQUEST['srrl_action'] ) {
	case 'update':
		check_admin_referer( $plugin_basename, 'srrl_nonce_name' );
		$role_name    = isset( $_REQUEST['srrl_role_name'] ) ? stripslashes( trim( esc_html( $_REQUEST['srrl_role_name'] ) ) ) : '';
		$role_slug    = isset( $_REQUEST['srrl_role_slug'] ) ? stripslashes( trim( esc_html( $_REQUEST['srrl_role_slug'] ) ) ) : '';
		$allowed_caps = array();
		if ( isset( $_POST['srrl_role_caps'] ) && ! empty( $_POST['srrl_role_caps'] ) ) {
			foreach ( $_POST['srrl_role_caps'] as $capability ) {
				$allowed_caps[ $capability ] = true;
			}
		}
		if ( 'administrator' == $role_slug ) {
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
		} elseif ( isset( $_REQUEST['srrl_copy_role'] ) && isset( $_REQUEST['srrl_select_role'] ) && '-1' != $_REQUEST['srrl_select_role'] ) {
			$result = srrl_copy_role();
			$allowed_caps = $result['caps'];
		}
		$error        = $result['error'];
		$message      = $result['message'];
		break;
	case 'edit':
		check_admin_referer( 'srrl_' . $_REQUEST['srrl_slug'] );
		$role_slug    = stripslashes( trim( esc_html( $_REQUEST['srrl_slug'] ) ) );
		$role_name    = $wp_roles->roles[ $role_slug ]['name'];
		$allowed_caps = $wp_roles->roles[ $role_slug ]['capabilities'];
		break;
	default:
		break;
}

if ( ! empty( $role_slug ) ) {
	/**
	 * getting array of registered capabilities
	 */
	foreach ( $roles as $key => $data_value ) {
		$select_roles .= '<option value="' . $key . '">' . $data_value['name'] . '</option>';
		if ( ! empty( $data_value['capabilities'] ) ) {
			foreach( $data_value['capabilities'] as $capability => $value ) {
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
	 * forming html-structure of the list of capabilities via metaboxes
	 */
	foreach ( $caps_array as $key => $value ) {
		add_meta_box(
			"postbox-{$key}",
			'<label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" id="' . $key . '_checkbox" type="checkbox" value="srrl_' . $key . '" />' . $labels_array[ $key ] . '</label>',
			'srrl_metabox_content',
			'user-role.php',
			'normal',
			'default',
			array( $value, $allowed_caps, 'srrl_' . $key )
		);
	}

	/**
	 * forming html-structure of additional settings on network
	 */
	if ( $is_network ) {
		global $wpdb;
		$blogs = $wpdb->get_results( "SELECT `blog_id`, `domain` FROM `{$wpdb->base_prefix}blogs`;" );
		$selected_blog = isset( $_REQUEST['srrl_blog_id'] ) ? $_REQUEST['srrl_blog_id'] : 1;
		$checkboxes    = '';
		foreach ( $blogs as $blog ) {
			$prefix    = 1 == $blog->blog_id ? $wpdb->base_prefix : $wpdb->base_prefix . $blog->blog_id . '_';
			$blog_name = $wpdb->get_var( "SELECT `option_value` FROM `{$prefix}options` WHERE `option_name` = 'blogname'" );
			/* check if role is already exists for current blog */
			if ( in_array( $_REQUEST['srrl_action'], array( 'update', 'edit' ) ) )
				$role_exists = array_key_exists( $role_slug, get_blog_option( $blog->blog_id, $prefix .'user_roles' ) ) ? '' : __( 'role doesn\'t exists', 'user-role' );
			else
				$role_exists = '';
			$checkboxes   .=
				'<label class="srrl_blogs_list">
					<span class="srrl_blog_checkbox"><input class="srrl_blog" type="checkbox" disabled="disabled" /></span>
					<span class="srrl_blog_info">' . $blog_name . '&nbsp;<br />(' . $blog->domain . ')<br /></span>
				</label>';
		}
		$blog_list_title =
			'<label>
				<input class="hide-if-no-js" id="all_blogs_checkbox" type="checkbox" disabled="disabled" />' . __( 'All', 'user-role' ) .
			'</label>' .
			bws_add_help_box( __( 'The role will be created automatically for blogs where it does not exist', 'user-role' ) );

		add_meta_box(
			"postbox-list-of-blogs",
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
	 * display warning-message
	 */
	if ( in_array( $_REQUEST['srrl_action'], array( 'edit', 'update' ) ) ) {
		global $current_user;
		$user_roles = $current_user->roles;
		$user_role  = array_shift($user_roles);
		if ( $user_role == $role_slug ) { ?>
			<div class="error inline"><p><strong><?php _e( 'Warning:', 'user-role' ); ?></strong>&nbsp;<?php _e( 'You are trying to edit your own role. Please read before', 'user-role' ); ?>&nbsp;<a href="https://codex.wordpress.org/Roles_and_Capabilities" target="_blank"><?php _e( 'Roles and Capabilities', 'user-role' ); ?></a>.<br/>
			<?php _e( 'Changes will be applied immediately after saving. If you are not sure - do not click "Update Role" button.', 'user-role' ); ?></p></div>
		<?php }
	}

	/**
	 * display page
	 */
	if ( ! empty( $message ) ) { ?>
		<div class="updated"><p><strong><?php echo $message; ?>.</strong></p></div>
	<?php }
	if ( ! empty( $error ) ) { ?>
		<div class="error"><p><strong><?php echo $error; ?>.</strong></p></div>
	<?php } ?>
	<div id="bws_save_settings_notice" class="updated fade" style="display:none">
		<p>
			<strong><?php _e( 'Notice', 'user-role' ); ?></strong>: <?php _e( "The role's settings have been changed.", 'user-role' ); ?>
			<a class="bws_save_anchor" href="#bws-submit-button"><?php echo $submit_title; ?></a>
		</p>
	</div>
	<form class="bws_form" id="srrl_form" method="post" action="<?php get_admin_url(); ?>?page=user-role.php">
		<table class="form-table">
			<tr>
				<th><?php _e( 'Role Name', 'user-role' ); ?></th>
				<td><input type="text" name="srrl_role_name" value="<?php echo $role_name; ?>" maxlength="150" /></td>
			</tr>
			<tr>
				<th><?php _e( 'Role Slug', 'user-role' ); ?></th>
				<td>
					<input type="text" name="srrl_role_slug" value="<?php echo $role_slug; ?>" maxlength="150" readonly="readonly" /><br />
					<span class="bws_info"><?php _e( 'Slug must contain only latin letters. Also You can add numbers and symbols "-" or "_"', 'user-role' ); ?>.</span>
				</td>
			</tr>
		</table>
		<div class="metabox-holder srrl_metabox">
			<table class="form-table"><tr><th><?php _e( 'Capabilities', 'user-role' ); ?></th></tr></table>
			<div class="postbox srrl_postbox">
				<div class="hndle">
					<div class="<?php echo is_rtl() ? 'alignright' : 'alignleft'; ?>">
						<label>
							<input class="hide-if-no-js" type="checkbox" name="srrl_all_capabilities" value="1" />
							<strong><?php _e( 'All', 'user-role' ); ?></strong>
						</label>
					</div>
					<div class="srrl_row <?php echo is_rtl() ? 'alignleft' : 'alignright';?>">
						<div class="srrl_cell"><strong><?php _e( 'Copy from', 'user-role' ); ?></strong>&nbsp;</div>
						<?php if ( $is_network ) { ?>
							<div class="srrl_cell">
								<?php srrl_pro_block( 'srrl_select', 'srrl_select', false, false ); ?>
							</div>
						<?php } ?>
						<div class="srrl_cell">
							<select name="srrl_select_role">
								<option value="-1"><?php _e( 'Select role', 'user-role' ); ?></option>
								<?php echo $select_roles; ?>
							</select>
							<input type="submit" class="button-primary" name="srrl_copy_role" value="<?php _e( 'Apply', 'user-role' ); ?>" />
						</div>
					</div>
				</div>
			</div>
			<?php do_meta_boxes( 'user-role.php', 'normal', null ); ?>
		</div>
		<?php if ( $is_network )
			srrl_pro_block( 'srrl_blog_list', 'srrl_blog_list' ); ?>
		<p>
			<input id="bws-submit-button" type="submit" class="button-primary" name="srrl_save" value="<?php echo $submit_title; ?>" />
			<input type="hidden" name="srrl_action" value="update" />
			<?php wp_nonce_field( $plugin_basename, 'srrl_nonce_name' ); ?>
		</p>
	</form>
<?php } ?>

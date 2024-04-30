<?php
/**
 * Banners on plugin settings page
 *
 * @package User Role
 * @since 1.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! function_exists( 'srrl_pro_add_new_block' ) ) {
	/**
	 * Display pro block
	 */
	function srrl_pro_add_new_block() { ?>
		<div class="bws_pro_version">
			<form id="srrl_form" method="post" action="">
				<table class="form-table">
					<tbody><tr>
						<th>Name</th>
						<td>
							<input disabled="disabled" type="text" name="srrl_role_name" value="" maxlength="150">
						</td>
					</tr>
					<tr>
						<th>Slug</th>
						<td>
							<input disabled="disabled" type="text" name="srrl_role_slug" value="" maxlength="150"><br>
							<span class="bws_info">Slug must contain only latin letters, numbers and symbols ( "-" or "_" ).</span>
						</td>
					</tr>
					</tbody>
				</table>
				<div class="metabox-holder srrl_metabox">
					<table class="form-table"><tbody><tr><th>Capabilities</th></tr></tbody></table>
					<div class="postbox srrl_postbox">
						<div class="hndle">
							<div class="alignleft">
								<label>
									<input disabled="disabled" class="hide-if-no-js" type="checkbox" name="srrl_all_capabilities" value="1">
									<strong>All</strong>
								</label>
							</div>
							<div class="alignright">
								<label><strong>Copy from</strong>&nbsp;</label>
								<select disabled="disabled" name="srrl_select_role">
									<option value="-1">Select role</option>
									<option value="administrator">Administrator</option><option value="editor">Editor</option><option value="author">Author</option><option value="contributor">Contributor</option><option value="subscriber">Subscriber</option></select>
								<input disabled="disabled" type="submit" class="button-primary" name="srrl_copy_role" value="Apply">
							</div>
						</div>
					</div>
					<div id="normal-sortables" class="meta-box-sortables">
						<div id="postbox-plugins" class="postbox">
							<button type="button" class="handlediv srrl_handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <label class="srrl_group_label"><input disabled="disabled" class="hide-if-no-js srrl_group_cap" id="plugins_checkbox" type="checkbox" value="srrl_plugins">Access to plugins (menu items)</label></span><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle"><span><label class="srrl_group_label"><input disabled="disabled" class="hide-if-no-js srrl_group_cap" id="menus_checkbox" type="checkbox" value="srrl_menus">Access to plugins (menu items)</label></span></h2>
							<div class="inside">
								<label class="srrl_label_cap" for="srrl_activate_menus_Dashboard">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Dashboard" id="srrl_activate_menus_Dashboard" value="1" checked="checked">
									Dashboard							</label>
								<label class="srrl_label_cap" for="srrl_activate_menus_Posts">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Posts" id="srrl_activate_menus_Posts" value="0">
									Posts							</label>
								<label class="srrl_label_cap" for="srrl_activate_menus_Media">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Media" id="srrl_activate_menus_Media" value="0">
									Media							</label>
								<label class="srrl_label_cap" for="srrl_activate_menus_Pages">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Pages" id="srrl_activate_menus_Pages" value="0">
									Pages							</label>
								<label class="srrl_label_cap" for="srrl_activate_menus_Comments">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Comments" id="srrl_activate_menus_Comments" value="0">
									Comments							</label>
								<label class="srrl_label_cap" for="srrl_activate_menus_Appearance">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Appearance" id="srrl_activate_menus_Appearance" value="0">
									Appearance							</label>
								<label class="srrl_label_cap" for="srrl_activate_menus_Plugins">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Plugins" id="srrl_activate_menus_Plugins" value="0">
									Plugins							</label>
								<label class="srrl_label_cap" for="srrl_activate_menus_Users">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Users" id="srrl_activate_menus_Users" value="0">
									Users							</label>
								<label class="srrl_label_cap" for="srrl_activate_menus_Tools">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Tools" id="srrl_activate_menus_Tools" value="0">
									Tools							</label>
								<label class="srrl_label_cap" for="srrl_activate_menus_Settings">
									<input disabled="disabled" class="srrl_check_cap srrl_menus" type="checkbox" name="Settings" id="srrl_activate_menus_Settings" value="0">
									Settings							</label>
							</div>
						</div>
						<div id="normal-sortables" class="meta-box-sortables">
							<div id="postbox-plugins" class="postbox  closed">
								<div class="postbox-header">
									<h2 class="hndle"><label class="srrl_group_label"><input disabled="disabled" class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_plugins">Actions with plugins</label></h2>
									<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="postbox-plugins-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-plugins-handle-order-higher-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_plugins">Actions with plugins</label> box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="postbox-plugins-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-plugins-handle-order-lower-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_plugins">Actions with plugins</label> box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_plugins">Actions with plugins</label></span><span class="toggle-indicator" aria-hidden="true"></span></button></div>
								</div>
							</div>
							<div id="postbox-users" class="postbox  closed">
								<div class="postbox-header">
									<h2 class="hndle"><label class="srrl_group_label"><input disabled="disabled" class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_users">Actions with users</label></h2>
									<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="postbox-users-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-users-handle-order-higher-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_users">Actions with users</label> box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="postbox-users-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-users-handle-order-lower-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_users">Actions with users</label> box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_users">Actions with users</label></span><span class="toggle-indicator" aria-hidden="true"></span></button></div>
								</div>
							</div>
							<div id="postbox-themes" class="postbox  closed">
								<div class="postbox-header">
									<h2 class="hndle"><label class="srrl_group_label"><input disabled="disabled" class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_themes">Actions with themes</label></h2>
									<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="postbox-themes-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-themes-handle-order-higher-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_themes">Actions with themes</label> box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="postbox-themes-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-themes-handle-order-lower-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_themes">Actions with themes</label> box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_themes">Actions with themes</label></span><span class="toggle-indicator" aria-hidden="true"></span></button></div>
								</div>
							</div>
							<div id="postbox-pages" class="postbox  closed">
								<div class="postbox-header">
									<h2 class="hndle"><label class="srrl_group_label"><input disabled="disabled" class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_pages">Actions with pages</label></h2>
									<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="postbox-pages-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-pages-handle-order-higher-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_pages">Actions with pages</label> box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="postbox-pages-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-pages-handle-order-lower-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_pages">Actions with pages</label> box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_pages">Actions with pages</label></span><span class="toggle-indicator" aria-hidden="true"></span></button></div>
								</div>
							</div>
							<div id="postbox-posts" class="postbox  closed">
								<div class="postbox-header">
									<h2 class="hndle"><label class="srrl_group_label"><input disabled="disabled" class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_posts">Actions with posts</label></h2>
									<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="postbox-posts-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-posts-handle-order-higher-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_posts">Actions with posts</label> box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="postbox-posts-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-posts-handle-order-lower-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_posts">Actions with posts</label> box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_posts">Actions with posts</label></span><span class="toggle-indicator" aria-hidden="true"></span></button></div>
								</div>
							</div>
							<div id="postbox-default" class="postbox  closed">
								<div class="postbox-header">
									<h2 class="hndle"><label class="srrl_group_label"><input disabled="disabled" class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_default">Other default actions</label></h2>
									<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="postbox-default-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-default-handle-order-higher-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_default">Other default actions</label> box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="postbox-default-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-default-handle-order-lower-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_default">Other default actions</label> box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_default">Other default actions</label></span><span class="toggle-indicator" aria-hidden="true"></span></button></div>
								</div>
							</div>
							<div id="postbox-custom" class="postbox  closed">
								<div class="postbox-header">
									<h2 class="hndle"><label class="srrl_group_label"><input disabled="disabled" class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_custom">Other custom actions</label></h2>
									<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="postbox-custom-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-custom-handle-order-higher-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_custom">Other custom actions</label> box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="postbox-custom-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="postbox-custom-handle-order-lower-description">Move <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_custom">Other custom actions</label> box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <label class="srrl_group_label"><input class="hide-if-no-js srrl_group_cap" type="checkbox" value="srrl_custom">Other custom actions</label></span><span class="toggle-indicator" aria-hidden="true"></span></button></div>
								</div>
							</div>
						</div>
					</div>
					<p><input id="bws-submit-button" type="submit" class="button-primary" name="srrl_save" disabled="disabled" value="Add New"></p>
				</div>
			</form>
		</div>
		<?php
	}
}

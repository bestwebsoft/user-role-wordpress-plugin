<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'Srrl_Settings_Tabs' ) ) {
	/**
	 * Class for display Settings Tab
	 */
	class Srrl_Settings_Tabs extends Bws_Settings_Tabs {

		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename Plugin basename.
		 */
		public function __construct( $plugin_basename ) {
			global $srrl_options, $srrl_plugin_info;

			$tabs = array(
				'misc'    => array( 'label' => __( 'Misc', 'user-role' ) ),
				'license' => array( 'label' => __( 'License Key', 'user-role' ) ),
			);

			parent::__construct(
				array(
					'plugin_basename'    => $plugin_basename,
					'plugins_info'       => $srrl_plugin_info,
					'prefix'             => 'srrl',
					'default_options'    => srrl_get_options_default(),
					'options'            => $srrl_options,
					'is_network_options' => is_network_admin(),
					'tabs'               => $tabs,
					'wp_slug'            => 'user-role',
					'link_key'           => '0e8fa1e4abf7647412878a5570d4977a',
					'link_pn'            => '132',
					'doc_link'           => 'https://bestwebsoft.com/documentation/user-role/user-role-user-guide/',
				)
			);

		}

		/**
		 * Save options
		 */
		public function save_options() {}

		/**
		 * Display tab
		 */
		public function tab_settings() {}

	}
}

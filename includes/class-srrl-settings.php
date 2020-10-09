<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Srrl_Settings_Tabs' ) ) {
    class Srrl_Settings_Tabs extends Bws_Settings_Tabs {

        /**
	     * Constructor.
	     *
	     * @access public
	     *
	     * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
	     *
	     * @param string $plugin_basename
	     */
        public function __construct( $plugin_basename ) {
            global $srrl_options, $srrl_plugin_info;

            $tabs = array(
                'misc'      => array( 'label' => __( 'Misc', 'user-role' ) ),
                'license'   => array( 'label' => __( 'License Key', 'user-role' ) )
            );

            parent::__construct( array(
                'plugin_basename'		=> $plugin_basename,
                'plugins_info'			=> $srrl_plugin_info,
                'prefix'				=> 'srrl',
                'default_options'		=> srrl_get_options_default(),
                'options'				=> $srrl_options,
                'is_network_options'	=> is_network_admin(),
                'tabs'					=> $tabs,
                'wp_slug'				=> 'user-role',
	            'link_key'				=> '0e8fa1e4abf7647412878a5570d4977a',
				'link_pn'				=> '132',
                'doc_link'              => 'https://docs.google.com/document/d/1IJvHCU_bn3w0WnPP2mQ5dq6LLicRvOSNwaq_M26GsIY/'
            ) );

        }

        public function save_options() {}

        public function tab_settings() {}

    }
}
<?php
/**
 * List with user roles
 *
 * @package User Role
 * @since 1.4.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'Srrl_Roles_List' ) ) {
	/**
	 * Class for display table with user role
	 */
	class Srrl_Roles_List extends WP_List_Table {
		/**
		 * Array for default roles
		 *
		 * @var array
		 */
		public static $default_roles = array( 'administrator', 'author', 'editor', 'contributor', 'subscriber' );
		/**
		 * Default role
		 *
		 * @var string
		 */
		public $default_role;
		/**
		 * Count for all items
		 *
		 * @var int
		 */
		public $total_items;
		/**
		 * Flag for network
		 *
		 * @var bool
		 */
		public $is_network;
		/**
		 * Plugin basename
		 *
		 * @var string
		 */
		public $basename;
		/**
		 * Flag for show ads
		 *
		 * @var bool
		 */
		public $show_ads;

		/**
		 * Constructor of class
		 *
		 * @param string $plugin_basename Plugin basename.
		 */
		public function __construct( $plugin_basename ) {
			global $srrl_options;
			$this->basename     = $plugin_basename;
			$this->default_role = get_option( 'default_role' );
			$this->total_items  = 0;
			$this->is_network   = is_multisite() && is_network_admin() ? true : false;
			$this->show_ads     = ! bws_hide_premium_options_check( $srrl_options );
			parent::__construct();
		}

		/**
		 * Disaply list of roles
		 */
		public function display_list() {
			$result = $this->get_result_message(); ?>
			<div class="updated inline" <?php echo empty( $result['message'] ) ? 'style="display:none"' : ''; ?>><p><strong><?php echo esc_html( $result['message'] ); ?></strong></p></div>
			<div class="error inline" <?php echo empty( $result['notice'] ) ? 'style="display:none"' : ''; ?>><p><strong><?php echo esc_html( $result['notice'] ); ?></strong></p></div>
			<div class="error inline" <?php echo empty( $result['error'] ) ? 'style="display:none"' : ''; ?>><p><strong><?php echo esc_html( $result['error'] ); ?></strong></p></div>
			<form id="srrl_list_table" method="post" action="<?php get_admin_url(); ?>?page=user-role.php">
				<?php
				$this->current_action();
				$this->prepare_items();
				$this->display();
				?>
			</form>
			<div class="clear"></div>
			<?php
		}

		/**
		 * Fires before displaying of the list of roles
		 */
		public function prepare_items() {
			$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
			$this->items           = $this->items_list();
			$this->set_pagination_args(
				array(
					'total_items' => count( $this->items ),
					'total_pages' => 1,
					'per_page'    => 9999,
				)
			);
		}

		/**
		 * Function to show message if not templates found
		 */
		public function no_items() {
			?>
			<p><?php esc_html_e( 'Roles not found', 'user-role' ); ?></p>
		<?php }

		/**
		 * Function to add action links to drop down menu before and after templates list
		 *
		 * @return array of actions
		 */
		public function get_bulk_actions() {
			$actions            = array();
			$actions['recover'] = __( 'Recover', 'user-role' );
			return $actions;
		}

		/**
		 * Display dropdown menu with list of blogs
		 *
		 * @param string $which 'top' - function call before displaying of the list of roles, 'bottom' - after displaying of the list of roles.
		 */
		public function extra_tablenav( $which ) {
			if ( $this->is_network ) {
				srrl_pro_block( 'srrl_blog_switcher', 'srrl_blog_switcher alignright', false, false );
			}
		}

		/**
		 * Display paginations
		 *
		 * @param string $which 'top' - function call before displaying of the list of roles, 'bottom' - after displaying of the list of roles.
		 */
		public function pagination( $which ) {
			global $srrl_options;
			$style             = $this->show_ads ? ' style="margin-top: 17px;"' : '';
			$this->_pagination =
				'<div class="tablenav-pages alignright"' . $style . '>
					<span class="displaying-num">' .
						sprintf( _n( '%s role', '%s roles', $this->_pagination_args['total_items'], 'user-role' ), number_format_i18n( $this->_pagination_args['total_items'] ) ) .
					'</span>
				</div>';
			echo wp_kses_post( $this->_pagination );
		}

		/**
		 * Get a list of columns.
		 *
		 * @return array list of columns and titles
		 */
		public function get_columns() {
			$columns = array(
				'cb'    => '<input type="checkbox" />',
				'name'  => __( 'Role Name', 'user-role' ),
				'slug'  => __( 'Role Slug', 'user-role' ),
				'type'  => __( 'Type', 'user-role' ),
				'users' => __( 'Users', 'user-role' ),
				'caps'  => __( 'Capabilities', 'user-role' ),
			);
			return $columns;
		}

		/**
		 * Get a list of sortable columns.
		 *
		 * @return array list of sortable columns
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'name'  => array( 'name', false ),
				'slug'  => array( 'slug', false ),
				'type'  => array( 'type', false ),
				'users' => array( 'users', false ),
				'caps'  => array( 'caps', false ),
			);
			return $sortable_columns;
		}

		/**
		 * Fires when the default column output is displayed for a single row.
		 *
		 * @param array  $item        The cuurrent letter data.
		 * @param string $column_name The custom column's name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'cb':
				case 'name':
				case 'slug':
				case 'type':
				case 'users':
				case 'caps':
					return $item[ $column_name ];
				default:
					return print_r( $item, true );
			}
		}

		/**
		 * Add column of checboxes
		 *
		 * @param     array $item        The cuurrent letter data.
		 * @return    string                  with html-structure of <input type=['checkbox']>
		 */
		public function column_cb( $item ) {
			return sprintf( '<input id="cb_%1s" type="checkbox" name="srrl_slug[]" value="%2s" />', $item['slug'], $item['slug'] );
		}

		/**
		 * Add action links to title column depenting on status page
		 *
		 * @param array $item The current letter data.
		 * @return String with action links
		 */
		public function column_name( $item ) {
			$actions     = array();
			$row_actions = array(
				'edit'    => __( 'Edit', 'user-role' ),
				'recover' => __( 'Recover', 'user-role' ),
				'reset'   => __( 'Reset', 'user-role' ),
				'delete'  => __( 'Delete', 'user-role' ),
			);
			foreach ( $row_actions as $key => $value ) {
				switch ( $key ) {
					case 'edit':
						$title           = ' title="' . __( 'Edit role capabilities', 'user-role' ) . '"';
						$url             = wp_nonce_url( '?page=srrl_add_new_roles&srrl_action=' . $key . '&srrl_slug=' . $item['slug'], 'srrl_nonce_action' );
						$actions[ $key ] = '<a href="' . $url . '"' . $title . '>' . $value . '</a>';
						break;
					case 'reset':
						if ( in_array( $item['slug'], self::$default_roles ) && $this->show_ads ) {
							$title           = ' title="' . __( 'The default WordPress role will be restored to the default WordPress capabilities. This option is available in Pro version of plugin', 'user-role' ) . '"';
							$actions[ $key ] = '<span' . $title . ' style="color: #555;">' . $value . '</span>';
						}
						break;
					case 'recover':
						$title           = ' title="' . __( 'Restore role capabilities that were set at the time of the plugin activation or when the role was created', 'user-role' ) . '"';
						$nonce_url       = wp_nonce_url( '?page=user-role.php&srrl_action=' . $key . ' &srrl_slug=' . $item['slug'], 'srrl_nonce_action' );
						$actions[ $key ] = '<a data-confirm="recover" href="' . $nonce_url . '"' . $title . '>' . $value . '</a>';
						break;
					case 'delete':
						if ( ! in_array( $item['slug'], self::$default_roles ) && $this->show_ads ) {
							$title           = ' title="' . __( 'Delete role. This option is available in Pro version of plugin', 'user-role' ) . '"';
							$actions[ $key ] = '<span' . $title . ' style="color: #555;">' . $value . '</span>';
						}
						break;
					default:
						$title           = '';
						$nonce_url       = wp_nonce_url( '?page=user-role.php&srrl_action=' . $key . '&srrl_slug=' . $item['slug'], 'srrl_nonce_action' );
						$actions[ $key ] = '<a href="' . $nonce_url . '"' . $title . '>' . $value . '</a>';
						break;
				}
			}
			return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( $actions ) );
		}

		/**
		 * Getting of the list of roles
		 *
		 * @return   array    $list
		 */
		public function items_list() {
			global $wp_roles;
			if ( isset( $_REQUEST['srrl_action'] ) && in_array( sanitize_text_field( wp_unslash( $_REQUEST['srrl_action'] ) ), array( 'reset', 'recover' ) ) ) {
				if ( method_exists( $wp_roles, 'for_site' ) ) {
					$wp_roles->for_site();
				} else {
					$wp_roles->reinit();
				}
			}
			$list        = array();
			$count_users = count_users();
			$i           = 0;

			foreach ( $wp_roles->roles as $key => $role ) {
				$this->total_items ++;
				/* edit link  */
				$nonce_url = wp_nonce_url( "?page=user-role.php&srrl_action=edit&srrl_slug={$key}", 'srrl_nonce_action' );
				$default   = $key === $this->default_role ? '&nbsp;<i>-&nbsp;' . __( 'default', 'user-role' ) . '</i>' : '';
				$list[]    = array(
					'name'  => "<strong><a href=\"{$nonce_url}\">{$role['name']}</a></strong>{$default}",
					'slug'  => $key,
					'type'  => in_array( $key, self::$default_roles ) ? __( 'Built-in', 'user-role' ) : __( 'Custom', 'user-role' ),
					'users' => array_key_exists( $key, $count_users['avail_roles'] ) ? $count_users['avail_roles'][ $key ] : 0,
					'caps'  => count( $role['capabilities'] ),
				);
			}
			if ( ! empty( $list ) ) {
				if ( isset( $_REQUEST['orderby'] ) && 'name' !== sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) {
					$flag = isset( $_REQUEST['order'] ) && 'asc' === sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ? SORT_ASC : SORT_DESC;
					$list = $this->list_sort(
						$list,
						array(
							sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) => array( $flag, SORT_REGULAR ),
							'name' => array(
								SORT_ASC,
								SORT_STRING,
							),
						)
					);
				} else {
					$flag = isset( $_REQUEST['order'] ) && 'desc' === sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ? SORT_DESC : SORT_ASC;
					$list = $this->list_sort( $list, array( 'name' => array( $flag, SORT_STRING ) ) );
				}
			}
			return $list;
		}

		/**
		 * Handles incoming queries
		 *
		 * @return    array     $result    a message about the result of the implementation
		 */
		public function get_result_message() {
			$exclude_actions = array( 'edit', 'update' );
			$result          = array(
				'error'   => '',
				'notice'  => '',
				'message' => '',
			);
			if ( isset( $_POST['action'] ) && '-1' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
				$action = sanitize_text_field( wp_unslash( $_POST['action'] ) );
			} elseif ( isset( $_POST['action2'] ) && '-1' !== sanitize_text_field( wp_unslash( $_POST['action2'] ) ) ) {
				$action = sanitize_text_field( wp_unslash( $_POST['action2'] ) );
			}
			if ( isset( $_REQUEST['srrl_action'] ) && ! in_array( sanitize_text_field( wp_unslash( $_REQUEST['srrl_action'] ) ), $exclude_actions ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['srrl_action'] ) );
			}
			if ( ! empty( $action ) && isset( $_REQUEST['srrl_slug'] ) && ! empty( $_REQUEST['srrl_slug'] ) ) {
				check_admin_referer( 'srrl_nonce_action' );
				if ( 'recover' === $action ) {
					if ( is_array( $_REQUEST['srrl_slug'] ) ) {
						$result = srrl_recover_role( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_REQUEST['srrl_slug'] ) ) );
					} else {
						$result = srrl_recover_role( sanitize_text_field( wp_unslash( $_REQUEST['srrl_slug'] ) ) );
					}
				} else {
					$result['error'] = __( 'Unknown action', 'user-role' );
				}
			}
			return $result;
		}

		/**
		 * Sorting array with roles before displaying
		 *
		 * @param array $data       Array with roles.
		 * @param array $sort_flags Array of flags.
		 * @return array $args Sorted array.
		 */
		public function list_sort( $data, $sort_flags ) {
			$args = array();
			$i    = 0;
			foreach ( $sort_flags as $column => $sort_attr ) {
				$column_lists = array();
				foreach ( $data as $key => $row ) {
					$column_lists[ $column ][ $key ] =
							in_array( SORT_STRING, $sort_attr ) || in_array( SORT_REGULAR, $sort_attr )
						?
							strtolower( $row[ $column ] )
						:
							$row[ $column ];
				}
				$args[] = &$column_lists[ $column ];
				foreach ( $sort_attr as $sort_flag ) {
					$tmp[ $i ] = $sort_flag;
					$args[]    = &$tmp[ $i ];
					$i++;
				}
			}
			$args[] = &$data;
			call_user_func_array( 'array_multisort', $args );
			return end( $args );
		}
	}
} ?>

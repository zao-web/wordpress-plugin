<?php
namespace GatherContent\Importer\Admin;
use GatherContent\Importer\Post_Types\Template_Mappings;
use GatherContent\Importer\API;
use GatherContent\Importer\Admin\Mapping\Base as UI_Base;
use GatherContent\Importer\Admin\Enqueue;

class Bulk_Enqueue extends Enqueue {}

class Bulk extends UI_Base {

	/**
	 * GatherContent\Importer\API instance
	 *
	 * @var GatherContent\Importer\API
	 */
	protected $api = null;

	/**
	 * GatherContent\Importer\Post_Types\Template_Mappings instance
	 *
	 * @var GatherContent\Importer\Post_Types\Template_Mappings
	 */
	protected $mappings = null;

	/**
	 * GatherContent\Importer\Admin\Enqueue instance
	 *
	 * @var GatherContent\Importer\Admin\Enqueue
	 */
	protected $enqueue = null;

	/**
	 * A flag to check if this is an ajax request.
	 *
	 * @var boolean
	 */
	protected $doing_ajax = false;

	/**
	 * Creates an instance of this class.
	 *
	 * @since 3.0.0
	 *
	 * @param $api      API object
	 * @param $mappings Template_Mappings object
	 */
	public function __construct( API $api, Template_Mappings $mappings ) {
		$this->api        = $api;
		$this->mappings   = $mappings;
		$this->enqueue    = new Bulk_Enqueue;
		$this->doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * The page-specific script ID to enqueue.
	 *
	 * @since  3.0.0
	 *
	 * @return string
	 */
	protected function script_id() {
		return 'gathercontent-general';
	}

	/**
	 * Initiate admin hooks
	 *
	 * @since  3.0.0
	 *
	 * @return void
	 */
	public function init_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		$this->post_types = $this->mappings->get_mapping_post_types();

		global $pagenow;
		if (
			$pagenow
			&& ! empty( $this->post_types )
			&& 'edit.php' === $pagenow
			) {
			add_action( 'admin_enqueue_scripts', array( $this, 'ui' ) );
		}

		if ( $this->doing_ajax && ! empty( $this->post_types ) ) {
			foreach ( $this->post_types as $post_type => $mapping_ids ) {
				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'register_column_headers' ), 8 );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'column_display' ), 10, 2 );
			}

		}

		// Handle quick-edit/bulk-edit ajax-post-saving
		add_action( 'save_post', array( $this, 'set_gc_status' ), 10, 2 );
	}

	/**
	 * The Bulk Edit page UI callback.
	 *
	 * @since  3.0.0
	 *
	 * @return void
	 */
	public function ui_page() {
		$screen = get_current_screen();

		if (
			'edit' !== $screen->base
			|| ! $screen->post_type
			// || ! isset( $this->post_types[ $screen->post_type ] )
		) {
			return;
		}

		add_action( 'all_admin_notices', function() {
			echo '<div id="message" class="updated"><p>';

				echo '<xmp>$this->post_types: '. print_r( $this->post_types, true ) .'</xmp>';

			echo '</p></div>';
		} );
		if ( ! isset( $this->post_types[ $screen->post_type ] ) ) {
			return;
		}

		$this->enqueue->admin_enqueue_style();
		$this->enqueue->admin_enqueue_script();

		$this->hook_columns( $screen->post_type );
	}

	public function hook_columns( $post_type ) {
		add_filter( "manage_{$post_type}_posts_columns", array( $this, 'register_column_headers' ), 8 );
		add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'column_display' ), 10, 2 );
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_box' ), 10, 2 );
		add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit_box' ), 10, 2 );
	}

	/**
	 * Register the GC column header.
	 * @since 3.0.0
	 */
	public function register_column_headers( $columns ) {
		$columns['gathercontent'] = '<div title="'. __( 'GatherContent Item Status', 'gathercontent-importer' ) .'" class="gc-column-header"><span class="gc-logo-column"><img src="'. GATHERCONTENT_URL . 'images/logo.svg" alt="GatherContent" /></span>'. _x( 'Status', 'GatherContent Item Status', 'gathercontent-importer' ) .'</div>';

		return $columns;
	}

	/**
	 * The GC field column display output.
	 * @since 3.0.0
	 */
	public function column_display( $column_name, $post_id ) {
		if ( 'gathercontent' !== $column_name ) {
			return;
		}
		// if ( $this->doing_ajax ) {
		// 	return $this->ajax_column_output();
		// }

		$item_id = absint( \GatherContent\Importer\get_post_item_id( $post_id ) );
		$mapping_id = absint( \GatherContent\Importer\get_post_mapping_id( $post_id ) );

		if ( $this->doing_ajax ) {
			return $this->ajax_view( $post_id, $item_id, $mapping_id );
		}

		printf(
			'<span class="gc-status-column" data-id="%d" data-item="%d" data-mapping="%d">&mdash;</span>',
			absint( $post_id ),
			$item_id,
			$mapping_id
		);
	}

	protected function ajax_view( $post_id, $item_id, $mapping_id ) {
		$status_name = $status_color = '';

		if ( $item_id ) {

			$item = $this->api->uncached()->get_item( $item_id );

			if ( isset( $item->status->data ) ) {
				$status_name = $item->status->data->name;
				$status_color = $item->status->data->color;
			}
		}

		$this->view( 'gc-post-column-row', array(
			'post_id'      => $post_id,
			'item_id'      => $item_id,
			'mapping_id'   => $mapping_id,
			'status_name'  => $status_name,
			'status_color' => $status_color,
		) );
	}

	/**
	 * The GC field quick-edit display output.
	 * @since 3.0.0
	 */
	public function quick_edit_box( $column_name, $post_type ) {
		if ( 'gathercontent' !== $column_name ) {
			return;
		}

		?>
		<fieldset class="inline-edit-col-right inline-edit-gc-status">
			<?php wp_nonce_field( __CLASS__, 'gc-edit-nonce' ); ?>
			<div class="inline-edit-col column-<?php echo $column_name; ?>">
				<label class="inline-edit-group">
					<span class="title">GatherContent Status</span>
					<span class="gc-status-select2"><span class="spinner"></span></span>
				</label>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * The GC field bulk-edit display output.
	 * @since 3.0.0
	 */
	public function bulk_edit_box( $column_name, $post_type ) {
		if ( 'gathercontent' !== $column_name ) {
			return;
		}

		?>
		<div class="display:none">
			<?php echo \GatherContent\Importer\refresh_connection_link(); ?>
		</div>
		<p class="submit inline-edit-save">
			<button type="button" class="button gc-button-primary alignright">GatherContent Sync</button>
		</p>
		<?php
	}

	public function set_gc_status( $post_id, $post ) {
		if (
			wp_is_post_autosave( $post )
			|| wp_is_post_revision( $post )
			|| ! $this->_post_val( 'gc-edit-nonce' )
			|| ! wp_verify_nonce( $this->_post_val( 'gc-edit-nonce' ), __CLASS__ )
			|| ! ( $status_id = $this->_post_val( 'gc_status' ) )
			|| ! ( $item_id = absint( \GatherContent\Importer\get_post_item_id( $post_id ) ) )
			|| ! ( $mapping_id = absint( \GatherContent\Importer\get_post_mapping_id( $post_id ) ) )
			|| ! ( $item = $this->api->get_item( $item_id ) )
			|| ( isset( $item->status->data->id ) && $status_id == $item->status->data->id )
		) {
			return;
		}

		if ( isset( $item->status->data->id ) && $status_id == $item->status->data->id ) {
			return;
		}

		$this->api->set_item_status( $item_id, $status_id );
	}

	/**
	 * Gets the underscore templates array.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	protected function get_underscore_templates() {
		return array(
			'tmpl-gc-post-column-row' => array(),
			'tmpl-gc-status-select2' => array(),
			'tmpl-gc-select2-item' => array(),
		);
	}

	/**
	 * Get the localizable data array.
	 *
	 * @since  3.0.0
	 *
	 * @return array Array of localizable data
	 */
	protected function get_localize_data() {
		return array(
			'mapping_post_types' => $this->post_types,
		);
	}

}
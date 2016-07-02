<?php
namespace GatherContent\Importer;

class API extends Base {

	protected $base_url = 'https://api.gathercontent.com/';
	protected $user = '';
	protected $api_key = '';
	protected $only_cached = false;
	protected $reset_request_cache = false;
	protected $disable_cache = false;

	/**
	 * WP_Http instance
	 *
	 * @var WP_Http
	 */
	protected $http;

	/**
	 * Creates an instance of this class.
	 *
	 * @since 3.0.0
	 */
	public function __construct( \WP_Http $http ) {
		parent::__construct();

		$this->http = $http;
		$this->disable_cache = $this->_get_val( 'flush_cache' ) || $this->_post_val( 'flush_cache' );
	}

	public function set_user( $email ) {
		$this->user = $email;
	}

	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * GC API request to get the results from the "/me" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/me/get-me/
	 *
	 * @param  bool $uncached Whether bypass cache when making request.
	 * @return mixed          Results of request.
	 */
	public function get_me( $uncached = false ) {
		if ( $uncached ) {
			$this->reset_request_cache = true;
		}

		return $this->get( 'me' );
	}

	/**
	 * GC API request to get the results from the "/accounts" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/accounts/get-accounts/
	 *
	 * @return mixed Results of request.
	 */
	public function get_accounts() {
		return $this->get( 'accounts' );
	}

	/**
	 * GC API request to get the results from the "/projects?account_id=<ACCOUNT_ID>" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/projects/get-projects/
	 *
	 * @param  int   $account_id Account ID.
	 * @return mixed             Results of request.
	 */
	public function get_account_projects( $account_id ) {
		return $this->get( 'projects?account_id=' . $account_id );
	}

	/**
	 * GC API request to get the results from the "/projects/<PROJECT_ID>" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/projects/get-projects-by-id/
	 *
	 * @param  int   $project_id Project ID.
	 * @return mixed             Results of request.
	 */
	public function get_project( $project_id ) {
		return $this->get( 'projects/' . $project_id );
	}

	/**
	 * GC API request to get the results from the "/projects/<PROJECT_ID>/statuses" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/projects/get-projects-statuses/
	 *
	 * @param  int   $project_id Project ID.
	 * @return mixed             Results of request.
	 */
	public function get_project_statuses( $project_id ) {
		return $this->get( 'projects/' . $project_id .'/statuses' );
	}

	/**
	 * GC API request to get the results from the "/items?project_id=<PROJECT_ID>" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/items/get-items/
	 *
	 * @param  int   $project_id Project ID.
	 * @return mixed             Results of request.
	 */
	public function get_project_items( $project_id ) {
		return $this->get( 'items?project_id=' . $project_id );
	}

	/**
	 * GC API request to get the results from the "/items/<ITEM_ID>" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/items/get-items-by-id/
	 *
	 * @param  int   $item_id Item ID.
	 * @return mixed          Results of request.
	 */
	public function get_item( $item_id ) {
		return $this->get( 'items/'. $item_id );
	}

	/**
	 * GC API request to get the results from the "/items/<ITEM_ID>/files" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/items/get-items-files/
	 *
	 * @param  int   $item_id Item ID.
	 * @return mixed          Results of request.
	 */
	public function get_item_files( $item_id ) {
		return $this->get( 'items/'. $item_id .'/files' );
	}

	/**
	 * GC API request to get the results from the "/templates?project_id=<PROJECT_ID>" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/templates/get-templates/
	 *
	 * @param  int   $project_id Project ID.
	 * @return mixed             Results of request.
	 */
	public function get_project_templates( $project_id ) {
		return $this->get( 'templates?project_id=' . $project_id );
	}

	/**
	 * GC API request to get the results from the "/templates/<PROJECT_ID>" endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/templates/get-templates-by-id/
	 *
	 * @param  int   $template_id Template ID.
	 * @return mixed              Results of request.
	 */
	public function get_template( $template_id ) {
		return $this->get( 'templates/' . $template_id );
	}

	/**
	 * GC API request to set status ID for an item.
	 *
	 * /items/<ITEM_ID>/choose_status
	 *
	 * @since  3.0.0
	 *
	 * @link https://gathercontent.com/developers/items/post-items-choose_status/
	 *
	 * @param  int  $item_id   GatherContent Item Id.
	 * @param  int  $status_id Id of status to set.
	 * @return bool            If request was successful.
	 */
	public function set_item_status( $item_id, $status_id ) {
		$response = $this->post( 'items/'. absint( $item_id ) .'/choose_status', array(
			'body' => array(
				'status_id' => absint( $status_id ),
			),
		) );

		return 202 === $response['response']['code'];
	}

	/**
	 * GC API request to save an item.
	 *
	 * /items/<ITEM_ID>/save
	 *
	 * @since 3.0.0
	 *
	 * @link https://gathercontent.com/developers/items/post-items-by-id/
	 *
	 * @param  int   $item_id GatherContent Item Id.
	 * @param  array $data    Data to save.
	 * @return bool            If request was successful.
	 */
	public function save_item( $item_id, $data ) {
		$response = $this->post( 'items/'. absint( $item_id ) .'/save', array(
			'body' => array(
				'config' => $data,
			),
		) );

		return 202 === $response['response']['code'];
	}

	/**
	 * POST request helper, which assumes a data parameter in response.
	 *
	 * @since  3.0.0
	 *
	 * @see    API::request_cache() For additional information
	 *
	 * @param  string $endpoint GatherContent API endpoint to retrieve.
	 * @param  array  $args     Optional. Request arguments. Default empty array.
	 * @return mixed            The response.
	 */
	public function post( $endpoint, $args = array() ) {
		return $this->request( $endpoint, $args, 'POST' );
	}

	/**
	 * GET request helper which assumes caching, and assumes a data parameter in response.
	 *
	 * @since  3.0.0
	 *
	 * @see    API::request_cache() For additional information
	 *
	 * @param  string $endpoint GatherContent API endpoint to retrieve.
	 * @param  array  $args     Optional. Request arguments. Default empty array.
	 * @return mixed            The response.
	 */
	public function get( $endpoint, $args = array() ) {
		$data = $this->request_cache( $endpoint, DAY_IN_SECONDS, $args, 'GET' );
		if ( isset( $data->data ) ) {
			return $data->data;
		}

		return false;
	}

	/**
	 * Retrieve and cache the HTTP request.
	 *
	 * @since  3.0.0
	 *
	 * @see    API::request() For additional information
	 *
	 * @param  string $endpoint   GatherContent API endpoint to retrieve.
	 * @param  string $expiration The expiration time. Defaults to an hour.
	 * @param  array  $args       Optional. Request arguments. Default empty array.
	 * @param  array  $method     Optional. Request method, defaults to 'GET'.
	 * @return array              The response.
	 */
	public function request_cache( $endpoint, $expiration = HOUR_IN_SECONDS, $args = array(), $method = 'GET' ) {
		$trans_key = 'gctr-' . md5( serialize( compact( 'endpoint', 'args', 'method' ) ) );
		$response = get_transient( $trans_key );

		if ( $this->only_cached ) {
			$this->only_cached = false;
			return $response;
		}

		if ( ! $response || $this->disable_cache || $this->reset_request_cache ) {

			$response = $this->request( $endpoint, $args, $method );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			set_transient( $trans_key, $response, $expiration );

			$keys = get_option( 'gathercontent_transients' );
			$keys = is_array( $keys ) ? $keys : array();
			$keys[ $endpoint ][] = $trans_key;
			update_option( 'gathercontent_transients', $keys, false );

			$this->reset_request_cache = false;
		}

		return $response;
	}

	/**
	 * Retrieve the raw response from the HTTP request.
	 *
	 * Request method defaults for helper functions:
	 *  - Default 'GET'  for wp_remote_get()
	 *  - Default 'POST' for wp_remote_post()
	 *  - Default 'HEAD' for wp_remote_head()
	 *
	 * @since  3.0.0
	 *
	 * @see    WP_Http::request() For additional information on default arguments.
	 *
	 * @param  string $endpoint GatherContent API endpoint to retrieve.
	 * @param  array  $args     Optional. Request arguments. Default empty array.
	 * @param  array  $method   Optional. Request method, defaults to 'GET'.
	 * @return array            The response.
	 */
	public function request( $endpoint, $args = array(), $method = 'GET' ) {
		$uri = $this->base_url . $endpoint;
		$args = $this->request_args( $args );

		error_log( '$uri: '. print_r( add_query_arg( array(
			'disable_cache' => $this->disable_cache,
			'reset_request_cache' => $this->reset_request_cache,
		), $uri ), true ) );
		error_log( '$args: '. print_r( $args, true ) );

		$response = $this->http->{strtolower( $method )}( $uri, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code     = $response['response']['code'];
		$success  = $code >= 200 && $code < 300;

		if ( 'GET' === $method ) {
			return $success ? json_decode( wp_remote_retrieve_body( $response ) ) : $response;
		}

		return $response;
	}

	/**
	 * Prepares headers for GC requests.
	 *
	 * @since  3.0.0
	 *
	 * @param  array  $args Array of request args.
	 *
	 * @return array        Modified array of request args.
	 */
	public function request_args( $args ) {
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $this->user . ':' . $this->api_key ),
			'Accept'        => 'application/vnd.gathercontent.v0.5+json',
		);

		$args['headers'] = isset( $args['headers'] )
			? wp_parse_args( $args['headers'], $headers )
			: $headers;

		return $args;
	}

	/**
	 * Sets the only_cached flag and returns object, for chaining methods,
	 * and only gets results from cache (doesn't make actual request).
	 *
	 * e.g. `$this->only_cached()->get( 'me' )`
	 *
	 * @since  3.0.0
	 *
	 * @return $this
	 */
	public function only_cached() {
		$this->only_cached = true;
		return $this;
	}

	/**
	 * Sets the reset_request_cache flag and returns object, for chaining methods,
	 * and flushing/bypassing cache for next request.
	 *
	 * e.g. `$this->uncached()->get( 'me' )`
	 *
	 * @since  3.0.0
	 *
	 * @return $this
	 */
	public function uncached() {
		$this->reset_request_cache = true;
		return $this;
	}

	/**
	 * Flush all cached responses, or only for a given endpoint.
	 *
	 * @since  3.0.0
	 *
	 * @param  string $endpoint Optional endpoint to clear cached response.
	 *
	 * @return bool             Status of cache flush/deletion.
	 */
	public function flush_cache( $endpoint = '' ) {
		$deleted = false;
		$keys = get_option( 'gathercontent_transients' );
		$keys = is_array( $keys ) ? $keys : array();

		if ( $endpoint ) {
			if ( isset( $keys[ $endpoint ] ) ) {
				foreach ( $keys[ $endpoint ] as $transient ) {
					delete_transient( $transient );
				}

				unset( $keys[ $endpoint ] );
				$deleted = true;
			}
		} else {
			foreach ( $keys as $endpoint => $transients ) {
				foreach ( $transients as $transient ) {
					delete_transient( $transient );
				}
			}

			$keys = array();
			$deleted = true;
		}

		update_option( 'gathercontent_transients', $keys, false );

		return $deleted;
	}

}

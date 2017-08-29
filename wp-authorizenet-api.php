<?php
/**
 * Library for accessing the Authorize.net API on WordPress
 *
 * @link http://developer.authorize.net/api/ API Documentation
 * @package WP-API-Libraries\WP-Authorizenet-API
 */

/*
 * Plugin Name: Authorize.net API
 * Plugin URI: https://wp-api-libraries.com/
 * Description: Perform API requests.
 * Author: WP API Libraries
 * Version: 1.0.0
 * Author URI: https://wp-api-libraries.com
 * GitHub Plugin URI: https://github.com/imforza
 * GitHub Branch: master
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WPAuthorizeNetAPI' ) ) {

	/**
	 * A WordPress API library for accessing the Cloudflare API.
	 *
	 * @version 1.1.0
	 * @link http://developer.authorize.net/api/ API Documentation
	 * @package WP-API-Libraries\WP-Authorizenet-API
	 * @author Santiago Garza <https://github.com/sfgarza>
	 * @author imFORZA <https://github.com/imforza>
	 */
	class WPAuthorizeNetAPI {

		/**
		 * API Key.
		 *
		 * @var string
		 */
		static protected $api_key;

		/**
		 * Auth Email
		 *
		 * @var string
		 */
		static protected $auth_email;

		/**
		 * User Service Key
		 *
		 * @var string
		 */
		static protected $user_service_key;

		/**
		 * CloudFlare BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://api.authorize.net/xml/v1/request.api';

		protected $sandbox_uri = 'https://apitest.authorize.net/xml/v1/request.api';

		protected $xsd_uri = 'https://api.authorize.net/xml/v1/schema/AnetApiSchema.xsd';

		/**
		 * Route being called.
		 *
		 * @var string
		 */
		protected $route = '';


		/**
		 * Class constructor.
		 *
		 * @param string $api_key               Cloudflare API Key.
		 * @param string $auth_email            Email associated to the account.
		 * @param string $user_service_key      User Service key.
		 */
		public function __construct( $api_key, $auth_email, $user_service_key = '' ) {
			static::$api_key = $api_key;
			static::$auth_email = $auth_email;
			static::$user_service_key = $user_service_key;
		}

		/**
		 * Prepares API request.
		 *
		 * @param  string $route   API route to make the call to.
		 * @param  array  $args    Arguments to pass into the API call.
		 * @param  array  $method  HTTP Method to use for request.
		 * @return self            Returns an instance of itself so it can be chained to the fetch method.
		 */
		protected function build_request( $route, $args = array(), $method = 'GET' ) {
			// Start building query.
			$this->set_headers();
			$this->args['method'] = $method;
			$this->route = $route;

			// Generate query string for GET requests.
			if ( 'GET' === $method ) {
				$this->route = add_query_arg( array_filter( $args ), $route );
			} elseif ( 'application/json' === $this->args['headers']['Content-Type'] ) {
				$this->args['body'] = wp_json_encode( $args );
			} else {
				$this->args['body'] = $args;
			}

			return $this;
		}


		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @return array|WP_Error Request results or WP_Error on request failure.
		 */
		protected function fetch() {
			// Make the request.
			$response = wp_remote_request( $this->base_uri . $this->route, $this->args );

			// Retrieve Status code & body.
			$code = wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ) );

			$this->clear();
			// Return WP_Error if request is not successful.
			if ( ! $this->is_status_ok( $code ) ) {
				return new WP_Error( 'response-error', sprintf( __( 'Status: %d', 'wp-postmark-api' ), $code ), $body );
			}

			return $body;
		}


		/**
		 * Set request headers.
		 */
		protected function set_headers() {
			// Set request headers.
			$this->args['headers'] = array(
					'Content-Type' => 'application/json',
					'X-Auth-Email' => static::$auth_email,
					'X-Auth-Key' => static::$api_key,
			);
		}

		/**
		 * Clear query data.
		 */
		protected function clear() {
			$this->args = array();
			$this->query_args = array();
		}

		/**
		 * Check if HTTP status code is a success.
		 *
		 * @param  int $code HTTP status code.
		 * @return boolean       True if status is within valid range.
		 */
		protected function is_status_ok( $code ) {
			return ( 200 <= $code && 300 > $code );
		}


		/**
		 * Get Customer Profile IDs
		 *
		 * Use this function to retrieve all existing customer profile IDs.
		 *
		 * @api POST
		 * @see http://developer.authorize.net/api/reference/index.html#customer-profiles-get-customer-profile-ids Documentation.
		 * @access public
		 * @return array  API response.
		 */
		public function get_customer_profile_ids() {
			return $this->build_request( 'user' )->fetch();
		}

	} // End Class

} // End If

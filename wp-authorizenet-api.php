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
		static protected $api_login_id;

		/**
		 * Auth Email
		 *
		 * @var string
		 */
		static protected $transaction_key;

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
		 * Action being called.
		 *
		 * @var string
		 */
		protected $action;

		protected $use_sandbox;

		/**
		 * Class constructor.
		 *
		 * @param string $api_login_id     API Login ID.
		 * @param string $transaction_key  Transaction Key
		 */
		public function __construct( $api_login_id, $transaction_key, bool $use_sandbox = false ) {
			static::$api_login_id = $api_login_id;
			static::$transaction_key = $transaction_key;
			static::$use_sandbox = $use_sandbox;
		}

		/**
		 * Prepares API request.
		 *
		 * @param  string $action   API action to make the call to.
		 * @param  array  $args    Arguments to pass into the API call.
		 * @param  array  $method  HTTP Method to use for request.
		 * @return self            Returns an instance of itself so it can be chained to the fetch method.
		 */
		protected function build_request( $action, $args = array() ) {
			// Start building query.
			$this->args['headers'] = array(
					'Content-Type' => 'application/json',
					'X-Auth-Email' => static::$transaction_key,
					'X-Auth-Key' => static::$api_login_id,
			);

			$this->args['method'] = 'POST';

			$this->action = $action;

			$this->args['body'] = wp_json_encode( $args );

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
			$response = wp_remote_request( $this->base_uri, $this->args );

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
		 * Clear query data.
		 */
		protected function clear() {
			$this->args = array();
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
			return $this->build_request( 'getCustomerProfileIdsRequest' )->fetch();
		}

	} // End Class

} // End If

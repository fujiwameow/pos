<?php
/**
 * Its used for recaptcha
 *
 * @since: 21/09/2021
 * @author: Appsbd
 * @version 1.0.0
 * @package VitePos_Lite\Libs
 */

namespace VitePos\Libs;

if ( ! class_exists( __NAMESPACE__ . '\Recaptcha' ) ) {
	/**
	 * Class Recaptcha
	 *
	 * @package VitePos_Lite\Libs
	 */
	class Recaptcha {
		/**
		 * The isValid is generated by appsbd
		 *
		 * @param mixed  $token Its recaptcha token.
		 * @param string $secret Its recaptcha secret.
		 *
		 * @return bool
		 */
		public static function is_valid( $token, $secret = '' ) {
			if ( empty( $secret ) || empty( $token ) ) {
				return false;
			}
			try {
				$response = wp_remote_get(
					add_query_arg(
						array(
							'secret'   => $secret,
							'response' => $token,
						),
						'https://www.google.com/recaptcha/api/siteverify'
					)
				);

				if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
					$json = json_decode( $response['body'] );
					if ( ! empty( $json ) && $json->success ) {
						return true;
					}
				}
				return false;
			} catch ( Exception $e ) {
				return false;
			}

		}
	}
}

<?php
/*
 * SPID-Wordpress - Plugin che connette Wordpress e SPID
 * Copyright (C) 2017 Ludovico Pavesi, Valerio Bozzolan, spid-wordpress contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * The login-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Spid_Wordpress
 * @subpackage Spid_Wordpress/login
 * @author     Ludovico Pavesi, Valerio Bozzolan, spid-wordpress contributors
 */
class Spid_Wordpress_Login {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The ID of this version plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The ID of this plugin version.
	 */
	private $version;

	/**
	 * Another spawned settings from hell (TODO, to it well).
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $settings;

	/**
	 * More hellish nightmare fuel
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $user_meta;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->settings    = new Spid_Wordpress_Settings( $plugin_name );
		$this->user_meta   = new Spid_Wordpress_User_Meta( $plugin_name, $version );
	}

	/**
	 * Register the stylesheets for the login area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/spid-wordpress-login.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the login area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/spid-wordpress-login.js', array( 'jquery' ), $this->version, false );
	}

	public function login_form() {
		echo "SPID è una tecnologia subliminalmente eccezionale, transumanante, asd. SPID non è una backdoor. SPID is love. SPID is life. Se vedi questo messaggio, SPID è in te.";
	}

	/**
	 * In a message box.
	 *
	 * Called also for action=lostpassword.
	 *
	 * @param string $deafult Default message
	 *
	 * @return string
	 */
	public function login_errors( $default ) {
		return $default;
	}

	/**
	 * Not in message box.
	 *
	 * @param string $default Default login message
	 *
	 * @return string
	 */
	public function login_message( $default ) {
		return $default;
	}

	/**
	 * Never called.
	 */
	public function login_successful() {
		echo "SPID login eseguito asd tutto bene presa bn pija bns";
		die( "login_successful() fired?" );
	}

	/**
	 * Programmatically logs a user in.
	 *
	 * @param string $username the WORDPRESS, NOT SPID, username
	 *
	 * @return bool True if the login was successful; false if it wasn't
	 * @throws Exception if SPID login disabled
	 * @see https://wordpress.stackexchange.com/a/156431
	 */
	function bypass_login( $username ) {
		$user = get_user_by( 'login', $username );

		if ( ! $user ) {
			// TODO: remove and controllare a monte
			throw new Exception( 'User not found (this should never happen)' );
		}

		if ( ! $this->settings->get_option_value( Spid_Wordpress_Settings::USER_SECURITY_CHOICE ) && $this->user_meta->get_user_has_disabled_spid( $user->ID ) ) {
			throw new Exception( "SPID login disabled by user" );
		}

		if ( is_user_logged_in() ) {
			wp_logout();
		}

		$filter = array( __CLASS__, 'short_circuit_auth' );

		// Hook in earlier than other callbacks to short-circuit them
		add_filter( 'authenticate', $filter, 10, 3 );

		// Login the user with the previous registered hook
		$user = wp_signon( array( 'user_login' => $username ) );

		// Unregister the previously registered fake authentication hook
		// Secret undocumented parameters found in OpenID plugin or something
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		remove_filter( 'authenticate', $filter, 10, 3 );

		if ( is_a( $user, 'WP_User' ) ) {
			wp_set_current_user( $user->ID, $user->user_login );

			if ( is_user_logged_in() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * An 'authenticate' filter callback that authenticates the user using only the username.
	 *
	 * To avoid potential security vulnerabilities, this should only be used in the context of a programmatic login,
	 * and unhooked immediately after it fires.
	 *
	 * @param WP_User $user
	 * @param string $username
	 * @param string $password
	 *
	 * @return bool|WP_User a WP_User object if the username matched an existing user, or false if it didn't
	 */
	static function short_circuit_auth( $user, $username, $password ) {
		// Support also ' email'
		return get_user_by( 'login', $username );
	}

	/**
	 * Replace the default authentication method.
	 *
	 * Mike, CC BY-SA 40
	 * https://wordpress.stackexchange.com/a/156431
	 */
	public function authenticate() {
		// TODO: remove, probably (SPID needs authentication in init)
	}

}

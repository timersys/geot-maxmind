<?php

use GeotWP\GeotargetingWP;
use GeotCore\GeotUpdates;

/**
 * Fired during plugin updating
 *
 * @link       https://geotargetingwp.com/geotargeting-pro
 * @since      1.0.0
 *
 * @package    GeoTarget
 * @subpackage GeoTarget/includes
 */

/**
 * Fired during plugin updating.
 *
 * This class defines all code necessary to run during the plugin's updating.
 *
 * @since      1.0.0
 * @package    GeoTarget
 * @subpackage GeoTarget/includes
 * @author     Your Name <email@example.com>
 */
class GeotMaxmind_Updater {

	public function __construct() {

		// License and Updates
		add_action( 'admin_init', [ $this, 'handle_updates' ], 0 );
	}

	/**
	 * Handle Licences and updates
	 * @since 1.0.0
	 */
	public function handle_updates() {

		if( ! class_exists( 'GeotUpdates' ) || ! function_exists('geot_settings') )
			return false;

		$opts = geot_settings();

		// Setup the updater
		$GeoUpdate = new GeotUpdates( GEOT_MAXIND_FILE, [
				'version' => GEOT_MAXIND_VERSION,
				'license' => isset( $opts['license'] ) ? $opts['license'] : '',
			]
		);

		$this->apply_upgrade();

		return true;
	}


	protected function apply_upgrade() {

		do_action( 'geotWP/upgraded' );

		update_option( 'geot_maxmind_version', GEOT_MAXIND_VERSION );
	}
}
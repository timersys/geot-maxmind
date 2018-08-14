<?php
/**
 * Register Cron.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    GeoTarget
 * @subpackage GeoTarget/includes
 */
class GeotMaxmindCron {

	/**
	 * Construct
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array(self::class,'register_schedules'), 10, 1);
	}

	static function register_schedules($schedules) {
		$schedules['every_2_months'] = array(
			'interval' => 2 * MONTH_IN_SECONDS,
			'display' => __( 'Every 2 Months', 'geot' )
		);

		return $schedules;
	}

}

<?php
class GeotMaxmind {

	protected $cron;
	private static $url_maxmind_db;
	private static $path_maxmind_geo;

	public function __construct() {
		add_action( 'geo_maxmind_cron', array(self::class,'maybe_download_maxmind'));

		$this->load_dependencies();
	}

	private function load_dependencies() {
		require_once GEOT_MAXMIND_DIR . 'includes/class-geot-maxmind-cron.php';

		$this->cron = new GeotMaxmindCron();
	}


	/**
	 * Init Path
	 * @return
	 */
	protected static function set_paths() {
		$path_upload = wp_upload_dir();

		self::$url_maxmind_db = apply_filters('geomax/url_external','http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz');

		self::$path_maxmind_geo = apply_filters('geomax/path_local',$path_upload['basedir'].'/geot_plugin/GeoLite2-City.mmdb');
	}

	/**
	 * Create subfolder
	 * @return
	 */
	protected static function create_subfolder() {
		if( !file_exists( dirname(self::$path_maxmind_geo) ) )
			@wp_mkdir_p( dirname(self::$path_maxmind_geo) );
	}

	/**
	 * Maybe Download Maxmind Database
	 * @return
	 */
	public static function maybe_download_maxmind() {
		self::set_paths();

		if( !file_exists(self::$path_maxmind_geo) || defined( 'DOING_CRON' ) ) {
			self::create_subfolder();
			self::download_maxmind_db();
		}
	}

	/**
	 * Download Maxmind Database
	 * @return
	 */
	public static function download_maxmind_db() {

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$tmp_database_path = download_url( self::$url_maxmind_db );

		if ( ! is_wp_error( $tmp_database_path ) ) {
			
			try {
				$upload_dir = wp_upload_dir();
				$database = wp_basename(self::$path_maxmind_geo);
				$dest_path = trailingslashit( dirname(self::$path_maxmind_geo) ) . $database;

				update_option('optest5',print_r($dest_path,true));
				
				// Extract files with PharData. Tool built into PHP since 5.3.
				$file      = new PharData( $tmp_database_path ); // phpcs:ignore PHPCompatibility.PHP.NewClasses.phardataFound

				$file_path = trailingslashit( $file->current()->getFileName() ) . $database;
	
				// Extract under uploads directory.
				$file->extractTo( $upload_dir['basedir'], $file_path, true );

				// Remove old database.
				@unlink( $dest_path ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.VIP.FileSystemWritesDisallow.file_ops_unlink

				// Copy database and delete tmp directories.
				@rename( trailingslashit( $upload_dir['basedir'] ) . $file_path, $dest_path ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.VIP.FileSystemWritesDisallow.file_ops_rename

				@rmdir( trailingslashit( $upload_dir['basedir'] ) . $file->current()->getFileName() ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.VIP.FileSystemWritesDisallow.directory_rmdir
				// Set correct file permission.

				@chmod( $dest_path, 0644 ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.VIP.FileSystemWritesDisallow.chmod_chmod

			} catch ( Exception $e ) {
				print_r( $e->getMessage() );
			}

			@unlink( $tmp_database_path ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.VIP.FileSystemWritesDisallow.file_ops_unlink
		}
	}
}
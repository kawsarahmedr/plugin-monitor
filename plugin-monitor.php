<?php
/**
 * Plugin Name:       Plugin Monitor
 * Plugin URI:        https://urldev.com/plugin-monitor
 * Description:       Monitor WordPress.org plugins and view update information via the plugins_api function.
 * Version:           1.0.0
 * Author:            UrlDev
 * Author URI:        https://urldev.com
 * License: GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       plugin-monitor
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Requires PHP:      7.4
 *
 * @package PluginMonitor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Monitor Class
 *
 * This class handles the admin menu, settings, and cron job for monitoring plugins.
 */
class PluginMonitor {

	/**
	 * Option key for storing plugin slugs.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'plugin_monitor_slugs';

	/**
	 * Constructor to initialize the plugin.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'maybe_schedule_cron' ) );
		add_action( 'plugin_monitor_fetch_data', array( $this, 'fetch_plugin_data_cron' ) );
	}

	/**
	 * Add admin menu page for the plugin.
	 *
	 * This function creates a new menu item in the WordPress admin dashboard
	 * where users can enter plugin slugs and view their data.
	 */
	public function add_admin_menu() {
		add_menu_page(
			'Plugin Monitor',
			'Plugin Monitor',
			'manage_options',
			'plugin-monitor',
			array( $this, 'render_admin_page' ),
			'dashicons-visibility',
			65
		);
	}

	/**
	 * Render the admin page for the plugin monitor.
	 *
	 * This function displays the form for entering plugin slugs and shows the
	 * fetched plugin data.
	 */
	public function render_admin_page() {
		if ( isset( $_POST['plugin_monitor_slugs'] ) && check_admin_referer( 'plugin_monitor_save' ) ) {
			update_option( self::OPTION_KEY, sanitize_text_field( $_POST['plugin_monitor_slugs'] ) );
			$this->fetch_plugin_data_cron(); // fetch immediately on save.
		}

		$plugin_slugs = get_option( self::OPTION_KEY, '' );
		$slugs        = array_filter( array_map( 'trim', explode( ',', $plugin_slugs ) ) );
		$plugin_data  = get_transient( 'plugin_monitor_data' );
		?>

		<div class="wrap">
			<h1>Plugin Monitor</h1>
			<form method="post">
				<?php wp_nonce_field( 'plugin_monitor_save' ); ?>
				<label for="plugin_monitor_slugs">Enter plugin slugs (comma separated):</label>
				<input type="text" name="plugin_monitor_slugs" id="plugin_monitor_slugs" value="<?php echo esc_attr( $plugin_slugs ); ?>" style="width: 100%;" />
				<p><input type="submit" class="button button-primary" value="Save & Refresh Data"></p>
			</form>

			<hr>

			<?php
			if ( ! empty( $plugin_data ) ) {
				$total_active_installs = 0;
				$total_ratings         = 0;
				$total_downloads       = 0;

				foreach ( $plugin_data as $info ) {
					$total_active_installs += intval( $info->active_installs ?? 0 );
					$total_ratings         += intval( $info->num_ratings ?? 0 );
					$total_downloads       += intval( $info->downloaded ?? 0 );
				}

				// Summary Card.
				echo '<div style="padding:20px;margin-bottom:20px;background:#f8f9fa;border-left:5px solid #00a0d2;">';
				echo '<h2>Plugin Summary</h2>';
				echo '<p><strong>Total Active Installations:</strong> ' . number_format_i18n( $total_active_installs ) . '</p>';
				echo '<p><strong>Total Ratings:</strong> ' . number_format_i18n( $total_ratings ) . '</p>';
				echo '<p><strong>Total Downloads:</strong> ' . number_format_i18n( $total_downloads ) . '</p>';
				echo '</div>';

				// Display each plugin's data
                echo '<div style="display: flex; margin-bottom: 10px; flex-wrap: wrap;gap: 20px;">';
				foreach ( $plugin_data as $slug => $info ) {
					if ( isset( $info->name ) ) {
						echo '<div style="padding:15px;border:1px solid #ccc;border-left:5px solid #0073aa;background:#fff;width: calc(33.333% - 20px); box-sizing: border-box;">';
						echo '<h2>' . esc_html( $info->name ) . ' <small>(' . esc_html( $slug ) . ')</small></h2>';
						echo '<p><strong>Version:</strong> ' . esc_html( $info->version ) . '</p>';
						echo '<p><strong>Active Installations:</strong> ' . esc_html( number_format_i18n( $info->active_installs ) ) . '</p>';
						echo '<p><strong>Rating:</strong> ' . esc_html( $info->rating ) . '% (' . esc_html( $info->num_ratings ) . ' ratings)</p>';
						echo '<p><strong>Last Updated:</strong> ' . esc_html( date( 'F j, Y', strtotime( $info->last_updated ) ) ) . '</p>';
						echo '<p><a href="' . esc_url( $info->homepage ?? $info->plugin_url ?? '#' ) . '" target="_blank" class="button">View Plugin</a></p>';
						echo '</div>';
					}
				}
				echo '</div>';
			} else {
				echo '<p>No plugin data found. Enter slugs and click "Save & Refresh Data".</p>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Schedule a cron job to fetch plugin data.
	 *
	 * This function checks if the cron job is already scheduled and schedules it
	 * if not. The cron job will run every hour to fetch plugin data.
	 */
	public function maybe_schedule_cron() {
		if ( ! wp_next_scheduled( 'plugin_monitor_fetch_data' ) ) {
			wp_schedule_event( time(), 'hourly', 'plugin_monitor_fetch_data' );
		}
	}

	/**
	 * Fetch plugin data via the WordPress.org API.
	 *
	 * This function retrieves plugin information for the slugs stored in the
	 * plugin options and saves it as a transient for later use.
	 */
	public function fetch_plugin_data_cron() {
		$plugin_slugs = get_option( self::OPTION_KEY, '' );
		$slugs        = array_filter( array_map( 'trim', explode( ',', $plugin_slugs ) ) );

		if ( empty( $slugs ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		$data = array();

		foreach ( $slugs as $slug ) {
			$response = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array(
						'short_description' => false,
						'downloaded'        => true,
						// 'active_installs'   => true,
						// 'last_updated'      => true,

					),
				)
			);
			if ( ! is_wp_error( $response ) ) {
				$data[ $slug ] = $response;
			}
			usleep( 100000 ); // 0.1s to be kind to wp.org
		}

		set_transient( 'plugin_monitor_data', $data, DAY_IN_SECONDS );
	}
}

new PluginMonitor();

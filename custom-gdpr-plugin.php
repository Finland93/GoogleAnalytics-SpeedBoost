<?php
/*
Plugin Name: PopUp for Google Analytics
Plugin URI: https://github.com/Finland93/GoogleAnalytics-SpeedBoost
Description: Defers Google Analytics until the visitor consents, improving load times. Shows a consent popup, loads GA only on "Accept", and supports withdrawing consent. No jQuery.
Version: 2.0.0
Author: Finland93
Author URI: https://github.com/Finland93
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ga-speedboost
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GASB_VERSION', '2.0.0' );
define( 'GASB_FILE', __FILE__ );
define( 'GASB_URL', plugin_dir_url( __FILE__ ) );

final class GA_SpeedBoost {

	const COOKIE = 'AllowAnalytics';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_popup' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_shortcode( 'ga_consent_reset', array( $this, 'shortcode_reset' ) );

		register_uninstall_hook( GASB_FILE, array( __CLASS__, 'uninstall' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'ga-speedboost', false, dirname( plugin_basename( GASB_FILE ) ) . '/languages' );
	}

	private function ga_code() {
		return trim( (string) get_option( 'google_analytics_code', '' ) );
	}

	/* ---------------------------------------------------------------------
	 * Front-end
	 * ------------------------------------------------------------------- */

	public function frontend_assets() {
		// Nothing to consent to without a GA ID — don't load anything.
		if ( '' === $this->ga_code() ) {
			return;
		}

		wp_enqueue_style( 'gasb-popup', GASB_URL . 'css/custom-gdpr-popup.css', array(), GASB_VERSION );

		// No jQuery dependency — the script is plain vanilla JS.
		wp_enqueue_script( 'gasb-popup', GASB_URL . 'js/custom-gdpr-popup.js', array(), GASB_VERSION, true );

		wp_localize_script(
			'gasb-popup',
			'customGdprPopup',
			array(
				'analyticsScript' => $this->ga_code(),
				'cookieName'      => self::COOKIE,
				'cookieDays'      => 365,
			)
		);
	}

	public function render_popup() {
		if ( '' === $this->ga_code() ) {
			return;
		}

		$text   = get_option( 'gdpr_popup_text', __( 'Our website uses cookies', 'ga-speedboost' ) );
		$accept = get_option( 'gdpr_accept_text', __( 'Accept', 'ga-speedboost' ) );
		$reject = get_option( 'gdpr_reject_text', __( 'Reject', 'ga-speedboost' ) );
		?>
		<div id="gdpr-popup" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Cookie consent', 'ga-speedboost' ); ?>">
			<div>
				<p><?php echo esc_html( $text ); ?></p>
				<button type="button" id="accept-btn"><?php echo esc_html( $accept ); ?></button>
				<button type="button" id="reject-btn"><?php echo esc_html( $reject ); ?></button>
			</div>
		</div>
		<?php
	}

	/** [ga_consent_reset text="Manage cookies"] — link to withdraw consent. */
	public function shortcode_reset( $atts ) {
		$atts = shortcode_atts( array( 'text' => __( 'Manage cookie consent', 'ga-speedboost' ) ), $atts, 'ga_consent_reset' );
		return '<a href="#" class="ga-consent-reset">' . esc_html( $atts['text'] ) . '</a>';
	}

	/* ---------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------- */

	public function admin_menu() {
		add_menu_page(
			__( 'GDPR Popup Settings', 'ga-speedboost' ),
			__( 'GDPR Popup', 'ga-speedboost' ),
			'manage_options',
			'gasb-popup',
			array( $this, 'render_settings_page' ),
			'dashicons-chart-bar'
		);
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['gdpr_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gdpr_settings_nonce'] ) ), 'gdpr_settings' ) ) {
			update_option( 'gdpr_popup_text', sanitize_text_field( wp_unslash( $_POST['gdpr_popup_text'] ?? '' ) ) );
			update_option( 'gdpr_accept_text', sanitize_text_field( wp_unslash( $_POST['gdpr_accept_text'] ?? '' ) ) );
			update_option( 'gdpr_reject_text', sanitize_text_field( wp_unslash( $_POST['gdpr_reject_text'] ?? '' ) ) );
			// Measurement IDs are letters, digits and hyphens (G-…, UA-…, GTM-…).
			$code = preg_replace( '/[^A-Za-z0-9\-]/', '', wp_unslash( $_POST['google_analytics_code'] ?? '' ) );
			update_option( 'google_analytics_code', $code );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'ga-speedboost' ) . '</p></div>';
		}

		$popup_text = get_option( 'gdpr_popup_text', __( 'Our website uses cookies', 'ga-speedboost' ) );
		$accept     = get_option( 'gdpr_accept_text', __( 'Accept', 'ga-speedboost' ) );
		$reject     = get_option( 'gdpr_reject_text', __( 'Reject', 'ga-speedboost' ) );
		$code       = $this->ga_code();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'GDPR Popup Settings', 'ga-speedboost' ); ?></h1>
			<?php if ( '' === $code ) : ?>
				<div class="notice notice-warning inline"><p><?php esc_html_e( 'Enter a Google Analytics Measurement ID below to activate the popup.', 'ga-speedboost' ); ?></p></div>
			<?php endif; ?>
			<form method="post" action="">
				<?php wp_nonce_field( 'gdpr_settings', 'gdpr_settings_nonce' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Popup text', 'ga-speedboost' ); ?></th>
						<td><input type="text" name="gdpr_popup_text" value="<?php echo esc_attr( $popup_text ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Accept button text', 'ga-speedboost' ); ?></th>
						<td><input type="text" name="gdpr_accept_text" value="<?php echo esc_attr( $accept ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Reject button text', 'ga-speedboost' ); ?></th>
						<td><input type="text" name="gdpr_reject_text" value="<?php echo esc_attr( $reject ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Google Analytics Measurement ID', 'ga-speedboost' ); ?></th>
						<td>
							<input type="text" name="google_analytics_code" value="<?php echo esc_attr( $code ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
							<p class="description"><?php esc_html_e( 'Analytics is loaded only after the visitor clicks Accept.', 'ga-speedboost' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<hr>
			<p class="description"><?php
				printf(
					/* translators: %s: shortcode */
					esc_html__( 'Let visitors withdraw consent anywhere with the %s shortcode.', 'ga-speedboost' ),
					'<code>[ga_consent_reset]</code>'
				);
			?></p>
		</div>
		<?php
	}

	public static function uninstall() {
		delete_option( 'gdpr_popup_text' );
		delete_option( 'gdpr_accept_text' );
		delete_option( 'gdpr_reject_text' );
		delete_option( 'google_analytics_code' );
	}
}

GA_SpeedBoost::instance();

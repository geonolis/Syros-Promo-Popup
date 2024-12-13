<?php
/**
 * Plugin Name: Syros Popup Message on URL trigger
 * Description: Displays a nice popup message if the URL contains a specific word.
 * Version: 1.0
 * Author: GeoNolis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SyrosPromoPopup {
	private string $message_option_name = 'syros_promo_popup_message';
	private string $word_option_name = 'syros_promo_popup_word';
	private string $specific_word;
	private string $popup_shortcode;
	private string $css_file_path;
	private string $js_file_path;

	public function __construct() {
		// Set file paths
		$this->css_file_path = plugin_dir_url( __FILE__ ) . 'includes/popup-styles.css';
		$this->js_file_path  = plugin_dir_url( __FILE__ ) . 'includes/popup-script.js';

		// Fetch settings once
		$this->specific_word   = get_option( $this->word_option_name, 'promo' );
		$this->popup_shortcode = get_option( $this->message_option_name, 'Default promotional message.' );

		// Hook into WordPress
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_footer', [ $this, 'render_popup_html' ] );
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Checks if the current URL contains the specific word.
	 *
	 * @return bool
	 */
	function should_load_promo_popup(): bool {
		// Check if the specific word exists in the current URL.
		if ( isset( $_SERVER['REQUEST_URI'] ) && str_contains( $_SERVER['REQUEST_URI'], $this->specific_word ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Enqueues the CSS and JS files if the condition is met.
	 */
	function enqueue_assets() {
		if ( $this->should_load_promo_popup() ) {
			wp_enqueue_style( 'promo-popup-style', $this->css_file_path, array(), '1.0' );
			wp_enqueue_script( 'promo-popup-script', $this->js_file_path, array(), '1.0', true );
		}
	}

	/**
	 * Renders the popup HTML if the condition is met.
	 */
	function render_popup_html() {
		do_action( 'qm/debug', get_option( $this->word_option_name, 'promo' ) );
		if ( $this->should_load_promo_popup() ) {
			$popup_html = '
			<div id="syros-promo-popup">
    			<div class="promo-content">
        			<span id="close-syros-promo-popup">&times;</span>
        			' . wp_kses_post( do_shortcode( $this->popup_shortcode ) ) . '
    			</div>
			</div>
			';
			// Output the popup HTML,
			echo $popup_html;
		}
	}


	/**
	 * Adds the settings page to the WordPress admin menu.
	 */
	public function add_settings_page() {
		add_options_page(
			'Promo Popup Settings',
			'Promo Popup',
			'manage_options',
			'promo-popup-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Registers the settings fields for the popup.
	 */
	public function register_settings() {
		// Register options
		register_setting( 'promo_popup_settings', $this->message_option_name );
		register_setting( 'promo_popup_settings', $this->word_option_name );

		// Add settings section
		add_settings_section(
			'promo_popup_section',
			'Popup Settings',
			null,
			'promo-popup-settings'
		);

		// Add settings fields
		add_settings_field(
			$this->message_option_name,
			'Popup Message',
			[ $this, 'render_message_field' ],
			'promo-popup-settings',
			'promo_popup_section'
		);

		add_settings_field(
			$this->word_option_name,
			'Promo Word to find in URL',
			[ $this, 'render_word_field' ],
			'promo-popup-settings',
			'promo_popup_section'
		);
	}

	/**
	 * Renders the popup message settings field.
	 */
	public function render_message_field() {
		$value = get_option( $this->message_option_name, 'Default promotional message.' );

		// Display the WordPress WYSIWYG editor
		wp_editor(
			$value, // The current value of the option
			$this->message_option_name, // HTML ID and name for the editor
			array(
				'textarea_name' => $this->message_option_name, // Name for the <textarea>
				'media_buttons' => false, // Hide the "Add Media" button
				'textarea_rows' => 10, // Number of rows in the editor
				'teeny'         => true, // Use a simplified editor
			)
		);
		echo '<p class="description">Short Codes supported.</p>';
	}

	/**
	 * Renders the promo word settings field.
	 */
	public function render_word_field() {
		$value = get_option( $this->word_option_name, 'promo' );
		echo '<input type="text" name="' . esc_attr( $this->word_option_name ) . '" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">Specify the word to check in the URL (e.g., "promo").</p>';
	}

	/**
	 * Renders the settings page.
	 */
	public function render_settings_page() {
		?>
        <div class="wrap">
            <h1>Promo Popup Settings</h1>
            <form method="post" action="options.php">
				<?php
				settings_fields( 'promo_popup_settings' );
				do_settings_sections( 'promo-popup-settings' );
				submit_button();
				?>
            </form>
        </div>
		<?php
	}
}


// Instantiate the class
new SyrosPromoPopup();
<?php

class CCF_Settings {

	/**
	 * Placeholder method
	 *
	 * @since 7.2
	 */
	public function __construct() {}

	/**
	 * Setup general plugin stuff. Load API
	 *
	 * @since 7.2
	 */
	public function setup() {
		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
	}

	/**
	 * Setup JS and CSS
	 *
	 * @since 7.2
	 */
	public function action_admin_enqueue_scripts() {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$css_path = '/assets/build/css/settings.css';
			$js_path = '/assets/js/settings.js';
		} else {
			$css_path = '/assets/build/css/settings.min.css';
			$js_path = '/assets/build/js/settings.min.js';
		}
		wp_enqueue_style( 'ccf-settings', plugins_url( $css_path, dirname( __FILE__ ) ), array(), CCF_VERSION );
		wp_enqueue_script( 'ccf-settings', plugins_url( $js_path, dirname( __FILE__ ) ), array( 'jquery' ), CCF_VERSION, true );
	}

	/**
	 * Sanitize settings
	 *
	 * @since 7.2
	 * @return array
	 */
	public function sanitize( $option ) {
		$clean_option = array();

		$clean_option['asset_loading_restriction_enabled'] = ( '1' === $option['asset_loading_restriction_enabled'] ) ? true : false;

		foreach ( $option['asset_loading_restrictions'] as $asset ) {
			$clean_option['asset_loading_restrictions'][] = array(
				'type' => sanitize_text_field( $asset['type'] ),
				'location' => sanitize_text_field( $asset['location'] ),
			);
		}

		return $clean_option;
	}

	/**
	 * Register settings and settings fields
	 *
	 * @since 7.2
	 */
	public function register_settings() {
		register_setting( 'ccf-settings', 'ccf_settings', array( $this, 'sanitize' ) );

		$option = get_option( 'ccf_settings' );
		$restriction_classes = 'ccf-asset-loading-restrictions-wrap ccf-hide-field';
		if ( ! empty( $option['asset_loading_restriction_enabled'] ) ) {
			$restriction_classes = 'ccf-asset-loading-restrictions-wrap';
		}

		add_settings_section( 'asset-loading-restriction', 'Asset Loading Restriction', array( $this, 'asset_loading_restriction_summary' ), 'custom-contact-forms' );
		add_settings_field( 'asset-loading-restriction-enable', esc_html__( 'Enable Asset Loading Restrictions', 'custom-contact-forms' ), array( $this, 'asset_loading_restriction_enable' ), 'custom-contact-forms', 'asset-loading-restriction' );
		add_settings_field( 'asset-loading-restriction-choose', esc_html__( 'Restrict Asset Loading To', 'custom-contact-forms' ), array( $this, 'asset_loading_restriction_choose' ), 'custom-contact-forms', 'asset-loading-restriction', array( 'class' => $restriction_classes ) );
	}

	/**
	 * Output asset loading summary
	 *
	 * @since 7.2
	 */
	public function asset_loading_restriction_summary() {
		?>
			<p>
				<?php esc_html_e( "By default, Custom Contact Forms loads all it's assets (JavaScript, CSS, etc.) on every page of your site. The reason for this is that there is no where to determine where you use forms. Asset Page Control allows you to specify the URLs or post IDs where your CCF forms will exist. By specifying where your forms will live, assets will not be unnecessarily loaded on every page of your site.", 'custom-contact-forms' ); ?>
			</p>
		<?php
	}

	/**
	 * Output asset loading enabler field
	 *
	 * @since 7.2
	 */
	public function asset_loading_restriction_enable() {
		$option = get_option( 'ccf_settings' );

		?>
		<select class="ccf-asset-loading-restriction-enabled" name="ccf_settings[asset_loading_restriction_enabled]">
			<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
			<option <?php selected( $option['asset_loading_restriction_enabled'], true ); ?> value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Output asset restriction chooser
	 *
	 * @since 7.2
	 */
	public function asset_loading_restriction_choose() {
		$option = get_option( 'ccf_settings' );

		?>
			<div class="ccf-asset-restrictions">
				<?php if ( ! empty( $option['asset_loading_restrictions'] ) ) : $i = 0; foreach ( $option['asset_loading_restrictions'] as $asset ) : ?>
					<div class="asset">
						<input value="<?php echo esc_attr( $asset['location'] ); ?>" name="ccf_settings[asset_loading_restrictions][<?php echo $i; ?>][location]" class="asset-location" type="text" placeholder="<?php esc_attr_e( 'URL or post ID', 'custom-contact-forms' ); ?>"> 
						<?php esc_html_e( 'Restriction type:', 'custom-contact-forms' ); ?>
						<select class="restriction-type" name="ccf_settings[asset_loading_restrictions][<?php echo $i; ?>][type]">
							<option value="url"><?php esc_html_e( 'URL', 'custom-contact-forms' ); ?></option>
							<option <?php selected( $asset['type'], 'post_id' ); ?> value="post_id"><?php esc_html_e( 'Post ID', 'custom-contact-forms' ); ?></option>
						</select>

						<span class="add">+</span>
						<span class="delete">&times;</span>
					</div>
				<?php $i++;
endforeach; else : ?>
					<div class="asset">
						<input name="ccf_settings[asset_loading_restrictions][0][location]" class="asset-location" type="text" placeholder="<?php esc_attr_e( 'URL or post ID', 'custom-contact-forms' ); ?>"> 
						<?php esc_html_e( 'Restriction type:', 'custom-contact-forms' ); ?>
						<select class="restriction-type" name="ccf_settings[asset_loading_restrictions][0][type]">
							<option value="url"><?php esc_html_e( 'URL', 'custom-contact-forms' ); ?></option>
							<option value="post_id"><?php esc_html_e( 'Post ID', 'custom-contact-forms' ); ?></option>
						</select>

						<span class="add">+</span>
						<span class="delete">&times;</span>
					</div>
				<?php endif; ?>
			</div>
		<?php
	}

	public function register_menu_page() {
		add_submenu_page( 'edit.php?post_type=ccf_form', esc_html__( 'Custom Contact Forms Settings', 'custom-contact-forms' ), esc_html__( 'Settings', 'custom-contact-forms' ), 'manage_options', 'custom-contact-forms', array( $this, 'screen_options' ) );
	}

	/**
	 * Output options page wrap
	 *
	 * @since 7.2
	 */
	public function screen_options() {
		?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Custom Contact Forms Settings', 'custom-contact-forms' ); ?></h1>

				<form action="options.php" method="post">
					<?php settings_fields( 'ccf-settings' ); ?>
					<?php do_settings_sections( 'custom-contact-forms' ); ?>
					<?php submit_button(); ?>
				</form>
			</div>
		<?php
	}

	/**
	 * Return singleton instance of class
	 *
	 * @since 7.2
	 * @return object
	 */
	public static function factory() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self();
			$instance->setup();
		}

		return $instance;
	}
}

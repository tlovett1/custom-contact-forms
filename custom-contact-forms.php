<?php
/**
 * Plugin Name: Custom Contact Forms
 * Plugin URI: http://www.taylorlovett.com
 * Description: Build beautiful custom forms the WordPress way. View live previews of your forms while you build them.
 * Author: Taylor Lovett
 * Version: 6.9.0
 * Author URI: http://www.taylorlovett.com
 */

/**
 * Include plugin reqs
 */

require_once( dirname( __FILE__ ) . '/classes/class-ccf-constants.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-custom-contact-forms.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-form-cpt.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-submission-cpt.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-form-mail.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-field-cpt.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-choice-cpt.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-form-manager.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-field-renderer.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-form-renderer.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-form-handler.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-upgrader.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-widget.php' );
require_once( dirname( __FILE__ ) . '/classes/class-ccf-export.php' );

CCF_Custom_Contact_Forms::factory();
CCF_Constants::factory();
CCF_Form_CPT::factory();
CCF_Submission_CPT::factory();
CCF_Field_CPT::factory();
CCF_Choice_CPT::factory();
CCF_Form_Manager::factory();
CCF_Form_Renderer::factory();
CCF_Field_Renderer::factory();
CCF_Form_Handler::factory();
CCF_Upgrader::factory();
CCF_Export::factory();

/**
 * Setup the widget
 *
 * @since 6.4
 */
function ccf_register_widget() {
	register_widget( 'CCF_Widget' );
}
add_action( 'widgets_init', 'ccf_register_widget' );

/**
 * Flush the rewrites at the end of init after the plugin is been activated.
 *
 * @since 6.0
 */
function ccf_flush_rewrites() {
	update_option( 'ccf_flush_rewrites', true );
}

register_activation_hook( __FILE__, 'ccf_flush_rewrites' );
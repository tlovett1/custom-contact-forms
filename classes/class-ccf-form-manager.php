<?php

class CCF_Form_Manager {
	/**
	 * Placeholder method
	 *
	 * @since 6.0
	 */
	public function __construct() {}

	/**
	 * Setup backbone templates and MCE stuff
	 *
	 * @since 6.0
	 */
	public function setup() {
		add_action( 'media_buttons', array( $this, 'action_media_buttons' ) );
		add_action( 'admin_footer', array( $this, 'print_templates' ) );
		add_action( 'admin_enqueue_scripts' , array( $this, 'action_admin_enqueue_scripts_css' ), 9 );
		add_filter( 'mce_css', array( $this, 'filter_mce_css' ) );
	}

	/**
	 * Add preview css to MCE
	 *
	 * @param string $css
	 * @since 6.0
	 * @return string
	 */
	public function filter_mce_css( $css ) {
		if ( ! apply_filters( 'ccf_enable_tinymce_previews', true ) ) {
			return $css;
		}

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$css_path = '/build/css/form-mce.css';
		} else {
			$css_path = '/build/css/form-mce.min.css';
		}

		$css .= ', ' . plugins_url( $css_path, dirname( __FILE__ ) );
		return $css;
	}

	/**
	 * Print all Backbone templates for form manager
	 *
	 * @since 6.0
	 */
	public function print_templates() {
		$max_upload_size = wp_max_upload_size();
		if ( ! $max_upload_size ) {
			$max_upload_size = 0;
		}

		?>

		<script type="text/html" id="ccf-error-modal-template">
			<div class="notification-dialog-background"></div>
			<div class="notification-dialog">
				<div class="close">&times;</div>
				<div class="message">
					<div class="title"><?php esc_html_e( 'Custom Contact Forms is experiencing issues.', 'custom-contact-forms' ); ?></div>

					<p>
						<# if ( 'sync' === messageType ) { #>
							<?php printf( __( 'There is an issue with synchronizing data. Please try deactivating all other plugins and activating the TwentyFifteen theme. If this fixes the problem, you have a plugin or theme conflict. If it does not, please post in the <a href="%s">support forums</a> or <a href="%s">Github</a>.', 'custom-contact-forms' ), 'https://wordpress.org/support/plugin/custom-contact-forms', 'http://github.com/tlovett1/custom-contact-forms' ); ?>
						<# } #>
					</p>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-main-modal-template">
			<div class="wrap">
				<a class="close-icon">&times;</a>
				<div class="main-menu">
					<h1><?php esc_html_e( 'Manage Forms', 'custom-contact-forms' ); ?></h1>
					<# if ( ! single ) { #>
						<ul>
							<li><a class="selected menu-item" data-view="form-pane" href="#form-pane"><?php esc_html_e( 'New Form', 'custom-contact-forms' ); ?></a></li>
							<li><a class="menu-item" data-view="existing-form-pane" href="#existing-form-pane"><?php esc_html_e( 'Existing Forms', 'custom-contact-forms' ); ?></a></li>
						</ul>
					<# } #>
				</div>
				<div class="ccf-form-pane <# if ( single ) { #>single<# } #>"></div>
				<div class="ccf-existing-form-pane"></div>
			</div>
		</script>

		<script type="text/html" id="ccf-field-row-template">
			<h4>
				<span class="right">
					<a aria-hidden="true" data-icon="&#xe602;" class="delete"></a>
				</span>
				<span class="label">{{ label }}</span>
			</h4>

			<div class="preview"></div>
		</script>

		<script type="text/html" id="ccf-form-pane-template">
			<div class="disabled-overlay"></div>
			<div class="left-sidebar accordion-container">
				<div class="accordion-section expanded">
					<h2 aria-hidden="true"><?php esc_html_e( 'Standard Fields', 'custom-contact-forms' ); ?></h2>
					<div class="section-content">
						<div class="fields draggable-fields"></div>
					</div>
				</div>
				<div class="accordion-section">
					<h2 aria-hidden="true"><?php esc_html_e( 'Special Fields', 'custom-contact-forms' ); ?></h2>
					<div class="section-content">
						<div class="special-fields draggable-fields"></div>
					</div>
				</div>
				<div class="accordion-section">
					<h2 aria-hidden="true"><?php esc_html_e( 'Structure', 'custom-contact-forms' ); ?></h2>
					<div class="section-content">
						<div class="structure-fields draggable-fields"></div>
					</div>
				</div>
				<div class="accordion-section ccf-form-settings"></div>
				<div class="accordion-section ccf-form-notifications"></div>
			</div>

			<div class="form-content" data-drag-message="<?php esc_html_e( '&larr; Drag fields from the left here.', 'custom-contact-forms' ); ?>">
			</div>

			<div class="right-sidebar ccf-field-sidebar accordion-container"></div>

			<div class="bottom">
				<input type="button" class="button insert-form-button" value="<?php esc_html_e( 'Insert into post', 'custom-contact-forms' ); ?>">
				<input type="button" class="button button-primary save-button" value="<?php esc_html_e( 'Save Form', 'custom-contact-forms' ); ?>">
				<div class="spinner" style="background: url( '<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>' ) no-repeat;"></div>
			</div>
		</script>

		<script type="text/html" id="ccf-form-settings-template">
			<h2 aria-hidden="true"><?php esc_html_e( 'Form Settings', 'custom-contact-forms' ); ?></h2>
			<div class="section-content">
				<p>
					<label for="ccf_form_title"><?php esc_html_e( 'Form Title:', 'custom-contact-forms' ); ?></label>
					<input class="widefat form-title" id="ccf_form_title" name="title" type="text" value="{{ form.title }}">
				</p>

				<p>
					<label for="ccf_form_description"><?php esc_html_e( 'Form Description:', 'custom-contact-forms' ); ?></label>
					<textarea class="widefat form-description" id="ccf_form_description" name="description">{{ form.description }}</textarea>
				</p>

				<p>
					<label for="ccf_form_button_text"><?php esc_html_e( 'Button Text:', 'custom-contact-forms' ); ?></label>
					<input class="widefat form-button-text" id="ccf_form_button_text" name="text" type="text" value="{{ form.buttonText }}">
				</p>

				<p>
					<label for="ccf_form_completion_action_type"><?php esc_html_e( 'On form completion:', 'custom-contact-forms' ); ?></label>

					<select name="completion_action_type" class="form-completion-action-type" id="ccf_form_completion_action_type">
						<option value="text"><?php esc_html_e( 'Show text', 'custom-contact-forms' ); ?></option>
						<option value="redirect" <# if ( 'redirect' === form.completionActionType ) { #>selected<# } #>><?php esc_html_e( 'Redirect', 'custom-contact-forms' ); ?></option>
					</select>
				</p>
				<p class="completion-redirect-url">
					<label for="ccf_form_completion_redirect_url"><?php esc_html_e( 'Redirect URL:', 'custom-contact-forms' ); ?></label>
					<input class="widefat form-completion-redirect-url" id="ccf_form_completion_redirect_url" name="text" type="text" value="{{ form.completionRedirectUrl }}">
				</p>
				<p class="completion-message">
					<label for="ccf_form_completion_message"><?php esc_html_e( 'Completion Message:', 'custom-contact-forms' ); ?></label>
					<textarea class="widefat form-completion-message" id="ccf_form_completion_message" name="completion-message">{{ form.completionMessage }}</textarea>
				</p>
				<p>
					<label for="ccf_form_pause"><?php esc_html_e( 'Pause form:', 'custom-contact-forms' ); ?></label>

					<select name="form_pause" class="form-pause" id="ccf_form_pause">
						<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						<option value="1" <# if ( form.pause ) { #>selected<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
					</select>
				</p>
				<p class="pause-message">
					<label for="ccf_form_pause_message"><?php esc_html_e( 'Pause Message:', 'custom-contact-forms' ); ?></label>
					<textarea class="widefat form-pause-message" id="ccf_form_pause_message" name="pause-message">{{ form.pauseMessage }}</textarea>
				</p>
			</div>
		</script>

		<script type="text/html" id="ccf-form-notifications-template">
			<h2 aria-hidden="true"><?php esc_html_e( 'Form Notifications', 'custom-contact-forms' ); ?></h2>
			<div class="section-content">
				<p>
					<label for="ccf_form_send_email_notifications"><?php esc_html_e( 'Send Email Notifications:', 'custom-contact-forms' ); ?></label>

					<select name="send_email_notifications" class="form-send-email-notifications" id="ccf_form_send_email_notifications">
						<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						<option value="0" <# if ( ! form.sendEmailNotifications ) { #>selected<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
					</select>
				</p>

				<p class="email-notification-setting">
					<label for="ccf_form_email_notification_addresses"><?php esc_html_e( '"To" Email Addresses (comma separated):', 'custom-contact-forms' ); ?></label>
					<input class="widefat form-email-notification-addresses" id="ccf_form_email_notification_addresses" name="email-notification-addresses" value="{{ form.emailNotificationAddresses }}">
				</p>

				<p class="email-notification-setting">
					<label for="ccf_form_email_notification_from_type"><?php esc_html_e( '"From" Email Address Type:', 'custom-contact-forms' ); ?></label>
					<select name="email_notification_from_type" class="form-email-notification-from-type" id="ccf_form_email_notification_from_type">
						<option value="default"><?php esc_html_e( 'WordPress Default', 'custom-contact-forms' ); ?></option>
						<option value="custom" <# if ( 'custom' === form.emailNotificationFromType ) { #>selected<# } #>><?php esc_html_e( 'Custom Email', 'custom-contact-forms' ); ?></option>
						<option value="field" <# if ( 'field' === form.emailNotificationFromType ) { #>selected<# } #>><?php esc_html_e( 'Form Field', 'custom-contact-forms' ); ?></option>
					</select>

					<span class="explain"><?php esc_html_e( 'You can set the notification emails from address to be the WP default, a custom email address, or pull the address from a field in the form.', 'custom-contact-forms' ); ?></span>
				</p>

				<p class="email-notification-from-address">
					<label for="ccf_form_email_notification_from_address"><?php esc_html_e( 'Custom "From" Email Address:', 'custom-contact-forms' ); ?></label>
					<input class="widefat form-email-notification-from-address" id="ccf_form_email_notification_from_address" name="email-notification-from-address" value="{{ form.emailNotificationFromAddress }}">
				</p>

				<p class="email-notification-from-field">
					<label for="ccf_form_email_notification_from_field"><?php esc_html_e( 'Pull "From" Email Dynamically from Field:', 'custom-contact-forms' ); ?></label>
					<select name="email_notification_from_field" class="form-email-notification-from-field" id="ccf_form_email_notification_from_field">
					</select>
				</p>

				<p class="email-notification-setting">
					<label for="ccf_form_email_notification_from_name_type"><?php esc_html_e( '"From" Name Type:', 'custom-contact-forms' ); ?></label>
					<select name="email_notification_from_name_type" class="form-email-notification-from-name-type" id="ccf_form_email_notification_from_name_type">
						<option value="custom"><?php esc_html_e( 'Custom Name', 'custom-contact-forms' ); ?></option>
						<option value="field" <# if ( 'field' === form.emailNotificationFromNameType ) { #>selected<# } #>><?php esc_html_e( 'Form Field', 'custom-contact-forms' ); ?></option>
					</select>

					<span class="explain"><?php esc_html_e( 'You can set the notification emails from name to be a custom name or pull the name from a field in the form.', 'custom-contact-forms' ); ?></span>
				</p>

				<p class="email-notification-from-name">
					<label for="ccf_form_email_notification_from_name"><?php esc_html_e( 'Custom "From" Name:', 'custom-contact-forms' ); ?></label>
					<input class="widefat form-email-notification-from-name" id="ccf_form_email_notification_from_name" name="email-notification-from-name" value="{{ form.emailNotificationFromName }}">
				</p>

				<p class="email-notification-from-name-field">
					<label for="ccf_form_email_notification_from_name_field"><?php esc_html_e( 'Pull "From" Name Dynamically from Field:', 'custom-contact-forms' ); ?></label>
					<select name="email_notification_from_name_field" class="form-email-notification-from-name-field" id="ccf_form_email_notification_from_name_field">
					</select>
				</p>






				<p class="email-notification-setting">
					<label for="ccf_form_email_notification_subject_type"><?php esc_html_e( 'Email Subject Type:', 'custom-contact-forms' ); ?></label>
					<select name="email_notification_subject_type" class="form-email-notification-subject-type" id="ccf_form_email_notification_subject_type">
						<option value="default"><?php esc_html_e( 'Default', 'custom-contact-forms' ); ?></option>
						<option value="custom" <# if ( 'custom' === form.emailNotificationSubjectType ) { #>selected<# } #>><?php esc_html_e( 'Custom Subject', 'custom-contact-forms' ); ?></option>
						<option value="field" <# if ( 'field' === form.emailNotificationSubjectType ) { #>selected<# } #>><?php esc_html_e( 'Form Field', 'custom-contact-forms' ); ?></option>
					</select>

					<span class="explain"><?php esc_html_e( 'You can set the notification emails subject line to be the CCF default, custom text, or pull the subject from a field in the form.', 'custom-contact-forms' ); ?></span>
				</p>

				<p class="email-notification-subject">
					<label for="ccf_form_email_notification_subject"><?php esc_html_e( 'Custom Email Subject:', 'custom-contact-forms' ); ?></label>
					<input class="widefat form-email-notification-subject" id="ccf_form_email_notification_subject" name="email-notification-subject" value="{{ form.emailNotificationSubject }}">
				</p>

				<p class="email-notification-subject-field">
					<label for="ccf_form_email_notification_subject_field"><?php esc_html_e( 'Pull Email Subject Dynamically from Field:', 'custom-contact-forms' ); ?></label>
					<select name="email_notification_subject_field" class="form-email-notification-subject-field" id="ccf_form_email_notification_subject_field">
					</select>
				</p>
			</div>
		</script>

		<script type="text/html" id="ccf-existing-form-pane-template">

			<div class="ccf-existing-form-table"></div>

		</script>

		<script type="text/html" id="ccf-pagination-template">
			<span class="num-items">{{ totalObjects }} <?php esc_html_e( 'items', 'custom-contact-forms' ); ?></span>

			<a class="first <# if ( currentPage <= 1 ) { #>disabled<# } #>">&laquo;</a>
			<a class="prev <# if ( currentPage <= 1 ) { #>disabled<# } #>">&lsaquo;</a>

			<span class="pages">{{ currentPage }} of {{ totalPages }}</span>

			<a class="next <# if ( currentPage >= totalPages ) { #>disabled<# } #>">&rsaquo;</a>
			<a class="last <# if ( currentPage >= totalPages ) { #>disabled<# } #>">&raquo;</a>
		</script>

        <script type="text/html" id="ccf-existing-form-table-template">
			<table cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th class="id"><?php esc_html_e( 'ID', 'custom-contact-forms' ); ?></th>
						<th class="title"><?php esc_html_e( 'Title', 'custom-contact-forms' ); ?></th>
						<th class="date"><?php esc_html_e( 'Date', 'custom-contact-forms' ); ?></th>
						<th class="author"><?php esc_html_e( 'Author', 'custom-contact-forms' ); ?></th>
						<th class="number-of-fields"><?php esc_html_e( 'Number of Fields', 'custom-contact-forms' ); ?></th>
						<th class="submissions"><?php esc_html_e( 'Submissions', 'custom-contact-forms' ); ?></th>
					</tr>
				</thead>
				<tbody class="rows">

				</tbody>
				<tfoot>
				<tr>
					<th class="id"><?php esc_html_e( 'ID', 'custom-contact-forms' ); ?></th>
					<th class="title"><?php esc_html_e( 'Title', 'custom-contact-forms' ); ?></th>
					<th class="date"><?php esc_html_e( 'Date', 'custom-contact-forms' ); ?></th>
					<th class="author"><?php esc_html_e( 'Author', 'custom-contact-forms' ); ?></th>
					<th class="submissions"><?php esc_html_e( 'Number of Fields', 'custom-contact-forms' ); ?></th>
					<th class="submission"><?php esc_html_e( 'Submissions', 'custom-contact-forms' ); ?></th>
				</tr>
				</tfoot>
			</table>

			<div class="ccf-pagination"></div>
        </script>

		<script type="text/html" id="ccf-empty-field-template">
			<div class="no-field">
				<?php _e( '<span>&larr;</span> Click on a field to edit it.', 'custom-contact-forms' ); ?>
			</div>
		</script>

		<script type="text/html" id="ccf-single-line-text-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true">Basic</h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug', 'custom-contact-forms' ); ?> (a-z, 0-9, -, _):</label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-value"><?php esc_html_e( 'Initial Value:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-value" class="field-value" type="text" value="{{ field.value }}">
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-file-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true">Basic</h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug', 'custom-contact-forms' ); ?> (a-z, 0-9, -, _):</label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-file-extensions"><?php esc_html_e( 'Allowed File Extensions (comma separate):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-file-extensions" class="field-file-extensions" type="text" value="{{ field.fileExtensions }}">
						<span class="explain"><?php _e( 'If left blank, will default to all extensions registered by WordPress. If you use a file extension or mime type not <a href="http://codex.wordpress.org/Function_Reference/get_allowed_mime_types">whitelisted by WordPress</a>, you will need to filter and manually whitelist the new extension.', 'custom-contact-forms' ); ?></span>
					</div>
					<div>
						<label for="ccf-field-max-file-size"><?php esc_html_e( 'Max File Size (in MB):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-max-file-size" class="field-max-file-size" type="text" value="{{ field.maxFileSize }}">
						<span class="explain"><?php printf( esc_html__( 'If left blank, will default to %d MB. Maximum allowed by server is %d MB.', 'custom-contact-forms' ), (double) size_format( $max_upload_size ), (double) size_format( $max_upload_size ) ); ?></span>
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-recaptcha-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true">Basic</h2>
				<div class="section-content">
					<p><?php _e( 'reCAPTCHA is a simple captcha service provided by Google. <a target="_blank" href="https://www.google.com/recaptcha/intro/index.html">Learn more</a>', 'custom-contact-forms' ); ?></p>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-site-key"><span class="required">*</span> <?php esc_html_e( 'Site Key:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-site-key" class="field-site-key" type="text" value="{{ field.siteKey }}">
						<a href="http://google.com/recaptcha/" target="_blank"><?php _e( "Don't have one?", 'custom-contact-forms' ); ?></a>
					</div>
					<div>
						<label for="ccf-field-secret-key"><span class="required">*</span> <?php esc_html_e( 'Secret Key:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-secret-key" class="field-secret-key" type="text" value="{{ field.secretKey }}">
						<a href="http://google.com/recaptcha/" target="_blank"><?php _e( "Don't have one?", 'custom-contact-forms' ); ?></a>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-website-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true">Basic</h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug', 'custom-contact-forms' ); ?> (a-z, 0-9, -, _):</label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-value"><?php esc_html_e( 'Initial Value:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-value" class="field-value" type="text" value="{{ field.value }}">
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-html-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-html"><?php esc_html_e( 'HTML Content:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-html" class="field-html">{{ field.html }}</textarea>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-section-header-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-heading"><?php esc_html_e( 'Heading:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-heading" class="field-heading" type="text" value="{{ field.heading }}">
					</div>
					<div>
						<label for="ccf-field-subheading"><?php esc_html_e( 'Sub Heading:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-subheading" class="field-subheading" type="text">{{ field.subheading }}</textarea>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-paragraph-text-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug', 'custom-contact-forms' ); ?> (a-z, 0-9, -, _):</label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-value"><?php esc_html_e( 'Initial Value:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-value" class="field-value" type="text" value="{{ field.value }}">
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-hidden-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug (a-z, 0-9, -, _):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-value"><?php esc_html_e( 'Initial Value:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-value" class="field-value" type="text" value="{{ field.value }}">
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-name-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug (a-z, 0-9, -, _):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-date-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug (a-z, 0-9, -, _):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<# if ( ! field.showTime ) { #>
						<div>
							<label for="ccf-field-value"><?php esc_html_e( 'Initial Value:', 'custom-contact-forms' ); ?></label>
							<input id="ccf-field-value" class="field-value" type="text" value="{{ field.value }}">
						</div>
					<# } #>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div>
						<input type="checkbox" <# if ( field.showDate ) { #>checked="checked"<# } #> class="field-show-date" value="1" id="ccf-field-show-date">
						<label for="ccf-show-date"><?php esc_html_e( 'Enable Date Select', 'custom-contact-forms' ); ?></label>
					</div>
					<div>
						<input type="checkbox" <# if ( field.showTime ) { #>checked="checked"<# } #> class="field-show-time" value="1" id="ccf-field-show-time">
						<label for="ccf-show-time"><?php esc_html_e( 'Enable Time Select', 'custom-contact-forms' ); ?></label>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<# if ( ! ( field.showTime && field.showDate ) ) { #>
						<div>
							<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
							<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
						</div>
					<# } #>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-phone-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug (a-z, 0-9, -, _):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-value"><?php esc_html_e( 'Initial Value:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-value" class="field-value" type="text" value="{{ field.value }}">
					</div>
					<div>
						<label for="ccf-field-phone-format"><?php esc_html_e( 'Format:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-phone-format" class="field-phone-format">
							<option value="us">(xxx) xxx-xxxx</option>
							<option value="international" <# if ( 'international' === field.phoneFormat ) { #>selected="selected"<# } #>><?php esc_html_e( 'International', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-address-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug (a-z, 0-9, -, _):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-address-type"><?php esc_html_e( 'Type:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-address-type" class="field-address-type">
							<option value="us"><?php esc_html_e( 'United States', 'custom-contact-forms' ); ?></option>
							<option value="international" <# if ( 'international' === field.addressType ) { #>selected="selected"<# } #>><?php esc_html_e( 'International', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-email-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug (a-z, 0-9, -, _):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<# if ( ! field.emailConfirmation ) { #>
						<div>
							<label for="ccf-field-value"><?php esc_html_e( 'Initial Value:', 'custom-contact-forms' ); ?></label>
							<input id="ccf-field-value" class="field-value" type="text" value="{{ field.value }}">
						</div>
					<# } #>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div>
						<label for="ccf-field-email-confirmation"><?php esc_html_e( 'Require Confirmation:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-email-confirmation" class="field-email-confirmation">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.emailConfirmation ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<# if ( ! field.emailConfirmation ) { #>
						<div>
							<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
							<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
						</div>
					<# } #>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-field-choice-template">
			<a aria-hidden="true" data-icon="&#xe606;" class="move"></a>
			<input class="choice-selected" <# if ( choice.selected ) { #>checked<# } #> name="selected" type="checkbox" value="1">
			<input class="choice-label" type="text" placeholder="<?php esc_html_e( 'Label', 'custom-contact-forms' ); ?>" value="{{ choice.label }}">
			<input class="choice-value" type="text" placeholder="<?php esc_html_e( 'Value', 'custom-contact-forms' ); ?>" value="{{ choice.value }}">
			<a aria-hidden="true" data-icon="&#xe605;" class="add"></a>
			<a aria-hidden="true" data-icon="&#xe604;" class="delete"></a>
		</script>

		<script type="text/html" id="ccf-dropdown-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug (a-z, 0-9, -, _):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div>
						<label><?php esc_html_e( 'Manage field choices:', 'custom-contact-forms' ); ?></label>
						<div class="repeatable-choices">
						</div>

						<p><?php esc_html_e( "Note: If an option does not have a \"value\", it will not be considered a valid selection if the field is required. The \"value\" is what's read, stored, and displayed in the submission.", 'custom-contact-forms' ); ?></p>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-radio-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug (a-z, 0-9, -, _):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div>
						<label><?php esc_html_e( 'Manage field choices:', 'custom-contact-forms' ); ?></label>
						<div class="repeatable-choices">
						</div>

						<p><?php esc_html_e( "Note: If an option does not have a \"value\", it will not be considered a valid selection if the field is required. The \"value\" is what's read, stored, and displayed in the submission.", 'custom-contact-forms' ); ?></p>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-checkboxes-template">
			<div class="accordion-section expanded">
				<h2 aria-hidden="true"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-slug"><span class="required">*</span> <?php esc_html_e( 'Internal Unique Slug (a-z, 0-9, -, _):', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-slug" class="field-slug" type="text" value="{{ field.slug }}">
					</div>
					<div>
						<label for="ccf-field-label"><?php esc_html_e( 'Label:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-label" class="field-label" type="text" value="{{ field.label }}">
					</div>
					<div>
						<label for="ccf-field-description"><?php esc_html_e( 'Description:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-description" class="field-description">{{ field.description }}</textarea>
					</div>
					<div>
						<label for="ccf-field-required"><?php esc_html_e( 'Required:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-required" class="field-required">
							<option value="1"><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							<option value="0" <# if ( ! field.required ) { #>selected="selected"<# } #>><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div>
						<label><?php esc_html_e( 'Manage field choices:', 'custom-contact-forms' ); ?></label>
						<div class="repeatable-choices">
						</div>

						<p><?php esc_html_e( "Note: If an option does not have a \"value\", it will not be considered a valid selection if the field is required. The \"value\" is what's read, stored, and displayed in the submission.", 'custom-contact-forms' ); ?></p>
					</div>
				</div>
			</div>
			<div class="accordion-section">
				<h2 aria-hidden="true"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></h2>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-empty-form-table-row-template">
			<td class="empty-form-table" colspan="6">
				<?php esc_html_e( 'You currently have no forms. Add some!', 'custom-contact-forms' ); ?>
			</td>
		</script>

		<script type="text/html" id="ccf-single-line-text-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<input disabled type="text" placeholder="{{ field.placeholder }}" value="{{ field.value }}">
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-file-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<input disabled type="file" placeholder="{{ field.placeholder }}" value="{{ field.value }}">
			<div class="field-description">
				<# if ( field.fileExtensions ) {
					var extensions = field.fileExtensions.toLowerCase().replace( /\s/g, '' ).split( ',' ).join( ', ' );
					var file_size = <?php echo floor( $max_upload_size / 1000 / 1000 ); ?>;
					if ( field.maxFileSize ) {
						file_size = field.maxFileSize;
					}
					#>
					<?php esc_html_e( 'Allowed file extensions are {{ extensions }}. ', 'custom-contact-forms' ); ?>
				<# } #>
				<?php esc_html_e( 'Max file size is {{ file_size }} MB. ', 'custom-contact-forms' ); ?>
				{{ field.description }}
			</div>
		</script>

		<script type="text/html" id="ccf-recaptcha-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<img class="recaptcha-preview-img" src="<?php echo plugins_url( 'img/recaptcha.png', dirname( __FILE__ )); ?>">
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-paragraph-text-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<textarea placeholder="{{ field.placeholder }}" disabled>{{ field.value }}</textarea>
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-dropdown-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<select>
				<# if ( field.choices.length === 0 || ( field.choices.length === 1 && ! field.choices.at( 0 ).get( 'label' ) && ! field.choices.at( 0 ).get( 'value' ) ) ) { #>
					<option><?php esc_html_e( 'An example choice', 'custom-contact-forms' ); ?></option>
				<#} else { #>
					<# field.choices.each( function( choice ) { #>
						<option <# if ( choice.get( 'selected' ) ) { #>selected<# } #> value="{{ choice.get( 'value' ) }}">{{ choice.get( 'label' ) }}</option>
					<# }); #>
				<# } #>
			</select>
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-radio-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<# if ( field.choices.length === 0 || ( field.choices.length === 1 && ! field.choices.at( 0 ).get( 'label' ) && ! field.choices.at( 0 ).get( 'value' ) ) ) { #>
				<div>
					<input type="radio" value="1" checked="checked"> <label><?php esc_html_e( 'An example choice', 'custom-contact-forms' ); ?></label>
				</div>
			<#} else { #>
				<# field.choices.each( function( choice ) { #>
					<div class="choice">
						<input type="radio" value="{{ choice.get( 'value' ) }}" <# if ( choice.get( 'selected' ) ) { #>checked="checked"<# } #>> <label>{{ choice.get( 'label' ) }}</label>
					</div>
				<# }); #>
			<# } #>
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-checkboxes-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<# if ( field.choices.length === 0 || ( field.choices.length === 1 && ! field.choices.at( 0 ).get( 'label' ) && ! field.choices.at( 0 ).get( 'value' ) ) ) { #>
				<div>
					<input type="checkbox" value="1" checked="checked"> <label><?php esc_html_e( 'An example choice', 'custom-contact-forms' ); ?></label>
				</div>
			<#} else { #>
				<# field.choices.each( function( choice ) { #>
					<div class="choice">
						<input type="checkbox" value="{{ choice.get( 'value' ) }}" <# if ( choice.get( 'selected' ) ) { #>checked="checked"<# } #>> <label>{{ choice.get( 'label' ) }}</label>
					</div>
				<# }); #>
			<# } #>
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-html-preview-template">
			<# if ( typeof mce !== 'undefined' ) { #>
				{{{ field.html }}}
			<# } else { #>
				<pre>&lt;pre&gt;<?php esc_html_e( 'Arbitrary block of HTML.', 'custom-contact-forms' ); ?>&lt;/pre&gt;</pre>
			<# } #>
		</script>

		<script type="text/html" id="ccf-section-header-preview-template">
			<div class="heading">
				<# if ( field.heading ) { #>{{ field.heading }}<# } else { #><?php esc_html_e( 'Section Heading', 'custom-contact-forms' ); ?><# } #>
			</div>
			<div class="subheading"><# if ( field.subheading ) { #>{{ field.subheading }}<# } else { #><?php esc_html_e( 'This is the sub-heading text.', 'custom-contact-forms' ); ?><# } #></div>
		</script>

		<script type="text/html" id="ccf-name-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<div class="left">
				<input type="text">
				<label class="sub-label"><?php esc_html_e( 'First', 'custom-contact-forms' ); ?></label>
			</div>
			<div class="right">
				<input type="text">
				<label class="sub-label"><?php esc_html_e( 'Last', 'custom-contact-forms' ); ?></label>
			</div>
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-date-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<# if ( field.showDate && ! field.showTime ) { #>
				<input value="{{ field.value }}" class="ccf-datepicker" disabled type="text">
			<# } else if ( ! field.showDate && field.showTime ) { #>
				<div class="full">
					<div class="hour">
						<input type="text">
						<label class="sub-label"><?php esc_html_e( 'HH', 'custom-contact-forms' ); ?></label>
					</div>
					<div class="minute">
						<input type="text">
						<label class="sub-label"><?php esc_html_e( 'MM', 'custom-contact-forms' ); ?></label>
					</div>
					<div class="am-pm">
						<select>
							<option value="am"><?php esc_html_e( 'AM', 'custom-contact-forms' ); ?></option>
							<option value="pm"><?php esc_html_e( 'PM', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			<# } else { #>
				<div class="left">
					<input class="ccf-datepicker" disabled type="text">
					<label class="sub-label"><?php esc_html_e( 'Date', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="right">
					<div class="hour">
						<input type="text">
						<label class="sub-label"><?php esc_html_e( 'HH', 'custom-contact-forms' ); ?></label>
					</div>
					<div class="minute">
						<input type="text">
						<label class="sub-label"><?php esc_html_e( 'MM', 'custom-contact-forms' ); ?></label>
					</div>
					<div class="am-pm">
						<select>
							<option value="am"><?php esc_html_e( 'AM', 'custom-contact-forms' ); ?></option>
							<option value="pm"><?php esc_html_e( 'PM', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
			<# } #>
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-address-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<# if ( field.addressType === 'us' ) { #>
				<div class="full">
					<input type="text">
					<label class="sub-label"><?php esc_html_e( 'Street Address', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="full">
					<input type="text">
					<label class="sub-label"><?php esc_html_e( 'Address Line 2', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="left">
					<input type="text">
					<label class="sub-label"><?php esc_html_e( 'City', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="right">
					<select>
						<?php foreach ( CCF_Constants::factory()->get_us_states() as $state ) : ?>
							<option><?php echo $state; ?></option>
						<?php endforeach; ?>
					</select>
					<label class="sub-label"><?php esc_html_e( 'State', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="left">
					<input type="text">
					<label class="sub-label"><?php esc_html_e( 'ZIP Code', 'custom-contact-forms' ); ?></label>
				</div>
			<# } else if ( field.addressType === 'international' ) { #>
				<div class="full">
					<input type="text">
					<label class="sub-label"><?php esc_html_e( 'Street Address', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="full">
					<input type="text">
					<label class="sub-label"><?php esc_html_e( 'Address Line 2', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="left">
					<input type="text">
					<label class="sub-label"><?php esc_html_e( 'City', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="right">
					<input type="text">
					<label class="sub-label"><?php esc_html_e( 'State / Region / Province', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="left">
					<input type="text">
					<label class="sub-label"><?php esc_html_e( 'ZIP / Postal Code', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="right">
					<select>
						<?php foreach ( CCF_Constants::factory()->get_countries() as $country ) : ?>
							<option><?php echo $country; ?></option>
						<?php endforeach; ?>
					</select>
					<label class="sub-label"><?php esc_html_e( 'Country', 'custom-contact-forms' ); ?></label>
				</div>
			<# } #>
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-email-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<# if ( ! field.emailConfirmation ) { #>
				<input placeholder="<# if ( field.placeholder ) { #>{{ field.placeholder }}<# } else { #><?php esc_html_e( 'email@example.com', 'custom-contact-forms' ); ?><# } #>" disabled type="text" value="{{ field.value }}">
			<# } else { #>
				<div class="left">
					<input type="text">
					<div class="sub-label"><?php esc_html_e( 'Email', 'custom-contact-forms' ); ?></div>
				</div>
				<div class="right">
					<input type="text">
					<div class="sub-label"><?php esc_html_e( 'Confirm Email', 'custom-contact-forms' ); ?></div>
				</div>
			<# } #>
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-website-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<input placeholder="<# if ( field.placeholder ) { #>{{ field.placeholder }}<# } else { #>http://<# } #>" disabled type="text" value="{{ field.value }}">
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-phone-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span>*</span><# } #></label>
			<input placeholder="<# if ( field.placeholder ) { #>{{ field.placeholder }}<# } else if ( 'us' === field.phoneFormat ) { #>(301) 101-8976<# } #>" disabled type="text" value="{{ field.value }}">
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-existing-form-table-row-template">

			<td>{{ form.ID }}</td>
			<td>
				<a class="edit edit-form title" data-view="form-pane" data-form-id="{{ form.ID }}" href="#form-pane-{{ form.ID }}"><# if ( form.title ) { #>{{ form.title }}<# } else { #>{{ '<?php esc_html_e( '(No title)', 'custom-contact-forms' ); ?>' }}<# } #></a>
				<div class="actions">
					<a class="edit edit-form" data-view="form-pane" data-form-id="{{ form.ID }}" href="#form-pane-{{ form.ID }}"><?php esc_html_e( 'Edit', 'custom-contact-forms' ); ?></a> |
					<a class="insert-form-button"><?php esc_html_e( 'Insert into post', 'custom-contact-forms' ); ?></a> |
					<a class="delete"><?php esc_html_e( 'Trash', 'custom-contact-forms' ); ?></a>
				</div>
			</td>
			<td>
				{{ utils.getPrettyPostDate( form.date_gmt ) }}
			</td>
			<td>
				{{ form.author.username }}
			</td>
			<td>
				{{ form.fields.length }}
			</td>
			<td>
				0
			</td>
		</script>

		<script type="text/html" id="ccf-form-mce-preview">
			<div class="ccf-form-preview form-id-{{ form.ID }}">
				<# if ( form.title ) { #>
					<h2>{{ form.title }}</h2>
				<# } #>

				<# if ( form.description && form.description != '' ) { #>
					<p>{{ form.description }}</p>
				<# } #>

				<# if ( form.fields ) { #>
					<# _.each( form.fields, function( field ) { #>
						<div class="field {{ field.type }} field-{{ field.ID }}">
							{{{ field.preview }}}
						</div>
					<# } ); #>
				<# } #>

				<div class="field-submit">
					<input type="button" value="{{ form.buttonText }}">
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-form-mce-error-preview">
			<div class="ccf-form-preview preview-error">
				<?php esc_html_e( 'There is a problem with this form. Is it trashed or deleted?', 'custom-contact-forms' ); ?>
			</div>
		</script>

		<script type="text/html" id="ccf-submission-table-template">
			<table class="widefat fixed" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<# _.each( columns, function( column ) { #>
							<th scope="col" class="manage-column column-{{ column }}">
								<# if ( 'date' === column ) { #>
									<?php esc_html_e( 'Date', 'custom-contact-forms' ); ?>
								<# } else { #>
									{{ column }}
								<# } #>
							</th>
						<# } ); #>
						<th scope="col" class="manage-column column-actions"></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<# _.each( columns, function( column ) { #>
							<th scope="col" class="manage-column column-{{ column }}">
								<# if ( 'date' === column ) { #>
									<?php esc_html_e( 'Date', 'custom-contact-forms' ); ?>
								<# } else { #>
									{{ column }}
								<# } #>
							</th>
						<# } ); #>
						<th scope="col" class="manage-column column-actions"></th>
					</tr>
				</tfoot>

				<tbody class="submission-rows">
					<tr>
						<td colspan="{{ columns.length + 1 }}">
							<div class="spinner" style="background: url( '<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>' ) no-repeat;"></div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="ccf-pagination"></div>
		</script>

		<script type="text/html" id="ccf-submission-row-template">
			<# _.each( currentColumns, function( column ) { #>
				<# if ( 'date' === column ) { #>
					<td colspan="1">{{ utils.getPrettyPostDate( submission.date_gmt ) }}</td>
				<# } else { #>
					<td colspan="1">
						<# if ( submission.data[column] ) { #>
							<# if ( submission.data[column] instanceof Object ) { var output = '', i = 0; #>
								<# if ( utils.isFieldDate( submission.data[column] ) ) { #>
									{{ utils.wordChop( utils.getPrettyFieldDate( submission.data[column] ), 30 ) }}
								<# } else if ( utils.isFieldName( submission.data[column] ) ) { #>
									{{ utils.wordChop( utils.getPrettyFieldName( submission.data[column] ), 30 ) }}
								<# } else if ( utils.isFieldAddress( submission.data[column] ) ) { #>
									{{ utils.wordChop( utils.getPrettyFieldAddress( submission.data[column] ), 30 ) }}
								<# } else if ( utils.isFieldEmailConfirm( submission.data[column] ) ) { #>
									{{ utils.wordChop( utils.getPrettyFieldEmailConfirm( submission.data[column] ), 30 ) }}
								<# } else if ( utils.isFieldFile( submission.data[column] ) ) { #>
									<a href="{{ submission.data[column].url }}">{{ submission.data[column].file_name }}</a>
								<# } else { #>
									<# for ( var key in submission.data[column] ) { if ( submission.data[column].hasOwnProperty( key ) ) {
										if ( submission.data[column][key] !== '' ) {
											if ( i > 0 ) {
												output += ', ';
											}
											output += submission.data[column][key];

											i++;
										}
									} } #>

									<# if ( output ) { #>
										{{ utils.wordChop( output, 30 ) }}
									<# } else { #>
										<span>-</span>
									<# } #>

								<# } #>
							<# } else { #>
								{{ utils.wordChop( submission.data[column], 30 ) }}
							<# } #>
						<# } else { #>
							<span>-</span>
						<# } #>
					</td>
				<# } #>
			<# } ); #>
			<td class="actions">
				<a href="#TB_inline?height=300&amp;width=400&amp;inlineId=submission-content" data-submission-date="{{ submission.date_gmt }}" data-submission-id="{{ submission.ID }}" class="view"  data-icon="&#xe601;"></a>
				<a class="delete" data-icon="&#xe602;"></a>

				<div class="submission-wrapper" id="ccf-submission-content-{{ submission.ID }}">
					<div class="ccf-submission-content">
						<# for ( column in submission.data ) { #>
							<div class="field-slug">
								{{ column }}
							</div>
							<div class="field-content">
								<# if ( submission.data[column] ) { #>
									<# if ( submission.data[column] instanceof Object ) { var output = '', i = 0; #>
										<# if ( utils.isFieldDate( submission.data[column] ) ) { #>
											{{ utils.getPrettyFieldDate( submission.data[column] ) }}
										<# } else if ( utils.isFieldName( submission.data[column] ) ) { #>
											{{ utils.getPrettyFieldName( submission.data[column] ) }}
										<# } else if ( utils.isFieldAddress( submission.data[column] ) ) { #>
											{{ utils.getPrettyFieldAddress( submission.data[column] ) }}
										<# } else if ( utils.isFieldEmailConfirm( submission.data[column] ) ) { #>
											{{ utils.getPrettyFieldEmailConfirm( submission.data[column] ) }}
										<# } else if ( utils.isFieldFile( submission.data[column] ) ) { #>
											<a href="{{ submission.data[column].url }}">{{ submission.data[column].file_name }}</a>
										<# } else { #>
											<# for ( var key in submission.data[column] ) { if ( submission.data[column].hasOwnProperty( key ) ) {
												if ( submission.data[column][key] !== '' ) {
													if ( i > 0 ) {
														output += ', ';
													}
													output += submission.data[column][key];

													i++;
												}
											} } #>

											<# if ( output ) { #>
												{{ output }}
											<# } else { #>
												-
											<# } #>
										<# } #>
									<# } else { #>
										{{ submission.data[column] }}
									<# } #>
								<# } else { #>
									<span>-</span>
								<# } #>
							</div>
						<# } #>
						<div class="field-slug">
							<?php esc_html_e( 'IP Address', 'custom-contact-forms' ); ?>
						</div>
						<div class="field-content">
							{{ submission.ip_address }}
						</div>
					</div>
				</div>
			</td>
		</script>


		<script type="text/html" id="ccf-no-submissions-row-template">
			<td colspan="{{ columns.length + 1 }}" class="no-submissions"><?php esc_html_e( 'There are no submissions.', 'custom-contact-forms' ); ?></td>
		</script>

		<script type="text/html" id="ccf-submissions-controller-template">
			<# var i = 0; _.each( columns, function( column ) {  #>

				<label for="ccf-column-{{ column }}">
					<input class="submission-column-checkbox" type="checkbox" id="ccf-column-{{ column }}" <# if ( i < 4 || 'date' === column ) { #>checked<# } #> value="{{ column }}">
					<# if ( 'date' === column ) { #>
						<?php esc_html_e( 'Date', 'custom-contact-forms' ); ?>
					<# } else { #>
						{{ column }}
					<# } #>
				</label>

			<# i++; }); #>
		</script>

		<?php

		do_action( 'ccf_underscore_templates' );
	}

	/**
	 * Add form manager button above editor
	 *
	 * @since 6.0
	 */
	public function action_media_buttons() {
		echo '<a href="#" class="button ccf-open-form-manager">' . esc_html__( 'Add Form', 'custom-contact-forms' ) . '</a>';
	}

	/**
	 * Enqueue post new/edit screen scripts/styles includes mce and form manager stuff
	 *
	 * @since 6.0
	 */
	public function action_admin_enqueue_scripts_css() {
		global $pagenow;

		if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$js_manager_path = '/build/js/form-manager.js';
				$js_mce_path = '/js/form-mce.js';
				$css_path = '/build/css/form-manager.css';
			} else {
				$js_manager_path = '/build/js/form-manager.min.js';
				$js_mce_path = '/build/js/form-mce.min.js';
				$css_path = '/build/css/form-manager.min.css';
			}

			$field_labels = apply_filters( 'ccf_field_labels', array(
				'single-line-text' => __( 'Single Line Text', 'custom-contact-forms' ),
				'dropdown' => __( 'Dropdown', 'custom-contact-forms' ),
				'checkboxes' => __( 'Checkboxes', 'custom-contact-forms' ),
				'radio' => __( 'Radio Buttons', 'custom-contact-forms' ),
				'paragraph-text' => __( 'Paragraph Text', 'custom-contact-forms' ),
				'hidden' => __( 'Hidden', 'custom-contact-forms' ),
				'file' => __( 'File Upload', 'custom-contact-forms' ),
			));

			$structure_field_labels = apply_filters( 'ccf_structure_field_labels', array(
				'html' => __( 'HTML', 'custom-contact-forms' ),
				'section-header' => __( 'Section Header', 'custom-contact-forms' ),
			));

			$special_field_labels = apply_filters( 'ccf_special_field_labels', array(
				'email' => __( 'Email', 'custom-contact-forms' ),
				'name' => __( 'Name', 'custom-contact-forms' ),
				'date' => __( 'Date/Time', 'custom-contact-forms' ),
				'website' => __( 'Website', 'custom-contact-forms' ),
				'address' => __( 'Address', 'custom-contact-forms' ),
				'phone' => __( 'Phone', 'custom-contact-forms' ),
				'recaptcha' => __( 'reCAPTCHA', 'custom-contact-forms' ),
			));

			wp_register_script( 'moment', plugins_url( '/bower_components/moment/moment.js', dirname( __FILE__ ) ) );

			wp_enqueue_script( 'ccf-form-manager', plugins_url( $js_manager_path, dirname( __FILE__ ) ), array( 'json2', 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'underscore', 'backbone', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-sortable', 'jquery-ui-droppable', 'wp-api', 'moment' ), '1.0', true );
			wp_localize_script( 'ccf-form-manager', 'ccfSettings', array(
				'nonce' => wp_create_nonce( 'ccf_nonce' ),
				'downloadSubmissionsNonce' => wp_create_nonce( 'ccf_download_submissions_nonce' ),
				'adminUrl' => esc_url_raw( admin_url() ),
				'fieldLabels' => $field_labels,
				'gmtOffset' => get_option( 'gmt_offset' ),
				'adminEmail' => sanitize_email( get_option( 'admin_email' ) ),
				'single' => ( 'ccf_form' === get_post_type() ) ? true : false,
				'postId' => ( ! empty( $_GET['post'] ) ) ? (int) $_GET['post'] : null,
				'postsPerPage' => (int) get_option( 'posts_per_page' ),
				'structureFieldLabels' => $structure_field_labels,
				'specialFieldLabels' => $special_field_labels,
				'maxFileSize' => floor( wp_max_upload_size() / 1000 / 1000 ),
				'noEmailFields' => esc_html__( 'You have no email fields', 'custom-contact-forms' ),
				'noNameFields' => esc_html__( 'You have no name fields', 'custom-contact-forms' ),
				'noApplicableFields' => esc_html__( 'You have no applicable fields', 'custom-contact-forms' ),
				'invalidDate' => esc_html__( 'Invalid date', 'custom-contact-forms' ),
				'allLabels' => array_merge( $field_labels, $structure_field_labels, $special_field_labels ),
				'fieldLabel' => esc_html__( 'Field Label', 'custom-contact-forms' ),
				'thickboxTitle' => esc_html__( 'Form Submission', 'custom-contact-forms' ),
				'pauseMessage' => esc_html__( 'This form is paused right now. Check back later!', 'custom-contact-forms' ),
				'skipFields' => apply_filters( 'ccf_no_submission_display_fields', array( 'html', 'section-header', 'recaptcha' ) ),
			) );

			wp_enqueue_style( 'ccf-form-manager', plugins_url( $css_path, dirname( __FILE__ ) ) );

			if ( apply_filters( 'ccf_enable_tinymce_previews', true ) && 'ccf_form' !== get_post_type() ) {
				wp_enqueue_script( 'ccf-form-mce', plugins_url( $js_mce_path, dirname( __FILE__ ) ), array( 'mce-view', 'jquery', 'ccf-form-manager' ), '1.0', true );
			}
		}
	}

	/**
	 * Return singleton instance of class
	 *
	 * @since 6.0
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

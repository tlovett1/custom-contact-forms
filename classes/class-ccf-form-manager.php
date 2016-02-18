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
			$css_path = '/assets/build/css/form-mce.css';
		} else {
			$css_path = '/assets/build/css/form-mce.min.css';
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
							<p><?php esc_html_e( 'There is an issue with synchronizing data. Please try the following:', 'custom-contact-forms' ); ?></p>

							<ul>
								<li><?php printf( __( 'Go to Settings &gt; <a href="%s">Permalinks</a> and click "Save Changes". This flushes your permalinks. If this fixes your problem, you are good to go!', 'custom-contact-forms' ), esc_url( admin_url( 'options-permalink.php' ) ) ); ?></li>
								<li><?php _e( 'Deactivate all other plugins and activate the TwentySixteen theme. If this fixes the problem, there is a plugin or theme conflict. Please report on <a href="http://github.com/tlovett1/custom-contact-form">Github</a> or the <a href="https://wordpress.org/support/plugin/custom-contact-forms">support forums</a>.', 'custom-contact-forms' ); ?></li>
							</ul>

							<p><?php _e( 'If neither of these things fix your problem, please report on <a href="http://github.com/tlovett1/custom-contact-form">Github</a> or the <a href="https://wordpress.org/support/plugin/custom-contact-forms">support forums</a>.', 'custom-contact-forms' ); ?></p>
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
					<a class="accordion-heading"><?php esc_html_e( 'Standard Fields', 'custom-contact-forms' ); ?></a>
					<div class="section-content">
						<div class="fields draggable-fields"></div>
					</div>
				</div>
				<div class="accordion-section" class="special-fields">
					<a class="accordion-heading"><?php esc_html_e( 'Special Fields', 'custom-contact-forms' ); ?></a>
					<div class="section-content">
						<div class="special-fields draggable-fields"></div>
					</div>
				</div>
				<div class="accordion-section">
					<a class="accordion-heading"><?php esc_html_e( 'Structure', 'custom-contact-forms' ); ?></a>
					<div class="section-content">
						<div class="structure-fields draggable-fields"></div>
					</div>
				</div>
				<div class="accordion-section">
					<a class="form-settings-heading"><?php esc_html_e( 'Form Settings', 'custom-contact-forms' ); ?></a>
				</div>
			</div>

			<div class="ccf-form-settings"></div>

			<div class="form-content" data-drag-message="<?php esc_html_e( '&larr; Drag fields from the left here.', 'custom-contact-forms' ); ?>">
			</div>

			<div class="right-sidebar ccf-field-sidebar accordion-container"></div>

			<div class="bottom">
				<?php if ( ! apply_filters( 'ccf_hide_ads', false ) ) : ?>
					<div class="left signup">
						<strong>Want free WP blogging tips, tutorials, and marketing tricks? </strong>
						<input type="email" class="email-signup-field" placeholder="Email">
						<button type="button" class="button signup-button">Sign me up!</button>
						<span class="signup-check">✓</span>
						<span class="signup-x">&times;</span>
					</div>
				<?php endif; ?>
				<input type="button" class="button insert-form-button" value="<?php esc_html_e( 'Insert into post', 'custom-contact-forms' ); ?>">
				<input type="button" class="button button-primary save-button" value="<?php esc_html_e( 'Save Form', 'custom-contact-forms' ); ?>">
				<div class="spinner" style="background: url( '<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>' ) no-repeat;"></div>
			</div>
		</script>

		<script type="text/html" id="ccf-empty-form-notification-row-template">
			<td colspan="4" class="no-notifications"><?php esc_html_e( 'No notifications yet.', 'custom-contact-forms' ); ?> <a class="add"><?php esc_html_e( 'Add one?', 'custom-contact-forms' ); ?></a></td>
		</script>

		<script type="text/html" id="ccf-existing-form-notification-table-row-template">
			<# if ( 'view' === context ) { #>
				<td>
					<# if ( '' !== notification.title ) { #>
						{{ notification.title }}
					<# } else { #>
						<?php esc_html_e( '(Untitled)', 'custom-contact-forms' ); ?>
					<# } #>

					<div class="actions">
						<a class="edit-notification"><?php esc_html_e( 'Edit', 'custom-contact-forms' ); ?></a> |
						<a class="delete-notification"><?php esc_html_e( 'Delete', 'custom-contact-forms' ); ?></a>
					</div>
				</td>
				<td>
					<# if ( 'default' === notification.subjectType ) { #>

						<?php echo wp_specialchars_decode( get_bloginfo( 'name' ) ); ?>: <?php esc_html_e( 'Form Submission', 'custom-contact-forms' ); ?> 
						<# if ( form.title.raw ) { #>
							<?php esc_html_e( 'to', 'custom-contact-forms' ); ?> {{ form.title.raw }}
						<# } #>
					<# } else if ( 'custom' === notification.subjectType ) { #>
						{{ notification.subject }}
					<# } else { #>
						<?php esc_html_e( 'Pulled from', 'custom-contact-forms' ); ?> {{ notification.subjectField }}
					<# } #>
				</td>
				<td>
					<# if ( ! notification.addresses.length || ( 1 === notification.addresses.length && ! notification.addresses[0].email && ! notification.addresses[0].field ) ) { #>
						<?php esc_html_e( 'No One', 'custom-contact-forms' ); ?>
					<# } else { #>
						<# var i = 0; _.each( notification.addresses, function( address ) { if ( ( 'custom' === address.type && address.email ) || ( 'field' === address.type && address.field ) ) { i++ #>
							<# if ( i > 1 ) { #>
								,
							<# } #>

							<# if ( 'custom' === address.type ) { #>
								{{ address.email }}
							<# } else { #>
								&quot;{{ address.field }}&quot; <?php esc_html_e( 'Field', 'custom-contact-forms' ); ?>
							<# }  #>
						<# } } ); #>
					<# } #>
				</td>
				<td>
					<# if ( notification.active ) { #>
						<span class="active-indicator">&bull;</span>
					<# } else { #>
						<span class="inactive-indicator">&bull;</span>
					<# } #>
				</td>
			<# } else { #>
				<td colspan="4">
					<a class="close-notification">&times;</a>

					<div class="left">
						<p class="email-notification-name">
							<label for="ccf_form_email_notification_title"><?php esc_html_e( 'Notification Title:', 'custom-contact-forms' ); ?></label>
							<input class="widefat form-email-notification-title" id="ccf_form_email_notification_title" name="email-notification-title" value="{{ notification.title }}">
						</p>

						<label for="ccf_form_email_notification_content"><?php esc_html_e( 'Email Content (HTML):', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf_form_email_notification_content" class="form-email-notification-content">{{ notification.content }}</textarea><br />
						<p class="variables">
							<strong><?php esc_html_e( 'Variables:', 'custom-contact-forms' ); ?></strong>  [all_fields] [ip_address] [current_date_time] 
							<span class="field-variables"></span>

						</p>
						<p class="email-notification-setting">
							<label for="ccf_form_email_notification_addresses"><?php esc_html_e( '"To" Email Addresses:', 'custom-contact-forms' ); ?></label>

							<div class="addresses">
							</div>
						</p>

						<p><em><?php _e( 'If you are not receiving email notifications, we highly recommend installing the <a href="https://wordpress.org/plugins/easy-wp-smtp/">WP Easy SMTP</a> plugin as there is probably an issue with emailing on your host.', 'custom-contact-forms' ); ?></em></p>
					</div>
					<div class="right">
						<p class="email-notification-active">
							<label for="ccf_form_email_notification_active"><strong><?php esc_html_e( 'Activate Notification:', 'custom-contact-forms' ); ?></strong></label>

							<select name="email_notification_active" class="form-email-notification-active" id="ccf_form_email_notification_active">
								<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
								<option value="1" <# if ( notification.active ) { #>selected<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
							</select>

							<span class="explain"><?php esc_html_e( 'Only active notifications will be sent.', 'custom-contact-forms' ); ?></span>
						</p>

						<p class="email-notification-setting">
							<label for="ccf_form_email_notification_from_type"><?php esc_html_e( '"From" Email Address Type:', 'custom-contact-forms' ); ?></label>
							<select name="email_notification_from_type" class="form-email-notification-from-type" id="ccf_form_email_notification_from_type">
								<option value="default"><?php esc_html_e( 'WordPress Default', 'custom-contact-forms' ); ?></option>
								<option value="custom" <# if ( 'custom' === notification.fromType ) { #>selected<# } #>><?php esc_html_e( 'Custom Email', 'custom-contact-forms' ); ?></option>
								<option value="field" <# if ( 'field' === notification.fromType ) { #>selected<# } #>><?php esc_html_e( 'Form Field', 'custom-contact-forms' ); ?></option>
							</select>

							<span class="explain"><?php esc_html_e( 'You can set the notification emails from address to be the WP default, a custom email address, or pull the address from a field in the form.', 'custom-contact-forms' ); ?></span>
						</p>

						<p class="email-notification-from-address">
							<label for="ccf_form_email_notification_from_address"><?php esc_html_e( 'Custom "From" Email Address:', 'custom-contact-forms' ); ?></label>
							<input class="widefat form-email-notification-from-address" id="ccf_form_email_notification_from_address" name="email-notification-from-address" value="{{ notification.fromAddress }}">
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
								<option value="field" <# if ( 'field' === notification.fromNameType ) { #>selected<# } #>><?php esc_html_e( 'Form Field', 'custom-contact-forms' ); ?></option>
							</select>

							<span class="explain"><?php esc_html_e( 'You can set the notification emails from name to be a custom name or pull the name from a field in the form.', 'custom-contact-forms' ); ?></span>
						</p>

						<p class="email-notification-from-name">
							<label for="ccf_form_email_notification_from_name"><?php esc_html_e( 'Custom "From" Name:', 'custom-contact-forms' ); ?></label>
							<input class="widefat form-email-notification-from-name" id="ccf_form_email_notification_from_name" name="email-notification-from-name" value="{{ notification.fromName }}">
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
								<option value="custom" <# if ( 'custom' === notification.subjectType ) { #>selected<# } #>><?php esc_html_e( 'Custom Subject', 'custom-contact-forms' ); ?></option>
								<option value="field" <# if ( 'field' === notification.subjectType ) { #>selected<# } #>><?php esc_html_e( 'Form Field', 'custom-contact-forms' ); ?></option>
							</select>

							<span class="explain"><?php esc_html_e( 'You can set the notification emails subject line to be the CCF default, custom text, or pull the subject from a field in the form.', 'custom-contact-forms' ); ?></span>
						</p>

						<p class="email-notification-subject">
							<label for="ccf_form_email_notification_subject"><?php esc_html_e( 'Custom Email Subject:', 'custom-contact-forms' ); ?></label>
							<input class="widefat form-email-notification-subject" id="ccf_form_email_notification_subject" name="email-notification-subject" value="{{ notification.subject }}">
						</p>

						<p class="email-notification-subject-field">
							<label for="ccf_form_email_notification_subject_field"><?php esc_html_e( 'Pull Email Subject Dynamically from Field:', 'custom-contact-forms' ); ?></label>
							<select name="email_notification_subject_field" class="form-email-notification-subject-field" id="ccf_form_email_notification_subject_field">
							</select>
						</p>
					</div>
				</td>
			<# } #>
		</script>

		<script type="text/html" id="ccf-form-notification-address-template">
			<select name="form_notification_address_type" class="form-notification-address-type" id="ccf_form_notification_address_type">
				<option value="custom"><?php esc_html_e( 'Custom Email', 'custom-contact-forms' ); ?></option>
				<option value="field" <# if ( 'field' === address.type ) { #>selected<# } #>><?php esc_html_e( 'Form Field', 'custom-contact-forms' ); ?></option>
			</select>

			<# if ( 'custom' === address.type ) { #>
				<input class="form-notification-address-email" type="text" placeholder="<?php esc_html_e( 'Email', 'custom-contact-forms' ); ?>" value="{{ address.email }}">
			<# } else if ( 'field' === address.type ) { #>
				<select name="form_notification_address_field" class="form-notification-address-field" id="ccf_form_notification_address_field"></select>
			<# } #>

			<a aria-hidden="true" data-icon="&#xe605;" class="add"></a>
			<a aria-hidden="true" data-icon="&#xe604;" class="delete"></a>
		</script>

		<script type="text/html" id="ccf-form-settings-template">
			<h3><?php esc_html_e( 'General', 'custom-contact-forms' ); ?></h3>

			<p>
				<label for="ccf_form_title"><?php esc_html_e( 'Form Title:', 'custom-contact-forms' ); ?></label>
				<input class="widefat form-title" id="ccf_form_title" name="title" type="text" value="{{ form.title.raw }}">
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
				<label for="ccf_form_button_class"><?php esc_html_e( 'Button Class:', 'custom-contact-forms' ); ?></label>
				<input class="widefat form-button-class" id="ccf_form_button_class" name="class" type="text" value="{{ form.buttonClass }}">
			</p>

			<p>
				<label for="ccf_form_theme"><?php esc_html_e( 'Form Theme:', 'custom-contact-forms' ); ?></label>

				<select name="theme" class="form-theme" id="ccf_form_theme">
					<option value=""><?php esc_html_e( 'None', 'custom-contact-forms' ); ?></option>
					<option value="light" <# if ( 'light' === form.theme ) { #>selected<# } #>><?php esc_html_e( 'Light', 'custom-contact-forms' ); ?></option>
					<option value="dark" <# if ( 'dark' === form.theme ) { #>selected<# } #>><?php esc_html_e( 'Dark', 'custom-contact-forms' ); ?></option>
				</select>

				<span class="explain"><?php esc_html_e( '"None" will have your form inherit styles from your theme.', 'custom-contact-forms' ); ?></span>
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

			<h3><?php esc_html_e( 'Email Notifications', 'custom-contact-forms' ); ?></h3>

			<div class="ccf-form-notifications">
				<table cellpadding="0" cellspacing="0">
					<thead>
						<tr>
							<th class="name"><?php esc_html_e( 'Title', 'custom-contact-forms' ); ?></th>
							<th class="subject"><?php esc_html_e( 'Subject', 'custom-contact-forms' ); ?></th>
							<th class="to"><?php esc_html_e( 'To', 'custom-contact-forms' ); ?></th>
							<th class="active"><?php esc_html_e( 'Active', 'custom-contact-forms' ); ?></th>
						</tr>
					</thead>
					<tbody class="rows">

					</tbody>
					<tfoot>
						<tr>
							<th class="name"><?php esc_html_e( 'Title', 'custom-contact-forms' ); ?></th>
							<th class="subject"><?php esc_html_e( 'Subject', 'custom-contact-forms' ); ?></th>
							<th class="to"><?php esc_html_e( 'To', 'custom-contact-forms' ); ?></th>
							<th class="active"><?php esc_html_e( 'Active', 'custom-contact-forms' ); ?></th>
						</tr>
					</tfoot>
				</table>

				<a class="add-notification button"><?php esc_html_e( 'New Notification', 'custom-contact-forms' ); ?></a>

				<p>
					<span class="explain"><?php esc_html_e( 'For notification changes to take affect (updating, adding, deleting, etc.), you will need to save the form.', 'custom-contact-forms' ); ?></span>
				</p>
			</div>

			<h3><?php esc_html_e( 'Post Creation', 'custom-contact-forms' ); ?></h3>
			<p><?php esc_html_e( 'You can have Custom Contact Forms create a post (or custom post type) whenever someone submits your form.', 'custom-contact-forms' ); ?></p>

			<p>
				<label for="ccf_form_post_creation"><?php esc_html_e( 'Enable Post Creation:', 'custom-contact-forms' ); ?></label>

				<select name="form_post_creation" class="form-post-creation" id="ccf_form_post_creation">
					<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
					<option value="1" <# if ( form.postCreation ) { #>selected<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
				</select>
			</p>

			<p class="post-creation-mapping-field">
				<label for="ccf_form_post_creation_type"><?php esc_html_e( 'Post Type:', 'custom-contact-forms' ); ?></label>

				<select name="form_post_creation_type" class="form-post-creation-type" id="ccf_form_post_creation_type">
					<?php $post_types = get_post_types( array(), 'objects' ); foreach ( $post_types as $post_type ) : ?>
						<option <# if ( '<?php echo esc_attr( $post_type->name ); ?>' === form.postCreationType ) { #>selected<# } #> value="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_html( $post_type->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p class="post-creation-mapping-field">
				<label for="ccf_form_post_creation_status"><?php esc_html_e( 'Post Status:', 'custom-contact-forms' ); ?></label>

				<select name="form_post_creation_status" class="form-post-creation-status" id="ccf_form_post_creation_status">
					<?php $post_statuses = get_post_statuses(); foreach ( $post_statuses as $post_status => $post_status_name ) : ?>
						<option <# if ( '<?php echo esc_attr( $post_status ); ?>' === form.postCreationStatus ) { #>selected<# } #> value="<?php echo esc_attr( $post_status ); ?>"><?php echo esc_html( $post_status_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<div class="post-creation-mapping-field post-creation-mapping-wrapper">
				<label for="ccf_form_post_creation_type"><?php esc_html_e( 'Field Mappings:', 'custom-contact-forms' ); ?></label>

				<div class="post-creation-mapping">
				</div>

				<span class="explain"><?php esc_html_e( 'You can map as few or as many fields as you like. However, if no form fields are mapped, no post will be created. Mapping a field to post_title is required.', 'custom-contact-forms' ); ?></span>
			</div>
		</script>

		<script type="text/html" id="ccf-post-field-mapping">
			<select id="ccf_post_field_mapping_form_field" class="field-form-field">
			</select>

			<select id="ccf_post_field_mapping_post_field" class="field-post-field">
			</select>

			<# if ( 'custom_field' === mapping.postField ) { #>
				<input class="field-custom-field-key" type="text" placeholder="<?php esc_html_e( 'Custom Field Key', 'custom-contact-forms' ); ?>" value="{{ mapping.customFieldKey }}">
			<# } #>
			
			<a aria-hidden="true" data-icon="&#xe605;" class="add"></a>
			<a aria-hidden="true" data-icon="&#xe604;" class="delete"></a>
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
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading">Basic</a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-file-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading">Basic</a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-recaptcha-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading">Basic</a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-website-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading">Basic</a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-html-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-html"><?php esc_html_e( 'HTML Content:', 'custom-contact-forms' ); ?></label>
						<textarea id="ccf-field-html" class="field-html">{{ field.html }}</textarea>
					</div>
				</div>
			</div>
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-section-header-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-paragraph-text-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-hidden-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-name-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-date-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
						<label for="ccf-field-show-date"><?php esc_html_e( 'Enable Date Select', 'custom-contact-forms' ); ?></label>
					</div>
					<div>
						<input type="checkbox" <# if ( field.showTime ) { #>checked="checked"<# } #> class="field-show-time" value="1" id="ccf-field-show-time">
						<label for="ccf-field-show-time"><?php esc_html_e( 'Enable Time Select', 'custom-contact-forms' ); ?></label>
					</div>
					<# if ( field.showDate ) { #>
						<div>
							<label for="ccf-date-format"><?php esc_html_e( 'Date Format:', 'custom-contact-forms' ); ?></label>
							<select id="ccf-date-format" class="field-date-format">
								<option value="mm/dd/yyyy">mm/dd/yyyy</option>
								<option <# if ( 'dd/mm/yyyy' === field.dateFormat ) { #>selected="selected"<# } #>>dd/mm/yyyy</option>
							</select>
						</div>
					<# } #>
				</div>
			</div>
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
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
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-phone-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-placeholder"><?php esc_html_e( 'Placeholder Text:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-placeholder" class="field-placeholder" type="text" value="{{ field.placeholder }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-address-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-email-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
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
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
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

		<script type="text/html" id="ccf-field-conditional-template">
			<a aria-hidden="true" data-icon="&#xe605;" class="add"></a>
			<a aria-hidden="true" data-icon="&#xe604;" class="delete"></a>

			<select class="conditional-field">
			</select>

			<select class="conditional-compare">
				<option <# if ( 'is' === conditional.compare ) { #>selected<# } #> value="is"><?php esc_html_e( 'is', 'custom-contact-forms' ); ?></option>
				<option <# if ( 'is-not' === conditional.compare ) { #>selected<# } #> value="is-not"><?php esc_html_e( 'is not', 'custom-contact-forms' ); ?></option>
				<option <# if ( 'greater-than' === conditional.compare ) { #>selected<# } #> value="greater-than"><?php esc_html_e( '>', 'custom-contact-forms' ); ?></option>
				<option <# if ( 'less-than' === conditional.compare ) { #>selected<# } #> value="less-than"><?php esc_html_e( '<', 'custom-contact-forms' ); ?></option>
				<option <# if ( 'contains' === conditional.compare ) { #>selected<# } #> value="contains"><?php esc_html_e( 'contains', 'custom-contact-forms' ); ?></option>
			</select>
			<input class="conditional-value" placeholder="<?php esc_attr_e( 'Field value', 'custom-contact-forms' ); ?>" type="text" value="{{ conditional.value }}">
		</script>

		<script type="text/html" id="ccf-dropdown-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-radio-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="ccf-checkboxes-template">
			<div class="accordion-section <# if ( 'basic' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Basic', 'custom-contact-forms' ); ?></a>
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
			<div class="accordion-section <# if ( 'advanced' === startPanel ) { #>expanded<# } #>">
				<a class="accordion-heading"><?php esc_html_e( 'Advanced', 'custom-contact-forms' ); ?></a>
				<div class="section-content">
					<div>
						<label for="ccf-field-class-name"><?php esc_html_e( 'Class Name:', 'custom-contact-forms' ); ?></label>
						<input id="ccf-field-class-name" class="field-class-name" type="text" value="{{ field.className }}">
					</div>
					<div>
						<label for="ccf-field-conditionals-enabled"><?php esc_html_e( 'Enable Conditional Logic:', 'custom-contact-forms' ); ?></label>
						<select id="ccf-field-conditionals-enabled" class="field-conditionals-enabled">
							<option value="0"><?php esc_html_e( 'No', 'custom-contact-forms' ); ?></option>
							<option value="1" <# if ( field.conditionalsEnabled ) { #>selected="selected"<# } #>><?php esc_html_e( 'Yes', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
					<div class="<# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
						<select class="field-conditional-type">
							<option value="hide"><?php esc_html_e( 'Hide', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'show' === field.conditionalType ) { #>selected="selected"<# } #> value="show"><?php esc_html_e( 'Show', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'this field if', 'custom-contact-forms' ); ?>

						<select class="field-conditional-fields-required">
							<option value="all"><?php esc_html_e( 'All', 'custom-contact-forms' ); ?></option>
							<option <# if ( 'any' === field.conditionalFieldsRequired ) { #>selected="selected"<# } #> value="any"><?php esc_html_e( 'Any', 'custom-contact-forms' ); ?></option>
						</select>

						<?php esc_html_e( 'of these conditions are true:', 'custom-contact-forms' ); ?>
					</div>
					<div class="conditionals <# if ( ! field.conditionalsEnabled ) { #>hide<# } #>">
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
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
			<input disabled type="text" placeholder="{{ field.placeholder }}" value="{{ field.value }}">
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-file-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
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
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
			<img class="recaptcha-preview-img" src="<?php echo plugins_url( 'img/recaptcha.png', dirname( __FILE__ ) ); ?>">
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-paragraph-text-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
			<textarea placeholder="{{ field.placeholder }}" disabled>{{ field.value }}</textarea>
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-dropdown-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
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
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
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
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
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
			<# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #>
			<# if ( typeof mce !== 'undefined' ) { #>
				{{{ field.html }}}
			<# } else { #>
				<pre>&lt;pre&gt;<?php esc_html_e( 'Arbitrary block of HTML.', 'custom-contact-forms' ); ?>&lt;/pre&gt;</pre>
			<# } #>
		</script>

		<script type="text/html" id="ccf-section-header-preview-template">
			<# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #>
			<div class="heading">
				<# if ( field.heading ) { #>{{ field.heading }}<# } else { #><?php esc_html_e( 'Section Heading', 'custom-contact-forms' ); ?><# } #>
			</div>
			<div class="subheading"><# if ( field.subheading ) { #>{{ field.subheading }}<# } else { #><?php esc_html_e( 'This is the sub-heading text.', 'custom-contact-forms' ); ?><# } #></div>
		</script>

		<script type="text/html" id="ccf-name-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
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
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
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
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
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
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
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
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
			<input placeholder="<# if ( field.placeholder ) { #>{{ field.placeholder }}<# } else { #>http://<# } #>" disabled type="text" value="{{ field.value }}">
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-phone-preview-template">
			<label>{{ field.label }} <# if ( field.required ) { #><span class="required">*</span><# } #> <# if ( field.conditionalsEnabled ) { #><span class="conditionals-enabled">if</span><# } #></label>
			<input placeholder="<# if ( field.placeholder ) { #>{{ field.placeholder }}<# } else if ( 'us' === field.phoneFormat ) { #>(301) 101-8976<# } #>" disabled type="text" value="{{ field.value }}">
			<# if ( field.description ) { #>
				<div class="field-description">{{ field.description }}</div>
			<# } #>
		</script>

		<script type="text/html" id="ccf-existing-form-table-row-template">

			<td>{{ form.id }}</td>
			<td>
				<a class="edit edit-form title" data-view="form-pane" data-form-id="{{ form.id }}" href="#form-pane-{{ form.id }}"><# if ( form.title.raw ) { #>{{ form.title.raw }}<# } else { #>{{ '<?php esc_html_e( '(No title)', 'custom-contact-forms' ); ?>' }}<# } #></a>
				<div class="actions">
					<a class="edit edit-form" data-view="form-pane" data-form-id="{{ form.id }}" href="#form-pane-{{ form.id }}"><?php esc_html_e( 'Edit', 'custom-contact-forms' ); ?></a> |
					<a class="insert-form-button"><?php esc_html_e( 'Insert into post', 'custom-contact-forms' ); ?></a> |
					<a class="duplicate"><?php esc_html_e( 'Duplicate form', 'custom-contact-forms' ); ?></a> |
					<a class="delete"><?php esc_html_e( 'Trash', 'custom-contact-forms' ); ?></a>
				</div>
			</td>
			<td>
				{{ utils.getPrettyPostDate( form.date_gmt ) }}
			</td>
			<td>
				{{ form.author.user_login }}
			</td>
			<td>
				{{ form.fields.length }}
			</td>
			<td>
				{{ form.submissions }}
			</td>
		</script>

		<script type="text/html" id="ccf-form-mce-preview">
			<div class="ccf-form-preview form-id-{{ form.id }}">
				<# if ( form.title.raw ) { #>
					<h2>{{ form.title.raw }}</h2>
				<# } #>

				<# if ( form.description && form.description != '' ) { #>
					<p>{{ form.description }}</p>
				<# } #>

				<# if ( form.fields ) { #>
					<# _.each( form.fields, function( field ) { #>
						<div class="field {{ field.type }} field-{{ field.id }}">
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
			<#
			if ( ! submission.fields || '' == submission.fields ) {
				submission.fields = {};
			}

			_.each( currentColumns, function( column ) { #>
				<# if ( 'date' === column ) { #>
					<td colspan="1">{{ utils.getPrettyPostDate( submission.date_gmt ) }}</td>
				<# } else { #>
					<td colspan="1">
						<# if ( submission.data[column] ) { #>
							<# if ( submission.data[column] instanceof Object ) { var output = '', i = 0; #>
								<# if ( utils.isFieldDate( submission.data[column], submission.fields[column] ) ) { #>
									{{ utils.wordChop( utils.getPrettyFieldDate( submission.data[column], submission.fields[column] ), 30 ) }}
								<# } else if ( utils.isFieldName( submission.data[column], submission.fields[column] ) ) { #>
									{{ utils.wordChop( utils.getPrettyFieldName( submission.data[column], submission.fields[column] ), 30 ) }}
								<# } else if ( utils.isFieldAddress( submission.data[column], submission.fields[column] ) ) { #>
									{{ utils.wordChop( utils.getPrettyFieldAddress( submission.data[column], submission.fields[column] ), 30 ) }}
								<# } else if ( utils.isFieldEmailConfirm( submission.data[column], submission.fields[column] ) ) { #>
									{{ utils.wordChop( utils.getPrettyFieldEmailConfirm( submission.data[column], submission.fields[column] ), 30 ) }}
								<# } else if ( utils.isFieldFile( submission.data[column], submission.fields[column] ) ) { #>
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
				<a href="#TB_inline?height=300&amp;width=400&amp;inlineId=submission-content" data-submission-date="{{ submission.date_gmt }}" data-submission-id="{{ submission.id }}" class="view"  data-icon="&#xe601;"></a>
				<a class="delete" data-icon="&#xe602;"></a>

				<div class="submission-wrapper" id="ccf-submission-content-{{ submission.id }}">
					<div class="ccf-submission-content">
						<# for ( column in submission.data ) { #>
							<div class="field-slug">
								{{ column }}
							</div>
							<div class="field-content">
								<# if ( submission.data[column] ) { #>
									<# if ( submission.data[column] instanceof Object ) { var output = '', i = 0; #>
										<# if ( utils.isFieldDate( submission.data[column], submission.fields[column] ) ) { #>
											{{ utils.getPrettyFieldDate( submission.data[column], submission.fields[column] ) }}
										<# } else if ( utils.isFieldName( submission.data[column], submission.fields[column] ) ) { #>
											{{ utils.getPrettyFieldName( submission.data[column], submission.fields[column] ) }}
										<# } else if ( utils.isFieldAddress( submission.data[column], submission.fields[column] ) ) { #>
											{{ utils.getPrettyFieldAddress( submission.data[column], submission.fields[column] ) }}
										<# } else if ( utils.isFieldEmailConfirm( submission.data[column], submission.fields[column] ) ) { #>
											{{ utils.getPrettyFieldEmailConfirm( submission.data[column], submission.fields[column] ) }}
										<# } else if ( utils.isFieldFile( submission.data[column], submission.fields[column] ) ) { #>
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
				$js_manager_path = '/assets/build/js/form-manager.js';
				$js_mce_path = '/assets/js/form-mce.js';
				$css_path = '/assets/build/css/form-manager.css';
			} else {
				$js_manager_path = '/assets/build/js/form-manager.min.js';
				$js_mce_path = '/assets/build/js/form-mce.min.js';
				$css_path = '/assets/build/css/form-manager.min.css';
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
				'section-header' => __( 'Section', 'custom-contact-forms' ),
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

			wp_register_script( 'moment', plugins_url( '/bower_components/moment/moment.js', dirname( __FILE__ ) ), array(), CCF_VERSION );

			if ( ! wp_script_is( 'wp-api', 'registered' ) ) {
				wp_register_script( 'wp-api', plugins_url( '/wp-api/wp-api.js', dirname( __FILE__ ) ), array(), CCF_VERSION );
			}

			$site_url_parsed = parse_url( site_url() );
			$home_url_parsed = parse_url( home_url() );

			if ( $site_url_parsed['host'] === $home_url_parsed['host'] && strtolower( $site_url_parsed['scheme'] ) === strtolower( $home_url_parsed['scheme'] ) ) {
				$api_root = home_url( 'wp-json' );
			} else {
				$api_root = site_url( 'wp-json' );
			}

			wp_enqueue_script( 'ccf-form-manager', plugins_url( $js_manager_path, dirname( __FILE__ ) ), array( 'json2', 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'underscore', 'backbone', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-sortable', 'jquery-ui-droppable', 'wp-api', 'moment' ), CCF_VERSION, true );
			wp_localize_script( 'ccf-form-manager', 'ccfSettings', array(
				'apiRoot' => $api_root,
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
				'noAvailableFields' => esc_html__( 'No available fields', 'custom-contact-forms' ),
				'noNameFields' => esc_html__( 'You have no name fields', 'custom-contact-forms' ),
				'noApplicableFields' => esc_html__( 'You have no applicable fields', 'custom-contact-forms' ),
				'chooseFormField' => esc_html__( 'Choose a Form Field', 'custom-contact-forms' ),
				'invalidDate' => esc_html__( 'Invalid date', 'custom-contact-forms' ),
				'allLabels' => array_merge( $field_labels, $structure_field_labels, $special_field_labels ),
				'fieldLabel' => esc_html__( 'Field Label', 'custom-contact-forms' ),
				'thickboxTitle' => esc_html__( 'Form Submission', 'custom-contact-forms' ),
				'pauseMessage' => esc_html__( 'This form is paused right now. Check back later!', 'custom-contact-forms' ),
				'skipFields' => apply_filters( 'ccf_no_submission_display_fields', array( 'html', 'section-header', 'recaptcha' ) ),
				'choosePostField' => esc_html__( 'Choose a Post Field', 'custom-contact-forms' ),
				'postFields' => array(
					'single' => array(
						'post_title' => esc_html__( 'Post Title', 'custom-contact-forms' ),
						'post_content' => esc_html__( 'Post Content', 'custom-contact-forms' ),
						'post_excerpt' => esc_html__( 'Post Excerpt', 'custom-contact-forms' ),
						'post_date' => esc_html__( 'Post Date', 'custom-contact-forms' ),
					),
					'repeatable' => array(
						'post_tag' => esc_html__( 'Post Tags', 'custom-contact-forms' ),
						'custom_field' => esc_html__( 'Custom Field', 'custom-contact-forms' ),
					),
				),
			) );

			wp_enqueue_style( 'ccf-form-manager', plugins_url( $css_path, dirname( __FILE__ ) ), array(), CCF_VERSION );

			if ( apply_filters( 'ccf_enable_tinymce_previews', true ) && 'ccf_form' !== get_post_type() ) {
				wp_enqueue_script( 'ccf-form-mce', plugins_url( $js_mce_path, dirname( __FILE__ ) ), array( 'mce-view', 'jquery', 'ccf-form-manager' ), CCF_VERSION, true );
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

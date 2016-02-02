<?php
/**
 * Class CCF_Widget
 *
 * This widget simply lets you display a form in a sidebar.
 *
 * @since 6.4
 */
class CCF_Widget extends WP_Widget {

	/**
	 * Initialize the widget
	 *
	 * @since 6.4
	 */
	public function __construct() {
		$options = array( 'description' => esc_html__( 'Add a custom contact form to a sidebar.', 'custom-contact-forms' ) );
		parent::__construct( 'custom-contact-forms', esc_html__( 'Custom Contact Form', 'custom-contact-forms' ), $options );
	}

	/**
	 * Display widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( empty( $instance['form_id'] ) ) {
			return;
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		ccf_output_form( $instance['form_id'] );

		echo $args['after_widget'];
	}

	/**
	 * Display widget management form
	 *
	 * @param array $instance
	 * @return string|void
	 */
	public function form( $instance ) {
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$current_form_id = ( ! empty( $instance['form_id'] ) ) ? $instance['form_id'] : 0;

		$forms_query = new WP_Query( array(
			'post_type' => 'ccf_form',
			'posts_per_page' => apply_filters( 'ccf_max_widget_forms', 5000 ),
			'no_found_rows' => true,
			'post_status' => 'publish',
			'fields' => 'ids',
		) );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php esc_html_e( 'Title:', 'custom-contact-forms' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'form_id' ); ?>">
				<?php esc_html_e( 'Choose a form:', 'custom-contact-forms' ); ?>
			</label><br>
			<select id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>">
				<?php if ( $forms_query->have_posts() ) : ?>
					<?php foreach ( $forms_query->posts as $form_id ) : ?>
						<option <?php selected( $current_form_id, $form_id ); ?> value="<?php echo (int) $form_id; ?>">
							<?php
							$title = get_the_title( $form_id );
							if ( empty( $title ) ) {
								esc_html_e( 'Untitled', 'custom-contact-forms' );
							} else {
								echo esc_html( $title );
							}
							?>
							(ID: <?php echo (int) $form_id; ?>)
						</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['form_id'] = absint( $new_instance['form_id'] );

		return $instance;
	}
}

<?php

class CCF_Ads {

	/**
	 * Placeholder method
	 *
	 * @since 6.9.4
	 */
	public function __construct() {}

	/**
	 * Setup hooks
	 *
	 * @since 6.9.4
	 */
	public function setup() {
		add_action( 'admin_notices', array( $this, 'show_ad' ) );
		add_action( 'init', array( $this, 'process_submission' ) );
	}

	public function process_submission() {
		if ( ! empty( $_POST['ccf_subscribe'] ) && ! empty( $_POST['email'] ) ) {
			$request = wp_remote_request( 'http://taylorlovett.us8.list-manage.com/subscribe/post?u=66118f9a5b0ab0414e83f043a&amp;id=b4ed816a24', array(
				'method' => 'post',
				'body' => array(
					'EMAIL' => $_POST['email'],
				)
			));

			update_option( 'ccf_subscribed', 1 );
		}
	}

	/**
	 * Output ad
	 *
	 * @since 6.9.4
	 */
	public function show_ad() {
		global $pagenow;

		if ( 'edit.php' === $pagenow || 'post-new.php' === $pagenow ) {
			if ( empty( $_GET['post_type'] ) || 'ccf_form' !== $_GET['post_type'] ) {
				return;
			}
		}

		if ( 'post.php' === $pagenow ) {
			if ( 'ccf_form' !== get_post_type() ) {
				return;
			}
		}

		if ( 'post.php' !== $pagenow && 'edit.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
			return;
		}

		$subscribed = get_option( 'ccf_subscribed' );

		if ( ! empty( $subscribed ) ) {
			return;
		}
		
		?>
		<div class="updated update-nag ccf-subscribe">
			<form method="post">
				<p>
					<?php if ( empty( $_POST['ccf_subscribe'] ) || empty( $_POST['email'] ) ) : ?>
						WordPress exclusive tutorials, blogging tips, plugins, and more. 
						<input type="email" name="email">
						<input type="hidden" name="ccf_subscribe" value="1">
						<input type="submit" class="button button-primary" value="Sign Me Up">
					<?php else : ?>
						Check your email to confirm your subscription!
					<?php endif; ?>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Return singleton instance of class
	 *
	 * @since 6.9.4
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
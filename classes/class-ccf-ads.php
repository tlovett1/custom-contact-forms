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
		if ( apply_filters( 'ccf_hide_ads', false ) ) {
			return;
		}

		if ( ! empty( $_POST['ccf_subscribe'] ) && ! empty( $_POST['email'] ) ) {
			$request = wp_remote_request( 'http://taylorlovett.us8.list-manage.com/subscribe/post?u=66118f9a5b0ab0414e83f043a&amp;id=b4ed816a24', array(
				'method' => 'post',
				'body' => array(
					'EMAIL' => $_POST['email'],
				),
			));

			update_option( 'ccf_subscribed', 1 );
		} elseif ( ! empty( $_POST['ccf_unsubscribe'] ) ) {
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

		if ( apply_filters( 'ccf_hide_ads', false ) ) {
			return;
		}

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
			<div class="ad-wrap">
				<?php if ( empty( $_POST['ccf_subscribe'] ) || empty( $_POST['ccf_unsubscribe'] ) ) : ?>
					WordPress exclusive tutorials, blogging tips, themes, plugins, and more.
					<form method="post">
						<input type="email" name="email">
						<input type="hidden" name="ccf_subscribe" value="1">
						<input type="submit" class="button button-primary" value="Sign Me Up">
					</form>
					<form method="post">
						<input type="hidden" name="ccf_unsubscribe" value="1">
						<input type="submit" class="button" value="Not Interested">
					</form>
				<?php else : ?>
					Check your email to confirm your subscription!
				<?php endif; ?>
			</div>
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

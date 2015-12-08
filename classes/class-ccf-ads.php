<?php

class CCF_Ads {

	/**
	 * Setup ads
	 *
	 * @since  6.9.1
	 */
	public function setup() {
		add_action( 'admin_notices', array( $this, 'ad_nag' ) );
		add_action( 'init', array( $this, 'hide_ads' ) );
	}

	public function hide_ads() {
		if ( ! empty( $_POST['ccf_hide_ads'] ) ) {
			update_option( 'ccf_hide_ads', true );
		} elseif ( ! empty( $_POST['ccf_hosting_signup'] ) && ! empty( $_POST['email'] ) ) {

			$payload = array(
				'email_address' => $_POST['email'],
				'status' => 'subscribed',
			);

			$request = wp_remote_request( 'https://test:b7d9f4eff3654c742a805ed6d8ebc304-us8@us8.api.mailchimp.com/3.0/lists/b4ed816a24/members', array(
				'method' => 'POST',
				'body' => json_encode( $payload ),
			) );

			$headers = 'From: CCF User <' . $_POST['email'] . '>' . "\r\n";
			wp_mail( 'tlovett88@gmail.com', 'I Want Hosting', 'Hey! I\'d love some info on WordPress hosting. My email is ' . $_POST['email'], $headers );

			update_option( 'ccf_hide_ads', true );
		}
	}

	/**
	 * Show ads
	 *
	 * @since 6.9.1
	 */
	public function ad_nag() {
		$hide_ads = get_option( 'ccf_hide_ads', false);

		if ( $hide_ads ) {
			return;
		}
		?>

		<form method="post" action="" class="update-nag ccf-hosting-offer">
			<p>
				<h3>Is Your WordPress Site Running Slowly</h3>

				<p>Sign up for affordable no hassle, managed WordPress hosting including:</p>

				<ul>
					<li>Daily backups taken automatically.</li>
					<li>High speed caching.</li>
					<li>Dedicated hosting. You won't share space with any other websites.</li>
					<li>Optimized server settings for WordPress.</li>
					<li>Free support 9-5pm EDT Monday - Friday. Emergency support 24/7.</li>
					<li>100% uptime.</li>
					<li>Latest versions of PHP, MySQL, and WordPress.
					<li>Free migration to new platform.</li>
				</ul>
			</p>

			<p><input name="email" type="text" placeholder="Email address" /> <input name="ccf_hosting_signup" class="button button-primary" type="submit" value="Get more info" /> <input name="ccf_hide_ads" class="button" type="submit" value="No thanks" /></p>
		</form>

		<?php
	}

	/**
	 * Return singleton instance of class
	 *
	 * @since 6.1
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

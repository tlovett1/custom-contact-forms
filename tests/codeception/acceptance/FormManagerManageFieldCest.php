<?php

require_once( 'AcceptanceTester.php' );

use WPCC\Helper\PageObject\LoginPage;

class FormManagerManageFieldCest {

	/**
	 * The temporary user.
	 *
	 * @since 7.0
	 */
	private $_user;

	/**
	 * Logins temp user and creates him if it doesn't exist yet.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function _before( AcceptanceTester $I ) {
		if ( ! $this->_user ) {
			$this->_factory = \WPCC\Helper\Factory::create();
			$this->_user = $this->_factory->user->createAndGet( array(
				'user_pass' => 'test',
				'role'      => 'administrator',
			) );
		}

		LoginPage::of( $I )->login( $this->_user->user_login, 'test' );
	}
	
}

<?php

require_once( 'AcceptanceTester.php' );

use WPCC\Helper\PageObject\LoginPage;

class FormManagerCest {

	/**
	 * The temporary user.
	 *
	 * @since 7.0
	 */
	private $_user;

	/**
	 * Logins temp user and creates him if it doesn't exist yet.
	 *
	 * @since 7.0
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

	/**
	 * Tests that form manager opens on the new post page
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function formManagerOpensNewPort( AcceptanceTester $I ) {
		$I->wantTo( 'Ensure the form manager opens in the new post page' );

		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );

		$I->see( 'Click on a field to edit it' );
	}

	/**
	 * Tests that form manager opens on the new form page
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function formManagerOpensNewForm( AcceptanceTester $I ) {
		$I->wantTo( 'Ensure the form manager opens in the new form page' );

		$I->amOnPage( admin_url( 'post-new.php?post_type=ccf_form' ) );

		$I->click( 'Manage Form' );

		$I->see( 'Click on a field to edit it' );
	}

}
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
	 * Ensure standard fields show
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function createFields( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );

		$I->wantTo( 'Create one of each field' );
		
		$I->dragAndDrop( '.single-line-text.ui-draggable', '.form-content' );
		$I->see( '.form-content .single-line-text' );

		/*$I->dragAndDrop( '.left-sidebar .dropdown', '.form-content' );
		$I->see( '.form-content .single-line-text' );
		$I->see( '.form-content .dropdown' );

		$I->dragAndDrop( '.left-sidebar .checkboxes', '.form-content' );
		$I->see( '.form-content .single-line-text' );
		$I->see( '.form-content .dropdown' );
		$I->see( '.form-content .checkboxes' );*/
	}
}

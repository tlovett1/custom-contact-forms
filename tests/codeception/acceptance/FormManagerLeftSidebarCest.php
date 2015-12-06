<?php

require_once( 'AcceptanceTester.php' );

use WPCC\Helper\PageObject\LoginPage;

class FormManagerLeftSidebarCest {

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
	public function standardFieldsShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );

		$I->wantTo( 'Ensure only standard fields show by default' );
		$I->see( 'Single Line Text' );
		$I->dontSee( 'Date/Time' );
		$I->dontSee( 'Section Header' );
		$I->dontSee( 'On form completion' );
		$I->dontSee( 'Send Email Notifications' );
	}

	/**
	 * Ensure special fields show
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function specialFieldsShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );

		$I->wantTo( 'Ensure special fields show properly' );

		$I->click( 'Special Fields' );
		$I->dontSee( 'Single Line Text' );
		$I->see( 'Date/Time' );
		$I->dontSee( 'Section Header' );
		$I->dontSee( 'On form completion' );
		$I->dontSee( 'Send Email Notifications' );
	}

	/**
	 * Ensure structure fields show
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function structureFieldsShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );

		$I->wantTo( 'Ensure structure fields show properly' );

		$I->click( 'Structure' );
		$I->dontSee( 'Single Line Text' );
		$I->dontSee( 'Date/Time' );
		$I->see( 'Section Header' );
		$I->dontSee( 'On form completion' );
		$I->dontSee( 'Send Email Notifications' );
	}

	/**
	 * Ensure form settings show
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function formSettingsShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );

		$I->wantTo( 'Ensure form settings show properly' );

		$I->click( 'Form Settings' );
		$I->dontSee( 'Single Line Text' );
		$I->dontSee( 'Date/Time' );
		$I->dontSee( 'Section Header' );
		$I->see( 'On form completion' );
		$I->dontSee( 'Send Email Notifications' );
	}

	/**
	 * Ensure form settings show
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function formNotificationsShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );

		$I->wantTo( 'Ensure form notifications show properly' );

		$I->click( 'Form Notifications' );
		$I->dontSee( 'Single Line Text' );
		$I->dontSee( 'Date/Time' );
		$I->dontSee( 'Section Header' );
		$I->dontSee( 'On form completion' );
		$I->see( 'Send Email Notifications' );
	}

	/**
	 * Ensure form settings on completion shows
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function formSettingsOnCompletionShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );

		$I->wantTo( 'Ensure form settings show properly' );
		
		$I->wantTo( 'Ensure form settings on form completion works properly' );

		$I->click( 'Form Settings' );
		$I->see( 'Completion Message' );
		$I->dontSee( 'Redirect URL' );
		$I->selectOption( 'On form completion', 'Redirect' );
		$I->see( 'Redirect URL' );
	}

	/**
	 * Ensure form notifications send email notifications shows
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function formNotificationsSendEmailNotificationsShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );
		
		$I->wantTo( 'Ensure form notifications send email notifications works properly' );

		$I->click( 'Form Notifications' );
		$I->dontSee( '"To" Email Addresses' );
		$I->selectOption( 'Send Email Notifications', 'Yes' );
		$I->see( '"To" Email Addresses (comma separated)' );
		$I->see( '"From" Email Address Type' );
		$I->see( '"From" Name Type' );
		$I->see( 'Custom "From" Name' );
		$I->see( 'Email Subject Type' );
	}

	/**
	 * Ensure form notifications email type shows
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function formNotificationsFromEmailTypeShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );
		
		$I->wantTo( 'Ensure form notifications from email type shows' );

		$I->click( 'Form Notifications' );
		$I->selectOption( 'Send Email Notifications', 'Yes' );
		$I->see('Email Address Type');
		$I->selectOption( 'Email Address Type:', 'Custom Email' );
		$I->see( 'Custom "From" Email Address' );
		$I->selectOption( 'Email Address Type:', 'Form Field' ); // Form Field not working
		$I->see( 'Pull "From" Email Dynamically from Field' );
	}

	/**
	 * Ensure form notifications email name type shows
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function formNotificationsFromEmailNameTypeShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );
		
		$I->wantTo( 'Ensure form notifications from email name type shows' );

		$I->click( 'Form Notifications' );
		$I->selectOption( 'Send Email Notifications', 'Yes' );
		$I->see('"From" Name Type');
		$I->see( 'Custom "From" Name:' );
		$I->selectOption( '"From" Name Type', 'Form Field' );
		$I->dontSee( 'Custom From Name:' );
		$I->see( 'Pull "From" Name Dynamically from Field' );
	}

	/**
	 * Ensure form notifications subject type shows
	 *
	 * @since 7.0
	 * @param \GLM\Tests\Acceptance\AcceptanceTester $I The current actor.
	 */
	public function formNotificationsEmailSubjectTypeShow( AcceptanceTester $I ) {
		$I->amOnPage( admin_url( 'post-new.php' ) );

		$I->click( 'Add Form' );
		
		$I->wantTo( 'Ensure form notifications from email type shows' );

		$I->click( 'Form Notifications' );
		$I->selectOption( 'Send Email Notifications', 'Yes' );
		$I->dontSee( 'Custom Email Subject:' );
		$I->selectOption( 'Email Subject Type:', 'Custom Subject' );
		$I->see( 'Custom Email Subject:' );
		$I->selectOption( 'Email Subject Type:', 'Form Field' );
		$I->see( 'Pull Email Subject Dynamically from Field' );
		$I->dontSee( 'Custom Email Subject:' );
	}
}

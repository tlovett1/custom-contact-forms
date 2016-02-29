( function( $ ) {

	module( 'Test Form Errors' );

	var qunit = document.getElementById( 'qunit-fixture' );

	QUnit.test( 'Test simple successful form submit', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-form-1.html', function() {
			wp.ccf.setupDOM();

			var form = document.querySelectorAll( '.ccf-form' )[0];

			// Fill out form as needed
			var inputs = form.querySelectorAll( '.field-required .field-input' );

			for ( var i = 0; i < inputs.length; i++ ) {
				inputs[i].value = 'Test';
			}

			$( form ).on( 'ccfFormSuccess', function() {
				ok( true, 'Form submitted without errors' );
				done();
			});

			// Submit form
			form.querySelectorAll( '.ccf-submit-button' )[0].click();
		});
	});

	QUnit.test( 'Test simple unsuccessful form submit with two errors', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-form-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.on( 'ccfFormError', function( event ) {
				equal( arguments.length, 3, 'Form submitted with two errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful form submit with one error', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-form-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.find( '.field-required .field-input' )
				.first()
				.val( 'Test' );

			$form.on( 'ccfFormError', function( event, errors ) {
				equal( arguments.length, 2, 'Form submitted with one error' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple successful name field', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-name-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.find( '.field-required .field-input' )
				.val( 'Test' );

			$form.on( 'ccfFormSuccess', function() {
				ok( true, 'Form submitted without errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful name field', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-name-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			// Only provide first name
			$form.find( '.field-required .field-input' )
				.first()
				.val( 'Test' );

			$form.on( 'ccfFormError', function() {
				equal( arguments.length, 2, 'Form submitted with one error' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple successful phone field', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-phone-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.find( '.field-required .field-input' )
				.val( '3011111234' );

			$form.on( 'ccfFormSuccess', function() {
				ok( true, 'Form submitted without errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful missing phone field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-phone-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.on( 'ccfFormError', function() {
				equal( 2, arguments.length, 'Form has one error' );

				var errors = arguments[1].errors;
				var keys = _.keys( errors );

				// Required error
				equal( _.keys( errors[keys[0]] )[0], 'required', 'Form has required error' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful badly formatted phone field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-phone-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.find( '.field-required .field-input' )
				.val( 'badphone' );

			$form.on( 'ccfFormError', function() {
				equal( 2, arguments.length, 'Form has one error' );

				var errors = arguments[1].errors;
				var keys = _.keys( errors );

				// Phone error
				equal( _.keys( errors[keys[0]] )[0], 'phone', 'Form has badly formatted phone error' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	// @Todo: test international phone number
	QUnit.test( 'Test simple successful US address field', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-us-address-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			var $requiredFields = $form.find( '.field-required .field-input' );
			$requiredFields.each( function() {
				// make sure we skip state field
				if ( 'text' === $( this ).attr( 'type' ) ) {
					$( this ).val( 'Test' );
				}
			});

			$form.on( 'ccfFormSuccess', function() {
				ok( true, 'Form submitted without errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful half-complete US address field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-us-address-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			var $requiredFields = $form.find( '.field-input' ),
				i = 0;
			$requiredFields.each( function() {
				if ( i > 1 ) {
					return false;
				}

				$( this ).val( 'Test' );
				i++;
			});

			$form.on( 'ccfFormError', function() {
				equal( arguments.length, 2, 'Form submitted with one error field' );

				var errors = arguments[1].errors;

				var requiredErrorsFound = 0;

				for ( var errorKey in errors ) {
					if ( errors[errorKey].required ) {
						requiredErrorsFound++;
					}
				}

				equal( requiredErrorsFound, 2, 'Form has two address required errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful incomplete US address field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-us-address-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.on( 'ccfFormError', function() {
				equal( arguments.length, 2, 'Form submitted with one error field' );

				var errors = arguments[1].errors;

				var requiredErrorsFound = 0;

				for ( var errorKey in errors ) {
					if ( errors[errorKey].required ) {
						requiredErrorsFound++;
					}
				}

				equal( requiredErrorsFound, 3, 'Form has three address required errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	// Todo: Test international address
	QUnit.test( 'Test simple unsuccessful incomplete US address field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-us-address-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.on( 'ccfFormError', function() {
				equal( arguments.length, 2, 'Form submitted with one error field' );

				var errors = arguments[1].errors;

				var requiredErrorsFound = 0;

				for ( var errorKey in errors ) {
					if ( errors[errorKey].required ) {
						requiredErrorsFound++;
					}
				}

				equal( requiredErrorsFound, 3, 'Form has three address required errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple successful checkboxes field', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-checkboxes-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			var $requiredFields = $form.find( '.field-required .field-input' );
			$requiredFields.each( function() {
				// make sure we skip state field
				$( this ).attr( 'checked', 'checked' );
			});

			$form.on( 'ccfFormSuccess', function() {
				ok( true, 'Form submitted without errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful checkboxes field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-checkboxes-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.on( 'ccfFormError', function() {
				equal( arguments.length, 2, 'Form submitted with one error field' );

				var errors = arguments[1].errors;
				var keys = _.keys( errors );

				equal( _.keys( errors[keys[0]] )[0], 'required', 'Form has required error' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful checkboxes field only dead option checked', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-checkboxes-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			var $requiredFields = $form.find( '.field-input' ),
				i = 0;
			$requiredFields.each( function() {
				if ( i > 0 ) {
					return false;
				}

				$( this ).attr( 'checked', 'checked' );
				i++;
			});

			$form.on( 'ccfFormError', function() {
				equal( arguments.length, 2, 'Form submitted with one error field' );

				var errors = arguments[1].errors;
				var keys = _.keys( errors );

				equal( _.keys( errors[keys[0]] )[0], 'required', 'Form has required error' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	// @Todo: Test confirm email field
	QUnit.test( 'Test simple successful email field', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-email-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.find( '.field-required .field-input' )
				.val( 'test@test.com' );

			$form.on( 'ccfFormSuccess', function() {
				ok( true, 'Form submitted without errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful required email field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-email-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.on( 'ccfFormError', function() {
				equal( arguments.length, 2, 'Form submitted with one error field' );

				var errors = arguments[1].errors;
				var keys = _.keys( errors );

				equal( _.keys( errors[keys[0]] )[0], 'required', 'Form has required error' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful bad email field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-email-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.find( '.field-required .field-input' )
				.val( 'testsd' );

			$form.on( 'ccfFormError', function() {
				equal( arguments.length, 2, 'Form submitted with one error field' );

				var errors = arguments[1].errors;
				var keys = _.keys( errors );

				equal( _.keys( errors[keys[0]] )[0], 'email', 'Email is not valid' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	// Todo: Test dropdown
	// Todo: Test radio
	// Todo: Test date
	// Todo: Test hidden
	// Todo: Test reCAPTCHA
	// Todo: Test paragraph text
	// Todo: Test file
	QUnit.test( 'Test simple successful website field', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-website-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.find( '.field-required .field-input' )
				.val( 'http://google.com' );

			$form.on( 'ccfFormSuccess', function() {
				ok( true, 'Form submitted without errors' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful missing website field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-website-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.on( 'ccfFormError', function() {
				equal( 2, arguments.length, 'Form has one error' );

				var errors = arguments[1].errors;
				var keys = _.keys( errors );

				// Required error
				equal( _.keys( errors[keys[0]] )[0], 'required', 'Form has required error' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

	QUnit.test( 'Test simple unsuccessful badly formatted website field', function( assert ) {
		var done = assert.async();

		expect( 2 );

		$( qunit ).load( 'forms/simple-website-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $( '.ccf-form' );

			$form.find( '.field-required .field-input' )
				.val( 'something' );

			$form.on( 'ccfFormError', function() {
				equal( 2, arguments.length, 'Form has one error' );

				var errors = arguments[1].errors;
				var keys = _.keys( errors );

				// Phone error
				equal( _.keys( errors[keys[0]] )[0], 'website', 'Form has badly formatted website error' );
				done();
			});

			// Submit form
			$form.find( '.ccf-submit-button' ).click();
		});
	});

})( jQuery );

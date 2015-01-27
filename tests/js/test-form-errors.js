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

			var $form = $('.ccf-form' );

			$form.on( 'ccfFormError', function( event, errors ) {
				ok( true, 'Form submitted with errors' );
				done();
			});

			// Submit form
			$form.submit();
		});
	});

	QUnit.test( 'Test simple unsuccessful form submit with one error', function( assert ) {
		var done = assert.async();

		expect( 1 );

		$( qunit ).load( 'forms/simple-form-1.html', function() {
			wp.ccf.setupDOM();

			var $form = $('.ccf-form' );

			$form.find( '.field-required .field-input')
				.first()
				.val( 'Test' );

			$form.on( 'ccfFormError', function( event, errors ) {
				ok( true, 'Form submitted with errors' );
				done();
			});

			// Submit form
			$form.submit();
		});
	});

})( jQuery );
( function( $, _ ) {

	'use strict';

	wp.ccf.preview = function( formId, element ) {
		var SELF = this;

		SELF.id = parseInt( formId );
		SELF.form = null;

		var fetchResult = SELF.fetch();

		$.when( fetchResult ).done( function() {
			if ( null === SELF.form ) {
				SELF.form = wp.ccf.forms.findWhere( { id: SELF.id } );
			} else {
				wp.ccf.forms.add( SELF.form );
				delete wp.ccf.forms.formsFetching[SELF.id];
			}

			SELF.renderPreviews.call( SELF );

			element.innerHTML = SELF.template( { form: SELF.form.toJSON() } );
		});

		wp.ccf.dispatcher.on( 'saveFormComplete', function() {
			SELF.renderPreviews();

			element.innerHTML = SELF.template( { form: SELF.form.toJSON() } );
		});
	};

	wp.ccf.preview.prototype = {
		template: wp.ccf.utils.template( 'ccf-form-mce-preview' ),

		fetch: function() {
			var SELF = this;

			var form = wp.ccf.forms.findWhere( { id: SELF.id } );

			if ( ! form ) {
				var $deferred;

				if ( typeof wp.ccf.forms.formsFetching[SELF.id] !== 'undefined' ) {
					$deferred = wp.ccf.forms.formsFetching[SELF.id];
				} else {
					SELF.form = new wp.ccf.models.Form( { id: SELF.id } );
					$deferred = SELF.form.fetch();
					wp.ccf.forms.formsFetching[SELF.id] = $deferred;
				}

				return $deferred;
			}

			return true;
		},

		renderPreviews: function() {
			var SELF = this;

			var fields = SELF.form.get( 'fields' );

			fields.each( function( field ) {
				var template = document.getElementById( 'ccf-' + field.get( 'type' ) + '-preview-template' );

				if ( template ) {
					var preview = wp.ccf.utils.template( 'ccf-' + field.get( 'type' ) + '-preview-template' )( { field: field.toJSON(), mce: true } );
					field.set( 'preview', preview );
				}
			});
		}
	};

	var previews = document.querySelectorAll( '.ccf-form-cpt-preview' );

	_.each( previews, function( preview ) {
		var formId = parseInt( preview.getAttribute( 'data-form-id' ) );

		new wp.ccf.preview( formId, preview );
	});

})( jQuery, _ );
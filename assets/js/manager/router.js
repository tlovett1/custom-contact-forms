( function( $, Backbone, _, ccfSettings ) {
	'use strict';

	wp.ccf.router = wp.ccf.router || Backbone.Router.extend( {
		routes: {
			'ccf-form': 'open',
			'ccf-form/:formId': 'open'
		},

		open: function( formId ) {
			wp.ccf.show( formId );
		}
	});
})( jQuery, Backbone, _, ccfSettings );
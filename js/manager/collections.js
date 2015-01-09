( function( $, Backbone, _, ccfSettings ) {
	'use strict';

	wp.ccf.collections = wp.ccf.collections || {};

	wp.ccf.collections.Forms = wp.ccf.collections.Forms || wp.api.collections.Posts.extend(
		{
			model: wp.ccf.models.Form,

			url: WP_API_Settings.root + '/ccf/forms',

			formsFetching: {},

			initialize: function() {
				this.constructor.__super__.initialize();
				this.formsFetching = {};
			},

			remove: function( model, options ) {
				options = options || {};

				var result = this.constructor.__super__.remove.call( this, model, options );

				if ( options.destroy ) {

					if ( model instanceof Array ) {
						_.each( model, function( model ) {
							model.destroy();
						});
					} else {
						model.destroy();
					}
				}

				return result;
			}
		}
	);

	wp.ccf.collections.Fields = wp.ccf.collections.Fields || wp.api.collections.Posts.extend(
		{
			model: wp.ccf.models.Field,

			url: function() {
				return WP_API_Settings.root + '/ccf/forms/' + this.formId + '/fields';
			},

			initialize: function( models, options ) {
				if ( options && options.formId ) {
					this.formId = options.formId;
				}
			}
		}
	);

	wp.ccf.collections.Submissions = wp.ccf.collections.Submissions || wp.api.collections.Posts.extend(
		{
			model: wp.ccf.models.Submission,

			url: function() {
				return WP_API_Settings.root + '/ccf/forms/' + this.formId + '/submissions';
			},

			initialize: function( models, options ) {
				this.constructor.__super__.initialize.apply( this, arguments );

				if ( options && options.formId ) {
					this.formId = options.formId;
				}
			}
		}
	);

	wp.ccf.collections.FieldChoices = wp.ccf.collections.FieldChoices || Backbone.Collection.extend(
		{
			model: wp.ccf.models.FieldChoice
		}
	);

})( jQuery, Backbone, _, ccfSettings );
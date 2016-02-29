( function( $, Backbone, _, ccfSettings ) {
	'use strict';

	/**
	 * Many web servers don't support PUT
	 */
	var _sync = function( method, model, options ) {
		options = options || {};

		options.emulateHTTP = true;

		return this.constructor.__super__.sync.call( this, method, model, options );
	};

	wp.ccf.collections = wp.ccf.collections || {};

	wp.ccf.collections.Forms = wp.ccf.collections.Forms || wp.api.collections.Posts.extend(
		{
			model: wp.ccf.models.Form,

			url: ccfSettings.apiRoot.replace( /\/$/, '' ) + '/ccf/v1/forms',

			formsFetching: {},

			initialize: function() {
				this.constructor.__super__.initialize();
				this.formsFetching = {};
			},

			sync: _sync,

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
				return ccfSettings.apiRoot + '/ccf/forms/' + this.formId + '/fields';
			},

			initialize: function( models, options ) {
				if ( options && options.formId ) {
					this.formId = options.formId;
				}
			},

			sync: _sync
		}
	);

	wp.ccf.collections.PostFieldMappings = wp.ccf.collections.PostFieldMappings || Backbone.Collection.extend(
		{
			model: wp.ccf.models.PostFieldMapping
		}
	);

	wp.ccf.collections.FormNotificationAddresses = wp.ccf.collections.FormNotificationAddresses || Backbone.Collection.extend(
		{
			model: wp.ccf.models.FormNotificationAddress
		}
	);

	wp.ccf.collections.FormNotifications = wp.ccf.collections.FormNotifications || Backbone.Collection.extend(
		{
			model: wp.ccf.models.FormNotification
		}
	);

	wp.ccf.collections.Submissions = wp.ccf.collections.Submissions || wp.api.collections.Posts.extend(
		{
			model: wp.ccf.models.Submission,

			url: function() {
				return ccfSettings.apiRoot.replace( /\/$/, '' ) + '/ccf/v1/forms/' + this.formId + '/submissions';
			},

			initialize: function( models, options ) {
				this.constructor.__super__.initialize.apply( this, arguments );

				if ( options && options.formId ) {
					this.formId = options.formId;
				}
			},

			sync: _sync
		}
	);

	wp.ccf.collections.FieldChoices = wp.ccf.collections.FieldChoices || Backbone.Collection.extend(
		{
			model: wp.ccf.models.FieldChoice
		}
	);

	wp.ccf.collections.FieldConditionals = wp.ccf.collections.FieldConditionals || Backbone.Collection.extend(
		{
			model: wp.ccf.models.FieldConditional
		}
	);

})( jQuery, Backbone, _, ccfSettings );

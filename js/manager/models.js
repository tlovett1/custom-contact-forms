( function( $, Backbone, _, ccfSettings, WP_API_Settings ) {
	'use strict';

	wp.ccf.models = wp.ccf.models || {};
	wp.ccf.models.Fields = wp.ccf.models.Fields || {};

	/**
	 * A terrible ie8 polyfill to fix JSON.stringify issues and empty strings pulled
	 * straight from the DOM.
 	 */

	var _modelSet = function( key, value, options ) {
		if ( typeof value !== 'object' && value === '' ) {
			value = '';
		}

		return Backbone.Model.prototype.set.call( this, key, value, options );
	};

	wp.ccf.models.FieldChoice = wp.ccf.models.FieldChoice || Backbone.Model.extend(
		{
			defaults: {
				label: '',
				value: '',
				selected: false
			},

			set: _modelSet
		}
	);

	wp.ccf.models.Form = wp.ccf.models.Form || wp.api.models.Post.extend(
		{

			urlRoot: WP_API_Settings.root + '/ccf/forms',

			set: _modelSet,

			defaults: function() {
				var defaults = {
					fields: new wp.ccf.collections.Fields(),
					type: 'ccf_form',
					status: 'publish',
					description: '',
					buttonText: 'Submit Form',
					completionActionType: 'text',
					completionRedirectUrl: '',
					completionMessage: '',
					sendEmailNotifications: false,
					emailNotificationAddresses: ccfSettings.adminEmail
				};

				defaults = _.defaults( defaults, this.constructor.__super__.defaults );
				wp.ccf.utils.cleanDateFields( defaults );

				return defaults;
			},

			getFieldSlugs: function( mutableOnly ) {
				var fields = wp.ccf.currentForm.get( 'fields' ),
					columns = [];

				fields.each( function( field ) {
					if ( mutableOnly ) {
						if ( field.isImmutable ) {
							return;
						}
					}

					columns.push( field.get( 'slug' ) );
				});

				return columns;
			},

			parse: function( response ) {
				var SELF = this;

				if ( response.fields ) {

					var fields = SELF.get( 'fields' );

					if ( fields && fields.length > 0 ) {

						for ( var i = 0; i < response.fields.length; i++ ) {
							var newField = response.fields[i];

							var field = fields.findWhere( { slug: newField.slug } );

							if ( field ) {
								if ( typeof newField.choices !== 'undefined' ) {
									var choices = SELF.get( 'choices' );

									if ( choices && choices.length > 0 ) {
										for ( var z = 0; z < newField.choices; z++ ) {
											choices.at( z ).set( newField.choices[z] );
										}
									}

									delete response.fields[i].choices;
								}

								field.set( newField );
							}
						}

						delete response.fields;
					} else {
						var newFields = [];

						_.each( response.fields, function( field ) {
							newFields.push( new wp.ccf.models.Fields[field.type]( field ) );
						});

						response.fields = new wp.ccf.collections.Fields( newFields, { formId: response.ID } );
					}
				}

				return this.constructor.__super__.parse.call( this, response );
			},

			toJSON: function() {
				var attributes = this.constructor.__super__.toJSON.call( this );

				if ( attributes.fields ) {
					attributes.fields = attributes.fields.toJSON();
				}

				if ( attributes.author ) {
					attributes.author = attributes.author.toJSON();
				}

				return attributes;
			}
		}
	);

	wp.ccf.models.Submission = wp.api.models.Submission || wp.api.models.Post.extend(
		{
			idAttribute: 'ID',

			defaults: {
				ID: null,
				data: {}
			}
		}
	);

	wp.ccf.models.Field = wp.api.models.Field || wp.api.models.Post.extend(
		{
			idAttribute: 'ID',

			defaults: {
				ID: null
			},

			set: _modelSet,

			required: function() {
				return [ 'slug' ];
			},

			hasRequiredAttributes: function() {
				var SELF = this;
				var reqsMet = true;

				_.each( this.required(), function( fieldSlug ) {
					if ( typeof SELF.get( fieldSlug ) === 'undefined' || SELF.get( fieldSlug ) === '' ) {
						reqsMet = false;
					}
				});

				return reqsMet;
			}
		}
	);

	wp.ccf.models.StandardField = wp.ccf.models.StandardField || wp.ccf.models.Field.extend(
		{
			idAttribute: 'ID',

			defaults: function() {
				var defaults = {
					label: 'Field Label',
					value: '',
					placeholder: '',
					slug: '',
					type: '',
					required: false,
					className: ''
				};

				return _.defaults( defaults, this.constructor.__super__.defaults );
			}
		}
	);

	wp.ccf.models.Fields['single-line-text'] = wp.ccf.models.Fields['single-line-text'] || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'single-line-text'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			}
		}
	);

	wp.ccf.models.Fields['paragraph-text'] = wp.ccf.models.Fields['paragraph-text'] || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'paragraph-text'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			}
		}
	);

	wp.ccf.models.Fields.hidden = wp.ccf.models.Fields.hidden || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'hidden'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			}
		}
	);

	wp.ccf.models.Fields.email = wp.ccf.models.Fields.email || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'email',
					emailConfirmation: false
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			}
		}
	);

	wp.ccf.models.Fields.website = wp.ccf.models.Fields.website || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'website'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			}
		}
	);

	wp.ccf.models.Fields.phone = wp.ccf.models.Fields.phone || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'phone',
					phoneFormat: 'us'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			}
		}
	);

	wp.ccf.models.Fields.date = wp.ccf.models.Fields.date || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'date',
					showDate: true,
					showTime: true
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			}
		}
	);

	wp.ccf.models.Fields.name = wp.ccf.models.Fields.name || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'name'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			}
		}
	);

	wp.ccf.models.Fields.address = wp.ccf.models.Fields.address || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'address',
					addressType: 'us'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			}
		}
	);

	wp.ccf.models.Fields['section-header'] = wp.ccf.models.Fields['section-header'] || wp.ccf.models.Field.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'section-header',
					slug: '',
					heading: '',
					subheading: '',
					className: ''
				};

				return _.defaults( defaults, this.constructor.__super__.defaults );
			},

			required: function() {
				return [];
			},

			isImmutable: true
		}
	);

	wp.ccf.models.Fields.html = wp.ccf.models.Fields.html || wp.ccf.models.Field.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'html',
					slug: '',
					html: '',
					className: ''
				};

				return _.defaults( defaults, this.constructor.__super__.defaults );
			},

			required: function() {
				return [];
			},

			isImmutable: true
		}
	);

	wp.ccf.models.ChoiceableField = wp.ccf.models.ChoiceableField || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					choices: new wp.ccf.collections.FieldChoices()
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			},

			initialize: function( attributes ) {
				if ( typeof attributes === 'object' && attributes.choices ) {
					var choices = [];

					_.each( attributes.choices, function( choice ) {
						choices.push( new wp.ccf.models.FieldChoice( choice ) );
					});

					this.set( 'choices', new wp.ccf.collections.FieldChoices( choices ) );
				}
			}
		}
	);

	wp.ccf.models.Fields.radio = wp.ccf.models.Fields.radio || wp.ccf.models.ChoiceableField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'radio'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
			}
		}
	);

	wp.ccf.models.Fields.checkboxes = wp.ccf.models.Fields.checkboxes || wp.ccf.models.ChoiceableField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'checkboxes'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
			}
		}
	);

	wp.ccf.models.Fields.dropdown = wp.ccf.models.Fields.dropdown || wp.ccf.models.ChoiceableField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'dropdown'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
			}
		}
	);
})( jQuery, Backbone, _, ccfSettings, WP_API_Settings );
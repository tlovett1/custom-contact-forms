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

	/**
	 * Many web servers don't support PUT
	 */
	var _sync = function( method, model, options ) {
		options = options || {};

		options.emulateHTTP = true;

		return this.constructor.__super__.sync.call( this, method, model, options );
	};

	/**
	 * We decode HTML entities after syncing then escape on output. The
	 * point of this is to prevent double escaping.
	 */
	var _modelDecode = function( excludeKeys ) {
		for ( var key in this.attributes ) {
			if ( _.indexOf( excludeKeys, key ) === -1 ) {
				var value = this.get( key );

				if ( typeof value === 'string' && value !== '' ) {
					value = String( value )
						.replace( /&amp;/g, '&' )
						.replace( /&lt;/g, '<' )
						.replace( /&gt;/g, '>' )
						.replace( /&quot;/g, '"' )
						.replace( /&#8220;/g, '”' )
						.replace( /&#8221;/g, '”' )
						.replace( /&#8216;/g, "‘" )
						.replace( /&#038;/g, "&" )
						.replace( /&#039;/g, "'" );

					this.set( key, value );
				}
			}
		}

		return this;
	};

	wp.ccf.models.FieldChoice = wp.ccf.models.FieldChoice || Backbone.Model.extend(
		{
			defaults: {
				label: '',
				value: '',
				selected: false
			},

			decode: function() {
				return _modelDecode.call( this, [] );
			},

			set: _modelSet
		}
	);

	wp.ccf.models.Form = wp.ccf.models.Form || wp.api.models.Post.extend(
		{

			urlRoot: WP_API_Settings.root + '/ccf/forms',

			set: _modelSet,

			sync: _sync,

			initialize: function() {
				this.on( 'sync', this.decode, this );
			},

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
					emailNotificationAddresses: ccfSettings.adminEmail,
					emailNotificationFromType: 'default',
					emailNotificationFromAddress: '',
					emailNotificationFromField: '',
					emailNotificationSubjectType: 'default',
					emailNotificationSubject: '',
					emailNotificationSubjectField: '',
					emailNotificationFromNameType: 'custom',
					emailNotificationFromName: 'WordPress',
					emailNotificationFromNameField: '',
					pause: false,
					pauseMessage: ccfSettings.pauseMessage
				};

				defaults = _.defaults( defaults, this.constructor.__super__.defaults );
				wp.ccf.utils.cleanDateFields( defaults );

				return defaults;
			},

			decode: function() {
				var keys = _.keys( wp.api.models.Post.prototype.defaults );
				keys = _.without( keys, 'title' );

				return _modelDecode.call( this, keys );
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
											var choice = choices.at( z );
											choice.set( newField.choices[z] );
											choice.decode();
										}
									}

									delete response.fields[i].choices;
								}

								field.set( newField );
								field.decode();
							}
						}

						delete response.fields;
					} else {
						var newFields = [];

						_.each( response.fields, function( field ) {
							var fieldModel = new wp.ccf.models.Fields[field.type]( field );
							fieldModel.decode();

							newFields.push( fieldModel );
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
			},

			sync: _sync
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

			decode: function() {
				return _modelDecode.call( this, _.keys( wp.api.models.Post.prototype.defaults ) );
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
			},

			sync: _sync
		}
	);

	wp.ccf.models.StandardField = wp.ccf.models.StandardField || wp.ccf.models.Field.extend(
		{
			idAttribute: 'ID',

			defaults: function() {
				var defaults = {
					label: ccfSettings.fieldLabel,
					value: '',
					placeholder: '',
					slug: '',
					type: '',
					required: false,
					className: '',
					description: ''
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

	wp.ccf.models.Fields.file = wp.ccf.models.Fields.file || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'file',
					fileExtensions: '',
					maxFileSize: ccfSettings.maxFileSize
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
					type: 'website',
					placeholder: 'http://'
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

	wp.ccf.models.Fields.recaptcha = wp.ccf.models.Fields.recaptcha || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'recaptcha',
					siteKey: '',
					secretKey: ''
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			},

			required: function() {
				return [ 'siteKey', 'secretKey' ];
			},

			isImmutable: true
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
						var choiceModel = new wp.ccf.models.FieldChoice( choice );
						choiceModel.decode();

						choices.push( choiceModel );
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
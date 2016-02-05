( function( $, Backbone, _, ccfSettings ) {
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

	wp.ccf.models.FieldConditional = wp.ccf.models.FieldConditional || Backbone.Model.extend(
		{
			defaults: {
				field: '',
				compare: 'is',
				value: ''
			},

			decode: function() {
				return _modelDecode.call( this, [] );
			},

			set: _modelSet
		}
	);

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

	wp.ccf.models.PostFieldMapping = wp.ccf.models.PostFieldMapping || Backbone.Model.extend(
		{
			defaults: {
				formField: '',
				postField: '',
				customFieldKey: ''
			},

			decode: function() {
				return _modelDecode.call( this, [] );
			},

			set: _modelSet
		}
	);

	wp.ccf.models.FormNotificationAddress = wp.ccf.models.FormNotificationAddress || Backbone.Model.extend(
		{
			defaults: {
				type: 'custom',
				field: '',
				email: ''
			},

			decode: function() {
				return _modelDecode.call( this, [] );
			},

			set: _modelSet
		}
	);

	wp.ccf.models.FormNotification = wp.ccf.models.FormNotification || Backbone.Model.extend(
		{
			defaults: function() {
				return {
					title: '',
					content: '[all_fields]',
					active: false,
					addresses: new wp.ccf.collections.FormNotificationAddresses(),
					fromType: 'default',
					fromAddress: '',
					fromField: '',
					subjectType: 'default',
					subject: '',
					subjectField: '',
					fromNameType: 'custom',
					fromName: 'WordPress',
					fromNameField: ''
				};
			},

			initialize: function( attributes ) {
				if ( typeof attributes === 'object' && attributes.addresses ) {
					var addresses = [];

					_.each( attributes.addresses, function( address ) {
						var addressModel = new wp.ccf.models.FormNotificationAddress( address );
						addressModel.decode();

						addresses.push( addressModel );
					});

					this.set( 'addresses', new wp.ccf.collections.FormNotificationAddresses( addresses ) );
				}
			},

			decode: function() {
				return _modelDecode.call( this, [] );
			},

			toJSON: function() {
				var attributes = this.constructor.__super__.toJSON.call( this );

				if ( attributes.addresses ) {
					attributes.addresses = attributes.addresses.toJSON();
				}

				return attributes;
			},

			set: _modelSet
		}
	);

	wp.ccf.models.Form = wp.ccf.models.Form || wp.api.models.Post.extend(
		{

			urlRoot: ccfSettings.apiRoot.replace( /\/$/, '' ) + '/ccf/v1/forms',

			set: _modelSet,

			sync: _sync,

			idAttribute: 'id',

			initialize: function( attributes ) {
				this.on( 'sync', this.decode, this );
			},

			defaults: function() {
				var defaults = {
					fields: new wp.ccf.collections.Fields(),
					type: 'ccf_form',
					status: 'publish',
					description: '',
					buttonText: 'Submit Form',
					buttonClass: '',
					completionActionType: 'text',
					completionRedirectUrl: '',
					completionMessage: '',
					postCreation: false,
					postCreationType: 'post',
					postCreationStatus: 'draft',
					postFieldMappings: new wp.ccf.collections.PostFieldMappings(),
					notifications: new wp.ccf.collections.FormNotifications(),
					pause: false,
					pauseMessage: ccfSettings.pauseMessage,
					theme: 'none'
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
				var SELF = this,
					i = 0,
					z = 0;

				if ( response.fields ) {

					var fields = SELF.get( 'fields' );

					if ( fields && fields.length > 0 ) {

						for ( i = 0; i < response.fields.length; i++ ) {
							var newField = response.fields[i];

							var field = fields.findWhere( { slug: newField.slug } );

							if ( field ) {
								if ( typeof newField.choices !== 'undefined' ) {
									var choices = SELF.get( 'choices' );

									if ( choices && choices.length > 0 ) {
										for ( z = 0; z < newField.choices; z++ ) {
											var choice = choices.at( z );
											choice.set( newField.choices[z] );
											choice.decode();
										}
									}

									delete response.fields[i].choices;
								}

								if ( typeof newField.conditionals !== 'undefined' ) {
									var conditionals = SELF.get( 'conditionals' );

									if ( conditionals && conditionals.length > 0 ) {
										for ( z = 0; z < newField.conditionals; z++ ) {
											var conditional = conditionals.at( z );
											conditional.set( newField.conditionals[z] );
											conditional.decode();
										}
									}

									delete response.fields[i].conditionals;
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

						response.fields = new wp.ccf.collections.Fields( newFields, { formId: response.id } );
						if ( ! fields ) {
							response.fields = new wp.ccf.collections.Fields( newFields, { formId: response.id } );
						} else {
							fields.add( newFields );
							delete response.fields;
						}
					}
				}

				if ( response.notifications ) {

					var notifications = SELF.get( 'notifications' );

					if ( notifications && notifications.length > 0 ) {

						for ( i = 0; i < response.notifications.length; i++ ) {
							var newNotification = response.notifications[i];

							var notification = notifications.at( i );

							if ( notification ) {
								if ( typeof newNotification.addresses !== 'undefined' ) {
									var addresses = notification.get( 'addresses' );

									if ( addresses && addresses.length > 0 ) {
										for ( z = 0; z < newNotification.addresses; z++ ) {
											var address = addresses.at( z );
											address.set( newNotification.addresses[z] );
											address.decode();
										}
									}

									delete response.notifications[i].addresses;
								}

								notification.set( newNotification );
								notification.decode();
							}
						}

						delete response.notifications;
					} else {
						var newNotifications = [];

						_.each( response.notifications, function( notification ) {
							var notificationModel = new wp.ccf.models.FormNotification( notification );
							notificationModel.decode();

							newNotifications.push( notificationModel );
						});

						if ( ! notifications ) {
							response.notifications = new wp.ccf.collections.FormNotifications( newNotifications );
						} else {
							notifications.add( newNotifications );
							delete response.notifications;
						}
					}
				}

				if ( response.postFieldMappings ) {

					var postFieldMappings = SELF.get( 'postFieldMappings' );

					if ( postFieldMappings && postFieldMappings.length > 0 ) {

						for ( i = 0; i < response.postFieldMappings.length; i++ ) {
							var newPostFieldMapping = response.postFieldMappings[i];

							var postFieldMapping = postFieldMappings.at( i );

							if ( postFieldMapping ) {
								postFieldMapping.set( newPostFieldMapping );
								postFieldMapping.decode();
							}
						}

						delete response.postFieldMappings;
					} else {
						var newPostFieldMappings = [];

						_.each( response.postFieldMappings, function( postFieldMapping ) {
							var postFieldMappingModel = new wp.ccf.models.PostFieldMapping( postFieldMapping );
							postFieldMappingModel.decode();

							newPostFieldMappings.push( postFieldMappingModel );
						});

						if ( ! postFieldMappings ) {
							response.postFieldMappings = new wp.ccf.collections.PostFieldMappings( newPostFieldMappings );
						} else {
							postFieldMappings.add( newPostFieldMappings );
							response.postFieldMappings = postFieldMappings;
						}
					}
				}

				return this.constructor.__super__.parse.call( this, response );
			},

			toJSON: function() {
				var attributes = this.constructor.__super__.toJSON.call( this );

				if ( attributes.fields ) {
					attributes.fields = attributes.fields.toJSON();
				}

				if ( attributes.notifications ) {
					attributes.notifications = attributes.notifications.toJSON();
				}

				if ( attributes.postFieldMappings ) {
					attributes.postFieldMappings = attributes.postFieldMappings.toJSON();
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
			defaults: {
				id: null,
				data: {},
				fields: {}
			},

			sync: _sync,

			urlRoot: ccfSettings.apiRoot.replace( /\/$/, '' ) + '/ccf/v1/submissions'
		}
	);

	wp.ccf.models.Field = wp.api.models.Field || wp.api.models.Post.extend(
		{
			idAttribute: 'id',

			defaults: function() {
				return {
					id: null,
					conditionalsEnabled: false,
					conditionalType: 'show',
					conditionalFieldsRequired: 'all',
					conditionals: new wp.ccf.collections.FieldConditionals()
				};
			},

			set: _modelSet,

			initialize: function( attributes ) {
				if ( typeof attributes === 'object' && attributes.conditionals ) {
					var conditionals = [];

					_.each( attributes.conditionals, function( conditional ) {
						var conditionalModel = new wp.ccf.models.FieldConditional( conditional );
						conditionalModel.decode();

						conditionals.push( conditionalModel );
					});

					this.set( 'conditionals', new wp.ccf.collections.FieldConditionals( conditionals ) );
				}
			},

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
			idAttribute: 'id',

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

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			},

			initialize: function() {
				return wp.ccf.models.StandardField.__super__.initialize.apply( this, arguments );
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
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
			}
		}
	);

	wp.ccf.models.Fields.date = wp.ccf.models.Fields.date || wp.ccf.models.StandardField.extend(
		{
			defaults: function() {
				var defaults = {
					type: 'date',
					showDate: true,
					showTime: true,
					dateFormat: 'mm/dd/yyyy'
				};

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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

			isImmutable: true,

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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
			},

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
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

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			},

			required: function() {
				return [];
			},

			isImmutable: true,

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
			}
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

				return _.defaults( defaults, this.constructor.__super__.defaults() );
			},

			required: function() {
				return [];
			},

			isImmutable: true,

			initialize: function() {
				return this.constructor.__super__.initialize.apply( this, arguments );
			}
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

				return wp.ccf.models.ChoiceableField.__super__.initialize.apply( this, arguments );
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
})( jQuery, Backbone, _, ccfSettings );

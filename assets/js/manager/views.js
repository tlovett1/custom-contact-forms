( function( $, Backbone, _, ccfSettings ) {
	'use strict';

	wp.ccf.views = wp.ccf.views || {};
	wp.ccf.views.Fields = wp.ccf.views.Fields || {};

	wp.ccf.views.FieldChoice = Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-field-choice-template' ),
			className: 'choice',

			events: {
				'click .add': 'triggerAdd',
				'click .delete': 'triggerDelete',
				'saveChoice': 'saveChoice',
				'sorted': 'triggerUpdateSort'
			},

			initialize: function( options ) {
				this.field = options.field;
			},

			destroy: function() {
				wp.ccf.dispatcher.off( 'mainViewChange', this.saveChoice );
				this.unbind();
			},

			triggerUpdateSort: function( event, index ) {
				this.field.get( 'choices' ).remove( this.model, { silent: true } );

				this.field.get( 'choices' ).add( this.model, { at: index, silent: true } );
			},

			saveChoice: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				var label = this.el.querySelectorAll( '.choice-label' )[0].value;
				var value = this.el.querySelectorAll( '.choice-value' )[0].value;

				this.model.set( 'label', label );
				this.model.set( 'value', value );

				var selectedElement = this.el.querySelectorAll( '.choice-selected' )[0];
				var selected = ( selectedElement.checked ) ? true : false;

				this.model.set( 'selected', selected );

				return this;

			},

			render: function() {
				var context = {};
				if ( this.model ) {
					context.choice = this.model.toJSON();
				}

				this.el.innerHTML = this.template( context );

				wp.ccf.dispatcher.on( 'mainViewChange', this.saveChoice, this );

				return this;
			},

			triggerAdd: function() {
				this.field.get( 'choices' ).add( new wp.ccf.models.FieldChoice() );
			},

			triggerDelete: function() {
				var choices = this.field.get( 'choices' );
				if ( choices.length > 1 ) {
					choices.remove( this.model );
					this.destroy();
					this.remove();
				} else {
					var inputs = this.el.querySelectorAll( '.choice-label, .choice-value' );
					var selected = this.el.querySelectorAll( '.choice-selected' );

					for ( var i = 0; i < inputs.length; i++ ) {
						inputs[i].value = '';
					}

					selected[0].checked = false;
				}
			}
		}
	);

	wp.ccf.views.FieldConditional = Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-field-conditional-template' ),
			className: 'conditional',

			events: {
				'click .add': 'triggerAdd',
				'click .delete': 'triggerDelete',
				'saveConditional': 'saveConditional'
			},

			initialize: function( options ) {
				this.field = options.field;
				this.fieldCollection = options.fieldCollection;
			},

			destroy: function() {
				wp.ccf.dispatcher.off( 'mainViewChange', this.saveConditional );
				this.unbind();
			},

			saveConditional: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				var field = this.el.querySelectorAll( '.conditional-field' )[0].value;
				var value = this.el.querySelectorAll( '.conditional-value' )[0].value;
				var compare = this.el.querySelectorAll( '.conditional-compare' )[0].value;

				this.model.set( 'field', field );
				this.model.set( 'value', value );
				this.model.set( 'compare', compare );

				return this;

			},

			updateFields: function() {
				var conditionalFields = this.el.querySelectorAll( '.conditional-field' )[0];
				conditionalFields.innerHTML = '';
				conditionalFields.disabled = false;

				var fieldsAdded = 0;

				var conditionalField = this.model.get( 'field' ),
					option;

				if ( this.fieldCollection.length >= 1 ) {
					option = document.createElement( 'option' );
					option.innerHTML = ccfSettings.chooseFormField;
					option.value = '';

					conditionalFields.appendChild( option );

					this.fieldCollection.each( function( field ) {
						if ( this.field.get( 'slug' ) !== field.get( 'slug' ) ) {
							var type = field.get( 'type' );

							if ( 'address' !== type && 'checkboxes' !== type && 'date' !== type && 'name' !== type && 'file' !== type && 'recaptcha' !== type && 'section-header' !== type && 'html' !== type ) {
								option = document.createElement( 'option' );
								option.innerHTML = field.get( 'slug' );
								option.value = field.get( 'slug' );

								if ( field.get( 'slug' ) === conditionalField ) {
									option.selected = true;
								}

								conditionalFields.appendChild( option );

								fieldsAdded++;
							}
						}
					}, this );
				}

				if ( 0 === fieldsAdded ) {
					conditionalFields.innerHTML = '';
					option = document.createElement( 'option' );
					option.innerHTML = ccfSettings.noAvailableFields;
					option.value = '';
					conditionalFields.appendChild( option );
					conditionalFields.disabled = true;
				}
			},

			render: function() {
				var context = {};
				if ( this.model ) {
					context.conditional = this.model.toJSON();
				}

				this.el.innerHTML = this.template( context );

				wp.ccf.dispatcher.on( 'mainViewChange', this.saveConditional, this );

				this.listenTo( this.fieldCollection, 'add', this.updateFields, this );
				this.listenTo( this.fieldCollection, 'remove', this.updateFields, this );

				this.updateFields();

				return this;
			},

			triggerAdd: function() {
				this.field.get( 'conditionals' ).add( new wp.ccf.models.FieldConditional() );
			},

			triggerDelete: function() {
				var conditionals = this.field.get( 'conditionals' );
				if ( conditionals.length > 1 ) {
					conditionals.remove( this.model );
					this.destroy();
					this.remove();
				} else {
					var value  = this.el.querySelectorAll( '.conditional-value' )[0];
					var field = this.el.querySelectorAll( '.conditional-field' )[0];

					value.value = '';

					for ( var i = 0; i < field.childNodes.length; i++ ) {
						field.childNodes[i].selected = false;
					}
				}
			}
		}
	);

	wp.ccf.views.EmptyFormNotificationTableRow = wp.ccf.views.EmptyFormNotificationTableRow || Backbone.View.extend(
		{
			tagName: 'tr',
			template: wp.ccf.utils.template( 'ccf-empty-form-notification-row-template'),

			events: {
				'click .add': 'triggerAdd'
			},

			initialize: function( options ) {
				this.form = options.form;
			},

			destroy: function() {
				this.unbind();
			},

			render: function() {
				this.$el.html( this.template() );
				return this;
			},

			triggerAdd: function() {
				var notifications = this.form.get( 'notifications' );

				this.destroy();

				notifications.add( new wp.ccf.models.FormNotification() );
			}
		}
	);

	wp.ccf.views.EmptyFormTableRow = wp.ccf.views.EmptyFormTableRow || Backbone.View.extend(
		{
			tagName: 'tr',
			template: wp.ccf.utils.template( 'ccf-empty-form-table-row-template'),

			render: function() {
				this.$el.html( this.template() );
				return this;
			}
		}
	);

	wp.ccf.views.FormNotificationAddress = Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-form-notification-address-template' ),
			className: 'address',

			events: {
				'click .add': 'triggerAdd',
				'click .delete': 'triggerDelete',
				'blur input': 'save',
				'change select': 'save'
			},

			initialize: function( options ) {
				this.notification = options.notification;
				this.parent = options.parent;
				this.form = options.form;
			},

			destroy: function() {
				this.unbind();
			},

			save: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				var type = this.el.querySelectorAll( '.form-notification-address-type' )[0].value;
				var email = this.el.querySelectorAll( '.form-notification-address-email' );
				var field = this.el.querySelectorAll( '.form-notification-address-field' );
				var oldType = this.model.get( 'type' );

				if ( email.length ) {
					this.model.set( 'email', email[0].value );
				}

				if ( field.length ) {
					this.model.set( 'field', field[0].value );
				}

				this.model.set( 'type', type );

				if ( oldType !== type ) {
					this.render();
				}

				return this;

			},

			updateFromFieldField: function() {
				if ( 'edit' !== this.parent.context || 'field' !== this.model.get( 'type' ) ) {
					return;
				}

				var addressFromField = this.el.querySelectorAll( '.form-notification-address-field' )[0];
				addressFromField.innerHTML = '';
				addressFromField.disabled = false;

				var fields = this.form.get( 'fields' ),
					fieldsAdded = 0;

				var addressField = this.model.get( 'field' ),
					option;

				if ( fields.length >= 1 ) {
					fields.each( function( field ) {
						if ( 'email' === field.get( 'type' ) || 'dropdown' === field.get( 'type' ) || 'radio' === field.get( 'type' ) || 'single-line-text' === field.get( 'type' ) ) {
							option = document.createElement( 'option' );
							option.innerHTML = field.get( 'slug' );
							option.value = field.get( 'slug' );

							if ( field.get( 'slug' ) === addressField ) {
								option.selected = true;
							}

							addressFromField.appendChild( option );

							fieldsAdded++;
						}
					});
				}

				if ( 0 === fieldsAdded ) {
					option = document.createElement( 'option' );
					option.innerHTML = ccfSettings.noApplicableFields;
					option.value = '';
					addressFromField.appendChild( option );
					addressFromField.disabled = true;
				}
			},

			render: function() {
				var context = {};
				if ( this.model ) {
					context.address = this.model.toJSON();
				}

				this.el.innerHTML = this.template( context );

				var fields = this.form.get( 'fields' );

				this.listenTo( fields, 'add', this.updateFromFieldField, this );
				this.listenTo( fields, 'remove', this.updateFromFieldField, this );

				if ( 'field' === this.model.get( 'type' ) ) {
					this.updateFromFieldField();
				}

				return this;
			},

			triggerAdd: function() {
				this.notification.get( 'addresses' ).add( new wp.ccf.models.FormNotificationAddress() );
			},

			triggerDelete: function() {
				var addresses = this.notification.get( 'addresses' );

				if ( addresses.length > 1 ) {
					this.parent.deleteAddress( this );
				} else {
					this.model.clear().set( wp.ccf.models.FormNotificationAddress.prototype.defaults );
					this.destroy();
					this.render();
				}
			}
		}
	);

	wp.ccf.views.ExistingFormNotificationRow = Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-existing-form-notification-table-row-template' ),
			tagName: 'tr',

			events: {
				'change select.form-email-notification-from-type': 'toggleNotificationFields',
				'change select.form-email-notification-from-name-type': 'toggleNotificationFields',
				'change select.form-email-notification-subject-type': 'toggleNotificationFields',
				'click .close-notification': 'changeContext',
				'click .edit-notification': 'changeContext',
				'click .delete-notification': 'triggerDelete',
				'blur input': 'save',
				'change select': 'save'
			},

			addressViews: [],

			initialize: function( options ) {
				this.form = options.form;
				this.addressViews = [];
				this.parent = options.parent;
				this.context = ( 'undefined' !== typeof options.context ) ? options.context : 'view';

				var addresses = this.model.get( 'addresses' );
				this.listenTo( addresses, 'add', this.addAddress );
			},

			deleteAddress: function( view ) {
				_.each( this.addressViews, function( currentView ) {
					if ( view.cid === currentView.cid ) {
						var index = _.indexOf( this.addressViews, currentView );
						this.model.get( 'addresses' ).remove( view.model );
						this.addressViews[index].remove();
						this.addressViews.splice( index, 1 );
					}
				}, this );
			},

			addAddress: function( model ) {
				var addressesContainer = this.el.querySelectorAll( '.addresses' )[0];
				var view = new wp.ccf.views.FormNotificationAddress( { model: model, parent: this, notification: this.model, form: this.form } );
				this.addressViews.push( view );
				addressesContainer.appendChild( view.render().el );
			},

			destroy: function() {
				this.unbind();
			},

			changeContext: function( event, forceContext ) {
				if ( 'edit' === this.context ) {
					this.save();
				}

				if ( forceContext ) {
					this.context = forceContext;
				} else {
					if ( 'view' === this.context ) {
						this.parent.closeAllNotifications();
					}

					this.context = ( 'edit' === this.context ) ? 'view' : 'edit';
				}

				this.destroy();
				this.render();
			},

			updateFieldVariables: function() {
				if ( 'edit' !== this.context ) {
					return;
				}
				
				var fieldVariables = this.el.querySelectorAll( '.field-variables' )[0];
				var variablesText = '';
				var type;
				var fields = this.form.get( 'fields' );

				fields.each( function( field ) {
					type = field.get( 'type' );

					if ( 'html' !== type && 'section-header' !== type && 'recaptcha' !== type ) {
						variablesText += '[' + field.get( 'slug' ) + '] ';
					}
				} );

				fieldVariables.innerText = variablesText;
			},

			updateFromFieldField: function() {
				if ( 'edit' !== this.context ) {
					return;
				}

				var emailNotificationFromField = this.el.querySelectorAll( '.form-email-notification-from-field' )[0];
				emailNotificationFromField.innerHTML = '';
				emailNotificationFromField.disabled = false;

				var emailNotificationSubjectField = this.el.querySelectorAll( '.form-email-notification-subject-field' )[0];
				emailNotificationSubjectField.innerHTML = '';
				emailNotificationSubjectField.disabled = false;

				var emailNotificationFromNameField = this.el.querySelectorAll( '.form-email-notification-from-name-field' )[0];
				emailNotificationFromNameField.innerHTML = '';
				emailNotificationFromNameField.disabled = false;

				var fields = this.form.get( 'fields' ),
					addressFieldsAdded = 0,
					nameFieldsAdded = 0,
					subjectFieldsAdded = 0;

				var addressField = this.model.get( 'emailNotificationFromField' );
				var subjectField = this.model.get( 'emailNotificationSubjectField' );
				var nameField = this.model.get( 'emailNotificationFromNameField' ),
					option;

				if ( fields.length >= 1 ) {
					fields.each( function( field ) {
						if ( 'email' === field.get( 'type' ) || 'dropdown' === field.get( 'type' ) || 'radio' === field.get( 'type' ) || 'single-line-text' === field.get( 'type' ) ) {
							option = document.createElement( 'option' );
							option.innerHTML = field.get( 'slug' );
							option.value = field.get( 'slug' );

							if ( field.get( 'slug' ) === addressField ) {
								option.selected = true;
							}

							emailNotificationFromField.appendChild( option );

							addressFieldsAdded++;
						} if ( 'name' === field.get( 'type' ) || 'single-line-text' === field.get( 'type' ) || 'radio' === field.get( 'type' ) || 'dropdown' === field.get( 'type' ) ) {
							option = document.createElement( 'option' );
							option.innerHTML = field.get( 'slug' );
							option.value = field.get( 'slug' );

							if ( field.get( 'slug' ) === nameField ) {
								option.selected = true;
							}

							emailNotificationFromNameField.appendChild( option );

							nameFieldsAdded++;
						} if ( 'single-line-text' === field.get( 'type' ) || 'radio' === field.get( 'type' ) || 'dropdown' === field.get( 'type' ) ) {
							// @Todo: add more applicable fields

							option = document.createElement( 'option' );
							option.innerHTML = field.get( 'slug' );
							option.value = field.get( 'slug' );

							if ( field.get( 'slug' ) === subjectField ) {
								option.selected = true;
							}

							emailNotificationSubjectField.appendChild( option );

							subjectFieldsAdded++;
						}
					});
				}

				if ( 0 === addressFieldsAdded ) {
					option = document.createElement( 'option' );
					option.innerHTML = ccfSettings.noEmailFields;
					option.value = '';
					emailNotificationFromField.appendChild( option );
					emailNotificationFromField.disabled = true;
				}

				if ( 0 === nameFieldsAdded ) {
					option = document.createElement( 'option' );
					option.innerHTML = ccfSettings.noNameFields;
					option.value = '';
					emailNotificationFromNameField.appendChild( option );
					emailNotificationFromNameField.disabled = true;
				}

				if ( 0 === subjectFieldsAdded ) {
					option = document.createElement( 'option' );
					option.innerHTML = ccfSettings.noApplicableFields;
					option.value = '';
					emailNotificationSubjectField.appendChild( option );
					emailNotificationSubjectField.disabled = true;
				}
			},

			toggleNotificationFields: function() {
				var i;

				var emailNotificationFromAddress = this.el.querySelectorAll( '.email-notification-from-address' )[0];

				var emailNotificationFromField = this.el.querySelectorAll( '.email-notification-from-field' )[0];

				var emailNotificationFromType = this.el.querySelectorAll( '.form-email-notification-from-type' )[0];

				var emailNotificationSubject = this.el.querySelectorAll( '.email-notification-subject' )[0];

				var emailNotificationSubjectField = this.el.querySelectorAll( '.email-notification-subject-field' )[0];

				var emailNotificationSubjectType = this.el.querySelectorAll( '.form-email-notification-subject-type' )[0];

				var emailNotificationFromName = this.el.querySelectorAll( '.email-notification-from-name' )[0];

				var emailNotificationFromNameField = this.el.querySelectorAll( '.email-notification-from-name-field' )[0];

				var emailNotificationFromNameType = this.el.querySelectorAll( '.form-email-notification-from-name-type' )[0];

				emailNotificationFromAddress.style.display = 'none';
				emailNotificationFromField.style.display = 'none';

				if ( 'custom' === emailNotificationFromType.value ) {
					emailNotificationFromAddress.style.display = 'block';
				} else if ( 'field' === emailNotificationFromType.value ) {
					emailNotificationFromField.style.display = 'block';
				}

				emailNotificationSubject.style.display = 'none';
				emailNotificationSubjectField.style.display = 'none';

				if ( 'custom' === emailNotificationSubjectType.value ) {
					emailNotificationSubject.style.display = 'block';
				} else if ( 'field' === emailNotificationSubjectType.value ) {
					emailNotificationSubjectField.style.display = 'block';
				}

				emailNotificationFromName.style.display = 'none';
				emailNotificationFromNameField.style.display = 'none';

				if ( 'custom' === emailNotificationFromNameType.value ) {
					emailNotificationFromName.style.display = 'block';
				} else if ( 'field' === emailNotificationFromNameType.value ) {
					emailNotificationFromNameField.style.display = 'block';
				}
			},

			save: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				if ( 'edit' !== this.context ) {
					return;
				}

				var emailNotificationTitle = this.el.querySelectorAll( '.form-email-notification-title' )[0].value;
				this.model.set( 'title', emailNotificationTitle );

				var emailNotificationContent = this.el.querySelectorAll( '.form-email-notification-content' )[0].value;
				this.model.set( 'content', emailNotificationContent );

				var emailNotificationActive = this.el.querySelectorAll( '.form-email-notification-active' )[0].value;
				this.model.set( 'active', ( '1' === emailNotificationActive ) ? true : false );

				var emailNotificationFromType = this.el.querySelectorAll( '.form-email-notification-from-type' )[0].value;
				this.model.set( 'fromType', emailNotificationFromType );

				var emailNotificationFromAddress = this.el.querySelectorAll( '.form-email-notification-from-address' )[0].value;
				this.model.set( 'fromAddress', emailNotificationFromAddress );

				var emailNotificationFromField = this.el.querySelectorAll( '.form-email-notification-from-field' )[0].value;
				this.model.set( 'fromField', emailNotificationFromField );

				var emailNotificationFromNameType = this.el.querySelectorAll( '.form-email-notification-from-name-type' )[0].value;
				this.model.set( 'fromNameType', emailNotificationFromNameType );

				var emailNotificationFromName = this.el.querySelectorAll( '.form-email-notification-from-name' )[0].value;
				this.model.set( 'fromName', emailNotificationFromName );

				var emailNotificationFromNameField = this.el.querySelectorAll( '.form-email-notification-from-name-field' )[0].value;
				this.model.set( 'fromNameField', emailNotificationFromNameField );

				var emailNotificationSubjectType = this.el.querySelectorAll( '.form-email-notification-subject-type' )[0].value;
				this.model.set( 'subjectType', emailNotificationSubjectType );

				var emailNotificationSubject = this.el.querySelectorAll( '.form-email-notification-subject' )[0].value;
				this.model.set( 'subject', emailNotificationSubject );

				var emailNotificationSubjectField = this.el.querySelectorAll( '.form-email-notification-subject-field' )[0].value;
				this.model.set( 'subjectField', emailNotificationSubjectField );

				for ( var i = 0; i < this.addressViews.length; i++ ) {
					this.addressViews[i].save();
				}

				return this;

			},

			render: function() {
				var context = {
					context: this.context,
					form: this.form.toJSON()
				};

				if ( this.model ) {
					context.notification = this.model.toJSON();
				}

				this.el.innerHTML = this.template( context );

				if ( 'edit' === this.context) {
					this.toggleNotificationFields();
					this.updateFromFieldField();
					this.updateFieldVariables();

					var addressesContainer = this.el.querySelectorAll( '.addresses' )[0];
					var addresses = this.model.get( 'addresses' );

					if ( addresses.length >= 1 ) {
						addresses.each( function( model ) {
							var address = new wp.ccf.views.FormNotificationAddress( { model: model, parent: this, notification: this.model, form: this.form } ).render();
							addressesContainer.appendChild( address.el );
							this.addressViews.push( address );
						}, this );
					} else {
						var newAddress = new wp.ccf.models.FormNotificationAddress();
						addresses.add( newAddress );
					}
				}

				var fields = this.form.get( 'fields' );

				this.listenTo( fields, 'add', this.updateFromFieldField, this );
				this.listenTo( fields, 'remove', this.updateFromFieldField, this );
				this.listenTo( fields, 'add', this.updateFieldVariables, this );
				this.listenTo( fields, 'remove', this.updateFieldVariables, this );

				return this;
			},

			triggerDelete: function() {
				this.parent.deleteNotification( this );
			}
		}
	);

	wp.ccf.views.FieldBase = wp.ccf.views.FieldBase || Backbone.View.extend(
		{
			events: {
				'blur input': 'saveField',
				'blur input.field-slug': 'checkSlug',
				'blur textarea': 'saveField',
				'change select': 'saveField',
				'change input[type="checkbox"]': 'saveField'
			},

			initialize: function() {
				var conditionals = this.model.get( 'conditionals' );
				this.listenTo( conditionals, 'add', this.addConditional );
			},

			addConditional: function( model ) {
				var view = new wp.ccf.views.FieldConditional( { model: model, field: this.model, fieldCollection: this.collection } ).render();
				var conditionals = this.el.querySelectorAll( '.conditionals' )[0];

				conditionals.appendChild( view.el );
			},

			checkSlug: function() {
				var slugSelection = this.el.querySelectorAll( '.field-slug');

				if ( slugSelection.length > 0 ) {
					var slug = slugSelection[0];
					var duplicate = false;

					if ( slug.value && ! slug.value.match( /^[a-zA-Z0-9\-_]+$/ ) ) {
						slug.parentNode.className = slug.parentNode.className.replace( / field-error/i, '' ) + ' field-error';
					} else {
						slug.parentNode.className = slug.parentNode.className.replace( / field-error/i, '' );
					}

					if ( this.collection.length > 0 && '' !== slug.value ) {
						this.collection.each( function( field ) {
							if ( field !== this.model && slug.value === field.get( 'slug' ) ) {
								duplicate = true;
							}
						}, this );

						if ( duplicate ) {
							slug.parentNode.className = slug.parentNode.className.replace( / field-duplicate-slug/i, '' ) + ' field-duplicate-slug';
						} else {
							slug.parentNode.className = slug.parentNode.className.replace( / field-duplicate-slug/i, '' );
						}
					} else {
						slug.parentNode.className = slug.parentNode.className.replace( / field-duplicate-slug/i, '' );
					}
				}

			},

			destroy: function() {
				this.unbind();
			},

			saveField: function() {
				var conditionals = this.el.querySelectorAll( '.conditionals' )[0].querySelectorAll( '.conditional' );

				_.each( conditionals, function( conditional ) {
					$( conditional ).trigger( 'saveConditional' );
				});

				this.model.set( 'conditionalType', this.el.querySelectorAll( '.field-conditional-type' )[0].value );
				this.model.set( 'conditionalFieldsRequired', this.el.querySelectorAll( '.field-conditional-fields-required' )[0].value );

				var oldConditionals = this.model.get( 'conditionalsEnabled' );
				this.model.set( 'conditionalsEnabled', ( this.el.querySelectorAll( '.field-conditionals-enabled' )[0].value == 1 ) ? true : false );

				if ( oldConditionals !== this.model.get( 'conditionalsEnabled' ) ) {
					this.render( 'advanced' );
				}
			},

			render: function( startPanel ) {
				startPanel = ( startPanel ) ? startPanel : 'basic';

				this.el.innerHTML = this.template( { field: this.model.toJSON(), startPanel: startPanel } );

				this.checkSlug();

				var conditionalsCollection = this.model.get( 'conditionals' );

				var conditionals = this.el.querySelectorAll( '.conditionals' )[0];

				if ( conditionalsCollection.length >= 1 ) {

					conditionalsCollection.each( function( model ) {
						var view = new wp.ccf.views.FieldConditional( { model: model, field: this.model, fieldCollection: this.collection } ).render();
						conditionals.appendChild( view.el );
					}, this );
				} else {
					var conditional = new wp.ccf.models.FieldConditional();
					conditionalsCollection.add( conditional );
				}

				return this;
			}
		}
	);

	wp.ccf.views.Fields['single-line-text'] = wp.ccf.views.Fields['single-line-text'] || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-single-line-text-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );
				this.model.set( 'value', this.el.querySelectorAll( '.field-value' )[0].value );
				this.model.set( 'placeholder', this.el.querySelectorAll( '.field-placeholder' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields.file = wp.ccf.views.Fields.file || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-file-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );
				this.model.set( 'fileExtensions', this.el.querySelectorAll( '.field-file-extensions' )[0].value );
				this.model.set( 'maxFileSize', this.el.querySelectorAll( '.field-max-file-size' )[0].value );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields.recaptcha = wp.ccf.views.Fields.recaptcha || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-recaptcha-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );
				this.model.set( 'siteKey', this.el.querySelectorAll( '.field-site-key' )[0].value );
				this.model.set( 'secretKey', this.el.querySelectorAll( '.field-secret-key' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields['section-header'] = wp.ccf.views.Fields['section-header'] || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-section-header-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'heading', this.el.querySelectorAll( '.field-heading' )[0].value );
				this.model.set( 'subheading', this.el.querySelectorAll( '.field-subheading' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields.html = wp.ccf.views.Fields.html || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-html-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'html', this.el.querySelectorAll( '.field-html' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields['paragraph-text'] = wp.ccf.views.Fields['paragraph-text'] || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-paragraph-text-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );
				this.model.set( 'value', this.el.querySelectorAll( '.field-value' )[0].value );
				this.model.set( 'placeholder', this.el.querySelectorAll( '.field-placeholder' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields.hidden = wp.ccf.views.Fields.hidden || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-hidden-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'value', this.el.querySelectorAll( '.field-value' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields.date = wp.ccf.views.Fields.date || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-date-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );

				this.constructor.__super__.saveField.apply( this, arguments );

				var value = this.el.querySelectorAll( '.field-value' );
				if ( value.length > 0 ) {
					this.model.set( 'value', value[0].value );
				}

				var dateFormat = this.el.querySelectorAll( '.field-date-format' );
				if ( dateFormat.length ) {
					this.model.set( 'dateFormat', dateFormat[0].value );
				}

				var oldShowDate = this.model.get( 'showDate' );
				var showDate = ( this.el.querySelectorAll( '.field-show-date' )[0].checked ) ? true : false;
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'showDate', showDate );

				var oldShowTime = this.model.get( 'showTime' );
				var showTime = ( this.el.querySelectorAll( '.field-show-time' )[0].checked ) ? true : false;

				this.model.set( 'showTime', showTime );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );

				if ( showTime != oldShowTime || showDate != oldShowDate ) {
					this.render();
				}

				return this;
			}
		}
	);

	wp.ccf.views.Fields.name = wp.ccf.views.Fields.name || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-name-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields.website = wp.ccf.views.Fields.website || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-website-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );
				this.model.set( 'value', this.el.querySelectorAll( '.field-value' )[0].value );
				this.model.set( 'placeholder', this.el.querySelectorAll( '.field-placeholder' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields.phone = wp.ccf.views.Fields.phone || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-phone-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );
				this.model.set( 'value', this.el.querySelectorAll( '.field-value' )[0].value );
				this.model.set( 'placeholder', this.el.querySelectorAll( '.field-placeholder' )[0].value );
				this.model.set( 'phoneFormat', this.el.querySelectorAll( '.field-phone-format' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields.address = wp.ccf.views.Fields.address || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-address-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );
				this.model.set( 'addressType', this.el.querySelectorAll( '.field-address-type' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );

				this.constructor.__super__.saveField.apply( this, arguments );

				return this;
			}
		}
	);

	wp.ccf.views.Fields.email = wp.ccf.views.Fields.email || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-email-template' ),

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );

				this.constructor.__super__.saveField.apply( this, arguments );

				var value = this.el.querySelectorAll( '.field-value' );
				if ( value.length ) {
					this.model.set( 'value', value[0].value );
				}

				var placeholder = this.el.querySelectorAll( '.field-placeholder' );
				if ( placeholder.length ) {
					this.model.set( 'placeholder', placeholder[0].value );
				}

				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );

				var emailConfirmation = ( this.el.querySelectorAll( '.field-email-confirmation' )[0].value == 1 ) ? true : false;
				var oldEmailConfirmation = this.model.get( 'emailConfirmation' );

				this.model.set( 'emailConfirmation', emailConfirmation );

				if ( oldEmailConfirmation != emailConfirmation ) {
					this.render();
				}

				return this;
			}
		}
	);

	wp.ccf.views.ChoiceableField = wp.ccf.views.ChoiceableField || wp.ccf.views.FieldBase.extend(
		{
			template: wp.ccf.utils.template( 'ccf-dropdown-template' ),

			initialize: function() {
				var choices = this.model.get( 'choices' );
				this.listenTo( choices, 'add', this.addChoice );
			},

			addChoice: function( model ) {
				var view = new wp.ccf.views.FieldChoice( { model: model, field: this.model } ).render();
				var choices = this.el.querySelectorAll( '.repeatable-choices' )[0];

				choices.appendChild( view.el );
			},

			saveField: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				this.model.set( 'slug', this.el.querySelectorAll( '.field-slug' )[0].value );
				this.model.set( 'label', this.el.querySelectorAll( '.field-label' )[0].value );
				this.model.set( 'description', this.el.querySelectorAll( '.field-description' )[0].value );
				this.model.set( 'className', this.el.querySelectorAll( '.field-class-name' )[0].value );
				this.model.set( 'required', ( this.el.querySelectorAll( '.field-required' )[0].value == 1 ) ? true : false  );

				wp.ccf.views.ChoiceableField.__super__.saveField.apply( this, arguments );

				var choices = this.el.querySelectorAll( '.repeatable-choices' )[0].querySelectorAll( '.choice' );

				_.each( choices, function( choice ) {
					$( choice ).trigger( 'saveChoice' );
				});

				return this;

			},

			render: function( startPanel ) {
				var SELF = this;

				startPanel = ( startPanel ) ? startPanel : 'basic';

				SELF.el.innerHTML = SELF.template( { field: SELF.model.toJSON(), startPanel: startPanel } );

				SELF.checkSlug();

				var choicesCollection = SELF.model.get( 'choices' );

				var choices = this.el.querySelectorAll( '.repeatable-choices' )[0];

				if ( choicesCollection.length >= 1 ) {

					choicesCollection.each( function( model ) {
						var view = new wp.ccf.views.FieldChoice( { model: model, field: SELF.model } ).render();
						choices.appendChild( view.el );
					});
				} else {
					var choice = new wp.ccf.models.FieldChoice();
					choicesCollection.add( choice );
				}

				choices = this.el.querySelectorAll( '.repeatable-choices' )[0];

				$( choices ).sortable( {
					handle: '.move',
					axis: 'y',
					stop: function( event, $ui ) {
						$ui.item.trigger( 'sorted', $ui.item.index() );
					}
				});

				var conditionalsCollection = this.model.get( 'conditionals' );

				var conditionals = this.el.querySelectorAll( '.conditionals' )[0];

				if ( conditionalsCollection.length >= 1 ) {

					conditionalsCollection.each( function( model ) {
						var view = new wp.ccf.views.FieldConditional( { model: model, field: this.model, fieldCollection: this.collection } ).render();
						conditionals.appendChild( view.el );
					}, this );
				} else {
					var conditional = new wp.ccf.models.FieldConditional();
					conditionalsCollection.add( conditional );
				}

				return SELF;
			}
		}
	);

	wp.ccf.views.Fields.dropdown = wp.ccf.views.Fields.dropdown || wp.ccf.views.ChoiceableField.extend(
		{
			template: wp.ccf.utils.template( 'ccf-dropdown-template' ),
			events: function() {
				return this.constructor.__super__.events;
			}
		}
	);

	wp.ccf.views.Fields.radio = wp.ccf.views.Fields.radio || wp.ccf.views.ChoiceableField.extend(
		{
			template: wp.ccf.utils.template( 'ccf-radio-template' ),
			events: function() {
				return this.constructor.__super__.events;
			}
		}
	);

	wp.ccf.views.Fields.checkboxes = wp.ccf.views.Fields.checkboxes || wp.ccf.views.ChoiceableField.extend(
		{
			template: wp.ccf.utils.template( 'ccf-checkboxes-template' ),
			events: function() {
				return this.constructor.__super__.events;
			}
		}
	);

	wp.ccf.views.FieldSidebar = wp.ccf.views.FieldSidebar || Backbone.View.extend(
		{
			initialize: function( options ) {
				this.currentFieldView = null;
				this.form = options.form;
			},

			save: function( $promise ) {
				if ( this.currentFieldView ) {

					// @todo: fix this ie8 hack
					if ( this.currentFieldView.el.innerHTML !== '' ) {
						this.currentFieldView.saveField();
					}
				}

				if ( $promise && $promise instanceof Object ) {
					$promise.resolve();
				}
			},

			fieldRemoved: function() {
				if ( this.currentFieldView ) {
					if ( ! this.form.get( 'fields' ).get( this.currentFieldView.model ) ) {
						this.render();
					}
				}
			},

			destroy: function() {
				wp.ccf.dispatcher.off( 'saveField', this.save );
				wp.ccf.dispatcher.off( 'mainViewChange', this.save );
				this.unbind();
			},

			render: function( field ) {
				var context = {};

				if ( ! field ) {
					var template = wp.ccf.utils.template( 'ccf-empty-field-template' );
					this.el.innerHTML = template( context );
				} else {
					var type = field.get( 'type' );

					if ( this.currentFieldView ) {
						this.currentFieldView.saveField();

						if ( this.currentFieldView.destroy ) {
							this.currentFieldView.destroy();
						}
					}

					this.currentFieldView = new wp.ccf.views.Fields[type]( { model: field, collection: this.form.get( 'fields' ) } );

					this.currentFieldView.render();

					this.el.innerHTML = '';

					this.el.appendChild( this.currentFieldView.el );

					var fields = this.form.get( 'fields' );
					this.listenTo( fields, 'remove', this.fieldRemoved );
				}

				wp.ccf.dispatcher.on( 'saveField', this.save, this );
				wp.ccf.dispatcher.on( 'mainViewChange', this.save, this );

				return this;
			}
		}
	);

	wp.ccf.views.FieldRowPlaceholder = wp.ccf.views.FieldRowPlaceholder || Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-field-row-template'),
			tagName: 'div',
			className: 'field',

			initialize: function( options ) {
				this.type = options.type;
			},

			render: function() {
				this.el.innerHTML = this.template( { label: ccfSettings.allLabels[this.type] } );

				this.el.setAttribute( 'data-field-type', this.type );
				this.el.className += ' ' + this.type;

				return this;
			}
		}
	);

	wp.ccf.views.FieldRow = wp.ccf.views.FieldRow || Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-field-row-template'),
			tagName: 'div',
			className: 'field',

			events: {
				'click .delete': 'triggerDelete',
				'click h4': 'triggerEdit',
				'sorted': 'triggerUpdateSort'
			},

			initialize: function( options ) {
				_.bindAll( this, 'triggerDelete' );
				this.form = options.form;

				this.listenTo( this.model, 'change', this.handleChange, this );
				this.listenTo( this.model, 'requirementsNotMet', this.requirementsNotMet, this );
				this.listenTo( this.model, 'requirementsMet', this.requirementsMet, this );
				this.listenTo( this.model, 'duplicateSlug', this.duplicateSlug, this );

				if ( this.model.attributes.choices ) {
					this.listenTo( this.model.attributes.choices, 'change', this.handleChange, this );
				}
			},

			duplicateSlug: function() {
				this.requirementsMet();
				this.el.className += ' field-duplicate-slug';
			},

			requirementsNotMet: function() {
				this.requirementsMet();
				this.el.className += ' field-incomplete';
			},

			requirementsMet: function() {
				this.el.className = this.el.className.replace( /(field-incomplete|field-duplicate-slug)/i, '' );
			},

			triggerUpdateSort: function( event, index ) {
				this.form.get( 'fields' ).remove( this.model );

				this.form.get( 'fields' ).add( this.model, { at: index } );
			},

			handleChange: function( event ) {
				this.render();
			},

			triggerDelete: function( event ) {
				event.stopPropagation();

				this.form.get( 'fields' ).remove( this.model );
				this.undelegateEvents();
				this.remove();
			},

			triggerEdit: function( event ) {
				var editing = this.el.parentNode.querySelectorAll( '.ccf-editing' );
				_.each( editing, function( node ) {
					node.className = node.className.replace( /ccf-editing/i, '' );
				});

				this.el.className = this.el.className.replace( /ccf-editing/i, '' ) + ' ccf-editing';
				wp.ccf.dispatcher.trigger( 'openEditField', this.model );
			},

			render: function( instantiate ) {
				this.el.innerHTML = this.template( { label: ccfSettings.allLabels[this.model.get( 'type' )] } );

				this.el.setAttribute( 'data-field-type', this.model.get( 'type' ) );

				var regex = new RegExp( ' ' + this.model.get( 'type' ), 'i' );

				this.el.className = this.el.className.replace( regex, '' ) + ' ' + this.model.get( 'type' );

				if ( instantiate ) {
					this.el.className = this.el.className.replace( / instantiated/i, '' ) + ' instantiated';
				}

				var previewTemplate = document.getElementById( 'ccf-' + this.model.get( 'type' ) + '-preview-template' );

				if ( previewTemplate ) {
					var preview = this.el.querySelectorAll( '.preview' )[0];
					preview.style.display = 'block';
					preview.innerHTML = wp.ccf.utils.template( 'ccf-' + this.model.get( 'type' ) + '-preview-template' )( { field: this.model.toJSON() } );
				}

				return this;
			}
		}
	);

	wp.ccf.views.PostFieldMapping = Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-post-field-mapping' ),
			className: 'field-mapping',

			events: {
				'click .add': 'triggerAdd',
				'click .delete': 'triggerDelete',
				'blur input': 'save',
				'change select': 'save'
			},

			initialize: function( options ) {
				this.parent = options.parent;
				this.form = options.form;
			},

			destroy: function() {
				this.unbind();
			},

			save: function() {
				// @todo: fix this ie8 hack
				if ( this.el.innerHTML === '' ) {
					return;
				}

				var formField = this.el.querySelectorAll( '.field-form-field' )[0].value;
				var postField = this.el.querySelectorAll( '.field-post-field' )[0].value;
				var customFieldKey = this.el.querySelectorAll( '.field-custom-field-key' );

				var oldPostField = this.model.get( 'postField' );
				
				this.model.set( 'formField', formField );
				this.model.set( 'postField', postField );

				if ( customFieldKey.length ) {
					this.model.set( 'customFieldKey', customFieldKey[0].value );
				}

				if ( oldPostField !== postField ) {
					this.render();
				}

				return this;

			},

			updateFormFieldField: function() {
				var fieldFormField = this.el.querySelectorAll( '.field-form-field' )[0];
				fieldFormField.innerHTML = '';
				fieldFormField.disabled = false;

				var fields = this.form.get( 'fields' ),
					fieldsAdded = 0;

				var formField = this.model.get( 'formField' ),
					option;

				if ( fields.length >= 1 ) {
					option = document.createElement( 'option' );
					option.innerHTML = ccfSettings.chooseFormField;
					option.value = '';

					fieldFormField.appendChild( option );

					fields.each( function( field ) {
						option = document.createElement( 'option' );
						option.innerHTML = field.get( 'slug' );
						option.value = field.get( 'slug' );

						if ( field.get( 'slug' ) === formField ) {
							option.selected = true;
						}

						fieldFormField.appendChild( option );

						fieldsAdded++;
					});
				}

				if ( 0 === fieldsAdded ) {
					option = document.createElement( 'option' );
					option.innerHTML = ccfSettings.noAvailableFields;
					option.value = '';
					fieldFormField.appendChild( option );
					fieldFormField.disabled = true;
				}
			},

			updatePostFields: function() {
				var dropdown = this.el.querySelectorAll( '.field-post-field' )[0],
					option;

				option = document.createElement( 'option' );
				option.value = '';
				option.innerText = ccfSettings.choosePostField;
				dropdown.appendChild( option );

				var mappings = this.form.get( 'postFieldMappings' );
				var usedFields = [];

				mappings.each( function( model ) {
					if ( model !== this.model ) {
						usedFields.push( model.get( 'postField' ) );
					}
				}, this );

				_.each( ccfSettings.postFields.single, function( field, slug ) {
					if ( -1 === usedFields.indexOf( slug ) ) {
						option = document.createElement( 'option' );
						option.value = slug;
						option.innerText = field;

						if ( this.model.get( 'postField' ) === slug ) {
							option.selected = true;
						}

						dropdown.appendChild( option );
					}
				}, this );

				_.each( ccfSettings.postFields.repeatable, function( field, slug ) {

					option = document.createElement( 'option' );
					option.value = slug;
					option.innerText = field;

					if ( this.model.get( 'postField' ) === slug ) {
						option.selected = true;
					}

					dropdown.appendChild( option );

				}, this );
			},

			render: function() {
				var context = {};
				if ( this.model ) {
					context.mapping = this.model.toJSON();
				}

				this.el.innerHTML = this.template( context );

				var fields = this.form.get( 'fields' );

				this.listenTo( fields, 'add', this.updateFormFieldField, this );
				this.listenTo( fields, 'remove', this.updateFormFieldField, this );

				this.updateFormFieldField();
				this.updatePostFields();

				return this;
			},

			triggerAdd: function() {
				this.form.get( 'postFieldMappings' ).add( new wp.ccf.models.PostFieldMapping() );
			},

			triggerDelete: function() {
				var mappings = this.form.get( 'postFieldMappings' );

				if ( mappings.length > 1 ) {
					this.parent.deletePostFieldMapping( this );
				} else {
					this.model.clear().set( wp.ccf.models.PostFieldMapping.prototype.defaults );
					this.destroy();
					this.render();
				}
			}
		}
	);

	wp.ccf.views.FormSettings = wp.ccf.views.FormSettings || Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-form-settings-template' ),

			events: {
				'blur input': 'save',
				'change select': 'save',
				'change select.form-completion-action-type': 'toggleCompletionFields',
				'change select.form-pause': 'togglePauseFields',
				'change select.form-post-creation': 'togglePostCreationFields',
				'click .add-notification': 'triggerAddNotification'
			},

			notificationViews: [],
			mappingViews: [],

			initialize: function( options ) {
				this.model = options.form;
				this.notificationViews = [];
				this.mappingViews = [];

				var notifications = this.model.get( 'notifications' );
				this.listenTo( notifications, 'add', this.addNotification );

				var mappings = this.model.get( 'postFieldMappings' );
				this.listenTo( mappings, 'add', this.addPostFieldMapping );
			},

			deletePostFieldMapping: function( view ) {
				_.each( this.mappingViews, function( currentView ) {
					if ( view.cid === currentView.cid ) {
						var index = _.indexOf( this.mappingViews, currentView );
						this.model.get( 'postFieldMappings' ).remove( view.model );
						this.mappingViews[index].remove();
						this.mappingViews.splice( index, 1 );
					}
				}, this );
			},

			addPostFieldMapping: function( model ) {
				var mappingContainer = this.el.querySelectorAll( '.post-creation-mapping' )[0];
				var view = new wp.ccf.views.PostFieldMapping( { model: model, parent: this, form: this.model } );
				this.mappingViews.push( view );
				mappingContainer.appendChild( view.render().el );
			},

			triggerAddNotification: function() {
				var notifications = this.model.get( 'notifications' );

				notifications.add( new wp.ccf.models.FormNotification() );
			},

			closeAllNotifications: function() {
				_.each( this.notificationViews, function( view ) {
					view.changeContext( null, 'view' );
				} );
			},

			addNotification: function( model ) {
				var view = new wp.ccf.views.ExistingFormNotificationRow( { model: model, form: this.model, context: 'edit', parent: this } ).render();
				var rowContainer = this.el.querySelectorAll( '.ccf-form-notifications .rows' )[0];

				if ( rowContainer.querySelectorAll( '.no-notifications' ).length > 0 ) {
					rowContainer.removeChild( rowContainer.firstChild );
				}

				_.each( this.notificationViews, function( view ) {
					view.changeContext( null, 'view' );
				} );

				this.notificationViews.push( view );

				rowContainer.appendChild( view.el );
			},

			toggleCompletionFields: function() {
				var completionActionType = this.el.querySelectorAll( '.form-completion-action-type' )[0].value;

				var completionMessage = this.el.querySelectorAll( '.completion-message' )[0];
				var completionRedirect = this.el.querySelectorAll( '.completion-redirect-url' )[0];

				if ( 'text' === completionActionType ) {
					completionMessage.style.display = 'block';
					completionRedirect.style.display = 'none';
				} else {
					completionMessage.style.display = 'none';
					completionRedirect.style.display = 'block';
				}
			},

			togglePauseFields: function() {

				var pause = this.el.querySelectorAll( '.form-pause' )[0].value;
				var pauseMessage = this.el.querySelectorAll( '.pause-message' )[0];

				if ( parseInt( pause ) ) {
					pauseMessage.style.display = 'block';
				} else {
					pauseMessage.style.display = 'none';
				}
			},

			togglePostCreationFields: function() {

				var postCreation = this.el.querySelectorAll( '.form-post-creation' )[0].value;
				var $postCreationMappingFields = $( this.el.querySelectorAll( '.post-creation-mapping-field' ) );

				if ( parseInt( postCreation ) ) {
					$postCreationMappingFields.show();
				} else {
					$postCreationMappingFields.hide();
				}
			},

			save: function() {
				if ( this.el.innerHTML === '' ) {
					// @todo: for some reason this is needed for IE8
					return;
				}

				var title = this.el.querySelectorAll( '.form-title' )[0].value;
				this.model.set( 'title', { raw: title } );

				var description = this.el.querySelectorAll( '.form-description' )[0].value;
				this.model.set( 'description', description );

				var buttonText = this.el.querySelectorAll( '.form-button-text' )[0].value;
				this.model.set( 'buttonText', buttonText );

				var buttonClass = this.el.querySelectorAll( '.form-button-class' )[0].value;
				this.model.set( 'buttonClass', buttonClass );

				var pause = this.el.querySelectorAll( '.form-pause' )[0].value;
				this.model.set( 'pause', ( parseInt( pause ) ) ? true : false );

				var postCreation = this.el.querySelectorAll( '.form-post-creation' )[0].value;
				this.model.set( 'postCreation', ( parseInt( postCreation ) ) ? true : false );

				var postCreationType = this.el.querySelectorAll( '.form-post-creation-type' )[0].value;
				this.model.set( 'postCreationType', postCreationType );

				var postCreationStatus = this.el.querySelectorAll( '.form-post-creation-status' )[0].value;
				this.model.set( 'postCreationStatus', postCreationStatus );

				var pauseMessage = this.el.querySelectorAll( '.form-pause-message' )[0].value;
				this.model.set( 'pauseMessage', pauseMessage );

				var completionMessage = this.el.querySelectorAll( '.form-completion-message' )[0].value;
				this.model.set( 'completionMessage', completionMessage );

				var completionRedirectUrl = this.el.querySelectorAll( '.form-completion-redirect-url' )[0].value;
				this.model.set( 'completionRedirectUrl', completionRedirectUrl );

				var completionActionType = this.el.querySelectorAll( '.form-completion-action-type' )[0].value;
				this.model.set( 'completionActionType', completionActionType );

				var theme = this.el.querySelectorAll( '.form-theme' )[0].value;
				this.model.set( 'theme', theme );
			},

			fullSave: function( $promise ) {
				if ( this.el.innerHTML === '' ) {
					// @todo: for some reason this is needed for IE8
					return;
				}

				this.save();

				_.each( this.notificationViews, function( view ) {
					view.save();
				} );

				_.each( this.mappingViews, function( view ) {
					view.save();
				} );

				if ( typeof $promise !== 'undefined' && typeof $promise.promise !== 'undefined' ) {
					$promise.resolve();
				}
			},

			destroy: function() {
				wp.ccf.dispatcher.off( 'saveFormSettings', this.fullSave );
				wp.ccf.dispatcher.off( 'mainViewChange', this.fullSave );
			},

			deleteNotification: function( view ) {
				_.each( this.notificationViews, function( currentView ) {
					if ( view.cid === currentView.cid ) {
						var index = _.indexOf( this.notificationViews, currentView );
						this.model.get( 'notifications' ).remove( view.model );
						this.notificationViews[index].remove();
						this.notificationViews.splice( index, 1 );
					}
				}, this );

				if ( ! this.notificationViews.length ) {
					var rowContainer = this.el.querySelectorAll( '.ccf-form-notifications .rows' )[0];
					rowContainer.appendChild( new wp.ccf.views.EmptyFormNotificationTableRow( { form: this.model } ).render().el );
				}
			},

			render: function() {
				var context = {
					form: this.model.toJSON()
				};

				var fields = this.model.get( 'fields' );
				var notifications = this.model.get( 'notifications' );

				this.el.innerHTML = this.template( context );

				this.toggleCompletionFields();
				this.togglePostCreationFields();
				this.togglePauseFields();

				var rowContainer = this.el.querySelectorAll( '.ccf-form-notifications .rows' )[0];
				var newRowContainer = document.createElement( 'tbody');

				newRowContainer.className = 'rows';

				if ( notifications.length >= 1 ) {
					notifications.each( function( model ) {
						var row = new wp.ccf.views.ExistingFormNotificationRow( { model: model, form: this.model, parent: this } ).render();
						newRowContainer.appendChild( row.el );
						this.notificationViews.push( row );
					}, this );
				} else {
					newRowContainer.appendChild( new wp.ccf.views.EmptyFormNotificationTableRow( { form: this.model } ).render().el );
				}

				rowContainer.parentNode.replaceChild( newRowContainer, rowContainer );

				var mappingsContainer = this.el.querySelectorAll( '.post-creation-mapping' )[0];
				var mappings = this.model.get( 'postFieldMappings' );

				if ( mappings.length >= 1 ) {
					mappings.each( function( model ) {
						var mapping = new wp.ccf.views.PostFieldMapping( { model: model, parent: this, form: this.model } ).render();
						mappingsContainer.appendChild( mapping.el );
						this.mappingViews.push( mapping );
					}, this );
				} else {
					var newMapping = new wp.ccf.models.PostFieldMapping();
					mappings.add( newMapping );
				}

				wp.ccf.dispatcher.on( 'mainViewChange', this.fullSave, this );
				wp.ccf.dispatcher.on( 'saveFormSettings', this.fullSave, this );

				return this;
			}
		}
	);

	wp.ccf.views.FormPane = wp.ccf.views.FormPane || Backbone.View.extend( _.defaults(
		{
			template: wp.ccf.utils.template( 'ccf-form-pane-template' ),
			subViews: {
				'field-sidebar': wp.ccf.views.FieldSidebar,
				'form-settings': wp.ccf.views.FormSettings
			},

			events: {
				'click .save-button': 'sync',
				'click .signup-button': 'signup',
				'click .accordion-heading': 'accordionClick',
				'click .form-settings-heading': 'accordionClick',
				'click .insert-form-button': 'insertForm'
			},

			initialize: function() {
				wp.ccf.dispatcher.on( 'openEditField', this.openEditField, this );
			},

			insertForm: function( event ) {
				wp.ccf.utils.insertFormShortcode( this.model );

				wp.ccf.toggle();
			},

			signup: function( event ) {
				var email = this.el.querySelectorAll( '.email-signup-field' )[0].value;
				var signupContainer = this.el.querySelectorAll( '.bottom .left.signup' )[0];
				signupContainer.className = 'left signup';

				if (email) {
					$.ajax( {
						url: '//taylorlovett.us8.list-manage.com/subscribe/post-json?u=66118f9a5b0ab0414e83f043a&amp;id=b4ed816a24&c=?',
						method: 'post',
						dataType: 'jsonp',
						data: {
							EMAIL: email
						}
					}).done(function() {
						signupContainer.className = 'left signup signup-success';
					});
				} else {
					signupContainer.className = 'left signup signup-error';
				}
			},

			accordionClick: function( event ) {
				var parentContainer = $( event.currentTarget ).parents( '.accordion-container' )[0];

				var sections = parentContainer.querySelectorAll( '.accordion-section' );

				if ( event.currentTarget.parentNode.className.match( /expanded/i ) ) {
					event.currentTarget.parentNode.className = event.currentTarget.parentNode.className.replace( /expanded/i, '' );
				} else {
					event.currentTarget.parentNode.className += ' expanded';
				}

				_.each( sections, function( section, index ) {
					if ( section != event.currentTarget.parentNode && section.className.match( /expanded/i ) ) {
						section.className = section.className.replace( /expanded/i, '' );
					}
				});

				if ( event.currentTarget.className.match( /form-settings-heading/i ) ) {
					if ( this.el.className.match( /show-form-settings/i ) ) {
						this.el.className = this.el.className.replace( /show-form-settings/i, '' );
					} else {
						this.el.className += ' show-form-settings';
					}
				} else {
					this.el.className = this.el.className.replace( /show-form-settings/i, '' );
				}
			},

			openEditField: function( field ) {
				this.renderedSubViews['field-sidebar'].render( field ).el.style.display = 'block';
			},

			disable: function() {
				this.el.querySelectorAll( '.save-button' )[0].setAttribute( 'disabled', 'disabled' );
				this.el.querySelectorAll( '.disabled-overlay' )[0].style.display = 'block';
			},

			enable: function() {
				this.el.querySelectorAll( '.save-button' )[0].removeAttribute( 'disabled' );
				this.el.querySelectorAll( '.disabled-overlay' )[0].style.display = 'none';
			},

			sync: function() {
				var SELF = this;

				var $spinner = $( this.el.querySelectorAll( '.spinner' )[0] );
				$spinner.fadeIn();
				SELF.disable();

				var $settings = $.Deferred();
				var $field = $.Deferred();

				wp.ccf.dispatcher.trigger( 'saveFormSettings', $settings );
				wp.ccf.dispatcher.trigger( 'saveField', $field );

				$.when( $settings, $field ).then( function() {
					var fields = SELF.model.get( 'fields' );
					var allReqsMet = true;
					var slugs = {};

					fields.each( function( field ) {
						var slug = field.get( 'slug' );
						if ( ! field.hasRequiredAttributes() ) {
							allReqsMet = false;

							field.trigger( 'requirementsNotMet' );

						} else if ( slug && ! slug.match( /^[a-zA-Z0-9\-_]+$/ ) ) {
							allReqsMet = false;

							field.trigger( 'requirementsNotMet' );
						} else if ( typeof slugs[field.get( 'slug' )] !== 'undefined' ) {
							allReqsMet = false;

							field.trigger( 'duplicateSlug' );
							slugs[field.get( 'slug' )].trigger( 'duplicateSlug' );
						} else {
							field.trigger( 'requirementsMet' );
						}

						if ( field.get( 'slug' ) ) {
							slugs[field.get( 'slug' )] = field;
						}
					});

					if ( allReqsMet ) {

						SELF.model.save( {}, { context: 'edit' }).error( function( jqXHR, textStatus, errorThrown ) {
							var messageType = 'sync';

							wp.ccf.errorModal.render( messageType ).show();
						}).done( function( response ) {
							if (ccfSettings.single && ! ccfSettings.postId ) {
								window.location = ccfSettings.adminUrl + 'post.php?post=' + SELF.model.get( 'id' ) + '&action=edit#ccf-form/' + SELF.model.get( 'id' );
							}
						}).complete( function( response ) {
							$spinner.fadeOut();
							SELF.enable();

							wp.ccf.dispatcher.trigger( 'saveFormComplete', SELF.model );
						});
					} else {
						SELF.enable();
						$spinner.fadeOut();
					}
				});
			},

			enableDisableInsert: function() {
				var insertButton = this.el.querySelectorAll( '.insert-form-button' )[0];
				if ( this.model.get( 'id' ) ) {
					insertButton.removeAttribute( 'disabled' );
				} else {
					insertButton.setAttribute( 'disabled', 'disabled' );
				}
			},

			getNextFieldOrd: function() {
				var fields = this.model.get( 'fields' );
				var ord = fields.length + 1;

				fields.each( function( field ) {
					var slug = field.get( 'slug' );
					var regex = /\-([0-9]+)$/g;
					var matches = regex.exec( slug );

					if ( matches && matches[1] ) {
						var fieldOrd = parseInt( matches[1] );

						if ( fieldOrd >= ord ) {
							ord = fieldOrd + 1;
						}
					}
				});

				return ord;
			},

			render: function( form ) {
				var SELF = this;

				if ( form ) {
					SELF.model = form;
				} else {
					SELF.model = new wp.ccf.models.Form();
				}

				this.listenTo( SELF.model, 'change', this.enableDisableInsert, this );

				var context = {
					labels: ccfSettings.fieldLabels,
					form: SELF.model.toJSON()
				};

				window.form = SELF.model;

				SELF.el.innerHTML = this.template( context );

				SELF.el.className = SELF.el.className.replace( /show-form-settings/i, '' );

				var fields = SELF.el.querySelectorAll( '.fields' )[0];

				_.each( ccfSettings.fieldLabels, function( label, type ) {
					fields.appendChild( new wp.ccf.views.FieldRowPlaceholder( { type: type } ).render().el );
				});

				var structureFields = SELF.el.querySelectorAll( '.structure-fields' )[0];

				_.each( ccfSettings.structureFieldLabels, function( label, type ) {
					structureFields.appendChild( new wp.ccf.views.FieldRowPlaceholder( { type: type } ).render().el );
				});

				var specialFields = SELF.el.querySelectorAll( '.special-fields' )[0];

				_.each( ccfSettings.specialFieldLabels, function( label, type ) {
					specialFields.appendChild( new wp.ccf.views.FieldRowPlaceholder( { type: type } ).render().el );
				});

				var fieldModels = SELF.model.get( 'fields' );
				var formContent = SELF.el.querySelectorAll( '.form-content' )[0];
				var $formContent = $( formContent );

				$( SELF.el.querySelectorAll( '.left-sidebar' )[0].querySelectorAll( '.field' ) ).draggable( {
					cursor: 'move',
					distance: 2,
					zIndex: 160001,
					scroll: false,
					containment: 'document',
					appendTo: '.ccf-main-modal',
					snap: false,
					connectToSortable: '.form-content',
					helper: function( event ) {
						var $field = $( event.currentTarget );
						var $helper = $( '<div class="field" data-field-type="' + $field.attr( 'data-field-type' ) + '"><h4>' + $field.find( '.label' ).html() + '</h4></div>' );
						return $helper.css( { 'width': $formContent.width(), opacity: '.75', 'height': $field.height() } );
					}

				});

				if ( fieldModels.length >= 1 ) {
					formContent.innerHTML = '';

					fieldModels.each( function( field ) {
						var row = new wp.ccf.views.FieldRow( { model: field, form: SELF.model } ).render( true ).el;
						formContent.appendChild( row );
					});
				}

				$( formContent ).sortable( {
					axis: 'y',
					distance: 2,
					handle: 'h4',
					placeholder: 'field-placeholder',
					stop: function( event, $ui ) {
						if ( ! $ui.item.hasClass( 'instantiated' ) ) {
							var type = $ui.item.attr( 'data-field-type' );
							var defaults = {};

							if ( typeof wp.ccf.models.Fields[type].prototype.defaults().slug !== 'undefined' ) {
								defaults.slug = type + '-' + SELF.getNextFieldOrd();
							}

							var field = new wp.ccf.models.Fields[type]( defaults );
							var fields = SELF.model.get( 'fields' );

							fields.add( field );

							new wp.ccf.views.FieldRow( { model: field, el: $ui.item, form: SELF.model } ).render( true );
							$ui.item.attr( 'style', '' );
						}

						$ui.item.trigger( 'sorted', $ui.item.index() );
					}

				});

				SELF.initRenderSubViews( false, true, { form: SELF.model } );

				SELF.enableDisableInsert();

				return SELF;
			}
		}, wp.ccf.mixins.subViewable )
	);

	wp.ccf.views.ExistingFormTableRow = wp.ccf.views.ExistingFormTableRow || Backbone.View.extend(
		{
			tagName: 'tr',
			template: wp.ccf.utils.template( 'ccf-existing-form-table-row-template'),
			events: {
				'click .edit': 'triggerMainViewChange',
				'click .delete': 'triggerDelete',
				'click .duplicate': 'triggerDuplicate',
				'click .insert-form-button': 'insertForm'
			},

			initialize: function( options) {
				this.parent = options.parent;
			},

			insertForm: function( event ) {
				wp.ccf.utils.insertFormShortcode( this.model );

				wp.ccf.toggle();
			},

			triggerMainViewChange: function() {
				wp.ccf.switchToForm( this.model );
			},

			triggerDelete: function() {
				var SELF = this,
					currentPage = SELF.parent.collection.state.currentPage,
					page;

				SELF.model.destroy().done( function() {
					page = currentPage;

					if ( page === SELF.parent.collection.state.totalPages && page - 1  === ( SELF.parent.collection.state.totalObjects - 1 ) / ccfSettings.postsPerPage ) {
						page--;
					}

					SELF.parent.showPage( page ).done( function() {
						SELF.parent.renderPagination();
					});
				});
			},

			triggerDuplicate: function() {
				var SELF = this,
					currentPage = SELF.parent.collection.state.currentPage;

				SELF.model
					.clone()
					.set( 'title', { raw: SELF.model.get( 'title' ).raw + ' (Duplicate)' } )
					.unset( 'id' )
					.save()
					.done( function() {
						SELF.parent.showPage( currentPage ).done( function() {
							SELF.parent.renderPagination();
						});
					});
			},

			render: function() {
				this.$el.html( this.template( { form: this.model.toJSON(), utils: { getPrettyPostDate: wp.ccf.utils.getPrettyPostDate } } ) );
				return this;
			}
		}
	);

	wp.ccf.views.EmptyFormTableRow = wp.ccf.views.EmptyFormTableRow || Backbone.View.extend(
		{
			tagName: 'tr',
			template: wp.ccf.utils.template( 'ccf-empty-form-table-row-template'),

			render: function() {
				this.$el.html( this.template() );
				return this;
			}
		}
	);

	wp.ccf.views.ExistingFormTable = wp.ccf.views.ExistingFormTable || Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-existing-form-table-template'),

			initialize: function() {
				this.parent = arguments.parent;
				this.collection = new wp.ccf.collections.Forms();

				wp.ccf.dispatcher.on( 'changeFormTablePage', this.showPage, this );

				// IMPORTANT
				wp.ccf.dispatcher.on( 'saveFormComplete', this.render, this );
			},

			showPage: function( page ) {
				var SELF = this;

				var fetch = this.collection.fetch( { data: { page: ( page ) } });

				fetch.error( function( jqXHR, textStatus, errorThrown ) {
					var messageType = 'sync';

					wp.ccf.errorModal.render( messageType ).show();
				});

				fetch.done( function() {
					var rowContainer = SELF.el.querySelectorAll( '.rows' )[0];
					var newRowContainer = document.createElement( 'tbody');

					newRowContainer.className = 'rows';

					if ( SELF.collection.length >= 1 ) {
						SELF.collection.each( function( model ) {
							var row = new wp.ccf.views.ExistingFormTableRow( { model: model, parent: SELF } ).render();
							newRowContainer.appendChild( row.el );
						}, SELF );
					} else {
						newRowContainer.appendChild( new wp.ccf.views.EmptyFormTableRow().render().el );
					}

					rowContainer.parentNode.replaceChild( newRowContainer, rowContainer );
				});

				return fetch;
			},

			renderPagination: function() {
				var container = this.el.querySelectorAll( '.ccf-pagination' )[0];

				container.innerHTML = '';
				if ( this.collection.state.totalPages > 1 ) {
					container.appendChild( new wp.ccf.views.Pagination( { parent: this } ).render( this.collection.state.totalPages, this.collection.state.currentPage ).el );
				}
			},

			render: function( skipRefreshPage ) {
				var SELF = this;

				this.el.innerHTML = this.template();

				var pagination = this.el.querySelectorAll( '.ccf-pagination' )[0];

				this.showPage( 1 ).done( function() {
					SELF.renderPagination();
				});

				return this;
			}
		}
	);

	wp.ccf.views.ExistingFormPane = wp.ccf.views.ExistingFormPane || Backbone.View.extend( _.defaults(
		{
			template: wp.ccf.utils.template( 'ccf-existing-form-pane-template' ),
			subViews: {
				'existing-form-table': wp.ccf.views.ExistingFormTable
			},

			render: function() {

				if ( this.rendered ) {
					return this;
				}

				this.rendered = true;

				this.el.innerHTML = this.template();
				this.initRenderSubViews( true );

				return this;
			}
		}, wp.ccf.mixins.subViewable )
	);

	wp.ccf.views.MainModal = wp.ccf.views.MainModal || Backbone.View.extend( _.defaults(
		{
			tagName: 'div',
			className: 'ccf-main-modal',
			template: wp.ccf.utils.template( 'ccf-main-modal-template' ),
			events: {
				'click .close-icon': 'hide',
				'click .main-menu a': 'menuClick'
			},

			subViews: {
				'form-pane': wp.ccf.views.FormPane
			},

			initialize: function() {
				if ( ! ccfSettings.single ) {
					this.subViews['existing-form-pane'] = wp.ccf.views.ExistingFormPane;
				}

				wp.ccf.dispatcher.on( 'mainViewChange', this.toggleView, this );
			},

			toggleView: function( view ) {
				this.showView( view, wp.ccf.currentForm );

				var menuView = view;

				if ( 'form-pane' === view && wp.ccf.currentForm ) {
					menuView = 'existing-form-pane';
				}

				var items = this.el.querySelectorAll( '.menu-item' );

				_.each( items, function( item ) {
					var itemView = item.getAttribute( 'data-view' );

					if ( itemView === menuView ) {
						item.className = item.className.replace( 'selected', '' ) + ' selected';
					} else {
						item.className = item.className.replace( 'selected', '' );
					}
				});
			},

			menuClick: function( event ) {
				var view = event.target.getAttribute( 'data-view' );

				if ( 'form-pane' === view ) {
					wp.ccf.currentForm = null;
				}

				wp.ccf.dispatcher.trigger( 'mainViewChange', view );

				event.preventDefault();
			},

			render: function( single ) {
				single = single || false;

				this.overlay();

				this.el.innerHTML = this.template( { single: single } );
				this.initRenderSubViews();

				this.showView( 'form-pane', wp.ccf.currentForm, true );

				return this;
			},

			overlay: function() {
				if ( typeof this.overlayEl === 'undefined' ) {
					this.overlayEl = document.createElement( 'div' );
					this.overlayEl.className = 'ccf-main-modal-overlay';
					document.body.appendChild( this.overlayEl );
				}

				return this.overlayEl;
			},

			remove: function() {
				document.body.removeChild( this.overlay() );

				return this;
			},

			show: function() {
				$( this.overlay() ).show();
				this.$el.show();
			},

			hide: function() {
				$( this.overlay() ).hide();
				this.$el.hide();
			}
		}, wp.ccf.mixins.subViewable )
	);

	wp.ccf.views.SubmissionRow = wp.ccf.views.SubmissionRow || Backbone.View.extend(
		{
			tagName: 'tr',
			template: wp.ccf.utils.template( 'ccf-submission-row-template' ),
			events: {
				'click .view': 'view',
				'click .delete': 'delete'
			},

			initialize: function( options ) {
				this.parent = options.parent;
			},

			'delete': function() {
				var SELF = this,
					currentPage = SELF.parent.collection.state.currentPage,
					page;

				SELF.model.destroy().done( function() {
					page = currentPage;

					if ( page === SELF.parent.collection.state.totalPages && page - 1  === ( SELF.parent.collection.state.totalObjects - 1 ) / ccfSettings.postsPerPage ) {
						page--;
					}

					SELF.parent.showPage( page ).done( function() {
						SELF.parent.renderPagination();
					});
				});
			},

			view: function( event ) {
				var id = event.currentTarget.getAttribute( 'data-submission-id'),
					date = event.currentTarget.getAttribute( 'data-submission-date');

				tb_show( ccfSettings.thickboxTitle + ' - ' + wp.ccf.utils.getPrettyPostDate( date ), '#TB_inline?height=500&amp;width=700&amp;inlineId=ccf-submission-content-' + parseInt( id ), null );
			},

			render: function() {
				this.$el.html( this.template( {
					submission: this.model.toJSON(),
					currentColumns: this.parent.columns,
					columns: wp.ccf.currentForm.getFieldSlugs( true ),
					utils: {
						getPrettyPostDate: wp.ccf.utils.getPrettyPostDate,
						wordChop: wp.ccf.utils.wordChop,
						isFieldDate: wp.ccf.utils.isFieldDate,
						isFieldName: wp.ccf.utils.isFieldName,
						isFieldFile: wp.ccf.utils.isFieldFile,
						isFieldAddress: wp.ccf.utils.isFieldAddress,
						isFieldEmailConfirm: wp.ccf.utils.isFieldEmailConfirm,
						getPrettyFieldDate: wp.ccf.utils.getPrettyFieldDate,
						getPrettyFieldAddress: wp.ccf.utils.getPrettyFieldAddress,
						getPrettyFieldName: wp.ccf.utils.getPrettyFieldName,
						getPrettyFieldEmailConfirm: wp.ccf.utils.getPrettyFieldEmailConfirm
					}
				} ) );

				return this;
			}
		}
	);


	wp.ccf.views.SubmissionsTable = wp.ccf.views.SubmissionsTable || Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-submission-table-template' ),
			events: {
				'click .prev:not(.disabled)': 'previousPage',
				'click .next:not(.disabled)': 'nextPage',
				'click .first:not(.disabled)': 'firstPage',
				'click .last:not(.disabled)': 'lastPage'
			},

			initialize: function() {
				this.collection = new wp.ccf.collections.Submissions( {}, { formId: ccfSettings.postId } );
				wp.ccf.dispatcher.on( 'submissionTableRebuild', this.render, this );
			},

			showPage: function( page ) {
				var SELF = this;

				var fetch = this.collection.fetch( { data: { page: ( page ) } } );

				fetch.error( function( jqXHR, textStatus, errorThrown ) {
					var messageType = 'sync';

					wp.ccf.errorModal.render( messageType ).show();
				});

				fetch.done( function() {
					var rowContainer = SELF.el.querySelectorAll( '.submission-rows' )[0];
					var newRowContainer = document.createElement( 'tbody');

					newRowContainer.className = 'submission-rows';

					if ( SELF.collection.length >= 1 ) {
						SELF.collection.each( function( submission ) {
							var row = new wp.ccf.views.SubmissionRow( { model: submission, parent: SELF } ).render();
							newRowContainer.appendChild( row.el );
						}, SELF );
					} else {
						newRowContainer.appendChild( new wp.ccf.views.EmptySubmissionTableRow( { parent: SELF } ).render( wp.ccf.currentForm.getFieldSlugs( true ).concat( 'date' ) ).el );
					}

					rowContainer.parentNode.replaceChild( newRowContainer, rowContainer );
				});

				return fetch;
			},

			renderPagination: function() {
				var container = this.el.querySelectorAll( '.ccf-pagination' )[0];

				container.innerHTML = '';
				if ( this.collection.state.totalPages > 1 ) {
					container.appendChild( new wp.ccf.views.Pagination( { parent: this } ).render( this.collection.state.totalPages, this.collection.state.currentPage ).el );
				}
			},

			render: function( columns ) {
				var SELF = this;

				if ( ! columns ) {
					SELF.columns = wp.ccf.currentForm.getFieldSlugs( true ).slice( 0, 4 ).concat( 'date' );
				} else {
					SELF.columns = columns;
				}

				if ( SELF.columns.length < 1 ) {
					SELF.el.innerHTML = '';
				} else {
					SELF.el.innerHTML = SELF.template( { columns: SELF.columns } );

					var pagination = SELF.el.querySelectorAll( '.ccf-pagination' )[0];

					SELF.showPage( 1 ).done( function() {
						SELF.renderPagination();
					});
				}

				return SELF;
			}
		}
	);

	wp.ccf.views.ErrorModal = wp.ccf.views.ErrorModal || Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-error-modal-template'),
			tagName: 'div',
			className: 'ccf-error-modal',

			events: {
				'click .close': 'hide'
			},

			hide: function() {
				this.el.className = this.el.className.replace( ' show', '' );
			},

			show: function() {
				this.el.className = this.el.className.replace( ' show', '' ) + ' show';
			},

			toggle: function() {
				if ( this.el.className.match( ' show') ) {
					this.hide();
				} else {
					this.show();
				}
			},

			render: function( messageType ) {
				var context = {
					messageType: ''
				};

				if ( messageType ) {
					context.messageType = messageType;
				}

				this.el.innerHTML = this.template( context );

				return this;
			}
		}
	);

	wp.ccf.views.Pagination = wp.ccf.views.Pagination || Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-pagination-template' ),

			events: {
				'click .prev:not(.disabled)': 'previousPage',
				'click .next:not(.disabled)': 'nextPage',
				'click .first:not(.disabled)': 'firstPage',
				'click .last:not(.disabled)': 'lastPage'
			},

			initialize: function( options ) {
				this.parent = options.parent;
			},

			previousPage: function( event ) {
				var SELF = this;

				SELF.parent.showPage( SELF.parent.collection.state.currentPage - 1 ).done( function() {
					SELF.render();
				});
			},

			nextPage: function( event ) {
				var SELF = this;

				SELF.parent.showPage( SELF.parent.collection.state.currentPage + 1 ).done( function() {
					SELF.render();
				});
			},

			firstPage: function( event ) {
				var SELF = this;

				SELF.parent.showPage( 1 ).done( function() {
					SELF.render();
				});
			},

			lastPage: function( event ) {
				var SELF = this;

				SELF.parent.showPage( SELF.parent.collection.state.totalPages ).done( function() {
					SELF.render();
				});
			},

			render: function() {
				this.el.innerHTML = this.template( { totalPages: this.parent.collection.state.totalPages, currentPage: this.parent.collection.state.currentPage, totalObjects: this.parent.collection.state.totalObjects } );

				return this;
			}
		}
	);

	wp.ccf.views.EmptySubmissionTableRow = wp.ccf.views.EmptySubmissionTableRow || Backbone.View.extend(
		{
			tagName: 'tr',
			template: wp.ccf.utils.template( 'ccf-no-submissions-row-template'),

			initialize: function( options ) {
				this.parent = options.parent;
			},

			render: function( columns ) {
				this.el.innerHTML = this.template( { columns: this.parent.columns } );
				return this;
			}
		}
	);

	wp.ccf.views.SubmissionColumnController = wp.ccf.views.SubmissionColumnController || Backbone.View.extend(
		{
			template: wp.ccf.utils.template( 'ccf-submissions-controller-template'),

			events: {
				'click input[type=checkbox]': 'triggerTableRebuild'
			},

			render: function() {
				this.el.innerHTML = this.template( { columns: wp.ccf.currentForm.getFieldSlugs( true ).concat( 'date' ) } );
			},

			triggerTableRebuild: function( event ) {
				var columns = [];

				var columnElements = document.querySelectorAll( '.submission-column-checkbox' );

				if ( columnElements.length >= 1 ) {
					for ( var i = 0; i < columnElements.length; i++ ) {
						if ( columnElements[i].checked ) {
							columns.push( columnElements[i].value );
						}
					}
				}

				wp.ccf.dispatcher.trigger( 'submissionTableRebuild', columns );
			}
		}
	);
})( jQuery, Backbone, _, ccfSettings );

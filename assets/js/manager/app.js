( function( $, Backbone, _, ccfSettings ) {
	'use strict';

	/**
	 * Wrapper object for CCF form manager
	 *
	 * @returns {{}}
	 */
	wp.ccf = _.defaults( wp.ccf, {
		forms: new wp.ccf.collections.Forms(),

		currentForm: null,

		errorModal: null,

		// Used for single form pages
		_currentFormDeferred: null,

		dispatcher: {},

		show: function( form ) {
			this.switchToForm( form );

			this.instance.show();
			return this.instance;
		},

		initErrorModal: function() {
			this.errorModal = new wp.ccf.views.ErrorModal().render();
			var body = document.getElementsByTagName( 'body' )[0];
			body.appendChild( this.errorModal.el );
		},

		switchToForm: function( form ) {
			var SELF = this;

			if ( +form === parseInt( form ) ) {
				var formId = parseInt( form );

				form = SELF.forms.findWhere( { id: parseInt( formId ) } );

				if ( ! form ) {
					var $deferred;

					if ( typeof SELF.forms.formsFetching[formId] !== 'undefined' ) {
						$deferred = SELF.forms.formsFetching[formId];
						form = null;
					} else {
						form = new wp.ccf.models.Form( { id: formId } );
						$deferred = form.fetch();
						SELF.forms.formsFetching[formId] = $deferred;
					}

					$deferred.done( function() {
						if ( form ) {
							delete SELF.forms.formsFetching[formId];
							SELF.forms.add( form );
						} else {
							form = SELF.forms.findWhere( { id: formId } );
						}

						SELF.currentForm = form;

						wp.ccf.dispatcher.trigger( 'mainViewChange', 'form-pane' );
					});

					return $deferred;
				} else {
					SELF.currentForm = form;

					wp.ccf.dispatcher.trigger( 'mainViewChange', 'form-pane' );
				}
			} else {
				SELF.currentForm = form;

				wp.ccf.dispatcher.trigger( 'mainViewChange', 'form-pane' );
			}

			return true;
		},

		hide: function() {
			this.instance.hide();
			return this.instance;
		},

		toggle: function( form ) {
			this.switchToForm( form );

			if ( this.instance.$el.is( ':visible' ) ) {
				this.instance.hide();
			} else {
				this.instance.show();
			}

			return this.instance;
		},

		createSubmissionsTable: function( container ) {
			var columns = [];

			var columnControllerContainer = document.querySelectorAll( '.ccf-submission-column-controller' );

			var main = new wp.ccf.views.SubmissionsTable( { el: container } );

			main.render();

			if ( columnControllerContainer ) {
				( new wp.ccf.views.SubmissionColumnController( { el: columnControllerContainer } ) ).render();
			}
		},

		_setupMainModal: function( single ) {
			this.instance = new wp.ccf.views.MainModal().render( single );

			document.getElementsByTagName( 'body' )[0].appendChild( this.instance.el );

			Backbone.history.start();

			return this.instance;
		},

		createManager: function() {
			var SELF = this;

			var managerButton = document.querySelectorAll( '.ccf-open-form-manager')[0];

			if ( ! managerButton ) {
				return false;
			}

			_.extend( this.dispatcher, Backbone.Events );

			new wp.ccf.router();

			SELF.initErrorModal();

			var single = false;

			if ( ccfSettings.single ) {
				single = true;

				if ( ccfSettings.postId ) {
					var formId = parseInt( ccfSettings.postId );

					if ( typeof SELF.forms.formsFetching[formId] === 'undefined' ) {

						var form = new wp.ccf.models.Form( { id: formId } );
						var $deferred = form.fetch();
						SELF.forms.formsFetching[formId] = $deferred;
						SELF._currentFormDeferred = $deferred;

						$deferred.done( function() {
							delete SELF.forms.formsFetching[formId];
							SELF.forms.add( form );
							SELF.currentForm = form;
						});
					} else {
						SELF._currentFormDeferred = SELF.forms.formsFetching[formId];

						SELF._currentFormDeferred.done( function() {
							SELF.currentForm = SELF.forms.findWhere( { id: formId } );
						});
					}

					$.when( SELF._currentFormDeferred ).then( function() {
						SELF._setupMainModal( true );
						managerButton.style.display = 'inline-block';

						var metabox = document.getElementById( 'ccf-submissions' );
						if ( metabox ) {
							var container = metabox.querySelectorAll( '.inside' )[0];

							var settings = document.createElement( 'div' );
							settings.className = 'ccf-submission-icon';
							settings.setAttribute( 'data-icon', '' );

							var download = document.createElement( 'a' );
							download.href = '?action=edit&post=' + parseInt( ccfSettings.postId ) + '&download_submissions=1&download_submissions_nonce=' + ccfSettings.downloadSubmissionsNonce;
							download.className = 'ccf-submission-icon';
							download.setAttribute( 'data-icon', '' );

							var screenOptionsLink = document.getElementById( 'show-settings-link' );
							settings.onclick = function() {
								screenOptionsLink.click();
							};

							metabox.insertBefore( settings, metabox.firstChild.nextSibling.nextSibling );
							metabox.insertBefore( download, metabox.firstChild.nextSibling.nextSibling );

							wp.ccf.createSubmissionsTable( container );

							var duplicateButton = document.querySelectorAll( '#major-publishing-actions .duplicate')[0];

							var duplicateClick = function( evnt ) {
								evnt = evnt || window.event;
								evnt.preventDefault();

								SELF.currentForm.clone()
									.set( 'title', { raw: SELF.currentForm.get( 'title' ).raw + ' (duplicate)' } )
									.unset( 'id' )
									.save()
									.done( function( newForm ) {
										document.location = ccfSettings.adminUrl + '/post.php?action=edit&post=' + newForm.id;
									});
							};

							if ( duplicateButton.addEventListener ) {
								duplicateButton.addEventListener( 'click', duplicateClick, false );
							} else {
								duplicateButton.attachEvent( 'onclick', duplicateClick );
							}
						}
					});
				} else {
					SELF._setupMainModal( true );
					managerButton.style.display = 'inline-block';
				}
			} else {
				SELF._setupMainModal();
			}

			var managerClick = function( evnt ) {
				evnt = evnt || window.event;
				var target = ( evnt.currentTarget ) ? evnt.currentTarget : evnt.srcElement;
				var formId = target.getAttribute( 'data-form-id' );
				wp.ccf.toggle( formId );
			};

			if ( managerButton.addEventListener ) {
				managerButton.addEventListener( 'click', managerClick, false );
			} else {
				managerButton.attachEvent( 'onclick', managerClick );
			}
		}
	});

	wp.ccf.createManager();

})( jQuery, Backbone, _, ccfSettings );
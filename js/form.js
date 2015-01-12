( function( $, ccfSettings ) {
	'use strict';

	window.wp = window.wp || {};

	var datepickers = document.querySelectorAll( '.ccf-datepicker' );

	for ( var i = 0; i < datepickers.length; i++ ) {
		$( datepickers[i] ).datepicker();
	}

	wp.ccf = wp.ccf || {};
	wp.ccf.validators = wp.ccf.validators || {};

	var choiceValidator = function( fieldWrapperElement ) {
		this.wrapper = fieldWrapperElement;
		this.errors = {};

		if ( fieldWrapperElement.className.match( ' field-required' ) ) {
			this.inputs = this.wrapper.querySelectorAll( '.field-input' );

			var oldErrorNode = this.wrapper.querySelectorAll( '.error' );
			if ( oldErrorNode.length ) {
				oldErrorNode[0].parentNode.removeChild( oldErrorNode[0] );
			}

			var found = false;

			_.each( this.inputs, function( input ) {
				if ( input.checked || input.selected ) {
					found = true;
				}
			});

			if ( ! found ) {
				this.errors.required = true;

				var newErrorNode = document.createElement( 'div' );
				newErrorNode.className = 'error required-error';
				newErrorNode.innerHTML = ccfSettings.required;

				fieldWrapperElement.appendChild( newErrorNode );
			}
		}
	};

	var validator = function( inputCallback, fieldCallback ) {
		return function( fieldWrapperElement ) {
			this.wrapper = fieldWrapperElement;
			this.inputs = this.wrapper.querySelectorAll( '.field-input' );
			this.errors = {};

			var oldErrorNodes = this.wrapper.querySelectorAll( '.error' );
			for ( var i = oldErrorNodes.length - 1; i >= 0; i-- ) {
				oldErrorNodes[i].parentNode.removeChild( oldErrorNodes[i] );
			}

			_.each( this.inputs, function( input ) {
				var name = input.getAttribute( 'name' );
				this.errors[name] = {};

				if ( input.getAttribute( 'aria-required' ) ) {
					if ( input.value === '' ) {
						this.errors[name].required = input;
					}
				}

				if ( inputCallback ) {
					inputCallback.call( this, input );
				}
			}, this );

			if ( fieldCallback ) {
				fieldCallback.call( this );
			}

			var newErrorNode;

			for ( var field in this.errors ) {
				if ( this.errors.hasOwnProperty( field ) ) {

					for ( var errorKey in this.errors[field] ) {
						newErrorNode = document.createElement( 'div' );
						newErrorNode.className = 'error ' + errorKey + '-error';
						newErrorNode.setAttribute( 'data-field-name', field );
						newErrorNode.innerHTML = ccfSettings[errorKey];

						this.errors[field][errorKey].parentNode.insertBefore( newErrorNode, this.errors[field][errorKey].nextSibling );
					}
				}
			}
		};
	};

	wp.ccf.validators['single-line-text'] = wp.ccf.validators['single-line-text'] || validator();

	wp.ccf.validators['paragraph-text'] = wp.ccf.validators['paragraph-text'] || validator();

	wp.ccf.validators.name = wp.ccf.validators.name || validator();

	wp.ccf.validators.email = wp.ccf.validators.email || validator( false, function() {
		var email = this.inputs[0].value;

		if ( email ) {
			if ( this.inputs.length === 2 ) {
				if ( email !== this.inputs[1].value ) {
					this.errors[this.inputs[0].getAttribute( 'name' )].match = this.wrapper.lastChild;
				}
			}

			var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			if ( ! re.test( email ) ) {
				this.errors[this.inputs[0].getAttribute( 'name' )].email = this.wrapper.lastChild;
			}
		}
	});

	wp.ccf.validators.date = wp.ccf.validators.date || function( fieldWrapperElement ) {
		this.wrapper = fieldWrapperElement;
		this.errors = {};
		this.inputs = this.wrapper.querySelectorAll( '.field-input' );

		var oldErrorNodes = this.wrapper.querySelectorAll( '.error' );
		for ( var i = oldErrorNodes.length - 1; i >= 0; i-- ) {
			oldErrorNodes[i].parentNode.removeChild( oldErrorNodes[i] );
		}

		var newErrorNode;

		_.each( this.inputs, function( input ) {
			var name = input.getAttribute( 'name' );
			this.errors[name] = {};

			if ( input.getAttribute( 'aria-required' ) ) {
				if ( input.value === '' ) {
					this.errors[name].required = true;

					newErrorNode = document.createElement( 'div' );
					newErrorNode.className = 'error required-error';

					if ( this.inputs.length === 1 ) {
						newErrorNode.innerHTML = ccfSettings.required;
						newErrorNode.className += ' right-error';
						input.parentNode.insertBefore( newErrorNode, input.nextSibling );
					} else {
						newErrorNode.innerHTML = ccfSettings[ name.replace( /.*\[(.*?)\]/i, '$1' ) + '_required'];
						fieldWrapperElement.appendChild( newErrorNode );
					}
				}
			}

			if ( input.value !== '' ) {
				var type = name.replace( /^.*\[(.*?)\]$/, '$1');

				if ( type === 'date' ) {
					if ( ! input.value.match( /^([0-9]|\/)+$/ ) ) {
						newErrorNode = document.createElement( 'div' );
						newErrorNode.className = 'error date-error';
						newErrorNode.innerHTML = ccfSettings.date;
						fieldWrapperElement.appendChild( newErrorNode );
					}
				} else if ( type === 'hour' ) {
					if ( ! input.value.match( /^[0-9]+$/ ) ) {
						newErrorNode = document.createElement( 'div' );
						newErrorNode.className = 'error hour-error';
						newErrorNode.innerHTML = ccfSettings.hour;
						fieldWrapperElement.appendChild( newErrorNode );
					}
				} else if ( type === 'minute' ) {
					if ( ! input.value.match( /^[0-9]+$/ ) ) {
						newErrorNode = document.createElement( 'div' );
						newErrorNode.className = 'error minute-error';
						newErrorNode.innerHTML = ccfSettings.minute;
						fieldWrapperElement.appendChild( newErrorNode );
					}
				}
			}
		}, this );
	};

	wp.ccf.validators.address = wp.ccf.validators.address || validator();

	wp.ccf.validators.website = wp.ccf.validators.website || validator( function( input ) {
		if ( input.value ) {
			var re = /^http(s?)\:\/\/(([a-zA-Z0-9\-\._]+(\.[a-zA-Z0-9\-\._]+)+)|localhost)(\/?)([a-zA-Z0-9\-\.\?\,\'\/\\\+&amp;%\$#_]*)?([\d\w\.\/\%\+\-\=\&amp;\?\:\\\&quot;\'\,\|\~\;]*)$/;

			if ( ! re.test( input.value ) ) {
				this.errors[input.getAttribute( 'name' )].website = input;
			}
		}
	});

	wp.ccf.validators.checkboxes = wp.ccf.validators.checkboxes || choiceValidator;

	wp.ccf.validators.dropdown = wp.ccf.validators.dropdown || validator();

	wp.ccf.validators.radio = wp.ccf.validators.radio || choiceValidator;

	var forms = document.querySelectorAll( '.ccf-form-wrapper' );

	if ( forms.length >= 1 ) {
		_.each( forms, function( form ) {

			var formSubmit = function( event ) {
				event.returnFalse = false;

				if ( event.preventDefault ) {
					event.preventDefault();
				}

				var fields = form.querySelectorAll( '.field' );

				var errors = [];

				_.each( fields, function( field ) {
					if ( field.className.match( / skip-field/i ) ) {
						return;
					}

					var type = field.getAttribute( 'data-field-type' );

					var validation = new ( wp.ccf.validators[type] )( field );

					if ( _.size( validation.errors ) ) {
						var validationErrors = 0;
						for ( var key in validation.errors ) {
							if ( validation.errors.hasOwnProperty( key ) ) {
								if (_.size( validation.errors[key] ) ) {
									validationErrors++;
								}
							}
						}

						if ( validationErrors > 0 ) {
							errors.push( validation );
						}
					}
				});

				if ( errors.length ) {
					var docViewTop = $( window ).scrollTop();
					var docViewBottom = docViewTop + $( window ).height();

					var $firstError = $( errors[0].wrapper );
					var $firstErrorOffset = $firstError.offset();

					var top = $firstErrorOffset.top;
					var bottom = top + $firstError.height();

					if ( ! ( docViewTop <= top && docViewBottom >= bottom ) ) {
						$( 'html, body' ).animate( {
							scrollTop: $firstError.offset().top
						}, 500 );
					}
				} else {
					var $form = $( this.querySelectorAll( '.ccf-form' )[0] );

					form.className = form.className.replace( / loading/i, '' ) + ' loading';

					var $loading = $( form.querySelectorAll( '.loading-img' )[0] );
					$loading.animate( { opacity: 100 } );

					$.ajax( {
						url: ccfSettings.ajaxurl,
						type: 'post',
						data: $form.serialize()
					}).done( function( data ) {
						if ( data.success ) {
							if ( 'text' === data.action_type && data.completion_message ) {
								form.innerHTML = data.completion_message;

								$( 'html, body' ).animate( {
									scrollTop: $( form ).offset().top
								}, 500 );
							} else if ( 'redirect' === data.action_type && data.completion_redirect_url ) {
								document.location = data.completion_redirect_url;
							}

						}
					}).complete( function() {
						form.className = form.className.replace( / loading/i, '' );
						$loading.animate( { opacity: 0 } );
					});
				}

				console.log('submit!');

				return false;
			};

			$( form ).on( 'submit', formSubmit );

		});
	}
})( jQuery, ccfSettings );
( function( $, Backbone, _, ccfSettings ) {
	'use strict';

	window.wp = window.wp || {};
	wp.ccf = wp.ccf || {};
	wp.ccf.utils = wp.ccf.utils || {};

	wp.ccf.utils.cleanDateFields = function( object ) {
		delete object.date;
		delete object.date_gmt;
		delete object.modified;
		delete object.modified_gmt;
		delete object.date_tz;
		delete object.modified_tz;
	};

	wp.ccf.utils.template = _.memoize( function( id ) {
		// Use WordPress style Backbone template syntax
		var options = {
			evaluate:    /<#([\s\S]+?)#>/g,
			interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
			escape:      /\{\{([^\}]+?)\}\}(?!\})/g
		};

		return _.template( document.getElementById( id ).innerHTML, null, options );
	});

	wp.ccf.utils.insertFormShortcode = function( form ) {
		var existingForm = wp.ccf.forms.findWhere( { id: form.get( 'id' ) } );
		if ( ! existingForm ) {
			wp.ccf.forms.add( form );
		}

		var editor = tinymce.get( wpActiveEditor );
		var shortcode = '[ccf_form id="' + form.get( 'id' ) + '"]';

		if ( editor && ! editor.isHidden() ) {
			tinymce.activeEditor.execCommand( 'mceInsertContent', false, shortcode );
		} else {
			document.getElementById( wpActiveEditor ).value += shortcode;
		}
	};

	wp.ccf.utils.getPrettyPostDate = function( date ) {
		date = moment.utc( date );

		if ( ccfSettings.gmtOffset ) {
			date = date.utcOffset( parseInt( ccfSettings.gmtOffset ) * 60 );
		}

		return date.format( 'h:mm a M/D/YYYY' );
	};

	wp.ccf.utils.wordChop = function( string, maxLength ) {
		var trimmedString = string.substr( 0, maxLength );
		trimmedString.substr( 0, Math.min( trimmedString.length, trimmedString.lastIndexOf( ' ' ) ) );

		if ( trimmedString.length < string.length ) {
			trimmedString += '...';
		}

		return trimmedString;
	};

	wp.ccf.utils.isFieldDate = function( value ) {
		if ( typeof value.date !== 'undefined' || ( typeof value.hour !== 'undefined' && typeof value.minute !== 'undefined' && typeof value['am-pm'] !== 'undefined' ) ) {
			return true;
		}

		return false;
	};

	wp.ccf.utils.isFieldName = function( value ) {
		if ( typeof value.name !== 'undefined' || typeof value.last !== 'undefined' ) {
			return true;
		}

		return false;
	};

	wp.ccf.utils.isFieldEmailConfirm = function( value ) {
		if ( typeof value.email !== 'undefined' || typeof value.confirm !== 'undefined' ) {
			return true;
		}

		return false;
	};

	wp.ccf.utils.isFieldAddress = function( value ) {
		if ( typeof value.street !== 'undefined' && typeof value.city !== 'undefined' && typeof value.zipcode !== 'undefined' && typeof value.line_two !== 'undefined' ) {
			return true;
		}

		return false;
	};

	wp.ccf.utils.isFieldFile = function( value ) {
		if ( typeof value.id !== 'undefined' && typeof value.url !== 'undefined' && typeof value.file_name !== 'undefined' ) {
			return true;
		}

		return false;
	};

	wp.ccf.utils.getPrettyFieldEmailConfirm = function( value ) {
		if ( value.email ) {
			return value.email;
		}

		if ( value.confirm ) {
			return value.confirm;
		}

		return '-';
	};

	wp.ccf.utils.getPrettyFieldDate = function( value, field ) {
		var dateString = '',
			output = '',
			format = 'HH:mm MM/DD/YY';

		if ( field && field.ccf_field_dateFormat && 'dd/mm/yyyy' === field.ccf_field_dateFormat ) {
			format = 'HH:mm DD/MM/YY';
		}

		if ( value.hour && value.minute && value['am-pm'] ) {
			dateString += value.hour + ':' + value.minute + ' ' + value['am-pm'];
		}

		if ( value.date ) {
			dateString += ' ' + value.date;
		}

		if ( ! dateString ) {
			return '-';
		}

		var date = moment( dateString, format );

		if ( ! date.isValid() ) {
			return ccfSettings.invalidDate;
		}

		if ( value.hour && value.minute && value['am-pm'] ) {
			output += date.format( 'h:mm a' );
		}

		if ( value.date ) {
			if ( output ) {
				output += ' ';
			}

			output += value.date;
		}

		return output;
	};

	wp.ccf.utils.getPrettyFieldName = function( value ) {
		var nameString = value.first;

		if ( nameString.length > 0 ) {
			nameString += ' ';
		}

		if ( value.last ) {
			nameString += value.last;
		}

		if ( ! nameString ) {
			nameString = '-';
		}

		return nameString;
	};

	wp.ccf.utils.getPrettyFieldAddress = function( value ) {
		if ( ! value.street || ! value.city ) {
			return '-';
		}

		var addressString = value.street;

		if ( value.line_two ) {
			addressString += ' ' + value.line_two;
		}

		addressString += ', ' + value.city;

		if ( value.state ) {
			addressString += ', ' + value.state;
		}

		if ( value.zipcode ) {
			addressString += ' ' + value.zipcode;
		}

		if ( value.country ) {
			addressString += ' ' + value.country;
		}

		return addressString;
	};

})( jQuery, Backbone, _, ccfSettings );
( function( $, Backbone, _, ccfSettings ) {
	'use strict';

	window.wp = window.wp || {};
	wp.ccf = wp.ccf || {};
	wp.ccf.utils = wp.ccf.utils || {};

	// ie8 polyfil for Date
	var D = new Date( '2011-06-02T09:34:29+02:00' );
	if ( ! D || +D !== 1307000069000 ) {
		Date.fromISO = function( s ){
			var day, tz,
				rx=/^(\d{4}\-\d\d\-\d\d([tT ][\d:\.]*)?)([zZ]|([+\-])(\d\d):(\d\d))?$/,
				p= rx.exec( s ) || [];
			if ( p[1] ){
				day = p[1].split( /\D/ );
				for ( var i= 0, L = day.length; i < L; i++ ){
					day[i] = parseInt( day[i], 10 ) || 0;
				};
				day[1] -= 1;
				day = new Date( Date.UTC.apply( Date, day ) );
				if( ! day.getDate() ) return NaN;
				if( p[5] ){
					tz = ( parseInt( p[5], 10 ) * 60 );
					if ( p[6] ) tz += parseInt( p[6], 10 );
					if  (p[4] == '+' ) tz *= -1;
					if ( tz ) day.setUTCMinutes( day.getUTCMinutes() + tz );
				}
				return day;
			}
			return NaN;
		};
	} else {
		Date.fromISO = function( s ){
			return new Date( s );
		};
	}

	wp.ccf.utils.cleanDateFields = function( object ) {
		delete object.date;
		delete object.date_gmt;
		delete object.modified;
		delete object.modified_gmt;
		delete object.date_tz;
		delete object.modified_tz;
	};

	wp.ccf.utils.insertFormShortcode = function( form ) {
		var existingForm = wp.ccf.forms.findWhere( { ID: form.get( 'ID' ) } );
		if ( ! existingForm ) {
			wp.ccf.forms.add( form );
		}

		var editor = tinymce.get( wpActiveEditor );
		var shortcode = '[ccf_form id="' + form.get( 'ID' ) + '"]';

		if ( editor && ! editor.isHidden() ) {
			tinymce.activeEditor.execCommand( 'mceInsertContent', false, shortcode );
		} else {
			document.getElementById( wpActiveEditor ).value += shortcode;
		}
	};

	wp.ccf.utils.getPrettyPostDate = function( date ) {
		date = Date.fromISO( date );

		var hours = date.getHours(),
			ampm = 'AM';

		if ( hours >= 12 ) {
			ampm = 'PM';
			hours = hours - 12;
		}

		if ( hours === 0 ) {
			hours = 12;
		}

		return hours + ':' + ( '0' + date.getMinutes() ).slice( -2 ) + ' ' + ampm + ' ' + ( date.getMonth() + 1 ) +
			'/' + date.getDate() + '/' + date.getFullYear();
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

	wp.ccf.utils.isFieldAddress = function( value ) {
		if ( typeof value.street !== 'undefined' && typeof value.city !== 'undefined' && typeof value.zipcode !== 'undefined' && typeof value.line_two !== 'undefined' ) {
			return true;
		}

		return false;
	};

	wp.ccf.utils.getPrettyFieldDate = function( value ) {
		var dateString = '';

		if ( value.date ) {
			dateString += value.date;
		} else {
			var today = new Date();
			dateString += ( today.getMonth() + 1 ) + '/' + today.getDate() + '/' + today.getFullYear();
		}

		if ( value.hour && value.minute && value['am-pm'] ) {
			dateString += ' ' + value.hour + ':' + value.minute + ' ' + value['am-pm'];
		}

		var date = Date.fromISO( dateString );

		var hours = date.getHours(),
			ampm = 'AM';

		if ( hours >= 12 ) {
			ampm = 'PM';
			hours = hours - 12;
		}

		if ( hours === 0 ) {
			hours = 12;
		}

		var returnDate = '';

		if ( value.hour && value.minute && value['am-pm'] ) {
			returnDate = hours + ':' + ( '0' + date.getMinutes() ).slice( -2 ) + ' ' + ampm;
		}

		if ( value.date ) {
			if ( returnDate ) {
				returnDate += ' ';
			}

			returnDate += ( date.getMonth() + 1 ) + '/' + date.getDate() + '/' + date.getFullYear();
		} if ( ! returnDate ) {
			returnDate = '-';
		}

		return returnDate;
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
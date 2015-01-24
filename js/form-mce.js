( function( $ ){

	wp.mce.ccfForm = {
		shortcode_data: {},

		forms: {},

		View: {
			template: wp.ccf.utils.template( 'ccf-form-mce-preview' ),

			type: 'video',

			postID: document.getElementById( 'post_ID' ).value,

			initialize: function( options ) {
				this.shortcode = options.shortcode;

				wp.ccf.dispatcher.on( 'saveFormComplete', this.triggerRefresh, this );

				this.fetch();
			},

			triggerRefresh: function( form ) {
				if ( form === wp.ccf.forms.findWhere( { ID: parseInt( this.shortcode.attrs.named.id ) } ) ) {
					this.renderPreviews();
					this.render( true );
				}
			},
			fetch: function() {
				var SELF = this;

				var id = parseInt( SELF.shortcode.attrs.named.id );

				var form = wp.ccf.forms.findWhere( { ID: id } );

				if ( ! form ) {

					if ( typeof wp.ccf.forms.formsFetching[id] !== 'undefined' ) {
						SELF.formFetch = wp.ccf.forms.formsFetching[id];
					} else {
						form = new wp.ccf.models.Form( { ID: id } );
						SELF.formFetch = form.fetch();
						wp.ccf.forms.formsFetching[id] = SELF.formFetch;
					}

					SELF.formFetch.complete( function() {
						if ( 'resolved' === SELF.formFetch.state() && typeof form !== 'undefined' ) {
							wp.ccf.forms.add( form );
							delete wp.ccf.forms.formsFetching[id];
						}

						SELF.renderPreviews();

						SELF.render( true );
					});
				} else {
					SELF.renderPreviews();

					SELF.render( true );
				}
			},

			renderPreviews: function() {
				var id = parseInt( this.shortcode.attrs.named.id );

				var form = wp.ccf.forms.findWhere( { ID: id } );

				if ( form ) {
					var fields = form.get( 'fields' );

					fields.each( function( field ) {
						var template = document.getElementById( 'ccf-' + field.get( 'type' ) + '-preview-template' );

						if ( template ) {
							var preview = wp.ccf.utils.template( 'ccf-' + field.get( 'type' ) + '-preview-template' )( { field: field.toJSON(), mce: true } );
							field.set( 'preview', preview );
						}
					});
				}
			},

			getHtml: function() {
				var id = parseInt( this.shortcode.attrs.named.id );

				if ( typeof this.formFetch === 'undefined' || this.formFetch.state() === 'resolved' || this.formFetch.state() === 'rejected' ) {
					var form = wp.ccf.forms.findWhere( { ID: id } );

					if ( typeof this.formFetch === 'undefined' ) {
						return this.template( { form: form.toJSON() } );
					} else {
						if ( this.formFetch.state() === 'resolved' ) {
							return this.template( { form: form.toJSON() } );
						} else {
							return wp.ccf.utils.template( 'ccf-form-mce-error-preview' )();
						}
					}
				}

				return false;
			}
		},

		edit: function( node ) {
			var data = window.decodeURIComponent( $( node ).attr('data-wpview-text') );

			var id = data.replace( /^.*id=('|")([0-9]+)('|").*$/i, '$2' );
			var form = wp.ccf.forms.findWhere( { ID: parseInt( id ) } );

			if ( form ) {
				wp.ccf.show( form );
			} else {
				return false;
			}
		}
	};

	wp.mce.views.register( 'ccf_form', wp.mce.ccfForm );
})( jQuery );
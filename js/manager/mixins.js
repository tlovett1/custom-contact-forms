( function( $, Backbone, _, ccfSettings ) {
	'use strict';

	wp.ccf.mixins = wp.ccf.mixins || {};

	wp.ccf.mixins.subViewable = wp.ccf.mixins.subViewable || {
		subViews: {},

		initRenderSubViews: function( showAll, forceInit, args ) {
			if ( ! this.renderedSubViews ) {
				this.renderedSubViews = {};
			}

			for ( var id in this.subViews ) {
				var context = {
					el: this.$el.find( '.ccf-' + id ),
					parent: this
				};

				if ( args ) {
					_.extend( context, args );
				}

				if ( this.renderedSubViews[id] && this.renderedSubViews[id].destroy ) {
					this.renderedSubViews[id].destroy();
				}

				if ( forceInit || ! this.renderedSubViews[id] ) {
					this.renderedSubViews[id] = new this.subViews[id]( context );
				}

				this.renderedSubViews[id].render();

				if ( showAll ) {
					this.renderedSubViews[id].el.style.display = 'block';
				}
			}

			return this;
		},

		showView: function( id, options, noRender ) {
			if ( typeof this.renderedSubViews !== 'undefined' && typeof this.renderedSubViews[id] !== 'undefined' ) {
				var view = this.renderedSubViews[id];
				if ( ! noRender ) {
					view.render( options );
				}

				view.el.style.display = 'block';
				this.currentView = id;

				for ( var viewId in this.subViews ) {
					if ( viewId !== id ) {
						this.renderedSubViews[viewId].el.style.display = 'none';
					}
				}
			}
		}
	};
})( jQuery, Backbone, _, ccfSettings );
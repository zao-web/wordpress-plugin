module.exports = function( app ) {
	return app.views.base.extend({
		template : wp.template( 'gc-item' ),
		tagName : 'tr',
		className : 'gc-item',
		id : function() {
			return this.model.get( 'id' );
		},

		events : {
			'change .check-column input' : 'toggleCheck',
			'click .gc-reveal-items'     : 'toggleExpanded'
		},

		initialize: function() {
			this.listenTo( this.model, 'change:checked', this.render );
		},

		toggleCheck : function() {
			this.model.set( 'checked', ! this.model.get( 'checked' ) );
		},

		render : function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		}
	});
};
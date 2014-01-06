(function($){
	
	/*
	*  Select
	*
	*  static model and events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.select = {
		
		$el : null,
		$select : null,
		
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find input
			this.$select = this.$el.find('select');
			
			
			// get options
			this.o = acf.get_atts( this.$select );
			
			
			// return this for chaining
			return this;
			
		},
		
		init : function(){
			
			// bail early if no ui
			if( ! this.o.ui )
			{
				return;
			}
			
			
			// construct args
			var args = {
				width		: '100%',
				allowClear	: this.o.allow_null,
				placeholder	: this.o.placeholder
			};
			
			
			// remove the blank option as we have a clear all button!
			if( this.o.allow_null )
			{
				this.$select.find('option[value=""]').remove();
			}
			
			
			// add select2
			this.$select.select2( args );
			
		}
	};
	
	
	/*
	*  acf/setup_fields
	*
	*  run init function on all elements for this field
	*
	*  @type	event
	*  @date	20/07/13
	*
	*  @param	{object}	e		event object
	*  @param	{object}	el		DOM object which may contain new ACF elements
	*  @return	N/A
	*/
	
	acf.add_action('ready append', function( $el ){
		
		acf.get_fields({ type : 'select'}, $el).each(function(){
			
			acf.fields.select.set({ $el : $(this) }).init();
			
		});
		
		acf.get_fields({ type : 'user'}, $el).each(function(){
			
			acf.fields.select.set({ $el : $(this) }).init();
			
		});
		
	});
	
	

})(jQuery);
(function($){
	
	acf.fields.color_picker = {
		
		init : function( $input ){
			
			$input.wpColorPicker();
			
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
		
		acf.get_fields({ type : 'color_picker'}, $el).each(function(){
			
			acf.fields.color_picker.init( $(this).find('input[type="text"]') );
			
		});
		
	});
	

})(jQuery);
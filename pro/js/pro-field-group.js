(function($){        
    
    acf_field_group.pro = {
    	
    	init : function(){
	    	
	    	// reference
	    	var _this = this;
	    	
	    	
	    	acf.on('append, sortstop', function( e, $el ){
			    
			    _this.render_field( $el );
				
		    });
	    	
    	},
    	
    	
    	render_field : function( $el ){
	    	
	    	// vars
		    var $parents = $el.parents('.field'),
		    	val = 0;
		    
		    
		    // find parent
			if( $parents.exists() )
			{
				// vars
				var id = $parents.first().attr('data-id'),
					key = $parents.first().attr('data-key');
					
				
				// set val
				val = 'field_' + key;
				
				
				// if field has an ID, use that
				if( id )
				{
					val = id;
				}
				
			}
			
			
			// update parent
			$el.find('> .acf-hidden > .input-parent').val( val );
	    	
    	}
	    
    };
    
    
    /*
	*  Document Ready
	*
	*  initialize
	*
	*  @type	event
	*  @date	15/10/12
	*  @since	3.5.1
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	$(document).on('ready', function(){
		
		// initialize modules
		acf_field_group.pro.init();
			
	});

})(jQuery);
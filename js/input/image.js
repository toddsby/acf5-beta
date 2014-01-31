(function($){
	
	acf.fields.image = {
				
		edit : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-image-uploader'),
				id = $el.find('[data-name="value-id"]').val();
			
			
			acf.media.edit_popup({
				title : acf._e('image', 'edit'),
				button : acf._e('image', 'update'),
				id : id
			});
			
		},
		
		remove : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-image-uploader');
			
			
			// set atts
		 	$el.find('[data-name="value-url"]').attr( 'src', '' );
			$el.find('[data-name="value-id"]').val('').trigger('change');
			
			
			// remove class
			$el.removeClass('has-value');
			
		},
		
		popup : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-image-uploader'),
				library = acf.get_field_data( $el, 'library' ),
				preview_size = acf.get_field_data( $el, 'preview_size' );
			
			
			// popup
			var frame = acf.media.upload_popup({
				title		: acf._e('image', 'select'),
				type		: 'image',
				multiple	: ( $el.closest('.repeater').exists() ) ? 1 : 0,
				uploadedTo	: ( library == 'uploadedTo' ) ? acf.get('post_id') : 0,
				select		: function( attachment, i ) {
					
					// select / add another image field?
			    	if( i > 1 )
					{
						// vars
						var $td			=	$el.closest('td'),
							$tr 		=	$td.closest('.row'),
							$repeater 	=	$tr.closest('.repeater'),
							key 		=	$td.attr('data-field_key'),
							selector	=	'td .acf-image-uploader:first';
							
						
						// key only exists for repeater v1.0.1 +
						if( key )
						{
							selector = 'td[data-field_key="' + key + '"] .acf-image-uploader';
						}
						
						
						// add row?
						if( ! $tr.next('.row').exists() )
						{
							$repeater.find('.add-row-end').trigger('click');
							
						}
						
						
						// update current div
						$el = $tr.next('.row').find( selector );
						
					}
					
					
			    	// vars
			    	var image_id = attachment.id,
			    		image_url = attachment.attributes.url;
			    	
					
			    	// is preview size available?
			    	if( attachment.attributes.sizes && attachment.attributes.sizes[ preview_size ] )
			    	{
				    	image_url = attachment.attributes.sizes[ preview_size ].url;
			    	}
			    	
			    	
			    	// add image to field
			        acf.fields.image.add( $el, image_id, image_url );
					
				}
			});
			
			
		},
		
		add : function( $el, id, url ){
			
			// set atts
		 	$el.find('[data-name="value-url"]').attr( 'src', url );
			$el.find('[data-name="value-id"]').val( id ).trigger('change');
			
			
			// add class
			$el.addClass('has-value');
	
		}
		
	};
	
	
	/*
	*  Events
	*
	*  jQuery events for this field
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('click', '.acf-image-uploader [data-name="remove-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.image.remove( $(this) );
			
	});
	
	$(document).on('click', '.acf-image-uploader [data-name="edit-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.image.edit( $(this) );
			
	});
	
	$(document).on('click', '.acf-image-uploader [data-name="add-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.image.popup( $(this) );
		
	});
	

})(jQuery);
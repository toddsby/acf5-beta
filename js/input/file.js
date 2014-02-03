(function($){
	
	acf.fields.file = {
		
		edit : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-file-uploader'),
				id = $el.find('[data-name="id"]').val();
			
			
			acf.media.edit_popup({
				title		: acf._e('file', 'edit'),
				button		: acf._e('file', 'update'),
				id			: id,
				select		: function( attachment, i ) {
					
			    	// vars
			    	var file = {
				    	id		:	attachment.id,
				    	title	:	attachment.attributes.title,
				    	name	:	attachment.attributes.filename,
				    	url		:	attachment.attributes.url,
				    	icon	:	attachment.attributes.icon,
				    	size	:	attachment.attributes.filesize
			    	};
			    	
			    	
			    	// add file to field
			        acf.fields.file.add( $el, file );
					
				}
			});
			
		},
		
		remove : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-file-uploader');
			
			
			// set atts
			$el.find('[data-name="icon"]').attr( 'src', '' );
			$el.find('[data-name="title"]').text( '' );
		 	$el.find('[data-name="name"]').text( '' ).attr( 'href', '' );
		 	$el.find('[data-name="size"]').text( '' );
			$el.find('[data-name="id"]').val( '' ).trigger('change');
			
			
			// remove class
			$el.removeClass('has-value');
			
		},
		
		popup : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-file-uploader'),
				library = acf.get_data( $el, 'library' );
			
			
			// popup
			var frame = acf.media.upload_popup({
				title		: acf._e('file', 'select'),
				type		: '',
				multiple	: ( $el.closest('.repeater').exists() ) ? 1 : 0,
				uploadedTo	: ( library == 'uploadedTo' ) ? acf.get('post_id') : 0,
				select		: function( attachment, i ) {
					
					// select / add another file field?
			    	if( i > 1 )
					{
						// vars
						var $td			=	$el.closest('td'),
							$tr 		=	$td.closest('.row'),
							$repeater 	=	$tr.closest('.repeater'),
							key 		=	$td.attr('data-field_key'),
							selector	=	'td .acf-file-uploader:first';
							
						
						// key only exists for repeater v1.0.1 +
						if( key )
						{
							selector = 'td[data-field_key="' + key + '"] .acf-file-uploader';
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
			    	var file = {
				    	id		:	attachment.id,
				    	title	:	attachment.attributes.title,
				    	name	:	attachment.attributes.filename,
				    	url		:	attachment.attributes.url,
				    	icon	:	attachment.attributes.icon,
				    	size	:	attachment.attributes.filesize
			    	};
			    	
			    	
			    	// add file to field
			        acf.fields.file.add( $el, file );
					
				}
			});
			
			
		},

		add : function( $el, file ){
			
			// set atts
			$el.find('[data-name="icon"]').attr( 'src', file.icon );
			$el.find('[data-name="title"]').text( file.title );
		 	$el.find('[data-name="name"]').text( file.name ).attr( 'href', file.url );
		 	$el.find('[data-name="size"]').text( file.size );
			$el.find('[data-name="id"]').val( file.id ).trigger('change');
			
					 	
		 	// set div class
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
	
	$(document).on('click', '.acf-file-uploader [data-name="remove-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.file.remove( $(this) );
			
	});
	
	$(document).on('click', '.acf-file-uploader [data-name="edit-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.file.edit( $(this) );
			
	});
	
	$(document).on('click', '.acf-file-uploader [data-name="add-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.file.popup( $(this) );
		
	});
	

})(jQuery);
(function($){
	
	acf.fields.select = {
		
		init : function( $select ){
			
			// vars
			var o = acf.get_data( $select );
			
			
			// bail early if no ui
			if( ! o.ui )
			{
				return;
			}
			
			
			// vars
			var $input = $select.siblings('input');
			
			
			// select2 args
			var args = {
				width		: '100%',
				allowClear	: o.allow_null,
				placeholder	: o.placeholder,
				multiple	: o.multiple,
				data		: []
			};
			
			
			// remove the blank option as we have a clear all button!
			if( o.allow_null )
			{
				args.placeholder = o.placeholder;
				$select.find('option[value=""]').remove();
			}
			
			
			// vars
			var selected = $input.val().split(',').reverse(),
				selected_i = [];
			
			
			// populate args.data
			$select.find('option').each(function( i ){
				
				// append to choices
				args.data.push({
					id		: $(this).attr('value'),
					text	: $(this).text()
				});
				
			});
			
			
			// re-order options
			$.each( selected, function( k, value ){
					
				$.each( args.data, function( i, choice ){
					
					if( value == choice.id )
					{
						selected_i.push( i );
					}
					
				});
								
			});
			
			
			// ajax
			if( o.ajax )
			{
				// vars
				var data = {
					action		: 'acf/fields/select/query',
					field_key	: acf.get_field_data($input, 'key'),
					nonce		: acf.get('nonce'),
					post_id		: acf.get('post_id'),
				};
				
				args.ajax = {
					url			: acf.get('ajaxurl'),
					dataType	: 'json',
					type		: 'get',
					cache		: true,
					data		: function (term, page) {
						
						//add search term
						data.s = term;
												
						return data;
						
					},
					results		: function (data, page) {
						
						return { results: data };
						
					}
				};
				
				args.initSelection = function (element, callback) {
					
					// vars
					var data = [];
					
					
					$.each( selected_i, function( k, v ){
						
						data.push( args.data[ v ] );
						
					});
					
			        
			        // callback
			        callback( data );
			        
			    };
			}
			
			
			// add select2
			$input.select2( args );
			
			
			// sortable?
			if( o.sortable )
			{
				$input.select2("container").find("ul.select2-choices").sortable({
					 containment: 'parent',
					 start: function() {
					 	$input.select2("onSortStart");
					 },
					 update: function() {
					 	$input.select2("onSortEnd");
					 }
				});
			}
			
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
			
			acf.fields.select.init( $(this).find('select') );
			
		});
		
		acf.get_fields({ type : 'user'}, $el).each(function(){
			
			acf.fields.select.init( $(this).find('select') );
			
		});
		
	});
	
	

})(jQuery);
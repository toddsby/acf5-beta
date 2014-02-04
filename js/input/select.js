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
			var $field = acf.get_field_wrap( $select ),
				$input = $select.siblings('input');
			
			
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
			var selection = $input.val().split(','),
				initial_selection = [];
			
			
			// populate args.data
			var optgroups = {};
			
			$select.find('option').each(function( i ){
				
				// var
				var parent = '_root';
				
				
				// optgroup?
				if( $(this).parent().is('optgroup') )
				{
					parent = $(this).parent().attr('label');
				}
				
				
				// append to choices
				if( ! optgroups[ parent ] )
				{
					optgroups[ parent ] = [];
				}
				
				optgroups[ parent ].push({
					id		: $(this).attr('value'),
					text	: $(this).text()
				});
				
			});
			
			
			$.each( optgroups, function( label, children ){
				
				if( label == '_root' )
				{
					$.each( children, function( i, child ){
						
						args.data.push( child );
						
					});
				}
				else
				{
					args.data.push({
						text		: label,
						children	: children
					});
				}
							
			});
			
			
			// re-order options
			$.each( selection.reverse(), function( k, value ){
				
				$.each( args.data, function( i, choice ){
					
					if( value == choice.id )
					{
						initial_selection.push( choice );
					}
					
				});
								
			});
			
			
			// ajax
			if( o.ajax )
			{
				args.ajax = {
					url			: acf.get('ajaxurl'),
					dataType	: 'json',
					type		: 'get',
					cache		: true,
					data		: function (term, page) {
						
						// Alow for dynamic action becuase post_object and user fields use this JS
						var action = 'acf/fields/' + acf.get_data($field, 'type') + '/query';
						
						
						// vars
						var data = {
							action		: action,
							field_key	: acf.get_data($field, 'key'),
							nonce		: acf.get('nonce'),
							post_id		: acf.get('post_id'),
							s			: term
						};
						
						
						// return
						return data;
						
					},
					results		: function (data, page) {
						
						// vars
						return {
							results : data
						};
						
					}
				};
				
				args.initSelection = function (element, callback) {
								        
			        // callback
			        callback( initial_selection );
			        
			    };
			}
			
			
			// filter for 3rd party customization
			args = acf.apply_filters( 'select2_args', args, $field );
			
			
			// add select2
			$input.select2( args );
			
			
			// sortable?
			if( o.sortable )
			{
				$input.select2("container").find("ul.select2-choices").sortable({
					 //containment: 'parent',
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
		
		acf.get_fields({ type : 'post_object'}, $el).each(function(){
			
			acf.fields.select.init( $(this).find('select') );
			
		});
		
	});
	
	

})(jQuery);
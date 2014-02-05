(function($){        
    
    acf_field_group.pro = {
    	
    	init : function(){
	    	
	    	// reference
	    	var _this = this;
	    	
	    	
	    	acf.add_action('append sortstop', function( $el ){
			    
			    if( $el.is('.field') )
			    {
			    	// If this field has been dropped in a new list, make sure the list hides the message
			    	$el.siblings('.no-fields-message').hide();
			    	
			    	
				    _this.render_field( $el );
			    }
				
		    });
		    
		    
		    acf_field_group.fields.$el.find('.field').each(function(){
			   
			     _this.render_field( $(this) );
			    
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
				val = key;
				
				
				// if field has an ID, use that
				if( id )
				{
					val = id;
				}
				
			}
			
			
			// update parent
			this.update_field_input( $el, 'parent', val );
			
			
			// append hidden flexible content layout (fc_layout)
			if( $parents.exists() && $el.closest('tr.acf-field').attr('data-option') == 'flexible-content' )
			{
				// vars
				var key = $el.closest('tr.acf-field').attr('data-key');
				
				
				// update input
				//console.log('update_field_input: %o', $el);
				
				this.update_field_input( $el, 'parent_layout', key );
				
			}
			else
			{
				$el.find('> .acf-hidden > .input-parent_layout').remove();
			}
			
			
			// 3rd party hook
			acf.do_action('render_field', $el);
	    	
    	},
    	
    	update_field_input : function( $el, name, value ){
	    	
	    	// vars
	    	$input = $el.find('> .acf-hidden > .input-' + name);
	    	
	    	
	    	// create hidden input if doesn't exist
			if( !$input.exists() )
			{
				var html = $el.find('> .acf-hidden > .input-ID').outerHTML().replace(/ID/g, name);
				
				
				// update $input
				$input = $(html);
				
				
				// append
				$el.find('> .acf-hidden').append( $input );
			}
			
			
			// update value
			$input.val( value );
	    	
    	},
	    
    };
    
	
	/*
	*  Repeater
	*
	*  This object contains all the functionality required to edit a repeater field
	*
	*  @type	object
	*  @date	20/11/2013
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	acf_field_group.pro.repeater = {
		
		$el : null,
		
		init : function(){
			
			// reference
			var _this = this;
			
			
			// vars
			this.$el = acf_field_group.fields.$el;
			
			
			// events
			acf.add_action('render_field', function( $el ){
				
				// validate
				if( $el.attr('data-type') == 'repeater' )
				{
					_this.render( $el );
				}
				
			});
			
			
			this.$el.on('click', '.acf-field[data-name="layout"][data-option="repeater"] input', function( e ){
				
				// render
				acf_field_group.pro.repeater.render( $(this).closest('.field') );
				
			});
			
			
		},
		
		render : function( $el ){
			
			// vars
			var layout = $el.find('tr[data-name="layout"]:first input:checked').val()
			
			
			// add class
			$el.find('tr[data-name="sub_fields"]:first .acf-field-list:first').removeClass('layout-row layout-table').addClass( 'layout-' + layout );
			
		}
		
	};
	
	
	/*
	*  Flexible Content
	*
	*  description
	*
	*  @type	function
	*  @date	20/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf_field_group.pro.flexible_content = {
		
		$el : null,
		
		init : function(){
			
			// reference
			var _this = this;
			
			
			// vars
			this.$el = acf_field_group.fields.$el;
			
			
			// events
			this.$el.on('click', '.acf-fc-add', function( e ){
				
				e.preventDefault();
				
				_this.add( $(this).closest('.acf-field') );
				
			});
			
			
			this.$el.on('click', '.acf-fc-duplicate', function( e ){
				
				e.preventDefault();
				
				_this.duplicate( $(this).closest('.acf-field') );
				
			});
			
			
			this.$el.on('click', '.acf-fc-delete', function( e ){
				
				e.preventDefault();
				
				_this.remove( $(this).closest('.acf-field') );
				
			});
			
			
			this.$el.on('change blur', '.acf-fc-meta-label input', function( e ){
					
				var $label = $(this),
					$name = $label.closest('.acf-fc-meta').find('.acf-fc-meta-name input');
				
				// only if name is empty
				if( $name.val() == '' )
				{
					// thanks to https://gist.github.com/richardsweeney/5317392 for this code!
					var val = $label.val(),
						replace = {
							'ä': 'a',
							'æ': 'a',
							'å': 'a',
							'ö': 'o',
							'ø': 'o',
							'é': 'e',
							'ë': 'e',
							'ü': 'u',
							'ó': 'o',
							'ő': 'o',
							'ú': 'u',
							'é': 'e',
							'á': 'a',
							'ű': 'u',
							'í': 'i',
							' ' : '_',
							'\'' : ''
						};
					
					$.each( replace, function(k, v){
						var regex = new RegExp( k, 'g' );
						val = val.replace( regex, v );
					});
					
					
					val = val.toLowerCase();
					$name.val( val ).trigger('change');
				}
		
			});
			
			
			this.$el.on('mouseenter', '.acf-fc-reorder', function( e ){
				
				// vars
				var $tbody = $(this).closest('tbody');
				
				
				// validate
				if( $tbody.hasClass('ui-sortable') )
				{
					return;
				}
				
				
				// add sortable
				$tbody.sortable({
					items					: '> tr[data-name="fc_layout"]',
					handle					: '.acf-fc-reorder',
					forceHelperSize			: true,
					forcePlaceholderSize	: true,
					scroll					: true,
					start : function (event, ui) {
						
						acf.do_action('sortstart', ui.item, ui.placeholder);
						
		   			},
		   			
		   			stop : function (event, ui) {
					
						acf.do_action('sortstop', ui.item, ui.placeholder);
						
						
		   			}
				});
				
			});
			
			
			this.$el.on('change', '.acf-fc-meta-display select', function( e ){
				
				// vars
				var $repeater = $(this).closest('.acf-fc-meta').siblings('.repeater');
				
				
				// Set class
				$repeater.removeClass('layout-row').removeClass('layout-table').addClass( 'layout-' + $(this).val() );
				
			});
			
		},
		
		
		add : function( $tr ){
			
			// vars
			var $new_tr = $tr.clone( false );
		
		
			// remove sub fields
			$new_tr.find('.field:not([data-key="acfcloneindex"])').remove();
	
			
			// show add new message
			$new_tr.find('.no-fields-message').show();
			
			
			// reset layout meta values
			$new_tr.find('.acf-fc-meta input').val('');
			
			
			this.wipe_layout( $new_tr );
			
			
			// add new tr
			$tr.after( $new_tr );
			
			
			// display
			$new_tr.find('.acf-fc-meta select').val('row').trigger('change');
			
		},
		
		
		duplicate : function( $tr ){
			
			// save select values
			$tr.find('select').each(function(){
			
				$(this).attr( 'data-val', $(this).val() );
				
			});
			
			
			// vars
			var $new_tr = $tr.clone( false );
			
			
			this.wipe_layout( $new_tr );
			
			
			// update field names
			$new_tr.find('.field').each(function(){
				
				acf_field_group.fields.wipe_field( $(this) );
				
			});
			
			
			// add new tr
			$tr.after( $new_tr );
			
			
			// set select values
			$new_tr.find('select').each(function(){
			
				$(this).val( $(this).attr('data-val') ).trigger('change');
				
			});
			
			
			// focus on new label
			$new_tr.find('.acf-fc-meta-label input').focus();
			
		},
		
		
		remove : function( $tr ){
			
			if( $tr.siblings('tr[data-name="fc_layout"]').length == 0 )
			{
				alert( acf._e('flexible_content','delete') );
				return false;
			}
			
			
			// set layout
			$tr.css({
				height		: $tr.height(),
				width		: $tr.width(),
				position	: 'absolute'
			});
			
			
			// fade $tr
			$tr.animate({ opacity : 0 }, 250, function(){
				
				$(this).remove();
				
			});
			
			
			// create blank space
			$blank = $('<tr style="height:' + $tr.height() + 'px"><td colspan="2"></td></tr>');
			
			
			$tr.after( $blank );
			
			$blank.animate({ height : 0 }, 250, function(){
				
				$(this).remove();
				
			});
						
		},
		
		
		render : function( $el ){
			
			
			
		},
		
		
		wipe_layout : function( $tr ){
			
			// vars
			var old_key = $tr.attr('data-key'),
				new_key = acf.get_uniqid();
			
			
			// give field a new id
			$tr.attr('data-key', new_key);
			
			
			// update attributes
			$tr.find('[name]').each(function(){
			
				var name = $(this).attr('name').replace('[layouts][' + old_key + ']','[layouts][' + new_key + ']');
				
				$(this).attr('name', name);
				$(this).attr('id', name);
				
			});
						
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
		acf_field_group.pro.repeater.init();
		acf_field_group.pro.flexible_content.init();
		
	});

})(jQuery);
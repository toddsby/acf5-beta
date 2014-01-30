(function($){
	
	acf.ajax = {
		
		o : {
			action 			:	'acf/post/get_field_groups',
			post_id			:	0,
			page_template	:	0,
			page_parent		:	0,
			page_type		:	0,
			post_category	:	0,
			post_format		:	0,
			post_taxonomy	:	0,
			lang			:	0,
			nonce			:	0
		},
		
		update : function( k, v ){
			
			this.o[ k ] = v;
			return this;
			
		},
		
		get : function( k ){
			
			return this.o[ k ] || null;
			
		},
		
		init : function(){
			
			// bail early if ajax is disabled
			if( ! acf.get('ajax') )
			{
				return false;	
			}
			
			
			// vars
			this.update('post_id', acf.o.post_id);
			this.update('nonce', acf.o.nonce);
			
			
			// MPML
			if( $('#icl-als-first').length > 0 )
			{
				var href = $('#icl-als-first').children('a').attr('href'),
					regex = new RegExp( "lang=([^&#]*)" ),
					results = regex.exec( href );
				
				// lang
				this.update('lang', results[1]);
				
			}
			
			
			// add triggers
			this.add_events();
		},
		
		fetch : function(){
			
			// reference
			var _this = this;
			
			
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: this.o,
				type		: 'post',
				dataType	: 'json',
				success		: function( json ){
					
					if( json && json.length )
					{
						_this.render( json );
					}
					
				}
			});
			
		},
		
		render : function( json ){
			
			// hide all metaboxes
			$('.acf-postbox').addClass('acf-hidden');
			$('.acf-postbox-toggle').addClass('acf-hidden');
			
			
			// show the new postboxes
			$.each(json, function( k, field_group ){
				
				// vars
				var $el = $('#acf-' + field_group.key),
					$toggle = $('#adv-settings .acf_postbox-toggle[for="acf-' + field_group.key + '-hide"]');
				
				
				// classes
				$el.removeClass('acf-hidden hide-if-js');
				$toggle.removeClass('acf-hidden hide-if-js');
				$toggle.find('input[type="checkbox"]').attr('checked', 'checked');
				
				
				// replace HTML if needed
				$el.find('.acf-replace-with-fields').each(function(){
					
					$(this).replaceWith( field_group.html );
					
					acf.do_action('append', $el);
					
				});
				
				
				// update style if needed
				if( k === 0 )
				{
					$('#acf-style').html( field_group.style );
				}
				
			});
			
		},
		
		add_events : function(){
			
			// reference
			var _this = this;
			
			
			// page template
			$(document).on('change', '#page_template', function(){
				
				var page_template = $(this).val();
				
				_this.update( 'page_template', page_template ).fetch();
			    
			});
			
			
			// page parent
			$(document).on('change', '#parent_id', function(){
				
				var page_type = 'parent',
					page_parent = 0;
				
				
				if( val != "" )
				{
					page_type = 'child';
					page_parent = $(this).val();
				}
				
				_this.update( 'page_type', page_type ).update( 'page_parent', page_parent ).fetch();
			    
			});
			
			
			// post format
			$(document).on('change', '#post-formats-select input[type="radio"]', function(){
				
				var post_format = $(this).val();
				
				if( post_format == '0' )
				{
					post_format = 'standard';
				}
				
				_this.update( 'post_format', post_format ).fetch();
				
			});
			
			
			// post taxonmy
			$(document).on('change', '.categorychecklist input[type="checkbox"]', function(){
				
				// a taxonomy field may trigger this change event, however, the value selected is not
				// actually a term relatinoship, it is meta data
				if( $(this).closest('.categorychecklist').hasClass('no-ajax') )
				{
					return;
				}
				
				
				// set timeout to fix issue with chrome which does not register the change has yet happened
				setTimeout(function(){
					
					// vars
					var values = [];
					
					
					$('.categorychecklist input[type="checkbox"]:checked').each(function(){
						
						if( $(this).is(':hidden') || $(this).is(':disabled') )
						{
							return;
						}
						
						if( $.inArray( $(this).val(), values ) < 0 )
						{
							values.push( $(this).val() );
						}
						
					});
			
					
					_this.update( 'post_taxonomy', values ).fetch();
					
				
				}, 1);
				
				
			});
			
			
			// user role
			/*
			$(document).on('change', 'select[id="role"][name="role"]', function(){
				
				_this.update( 'user_role', $(this).val() ).fetch();
				
			});
			*/
			
		}
		
	};
	
	
	/*
	*  Document Ready
	*
	*  Initialize the object
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).ready(function(){
		
		// initialize
		acf.ajax.init();
		
	});


	
})(jQuery);
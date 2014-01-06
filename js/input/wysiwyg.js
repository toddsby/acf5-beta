(function($){
	
	/*
	*  WYSIWYG
	*
	*  jQuery functionality for this field type
	*
	*  @type	object
	*  @date	20/07/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	acf.fields.wysiwyg = {
		
		$el : null,
		$textarea : null,
		
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find textarea
			this.$textarea = this.$el.find('textarea');
			
			
			// get options
			this.o = acf.get_atts( this.$el );
			
			
			// add ID
			this.o.id = this.$textarea.attr('id');
			
			
			// return this for chaining
			return this;
			
		},
		has_tinymce : function(){
		
			var r = false;
			
			if( typeof(tinyMCE) == "object" )
			{
				r = true;
			}
			
			return r;
			
		},
		init : function(){
			
			// temp store tinyMCE.settings
			var backup = $.extend( {}, tinyMCE.settings );
			
			
			// reset tinyMCE settings
			tinyMCE.settings.theme_advanced_buttons1 = '';
			tinyMCE.settings.theme_advanced_buttons2 = '';
			tinyMCE.settings.theme_advanced_buttons3 = '';
			tinyMCE.settings.theme_advanced_buttons4 = '';
			
			if( acf.isset_object( this.toolbars, this.o.toolbar ) )
			{
				$.each( this.toolbars[ this.o.toolbar ], function( k, v ){
					tinyMCE.settings[ k ] = v;
				})
			}
				
			
			// hook for 3rd party customization
			tinyMCE.settings = acf.apply_filters('wysiwyg_tinymce_settings', tinyMCE.settings, this.o.id);
			
			
			// add functionality back in
			tinyMCE.execCommand("mceAddControl", false, this.o.id);
				
				
			// add events (click, focus, blur) for inserting image into correct editor
			this.add_events();
				
			
			// restore tinyMCE.settings
			tinyMCE.settings = backup;
			
			
			// set active editor to null
			wpActiveEditor = null;
					
		},
		add_events : function(){
		
			// vars
			var id = this.o.id,
				editor = tinyMCE.get( id );
			
			
			// validate
			if( !editor )
			{
				return;
			}
			
			
			// vars
			var	$container = $('#wp-' + id + '-wrap'),
				$body = $( editor.getBody() ),
				$textarea = $( editor.getElement() );
	
			
			// events
			$container.on('click', function(){
				
				acf.validation.remove_error( $container.closest('.acf-field') );
				
			});
			
			$body.on('focus', function(){
			
				wpActiveEditor = id;
		
				acf.validation.remove_error( $container.closest('.acf-field') );
				
			});
			
			$body.on('blur', function(){
			
				wpActiveEditor = null;
				
				// update the hidden textarea
				// - This fixes a but when adding a taxonomy term as the form is not posted and the hidden tetarea is never populated!

				// save to textarea	
				editor.save();
				
				
				// trigger change on textarea
				$textarea.trigger('change');
				
			});
			
			
		},
		destroy : function(){
			
			// vars
			var id = this.o.id,
				editor = tinyMCE.get( id );
			
			
			// Remove tinymcy functionality.
			// Due to the media popup destroying and creating the field within such a short amount of time,
			// a JS error will be thrown when launching the edit window twice in a row.
			try
			{
				tinyMCE.execCommand("mceRemoveControl", false, id);
			} 
			catch(e)
			{
				console.log( e );
			}
			
			
			// set active editor to null
			wpActiveEditor = null;
			
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
	
	acf.add_action('ready', function( $el ){
		
		// validate
		if( ! acf.fields.wysiwyg.has_tinymce() )
		{
			return;
		}
		
		
		// events
		acf.add_action('remove', function( $el ){
		
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
				
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).destroy();
				
			});
			
		}).add_action('sortstart', function( $el ){
		
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
				
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).destroy();
				
			});
			
		}).add_action('sortstop', function( $el ){
		
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
				
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).init();
				
			});
			
		}).add_action('load', function( $el ){
		
			// vars
			var wp_content = $('#wp-content-wrap').exists(),
				wp_acf_settings = $('#wp-acf_settings-wrap').exists()
				mode = 'tmce';
			
			
			// has_editor
			if( wp_acf_settings )
			{
				// html_mode
				if( $('#wp-acf_settings-wrap').hasClass('html-active') )
				{
					mode = 'html';
				}
			}
			
			
			setTimeout(function(){
				
				// trigger click on hidden wysiwyg (to get in HTML mode)
				if( wp_acf_settings && mode == 'html' )
				{
					$('#acf_settings-tmce').trigger('click');
				}
				
			}, 1);
			
			
			setTimeout(function(){
				
				// vars
				var $fields = acf.get_fields({ type : 'wysiwyg'}, $el);
				
				
				// Destory all WYSIWYG fields
				// This hack will fix a problem when the WP popup is created and hidden, then the ACF popup (image/file field) is opened
				$fields.each(function(){
					
					acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).destroy();
					
				});
				
				
				// Add WYSIWYG fields
				setTimeout(function(){
					
					$fields.each(function(){
					
						acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).init();
						
					});
					
				}, 0);
				
			}, 10);
			
			
			setTimeout(function(){
				
				// trigger html mode for people who want to stay in HTML mode
				if( wp_acf_settings && mode == 'html' )
				{
					$('#acf_settings-html').trigger('click');
				}
				
				// Add events to content editor
				if( wp_content )
				{
					acf.fields.wysiwyg.set({ $el : $('#wp-content-wrap') }).add_events();
				}
				
				
			}, 11);
			
		});
		
		
	});
	
	
	/*
	*  Full screen
	*
	*  @description: this hack will hide the 'image upload' button in the wysiwyg full screen mode if the field has disabled image uploads!
	*  @since: 3.6
	*  @created: 26/02/13
	*/
	
	$(document).on('click', '.acfacf.fields.wysiwyg a.mce_fullscreen', function(){
		
		// vars
		var wysiwyg = $(this).closest('.acfacf.fields.wysiwyg'),
			upload = wysiwyg.attr('data-upload');
		
		if( upload == 'no' )
		{
			$('#mce_fullscreen_container td.mceToolbar .mce_add_media').remove();
		}
		
	});
	

})(jQuery);
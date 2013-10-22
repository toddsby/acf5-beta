(function($){
	
	acf.validation = {
		
		status		: true,
		disabled	: false,
		
		init : function(){
			
			// bail early if no $form given
			if( !acf.get('$form') )
			{
				return false;
			}
			
			
			// add events
			this.add_events();
		},
		
		show_error : function( $field ){
			
			$field.addClass('error');
		},
		
		remove_error : function( $field ){
			
			$field.removeClass('error');
		},
		
		run : function(){
			
			// reference
			var _this = this;
			
			
			// reset
			this.status = true;
			
			
			// loop through all fields
			$(document).find('.acf-field.required').each(function(){
				
				// run validation
				_this.validate_field( $(this) );
				
	
			});
			
			
			// return
			return this.status;
		},
		
		validate_field : function( $field ){
			
			// set validation data
			$field.data('validation', true);
			
			
			// not visible
			if( $field.is(':hidden') )
			{
				return;
			}
			
			// if is hidden by conditional logic, ignore
			if( $field.hasClass('acf-conditional_logic-hide') )
			{
				return;
			}
			
			
			// if is hidden by conditional logic on a parent tab, ignore
			if( $field.hasClass('acf-tab_group-hide') )
			{
				if( $field.prevAll('.field_type-tab:first').hasClass('acf-conditional_logic-hide') )
				{
					return;
				}
			}
			
			
			// text / textarea
			if( $field.find('input[type="text"], input[type="email"], input[type="number"], input[type="hidden"], textarea').val() == "" )
			{
				$field.data('validation', false);
			}
			
			
			// wysiwyg
			if( $field.find('.acf_wysiwyg').exists() && typeof(tinyMCE) == "object")
			{
				$field.data('validation', true);
				
				var id = $field.find('.wp-editor-area').attr('id'),
					editor = tinyMCE.get( id );


				if( editor && !editor.getContent() )
				{
					$field.data('validation', false);
				}
			}
			
			
			// select
			if( $field.find('select').exists() )
			{
				$field.data('validation', true);

				if( $field.find('select').val() == "null" || ! $field.find('select').val() )
				{
					$field.data('validation', false);
				}
			}

			
			// radio
			if( $field.find('input[type="radio"]').exists() )
			{
				$field.data('validation', false);

				if( $field.find('input[type="radio"]:checked').exists() )
				{
					$field.data('validation', true);
				}
			}
			
			
			// checkbox
			if( $field.find('input[type="checkbox"]').exists() )
			{
				$field.data('validation', false);

				if( $field.find('input[type="checkbox"]:checked').exists() )
				{
					$field.data('validation', true);
				}
			}

			
			// relationship
			if( $field.find('.acf_relationship').exists() )
			{
				$field.data('validation', false);
				
				if( $field.find('.acf_relationship .relationship_right input').exists() )
				{
					$field.data('validation', true);
				}
			}
			
			
			// repeater
			if( $field.find('.repeater').exists() )
			{
				$field.data('validation', false);
				
				if( $field.find('.repeater tr.row').exists() )
				{
					$field.data('validation', true);
				}			
			}
			
			
			// flexible content
			if( $field.find('.acf_flexible_content').exists() )
			{
				$field.data('validation', false);
				if( $field.find('.acf_flexible_content .values table').exists() )
				{
					$field.data('validation', true);
				}	
			}
			
			
			// gallery
			if( $field.find('.acf-gallery').exists() )
			{
				$field.data('validation', false);
				
				if( $field.find('.acf-gallery .thumbnail').exists())
				{
					$field.data('validation', true);
				}
			}
			
			
			// hook for custom validation
			$(document).trigger('acf/validate_field', [ $field ] );
			
			
			// set validation
			if( ! $field.data('validation') )
			{
				this.status = false;
				this.show_error( $field );
			}
		},
		
		add_events : function(){
			
			var _this = this;
			
			
			// focus
			$(document).on('focus click', '.acf-field.required input, .acf-field.required textarea, .acf-field.required select', function( e ){
				
				_this.remove_error( $(this).closest('.acf-field') );
				
			});
			
			
			// save draft
			$(document).on('click', '#save-post', function(){
				
				_this.disabled = true;
				
			});
			
			
			// submit
			$(document).on('submit', acf.get('$form'), function(){
				
				// bail early if disabled
				if( _this.disabled )
				{
					return true;
				}
				
				
				// run validation
				var result = _this.run();
				
				
				// success
				if( result )
				{
					// remove hidden postboxes (this will stop them from being posted to save)
					$('.acf-postbox.acf-hidden').remove();
					
			
					// submit the form
					return true;
				}
				
				
				// show message
				$(this).children('.acf-validation-error').remove();
				$(this).prepend('<div class="acf-validation-error"><p>' + acf.l10n.validation.error + '</p></div>');
				
				
				// hide ajax stuff on submit button
				$('#publish').removeClass('button-primary-disabled');
				$('#ajax-loading').attr('style','');
				$('#publishing-action .spinner').hide();
				
				return false;
				
			});
			
		}
		
	};
	

})(jQuery);
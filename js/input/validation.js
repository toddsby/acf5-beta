(function($){
        
	acf.validation = {
		
		// vars
		active	: 1,
		ignore	: 0,
		
		
		// classes
		error_class : 'acf-error',
		message_class : 'acf-error-message',
		
		
		// el
		$trigger : null,
		
		
		// functions
		init : function(){
			
			// bail early if disabled
			if( this.active == 0 )
			{
				return;
			}
			
			
			// add events
			this.add_events();
		},
		
		add_error : function( $field, message ){
			
			// add class
			$field.addClass(this.error_class);
			
			
			// add message
			if( message !== undefined )
			{
				$field.children('.acf-input').children('.' + this.message_class).remove();
				$field.children('.acf-input').prepend('<div class="' + this.message_class + '"><p>' + message + '</p></div>');
			}
			
			
			// hook for 3rd party customization
			acf.do_action('add_field_error', $field);
		},
		
		remove_error : function( $field ){
			
			// var
			$message = $field.children('.acf-input').children('.' + this.message_class);
			
			
			// remove class
			$field.removeClass(this.error_class);
			
			
			// remove message
			setTimeout(function(){
				
				acf.remove_el( $message );
				
			}, 250);
			
			
			// hook for 3rd party customization
			acf.do_action('remove_field_error', $field);
		},
		
		add_warning : function( $field, message ){
			
			this.add_error( $field, message );
			
			setTimeout(function(){
				
				acf.validation.remove_error( $field )
				
			}, 1000);
		},
		
		fetch : function( $form ){
			
			// reference
			var _this = this;
			
			
			// vars
			var data = acf.serialize_form( $form );
				
			
			// append AJAX action		
			data.action = 'acf/validate_save_post';
			
				
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: data,
				type		: 'post',
				dataType	: 'json',
				success		: function( json ){
					
					_this.complete( $form, json );
					
				}
			});
			
		},
		
		complete : function( $form, json ){
			
			// filter for 3rd party customization
			json = acf.apply_filters('validation_complete', json, $form);
			
			
			// reference
			var _this = this;
			
			
			// remove previous error message
			$form.children('.' + this.message_class).remove();
			
			
			// validate json
			if( !json || json.result == 1)
			{
			
				// remove hidden postboxes (this will stop them from being posted to save)
				$form.find('.acf-postbox:hidden').remove();
					
					
				// bypass JS and submit form
				this.ignore = 1;
				
				
				// attempt to find $trigger
				/*
if( ! this.$trigger )
				{
					if( $form.find('.submit input[type="submit"]').exists() )
					{
						this.$trigger = $form.find('.submit input[type="submit"]');
					}
				}
*/
				
				
				// submit form again
				if( this.$trigger )
				{
					// remove disabled
					this.$trigger.removeClass('disabled button-disabled button-primary-disabled');
					
					this.$trigger.click();
				}
				else
				{
					$form.submit();
				}
				
				
				// end function
				return;
			}
			
			
			// hide ajax stuff on submit button
			/*
if( $('#submitdiv').exists() )
			{
				$('#save-post').removeClass('button-disabled');
				$('#publish').removeClass('button-primary-disabled');
				$('#ajax-loading').removeAttr('style');
				$('#publishing-action .spinner').hide();
			}
*/
			
			
			// show error message	
			$form.prepend('<div class="' + this.message_class + '"><p>' + json.message + '</p></div>');
			
			
			// show field error messages
			$.each( json.errors, function( k, item ){
			
				var $input = $form.find('[name="' + item.input + '"]').first(),
					$field = acf.get_field_wrap( $input );
				
				
				// add error
				_this.add_error( $field, item.message );
				
			});
			
		},
		
		add_events : function(){
			
			var _this = this;
			
			
			// focus
			$(document).on('focus click change', '.acf-field[data-required="1"] input, .acf-field[data-required="1"] textarea, .acf-field[data-required="1"] select', function( e ){

				_this.remove_error( $(this).closest('.acf-field') );
				
			});
			
			
			// click save
			$(document).on('click', '#save-post', function(){
				
				_this.ignore = 1;
				_this.$trigger = $(this);
				
			});
			
			
			// click publish
			$(document).on('click', '#submit', function(){
				
				_this.$trigger = $(this);
				
			});
			
			
			// click publish
			$(document).on('click', '#publish', function(){
				
				_this.$trigger = $(this);
				
			});
			
			
			// submit
			$(document).on('submit', 'form', function( e ){
				
				// bail early if this form does not contain ACF data
				if( ! $(this).find('#acf-form-data').exists() )
				{
					return true;
				}
				
				
				// ignore this submit?
				if( _this.ignore == 1 )
				{
					_this.ignore = 0;
					return true;
				}
				
				
				// bail early if disabled
				if( _this.active == 0 )
				{
					return true;
				}
				
				
				// prevent default
				e.preventDefault();
				
				
				// run validation
				_this.fetch( $(this) );
								
			});
			
		}
		
	};
	
	
	acf.add_action('ready', function(){
		
		acf.validation.init();
		
	});
	

})(jQuery);
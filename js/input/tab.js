(function($){

	acf.fields.tab = {
		
		add_group : function( $wrap ){
			
			// vars
			var html = '';
			
			
			// generate html
			if( $wrap.is('tbody') )
			{
				html = '<tr class="acf-tab-wrap"><td colspan="2"><ul class="acf-hl acf-tab-group"></ul></td></tr>';
			}
			else
			{
				html = '<div class="acf-tab-wrap"><ul class="acf-hl acf-tab-group"></ul></div>';
			}
			
			
			// append html
			acf.get_fields({ type : 'tab'}, $wrap).first().before( html );
			
		},
		
		add_tab : function( $tab ){
			
			// vars
			var $field	= acf.get_field_wrap( $tab ),
				$wrap	= $field.parent(),
				
				key		= acf.get_data( $field, 'key'),
				label 	= $tab.text();
				
				
			// create tab group if it doesnt exist
			if( ! $wrap.children('.acf-tab-wrap').exists() )
			{
				this.add_group( $wrap );
			}
			
			// add tab
			$wrap.children('.acf-tab-wrap').find('.acf-tab-group').append('<li><a class="acf-tab-button" href="#" data-key="' + key + '">' + label + '</a></li>');
			
		},
		
		toggle : function( $a ){
			
			// reference
			var _this = this;
			
			
			// vars
			var $wrap	= $a.closest('.acf-tab-wrap').parent(),
				key		= $a.attr('data-key');
			
			
			// classes
			$a.parent('li').addClass('active').siblings('li').removeClass('active');
			
			
			// hide / show
			acf.get_fields({ type : 'tab'}, $wrap).each(function(){
				
				// vars
				var $tab = $(this);
					
				
				if( acf.is_field( $(this), {key : key} ) )
				{
					_this.show_tab_fields( $(this) );
				}
				else
				{
					_this.hide_tab_fields( $(this) );
				}
				
			});
			
		},
		
		show_tab_fields : function( $field ) {
			
			//console.log('show tab fields %o', $field);
			$field.nextUntil('.acf-field[data-type="tab"]').each(function(){
				
				$(this).removeClass('hidden_by_tab');
				acf.do_action('show_field', $(this));
				
			});
		},
		
		hide_tab_fields : function( $field ) {
			
			$field.nextUntil('.acf-field[data-type="tab"]').each(function(){
				
				$(this).addClass('hidden_by_tab');
				acf.do_action('hide_field', $(this));
				
			});
		},
		
		refresh : function( $el ){
			
			// reference
			var _this = this;
			
			
			// trigger
			$el.find('.acf-tab-group').each(function(){
				
				$(this).find('.acf-tab-button:first').each(function(){
					
					_this.toggle( $(this) );
					
				});
				
			});
			
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
		
		
		// add tabs
		acf.get_fields({ type : 'tab'}, $el).each(function(){
			
			acf.fields.tab.add_tab( $(this) );
			
		});
		
		
		// activate first tab
		acf.fields.tab.refresh( $el );
		
	});
	
	
		
	
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
	
	$(document).on('click', '.acf-tab-button', function( e ){
		
		e.preventDefault();
		
		acf.fields.tab.toggle( $(this) );
		
		$(this).trigger('blur');
			
	});
	
	
	acf.add_action('hide_field', function( $field ){
		
		// validate
		if( ! acf.is_field($field, {type : 'tab'}) )
		{
			return;
		}
		
		
		// vars
		var $tab = $field.siblings('.acf-tab-wrap').find('a[data-key="' + acf.get_data($field, 'key') + '"]');
		
		
		// if tab is already hidden, then ignore the following functiolnality
		if( $tab.is(':hidden') )
		{
			return;
		}
		
		
		// visibility
		$tab.parent().hide();
		
		
		if( $tab.parent().siblings(':visible').exists() )
		{
			// if the $target to be hidden is a tab button, lets toggle a sibling tab button
			$tab.parent().siblings(':visible').first().children('a').trigger('click');
		}
		else
		{
			// no onther tabs
			acf.fields.tab.hide_tab_fields( $field );
		}
		
	});
	
	
	acf.add_action('show_field', function( $field ){
		
		// validate
		if( ! acf.is_field($field, {type : 'tab'}) )
		{
			return;
		}
		
		
		// vars
		var $tab = $field.siblings('.acf-tab-wrap').find('a[data-key="' + acf.get_data($field, 'key') + '"]');
		
		
		// if tab is already visible, then ignore the following functiolnality
		if( $tab.is(':visible') )
		{
			return;
		}
		
		
		// visibility
		$tab.parent().show();
		
		
		// if this is the active tab
		if( $tab.parent().hasClass('active') )
		{
			$tab.trigger('click');
			return;
		}
		
		
		// if the sibling active tab is actually hidden by conditional logic, take ownership of tabs
		if( $tab.parent().siblings('.active').hasClass('acf-conditional_logic-hide') )
		{
			// show this tab group
			$tab.trigger('click');
			return;
		}
		

	});
	
	

})(jQuery);
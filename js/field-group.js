var acf_field_group = {};

(function($){        
    
    /*
	*  Submit Post
	*
	*  Run validation and return true|false accordingly
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('submit', '#post', function(){
		
		// validate post title
		var title = $('#titlewrap #title');
		
		if( !title.val() )
		{
			alert( acf.l10n.title );
			
			title.focus();
		
			return false;
		}

		
	});
	
	
	/*
	*  Trash button confirmation
	*
	*  This event will trigger a popup confirmation when attempting to trash a field group
	*
	*  @type	event
	*  @date	30/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	$(document).on('click', '#submit-delete', function(){
			
		var response = confirm( acf.l10n.move_to_trash );
		
		if( !response )
		{
			return false;
		}
		
	});
	
	
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
	
	 acf.add_action('ready', function(){
		
		// update postbox classes
		$('#submitdiv, #acf-field-group-fields, #acf-field-group-locations, #acf-field-group-options').addClass('acf-postbox');
		
		$('#acf-field-group-fields').addClass('seamless');
		
		
		// custom Publish metabox
		$('#submitdiv #publish').attr('class', 'acf-button blue large');
		$('#submitdiv a.submitdelete').attr('class', 'delete-field-group').attr('id', 'submit-delete');
		
		
		// initialize modules
		acf_field_group.fields.init();
		acf_field_group.location.init();
		acf_field_group.conditional_logic.init();
		acf_field_group.options.init();
			
	});
	
	
	/*
	*  fields
	*
	*  This object will initialize the fields postbox
	*
	*  @type	object
	*  @date	1/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	acf_field_group.fields = {
		
		$el : null,
		
		init : function(){
			
			// reference
			var _this = this;
			
			
			// vars
			this.$el = $('#acf-field-group-fields');
			
			
			// sortable
			this.init_field_list( this.$el.find('.acf-field-list') );
			
			
			// events
			this.$el.on('click', '.edit-field', function( e ){
				
				e.preventDefault();
				
				_this.edit( $(this).closest('.field') );
				
			});
			
			this.$el.on('click', '.duplicate-field', function( e ){
				
				e.preventDefault();
				
				_this.duplicate( $(this).closest('.field') );
				
			});
			
			
			this.$el.on('click', '.move-field', function( e ){
				
				e.preventDefault();
				
				_this.move( $(this).closest('.field') );
				
			});
			
			
			this.$el.on('click', '.delete-field', function( e ){
				
				e.preventDefault();
				
				_this.remove( $(this).closest('.field') );
				
			});
			
			this.$el.on('click', '.acf-add-field', function( e ){
				
				e.preventDefault();
				
				_this.add( $(this).closest('.acf-field-list-wrap').children('.acf-field-list') );
				
			});
			
			this.$el.on('change', 'tr[data-name="type"] select', function(){
				
				_this.change_type( $(this) );
				
			});
			
			this.$el.on('blur', 'tr[data-name="label"] input', function( e ){
				
				_this.change_label( $(this).closest('.field') );
				
			});
			
			this.$el.on('keyup', 'tr[data-name="label"] input, tr[data-name="name"] input', function( e ){
				
				_this.render_field( $(this).closest('.field') );
				
			});
			
			this.$el.on('change', 'input, textarea, select', function( e ){
				
				_this.save_field( $(this).closest('.field') );
				
			});
			
		},
		
		init_field_list : function( $el ){
			
			// reference
			var _this = this;
			
			
			// sortable
			$el.sortable({
				connectWith: '.acf-field-list',
				update: function(event, ui){
					
					// vars
					var $el = ui.item;
					
					
					// render
					_this.render();
					
					
					// actions
					acf.do_action('sortstop', $el);
					
				},
				handle: '.acf-icon'
			});
			
		},
		
		get_field_meta : function( $el, data ){
		
			return $el.find('> .acf-hidden > .input-' + data).val();
			
		},
		
		update_field_meta : function( $el, data ){
			
			$.each(data, function( k, v ){
				
				$el.find('> .acf-hidden > .input-' + k).val( v );
				
			});
			
		},
		
		save_field : function( $el ){
			
			this.update_field_meta( $el, { changed : 1 } );
			
		},
		
		render_field : function( $el ){
			
			// vars
			var label = $el.find('tr[data-name="label"]:first input').val(),
				name = $el.find('tr[data-name="name"]:first input').val(),
				type = $el.attr('data-type');
			
			
			// update label
			$el.find('> .field-info .li-field_label strong a').text( label );
			
			// update name
			$el.find('> .field-info .li-field_name').text( name );
			
			// update type
			$el.find('> .field-info .li-field_type').text( type );
			
		},
		
		
		edit : function( $el ){
			
			// class / action
			if( $el.hasClass('open') )
			{
				this.close_field( $el );
			}
			else
			{
				this.open_field( $el );
			}
			
		},
		
		open_field : function( $el ){
			
			// already open?
			if( $el.hasClass('open') )
			{
				return false;
			}
			
			
			$el.addClass('open');
			acf.do_action('open', $el);
			
			
			// animate toggle
			$el.children('.field-options').animate({ 'height' : 'toggle' }, 250 );
			
		},
		
		close_field : function( $el ){
			
			// already closed?
			if( !$el.hasClass('open') )
			{
				return false;
			}
			
			
			$el.removeClass('open');
			acf.do_action('close', $el);
			
			
			// animate toggle
			$el.children('.field-options').animate({ 'height' : 'toggle' }, 250 );
		},
		
		change_type : function( $select ){
			
			// vars
			var $tbody		= $select.closest('tbody'),
				$el			= $tbody.closest('.field'),
				
				old_type	= $el.attr('data-type'),
				new_type	= $select.val();
				
			
			
			// update atts
			$el.removeClass( 'field_type-' + old_type ).addClass( 'field_type-' + new_type ).attr( 'data-type', new_type );
			
			
			// tab - override field_name
			if( new_type == 'tab' || new_type == 'message' )
			{
				$tbody.find('tr[data-name="name"] input').val('').trigger('keyup');
			}
			
			
			// hide and disable current options
			$tbody.children('tr[data-option]').hide().find('[name]').attr('disabled', 'true');
				
			
			// render meta
			this.render_field( $select.closest('.field') );
			
			
			// show field options if they already exist
			if( $tbody.children('tr[data-option="' + new_type + '"]').exists() )
			{
				// show and enable options
				$tbody.children('tr[data-option="' + new_type + '"]').show().find('[name]').removeAttr('disabled');
				
				
				// trigger event
				acf.do_action('change', $el);
			}
			else
			{
				// add loading gif
				var $tr = $('<tr class="acf-field"><td class="acf-label"></td><td class="acf-input"><div class="acf-loading"></div></td></tr>');
				
				
				// add $tr
				$tbody.children('.acf-field[data-name="conditional_logic"]').before( $tr );
				
				
				var ajax_data = {
					action		: 'acf/field_group/render_field_options',
					nonce		: acf.o.nonce,
					post_id		: acf.o.post_id,
					prefix		: $select.attr('name').replace('[type]', ''),
					type		: new_type,
				};
				
				$.ajax({
					url: acf.o.ajaxurl,
					data: ajax_data,
					type: 'post',
					dataType: 'html',
					success: function( html ){
						
						if( ! html )
						{
							$tr.remove();
							return;
						}
						
						
						// vars
						var $new_tr = $(html);
						
						
						// replace
						$tr.replaceWith( $new_tr );
						
						
						// trigger event
						acf.do_action('append', $new_tr);
						acf.do_action('change', $el);

						
					}
				});
			}

			
		},
		
		add : function( $field_list ){
			
			// clone last tr
			var $el = $field_list.children('.field[data-key="acfcloneindex"]').clone();
			
			
			// update names
			this.wipe_field( $el );
			
			
			// show
			$el.show();
			
			
			// append to table
			$field_list.children('.field[data-key="acfcloneindex"]').before( $el );
			
			
			// remove no fields message
			$field_list.children('.no-fields-message').hide();
			
			
			// clear name
			$el.find('.field-options .tr-field-type:first select').trigger('change');	
			$el.find('.field-options input[type="text"]').val('');
			
			
			// focus after form has dropped down
			// - this prevents a strange rendering bug in Firefox
			setTimeout(function(){
			
	        	$el.find('.field_form input[type="text"]:first').focus();
	        	
	        }, 251);
	        
			
			// update order numbers
			this.render();
			
			
			// trigger append
			acf.do_action('append', $el);
			
			
			// open up form
			this.edit( $el );
			
		},
		
		remove : function( $el ){
			
			// reference
			var _this	= this,
				id		= $el.attr('data-ID');
			
			
			// add to remove list
			if( id )
			{
				$('#input-delete-fields').val( $('#input-delete-fields').val() + '|' + id );	
			}
			
			
			// vars
			var $field_list	= $el.closest('.acf-field-list');
			
			
			// set layout
			$el.css({
				height		: $el.height(),
				width		: $el.width(),
				position	: 'absolute'
			});
			
			
			// wrap field
			$el.wrap( '<div class="temp-field-wrap" style="height:' + $el.height() + 'px"></div>' );
			
			
			// fade $el
			$el.animate({ opacity : 0 }, 250);
			
			
			// close field
			var end_height = 0,
				$show = false;
			
			if( $field_list.children('.field').length == 1 )
			{
				$show = $field_list.children('.no-fields-message');
				end_height = $show.outerHeight();
			}
			
			$el.parent('.temp-field-wrap').animate({ height : end_height }, 250, function(){
				
				if( $show )
				{
					$show.show();
				}
				
				$(this).remove();
				
				_this.render();
				
			});
						
		},
		
		move : function( $field ){
			
			// open popup
			acf.open_popup({
				title	: acf._e('move_field'),
				loading	: true,
				height : 220
			});
			
			
			// AJAX data
			var ajax_data = {
				'action'	: 'acf/field_group/move_field',
				'nonce'		: acf.get('nonce'),
				'field_id'	: this.get_field_meta($field, 'ID')
			};
			
			
			// get HTML
			$.ajax({
				url: acf.get('ajaxurl'),
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function(html){
				
					acf_field_group.fields.move_confirm( $field, html );
					
				}
			});
			
		},
		
		move_confirm : function( $field, html ){
			
			// update popup
			acf.update_popup({
				content : html
			});
			
			
			// AJAX data
			var ajax_data = {
				'action'			: 'acf/field_group/move_field',
				'nonce'				: acf.get('nonce'),
				'field_id'			: this.get_field_meta($field, 'ID'),
				'field_group_id'	: 0
			};
			
			
			// submit form
			$('#acf-move-field-form').on('submit', function(){

				ajax_data.field_group_id = $(this).find('select').val();
				
				
				// get HTML
				$.ajax({
					url: acf.get('ajaxurl'),
					data: ajax_data,
					type: 'post',
					dataType: 'html',
					success: function(html){
					
						acf.update_popup({
							content : html
						});
						
						acf_field_group.fields.remove( $field );
						
					}
				});
				
				return false;
				
			});
			
		},
		
		duplicate : function( $field ){
			
			// save select values
			$field.find('select').each(function(){
				$(this).attr( 'data-val', $(this).val() );
			});
			
			
			// vars
			var $el = $field.clone(),
				$field_list	= $field.closest('.acf-field-list');
			
			
			// update names
			this.wipe_field( $el );
			
			
			// append to table
			$field_list.children('.field[data-key="acfcloneindex"]').before( $el );
			
			
			// set select values
			$el.find('select').each(function(){
				$(this).val( $(this).attr('data-val') ).removeAttr('data-val');
			});
			
			
			// focus after form has dropped down
			// - this prevents a strange rendering bug in Firefox
			setTimeout(function(){
			
	        	$el.find('.field_form input[type="text"]:first').focus();
	        	
	        }, 251);
	        
			
			// update order numbers
			this.render();
			
			
			// trigger append
			acf.do_action('append', $el);
			
			
			// open up form
			if( $field.hasClass('open') )
			{
				this.close_field( $field );
			}
			else
			{
				this.open_field( $el );
			}
			
			
			// update new_field label / name
			var $label = $el.find('tr[data-name="label"]:first input'),
				$name = $el.find('tr[data-name="name"]:first input');
					
			
			$label.val( $label.val() + ' (' + acf._e('copy') + ')' );
			$name.val( $name.val() + '_' + acf._e('copy') );
			
			
			this.save_field( $el );
			this.render_field( $el );
		},
		
		wipe_field : function( $el ){
			
			// vars
			var old_id = $el.attr('data-id'),
				new_id = acf.get_uniqid('field_');
			
			
			// give field a new id
			$el.attr('data-key', new_id);
			$el.attr('data-id', new_id);
			
			
			// update hidden inputs
			this.update_field_meta($el, {
				ID : '',
				key : new_id
			});
			
			
			// update attributes
			$el.find('[id*="' + old_id + '"]').each(function()
			{	
				$(this).attr('id', $(this).attr('id').replace(old_id, new_id) );
			});
			
			$el.find('[name*="' + old_id + '"]').each(function()
			{	
				$(this).attr('name', $(this).attr('name').replace(old_id, new_id) );
			});
			
		},
		
		render : function(){
			
			// reference
			var _this = this;
			
			
			// update_order_numbers
			this.$el.find('.acf-field-list').each(function(){
				
				var $fields = $(this).children('.field').not('[data-id="acfcloneindex"]');
				
				$fields.each(function( i ){
					
					// vars
					var menu_order = _this.get_field_meta( $(this), 'menu_order' );
					
					
					// bail early if no change in order
					if( parseInt(menu_order) === i )
					{
						return;
					}
					
					
					// update meta
					_this.update_field_meta($(this), {
						menu_order : i
					});
					
					
					// update icon number
					$(this).find('.li-field_order:first .acf-icon').html( i+1 );
					
				});
				
				if( ! $fields.exists() )
				{
					$(this).children('.no-fields-message').show();
				}
				
			});
		},
		
		change_label : function( $el ){
			
			// vars
			var $label = $el.find('tr[data-name="label"]:first input'),
				$name = $el.find('tr[data-name="name"]:first input'),
				type = $el.attr('data-type');
				
				
			// leave blank for tab or message field
			if( type == 'tab' || type == 'message' )
			{
				$name.val('').trigger('change');
				return;
			}
				
			
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
						'\'' : '',
						'\\?' : ''
					};
				
				$.each( replace, function(k, v){
					var regex = new RegExp( k, 'g' );
					val = val.replace( regex, v );
				});
				
				
				val = val.toLowerCase();
				$name.val( val ).trigger('change');
			}
			
			
			// render meta
			this.render_field( $el );
			
		}
		
	};
	
	
	// filter for new_field
	acf.add_filter('is_field_ready_for_js', function( ready, $field ){
		
		// repeater sub field
		if( $field.parents('.field[data-key="acfcloneindex"]').exists() )
		{
			//console.log( $field );
			ready = false;
		}
		
		
		// return
		return ready;
	    
    }, 99);
	
	
	/*
	*  options
	*
	*  This object will initialize the options postbox
	*
	*  @type	object
	*  @date	1/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	acf_field_group.options = {
		
		$el : null,
		
		init : function(){
			
			// vars
			this.$el = $('#acf-field-group-options');
			
			
			// hide on screen toggle
			var $ul = this.$el.find('.tr-hide-on-screen ul.acf-checkbox-list'),
				$li = $('<li><label><input type="checkbox" value="" name="" >' + acf.l10n.hide_show_all + '</label></li>');
			
			
			// start checked?
			if( $ul.find('input:not(:checked)').length == 0 )
			{
				$li.find('input').attr('checked', 'checked');
			}
			
			
			// event
			$li.on('change', 'input', function(){
				
				var checked = $(this).is(':checked');
				
				$ul.find('input').attr('checked', checked);
				
			});
			
			
			// add to ul
			$ul.prepend( $li );
			
		}
		
	};
	
	
	
	/*
	*  location
	*
	*  {description}
	*
	*  @since: 4.0.3
	*  @created: 13/04/13
	*/
	
	acf_field_group.location = {
		$el : null,
		init : function(){
			
			// vars
			var _this = this;
			
			
			// $el
			_this.$el = $('#acf-field-group-locations');
			
			
			// add rule
			_this.$el.on('click', '.location-add-rule', function( e ){
				
				e.preventDefault();
				
				_this.add_rule( $(this).closest('tr') );
								
			});
			
			
			// remove rule
			_this.$el.on('click', '.location-remove-rule', function( e ){
					
				e.preventDefault();
						
				_this.remove_rule( $(this).closest('tr') );
								
			});
			
			
			// add group
			_this.$el.on('click', '.location-add-group', function( e ){
				
				e.preventDefault();
							
				_this.add_group();
								
			});
			
			
			// change rule
			_this.$el.on('change', '.param select', function(){
							
				// vars
				var $tr = $(this).closest('tr'),
					rule_id = $tr.attr('data-id'),
					$group = $tr.closest('.location-group'),
					group_id = $group.attr('data-id');
				
				
				// add loading gif
				var div = $('<div class="acf-loading"></div>');
				$tr.find('td.value').html( div );
				
				
				// load location html
				$.ajax({
					url			: acf.get('ajaxurl'),
					data		: acf.prepare_for_ajax({
						'action'	: 'acf/field_group/render_location_value',
						'rule_id'	: rule_id,
						'group_id'	: group_id,
						'value'		: '',
						'param'		: $(this).val(),
					}),
					type		: 'post',
					dataType	: 'html',
					success		: function(html){
		
						div.replaceWith(html);
		
					}
				});
				
				
			});
			
		},
		add_rule : function( $tr ){
			
			// vars
			var $tr2 = $tr.clone(),
				old_id = $tr2.attr('data-id'),
				new_id = acf.get_uniqid();
			
			
			// update names
			$tr2.find('[name]').each(function(){
				
				$(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
				$(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
				
			});
				
				
			// update data-i
			$tr2.attr( 'data-id', new_id );
			
			
			// add tr
			$tr.after( $tr2 );
					
			
			return false;
			
		},
		remove_rule : function( $tr ){
			
			// vars
			var siblings = $tr.siblings('tr').length;

			
			if( siblings == 0 )
			{
				// remove group
				this.remove_group( $tr.closest('.location-group') );
			}
			else
			{
				// remove tr
				$tr.remove();
			}
			
		},
		add_group : function(){
			
			// vars
			var $group = this.$el.find('.location-group:last'),
				$group2 = $group.clone(),
				old_id = $group2.attr('data-id'),
				new_id = acf.get_uniqid();
			
			
			// update names
			$group2.find('[name]').each(function(){
				
				$(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
				$(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
				
			});
			
			
			// update data-i
			$group2.attr( 'data-id', new_id );
			
			
			// update h4
			$group2.find('h4').text( acf.l10n.or );
			
			
			// remove all tr's except the first one
			$group2.find('tr:not(:first)').remove();
			
			
			// add tr
			$group.after( $group2 );
			
			
			
		},
		remove_group : function( $group ){
			
			$group.remove();
			
		}
	};
	
	

	/*
	*  Screen Options
	*
	*  @description: 
	*  @created: 4/09/12
	*/
	
	/*
$(document).on('change', '#adv-settings input[name="show-field_key"]', function(){
		
		if( $(this).val() == "1" )
		{
			$('#acf_fields table.acf').addClass('show-field_key');
		}
		else
		{
			$('#acf_fields table.acf').removeClass('show-field_key');
		}
		
	});
*/
	
	

	/*
	*  Conditional Logic
	*
	*  This object contains all the functionality for seting up the conditional logic rules for fields
	*
	*  @type	object
	*  @date	21/08/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	acf_field_group.conditional_logic = {
		
		$el : null,
		
		init : function(){
			
			// vars
			this.$el = acf_field_group.fields.$el;
			
			
			// reference
			var _this = this;
			
			
			// events
			acf.add_action('open', function($field){
				
				// render select elements
				_this.render( $field );
			
			});
			
			_this.$el.on('change', 'tr[data-name="label"] input', function(){
				
				// render all open fields
				_this.$el.find('.field.open').each(function(){
					
					_this.render( $(this) );
					
				});
				
			});
			
			_this.$el.on('change', 'tr[data-name="conditional_logic"] input[type="radio"]', function( e ){
				
				e.preventDefault();
				
				_this.change_toggle( $(this) );
				
			});
	
			_this.$el.on('change', '.conditional-logic-field', function( e ){
				
				e.preventDefault();
				
				_this.change_trigger( $(this) );
				
			});
			
			
			// add rule
			_this.$el.on('click', '.location-add-rule', function( e ){
				
				e.preventDefault();
				
				_this.add_rule( $(this).closest('tr') );
								
			});
			
			
			// remove rule
			_this.$el.on('click', '.location-remove-rule', function( e ){
					
				e.preventDefault();
						
				_this.remove_rule( $(this).closest('tr') );
								
			});
			
			
			// add group
			_this.$el.on('click', '.location-add-group', function( e ){
				
				e.preventDefault();
							
				_this.add_group( $(this).closest('.location-groups') );
								
			});
			
		},
		
		update_select : function( $select, choices ){
			
			// vars
			var val = $select.val();
			
			
			// clear choices
			$select.html('');
			
			
			// populate choices
			$.each(choices, function( k, v ){
				
				var $optgroup = $select;
				
				if( v.group )
				{
					$optgroup = $select.find('optgroup[label="' + v.group + '"]');
					
					if( ! $optgroup.exists() )
					{
						$optgroup = $('<optgroup label="' + v.group + '"></optgroup>');
						
						$select.append( $optgroup );
					}
				}
				
				
				// append select
				$optgroup.append( '<option value="' + v.value + '">' + v.label + '</option>' );
			});
			
			
			// reset val
			if( $select.find('option[value="' + val + '"]').exists() )
			{
				$select.val( val );
			}
			
		},
		
		render : function( $field ){
			
			// reference
			var _this = this;
			
			
			// vars
			var choices		= [],
				key			= $field.attr('data-key'),
				$ancestors	= $field.parents('.acf-field-list'),
				$tr			= $field.find('tr[data-name="conditional_logic"]:first');
				
			
			$.each( $ancestors, function( i ){
				
				var group = (i == 0) ? acf.l10n.sibling_fields : acf.l10n.parent_fields;
				
				$(this).children('.field').each(function(){
					
					
					// vars
					var $this_field	= $(this),
						this_key	= $this_field.attr('data-key'),
						this_type	= $this_field.attr('data-type'),
						this_label	= $this_field.find('tr[data-name="label"]:first input').val();
					
					
					// validate
					if( this_key == 'acfcloneindex' )
					{
						return;
					}
					
					if( this_key == key )
					{
						return;
					}
										
					
					// add this field to available triggers
					if( this_type == 'select' || this_type == 'checkbox' || this_type == 'true_false' || this_type == 'radio' )
					{
						choices.push({
							value	: this_key,
							label	: this_label,
							group	: group
						});
					}
					
					
				});
				
			});
				
			
			// empty?
			if( choices.length == 0 )
			{
				choices.push({
					'value' : 'null',
					'label' : acf.l10n.no_fields
				});
			}
			
			
			// create select fields
			$tr.find('.conditional-logic-field').each(function(){
				
				_this.update_select( $(this), choices );
				
				_this.change_trigger( $(this) );
				
			});
			
		},
		
		change_toggle : function( $input ){
			
			// vars
			var val = $input.val(),
				$td = $input.closest('.acf-input');
				
			
			if( val == "1" )
			{
				$td.find('.location-groups').show();
				$td.find('.location-groups').find('[name]').removeAttr('disabled');
			}
			else
			{
				$td.find('.location-groups').hide();
				$td.find('.location-groups').find('[name]').attr('disabled', 'disabled');
			}
			
		},
		
		change_trigger : function( $select ){
			
			// vars
			var val			= $select.val(),
				$trigger	= this.$el.find('.field[data-key="' + val + '"]'),
				type		= $trigger.attr('data-type'),
				$value		= $select.closest('tr').find('.conditional-logic-value'),
				choices		= [];
				
			
			// populate choices
			if( type == "true_false" )
			{
				choices = [
					{ value : 1, label : acf.l10n.checked }
				];
							
			}
			else if( type == "select" || type == "checkbox" || type == "radio" )
			{
				var field_choices = $trigger.find('tr[data-name="choices"] textarea').val().split("\n");
							
				if( field_choices )
				{
					for( var i = 0; i < field_choices.length; i++ )
					{
						var choice = field_choices[i].split(':');
						
						var label = choice[0];
						if( choice[1] )
						{
							label = choice[1];
						}
						
						choices.push({
							'value' : $.trim( choice[0] ),
							'label' : $.trim( label )
						});
						
					}
				}
				
			}
			
			
			// update select
			this.update_select( $value, choices );
			
		},
		
		add_rule : function( $tr ){
			
			// vars
			var $tr2 = $tr.clone(),
				old_id = $tr2.attr('data-id'),
				new_id = acf.get_uniqid();
			
			
			// update names
			$tr2.find('[name]').each(function(){
				
				$(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
				$(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
				
			});
				
				
			// update data-i
			$tr2.attr( 'data-id', new_id );
			
			
			// add tr
			$tr.after( $tr2 );
					
			
			return false;
			
		},
		
		remove_rule : function( $tr ){
			
			// vars
			var siblings = $tr.siblings('tr').length;

			
			if( siblings == 0 )
			{
				// remove group
				this.remove_group( $tr.closest('.location-group') );
			}
			else
			{
				// remove tr
				$tr.remove();
			}
			
		},
		
		add_group : function( $groups ){
			
			// vars
			var $group = $groups.find('.location-group:last'),
				$group2 = $group.clone(),
				old_id = $group2.attr('data-id'),
				new_id = acf.get_uniqid();
			
			
			// update names
			$group2.find('[name]').each(function(){
				
				$(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
				$(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
				
			});
			
			
			// update data-i
			$group2.attr( 'data-id', new_id );
			
			
			// update h4
			$group2.find('h4').text( acf.l10n.or );
			
			
			// remove all tr's except the first one
			$group2.find('tr:not(:first)').remove();
			
			
			// add tr
			$group.after( $group2 );
		
			
		},
		remove_group : function( $group ){
			
			$group.remove();
			
		}
		
	};
	
		
	
	
	/*
	*  Select
	*
	*  The select field requies some conditional logic on it's settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	
	function acf_render_select_field( $el ){
		
		// vars
		var ui			= $el.find('.acf-field[data-name="ui"] input:checked').val(),
			multiple	= $el.find('.acf-field[data-name="multiple"] input:checked').val();
		
		
		
		if( multiple == '1' )
		{
			$el.find('.acf-field[data-name="sortable"]').show();
		}
		else
		{
			$el.find('.acf-field[data-name="sortable"]').hide();
		}
		
		
		if( ui == '1' )
		{
			$el.find('.acf-field[data-name="search"], .acf-field[data-name="ajax"]').show();
		}
		else
		{
			$el.find('.acf-field[data-name="search"], .acf-field[data-name="ajax"], .acf-field[data-name="sortable"]').hide();
		}		
		
	}
	
	acf.add_action('open', function( $el ){
		
		if( $el.attr('data-type') == 'select' )
		{
			acf_render_select_field( $el );
		}
		
	});
	
	$(document).on('change', '.field[data-type="select"] input[type="radio"]', function(){
		
		acf_render_select_field( $(this).closest('.field') );
		
	});
	
	
	/*
	*  Post Object
	*
	*  The select field requies some conditional logic on it's settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function acf_render_post_object_field( $el ){
		
		// vars
		var multiple = $el.find('.acf-field[data-name="multiple"] input:checked').val();
		
		
		if( multiple == '1' )
		{
			$el.find('.acf-field[data-name="sortable"]').show();
		}
		else
		{
			$el.find('.acf-field[data-name="sortable"]').hide();
		}
			
	}
	
	acf.add_action('open change', function( $el ){
		
		if( $el.attr('data-type') == 'post_object' )
		{
			acf_render_post_object_field( $el );
		}
		
	});
	
	$(document).on('change', '.field[data-type="post_object"] input[type="radio"]', function(){
		
		acf_render_post_object_field( $(this).closest('.field') );
		
	});
	
	
	
	/*
	*  Field: Radio
	*
	*  Simple toggle for the radio 'other_choice' option
	*
	*  @type	function
	*  @date	1/07/13
	*/
	
	$(document).on('change', '.radio-option-other_choice input', function(){
		
		// vars
		var $el = $(this),
			$td = $el.closest('td');
		
		
		if( $el.is(':checked') )
		{
			$td.find('.radio-option-save_other_choice').show();
		}
		else
		{
			$td.find('.radio-option-save_other_choice').hide();
			$td.find('.radio-option-save_other_choice input').removeAttr('checked');
		}
		
	});
	
	
	/*
	*  Google Maps
	*
	*  Modify the HTML markup
	*
	*  @type	function
	*  @date	31/10/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('open change', function( $el ){
		
		// validate
		if( $el.attr('data-type') != 'google_map' )
		{
			return;
		}
		
		
		// vars
		$lat = $el.find('tr[data-name="center_lat"]');
		$lng = $el.find('tr[data-name="center_lng"]');
		tmpl = '<ul class="acf-hl"><li style="width:48%;">$lat</li><li style="width:48%; margin-left:4%;">$lng</li></ul>';
		
		
		// validate
		if( !$lng.exists() )
		{
			return;
		}
		
		
		// update tmpl
		tmpl = tmpl.replace( '$lat', $lat.find('.acf-input').html() );
		tmpl = tmpl.replace( '$lng', $lng.find('.acf-input').html() );
		
		
		// update $lat
		$lat.find('.acf-input').html( tmpl );
		
		
		// remove $lng
		$lng.remove();
		
	});
	
	
	/*
	*  oEmbed
	*
	*  Modify the HTML markup
	*
	*  @type	function
	*  @date	31/10/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('open change', function( $el ){
		
		// validate
		if( $el.attr('data-type') != 'oembed' )
		{
			return;
		}
		
		
		// vars
		$width = $el.find('tr[data-name="width"]');
		$height = $el.find('tr[data-name="height"]');
		tmpl = '<ul class="acf-hl"><li style="width:48%;">$width</li><li style="width:48%; margin-left:4%;">$height</li></ul>';
		
		
		// validate
		if( !$width.exists() )
		{
			return;
		}
		
		
		// update tmpl
		tmpl = tmpl.replace( '$width', $width.find('.acf-input').html() );
		tmpl = tmpl.replace( '$height', $height.find('.acf-input').html() );
		
		
		// update $lat
		$width.find('.acf-input').html( tmpl );
		
		
		// remove $lng
		$height.remove();
		
	});
	
	
	/*
	*  Date Picker
	*
	*  The select field requies some conditional logic on it's settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function acf_render_date_picker_field( $el ){
		
		$.each(['display_format', 'return_format'], function(k,v){
			
			// vars
			var $radio = $el.find('.acf-field[data-name="' + v + '"] input[type="radio"]:checked'),
				$other = $el.find('.acf-field[data-name="' + v + '"] input[type="text"]');
			
			
			if( $radio.val() != 'other' )
			{
				$other.val( $radio.val() );
			}
			
		});
		
	}
	
	acf.add_action('open change', function( $el ){
		
		if( $el.attr('data-type') == 'date_picker' )
		{
			acf_render_date_picker_field( $el );
		}
		
	});
	
	$(document).on('change', '.field[data-type="date_picker"] input[type="radio"]', function(){
		
		acf_render_date_picker_field( $(this).closest('.field') );
		
	});
	
	
	

})(jQuery);
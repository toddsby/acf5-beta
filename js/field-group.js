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
	
	$(document).on('ready', function(){
		
		// update postbox classes
		$('#submitdiv, #acf-field-group-fields, #acf-field-group-locations, #acf-field-group-options').addClass('acf-postbox settings');
		
		
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
			this.$el.find('> .inside > .acf-field-list').sortable({
				update: function(event, ui){
				
					_this.render();
					
				},
				handle: '.acf-icon'
			});
			
			
			// events
			this.$el.on('click', '.edit-field', function( e ){
				
				e.preventDefault();
				
				_this.edit( $(this).closest('.field') );
				
			});
			
			
			this.$el.on('click', '.delete-field', function( e ){
				
				e.preventDefault();
				
				_this.remove( $(this).closest('.field') );
				
			});
			
			this.$el.on('click', '#add-field', function( e ){
				
				e.preventDefault();
				
				_this.add();
				
			});
			
			this.$el.on('change', '.acf-field-type', function(){
				
				_this.change_type( $(this) );
				
			});
			
			this.$el.on('change', '.field-label-input', function( e ){
				
				e.preventDefault();
				
				_this.change_label( $(this).closest('.field') );
				
			});
			
			acf.on('open', function(e, $el){
				
				
				
			});
			
		},
		
		edit : function( $el ){
			
			// class / action
			if( $el.hasClass('open') )
			{
				$el.removeClass('open');
				acf.trigger('close', [ $el ]);
			}
			else
			{
				$el.addClass('open');
				acf.trigger('open', [ $el ]);
			}
			
			
			// either way, the field has most likely changed
			$el.find('> .acf-hidden > .input-changed').val( 1 );
			
			
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
				
				
			// show field options if they already exist
			if( $tbody.children('tr[data-option="' + new_type + '"]').exists() )
			{
				// show and enable options
				$tbody.children('tr[data-option="' + new_type + '"]').show().find('[name]').removeAttr('disabled');
				
				
				// trigger event
				acf.trigger('change', [ $el ]);
			}
			else
			{
				// add loading gif
				var $tr = $('<tr class="acf-field"><td class="acf-label"></td><td class="acf-input"><div class="acf-loading"></div></td></tr>');
				
				
				// add $tr
				$tbody.children('.field_save').before( $tr );
				
				
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
						acf.trigger('append', [ $new_tr ]);
						acf.trigger('change', [ $el ]);
						
					}
				});
			}

			
		},
		
		
		add : function(){
			
			// clone last tr
			var $field_list = this.$el.find('> .inside > .acf-field-list'),
				$el			= $field_list.children('.field[data-key="acfcloneindex"]').clone();
			
			
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
			acf.trigger('append', [ $el ]);
			
			
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
			var $field	= $el.closest('.acf-field-list');
			
			
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
			var end_height = 0;
			
			if( $field.children('.field').length == 1 )
			{
				end_height = $field.children('.no-fields-message').height();
			}
			
			$el.parent('.temp-field-wrap').animate({ height : end_height }, 250, function(){
				
				$(this).remove();
				
				_this.render();
				
			});
						
		},
		
		move : function(){
			
			
		},
		
		duplicate : function(){
			
			
		},
		
		wipe_field : function( $el ){
			
			// vars
			var old_id = $el.attr('data-id'),
				new_id = acf.helpers.uniqid('field_');
			
			
			// give field a new id
			$el.attr('data-key', new_id);
			$el.attr('data-id', '');
			
			
			// update hidden inputs
			$el.find('> .acf-hidden > .input-ID').val('');
			$el.find('> .acf-hidden > .input-key').val( new_id );
			
			
			// update class
			$el.attr('class', $el.attr('class').replace(old_id, new_id) );
			
			
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
			
			// update_order_numbers
			this.$el.find('.acf-field-list').each(function(){
			
				$(this).children('.field').each(function( i ){
				
					$(this).find('.li-field_order:first .acf-icon').html( i+1 );
					$(this).find('> .acf-hidden > .input-menu_order').val( i );
					
				});
				
			});
		},
		
		change_label : function( $el ){
			
			// vars
			var $label = $el.find('tr[data-name="label"] input'),
				$name = $el.find('tr[data-name="name"] input'),
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
						'\'' : ''
					};
				
				$.each( replace, function(k, v){
					var regex = new RegExp( k, 'g' );
					val = val.replace( regex, v );
				});
				
				
				val = val.toLowerCase();
				$name.val( val ).trigger('change');
			}
			
		}
		
		
		
		
	};
	
	
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
			
			
			// add rule
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
					group_id = $group.attr('data-id'),
					ajax_data = {
						'action'	: 'acf/field_group/render_location_value',
						'nonce'		: acf.o.nonce,
						'rule_id'	: rule_id,
						'group_id'	: group_id,
						'value'		: '',
						'param'		: $(this).val()
					};
				
				
				// add loading gif
				var div = $('<div class="acf-loading"></div>');
				$tr.find('td.value').html( div );
				
				
				// load location html
				$.ajax({
					url: acf.o.ajaxurl,
					data: ajax_data,
					type: 'post',
					dataType: 'html',
					success: function(html){
		
						div.replaceWith(html);
		
					}
				});
				
				
			});
			
		},
		add_rule : function( $tr ){
			
			// vars
			var $tr2 = $tr.clone(),
				old_id = $tr2.attr('data-id'),
				new_id = acf.helpers.uniqid();
			
			
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
				new_id = acf.helpers.uniqid();
			
			
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
		
		triggers : null,
		
		init : function(){
			
			
			// reference
			var _this = this;
			
			
			// events
			acf.on('open', function(e, $field){
				
				// populate the triggers
				_this.sync();
				
				
				// render select elements
				_this.render( $field );
			
			});
			
			$(document).on('change', 'tr.conditional-logic input[type="radio"]', function( e ){
				
				e.preventDefault();
				
				_this.change_toggle( $(this) );
				
			});
	
			$(document).on('change', 'select.conditional-logic-field', function( e ){
				
				e.preventDefault();
				
				_this.change_trigger( $(this) );
				
			});
			
			$(document).on('click', 'tr.conditional-logic .acf-button-add', function( e ){
		
				e.preventDefault();
				
				_this.add( $(this).closest('tr') );
				
			});
			
			$(document).on('click', 'tr.conditional-logic .acf-button-remove', function( e ){
		
				e.preventDefault();
				
				_this.remove( $(this).closest('tr') );
				
			});
			
		},
		
		sync : function(){
			
			// reference
			var _this = this;
			
			
			// reset
			this.triggers = {
				0 : []
			};
			
			
			// loop through fields
			$('#acf_fields .field').each(function(){
				
				// vars
				var $field	= $(this),
					id		= $field.attr('data-id'),
					type	= $field.attr('data-type'),
					label	= $field.find('tr.field_label input').val(),
					parent	= 0;
				
				
				// validate
				if( id == 'acfcloneindex' )
				{
					return;
				}
				
				
				// parent
				var $parent = $field.parent().closest('.field');
				
				if( $parent.exists() )
				{
					parent = $parent.attr('data-id');
					
					// add placeholder
					if( _this.triggers[ parent ] === undefined )
					{
						_this.triggers[ parent ] = [];
					}
				}
				
				
				// add this field to available triggers
				if( type == 'select' || type == 'checkbox' || type == 'true_false' || type == 'radio' )
				{
					_this.triggers[ parent ].push({
						id		: id,
						type	: type,
						label	: label
					});
				}
				
				
			});
			

		},
		
		render : function( $field ){
			
			// reference
			var _this = this;
			
			
			// vars
			var choices		= [],
				$ancestors	= $field.parent().parents('.field'),
				$tr			= $field.find('> .field_form_mask > .field_form > table > tbody > tr.conditional-logic');
				
			
			// populate choices
			$.each( this.triggers[ 0 ], function(k, v){
				
				choices.push({
					value : v.id,
					label : v.label
				});
				
			});
			
			
			// add ancestors
			if( $ancestors.exists() )
			{
				// add group to current options
				$.each( choices, function(k, v){
						
					choices[ k ].group = acf.l10n.fields;
					
				});
				
				
				$ancestors.each(function( k ){
					
					var id = $(this).attr('data-id'),
						group = (k == 0) ? acf.l10n.sibling_fields : acf.l10n.parent_fields;
					
					// populate choices
					$.each( _this.triggers[ id ], function(k, v){
						
						choices.push({
							value : v.id,
							label : v.label,
							group : group
						});
						
					});
					
				});
			}
				
			
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
			
				var val = $(this).val(),
					name = $(this).attr('name');
				
				
				// create select
				var $select = acf.helpers.render_field({
					'type'		: 'select',
					'classname'	: 'conditional-logic-field',
					'name'		: name,
					'value'		: val,
					'choices'	: choices
				});
				
				
				// update select
				$(this).replaceWith( $select );
				
				
				// trigger change
				$select.trigger('change');
					
			});
			
		},
		
		change_toggle : function( $input ){
			
			// vars
			var val = $input.val(),
				$tr = $input.closest('tr.conditional-logic');
				
			
			if( val == "1" )
			{
				$tr.find('.contional-logic-rules-wrapper').show();
			}
			else
			{
				$tr.find('.contional-logic-rules-wrapper').hide();
			}
			
		},
		
		change_trigger : function( $select ){
			
			// vars
			var val			= $select.val(),
				$trigger	= $('.field_key-' + val),
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
				var field_choices = $trigger.find('.field_option-choices').val().split("\n");
							
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
			
			
			// create select
			var $select = acf.helpers.render_field({
				'type'		: 'select',
				'classname'	: 'conditional-logic-value',
				'name'		: $value.attr('name'),
				'value'		: $value.val(),
				'choices'	: choices
			});
			
			$value.replaceWith( $select );
			
			$select.trigger('change');
			
		},
		
		add : function( $old_tr ){
			
			// vars
			var $new_tr = $old_tr.clone(),
				old_i = parseFloat( $old_tr.attr('data-i') ),
				new_i = old_i + 1;
			
			
			// update names
			$new_tr.find('[name]').each(function(){
				
				$(this).attr('name', $(this).attr('name').replace('[' + old_i + ']', '[' + new_i + ']') );
				$(this).attr('id', $(this).attr('id').replace('[' + old_i + ']', '[' + new_i + ']') );
				
			});
				
				
			// update data-i
			$new_tr.attr('data-i', new_i);
			
			
			// add tr
			$old_tr.after( $new_tr );
			
			
			// remove disabled
			$old_tr.closest('table').removeClass('remove-disabled');
			
		},
		
		remove : function( $tr ){
			
			var $table = $tr.closest('table');
		
			// validate
			if( $table.hasClass('remove-disabled') )
			{
				return false;
			}
			
			
			// remove tr
			$tr.remove();
			
			
			// add clas to table
			if( $table.find('tr').length <= 1 )
			{
				$table.addClass('remove-disabled');
			}
			
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
	
	acf.on('open', function( e, $el ){
		
		if( $el.attr('data-type') == 'select' )
		{
			acf_render_select_field( $el );
		}
		
	});
	
	$(document).on('change', '.field[data-type="select"] input[type="radio"]', function(){
		
		acf_render_select_field( $(this).closest('.field') );
		
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

	

})(jQuery);
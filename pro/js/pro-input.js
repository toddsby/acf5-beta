(function($){
	
	acf.fields.repeater = {
		
		// vars	
		o		: {},
		el		: '.acf-repeater',
		
		
		// el
		$field	: null,
		$el		: null,	
		$clone : null,
		
		
		// functions
		set : function( $field ){
			
			// sel $el
			this.$field = $field;
			this.$el = $field.find( this.el ).first();
			

			// find elements
			this.$clone = this.$el.find('> table > tbody > tr[data-id="acfcloneindex"]');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			

			// add layout_count
			this.o.row_count = this.$el.find('> table > tbody > tr').length - 1;	
			
			
			// return this for chaining
			return this;
			
		},
		
		init : function(){
			
			// vars
			var $field = this.$field;
			
			
			// sortable
			if( this.o.max != 1 )
			{
				this.$el.find('> table > tbody').unbind('sortable').sortable({
				
					items					: '> tr',
					handle					: '> td.order',
					forceHelperSize			: true,
					forcePlaceholderSize	: true,
					scroll					: true,
					
					start : function (event, ui) {
						
						acf.do_action('sortstart', ui.item, ui.placeholder);
						
		   			},
		   			
		   			stop : function (event, ui) {
					
						acf.do_action('sortstop', ui.item, ui.placeholder);
						
						
						// render
						acf.fields.repeater.set( $field ).render();
						
		   			}
				});
			}
			
			
			// render
			this.render();
					
		},
		
		render : function(){
			
			// update order numbers
			this.$el.find('> table > tbody > tr').each(function(i){
			
				$(this).children('td.order').html( i+1 );
				
			});
			
			console.log( this.o.row_count );
			// empty?
			if( this.o.row_count == 0 )
			{
				this.$el.addClass('empty');
			}
			else
			{
				this.$el.removeClass('empty');
			}
			
			
			// row limit reached
			if( this.o.row_count >= this.o.max_rows )
			{
				this.$el.addClass('disabled');
				this.$el.find('> .acf-hl .acf-button').addClass('disabled');
			}
			else
			{
				this.$el.removeClass('disabled');
				this.$el.find('> .acf-hl .acf-button').removeClass('disabled');
			}
			
		},
		
		add : function( $before ){
			
			// vars
			var $field = this.$field;
			
			
			// validate
			if( this.o.row_count >= this.o.max_rows )
			{
				alert( acf._e('repeater','max').replace('{max}', this.o.max_rows) );
				return false;
			}
			
		
			// create and add the new field
			var new_id = acf.get_uniqid(),
				html = this.$clone.html();
				
				
			// replace [acfcloneindex]
			var html = html.replace(/(="[\w-\[\]]*?)(\[acfcloneindex\])/g, '$1' + '[' + new_id + ']'),
				$html = $('<tr class="acf-row" data-id="' + new_id + '"></tr>').append( html );
				
						
			// add row
			if( !$before.exists() )
			{
				$before = this.$clone;
			}
			
			$before.before( $html );
			
			
			// trigger mouseenter on parent repeater to work out css margin on add-row button
			this.$field.parents('.acf-row').trigger('mouseenter');
			
			
			// update order
			// remember to reset field so that row_number is updated
			this.set( $field ).render();
			
			
			// setup fields
			acf.do_action('append', $html);
	
			
			// validation
			acf.validation.remove_error( this.$field );
			
		},
		
		remove : function( $tr ){
			
			// vars
			var $field = this.$field;
			
			
			// validate
			if( this.o.row_count <= this.o.max_rows )
			{
				alert( acf._e('repeater','min').replace('{min}', this.o.min_rows) );
				return false;
			}
			
			
			// animate out tr
			acf.remove_tr( $tr, function(){
				
				// trigger mouseenter on parent repeater to work out css margin on add-row button
				$field.closest('.acf-row').trigger('mouseenter');
				
				
				// render
				acf.fields.repeater.set( $field ).render();
				
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
		
		acf.get_fields({ type : 'repeater'}, $el).each(function(){
			
			acf.fields.repeater.set( $(this) ).init();
			
		});
		
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
	
	$(document).on('click', '.acf-repeater .acf-repeater-add-row', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			$before	= $a.closest('.acf-row');
			
			
		// remove
		acf.fields.repeater.set( $field ).add( $before );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-repeater .acf-repeater-remove-row', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			$tr		= $a.closest('.acf-row');
			
			
		// remove
		acf.fields.repeater.set( $field ).remove( $tr );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('mouseenter', '.acf-repeater .acf-row', function( e ){
		
		// vars
		var $el = $(this).find('> td.remove .acf-repeater-add-row'),
			margin = ( $el.parent().height() / 2 ) + 9; // 9 = padding + border
		
		
		// css
		$el.css('margin-top', '-' + margin + 'px' );
		
	});
	
	
	
	/*
	*  Flexible Content
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	18/08/13
	*
	*/
	
	acf.fields.flexible_content = {
		
		// vars	
		o		: {},
		el		: '.acf-flexible-content',
		
		
		// el
		$field	: null,
		$el		: null,	
		$values : null,
		$clones : null,
		
		
		// functions
		set : function( $field ){
			
			// sel $el
			this.$field = $field;
			this.$el = $field.find( this.el ).first();
			
			
			// find elements
			this.$values = this.$el.children('.values');
			this.$clones = this.$el.children('.clones');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// add layout_count
			this.o.layout_count = this.$values.children('.layout').length;	
			
			
			// return this for chaining
			return this;
			
		},
		
		init : function(){
			
			// refernce
			var _this = this,
				$field = this.$field;
			
			
			// sortable
			if( this.o.max != 1 )
			{
				this.$values.unbind('sortable').sortable({
					
					items					: '> .layout',
					handle					: '> .acf-fc-layout-handle',
					forceHelperSize			: true,
					forcePlaceholderSize	: true,
					scroll					: true,
					
					start : function (event, ui) {
					
						acf.do_action('sortstart', ui.item, ui.placeholder);
		        		
		   			},
		   			stop : function (event, ui) {
						
						acf.do_action('sortstop', ui.item, ui.placeholder);
						
						
						// render
						_this.set( $field ).render();
		   			}
				});
			}
						
			
			// render
			this.render();
			
			
			// make field required if has min
			var add_required = false;
			
			if( this.o.min > 0 )
			{
				add_required = true;
			}
			else
			{
				// vars
				var $popup = $( this.$el.children('.tmpl-popup').html() );
				
				
				$popup.find('a').each(function(){
					
					// vars
					var min = parseInt( $(this).attr('data-min') );
					
					
					if( min > 0 )
					{
						add_required = true;
						
						// end loop
						return false;	
					}
					
				});

				
			}
			
			if( add_required )
			{
				this.$field.addClass('required');
			}		
			
		},
		
		render : function(){
			
			// update order numbers
			this.$values.children('.layout').each(function( i ){
			
				$(this).find('> .acf-fc-layout-handle .fc-layout-order').html( i+1 );
				
			});
			
			
			// empty?
			if( this.o.layout_count == 0 )
			{
				this.$el.addClass('empty');
			}
			else
			{
				this.$el.removeClass('empty');
			}
			
			
			// row limit reached
			if( this.o.layout_count >= this.o.max )
			{
				this.$el.addClass('disabled');
				this.$el.find('> .acf-hl .acf-button').addClass('disabled');
			}
			else
			{
				this.$el.removeClass('disabled');
				this.$el.find('> .acf-hl .acf-button').removeClass('disabled');
			}
			
		},
		
		validate_add : function( layout ){
			
			var r = true;
			
			// vadiate max
			this.o.max = parseInt(this.o.max);
			if( this.o.max > 0 && this.o.layout_count >= this.o.max )
			{
				var identifier	= ( this.o.max == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'max');
				
				// translate
				s = s.replace('{max}', this.o.max);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				
				r = false;
				
				alert( s );
			}
			
			
			// vadiate max layout
			var $popup			= $( this.$el.children('.tmpl-popup').html() ),
				$a				= $popup.find('[data-layout="' + layout + '"]'),
				layout_max		= $a.attr('data-max'),
				layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
			
			
			layout_max = parseInt(layout_max);
			if( layout_max > 0 && layout_count >= layout_max )
			{
				var identifier	= ( layout_max == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'max_layout');
				
				// translate
				s = s.replace('{max}', layout_count);
				s = s.replace('{label}', '"' + $a.text() + '"');
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				
				r = false;
				
				alert( s );
			}
			
			
			// return
			return r;
			
		},
		
		validate_remove : function( layout ){
			
			// vadiate min
			this.o.min = parseInt(this.o.min);
			if( this.o.min > 0 && this.o.layout_count <= this.o.min )
			{
				var identifier	= ( this.o.min == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'min') + ', ' + acf._e('flexible_content', 'remove');
				
				// translate
				s = s.replace('{min}', this.o.min);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
				
				return confirm( s );

			}
			
			
			// vadiate max layout
			
			var $popup			= $( this.$el.children('.tmpl-popup').html() ),
				$a				= $popup.find('[data-layout="' + layout + '"]'),
				layout_min		= $a.attr('data-min'),
				layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
			
			
			layout_min = parseInt(layout_min);
			if( layout_min > 0 && layout_count <= layout_min )
			{
				var identifier	= ( layout_min == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'min_layout') + ', ' + acf._e('flexible_content', 'remove');
				
				// translate
				s = s.replace('{min}', layout_count);
				s = s.replace('{label}', '"' + $a.text() + '"');
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
				
				return confirm( s );
			}
			
			
			// return
			return true;
			
		},
		
		add : function( layout, $before ){
			
			// bail early if validation fails
			if( !this.validate_add( layout ) )
			{
				return;
			}
			
			
			// create and add the new field
			var new_id = acf.get_uniqid(),
				html = this.$clones.children('.layout[data-layout="' + layout + '"]').html();
				
				
			// replace [acfcloneindex]
			var html = html.replace(/(="[\w-\[\]]*?)(\[acfcloneindex\])/g, '$1' + '[' + new_id + ']'),
				$html = $('<div class="layout" data-layout="' + layout + '"></div>').append( html );
			
							
			// hide no values message
			this.$el.children('.no-value-message').hide();
			
			
			// add row
			if( $before )
			{
				$before.before( $html );
			}
			else
			{
				this.$values.append( $html ); 
			}
			
			
			// setup fields
			acf.do_action('append', $html);
			
			
			// update order
			this.render();
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
		},
		
		remove : function( $layout ){
			
			// bail early if validation fails
			if( !this.validate_remove( $layout.attr('data-layout') ) )
			{
				return;
			}
			
			
			// close field
			var end_height = 0,
				$message = this.$el.children('.no-value-message');
			
			if( $layout.siblings('.layout').length == 0 )
			{
				end_height = $message.outerHeight();
			}
			
			
			// remove
			acf.remove_el( $layout, function(){
				
				if( end_height > 0 )
				{
					$message.show();
				}
				
			}, end_height);
			
		},
		
		toggle : function( $layout ){
			
			if( $layout.attr('data-toggle') == 'closed' )
			{
				$layout.attr('data-toggle', 'open');
				$layout.children('.acf-input-table').show();
			}
			else
			{
				$layout.attr('data-toggle', 'closed');
				$layout.children('.acf-input-table').hide();
			}	
			
		},
		
		open_popup : function( $a, in_layout ){
			
			// reference
			var _this = this;
			
			
			// defaults
			in_layout = in_layout || false;
			
			
			// vars
			var $popup = $( this.$el.children('.tmpl-popup').html() );
			
			
			$popup.find('a').each(function(){
				
				// vars
				var min		= parseInt( $(this).attr('data-min') ),
					max		= parseInt( $(this).attr('data-max') ),
					name	= $(this).attr('data-layout'),
					label	= $(this).text(),
					count	= _this.$values.children('.layout[data-layout="' + name + '"]').length,
					$status = $(this).children('.status');
				
				
				if( max > 0 )
				{
					// find diff
					var available	= max - count,
						s			= acf.l10n.flexible_content.available,
						identifier	= ( available == 1 ) ? 'layout' : 'layouts',
				
					// translate
					s = s.replace('{available}', available);
					s = s.replace('{max}', max);
					s = s.replace('{label}', '"' + label + '"');
					s = s.replace('{identifier}', acf.l10n.flexible_content[ identifier ]);
					
					
					$status.show().text( available ).attr('title', s);
					
					// limit reached?
					if( available == 0 )
					{
						$status.addClass('warning');
					}
				}
				
				
				if( min > 0 )
				{
					// find diff
					var required	= min - count,
						s			= acf.l10n.flexible_content.required,
						identifier	= ( required == 1 ) ? 'layout' : 'layouts',
				
					// translate
					s = s.replace('{required}', required);
					s = s.replace('{min}', min);
					s = s.replace('{label}', '"' + label + '"');
					s = s.replace('{identifier}', acf.l10n.flexible_content[ identifier ]);
					
					
					if( required > 0 )
					{
						$status.addClass('warning').show().text( required ).attr('title', s);
					}
					
					
				}
				
			});
			
			
			// add popup
			$a.after( $popup );
			
			
			// within layout?
			if( in_layout )
			{
				$popup.addClass('within-layout');
				$popup.closest('.layout').addClass('popup-open');
			}
			
			
			// vars
			$popup.css({
				'margin-top' : 0 - $popup.height() - $a.outerHeight() - 14,
				'margin-left' : ( $a.outerWidth() - $popup.width() ) / 2,
			});
			
			
			// check distance to top
			var offset = $popup.offset().top;
			
			if( offset < 30 )
			{
				$popup.css({
					'margin-top' : 15
				});
				
				$popup.find('.bit').addClass('top');
			}
			
			
			$popup.children('.focus').trigger('focus');
			
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
		
		acf.get_fields({ type : 'flexible_content'}, $el).each(function(){
			
			acf.fields.flexible_content.set( $(this) ).init();
			
		});
		
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
	
	$(document).on('click', '.acf-flexible-content .acf-fc-add', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			before	= false;
			
		
		// before
		if( $(this).attr('data-before') )
		{
			before = true;
		}
		
		
		// open_popup
		acf.fields.flexible_content.set( $field ).open_popup( $a, before );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-flexible-content .acf-fc-remove', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			$layout	= $a.closest('.layout');
			
			
		// remove
		acf.fields.flexible_content.set( $field ).remove( $layout );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-flexible-content .acf-fc-layout-handle', function( e ){
	
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$layout	= $a.closest('.layout');
			
		
		// toggle
		acf.fields.flexible_content.toggle( $layout );
		
		
		// blur
		$(this).blur();
			
	});
	
	$(document).on('click', '.acf-flexible-content .acf-fc-popup li a', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			$popup	= $a.closest('.acf-fc-popup')
			$layout	= null;
			
			
		// $layout
		if( $popup.hasClass('within-layout') )
		{
			$layout = $popup.closest('.layout');
		}
		
		
		// add
		acf.fields.flexible_content.set( $field ).add( $a.attr('data-layout'), $layout );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('blur', '.acf-flexible-content .acf-fc-popup .focus', function( e ){
		
		var $popup = $(this).parent();
		
		
		// hide controlls?
		if( $popup.closest('.layout').exists() )
		{
			$popup.closest('.layout').removeClass('popup-open');
		}
		
		
		setTimeout(function(){
			
			$popup.remove();
			
		}, 200);

		
	});
	
	
	/*
	*  Validate
	*
	*  jQuery events for this field
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('acf/validate_field', function( e, field ){
		
		// vars
		var $field = $( field );
		
		
		// validate
		if( ! $field.hasClass('field_type-flexible_content') )
		{
			return;
		}
		
		var $el = $field.find('.acf-flexible-content:first');
		
		
		// required
		$field.data('validation', false);
		$field.data('validation_message', false);
		
		
		if( $el.children('.values').children('.layout').exists() )
		{
			$field.data('validation', true);
		}
		
		
		// min total
		var min = parseInt( $el.attr('data-min') );
		
		if( min > 0 )
		{
			if( $el.children('.values').children('.layout').length < min )
			{
				var identifier	= ( min == 1 ) ? 'layout' : 'layouts',
					s 			= acf.l10n.flexible_content.min;
				
				// translate
				s = s.replace('{min}', min);
				s = s.replace('{identifier}', acf.l10n.flexible_content[ identifier ]);
				
				
				$field.data('validation', false);
				$field.data('validation_message', s);
			}
		}
		
		
		// min layout
		var $popup = $( $el.children('.tmpl-popup').html() );
		
		$popup.find('a').each(function(){
			
			// vars
			var min		= parseInt( $(this).attr('data-min') ),
				max		= parseInt( $(this).attr('data-max') ),
				name	= $(this).attr('data-layout'),
				label	= $(this).text(),
				count	= $el.children('.values').children('.layout[data-layout="' + name + '"]').length;
			
			
			if( count < min )
			{
				var identifier	= ( min == 1 ) ? 'layout' : 'layouts',
					s 			= acf.l10n.flexible_content.min_layout;
				
				// translate
				s = s.replace('{min}', min);
				s = s.replace('{label}', '"' + label + '"');
				s = s.replace('{identifier}', acf.l10n.flexible_content[ identifier ]);
				
				$field.data('validation', false);
				$field.data('validation_message', s);
			}
			
		});
		
		
		
		
	});
	
	
	
	/*
	*  Gallery
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	18/08/13
	*
	*/
	
	acf.fields.gallery = {
		
		get_field : function( $el ){
			
			return $el.closest('.acf-gallery');
			
		},
		
		has_value : function( $el ){
			
			return $el.find('.acf-gallery-attachment').exists();
				
		},
		
		init : function( $el ){
					
			// sortable
			$el.find('.acf-gallery-attachments').unbind('sortable').sortable({
				
				items					: '.acf-gallery-attachment',
				//handle				: '.acf-gallery-attachment',
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
			
			
			// render
			this.render( $el );
					
		},
		
		render : function( $el ) {
			
			// vars
			var $select = $el.find('[data-name="bulk-actions-select"]');
			
			
			// disable select
			if( this.has_value( $el ) )
			{
				$select.removeAttr('disabled');
			}
			else
			{
				$select.attr('disabled', 'disabled');
			}
			
		},
		
		sort : function( $select ){
			
			// vars
			var $gallery = this.get_field( $select ),
				sort = $select.val();
			
			
			// validate
			if( !sort )
			{
				return;	
			}
			
			
			// vars
			var data = {
				action		: 'acf/fields/gallery/get_sort_order',
				field_key	: acf.get_field_wrap( $gallery ).attr('data-key'),
				nonce		: acf.get('nonce'),
				post_id		: acf.get('post_id'),
				ids			: [],
				sort		: sort
			};
			
			
			$gallery.find('.acf-gallery-attachment').each(function(){
				
				data.ids.push( $(this).attr('data-id') );
				
			});
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'json',
				type		: 'get',
				cache		: false,
				data		: data,
				success		: function( json ){
					
					// validate
					if( !json.success )
					{
						return;
					}
					
					
					// reverse order
					json.data.reverse();
					
					
					_.each( json.data, function( id ) {
						
						var $el = $gallery.find('.acf-gallery-attachment[data-id="' + id  + '"]');
						
						$gallery.find('.acf-gallery-attachments').prepend( $el );
						
					});
					
				}
			});
			
		},
		
		clear_selection : function( $gallery ){
			
			$gallery.find('.acf-gallery-attachment.active').removeClass('active');
		},
		
		select : function( $attachment ){
			
			// vars
			var $gallery = this.get_field( $attachment ),
				id = $attachment.attr('data-id');
			
			
			// clear selection
			this.clear_selection( $gallery );
			
			
			// add selection
			$attachment.addClass('active');
			
			
			// fetch
			this.fetch( id, $gallery );
			
			
			// open sidebar
			this.open_sidebar( $gallery );
			
		},
		
		open_sidebar : function( $gallery ){
			
			$gallery.find('.acf-gallery-main').animate({ right : 300 }, 250);
			$gallery.find('.acf-gallery-side').animate({ width : 299 }, 250);
			
		},
		
		close_sidebar : function( $gallery ){
			
			// deselect attachmnet
			this.clear_selection( $gallery );
			
			
			// animate
			$gallery.find('.acf-gallery-main').animate({ right : 0 }, 250);
			$gallery.find('.acf-gallery-side').animate({ width : 0 }, 250, function(){
				
				$gallery.find('.acf-gallery-side-data').html( '' );
				
			});
			
		},
		
		close : function( $a ){
			
			// vars
			var $gallery = this.get_field( $a );
			
			
			this.close_sidebar( $gallery );
		},
		
		fetch : function( id, $gallery ){
		
		
			// add loading class, stops scroll loading
			
			
			// vars
			var data = {
				action		: 'acf/fields/gallery/get_attachment',
				field_key	: acf.get_field_wrap( $gallery ).attr('data-key'),
				nonce		: acf.get('nonce'),
				post_id		: acf.get('post_id'),
				id			: id
			};
			
			
			// abort XHR if this field is already loading AJAX data
			if( $gallery.data('xhr') )
			{
				$gallery.data('xhr').abort();
			}
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'get',
				cache		: false,
				data		: data,
				success		: function( html ){
					
					// render
					$gallery.find('.acf-gallery-side-data').html( html );
					
				}
			});
			
			
			// update el data
			$gallery.data('xhr', xhr);
			
		},
		
		save : function( $a ){
			
			// validate
			if( $a.attr('disabled') )
			{
				return false;
			}
			
			
			// vars
			var $gallery = this.get_field( $a ),
				$form = $gallery.find('.acf-gallery-side-data'),
				data = acf.serialize_form( $form );
				
			
			// add attr
			$a.attr('disabled', 'true');
			$a.before('<i class="acf-loading"></i>');
			
			
			// append AJAX action		
			data.action = 'acf/fields/gallery/update_attachment';
			data.nonce = acf.get('nonce');
			
				
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: data,
				type		: 'post',
				dataType	: 'json',
				success		: function( json ){
					
					$a.removeAttr('disabled');
					$a.prev('.acf-loading').remove();
					
				}
			});
			
		},
		
		add : function( $el, image ){
			
			// append to image data
			$.extend(image, {
				name	: $el.find('[data-name="ids"]').attr('name')
			});
			
			
			// template
			var tmpl = acf._e('gallery', 'tmpl'),
				html = _.template(tmpl, image);
			
			
			// append
			$el.find('.acf-gallery-attachments').append( html );
			
			
			// render
			this.render( $el );
			
			
		},
		
		remove : function( $a ){
			
			// vars
			var id = $a.attr('data-id'),
				$el = this.get_field( $a );
			
			
			// deselect attachmnet
			this.clear_selection( $el );
			
			
			// update sidebar
			$el.find('.acf-gallery-side-data').html('');
			
			
			// remove image
			$el.find('.acf-gallery-attachment[data-id="' + id  + '"]').remove();
			
			
			// close sidebar
			if( !this.has_value( $el ) )
			{
				this.close_sidebar( $el );
			}
			
			
			// render
			this.render( $el );
		},
		
		render_collection : function( frame ){
			
			// Note: Need to find a differen 'on' event. Now that attachments load custom fields, this function can't rely on a timeout. Instead, hook into a render function foreach item
			
			// set timeout for 0, then it will always run last after the add event
			setTimeout(function(){
			
			
			// vars
			var $gallery	=	_media.div,
				$content	=	_media.frame.content.get().$el

				collection	=	_media.frame.content.get().collection || null;
				
			
			if( collection )
			{
				var i = -1;
				
				collection.each(function( item ){
					
					i++;
					
					var $li = $content.find('.attachments > .attachment:eq(' + i + ')');
					
					
					// if image is already inside the gallery, disable it!
					if( $gallery.find('.thumbnails .thumbnail[data-id="' + item.id + '"]').exists() )
					{
						item.off('selection:single');
						$li.addClass('acf-selected');
					}
					
				});
			}
			
			
			}, 0);

				
		},
		
		popup : function( $a ){
			
			// vars
			var $el = $a.closest('.acf-gallery'),
				library = acf.get_data( $el, 'library' ),
				preview_size = acf.get_data( $el, 'preview_size' );
			
			
			// popup
			var frame = acf.media.upload_popup({
				title		: acf._e('gallery', 'select'),
				type		: 'image',
				multiple	: 1,
				uploadedTo	: ( library == 'uploadedTo' ) ? acf.get('post_id') : 0,
				activate	: function( frame ){
					
					acf.fields.gallery.render_collection( frame );
					
					/*

					
					 
					frame.content.get().collection.on( 'reset add', function(){
					    
						acf.fields.gallery.render_collection( frame );
					    
				    });
*/
						
				},
				select		: function( attachment, i ) {
					
					// is image already in gallery?
					if( $el.find('.acf-gallery-attachment[data-id="' + attachment.id  + '"]').exists() )
					{
						return;
					}
					
					
			    	// vars
			    	var image = {
				    	id			:	attachment.id,
				    	url			:	attachment.attributes.url
			    	};
			    	
			    	
			    	// file?
				    if( attachment.attributes.type != 'image' )
				    {
					    image.url = attachment.attributes.icon;
				    }
				    
				    
				    // is preview size available?
			    	if( attachment.attributes.sizes && attachment.attributes.sizes[ preview_size ] )
			    	{
				    	image.url = attachment.attributes.sizes[ preview_size ].url;
			    	}
				    
				    
			    	// add file to field
			        acf.fields.gallery.add( $el, image );
					
				}
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
		
		acf.get_fields({ type : 'gallery'}, $el).each(function(){
			
			acf.fields.gallery.init( $(this).find('.acf-gallery') );
			
		});
		
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
	
	$(document).on('click', '.acf-gallery .acf-gallery-attachment', function( e ){
		
		acf.fields.gallery.select( $(this) );
		
	});
	
	$(document).on('click', '.acf-gallery [data-name="close-attachment-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.gallery.close( $(this) );
		
	});	
	
	$(document).on('click', '.acf-gallery [data-name="save-attachment-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.gallery.save( $(this) );
		
	});
	
	$(document).on('click', '.acf-gallery [data-name="remove-attachment-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.gallery.remove( $(this) );
		
	});
	
	$(document).on('click', '.acf-gallery [data-name="add-attachment-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.gallery.popup( $(this) );
		
	});
	
	$(document).on('change', '.acf-gallery [data-name="bulk-actions-select"]', function( e ){
		
		if( ! $(this).val() )
		{
			return false;
		}
		
		acf.fields.gallery.sort( $(this) );
		
		$(this).val('');
		
	});
	
	

})(jQuery);
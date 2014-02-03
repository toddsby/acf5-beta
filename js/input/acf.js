/*
*  input.js
*
*  All javascript needed for ACF to work
*
*  @type	awesome
*  @date	1/08/13
*
*  @param	N/A
*  @return	N/A
*/ 

var acf = {
	
	// vars
	l10n				: {},
	o					: {},
	
	
	// functions
	get					: null,
	update				: null,
	_e					: null,
	get_atts			: null,
	get_fields			: null,
	get_uniqid			: null,
	serialize_form		: null,
	
	
	// hooks
	add_action			: null,
	remove_action		: null,
	do_action			: null,
	add_filter			: null,
	remove_filtern		: null,
	apply_filters		: null,
	
	
	// helper functions
	helpers				:	{
		is_clone_field	:	null,
	},
	
	
	// modules
	validation			:	null,
	conditional_logic	:	null,
	media				:	null,
	
	
	// fields
	fields				:	{
		date_picker		:	null,
		color_picker	:	null,
		image			:	null,
		file			:	null,
		wysiwyg			:	null,
		gallery			:	null,
		relationship	:	null
	}
};

(function($){
	
	
	/*
	*  Functions
	*
	*  These functions interact with the o object, and events
	*
	*  @type	function
	*  @date	23/10/13
	*  @since	5.0.0
	*
	*  @param	$n/a
	*  @return	$n/a
	*/
	
	$.extend(acf, {
		
		update : function( k, v ){
				
			this.o[ k ] = v;
			
		},
		
		get : function( k ){
			
			return this.o[ k ] || null;
			
		},
		
		_e : function( context, string ){
			
			// defaults
			string = string || false;
			
			
			// get context
			var r = this.l10n[ context ] || false;
			
			
			// get string
			if( string )
			{
				r = r[ string ] || false;
			}
			
			
			// return
			return r || '';
			
		},
		
		get_fields : function( args, $el, allow_filter ){
			
			// defaults
			args = args || {};
			$el = $el || $('body');
			allow_filter = allow_filter || true;
			
			
			// vars
			var selector = '.acf-field';
			
			
			// add selector
			$.each( args, function( k, v ) {
				
				selector += '[data-' + k + '="' + v + '"]';
				
			});
			
			
			// get fields
			var $fields = $el.find(selector);
			
			//console.log('get_fields(%o, %s, %b). selector = %s', $el, field_type, allow_filter, selector);
			//console.log( $el );
			//console.log( $fields );
			
			// filter out fields
			if( allow_filter )
			{
				$fields = $fields.filter(function(){
					
					return acf.apply_filters('is_field_ready_for_js', true, $(this));			

				});
			}
			
			
			// return
			return $fields;
							
		},
		
		get_field : function( field_key, $el ){
			
			// defaults
			$el = $el || $('body');
			
			
			// get fields
			$fields = this.get_fields({ key : field_key }, $el);
			
			
			// validate
			if( !$fields.exists() )
			{
				return false;
			}
			
			
			// return
			return $fields.first();
			
		},
		
		get_field_wrap : function( $el ){
			
			return $el.closest('.acf-field');
			
		},
		
		get_field_data : function( $el, name ){
			
			// defaults
			name = name || false;
			
			
			// vars
			$field = this.get_field_wrap( $el );
			
			console.log( $field );
			// return
			return this.get_data( $field, name );
			
		},
		
		get_data : function( $el, name ){
			
			// defaults
			name = name || false;
			
			
			// vars
			var data = false;
			
			
			// specific data-name
			if( name )
			{
				data = $el.attr('data-' + name)
				
				// convert ints (don't worry about floats. I doubt these would ever appear in data atts...)
        		if( $.isNumeric(data) )
        		{
	        		data = parseInt(data);
        		}
			}
			else
			{
				// all data-names
				data = {};
				
				if( $el.exists() )
				{
					$.each( $el[0].attributes, function( index, attr ) {
			        	
			        	if( attr.name.substr(0, 5) == 'data-' )
			        	{
			        		// vars
			        		var v = attr.value,
			        			k = attr.name.replace('data-', '');
			        		
			        		
			        		// convert ints (don't worry about floats. I doubt these would ever appear in data atts...)
			        		if( $.isNumeric(v) )
			        		{
				        		v = parseInt(v);
			        		}
			        		
			        		
			        		// add to atts
				        	data[ k ] = v;
			        	}
			        });
		        }
			}
			
			
			// return
	        return data;
				
		},
		
		is_field : function( $el, args ){
			
			// var
			var r = true;
			
			
			// check $el calss
			if( ! $el.hasClass('acf-field') )
			{
				r = false;
			}
			
			
			// check args (data attributes)
			$.each( args, function( k, v ) {
				
				if( $el.attr('data-' + k) != v )
				{
					r = false;
				}
				
			});
			
			
			// return
			return r;
			
		},
		
		get_uniqid : function( prefix, more_entropy ){
		
			// + original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// + revised by: Kankrelune (http://www.webfaktory.info/)
			// % note 1: Uses an internal counter (in php_js global) to avoid collision
			// * example 1: uniqid();
			// * returns 1: 'a30285b160c14'
			// * example 2: uniqid('foo');
			// * returns 2: 'fooa30285b1cd361'
			// * example 3: uniqid('bar', true);
			// * returns 3: 'bara20285b23dfd1.31879087'
			if (typeof prefix === 'undefined') {
				prefix = "";
			}
			
			var retId;
			var formatSeed = function (seed, reqWidth) {
				seed = parseInt(seed, 10).toString(16); // to hex str
				if (reqWidth < seed.length) { // so long we split
					return seed.slice(seed.length - reqWidth);
				}
				if (reqWidth > seed.length) { // so short we pad
					return Array(1 + (reqWidth - seed.length)).join('0') + seed;
				}
				return seed;
			};
			
			// BEGIN REDUNDANT
			if (!this.php_js) {
				this.php_js = {};
			}
			// END REDUNDANT
			if (!this.php_js.uniqidSeed) { // init seed with big random int
				this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
			}
			this.php_js.uniqidSeed++;
			
			retId = prefix; // start with prefix, add current milliseconds hex string
			retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
			retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
			if (more_entropy) {
				// for more entropy we add a float lower to 10
				retId += (Math.random() * 10).toFixed(8).toString();
			}
			
			return retId;
			
		},
		
		serialize_form : function( $el ){
			
			// vars
			var data = {},
				names = {};
			
			
			// selector
			$selector = $el.find('select, textarea, input');
			
			
			// populate data
			$.each( $selector.serializeArray(), function( i, pair ) {
				
				// initiate name
				if( pair.name.slice(-2) === '[]' )
				{
					// remove []
					pair.name = pair.name.replace('[]', '');
					
					
					// initiate counter
					if( typeof names[ pair.name ] === 'undefined'){
						
						names[ pair.name ] = -1;
					}
					
					
					// increase counter
					names[ pair.name ]++;
					
					
					// add key
					pair.name += '[' + names[ pair.name ] +']';
				}
				
				
				// append to data
				data[ pair.name ] = pair.value;
				
			});
			
			
			// return
			return data;
		},
		
		remove_tr : function( $tr, callback ){
			
			// vars
			var height = $tr.height(),
				children = $tr.children().length;
			
			
			// add class
			$tr.addClass('acf-remove-element');
			
			
			// after animation
			setTimeout(function(){
				
				// remove class
				$tr.removeClass('acf-remove-element');
				
				
				// vars
				$tr.html('<td style="padding:0; height:' + height + 'px" colspan="' + children + '"></td>');
				
				
				$tr.children('td').animate({ height : 0}, 250, function(){
					
					$tr.remove();
					
					if( typeof(callback) == 'function' )
					{
						callback();
					}
					
					
				});
				
					
			}, 250);
			
		},
		
		remove_el : function( $el, callback ){
			
			// set layout
			$el.css({
				height		: $el.height(),
				width		: $el.width(),
				position	: 'absolute',
				padding		: 0
			});
			
			
			// wrap field
			$el.wrap( '<div class="acf-temp-wrap" style="height:' + $el.outerHeight(true) + 'px"></div>' );
			
			
			// fade $el
			$el.animate({ opacity : 0 }, 250);
			
			
			// remove
			$el.parent('.acf-temp-wrap').animate({ height : 0 }, 250, function(){
				
				$(this).remove();
				
				if( typeof(callback) == 'function' )
				{
					callback();
				}
				
			});
			
			
		},
		
		isset_object : function(){
			
			var args = Array.prototype.slice.call(arguments),
				obj = args.shift();
			
			for (var i = 0; i < args.length; i++) {
				if (!obj.hasOwnProperty(args[i])) {
					return false;
				}
				obj = obj[args[i]];
			}
			
			return true;
				
		},
		
		open_popup : function( args ){
			
			// vars
			$popup = $('body > #acf-poup');
			
			
			// already exists?
			if( $popup.exists() )
			{
				return update_popup(args);
			}
			
			
			// template
			var tmpl = [
				'<div id="acf-popup">',
					'<div class="acf-popup-box acf-box">',
						'<div class="title"><h3></h3><a href="#" class="acf-icon"><i class="acf-sprite-delete "></i></a></div>',
						'<div class="inner"></div>',
						'<div class="loading"><i class="acf-loading"></i></div>',
					'</div>',
					'<div class="bg"></div>',
				'</div>'
			].join('');
			
			
			// append
			$('body').append( tmpl );
			
			
			$('#acf-popup .bg, #acf-popup .acf-icon').on('click', function(){
				
				acf.close_popup();
				
			});
			
			
			// update
			return this.update_popup(args);
			
		},
		
		update_popup : function( args ){
			
			// vars
			$popup = $('#acf-popup');
			
			
			// validate
			if( !$popup.exists() )
			{
				return false
			}
			
			
			// defaults
			args = $.extend({}, {
				title	: '',
				content : '',
				width	: 0,
				height	: 0,
				loading : false
			}, args);
			
			
			if( args.width )
			{
				$popup.find('.acf-popup-box').css({
					'width'			: args.width,
					'margin-left'	: 0 - (args.width / 2),
				});
			}
			
			if( args.height )
			{
				$popup.find('.acf-popup-box').css({
					'height'		: args.height,
					'margin-top'	: 0 - (args.height / 2),
				});	
			}
			
			if( args.title )
			{
				$popup.find('.title h3').html( args.title );
			}
			
			if( args.content )
			{
				$popup.find('.inner').html( args.content );
			}
			
			if( args.loading )
			{
				$popup.find('.loading').show();
			}
			else
			{
				$popup.find('.loading').hide();
			}
			
			return $popup;
		},
		
		close_popup : function(){
			
			// vars
			$popup = $('#acf-popup');
			
			
			// already exists?
			if( $popup.exists() )
			{
				$popup.remove();
			}
			
			
		}
		
	});
	
	
	/*
	*  Hooks
	*
	*  These functions act as wrapper functions for the included event-manajer JS library
	*  Wrapper functions will ensure that future changes to event-manager do not distrupt
	*  any custom actions / filter code written by users
	*
	*  @type	functions
	*  @date	30/11/2013
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	$.extend(acf, {
		
		add_action : function() {
			
			// allow multiple action parameters such as 'ready append'
			var actions = arguments[0].split(' ');
			
			for( k in actions )
			{
				// prefix action
				arguments[0] = 'acf.' + actions[ k ];
				
				wp.hooks.addAction.apply(this, arguments);
			}
			
			return this;
		},
		
		remove_action : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.removeAction.apply(this, arguments);
			
			return this;
		},
		
		do_action : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.doAction.apply(this, arguments);
			
			return this;
		},
		
		add_filter : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.addFilter.apply(this, arguments);
			
			return this;
		},
		
		remove_filter : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.removeFilter.apply(this, arguments);
			
			return this;
		},
		
		apply_filters : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			return wp.hooks.applyFilters.apply(this, arguments);
		}
		
	});
    
    
    acf.add_filter('is_field_ready_for_js', function( ready, $field ){
		
		// repeater sub field
		if( $field.parents('.acf-row[data-id="acfcloneindex"]').exists() )
		{
			ready = false;
		}
		
		
		// widget
		if( $field.parents('#available-widgets').exists() )
		{
			ready = false;
		}
		
		
		// debug
		//console.log('is_field_ready_for_js %o, %b', $field, ready);
		
		
		// return
		return ready;
	    
    });
    
    
	/*
	*  is_clone_field
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	acf.helpers.is_clone_field = function( input )
	{
		// sub field
		if( input.attr('name') && input.attr('name').indexOf('[acfcloneindex]') != -1 )
		{
			return true;
		}
		

		return false;
	};
	
	
	/*
	*  Exists
	*
	*  @description: returns true / false		
	*  @created: 1/03/2011
	*/
	
	$.fn.exists = function()
	{
		return $(this).length>0;
	};
	
	
	/*
	*  outerHTML
	*
	*  This function will return a string containing the HTML of the selected element
	*
	*  @type	function
	*  @date	19/11/2013
	*  @since	5.0.0
	*
	*  @param	$.fn
	*  @return	(string)
	*/
	
	$.fn.outerHTML = function() {
	    
	    return $(this).clone().wrap('<div>').parent().html();
	    
	}
	
	
	/*
	*  3.5 Media
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 16/01/13
	*/
	
	acf.media = {
		
		upload_popup : function( args ) {
			
			// defaults
			args = $.extend({}, {
				title		: '',		// Uploade Image
				type		: '',		// image
				query		: {},		// library query
				uploadedTo	: 0,		// restrict browsing to post_id
				multiple	: 0,		// allow multiple attachments to be selected
				activate	: function(){},
				select		: function( attachment, i ){}
			}, args);
			
			
			// args.query
			if( args.type )
			{
				args.query = { 
					type : args.type
				};
			}
			
			
			// create frame
			var frame = wp.media({
				states : [
					new wp.media.controller.Library({
						title		: args.title,
						multiple	: args.multiple,
						library		: wp.media.query(args.query),
						priority	: 20,
						filterable	: 'all'
					})
				]
			});
			
			
			// events
			frame.on('content:activate', function(){
				
				// vars
				var toolbar = null,
					filters = null;
					
				
				// populate above vars making sure to allow for failure
				try
				{
					toolbar = frame.content.get().toolbar;
					filters = toolbar.get('filters');
				} 
				catch(e)
				{
					// one of the objects was 'undefined'... perhaps the frame open is Upload Files
					//console.log( e );
				}
				
				
				// validate
				if( !filters )
				{
					return false;
				}
				
				
				// no need for 'uploaded' filter
				if( args.uploadedTo )
				{
					filters.$el.find('option[value="uploaded"]').remove();
					filters.$el.after('<span>' + acf._e('image', 'uploadedTo') + '</span>')
					
					$.each( filters.filters, function( k, v ){
						
						v.props.uploadedTo = args.uploadedTo;
						
					});
				}
				
				
				// image
				if( args.type == 'image' )
				{
					// filter only images
					$.each( filters.filters, function( k, v ){
					
						v.props.type = 'image';
						
					});
					
					
					// remove non image options from filter list
					filters.$el.find('option').each(function(){
						
						// vars
						var v = $(this).attr('value');
						
						
						// don't remove the 'uploadedTo' if the library option is 'all'
						if( v == 'uploaded' && !args.uploadedTo )
						{
							return;
						}
						
						if( v.indexOf('image') === -1 )
						{
							$(this).remove();
						}
						
					});
					
					
					// set default filter
					filters.$el.val('image').trigger('change');
					
				}
				
				
				// callback
				if( typeof args.activate === 'function' )
				{
					args.activate.apply( this, [ frame ] );
				}
			});
			
			
			// select callback
			frame.on( 'select', function() {
				
				
				// validate
				if( typeof args.select !== 'function' )
				{
					return false;
				}
				
				
				// get selected images
				var selection = frame.state().get('selection');
				
				
				// loop over selection
				if( selection )
				{
					var i = -1;
					
					selection.each(function( attachment ){
						
						i++;
						
						args.select.apply( this, attachment, i );
						
					});
				}
				
			});
			
			
			// close
			frame.on('close',function(){
			
				setTimeout(function(){
					
					// detach
					frame.detach();
					frame.dispose();
					
					
					// reset var
					frame = null;
					
				}, 500);
				
			});
			
			
			// open popup
			frame.open();
			
			
			// return
			return frame;
			
		},
		
		edit_popup : function( args ) {
			
			// defaults
			args = $.extend({}, {
				title		: '',
				button		: '',
				id			: 0
			}, args);
			
			
			// create frame
			var frame = wp.media({
				title		: args.title,
				multiple	: 0,
				button		: { text : args.button }
			});
			
			
			// open
			frame.on('open',function() {
				
				// set to browse
				if( frame.content._mode != 'browse' )
				{
					frame.content.mode('browse');
				}
				
				
				// add class
				frame.$el.closest('.media-modal').addClass('acf-media-modal acf-expanded');
					
				
				// set selection
				var selection	=	frame.state().get('selection'),
					attachment	=	wp.media.attachment( args.id );
				
				
				// to fetch or not to fetch
				if( $.isEmptyObject(attachment.changed) )
				{
					attachment.fetch();
				}
				

				selection.add( attachment );
						
			});
			
			
			// select callback
			frame.on( 'select', function() {
				
				
				// validate
				if( typeof args.select !== 'function' )
				{
					return false;
				}
				
				
				// get selected images
				var selection = frame.state().get('selection');
				
				
				// loop over selection
				if( selection )
				{
					var i = -1;
					
					selection.each(function( attachment ){
						
						i++;
						
						args.select( attachment, i );
						
					});
				}
				
			});
			
			
			// close
			frame.on('close',function(){
				
				setTimeout(function(){
					
					// detach
					frame.detach();
					frame.dispose();
					
					
					// reset var
					frame = null;
					
				}, 500);
				
				
				// remove class
				frame.$el.closest('.media-modal').removeClass('acf-media-modal');
				
			});
			
			
			// open popup
			frame.open();
			
			
			// return
			return frame;

		},
		
		init : function(){
			
			// bail early if wp.media does not exist (field group edit page)
			if( typeof(wp.media) == 'undefined' )
			{
				return false;
			}
			
			
			// vars
			var _prototype = wp.media.view.AttachmentCompat.prototype;
			
			
			// orig
			_prototype.orig_render = _prototype.render;
			_prototype.orig_dispose = _prototype.dispose;
			
			
			// update class
			_prototype.className = 'compat-item acf_postbox no_box';
			
			
			// modify render
			_prototype.render = function() {
				
				// reference
				var _this = this;
				
				
				// validate
				if( _this.ignore_render )
				{
					return this;	
				}
				
				
				// run the old render function
				this.orig_render();
				
				
				// add button
				setTimeout(function(){
					
					// vars
					var $media_model = _this.$el.closest('.media-modal');
					
					
					// is this an edit only modal?
					if( $media_model.hasClass('acf-media-modal') )
					{
						return;	
					}
					
					
					// does button already exist?
					if( $media_model.find('.media-frame-router .acf-expand-details').exists() )
					{
						return;	
					}
					
					
					// create button
					var button = $([
						'<a href="#" class="acf-expand-details">',
							'<span class="icon"></span>',
							'<span class="is-closed">' + acf.l10n.core.expand_details +  '</span>',
							'<span class="is-open">' + acf.l10n.core.collapse_details +  '</span>',
						'</a>'
					].join('')); 
					
					
					// add events
					button.on('click', function( e ){
						
						e.preventDefault();
						
						if( $media_model.hasClass('acf-expanded') )
						{
							$media_model.removeClass('acf-expanded');
						}
						else
						{
							$media_model.addClass('acf-expanded');
						}
						
					});
					
					
					// append
					$media_model.find('.media-frame-router').append( button );
						
				
				}, 0);
				
				
				// setup fields
				// The clearTimout is needed to prevent many setup functions from running at the same time
				clearTimeout( acf.media.render_timout );
				acf.media.render_timout = setTimeout(function(){

					$(document).trigger( 'acf/setup_fields', [ _this.$el ] );
					
				}, 50);

				
				// return based on the origional render function
				return this;
			};
			
			
			// modify dispose
			_prototype.dispose = function() {
				
				// remove
				$(document).trigger('acf/remove_fields', [ this.$el ]);
				
				
				// run the old render function
				this.orig_dispose();
				
			};
			
			
			// override save
			_prototype.save = function( event ) {
			
				var data = {},
					names = {};
				
				if ( event )
					event.preventDefault();
					
					
				_.each( this.$el.serializeArray(), function( pair ) {
				
					// initiate name
					if( pair.name.slice(-2) === '[]' )
					{
						// remove []
						pair.name = pair.name.replace('[]', '');
						
						
						// initiate counter
						if( typeof names[ pair.name ] === 'undefined'){
							
							names[ pair.name ] = -1;
							//console.log( names[ pair.name ] );
							
						}
						
						
						names[ pair.name ]++
						
						pair.name += '[' + names[ pair.name ] +']';
						
						
					}
 
					data[ pair.name ] = pair.value;
				});
 
				this.ignore_render = true;
				this.model.saveCompat( data );
				
			};
		}
	};
	
	
	/*
	*  Conditional Logic Calculate
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	acf.conditional_logic = {
		
		items : [],
		
		init : function(){
			
			// reference
			var _this = this;
			
			
			// events
			$(document).on('change', '.acf-field input, .acf-field textarea, .acf-field select', function(){
				
				// preview hack
				if( $('#acf-has-changed').exists() )
				{
					$('#acf-has-changed').val(1);
				}
				
				_this.change( $(this) );
				
			});
			
			
			acf.add_action('ready', function( $el ){
				
				//console.log('acf/setup_fields calling acf.conditional_logic.refresh()');
				_this.refresh( $(el) );
				
			});
			
			
			//console.log('acf.conditional_logic.init() calling acf.conditional_logic.refresh()');
			_this.refresh();
			
		},
		change : function( $el ){
			
			//console.log('change %o', $el);
			// reference
			var _this = this;
			
			
			// vars
			var $field	= acf.get_field_wrap($el),
				key		= acf.get_data($field, 'key');
			
			
			// loop through items and find rules where this field key is a trigger
			$.each(this.items, function( k, item ){
				
				$.each(item.rules, function( k2, rule ){
					
					// compare rule against the changed $field
					if( rule.field == key )
					{
						_this.refresh_field( item );
					}
					
				});
				
			});
			
		},
		
		refresh_field : function( item ){
			
			//console.log( 'refresh_field: %o ', item );
			// reference
			var _this = this;
			
			
			// vars
			var $targets = acf.get_fields({key : item.field});

			
			// may be multiple targets (sub fields)
			$targets.each(function(){
				
				//console.log('target %o', $(this));
				
				// vars
				var show = true;
				
				
				// if 'any' was selected, start of as false and any match will result in show = true
				if( item.allorany == 'any' )
				{
					show = false;
				}
				
				
				// vars
				var $target		=	$(this),
					hide_all	=	true;
				
				
				// loop through rules
				$.each(item.rules, function( k2, rule ){
					
					// vars
					var $toggle = acf.get_fields({key : rule.field});
					
					
					// are any of $toggle a sub field?
					if( $toggle.hasClass('sub_field') )
					{
						// toggle may be a sibling sub field.
						// if so ,show an empty td but keep the column
						$toggle = $target.siblings('.field_key-' + rule.field);
						hide_all = false;
						
						
						// if no toggle was found, we need to look at parent sub fields.
						// if so, hide the entire column
						if( ! $toggle.exists() )
						{
							// loop through all the parents that could contain sub fields
							$target.parents('tr').each(function(){
								
								// attempt to update $toggle to this parent sub field
								$toggle = $(this).find('.field_key-' + rule.field)
								
								// if the parent sub field actuallly exists, great! Stop the loop
								if( $toggle.exists() )
								{
									return false;
								}
								
							});

							hide_all = true;
						}
						
					}
					
					
					// if this sub field is within a flexible content layout, hide the entire column because 
					// there will never be another row added to this table
					var parent = $target.parent('tr').parent().parent('table').parent('.layout');
					if( parent.exists() )
					{
						hide_all = true;
						
						if( $target.is('th') && $toggle.is('th') )
						{
							$toggle = $target.closest('.layout').find('td.field_key-' + rule.field);
						}

					}
					
					// if this sub field is within a repeater field which has a max row of 1, hide the entire column because 
					// there will never be another row added to this table
					var parent = $target.parent('tr').parent().parent('table').parent('.repeater');
					if( parent.exists() && parent.attr('data-max_rows') == '1' )
					{
						hide_all = true;
						
						if( $target.is('th') && $toggle.is('th') )
						{
							$toggle = $target.closest('table').find('td.field_key-' + rule.field);
						}

					}
					
					
					var calculate = _this.calculate( rule, $toggle, $target );
					
					if( item.allorany == 'all' )
					{
						if( calculate == false )
						{
							show = false;
							
							// end loop
							return false;
						}
					}
					else
					{
						if( calculate == true )
						{
							show = true;
							
							// end loop
							return false;
						}
					}
					
				});
				// $.each(item.rules, function( k2, rule ){
				
				
				// clear classes
				$target.removeClass('acf-conditional_logic-hide acf-conditional_logic-show acf-show-blank');
				
				
				// hide / show field
				if( show )
				{
					// remove "disabled"
					$target.find('input, textarea, select').removeAttr('disabled');
					
					$target.addClass('acf-conditional_logic-show');
					
					// hook
					$(document).trigger('acf/conditional_logic/show', [ $target, item ]);
					
				}
				else
				{
					// add "disabled"
					$target.find('input, textarea, select').attr('disabled', 'disabled');
					
					$target.addClass('acf-conditional_logic-hide');
					
					if( !hide_all )
					{
						$target.addClass('acf-show-blank');
					}
					
					// hook
					$(document).trigger('acf/conditional_logic/hide', [ $target, item ]);
				}
				
				
			});
			
		},
		
		refresh : function( $el ){
			
			// defaults
			$el = $el || $('body');
			
			
			// reference
			var _this = this;
			
			
			// loop through items and find rules where this field key is a trigger
			$.each(this.items, function( k, item ){
				
				$.each(item.rules, function( k2, rule ){
					
					// is this field within the $el
					// this will increase performance by ignoring conditional logic outside of this newly appended element ($el)
					if( ! $el.find('.field[data-field_key="' + item.field + '"]').exists() )
					{
						return;
					}
					
					_this.refresh_field( item );
					
				});
				
			});
			
		},
		calculate : function( rule, $toggle, $target ){
			
			// vars
			var r = false;
			

			// compare values
			if( $toggle.hasClass('field_type-true_false') || $toggle.hasClass('field_type-checkbox') || $toggle.hasClass('field_type-radio') )
			{
				var exists = $toggle.find('input[value="' + rule.value + '"]:checked').exists();
				
				
				if( rule.operator == "==" )
				{
					if( exists )
					{
						r = true;
					}
				}
				else
				{
					if( ! exists )
					{
						r = true;
					}
				}
				
			}
			else
			{
				// get val and make sure it is an array
				var val = $toggle.find('input, textarea, select').last().val();
				
				if( ! $.isArray(val) )
				{
					val = [ val ];
				}
				
				
				if( rule.operator == "==" )
				{
					if( $.inArray(rule.value, val) > -1 )
					{
						r = true;
					}
				}
				else
				{
					if( $.inArray(rule.value, val) < 0 )
					{
						r = true;
					}
				}
				
			}
			
			
			// return
			return r;
			
		}
		
	}; 
	
	
	$(document).ready(function(){
		
		acf.do_action('ready', $('body'));
		
		
		// conditional logic
		//acf.conditional_logic.init();
		
	});
	
	
	/*
	*  window load
	*
	*  @description: 
	*  @since: 3.5.5
	*  @created: 22/12/12
	*/
	
	$(window).load(function(){
		
		acf.do_action('load', $('body'));
		
		
		// init
		acf.media.init();
		
		
		setTimeout(function(){
			
			// Hack for CPT without a content editor
			try
			{
				// post_id may be string (user_1) and therefore, the uploaded image cannot be attached to the post
				if( $.isNumeric(acf.o.post_id) )
				{
					wp.media.view.settings.post.id = acf.o.post_id;
				}
				
			} 
			catch(e)
			{
				// one of the objects was 'undefined'...
			}
			
			
			// setup fields
			//$(document).trigger('acf/setup_fields', [ $(document) ]);
			
		}, 10);
		
	});
	
	
	/*
	*  Sortable
	*
	*  These functions will hook into the start and stop of a jQuery sortable event and modify the item and placeholder to look seamless
	*
	*  @type	function
	*  @date	12/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('sortstart', function( $item, $placeholder ){
		
		// if $item is a tr, apply some css to the elements
		if( $item.is('tr') )
		{
			// temp set as relative to find widths
			$item.css('position', 'relative');
			
			
			// set widths for td children		
			$item.children().each(function(){
			
				$(this).width($(this).width());
				
			});
			
			
			// revert postision css
			$item.css('position', 'absolute');
			
			
			// add markup to the placeholder
			$placeholder.html('<td style="height:' + $item.height() + 'px; padding:0;" colspan="' + $item.children('td').length + '"></td>');
		}
		
	});
	
	
	
})(jQuery);
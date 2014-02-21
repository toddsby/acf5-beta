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
			
			
			// is current $el a field?
			// this is the case when editing a field group
			if( $el.is( selector ) )
			{
				$fields = $fields.add( $el );
			}
			
			
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
			var $fields = this.get_fields({ key : field_key }, $el);
			
			
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
		
		is_sub_field : function( $field ) {
			
			if( $field.closest('.acf-field').exists() )
			{
				return true;
			}
			
			return false;
			
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
		
		remove_el : function( $el, callback, end_height ){
			
			// defaults
			end_height = end_height || 0;
			
			
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
			$el.parent('.acf-temp-wrap').animate({ height : end_height }, 250, function(){
				
				$(this).remove();
				
				if( typeof(callback) == 'function' )
				{
					callback();
				}
				
			});
			
			
		},
		
		isset : function(){
			
			var a = arguments,
		        l = a.length,
		        c = null,
		        undef;
			
		    if (l === 0) {
		        throw new Error('Empty isset');
		    }
			
			c = a[0];
			
		    for (i = 1; i < l; i++) {
		    	
		        if (a[i] === undef || c[ a[i] ] === undef) {
		            return false;
		        }
		        
		        c = c[ a[i] ];
		        
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
		
		
		// flexible content sub field
		if( $field.parents('.acf-flexible-content > .clones').exists() )
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
						
						args.select.apply( this, [ attachment, i] );
						
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
			if( typeof wp == 'undefined' )
			{
				return false;
			}
			
			
			// validate prototype
			if( ! acf.isset(wp, 'media', 'view', 'AttachmentCompat', 'prototype') )
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
							'<span class="is-closed"><span class="acf-icon small"><i class="acf-sprite-left"></i></span>' + acf.l10n.core.expand_details +  '</span>',
							'<span class="is-open"><span class="acf-icon small"><i class="acf-sprite-right"></i></span>' + acf.l10n.core.collapse_details +  '</span>',
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
	*  conditional_logic
	*
	*  description
	*
	*  @type	function
	*  @date	21/02/2014
	*  @since	3.5.1
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		
	acf.conditional_logic = {
		
		items : {},
		triggers : {},
		
		init : function(){
			
			console.log( this );
			// reference
			var _this = this;
			
			
			// events
			$(document).on('change', '.acf-field input, .acf-field textarea, .acf-field select', function(){
				
				_this.change( $(this) );
				
			});
			
			
			// actions
			acf.add_action('append', function( $el ){
				
				//console.log('acf/setup_fields calling acf.conditional_logic.refresh()');
				_this.render( $el );
				
			});
			
			
			//console.log('acf.conditional_logic.init() calling acf.conditional_logic.refresh()');
			_this.render();
			
		},
		
		add : function( key, groups ){
			
			// reference
			var _this = this;
			
			
			// append items
			_this.items[ key ] = groups;
			
			
			// populate triggers
			$.each(groups, function( k1, group ){
				
				$.each(group, function( k2, rule ){
					
					// add rule.field to triggers
					if( !acf.isset(_this, 'triggers', rule.field) )
					{
						_this.triggers[ rule.field ] = [];
					}
					
					
					// add key to this trigger
					if( _this.triggers[ rule.field ].indexOf(key) === -1 )
					{
						 _this.triggers[ rule.field ].push( key );
					}
										
				});
				
			});
			
		},
		
		change : function( $el ){
			
			console.log('change %o', $el);
			
			
			// reference
			var _this = this;
			
			
			// vars
			var key = acf.get_field_data($el, 'key');
			
			
			// does this field trigger any actions
			if( acf.isset(_this, 'triggers', key) )
			{
				// update visibiliy
				$.each(_this.triggers[ key ], function( i, target ){
					
					_this.render_field( target );
					
				});
			}
			
		},
		
		render_field : function( key ){
			
			// reference
			var _this = this;
			
			
			// get conditional logic
			var groups = this.items[ key ];
			
			
			// get targets
			var $targets = acf.get_fields({key : key});
			
			
			// may be multiple targets (sub fields)
			$targets.each(function(){
			
				// vars
				var visibility = false;
				
				
				// $el
				var $target = $(this);
				
				
				// loop over groups
				$.each( groups, function( k, group ){
					
					var match_group = true;
					
					
					// loop over rules
					$.each( group, function( k2, rule ){
						
						if( !_this.get_visibility( $target, rule) )
						{
							match_group = false;
							return false;
						}
						
					});
					
					
					if( match_group )
					{
						visibility = true;
						return false;
					}
					
				});
				
				
				// hide / show field
				if( visibility )
				{
					_this.show_field( $target );					
				}
				else
				{
					_this.hide_field( $target );
				}
				
				
			});
			
			
		},
		
		
		show_field : function( $field ){
			
			// vars
			var key = acf.get_data($field, 'key'),
				c = 'hidden-by-conditional-logic';
			
			
			if( acf.is_sub_field($field) )
			{
				var $repeater = $field.closest('table').parent('.acf-repeater');
				
				if( $repeater.exists() && acf.get_data($repeater, 'max') != 1)
				{
					c += ' appear-empty';
					
					$repeater.find('> table > thead th[data-key="' + key + '"]').removeClass( c );
				}
				
			}
			
			
			// add class
			$field.removeClass( c );
			
			
			// remove "disabled"
			$field.find('input, textarea, select').removeAttr('disabled');
			
			
			// hook
			acf.do_action('conditional_logic_show', $field );
			
		},
		
		hide_field : function( $field ){
			
			// vars
			var key = acf.get_data($field, 'key'),
				c = 'hidden-by-conditional-logic';
			
			
			if( acf.is_sub_field($field) )
			{
				var $repeater = $field.closest('table').parent('.acf-repeater');
				
				if( $repeater.exists() && acf.get_data($repeater, 'max') != 1)
				{
					c += ' appear-empty';
					
					$repeater.find('> table > thead th[data-key="' + key + '"]').addClass( c );
				}
				
			}
			
			
			// add class
			$field.addClass( c );
			
			
			// add "disabled"
			$field.find('input, textarea, select').attr('disabled', 'disabled');
			
			
			// hook
			acf.do_action('conditional_logic_hide', $field );
			
		},
		
		get_visibility : function( $target, rule ){
			
			// vars
			var $search = acf.is_sub_field( $target ) ? $target.parent() : $('body');
			
			
			// vars
			var $trigger = acf.get_fields({key : rule.field}, $search);
			
			
			if( !$trigger.exists() )
			{
				// loop through all the parents that could contain sub fields
				$target.parents('tr').each(function(){
					
					$trigger = acf.get_fields({key : rule.field}, $(this));
					
					if( $trigger.length == 1 )
					{
						return false;
					}
					
				});
				
			}
			
						
			//console.log('this.calculate( %o, %o, %o );', rule, $trigger, $target);
			
			
			// calculate
			var visibility = this.calculate( rule, $trigger, $target );
			
			
			// return
			return visibility;
		},
		
		render : function( $el ){
			
			// defaults
			$el = $el || $('body');
			
			
			// reference
			var _this = this;
			
			
			acf.get_fields({},$el).each(function(){
				
				var key = acf.get_data($(this), 'key');
				
				if( acf.isset(_this, 'items', key) )
				{
					_this.render_field( key );
				}
				/*
else if( acf.isset(_this, 'triggers', key) )
				{
					key = _this.triggers[ key ];
					_this.render_field( key );
				}
*/
				
			});
			
		},
		
		calculate : function( rule, $trigger, $target ){
			
			// vars
			var r = false;
			

			// compare values
			if( $trigger.hasClass('field_type-true_false') || $trigger.hasClass('field_type-checkbox') || $trigger.hasClass('field_type-radio') )
			{
				var exists = $trigger.find('input[value="' + rule.value + '"]:checked').exists();
				
				
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
				var val = $trigger.find('input, textarea, select').last().val();
				
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
	
	
	/*
	*  ready
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	$(document).ready(function(){
		
		// fire ready
		acf.do_action('ready', $('body'));
		
		
		// initialize conditional logic
		acf.conditional_logic.init();
		
	});
	
	
	
	/*
	*  Force revisions
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	$(document).on('change', '.acf-field input, .acf-field textarea, .acf-field select', function(){
		
		// preview hack
		if( $('#acf-form-data input[name="_acfchanged"]').exists() )
		{
			$('#acf-form-data input[name="_acfchanged"]').val(1);
		}
		
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
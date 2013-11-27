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
	l10n				:	{},
	o					:	{},
	
	update				:	null,
	get					:	null,
	_e					:	null,
	on					:	null,
	trigger				:	null,
	
	
	// helper functions
	helpers				:	{
		get_atts		: 	null,
		version_compare	:	null,
		uniqid			:	null,
		sortable		:	null,
		add_message		:	null,
		is_clone_field	:	null,
		url_to_object	:	null,
		remove_tr		:	null
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
	*  Basic Object Functions
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
		
		on : function( event, callback ){
			
			$(this).on(event, callback);
			
		},
		
		trigger : function( event, args ){
			
			$(this).trigger(event, args);
			
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
			
		}
		
	});
	
	
	/*
	*  helpers
	*
	*  description
	*
	*  @type	function
	*  @date	5/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	$.extend(acf.helpers, {
		
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
		
		get_atts : function( $el ){
		
			var atts = {};
			
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
		        	atts[ k ] = v;
	        	}
	        });
	        
	        return atts;
				
		},
		
		uniqid : function( prefix, more_entropy ){
		
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
		
		serialize_form : function( $form ){
			
			// vars
			var data = {},
				names = {};
			
			
			// populate data
			$.each( $form.serializeArray(), function( i, pair ) {
				
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
		}
		
	});
	
		
	/*
	*  acf.helpers.isset
	*
	*  http://phpjs.org/functions/isset
	*
	*  @type	function
	*  @date	20/07/13
	*
	*  @param	{mixed}		arguments
	*  @return	{boolean}	
	*/
	
	acf.helpers.isset = function(){
		
		var a = arguments,
	        l = a.length,
	        i = 0,
	        undef;
	
	    if (l === 0) {
	        throw new Error('Empty isset');
	    }
	
	    while (i !== l) {
	        if (a[i] === undef || a[i] === null) {
	            return false;
	        }
	        i++;
	    }
	    
	    return true;
			
	};
    
    
    /*
	*  Helper: url_to_object
	*
	*  @description: 
	*  @since: 4.0.0
	*  @created: 17/01/13
	*/
	
    acf.helpers.url_to_object = function( url ){
	    
	    // vars
	    var obj = {},
	    	pairs = url.split('&');
	    
	    
		for( i in pairs )
		{
		    var split = pairs[i].split('=');
		    obj[decodeURIComponent(split[0])] = decodeURIComponent(split[1]);
		}
		
		return obj;
	    
    };
    
	/*
	*  is_clone_field
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	acf.helpers.is_clone_field = function( input )
	{
		if( input.attr('name') && input.attr('name').indexOf('[acfcloneindex]') != -1 )
		{
			return true;
		}
		
		return false;
	};
	
	
	/*
	*  acf.helpers.add_message
	*
	*  @description: 
	*  @since: 3.2.7
	*  @created: 10/07/2012
	*/
	
	acf.helpers.add_message = function( message, div ){
		
		var message = $('<div class="acf-message-wrapper"><div class="message updated"><p>' + message + '</p></div></div>');
		
		div.prepend( message );
		
		setTimeout(function(){
			
			message.animate({
				opacity : 0
			}, 250, function(){
				message.remove();
			});
			
		}, 1500);
			
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
	
		div : null,
		frame : null,
		render_timout : null,
		
		clear_frame : function(){
			
			// validate
			if( !this.frame )
			{
				return;
			}
			
			
			// detach
			this.frame.detach();
			this.frame.dispose();
			
			
			// reset var
			this.frame = null;
			
		},
		type : function(){
			
			// default
			var type = 'thickbox';
			
			
			// if wp exists
			if( typeof(wp) == "object" )
			{
				type = 'backbone';
			}
			
			
			// return
			return type;
			
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
			$(document).on('change', '.field input, .field textarea, .field select', function(){
				
				// preview hack
				if( $('#acf-has-changed').exists() )
				{
					$('#acf-has-changed').val(1);
				}
				
				_this.change();
				
			});
			
			
			_this.change();
			
		},
		change : function(){
			
			
			// reference
			var _this = this;
			
			
			// loop through items
			$.each(this.items, function( k, item ){
				
				// vars
				var $targets	=	$('.field_key-' + item.field);

				
				// may be multiple targets (sub fields)
				$targets.each(function(){
					
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
						var $toggle = $('.field_key-' + rule.field);
						
						
						
						// sub field?
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
								$toggle = $target.parents('.row').last().find('.field_key-' + rule.field);
								hide_all = true;
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
		
		acf.trigger('ready', [ $(document) ]);
		
		
		// conditional logic
		acf.conditional_logic.init();
		
	});
	
	
	/*
	*  window load
	*
	*  @description: 
	*  @since: 3.5.5
	*  @created: 22/12/12
	*/
	
	$(window).load(function(){
		
		acf.trigger('load', [ $(document) ]);
		
		
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
	
	acf.on('sortstart', function( e, $item, $placeholder ){
		
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
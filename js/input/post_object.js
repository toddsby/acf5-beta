(function($){
	
	/*
	*  Post Object
	*
	*  static model and events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.post_object = {
		
		$el : null,
		$select : null,
		$input : null,
		
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find input
			this.$select = this.$el.find('select');
			
			
			// get options
			this.o = acf.get_atts( this.$select );
			
			
			// return this for chaining
			return this;
			
		},
		init : function(){
			
			// read choices
			var choices = [],
				val = [];
			
			this.$select.find('option:selected').each(function(){
				
				choices.push({
					id : $(this).attr('value'),
					text : $(this).text()
				});
				
				val.push( $(this).attr('value') );
				
			});
			
			
			// multiple
			if( this.o.multiple )
			{
				var name = this.$select.attr('name').replace('[]', '');
				this.$select.attr('name', name);
			}
			else
			{
				// single
				choices = choices[0];
			}
			
			
			// generate input
			var input = '<input type="hidden" name="%name%" id="%id%" value="%value%" />';
			
				input = input.replace( '%name%',	this.$select.attr('name') );
				input = input.replace( '%id%',		this.$select.attr('id') );
				input = input.replace( '%value%',	val.join(',') );
				
			this.$input = $(input);
			
			
			
			// replace DOM
			this.$select.replaceWith( this.$input );
			
			
			// vars
			var data = {
				action		: 'acf/fields/post_object/query',
				field_key	: this.$el.attr('data-key'),
				nonce		: acf.get('nonce'),
				post_id		: acf.get('post_id'),
			};
						
			
			// args
			var args = {
				
				width		: '100%',
				placeholder	: this.o.placeholder,
				allowClear	: 1,
				multiple	: this.o.multiple,
				ajax		: {
					url			: acf.get('ajaxurl'),
					dataType	: 'json',
					type		: 'get',
					cache		: true,
					data		: function (term, page) {
						
						//add search term
						data.s = term;
						/*
console.log('-- data --')
						console.log(term);
						console.log(page);
*/
						
						return data;
						
					},
					results		: function (data, page) {
						
						/*
console.log('-- results --')
						console.log(data);
						console.log(page);
*/

						return { results: data };
					}
				},
				initSelection : function (element, callback) {
				
			        callback( choices );
			        
			    }
				
			};
			
			
			// add select2
			this.$input.select2( args );
			
			
			var _this = this;
			
			// sortable?
			if( this.o.sortable )
			{
			
				this.$input.select2("container").find("ul.select2-choices").sortable({
					 containment: 'parent',
					 start: function() {
					 	_this.$input.select2("onSortStart");
					 },
					 update: function() {
					 	_this.$input.select2("onSortEnd")
					 }
				});
			}
			
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
		
		acf.get_fields({ type : 'post_object'}, $el).each(function(){
			
			acf.fields.post_object.set({ $el : $(this) }).init();
			
		});
		
	});
	

})(jQuery);
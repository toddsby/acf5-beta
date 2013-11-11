(function($){
	
	
	/*
	*  Repeater
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	18/08/13
	*
	*/
	
	acf.fields.repeater = {
		
		$field : null,
		$el : null,
				
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// get options
			this.o = acf.helpers.get_atts( this.$el );
			
			
			// field
			this.$field = this.$el.closest('.acf-field');
			
			
			// add row_count
			this.o.row_count = this.$el.find('> .acf-table > tbody > tr').length - 1; // remove the acfcloneindex
			
			
			// return this for chaining
			return this;
			
		},
		init : function(){
			
			// reference
			var _this = this,
				$el = this.$el;
			
			
			// sortable
			if( this.o.max_rows > 1 )
			{
				this.$el.find('> .acf-table > tbody').unbind('sortable').sortable({
				
					items					: '> tr',
					handle					: '> td.order',
					//helper					: acf.helpers.sortable,
					forceHelperSize			: true,
					forcePlaceholderSize	: true,
					scroll					: true,
					
					start : function (event, ui) {
						
						acf.trigger('sortstart', [ui.item]);
						
						
						// add markup to the placeholder
		        		ui.placeholder.html('<td colspan="' + ui.item.children('td').length + '"></td>');
		        		
		   			},
		   			
		   			stop : function (event, ui) {
					
						acf.trigger('sortstop', [ui.item]);
						
						
						// render
						_this.set({ $el : $el }).render();
						
		   			}
				});
			}
						
			
			// render
			this.render();
					
		},
		render : function(){
			
			// update row_count ( already done in set() )
			//this.o.row_count = this.$el.find('> table > tbody > tr.row').length;
			
			
			// update order numbers
			this.$el.find('> .acf-table > tbody > tr').each(function( i ){
			
				$(this).children('td.order').html( i + 1 );
				
			});
			
			
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
			
			
			// validate
			if( this.o.row_count >= this.o.max_rows )
			{
				alert( acf.l10n.repeater.max.replace('{max}', this.o.max_rows) );
				return false;
			}
			
		
			// create and add the new field
			var new_id = acf.helpers.uniqid(),
				$tr = this.$el.find('> table > tbody > tr[data-id="acfcloneindex"]').clone();
				
				
			// modify $tr
			$tr.attr('data-id', new_id);
			$tr.html( $tr.html().replace(/(=["]*[\w-\[\]]*?)(acfcloneindex)/g, '$1' + new_id) );
			
			
			// add row
			if( ! $before )
			{
				$before = this.$el.find('> table > tbody > tr[data-id="acfcloneindex"]');
			}
			
			$before.before( $tr );
			
			
			// trigger mouseenter on parent repeater to work out css margin on add-row button
			this.$el.closest('tr').trigger('mouseenter');
			
			
			// update order
			this.render();
			
			
			// setup fields
			acf.trigger('append', [ $tr ]);
	
			
			// validation
			acf.validation.remove_error( this.$field );
			
		},
		remove : function( $tr ){
			
			// refernce
			var _this = this;
			
			
			// validate
			if( this.o.row_count <= this.o.min_rows )
			{
				alert( acf.l10n.repeater.min.replace('{min}', this.o.min_rows) );
				return false;
			}
			
			
			// set layout
			acf.helpers.remove_tr( $tr, function(){
				
				
				// trigger mouseenter on parent repeater to work out css margin on add-row button
				$tr.closest('.acf-row').trigger('mouseenter');
				
				
				// render
				_this.render();
				
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
	
	acf.on('ready append', function(e, el){
		
		$(el).find('.acf-repeater').each(function(){
			
			acf.fields.repeater.set({ $el : $(this) }).init();
			
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
		
		// before
		var before = false;
		
		if( $(this).attr('data-before') )
		{
			before = $(this).closest('.acf-row');
		}
		
		
		acf.fields.repeater.set({ $el : $(this).closest('.acf-repeater') }).add( before );
		
		
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-repeater .acf-repeater-remove-row', function( e ){
		
		e.preventDefault();
		
		acf.fields.repeater.set({ $el : $(this).closest('.acf-repeater') }).remove( $(this).closest('.acf-row') );
		
		$(this).blur();
		
	});
	
	$(document).on('mouseenter', '.acf-repeater tr.row', function( e ){
		
		// vars
		var $el = $(this).find('> td.remove > a.acf-repeater-add-row'),
			margin = ( $el.parent().height() / 2 ) + 9; // 9 = padding + border
		
		
		// css
		$el.css('margin-top', '-' + margin + 'px' );
		
	});
	
	

})(jQuery);
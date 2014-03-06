<?php

class acf_field_google_map extends acf_field
{
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct()
	{
		// vars
		$this->name = 'google_map';
		$this->label = __("Google Map",'acf');
		$this->category = 'jquery';
		$this->defaults = array(
			'height'		=> '',
			'center_lat'	=> '',
			'center_lng'	=> '',
			'zoom'			=> ''
		);
		$this->default_values = array(
			'height'		=> '400',
			'center_lat'	=> '-37.81411',
			'center_lng'	=> '144.96328',
			'zoom'			=> '14'
		);
		$this->l10n = array(
			'locating'			=>	__("Locating",'acf'),
			'browser_support'	=>	__("Sorry, this browser does not support geolocation",'acf'),
		);
		
		
		// do not delete!
    	parent::__construct();
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field )
	{
		// require the googlemaps JS ( this script is now lazy loaded via JS )
		//wp_enqueue_script('acf-googlemaps');
		
		
		// value
		$field['value'] = acf_parse_args($field['value'], array(
			'address'	=> '',
			'lat'		=> '',
			'lng'		=> ''
		));
		
		
		// default options
		foreach( $this->default_values as $k => $v )
		{
			if( empty($field[ $k ]) )
			{
				$field[ $k ] = $v;
			}	
		}
		
		
		// vars
		$atts = array(
			'id'			=> $field['id'],
			'class'			=> $field['class'],
			'data-id'		=> $field['id'], 
			'data-lat'		=> $field['center_lat'],
			'data-lng'		=> $field['center_lng'],
			'data-zoom'		=> $field['zoom']
		);
		
		
		// modify atts
		$atts['class'] .= ' acf-google-map';
		
		if( $field['value']['address'] )
		{
			$atts['class'] .= ' active';
		}
		
		
		?>
		<div <?php acf_esc_attr_e($atts); ?>>
			
			<div class="acf-hidden">
				<?php foreach( $field['value'] as $k => $v ): ?>
					<input type="hidden" class="input-<?php echo $k; ?>" name="<?php echo esc_attr($field['name']); ?>[<?php echo $k; ?>]" value="<?php echo esc_attr( $v ); ?>" />
				<?php endforeach; ?>
			</div>
			
			<div class="title">
				
				<div class="has-value">
					<a href="#" class="acf-sprite-delete" title="Clear location"></a>
					<h4><?php echo $field['value']['address']; ?></h4>
				</div>
				
				<div class="no-value">
					<a href="#" class="acf-sprite-locate" title="Find current location"></a>
					<input type="text" placeholder="Search for address..." class="search" />
				</div>
				
			</div>
			
			<div class="canvas" style="height: <?php echo $field['height']; ?>px">
				
			</div>
			
		</div>
		<?php
	}
	
	
	
	/*
	*  render_field_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_options( $field ) {
		
		
		// center_lat
		acf_render_field_option( $this->name, array(
			'label'			=> __('Center','acf'),
			'instructions'	=> __('Center the initial map','acf'),
			'type'			=> 'text',
			'name'			=> 'center_lat',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['center_lat'],
			'prepend'		=> 'lat',
			'placeholder'	=> $this->default_values['center_lat']
		));
		
		
		// center_lng
		acf_render_field_option( $this->name, array(
			'label'			=> __('Center','acf'),
			'instructions'	=> __('Center the initial map','acf'),
			'type'			=> 'text',
			'name'			=> 'center_lng',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['center_lng'],
			'prepend'		=> 'lng',
			'placeholder'	=> $this->default_values['center_lng']
		));
		
		
		// zoom
		acf_render_field_option( $this->name, array(
			'label'			=> __('Zoom','acf'),
			'instructions'	=> __('Set the initial zoom level','acf'),
			'type'			=> 'text',
			'name'			=> 'zoom',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['zoom'],
			'placeholder'	=> $this->default_values['zoom']
		));
		
		
		// allow_null
		acf_render_field_option( $this->name, array(
			'label'			=> __('Height','acf'),
			'instructions'	=> __('Customise the map height','acf'),
			'type'			=> 'text',
			'name'			=> 'height',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['height'],
			'append'		=> 'px',
			'placeholder'	=> $this->default_values['height']
		));
		
	}
}

new acf_field_google_map();

?>
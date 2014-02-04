<?php

class acf_field_date_picker extends acf_field
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
		$this->name = 'date_picker';
		$this->label = __("Date Picker",'acf');
		$this->category = __("jQuery",'acf');
		$this->defaults = array(
			'date_format'		=> 'Ymd',
			'display_format'	=> 'd/m/Y',
			'return_format'		=> 'Ymd',
			'first_day'			=> 1
		);
		
		
		// actions
		add_action('init', array($this, 'init'));
		
		
		// do not delete!
    	parent::__construct();
	}
	
	
	/*
	*  init
	*
	*  This function is run on the 'init' action to set the field's $l10n data. Before the init action, 
	*  access to the $wp_locale variable is not possible.
	*
	*  @type	action (init)
	*  @date	3/09/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function init()
	{
		global $wp_locale;
		
		$this->l10n = array(
			'closeText'         => __( 'Done', 'acf' ),
	        'currentText'       => __( 'Today', 'acf' ),
	        'monthNames'        => array_values( $wp_locale->month ),
	        'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
	        'monthStatus'       => __( 'Show a different month', 'acf' ),
	        'dayNames'          => array_values( $wp_locale->weekday ),
	        'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
	        'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
	        'isRTL'             => isset($wp_locale->is_rtl) ? $wp_locale->is_rtl : false,
		);
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
	
	function render_field( $field ) {
		
		
		// default options
		foreach( $this->default_values as $k => $v )
		{
			if( empty($field[ $k ]) )
			{
				$field[ $k ] = $v;
			}	
		}
		
		
		// vars
		$e = '';
		$el_atts = array(
			'class'					=> 'acf-date_picker',
			'data-save_format'		=> $field['date_format'],
			'data-display_format'	=> $field['display_format'],
			'data-first_day'		=> $field['first_day'],
		);
		$input_atts = array(
			'id'					=> $field['id'],
			'class' 				=> 'input-alt',
			'type'					=> 'hidden',
			'name'					=> $field['name'],
			'value'					=> $field['value'],
		);
		

		// html
		$e .= '<div ' . acf_esc_attr($el_atts) . '>';
			$e .= '<input ' . acf_esc_attr($input_atts). '/>';
			$e .= '<input type="text" value="" class="input" />';
		$e .= '</div>';
		
		
		// return
		echo $e;
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
	
	function render_field_options( $field )
	{
		// global
		global $wp_locale;
		
		
		// display_format
		acf_render_field_option( $this->name, array(
			'label'			=> __('Display format','acf'),
			'instructions'	=> __('The format displayed when editing a post','acf'),
			'type'			=> 'radio',
			'name'			=> 'display_format',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['display_format'],
			'other_choice'	=> 1,
			'choices'		=> array(
				'd/m/Y'			=> date('d/m/Y'),
				'm/d/Y'			=> date('m/d/Y'),
				'F j, Y'		=> date('F j, Y'),
			)
		));
				
		
		// return_format
		acf_render_field_option( $this->name, array(
			'label'			=> __('Return format','acf'),
			'instructions'	=> __('The format returned via template functions','acf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['return_format'],
			'other_choice'	=> 1,
			'choices'		=> array(
				'd/m/Y'			=> date('d/m/Y'),
				'm/d/Y'			=> date('m/d/Y'),
				'F j, Y'		=> date('F j, Y'),
				'Ymd'			=> date('Ymd'),
			)
		));
		
		
		// first_day
		acf_render_field_option( $this->name, array(
			'label'			=> __('Week Starts On','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'first_day',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['first_day'],
			'choices'		=> array_values( $wp_locale->weekday )
		));
		
		
	}
	
}

new acf_field_date_picker();

?>
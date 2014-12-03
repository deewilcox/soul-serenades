<?php
/* Security: Disables direct access to theme files */
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/**
 * TM EPO Builder
 */
class tm_epo_builder {
	var $version        = '2.0.0';
	var $plugin_path;
	var $template_path;
	var $plugin_url;

	// element options
	var $elements_array;

	// sections options
	var $_section_elements;

	// sizes display
	var $sizer;

	// HTML helper
	var $html;

	// WooCommerce Subscriptions check
	var $woo_subscriptions_check=false;

	function __construct() {
		$this->plugin_path      = untrailingslashit( plugin_dir_path(  dirname( __FILE__ )  ) );
		$this->template_path    = $this->plugin_path.'/templates/';
		$this->plugin_url       = untrailingslashit( plugins_url( '/', dirname( __FILE__ ) ) );
		$this->woo_subscriptions_check=tm_woocommerce_subscriptions_check();

		// init HTML helper class
		$this->html    = new TM_EPO_HTML();

		$this->sizer=array(
			"w25"  => "1/4",
			"w33"  => "1/3",
			"w50"  => "1/2",
			"w66"  => "2/3",
			"w75"  => "3/4",
			"w100" => "1/1"
		);
		$this->_section_elements=array_merge( 
			$this->_prepend_div( "","tm-tabs" ),

			$this->_prepend_div( "section","tm-tab-headers" ),
			$this->_prepend_tab( "section0", __( "Title options", TM_EPO_TRANSLATION ),"" ),
			$this->_prepend_tab( "section1", __( "General options", TM_EPO_TRANSLATION ),"open" ),
			$this->_prepend_tab( "section2", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
			$this->_append_div( "section" ),
			
			$this->_prepend_div( "section0" ),
				$this->_get_header_array( "section"."_header" ),
				$this->_get_divider_array( "section"."_divider", 0 ),
				$this->_append_div( "section0" ),

			$this->_prepend_div( "section1" ),

			array(
				"sectionnum"=>array(
					"id"   		=> "sections",
					"default" 	=> 0,
					"nodiv"  	=> 1,
					"type"  	=> "hidden",
					"tags"  	=> array( "class"=>"tm_builder_sections", "name"=>"tm_meta[tmfbuilder][sections][]", "value"=>0 ),
					"label"  	=> "",
					"desc"   	=> ""
				),
				"sectionsize"=>array(
					"id"   		=> "sections_size",
					"default" 	=> "w100",
					"nodiv"  	=> 1,
					"type"  	=> "hidden",
					"tags"  	=> array( "class"=>"tm_builder_sections_size", "name"=>"tm_meta[tmfbuilder][sections_size][]", "value"=>"w100" ),
					"label"  	=> "",
					"desc"   	=> ""
				),
				"sectionuniqid"=>array(
					"id"   		=> "sections_uniqid",
					"default" 	=> "",
					"nodiv"  	=> 1,
					"type"  	=> "hidden",
					"tags"  	=> array( "class"=>"tm-builder-sections-uniqid", "name"=>"tm_meta[tmfbuilder][sections_uniqid][]", "value"=>"" ),
					"label"  	=> "",
					"desc"   	=> ""
				),
				"sectionstyle"=>array(
					"id"   		=> "sections_style",
					"default" 	=> "",
					"type"  	=> "select",
					"tags"  	=> array( "id"=>"tm_sections_style", "name"=>"tm_meta[tmfbuilder][sections_style][]" ),
					"options" 	=> array(
						array( "text" => __( "Normal (clear)", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text" => __( "Box", TM_EPO_TRANSLATION ), "value"=>"box" ),
						array( "text" => __( "Expand and Collapse (start opened)", TM_EPO_TRANSLATION ), "value"=>"collapse" ),
						array( "text" => __( "Expand and Collapse (start closed)", TM_EPO_TRANSLATION ), "value"=>"collapseclosed" ),
						array( "text" => __( "Accordion", TM_EPO_TRANSLATION ), "value"=>"accordion" )
					),
					"label"		=> __( "Section style", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Select this section's display style.", TM_EPO_TRANSLATION )
				),
				"sectionplacement"=>array(
					"id"   		=> "sections_placement",
					"default" 	=> "before",
					"type"  	=> "select",
					"tags"  	=> array( "id"=>"sections_placement", "name"=>"tm_meta[tmfbuilder][sections_placement][]" ),
					"options" 	=> array(
						array( "text" => __( "Before Local Options", TM_EPO_TRANSLATION ), "value"=>"before" ),
						array( "text" => __( "After Local Options", TM_EPO_TRANSLATION ), "value"=>"after" )
					),
					"label"		=> __( "Section placement", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Select where this section will appear compare to local Options.", TM_EPO_TRANSLATION )
				),
				"sectiontype"=>array(
					"id"   		=> "sections_type",
					"default" 	=> "",
					"type"  	=> "select",
					"tags"  	=> array( "id"=>"sections_type", "name"=>"tm_meta[tmfbuilder][sections_type][]" ),
					"options" 	=> array(
						array( "text" => __( "Normal", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text" => __( "Pop up", TM_EPO_TRANSLATION ), "value"=>"popup" )
					),
					"label"		=> __( "Section type", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Select this section's display type.", TM_EPO_TRANSLATION )
				),

				"sectionsclass"=>array(
					"id" 		=> "sections_class",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"t", "id"=>"sections_class", "name"=>"tm_meta[tmfbuilder][sections_class][]", "value"=>"" ),
					"label"		=> __( 'Section class name', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter an extra class name to add to this section', TM_EPO_TRANSLATION )
				)			
			),
				
			$this->_append_div( "section1" ),
				
			$this->_prepend_div( "section2" ),
			array(
				"sectionclogic"=>array(
					"id"   		=> "sections_clogic",
					"default" 	=> "",
					"nodiv"  	=> 1,
					"type"  	=> "hidden",
					"tags"  	=> array( "class"=>"tm-builder-clogic", "name"=>"tm_meta[tmfbuilder][sections_clogic][]", "value"=>"" ),
					"label"  	=> "",
					"desc"   	=> ""
				),
				"sectionlogic"=>array(
					"id"   		=> "sections_logic",
					"default" 	=> "",
					"type"  	=> "select",
					"tags"  	=> array( "class"=>"activate-sections-logic", "id"=>"sections_logic", "name"=>"tm_meta[tmfbuilder][sections_logic][]" ),
					"options" 	=> array(
						array( "text" => __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text" => __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
					),
					"extra"		=> $this->builder_showlogic(),
					"label"		=> __( "Section Conditional Logic", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Enable conditional logic for showing or hiding this section.", TM_EPO_TRANSLATION )
				)
			),
			$this->_append_div( "section2" ),

			$this->_append_div( "" )	
		);

		$this->elements_array=array(
			"divider"=>array_merge( 
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "divider","tm-tab-headers" ),
				$this->_prepend_tab( "divider2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "divider3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),
				$this->_prepend_tab( "divider4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "divider" ),
				
				$this->_prepend_div( "divider2" ),
				$this->_get_divider_array() ,

				$this->_append_div( "divider2" ),
				
				$this->_prepend_div( "divider3" ),
				$this->_prepend_logic( "divider" ), 
				$this->_append_div( "divider3" ),

				$this->_prepend_div( "divider4" ),
				array(
					array(
						"id" 		=> "divider_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_divider_class", "name"=>"tm_meta[tmfbuilder][divider_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "divider4" ),

				$this->_append_div( "" )				
			),
			
			"header"=>array_merge(
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "header","tm-tab-headers" ),
				$this->_prepend_tab( "header2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "header3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
				$this->_prepend_tab( "header4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "header" ),
				
				$this->_prepend_div( "header2" ),	
				array(
				array(
					"id" 		=> "header_size",
					"default"	=> "3",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_header_size", "name"=>"tm_meta[tmfbuilder][header_size][]" ),
					"options"	=> array(
						array( "text"=> __( "H1", TM_EPO_TRANSLATION ), "value"=>"1" ),
						array( "text"=> __( "H2", TM_EPO_TRANSLATION ), "value"=>"2" ),
						array( "text"=> __( "H3", TM_EPO_TRANSLATION ), "value"=>"3" ),
						array( "text"=> __( "H4", TM_EPO_TRANSLATION ), "value"=>"4" ),
						array( "text"=> __( "H5", TM_EPO_TRANSLATION ), "value"=>"5" ),
						array( "text"=> __( "H6", TM_EPO_TRANSLATION ), "value"=>"6" ),
						array( "text"=> __( "p", TM_EPO_TRANSLATION ), "value"=>"7" ),
						array( "text"=> __( "div", TM_EPO_TRANSLATION ), "value"=>"8" ),
						array( "text"=> __( "span", TM_EPO_TRANSLATION ), "value"=>"9" )
					),
					"label"		=> __( "Header size", TM_EPO_TRANSLATION ),
					"desc" 		=> ""
				),
				array(
					"id" 		=> "header_title",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"t tm-header-title", "id"=>"builder_header_title", "name"=>"tm_meta[tmfbuilder][header_title][]", "value"=>"" ),
					"label"		=> __( 'Header title', TM_EPO_TRANSLATION ),
					"desc" 		=> ""
				),
				array(
					"id" 		=> "header_title_color",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"tm-color-picker", "id"=>"builder_header_title_color", "name"=>"tm_meta[tmfbuilder][header_title_color][]", "value"=>"" ),
					"label"		=> __( 'Header color', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Leave empty for default value', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "header_subtitle",
					"default"	=> "",
					"type"		=> "textarea",
					"tags"		=> array( "id"=>"builder_header_subtitle", "name"=>"tm_meta[tmfbuilder][header_subtitle][]" ),
					"label"		=> __( "Subtitle", TM_EPO_TRANSLATION ),
					"desc" 		=> ""
				),
				array(
					"id" 		=> "header_subtitle_color",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"tm-color-picker", "id"=>"builder_header_subtitle_color", "name"=>"tm_meta[tmfbuilder][header_subtitle_color][]", "value"=>"" ),
					"label"		=> __( 'Subtitle color', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Leave empty for default value', TM_EPO_TRANSLATION )
				)				
				),

				$this->_append_div( "header2" ),
				
				$this->_prepend_div( "header3" ),
				$this->_prepend_logic( "header" ), 
				$this->_append_div( "header3" ),

				$this->_prepend_div( "header4" ),
				array(
					array(
						"id" 		=> "header_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_header_class", "name"=>"tm_meta[tmfbuilder][header_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "header4" ),

				$this->_append_div( "" )				
			),
			
			"textarea"=>array_merge( 
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "textarea","tm-tab-headers" ),
				$this->_prepend_tab( "textarea1", __( "Label options", TM_EPO_TRANSLATION ) ),
				$this->_prepend_tab( "textarea2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "textarea3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
				$this->_prepend_tab( "textarea4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "textarea" ),

				$this->_prepend_div( "textarea1" ),
				$this->_get_header_array( "textarea"."_header" ),
				$this->_get_divider_array( "textarea"."_divider", 0 ),
				$this->_append_div( "textarea1" ),
				
				$this->_prepend_div( "textarea2" ),	
				array(
				array(
					"id" 		=> "textarea_required",
					"default"	=> "0",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_textarea_required", "name"=>"tm_meta[tmfbuilder][textarea_required][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"0" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
					),
					"label"		=> __( "Required", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether the user must fill out this field or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textarea_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_textarea_price", "name"=>"tm_meta[tmfbuilder][textarea_price][]", "value"=>"" ),
					"label"		=> __( 'Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter the price for this field or leave it blank for no price.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textarea_text_after_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_textarea_text_after_price", "name"=>"tm_meta[tmfbuilder][textarea_text_after_price][]", "value"=>"" ),
					"label"		=> __( 'Text after Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a text to display after the price for this field or leave it blank for no text.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textarea_price_type",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_textarea_price_type", "name"=>"tm_meta[tmfbuilder][textarea_price_type][]" ),
					"options"	=> array(
						array( "text"=> __( "Fixed amount", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Percent of the original price", TM_EPO_TRANSLATION ), "value"=>"percent" ),
						array( "text"=> __( "Percent of the original price + options", TM_EPO_TRANSLATION ), "value"=>"percentcurrenttotal" ),
						array( "text"=> __( "Price per char", TM_EPO_TRANSLATION ), "value"=>"char" ),
						array( "text"=> __( "Fee", TM_EPO_TRANSLATION ), "value"=>"fee" ),
					),
					"label"		=> __( "Price type", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textarea_hide_amount",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_textarea_hide_amount", "name"=>"tm_meta[tmfbuilder][textarea_hide_amount][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"hidden" )
					),
					"label"		=> __( "Hide price", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to hide the price or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textarea_placeholder",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"t", "id"=>"builder_textarea_placeholder", "name"=>"tm_meta[tmfbuilder][textarea_placeholder][]", "value"=>"" ),
					"label"		=> __( 'Placeholder', TM_EPO_TRANSLATION ),
					"desc" 		=> ""
				),
				array(
					"id" 		=> "textarea_max_chars",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_textarea_max_chars", "name"=>"tm_meta[tmfbuilder][textarea_max_chars][]", "value"=>"" ),
					"label"		=> __( 'Maximum characters', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a value to limit the maximum characters the user can enter.', TM_EPO_TRANSLATION )
				)
				),

				$this->_append_div( "textarea" ),
				
				$this->_prepend_div( "textarea3" ),
				$this->_prepend_logic( "textarea" ), 
				$this->_append_div( "textarea3" ),

				$this->_prepend_div( "textarea4" ),
				array(
					array(
						"id" 		=> "textarea_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_textarea_class", "name"=>"tm_meta[tmfbuilder][textarea_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "textarea4" ),

				$this->_append_div( "" )
			),
			
			"textfield"=>array_merge( 
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "textfield","tm-tab-headers" ),
				$this->_prepend_tab( "textfield1", __( "Label options", TM_EPO_TRANSLATION ) ),
				$this->_prepend_tab( "textfield2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "textfield3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
				$this->_prepend_tab( "textfield4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "textfield" ),

				$this->_prepend_div( "textfield1" ),
				$this->_get_header_array( "textfield"."_header" ),
				$this->_get_divider_array( "textfield"."_divider", 0 ),
				$this->_append_div( "textfield1" ),
				
				$this->_prepend_div( "textfield2" ),	
				array(
				array(
					"id" 		=> "textfield_required",
					"default"	=> "0",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_textfield_required", "name"=>"tm_meta[tmfbuilder][textfield_required][]" ),
					"options"		=>array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"0" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
					),
					"label"		=> __( "Required", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether the user must fill out this field or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textfield_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_textfield_price", "name"=>"tm_meta[tmfbuilder][textfield_price][]", "value"=>"" ),
					"label"		=> __( 'Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter the price for this field or leave it blank for no price.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textfield_text_after_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_textfield_text_after_price", "name"=>"tm_meta[tmfbuilder][textfield_text_after_price][]", "value"=>"" ),
					"label"		=> __( 'Text after Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a text to display after the price for this field or leave it blank for no text.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textfield_price_type",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_textfield_price_type", "name"=>"tm_meta[tmfbuilder][textfield_price_type][]" ),
					"options"	=> array(
						array( "text"=> __( "Fixed amount", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Percent of the original price", TM_EPO_TRANSLATION ), "value"=>"percent" ),
						array( "text"=> __( "Percent of the original price + options", TM_EPO_TRANSLATION ), "value"=>"percentcurrenttotal" ),
						array( "text"=> __( "Price per char", TM_EPO_TRANSLATION ), "value"=>"char" ),
						array( "text"=> __( "Fee", TM_EPO_TRANSLATION ), "value"=>"fee" ),						
					),
					"label"		=> __( "Price type", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textfield_hide_amount",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_textfield_hide_amount", "name"=>"tm_meta[tmfbuilder][textfield_hide_amount][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"hidden" )
					),
					"label"		=> __( "Hide price", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to hide the price or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "textfield_placeholder",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"t", "id"=>"builder_textfield_placeholder", "name"=>"tm_meta[tmfbuilder][textfield_placeholder][]", "value"=>"" ),
					"label"		=> __( 'Placeholder', TM_EPO_TRANSLATION ),
					"desc" 		=> ""
				),
				array(
					"id" 		=> "textfield_max_chars",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_textfield_max_chars", "name"=>"tm_meta[tmfbuilder][textfield_max_chars][]", "value"=>"" ),
					"label"		=> __( 'Maximum characters', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a value for to limit the maximum characters the user can enter.', TM_EPO_TRANSLATION )
				)
				),

				$this->_append_div( "textfield2" ),
				
				$this->_prepend_div( "textfield3" ),
				$this->_prepend_logic( "textfield" ), 
				$this->_append_div( "textfield3" ),

				$this->_prepend_div( "textfield4" ),
				array(
					array(
						"id" 		=> "textfield_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_textfield_class", "name"=>"tm_meta[tmfbuilder][textfield_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "textfield4" ),

				$this->_append_div( "" )
			),
			
			"selectbox"=>array_merge( 
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "selectbox","tm-tab-headers" ),
				$this->_prepend_tab( "selectbox1", __( "Label options", TM_EPO_TRANSLATION ) ),
				$this->_prepend_tab( "selectbox2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "selectbox3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
				$this->_prepend_tab( "selectbox4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "selectbox" ),

				$this->_prepend_div( "selectbox1" ),
				$this->_get_header_array( "selectbox"."_header" ),
				$this->_get_divider_array( "selectbox"."_divider", 0 ),
				$this->_append_div( "selectbox1" ),
				
				$this->_prepend_div( "selectbox2" ),	
				array(
				array(
					"id" 		=> "selectbox_required",
					"default"	=> "0",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_selectbox_required", "name"=>"tm_meta[tmfbuilder][selectbox_required][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"0" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
					),
					"label"		=> __( "Required", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether the user must fill out this field or not.", TM_EPO_TRANSLATION )
				),
				($this->woo_subscriptions_check)?
				array(
					"id" 		=> "selectbox_price_type",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"tm_selectbox_fee", "id"=>"builder_selectbox_price_type", "name"=>"tm_meta[tmfbuilder][selectbox_price_type][]" ),
					"options"	=> array(
						array( "text"=> __( "Use options", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Subscription fee", TM_EPO_TRANSLATION ), "value"=>"subscriptionfee" ),
					),
					"label"		=> __( "Price type", TM_EPO_TRANSLATION )
				)
				:
				array(
					"id" 		=> "selectbox_price_type",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"tm_selectbox_fee", "id"=>"builder_selectbox_price_type", "name"=>"tm_meta[tmfbuilder][selectbox_price_type][]" ),
					"options"	=> array(
						array( "text"=> __( "Use options", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Fee", TM_EPO_TRANSLATION ), "value"=>"fee" ),
					),
					"label"		=> __( "Price type", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "selectbox_text_after_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_selectbox_text_after_price", "name"=>"tm_meta[tmfbuilder][selectbox_text_after_price][]", "value"=>"" ),
					"label"		=> __( 'Text after Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a text to display after the price for this field or leave it blank for no text.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "selectbox_hide_amount",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_selectbox_hide_amount", "name"=>"tm_meta[tmfbuilder][selectbox_hide_amount][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"hidden" )
					),
					"label"		=> __( "Hide price", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to hide the price or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "selectbox_use_url",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "class"=>"use_url", "id"=>"builder_selectbox_use_url", "name"=>"tm_meta[tmfbuilder][selectbox_use_url][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"url" )
					),
					"label"		=> __( "Use URL replacements", TM_EPO_TRANSLATION ),
					"desc" 		=> "Choose whether to redirect to a URL if the option is click."
				),
				array(
					"id" 		=> "selectbox_placeholder",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"t", "id"=>"builder_selectbox_placeholder", "name"=>"tm_meta[tmfbuilder][selectbox_placeholder][]", "value"=>"" ),
					"label"		=> __( 'Placeholder', TM_EPO_TRANSLATION ),
					"desc" 		=> ""
				),
				array(
					"id" 		=> "selectbox_options",
					"default" 	=> "",
					"type"		=> "custom",
					"leftclass" => "onerow",
					"rightclass"=> "onerow",
					"html"		=> $this->builder_sub_options( array(), 'multiple_selectbox_options' ),
					"label"		=> __( "Select box options", TM_EPO_TRANSLATION ),
					"desc" 		=> __( "Double click the radio button to remove its selected attribute.", TM_EPO_TRANSLATION )
				)
				),
				$this->_append_div( "selectbox2" ),
				
				$this->_prepend_div( "selectbox3" ),
				$this->_prepend_logic( "selectbox" ), 
				$this->_append_div( "selectbox3" ),

				$this->_prepend_div( "selectbox4" ),
				array(
					array(
						"id" 		=> "selectbox_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_selectbox_class", "name"=>"tm_meta[tmfbuilder][selectbox_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "selectbox4" ),


				$this->_append_div( "" )				
			),
			
			"radiobuttons"=>array_merge( 
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "radiobuttons","tm-tab-headers" ),
				$this->_prepend_tab( "radiobuttons1", __( "Label options", TM_EPO_TRANSLATION ) ),
				$this->_prepend_tab( "radiobuttons2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "radiobuttons3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
				$this->_prepend_tab( "radiobuttons4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "radiobuttons" ),

				$this->_prepend_div( "radiobuttons1" ),
				$this->_get_header_array( "radiobuttons"."_header" ),
				$this->_get_divider_array( "radiobuttons"."_divider", 0 ),
				$this->_append_div( "radiobuttons1" ),
				
				$this->_prepend_div( "radiobuttons2" ),

				array(
				array(
					"id" 		=> "radiobuttons_required",
					"default"	=> "0",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_radiobuttons_required", "name"=>"tm_meta[tmfbuilder][radiobuttons_required][]" ),
					"options"=>array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"0" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
					),
					"label"		=> __( "Required", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether the user must fill out this field or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "radiobuttons_text_after_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_radiobuttons_text_after_price", "name"=>"tm_meta[tmfbuilder][radiobuttons_text_after_price][]", "value"=>"" ),
					"label"		=> __( 'Text after Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a text to display after the price for this field or leave it blank for no text.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "radiobuttons_hide_amount",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_radiobuttons_hide_amount", "name"=>"tm_meta[tmfbuilder][radiobuttons_hide_amount][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"hidden" )
					),
					"label"		=> __( "Hide price", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to hide the price or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "radiobuttons_use_url",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "class"=>"use_url", "id"=>"builder_radiobuttons_use_url", "name"=>"tm_meta[tmfbuilder][radiobuttons_use_url][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"url" )
					),
					"label"		=> __( "Use URL replacements", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to redirect to a URL if the option is click.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "radiobuttons_use_images",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "class"=>"use_images", "id"=>"builder_radiobuttons_use_images", "name"=>"tm_meta[tmfbuilder][radiobuttons_use_images][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"images" )
					),
					"label"		=> __( "Use image replacements", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to use images in place of radio buttons.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "radiobuttons_changes_product_image",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "class"=>"use_images", "id"=>"builder_radiobuttons_changes_product_image", "name"=>"tm_meta[tmfbuilder][radiobuttons_changes_product_image][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"images" )
					),
					"label"		=> __( "Changes product image", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to change the product image if Use image replacements is enabled", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "radiobuttons_swatchmode",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "class"=>"swatchmode", "id"=>"builder_radiobuttons_swatchmode", "name"=>"tm_meta[tmfbuilder][radiobuttons_swatchmode][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"swatch" )
					),
					"label"		=> __( "Enable Swatch mode", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Swatch mode will show the option label on a tooltip when Use image replacements is active.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "radiobuttons_items_per_row",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_radiobuttons_items_per_row", "name"=>"tm_meta[tmfbuilder][radiobuttons_items_per_row][]" ),
					"label"		=> __( "Items per row", TM_EPO_TRANSLATION ),
					"desc" 		=> __( "Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "radiobuttons_options",
					"default" 	=> "",
					"type"		=> "custom",
					"leftclass" => "onerow",
					"rightclass"=> "onerow",
					"html"		=> $this->builder_sub_options( array(), 'multiple_radiobuttons_options' ),
					"label"		=> __( "Radio buttons options", TM_EPO_TRANSLATION ),
					"desc" 		=> __( "Double click the radio button to remove its selected attribute.", TM_EPO_TRANSLATION )
				)
				),
				$this->_append_div( "radiobuttons2" ),
				
				$this->_prepend_div( "radiobuttons3" ),
				$this->_prepend_logic( "radiobuttons" ), 
				$this->_append_div( "radiobuttons3" ),

				$this->_prepend_div( "radiobuttons4" ),
				array(
					array(
						"id" 		=> "radiobuttons_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_radiobuttons_class", "name"=>"tm_meta[tmfbuilder][radiobuttons_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "radiobuttons4" ),

				$this->_append_div( "" )
			),
			

			"checkboxes"=>array_merge( 
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "checkboxes","tm-tab-headers" ),
				$this->_prepend_tab( "checkboxes1", __( "Label options", TM_EPO_TRANSLATION ) ),
				$this->_prepend_tab( "checkboxes2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "checkboxes3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
				$this->_prepend_tab( "checkboxes4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "checkboxes" ),

				$this->_prepend_div( "checkboxes1" ),
				$this->_get_header_array( "checkboxes"."_header" ),
				$this->_get_divider_array( "checkboxes"."_divider", 0 ),
				$this->_append_div( "checkboxes1" ),
				
				$this->_prepend_div( "checkboxes2" ),				
				array(
				array(
					"id" 		=> "checkboxes_required",
					"default"	=> "0",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_checkboxes_required", "name"=>"tm_meta[tmfbuilder][checkboxes_required][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"0" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
					),
					"label"		=> __( "Required", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether the user must fill out this field or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "checkboxes_text_after_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_checkboxes_text_after_price", "name"=>"tm_meta[tmfbuilder][checkboxes_text_after_price][]", "value"=>"" ),
					"label"		=> __( 'Text after Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a text to display after the price for this field or leave it blank for no text.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "checkboxes_hide_amount",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_checkboxes_hide_amount", "name"=>"tm_meta[tmfbuilder][checkboxes_hide_amount][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"hidden" )
					),
					"label"		=> __( "Hide price", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to hide the price or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "checkboxes_limit_choices",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_checkboxes_limit_choices", "name"=>"tm_meta[tmfbuilder][checkboxes_limit_choices][]" ),
					"label"		=> __( "Limit selection", TM_EPO_TRANSLATION ),
					"desc" 		=> __( "Enter a number above 0 to limit the checkbox selection or leave blank for default behaviour.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "checkboxes_use_images",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "class"=>"use_images", "id"=>"builder_checkboxes_use_images", "name"=>"tm_meta[tmfbuilder][checkboxes_use_images][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"images" )
					),
					"label"		=> __( "Use image replacements", TM_EPO_TRANSLATION ),
					"desc" 		=> "Choose whether to use images in place of check boxes."
				),
				array(
					"id" 		=> "checkboxes_changes_product_image",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "class"=>"use_images", "id"=>"builder_checkboxes_changes_product_image", "name"=>"tm_meta[tmfbuilder][checkboxes_changes_product_image][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"images" )
					),
					"label"		=> __( "Changes product image", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to change the product image if Use image replacements is enabled", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "checkboxes_swatchmode",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "class"=>"swatchmode", "id"=>"builder_checkboxes_swatchmode", "name"=>"tm_meta[tmfbuilder][checkboxes_swatchmode][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"swatch" )
					),
					"label"		=> __( "Enable Swatch mode", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Swatch mode will show the option label on a tooltip when Use image replacements is active.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "checkboxes_items_per_row",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_checkboxes_items_per_row", "name"=>"tm_meta[tmfbuilder][checkboxes_items_per_row][]" ),
					"label"		=> __( "Items per row", TM_EPO_TRANSLATION ),
					"desc" 		=> __( "Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "checkboxes_options",
					"default" 	=> "",
					"type"		=> "custom",
					"leftclass" => "onerow",
					"rightclass"=> "onerow",
					"html"		=> $this->builder_sub_options( array(), 'multiple_checkboxes_options' ),
					"label"		=> __( "Checkbox options", TM_EPO_TRANSLATION ),
					"desc" 		=> ""
				)
				),
				$this->_append_div( "checkboxes2" ),
				
				$this->_prepend_div( "checkboxes3" ),
				$this->_prepend_logic( "checkboxes" ), 
				$this->_append_div( "checkboxes3" ),

				$this->_prepend_div( "checkboxes4" ),
				array(
					array(
						"id" 		=> "checkboxes_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_checkboxes_class", "name"=>"tm_meta[tmfbuilder][checkboxes_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "checkboxes4" ),

				$this->_append_div( "" )
			),

			"upload"=>array_merge( 
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "upload","tm-tab-headers" ),
				$this->_prepend_tab( "upload1", __( "Label options", TM_EPO_TRANSLATION ) ),
				$this->_prepend_tab( "upload2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "upload3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
				$this->_prepend_tab( "upload4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "upload" ),

				$this->_prepend_div( "upload1" ),
				$this->_get_header_array( "upload"."_header" ),
				$this->_get_divider_array( "upload"."_divider", 0 ),
				$this->_append_div( "upload1" ),
				
				$this->_prepend_div( "upload2" ),	
				array(
				array(
					"id" 		=> "upload_required",
					"default"	=> "0",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_upload_required", "name"=>"tm_meta[tmfbuilder][upload_required][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"0" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
					),
					"label"		=> __( "Required", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether the user must fill out this field or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "upload_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_upload_price", "name"=>"tm_meta[tmfbuilder][upload_price][]", "value"=>"" ),
					"label"		=> __( 'Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter the price for this field or leave it blank for no price.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "upload_text_after_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_upload_text_after_price", "name"=>"tm_meta[tmfbuilder][upload_text_after_price][]", "value"=>"" ),
					"label"		=> __( 'Text after Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a text to display after the price for this field or leave it blank for no text.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "upload_price_type",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_upload_price_type", "name"=>"tm_meta[tmfbuilder][upload_price_type][]" ),
					"options"	=> array(
						array( "text"=> __( "Fixed amount", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Percent of the original price", TM_EPO_TRANSLATION ), "value"=>"percent" ),
						array( "text"=> __( "Percent of the original price + options", TM_EPO_TRANSLATION ), "value"=>"percentcurrenttotal" )
					),
					"label"		=> __( "Price type", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "upload_hide_amount",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_upload_hide_amount", "name"=>"tm_meta[tmfbuilder][upload_hide_amount][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"hidden" )
					),
					"label"		=> __( "Hide price", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to hide the price or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "upload_button_type",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_upload_button_type", "name"=>"tm_meta[tmfbuilder][upload_button_type][]" ),
					"options"	=> array(
						array( "text"=> __( "Normal browser button", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Styled button", TM_EPO_TRANSLATION ), "value"=>"button" )
					),
					"label"		=> __( "Upload button style", TM_EPO_TRANSLATION )
				),
				),
				$this->_append_div( "upload2" ),
				
				$this->_prepend_div( "upload3" ),
				$this->_prepend_logic( "upload" ), 
				$this->_append_div( "upload3" ),

				$this->_prepend_div( "upload4" ),
				array(
					array(
						"id" 		=> "upload_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_upload_class", "name"=>"tm_meta[tmfbuilder][upload_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "upload4" ),

				$this->_append_div( "" )				
			),
			
			"date"=>array_merge( 
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "date","tm-tab-headers" ),
				$this->_prepend_tab( "date1", __( "Label options", TM_EPO_TRANSLATION ) ),
				$this->_prepend_tab( "date2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "date3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
				$this->_prepend_tab( "date4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "date" ),

				$this->_prepend_div( "date1" ),
				$this->_get_header_array( "date"."_header" ),
				$this->_get_divider_array( "date"."_divider", 0 ),
				$this->_append_div( "date1" ),
				
				$this->_prepend_div( "date2" ),	
				array(
				array(
					"id" 		=> "date_required",
					"default"	=> "0",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_date_required", "name"=>"tm_meta[tmfbuilder][date_required][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"0" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
					),
					"label"		=> __( "Required", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether the user must fill out this field or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "date_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_date_price", "name"=>"tm_meta[tmfbuilder][date_price][]", "value"=>"" ),
					"label"		=> __( 'Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter the price for this field or leave it blank for no price.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "date_text_after_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_date_text_after_price", "name"=>"tm_meta[tmfbuilder][date_text_after_price][]", "value"=>"" ),
					"label"		=> __( 'Text after Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a text to display after the price for this field or leave it blank for no text.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "date_price_type",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_date_price_type", "name"=>"tm_meta[tmfbuilder][date_price_type][]" ),
					"options"	=> array(
						array( "text"=> __( "Fixed amount", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Percent of the original price", TM_EPO_TRANSLATION ), "value"=>"percent" ),
						array( "text"=> __( "Percent of the original price + options", TM_EPO_TRANSLATION ), "value"=>"percentcurrenttotal" ),
						array( "text"=> __( "Fee", TM_EPO_TRANSLATION ), "value"=>"fee" ),
					),
					"label"		=> __( "Price type", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "date_hide_amount",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_date_hide_amount", "name"=>"tm_meta[tmfbuilder][date_hide_amount][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"hidden" )
					),
					"label"		=> __( "Hide price", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to hide the price or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "date_button_type",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_date_button_type", "name"=>"tm_meta[tmfbuilder][date_button_type][]" ),
					"options"	=> array(
						array( "text"=> __( "Date field", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Date picker", TM_EPO_TRANSLATION ), "value"=>"picker" ),
						array( "text"=> __( "Date field and picker", TM_EPO_TRANSLATION ), "value"=>"fieldpicker" ),
					),
					"label"		=> __( "Date picker style", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "date_format",
					"default"	=> "0",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_date_format", "name"=>"tm_meta[tmfbuilder][date_format][]" ),
					"options"	=> array(
						array( "text"=> __( "Day / Month / Year", TM_EPO_TRANSLATION ), "value"=>"0" ),
						array( "text"=> __( "Month / Date / Year", TM_EPO_TRANSLATION ), "value"=>"1" ),
						array( "text"=> __( "Day . Month . Year", TM_EPO_TRANSLATION ), "value"=>"2" ),
						array( "text"=> __( "Month . Date . Year", TM_EPO_TRANSLATION ), "value"=>"3" ),
						array( "text"=> __( "Day - Month - Year", TM_EPO_TRANSLATION ), "value"=>"4" ),
						array( "text"=> __( "Month - Date - Year", TM_EPO_TRANSLATION ), "value"=>"5" )
					),
					"label"		=> __( "Date format", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "date_start_year",
					"default"	=> "1900",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_date_start_year", "name"=>"tm_meta[tmfbuilder][date_start_year][]", "value"=>"" ),
					"label"		=> __( 'Start year', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter starting year.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "date_end_year",
					"default"	=> (date("Y")+10),
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_date_end_year", "name"=>"tm_meta[tmfbuilder][date_end_year][]", "value"=>"" ),
					"label"		=> __( 'End year', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter ending year.', TM_EPO_TRANSLATION )
				),

				array(
					"id" 			=> "date_tranlation_custom",
					"type"			=> "custom",
					"label"			=> __( 'Translations', TM_EPO_TRANSLATION ),
					"desc" 			=> "",
					"nowrap_end" 	=> 1,
					"noclear" 		=> 1
				),
				array(
					"id" 			=> "date_tranlation_day",
					"default"		=> "",
					"type"			=> "text",
					"tags"			=> array( "class"=>"n", "id"=>"builder_date_tranlation_day", "name"=>"tm_meta[tmfbuilder][date_tranlation_day][]", "value"=>"" ),
					"label"			=> "",
					"desc"			=> "",
					"prepend_element_html" => '<span class="prepend_span">'.__( 'Day', TM_EPO_TRANSLATION ).'</span> ',
					"nowrap_start" 	=> 1,
					"nowrap_end" 	=> 1
				),

				array(
					"id" 			=> "date_tranlation_month",
					"default"		=> "",
					"type"			=> "text",
					"nowrap_start" 	=> 1,
					"nowrap_end" 	=> 1,
					"tags"			=> array( "class"=>"n", "id"=>"builder_date_tranlation_month", "name"=>"tm_meta[tmfbuilder][date_tranlation_month][]", "value"=>"" ),
					"label"			=> "",
					"desc"			=> "",
					"prepend_element_html" => '<span class="prepend_span">'.__( 'Month', TM_EPO_TRANSLATION ).'</span> '
				),

				array(
					"id" 			=> "date_tranlation_year",
					"default"		=> "",
					"type"			=> "text",
					"tags"			=> array( "class"=>"n", "id"=>"builder_date_tranlation_year", "name"=>"tm_meta[tmfbuilder][date_tranlation_year][]", "value"=>"" ),
					"label"			=> "",
					"desc"			=> "",
					"prepend_element_html" => '<span class="prepend_span">'.__( 'Year', TM_EPO_TRANSLATION ).'</span> ',
					"nowrap_start" 	=> 1
				)
				
				

				),
				$this->_append_div( "date2" ),
				
				$this->_prepend_div( "date3" ),
				$this->_prepend_logic( "date" ), 
				$this->_append_div( "date3" ),

				$this->_prepend_div( "date4" ),
				array(
					array(
						"id" 		=> "date_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_date_class", "name"=>"tm_meta[tmfbuilder][date_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "date4" ),

				$this->_append_div( "" )				
			),
			"range"=>array_merge( 
				$this->_prepend_div( "","tm-tabs" ),

				$this->_prepend_div( "range","tm-tab-headers" ),
				$this->_prepend_tab( "range1", __( "Label options", TM_EPO_TRANSLATION ) ),
				$this->_prepend_tab( "range2", __( "General options", TM_EPO_TRANSLATION ),"open" ),
				$this->_prepend_tab( "range3", __( "Condition Logic", TM_EPO_TRANSLATION ) ),				
				$this->_prepend_tab( "range4", __( "CSS settings", TM_EPO_TRANSLATION ) ),			
				$this->_append_div( "range" ),

				$this->_prepend_div( "range1" ),
				$this->_get_header_array( "range"."_header" ),
				$this->_get_divider_array( "range"."_divider", 0 ),
				$this->_append_div( "range1" ),
				
				$this->_prepend_div( "range2" ),	
				array(
				array(
					"id" 		=> "range_required",
					"default"	=> "0",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_range_required", "name"=>"tm_meta[tmfbuilder][range_required][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"0" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
					),
					"label"		=> __( "Required", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether the user must fill out this field or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "range_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_range_price", "name"=>"tm_meta[tmfbuilder][range_price][]", "value"=>"" ),
					"label"		=> __( 'Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter the price for this field or leave it blank for no price.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "range_text_after_price",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_range_text_after_price", "name"=>"tm_meta[tmfbuilder][range_text_after_price][]", "value"=>"" ),
					"label"		=> __( 'Text after Price', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter a text to display after the price for this field or leave it blank for no text.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "range_price_type",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_range_price_type", "name"=>"tm_meta[tmfbuilder][range_price_type][]" ),
					"options"	=> array(
						array( "text"=> __( "Fixed amount", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Percent of the original price", TM_EPO_TRANSLATION ), "value"=>"percent" ),
						array( "text"=> __( "Percent of the original price + options", TM_EPO_TRANSLATION ), "value"=>"percentcurrenttotal" ),
						array( "text"=> __( "Fee", TM_EPO_TRANSLATION ), "value"=>"fee" ),
					),
					"label"		=> __( "Price type", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "range_hide_amount",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_range_hide_amount", "name"=>"tm_meta[tmfbuilder][range_hide_amount][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"hidden" )
					),
					"label"		=> __( "Hide price", TM_EPO_TRANSLATION ),
					"desc" 		=> __("Choose whether to hide the price or not.", TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "range_min",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_range_min", "name"=>"tm_meta[tmfbuilder][range_min][]", "value"=>"" ),
					"label"		=> __( 'Min value', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter the minimum value.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "range_max",
					"default"	=> "",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_range_max", "name"=>"tm_meta[tmfbuilder][range_max][]", "value"=>"" ),
					"label"		=> __( 'Max value', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter the maximum value.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "range_step",
					"default"	=> "1",
					"type"		=> "text",
					"tags"		=> array( "class"=>"n", "id"=>"builder_range_step", "name"=>"tm_meta[tmfbuilder][range_step][]", "value"=>"" ),
					"label"		=> __( 'Step value', TM_EPO_TRANSLATION ),
					"desc" 		=> __( 'Enter the step for the handle.', TM_EPO_TRANSLATION )
				),
				array(
					"id" 		=> "range_pips",
					"default"	=> "",
					"type"		=> "select",
					"tags"		=> array( "id"=>"builder_range_pips", "name"=>"tm_meta[tmfbuilder][range_pips][]" ),
					"options"	=> array(
						array( "text"=> __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Yes", TM_EPO_TRANSLATION ), "value"=>"yes" )
					),
					"label"		=> __( "Enable points display?", TM_EPO_TRANSLATION ),
					"desc" 		=> __( "This allows you to generate points along the range picker.", TM_EPO_TRANSLATION )
				),

				),
				$this->_append_div( "range2" ),
				
				$this->_prepend_div( "range3" ),
				$this->_prepend_logic( "range" ), 
				$this->_append_div( "range3" ),

				$this->_prepend_div( "range4" ),
				array(
					array(
						"id" 		=> "range_class",
						"default"	=> "",
						"type"		=> "text",
						"tags"		=> array( "class"=>"t", "id"=>"builder_range_class", "name"=>"tm_meta[tmfbuilder][range_class][]", "value"=>"" ),
						"label"		=> __( 'Element class name', TM_EPO_TRANSLATION ),
						"desc" 		=> __( 'Enter an extra class name to add to this element', TM_EPO_TRANSLATION )
					)
				),
				$this->_append_div( "range4" ),

				$this->_append_div( "" )				
			)
		);
		if ($this->woo_subscriptions_check){			
			$this->elements_array["textarea"][19]['options'][]=array( "text"=> __( "Subscription fee", TM_EPO_TRANSLATION ), "value"=>"subscriptionfee" );
			$this->elements_array["textfield"][19]['options'][]=array( "text"=> __( "Subscription fee", TM_EPO_TRANSLATION ), "value"=>"subscriptionfee" );
			$this->elements_array["date"][19]['options'][]=array( "text"=> __( "Subscription fee", TM_EPO_TRANSLATION ), "value"=>"subscriptionfee" );
		}
		
	}

	private function _prepend_tab( $id="",$label="" ,$closed="closed"){
		if (!empty($closed)){
			$closed=" ".$closed;
		}
		return array(array(
						"id" 		=> $id."_custom_tabstart",
						"default" 	=> "",
						"type"		=> "custom",
						"nodiv"		=> 1,
						"html"		=> "<div class='tm-box'>"										
										."<h4 data-id='".$id."-tab' class='tab-header".$closed."'>"
										.$label
										."<span class='fa fa-angle-down tm-arrow'></span>"
										."</h4></div>",
						"label"		=> "",
						"desc" 		=> ""
					));
	}	

	private function _prepend_div( $id="" ,$tmtab="tm-tab"){
		if (!empty($id)){
			$id .="-tab";
		}
		return array(array(
						"id" 		=> $id."_custom_divstart",
						"default" 	=> "",
						"type"		=> "custom",
						"nodiv"		=> 1,
						"html"		=> "<div class='transition ".$tmtab." ".$id."'>",
						"label"		=> "",
						"desc" 		=> ""
					));
	}

	private function _append_div( $id="" ){
		return array(array(
						"id" 		=> $id."_custom_divend",
						"default" 	=> "",
						"type"		=> "custom",
						"nodiv"		=> 1,
						"html"		=> "</div>",
						"label"		=> "",
						"desc" 		=> ""
					));
	}

	private function builder_showlogic (){
		$h="";
		$h .= '<div class="builder-logic-div">';
			$h .= '<div class="row nopadding">';
			$h .= '<select class="epo-rule-toggle"><option value="show">Show</option><option value="hide">Hide</option></select><span>this field if</span><select class="epo-rule-what"><option value="all">all</option><option value="any">any</option></select><span>of these rules match:</span>';
			$h .= '</div>';

			$h .= '<div class="tm-logic-wrapper">';
				
			$h .= '</div>';
		$h .= '</div>';
		return $h;
	}

	/**
	 * Common element options.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string  $id element internal id. (key from $this->elements_array)
	 *
	 * @return array List of common element options adjusted by element internal id.
	 */
	private function _get_header_array( $id="header" ) {
		return
		array(
			array(
				"id" 		=> $id."_size",
				"default"	=> "3",
				"type"		=> "select",
				"tags"		=> array( "id"=>"builder_".$id."_size", "name"=>"tm_meta[tmfbuilder][".$id."_size][]" ),
				"options"	=> array(
					array( "text"=> __( "H1", TM_EPO_TRANSLATION ), "value"=>"1" ),
					array( "text"=> __( "H2", TM_EPO_TRANSLATION ), "value"=>"2" ),
					array( "text"=> __( "H3", TM_EPO_TRANSLATION ), "value"=>"3" ),
					array( "text"=> __( "H4", TM_EPO_TRANSLATION ), "value"=>"4" ),
					array( "text"=> __( "H5", TM_EPO_TRANSLATION ), "value"=>"5" ),
					array( "text"=> __( "H6", TM_EPO_TRANSLATION ), "value"=>"6" ),
					array( "text"=> __( "p", TM_EPO_TRANSLATION ), "value"=>"7" ),
					array( "text"=> __( "div", TM_EPO_TRANSLATION ), "value"=>"8" ),
					array( "text"=> __( "span", TM_EPO_TRANSLATION ), "value"=>"9" )
				),
				"label"		=> __( "Label size", TM_EPO_TRANSLATION ),
				"desc" 		=> ""
			),
			array(
				"id" 		=> $id."_title",
				"default"	=> "",
				"type"		=> "text",
				"tags"		=> array( "class"=>"t tm-header-title", "id"=>"builder_".$id."_title", "name"=>"tm_meta[tmfbuilder][".$id."_title][]", "value"=>"" ),
				"label"		=> __( 'Label', TM_EPO_TRANSLATION ),
				"desc" 		=> ""
			),
			array(
				"id" 		=> $id."_title_color",
				"default"	=> "",
				"type"		=> "text",
				"tags"		=> array( "class"=>"tm-color-picker", "id"=>"builder_".$id."_title_color", "name"=>"tm_meta[tmfbuilder][".$id."_title_color][]", "value"=>"" ),
				"label"		=> __( 'Header color', TM_EPO_TRANSLATION ),
				"desc" 		=> __( 'Leave empty for default value', TM_EPO_TRANSLATION )
			),
			array(
				"id" 		=> $id."_subtitle",
				"default"	=> "",
				"type"		=> "textarea",
				"tags"		=> array( "id"=>"builder_".$id."_subtitle", "name"=>"tm_meta[tmfbuilder][".$id."_subtitle][]" ),
				"label"		=> __( "Subtitle", TM_EPO_TRANSLATION ),
				"desc" 		=> ""
			),
			array(
				"id" 		=> $id."_subtitle_position",
				"default"	=> "",
				"type"		=> "select",
				"tags"		=> array( "id"=>"builder_".$id."_subtitle_position", "name"=>"tm_meta[tmfbuilder][".$id."_subtitle_position][]" ),
				"options"	=> array(
					array( "text"=> __( "Above field", TM_EPO_TRANSLATION ), "value"=>"" ),
					array( "text"=> __( "Below field", TM_EPO_TRANSLATION ), "value"=>"below" )
				),
				"label"		=> __( "Subtitle position", TM_EPO_TRANSLATION ),
				"desc" 		=> ""
			),
			array(
				"id" 		=> $id."_subtitle_color",
				"default"	=> "",
				"type"		=> "text",
				"tags"		=> array( "class"=>"tm-color-picker", "id"=>"builder_".$id."_subtitle_color", "name"=>"tm_meta[tmfbuilder][".$id."_subtitle_color][]", "value"=>"" ),
				"label"		=> __( 'Subtitle color', TM_EPO_TRANSLATION ),
				"desc" 		=> __( 'Leave empty for default value', TM_EPO_TRANSLATION )
			)
		);
	}

	/**
	 * Sets element divider option.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string  $id element internal id. (key from $this->elements_array)
	 *
	 * @return array Element divider options adjusted by element internal id.
	 */
	private function _get_divider_array( $id="divider", $noempty=1 ) {
		$_divider = array(
			array(
				"id" 		=> $id."_type",
				"default"	=> "hr",
				"type"		=> "select",
				"tags"		=> array( "id"=>"builder_".$id."_type", "name"=>"tm_meta[tmfbuilder][".$id."_type][]" ),
				"options"	=> array(
					array( "text"=> __( "Horizontal rule", TM_EPO_TRANSLATION ), "value"=>"hr" ),
					array( "text"=> __( "Divider", TM_EPO_TRANSLATION ), "value"=>"divider" ),
					array( "text"=> __( "Padding", TM_EPO_TRANSLATION ), "value"=>"padding" )
				),
				"label"		=> __( "Divider type", TM_EPO_TRANSLATION ),
				"desc" 		=> ""
			)
		);
		if ( empty( $noempty ) ) {
			$_divider[0]["default"]="none";
			array_push( $_divider[0]["options"], array( "text"=>__( "None", TM_EPO_TRANSLATION ), "value"=>"none" ) );
		}
		return $_divider;
	}

	private function _prepend_logic($id=""){
		return array(
			array(
				"id" 		=> $id."_uniqid",
				"default"	=> "",
				"nodiv"  	=> 1,
				"type"		=> "hidden",
				"tags"		=> array( "class"=>"tm-builder-element-uniqid", "name"=>"tm_meta[tmfbuilder][".$id."_uniqid][]", "value"=>"" ),
				"label"		=> "",
				"desc" 		=> ""
			),
			array(
				"id"   		=> $id."_clogic",
				"default" 	=> "",
				"nodiv"  	=> 1,
				"type"  	=> "hidden",
				"tags"  	=> array( "class"=>"tm-builder-clogic", "name"=>"tm_meta[tmfbuilder][".$id."_clogic][]", "value"=>"" ),
				"label"  	=> "",
				"desc"   	=> ""
			),
			array(
				"id"   		=> $id."_logic",
				"default" 	=> "",
				"type"  	=> "select",
				"tags"  	=> array( "class"=>"activate-element-logic", "id"=>"divider_element_logic", "name"=>"tm_meta[tmfbuilder][".$id."_logic][]" ),
				"options" 	=> array(
					array( "text" => __( "No", TM_EPO_TRANSLATION ), "value"=>"" ),
					array( "text" => __( "Yes", TM_EPO_TRANSLATION ), "value"=>"1" )
				),
				"extra"		=> $this->builder_showlogic(),
				"label"		=> __("Element Conditional Logic", TM_EPO_TRANSLATION ),
				"desc" 		=> __("Enable conditional logic for showing or hiding this element.", TM_EPO_TRANSLATION )
			)
		);
	}

	/**
	 * Returns all elements for combo box selector.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_elements() {
		$elements=array();
		foreach ( $this->_elements() as $k=>$v ) {
			$elements[]=array( "text"=>$v[0], "value"=>$k );
		}
		return $elements;
	}

	/**
	 * Returns all meta options for saving meta data.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function elements_options() {
		$out=array();
		$options=array();
		$options[]= array(
			"id" => "element_type",
			"default"=>"",
			"type"=>"hidden",
			"tags"=>array( "id"=>"element_type", "name"=>"tm_meta[tmfbuilder][element_type][]" ),
		);
		$options[]= array(
			"id" => "div_size",
			"default"=>"",
			"type"=>"hidden",
			"tags"=>array( "id"=>"div_size", "name"=>"tm_meta[tmfbuilder][div_size][]" ),
		);

		foreach ( $this->elements_array as $k=>$v ) {
			$out=array_merge( $out, $v );
		}
		$out=array_merge( $out, $options );
		return $out;
	}

	/**
	 * Generates all hidden elements for use in jQuery.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_elements( $echo=0 ) {
		$out1='';
		$drag_elements='';
		foreach ( $this->_elements() as $k=>$v ) {
			if ( isset( $this->elements_array[$k] ) ) {
				$drag_elements .="<div data-element='".$k."' class='ditem element-".$k."'><div class='label'><i class='tmfa fa ".$v[3]."'></i> ".$v[0]."</div></div>";
				$_temp_option=$this->elements_array[$k];
				
				$out1 	.="<div class='bitem element-".$k." ".$v[1]."'>"
						."<input class='builder_element_type' name='tm_meta[tmfbuilder][element_type][]' type='hidden' value='".$k."' />"
						."<input class='div_size' name='tm_meta[tmfbuilder][div_size][]' type='hidden' value='".$v[1]."' />"
						."<div class='hstc2 closed'><div class='icon fa fa-arrows move'></div>"
						."<div class='icon fa fa-minus minus'></div><div class='icon fa fa-plus plus'></div>"
						."<div class='icon size'>".$v[2]."</div>"
						."<div class='icon fa fa-pencil edit'></div><div class='icon fa fa-copy clone'></div><div class='icon fa fa-times delete'></div>"
						."<div class='label-icon'><i class='tmfa fa ".$v[3]."'></i></div>"
						."<div class='label'>".$v[0]."</div><div class='inside'><div class='manager'>"
						."<div class='builder_element_wrap'>";
				foreach ( $_temp_option  as $key=>$value ) {
					$out1 .=$this->html->tm_make_field( $value, 0 );
				}
				$out1 .="</div></div></div></div></div>";
			}
		}
		$out  ='<div class="builder_elements"><div class="builder_hidden_elements" data-template="'.esc_html( json_encode( array( "html"=>$out1 ) ) ).'"></div>'
				.'<div class="builder_hidden_section" data-template="'.esc_html( json_encode( array( "html"=>$this->section_elements( 0 ) ) ) ).'"></div>'
				.'<div class="builder_drag_elements">'.$drag_elements.'</div>'
				."</div>";
		if ( empty( $echo ) ) {
			return $out;
		}else {
			echo $out;
		}
	}

	/**
	 * Generates all hidden sections for use in jQuery.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function section_elements( $echo=0 ) {
		$out='';

		foreach ( $this->_section_elements as $k=>$v ) {
			$out .=$this->html->tm_make_field( $v, 0 );
		}

		$out= "<div class='builder_wrapper'>"
			. "<div class='section_elements closed'>"
			. $out
			. "</div>"
			. "<div class='btitle'>"
			. "<div class='icon fa fa-arrows move'></div><div class='icon fa fa-minus minus'></div><div class='icon fa fa-plus plus'></div>"
			. "<div class='icon size'>".$this->sizer["w100"]."</div>"
			. "<div class='icon builder_add_on_section'>".__("Add item",TM_EPO_TRANSLATION)." <i class='fa fa-plus plus'></i></div>"
			. "<div class='icon fa fa-caret-down fold'></div><div class='icon fa fa-copy clone'></div><div class='icon fa fa-pencil edit'></div><div class='icon fa fa-times delete'></div>"
			. "</div>"
			. "<div class='bitem_wrapper'></div>"
			. "</div>";

		if ( empty( $echo ) ) {
			return $out;
		}else {
			echo $out;
		}

	}

	private function _tm_clear_array_values($val) { 
		if(is_array($val)){
			return array_map(array( $this,'_tm_clear_array_values'), $val);
		}else{
			return "";
		}
	}

	/**
	 * Generates all saved elements.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_saved_elements( $echo=0, $post_id=0 ) {
		$builder= get_post_meta( $post_id , 'tm_meta', true );
		$out='';
		if (!isset($builder['tmfbuilder'])){
			$builder['tmfbuilder']=array();
		}else{
			$builder=$builder['tmfbuilder'];
		}
		/* only check for element_type meta
		   as if it exists div_size will exist too
		   unless database has been compromised
		*/
		if ( !empty( $post_id ) && is_array( $builder ) && count( $builder )>0 && isset($builder['element_type']) && is_array($builder['element_type']) && count($builder['element_type'])>0 ) {
			// All the elements
			$_elements=$builder['element_type'];
			// All element sizes
			$_div_size=$builder['div_size'];

			// All sections (holds element count for each section)
			$_sections=$builder['sections'];
			// All section sizes
			$_sections_size=$builder['sections_size'];	
			if ( !is_array( $_sections ) ){
				$_sections=array( count( $_elements ) );
			}
			if ( !is_array( $_sections_size ) ){
				$_sections_size=array( "w100" );
			}

			$_helper_counter=0;
			$_this_elements= $this->_elements();

			$t=array();
			
			$_counter=array();
			$id_counter=array();
			for ( $_s = 0; $_s < count( $_sections ); $_s++ ) {
				$out 	.="<div class='builder_wrapper ".$_sections_size[$_s]."'>"
						."<div class='section_elements closed'>";
				foreach ( $this->_section_elements as $_sk=>$_sv ) {
					if (isset($builder[$_sv['id']])){
						$_sv['default'] = $builder[$_sv['id']][$_s];
					}
					if ( isset( $_sv['tags']['id'] ) ) {
						// we assume that $_sv['tags']['name'] exists if tag id is set
						$_name=str_replace(array("[","]"), "", $_sv['tags']['name']);						
						$_sv['tags']['id']=$_name.$_s;
					}
					if ($_sk=='sectionuniqid' && !isset($builder[$_sv['id']])){
						$_sv['default'] = uniqid("",true);
					}
					$out .=$this->html->tm_make_field( $_sv, 0 );
				}

				$out .="</div>"
					. "<div class='btitle'>"
					. "<div class='icon fa fa-arrows move'></div><div class='icon fa fa-minus minus'></div><div class='icon fa fa-plus plus'></div>"
					. "<div class='icon size'>".$this->sizer[$_sections_size[$_s]]."</div>"
					. "<div class='icon builder_add_on_section'>".__("Add item",TM_EPO_TRANSLATION)." <i class='fa fa-plus plus'></i></div>"
					. "<div class='icon fa fa-caret-down fold'></div><div class='icon fa fa-copy clone'></div><div class='icon fa fa-pencil edit'></div><div class='icon fa fa-times delete'></div>"
					. "</div>"
					. "<div class='bitem_wrapper'>";
				for ( $k0 = $_helper_counter; $k0 < intval( $_helper_counter+intval( $_sections[$_s] ) ); $k0++ ) {
					if (isset($_elements[$k0])){
						if ( isset( $this->elements_array[$_elements[$k0]] ) ) {
							$out .="<div class='bitem element-".$_elements[$k0]." ".$_div_size[$k0]. "'>"
								 . "<input class='builder_element_type' name='tm_meta[tmfbuilder][element_type][]' type='hidden' value='". $_elements[$k0]."' />"
								 . "<input class='div_size' name='tm_meta[tmfbuilder][div_size][]' type='hidden' value='". $_div_size[$k0]."' />"
								 . "<div class='hstc2 closed'><div class='icon fa fa-arrows move'></div><div class='icon fa fa-minus minus'></div><div class='icon fa fa-plus plus'></div>"
								 . "<div class='icon size'>". $this->sizer[$_div_size[$k0]]."</div>"
								 . "<div class='icon fa fa-pencil edit'></div><div class='icon fa fa-copy clone'></div><div class='icon fa fa-times delete'></div>"
								 . "<div class='label-icon'><i class='tmfa fa ".$_this_elements[$_elements[$k0]][3]."'></i></div>"
								 . "<div class='label'>".$_this_elements[$_elements[$k0]][0]."</div><div class='inside'><div class='manager'>";
							$_temp_option=$this->elements_array[$_elements[$k0]];
							if ( !isset( $_counter[$_elements[$k0]] ) ) {
								$_counter[$_elements[$k0]]=0;
							}else {
								$_counter[$_elements[$k0]]++;
							}
							$out .="<div class='builder_element_wrap'>";
							foreach ( $_temp_option  as $key=>$value ) {
								if ( isset( $value['id'] ) ) {
									$_vid=$value['id'];
									if ( !isset( $t[$_vid] )  ) {
										$t[$_vid]=isset($builder[$value['id']])?$builder[$value['id']]:null;
									}
									if ( $t[$_vid] !== NULL && count( $t[$_vid] )>0 && isset( $value['default'] ) && isset( $t[$_vid][$_counter[$_elements[$k0]]] ) ) {
										$value['default'] = $t[$_vid][$_counter[$_elements[$k0]]];
									}
									if (in_array($value['id'],array("checkboxes_options","radiobuttons_options","selectbox_options"))){
										/* holds the default checked values (cannot be cached in $t[$_vid]) */
										$_default_value=isset($builder['multiple_'.$value['id'].'_default_value'])?$builder['multiple_'.$value['id'].'_default_value']:null;
										
										if (is_null($t[$_vid])){
											$_titles=isset($builder['multiple_'.$value['id'].'_title'])?$builder['multiple_'.$value['id'].'_title']:null;
											$_values=isset($builder['multiple_'.$value['id'].'_value'])?$builder['multiple_'.$value['id'].'_value']:null;
											$_prices=isset($builder['multiple_'.$value['id'].'_price'])?$builder['multiple_'.$value['id'].'_price']:null;
											$_images=isset($builder['multiple_'.$value['id'].'_image'])?$builder['multiple_'.$value['id'].'_image']:null;
											$_prices_type=isset($builder['multiple_'.$value['id'].'_price_type'])?$builder['multiple_'.$value['id'].'_price_type']:null;
											$_url	=isset($builder['multiple_'.$value['id'].'_url'])?$builder['multiple_'.$value['id'].'_url']:null;

											
											if (!is_null($_titles) && !is_null($_values) && !is_null($_prices) ){
												$t[$_vid]=array();
												// backwards combatility
												
												if (is_null($_images)){
													$_images=$_titles;
													$_images = array_map(array( $this,'_tm_clear_array_values'), $_images);
												}
												if (is_null($_prices_type)){
													$_prices_type=$_prices;
													$_prices_type = array_map(array( $this,'_tm_clear_array_values'), $_prices_type);
												}
												if (is_null($_url)){
													$_url=$_titles;
													$_url = array_map(array( $this,'_tm_clear_array_values'), $_url);
												}
												foreach ($_titles as $option_key=>$option_value){
													$t[$_vid][]=array($_titles[$option_key],$_values[$option_key],$_prices[$option_key],$_images[$option_key],$_prices_type[$option_key],$_url[$option_key]);
												}
											}
										}
										if (!is_null($t[$_vid])){ 
											$value['html'] = $this->builder_sub_options( $t[$_vid][$_counter[$_elements[$k0]]], 'multiple_'.$value['id'], $_counter[$_elements[$k0]], $_default_value );
										}
									}
								}
								// we assume that $value['tags']['name'] exists if tag id is set
								if ( isset( $value['tags']['id'] ) ) {
									$_name=str_replace(array("[","]"), "", $value['tags']['name']);
									if (!isset($id_counter[$_name])){
										$id_counter[$_name]=0;
									}else{
										$id_counter[$_name]=$id_counter[$_name]+1;
									}
									$value['tags']['id']=$_name.$id_counter[$_name];
								}

								$out .=$this->html->tm_make_field( $value, 0 );
							}
							$out .="</div></div></div></div></div>";							
						}						
					}
				}
				$out .="</div>";//bitem_wrapper
				$out .="</div>";//builder_wrapper
				$_helper_counter=intval( $_helper_counter+intval( $_sections[$_s] ) );
			}
		}
		if ( empty( $echo ) ) {
			return $out;
		}else {
			echo $out;
		}
	}

	/**
	 * Generates element sub-options for selectbox, checkbox and radio buttons.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function builder_sub_options( $options=array(), $name="multiple_selectbox_options", $counter=NULL , $default_value=NULL) {
		$o=array();
		$upload="";
		$class="";
		if ($name == "multiple_radiobuttons_options" || $name == "multiple_checkboxes_options"){
			$upload = '&nbsp;<span class="tm_upload_button cp_button"><i class="fa fa-upload"></i></span>';
			$class= " withupload";
		}
		$o[]= array(
			"id" 		=> $name."_title",
			"default"	=>"",
			"type"		=> "text",
			"nodiv"		=> 1,
			"tags"		=> array( "class"=>"t tm_option_title".$class, "id"=>$name."_title", "name"=>$name."_title", "value"=>"" ),
			"extra" 	=> $upload
		);
		$o[]= array(
			"id" 		=> $name."_value",
			"default"	=> "",
			"type"		=> "text",
			"nodiv"		=> 1,
			"tags"		=> array( "class"=>"t tm_option_value", "id"=>$name."_value", "name"=>$name."_value" ),
		);
		$o[]= array(
			"id" 		=> $name."_price",
			"default"	=> "",
			"type"		=> "text",
			"nodiv"		=> 1,
			"tags"		=> array( "class"=>"n tm_option_price", "id"=>$name."_price", "name"=>$name."_price" ),
		);
		$o[]= array(
			"id" 		=> $name."_image",
			"default"	=> "",
			"type"		=> "hidden",
			"nodiv"		=> 1,
			"tags"		=> array( "class"=>"n tm_option_image", "id"=>$name."_image", "name"=>$name."_image" ),
		);		
		$o[]= array(
			"id" 		=> $name."_price_type",
			"default"	=> "",
			"type"		=> "select",
			"options"	=> array(
						array( "text"=> __( "Fixed amount", TM_EPO_TRANSLATION ), "value"=>"" ),
						array( "text"=> __( "Percent of the original price", TM_EPO_TRANSLATION ), "value"=>"percent" ),
						array( "text"=> __( "Percent of the original price + options", TM_EPO_TRANSLATION ), "value"=>"percentcurrenttotal" )
					),
			"nodiv"		=> 1,
			"tags"		=> array( "class"=>"n tm_option_price_type", "id"=>$name."_price_type", "name"=>$name."_price_type" ),
		);
		$o[]= array(
			"id" 		=> $name."_url",
			"default"	=>"",
			"type"		=> "text",
			"nodiv"		=> 1,
			"tags"		=> array( "class"=>"t tm_option_url", "id"=>$name."_url", "name"=>$name."_url", "value"=>"" ),
			"extra" 	=> $upload
		);
		if ($this->woo_subscriptions_check && $name!="multiple_selectbox_options"){
			$o[4]['options'][]=array( "text"=> __( "Subscription fee", TM_EPO_TRANSLATION ), "value"=>"subscriptionfee" );
		}
		if (!$this->woo_subscriptions_check && $name!="multiple_selectbox_options"){
			$o[4]['options'][]=array( "text"=> __( "Fee", TM_EPO_TRANSLATION ), "value"=>"fee" );
		}
		if ( !$options ) {
			$options=array( array( "" ), array( "" ), array( "" ), array( "" ), array( "" ), array( "" ));
		}

		$del=$this->html->tm_make_button( array(
				"text"=>"<i class='fa fa-times'></i>",
				"tags"=>array( "href"=>"#delete", "class"=>"button button-secondary button-small builder_panel_delete" ) 
				), 0 );
		$drag=$this->html->tm_make_button( array(
				"text"=>"<i class='fa fa-arrows'></i>",
				"tags"=>array( "href"=>"#move", "class"=>"builder_panel_move" )
				), 0 );

		$out  = "<div class='row nopadding multiple_options'>"
			. "<div class='cell col-1 tm_cell_move'>&nbsp;</div>"
			. "<div class='cell col-1 tm_cell_default'>".(($name == "multiple_checkboxes_options")?__( "Checked", TM_EPO_TRANSLATION ):__( "Default", TM_EPO_TRANSLATION ))."</div>"
			. "<div class='cell col-3 tm_cell_title'>".__( "Label", TM_EPO_TRANSLATION )."</div>"
			. "<div class='cell col-3 tm_cell_url'>".__( "URL", TM_EPO_TRANSLATION )."</div>"
			. "<div class='cell col-3 tm_cell_value'>".__( "Value", TM_EPO_TRANSLATION )."</div>"
			. "<div class='cell col-3 tm_cell_price'>".__( "Price", TM_EPO_TRANSLATION )."</div>"
			. "<div class='cell col-1 tm_cell_delete'>&nbsp;</div>"
			. "</div>"
			. "<div class='panels_wrap nof_wrapper'>";
		
		$d_counter=0;
		foreach ( $options[0] as $ar=>$el ) {
			$out  	.= "<div class='options_wrap'>"
					. "<div class='row nopadding'>";

			$o[0]["default"]  		= $options[0][$ar];//label
			$o[0]["tags"]["name"] 	= "tm_meta[tmfbuilder][".$name."_title][".( is_null( $counter )?0:$counter )."][]";
			$o[0]["tags"]["id"]		= str_replace(array("[","]"), "", $o[0]["tags"]["name"])."_".$ar;
			$o[0]["extra"]			= $upload.'<span class="tm_upload_image"><img class="tm_upload_image_img" alt="" src="'.$options[3][$ar].'" /></span>';
			
			$o[1]["default"]  		= $options[1][$ar];//value
			$o[1]["tags"]["name"] 	= "tm_meta[tmfbuilder][".$name."_value][".( is_null( $counter )?0:$counter )."][]";
			$o[1]["tags"]["id"]		= str_replace(array("[","]"), "", $o[1]["tags"]["name"])."_".$ar;
			
			$o[2]["default"]  		= $options[2][$ar];//price
			$o[2]["tags"]["name"] 	= "tm_meta[tmfbuilder][".$name."_price][".( is_null( $counter )?0:$counter )."][]";
			$o[2]["tags"]["id"]		= str_replace(array("[","]"), "", $o[2]["tags"]["name"])."_".$ar;

			$o[3]["default"]  		= $options[3][$ar];//image
			$o[3]["tags"]["name"] 	= "tm_meta[tmfbuilder][".$name."_image][".( is_null( $counter )?0:$counter )."][]";
			$o[3]["tags"]["id"]		= str_replace(array("[","]"), "", $o[3]["tags"]["name"])."_".$ar;

			$o[4]["default"]  		= $options[4][$ar];//price type
			$o[4]["tags"]["name"] 	= "tm_meta[tmfbuilder][".$name."_price_type][".( is_null( $counter )?0:$counter )."][]";
			$o[4]["tags"]["id"]		= str_replace(array("[","]"), "", $o[4]["tags"]["name"])."_".$ar;

			$o[5]["default"]  		= $options[5][$ar];//url
			$o[5]["tags"]["name"] 	= "tm_meta[tmfbuilder][".$name."_url][".( is_null( $counter )?0:$counter )."][]";
			$o[5]["tags"]["id"]		= str_replace(array("[","]"), "", $o[5]["tags"]["name"])."_".$ar;

			if ($name == "multiple_checkboxes_options"){
				$default_select = '<input type="checkbox" value="'.$d_counter.'" name="tm_meta[tmfbuilder]['.$name.'_default_value]['.( is_null( $counter )?0:$counter ).'][]" class="tm-default-checkbox" '.checked(  ( is_null( $counter )?"":isset($default_value[$counter])?in_array($d_counter, $default_value[$counter]):"" ) , true ,0).'>';
			}else{
				$default_select = '<input type="radio" value="'.$d_counter.'" name="tm_meta[tmfbuilder]['.$name.'_default_value]['.( is_null( $counter )?0:$counter ).']" class="tm-default-radio" '.checked(  ( is_null( $counter )?"":isset($default_value[$counter])?(string)$default_value[$counter]:"" ) , $d_counter ,0).'>';
			}
			
			$out .= "<div class='cell col-1 tm_cell_move'>".$drag."</div>";
			$out .= "<div class='cell col-1 tm_cell_default'>".$default_select."</div>";
			$out .= "<div class='cell col-3 tm_cell_title'>".$this->html->tm_make_field( $o[0], 0 ).$this->html->tm_make_field( $o[3], 0 )."</div>";
			$out .= "<div class='cell col-3 tm_cell_url'>".$this->html->tm_make_field( $o[5], 0 )."</div>";
			$out .= "<div class='cell col-3 tm_cell_value'>".$this->html->tm_make_field( $o[1], 0 )."</div>";
			$out .= "<div class='cell col-3 tm_cell_price'>".$this->html->tm_make_field( $o[2], 0 ).$this->html->tm_make_field( $o[4], 0 )."</div>";
			$out .= "<div class='cell col-1 tm_cell_delete'>".$del."</div>";

			$out .="</div></div>";
			$d_counter++;
		}
		$out .="</div>";
		$out .=' <a class="tm-button button button-primary button-large builder-panel-add" href="#">'.__( "Add item", TM_EPO_TRANSLATION ).'</a>';
		$out .=' <a class="tm-button button button-primary button-large builder-panel-mass-add" href="#">'.__( "Mass add", TM_EPO_TRANSLATION ).'</a>';

		return $out;
	}

	/**
	 * Holds all the elements types.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function _elements() {
		$elements=array(
			"header"  		=> array( __( "Heading", TM_EPO_TRANSLATION ), "w100", "1/1", "fa-header" ),
			"divider"  		=> array( __( "Divider", TM_EPO_TRANSLATION ), "w100", "1/1" , "fa-divider"),
			"textarea"  	=> array( __( "Text Area", TM_EPO_TRANSLATION ), "w100", "1/1", "fa-textarea" ),
			"textfield"  	=> array( __( "Text Field", TM_EPO_TRANSLATION ), "w100", "1/1", "fa-textfield" ),
			"selectbox"  	=> array( __( "Select Box", TM_EPO_TRANSLATION ), "w100", "1/1", "fa-selectbox" ),
			"radiobuttons" 	=> array( __( "Radio buttons", TM_EPO_TRANSLATION ), "w100", "1/1", "fa-radiobox" ),
			"checkboxes" 	=> array( __( "Checkboxes", TM_EPO_TRANSLATION ), "w100", "1/1", "fa-checkbox" ),
			"upload" 		=> array( __( "Upload", TM_EPO_TRANSLATION ), "w100", "1/1", "fa-upload" ),
			"date" 			=> array( __( "Date", TM_EPO_TRANSLATION ), "w100", "1/1", "fa-calendar" ),
			"range" 		=> array( __( "Range picker", TM_EPO_TRANSLATION ), "w100", "1/1", "fa-range" )
		);
		return $elements;
	}
}

// Init builder
function tm_initialize_tm_epo_builder() {
	global $tm_epo_builder;
	$tm_epo_builder = new tm_epo_builder();
}
add_action( 'init', 'tm_initialize_tm_epo_builder' );
?>
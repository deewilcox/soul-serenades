<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}
if ( !empty( $class ) ) {
	$class=" ".$class;
	$divclass=$class."-div";
	$ulclass=$class."-ul";
}else{
	$class="";
	$divclass="";
	$ulclass="";
}

if (!$haslogic){
	$logic="";
}

?>
<div data-uniqid="<?php echo $uniqid;?>" 
	data-logic="<?php echo $logic;?>" 
	data-haslogic="<?php echo $haslogic;?>" 
	class="cpf_hide_element cell <?php echo $column; ?> cpf-type-<?php echo $type.$divclass; ?>">
<?php
$use=" ".$class_id;
if (!empty($use_images)){
	switch ($use_images){
	case "images":
		$use .=" use_images_containter";
		break;
	}
}
if (!empty($use_url)){
	switch ($use_url){
	case "url":
		$use .=" use_url_containter";
		break;
	}
}

if ($element!="divider"){
	if (!empty($title) || !empty($required)){
		echo '<'.$title_size;
		if(!empty($title_color)){
			echo ' style="color:'.$title_color.'"';
		}
		if ( $element=='header' && !empty( $class ) ) {
			$class=" ".$class;
		}
		echo ' class="tm-epo-field-label'.$class.'">'.$title;
		if($required){
			echo '<span class="tm-epo-required">*</span>';
		}
		echo '</'.$title_size.'>';
	}
	if(!empty($description) && empty($description_position)){
		echo'<div'; 
		if(!empty($description_color)){
			echo ' style="color:'.$description_color.'"';
		}
		echo' class="tm-description">'.do_shortcode($description).'</div>';
	}

}
echo $divider;
if (!in_array($element,array('header','divider'))){
?>
	<div class="tm-extra-product-options-container">
        <ul data-rules="<?php echo $rules;?>" 
        	data-rulestype="<?php echo $rules_type; ?>" 
        	class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-<?php echo $type.$use.$ulclass; ?>">
<?php 
}
?>
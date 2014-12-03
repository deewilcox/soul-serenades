<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}
if (!empty ($sections_type)){
	$sections_class .=" section_popup";
}
if (!$haslogic){
	$logic="";
}
?>
<div data-uniqid="<?php echo $uniqid;?>" 
	data-logic="<?php echo $logic;?>" 
	data-haslogic="<?php echo $haslogic;?>" 
	class="cpf-section row cell <?php echo $column;?> <?php echo $sections_class;?>">
<?php

if (!empty ($sections_type)){
	$_popuplinkitle=__('Additional options',TM_EPO_TRANSLATION);
	if (!empty ($title)){
		$_popuplinkitle=$title;
	}
	$_popuplink='<a class="tm-section-link" href="#" data-title="'.esc_attr($_popuplinkitle).'" data-sectionid="'.$uniqid.'">'.$_popuplinkitle.'</a>';
	echo $_popuplink.'<div class="tm-section-pop">';
}

$icon='';
$toggler='';
if ($style=="box"){
	echo '<div class="tm-box">';
}
if ($style=="collapse" || $style=="collapseclosed" || $style=="accordion"){
	echo '<div class="tm-collapse'.($style=="accordion"?' accordion':'').'">';
	$icon='<span class="fa fa-angle-down tm-arrow"></span>';
	$toggler=' tm-toggle';
	if ($title==''){
		$title='&nbsp;';
	}
}


	if ($title!=''){
		echo '<'.$title_size;
		if(!empty($title_color)){
			echo ' style="color:'.$title_color.'"';
		}
		echo ' class="tm-epo-field-label tm-section-label'.$toggler.'">'.$title;
		
		echo $icon.'</'.$title_size.'>';
	}
	if(!empty($description)){
		echo'<div'; 
		if(!empty($description_color)){
			echo ' style="color:'.$description_color.'"';
		}
		echo' class="tm-description">'.do_shortcode($description).'</div>';
	}
	echo $divider;
if ($style=="collapse"){
	echo '<div class="tm-collapse-wrap">';
}
if ($style=="collapseclosed"){
	echo '<div class="tm-collapse-wrap closed">';
}
if ($style=="accordion"){
	echo '<div class="tm-collapse-wrap closed">';
}
?>
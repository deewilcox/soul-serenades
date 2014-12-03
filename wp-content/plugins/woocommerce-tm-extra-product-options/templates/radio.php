<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}
if (!isset($fieldtype)){
	$fieldtype="tmcp-field";
}
?>
<?php
$use="";
if (!empty($use_images)){
	switch ($use_images){
	case "images":
		$use=" use_images";
		if (!empty($image)){
			$swatch="";
			$swatch_class="";
			if ($swatchmode=='swatch'){
				$swatch_class=" tm-tooltip";
				$swatch=' '.'data-tm-tooltip-swatch="on"';
			}
			if ($tm_epo_no_lazy_load=='no'){
				$altsrc='data-original="'.$image.'"';
			}else{
				$altsrc='src="'.$image.'"';
			}
			$label='<img class="tmlazy radio_image'.$swatch_class.'" alt="" '.$altsrc.$swatch.' />'.'<span class="checkbox_image_label">'.$label.'</span>';
		}
		break;
	}
}
if (!empty($class)){
	$fieldtype .=" ".$class;
}
if (!empty($changes_product_image)){
	$fieldtype .=" tm-product-image";
}
if (!empty($use_url)){
	switch ($use_url){
	case "url":
		$url=' data-url="'.$url.'"';
		break;
	}
}else{
	$url="";
}
$checked=false;

if (isset($_POST[$name]) && esc_attr(stripslashes($_POST[$name]))==$value){
	$checked=true;
}
elseif (empty($_POST) && isset($default_value)){
	if ($default_value){
		$checked=true;
	}
}
if (isset($textafterprice) && $textafterprice!=''){
	$textafterprice = '<span class="after-amount'.(!empty($hide_amount)?" ".$hide_amount:"").'">'.$textafterprice.'</span>';
}
?>
<li class="tmcp-field-wrap<?php echo $grid_break;?>">
	<input class="<?php echo $fieldtype;?> tm-epo-field tmcp-radio<?php echo $use; ?>" name="<?php echo $name; ?>" data-price="" data-rules="<?php echo $rules; ?>" data-rulestype="<?php echo $rules_type; ?>" value="<?php echo $value; ?>" id="<?php echo $id; ?>" tabindex="<?php echo $tabindex; ?>" type="radio" <?php checked( $checked, true ); echo $url; ?> />
	<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
	<span class="amount<?php if (!empty($hide_amount)){echo " ".$hide_amount;} ?>"><?php echo $amount; ?></span>
	<?php echo $textafterprice; ?>
</li>
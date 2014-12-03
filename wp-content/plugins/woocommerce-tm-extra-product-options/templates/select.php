<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}
if (!isset($fieldtype)){
	$fieldtype="tmcp-field";
}
if (isset($textafterprice) && $textafterprice!=''){
	$textafterprice = '<span class="after-amount'.(!empty($hide_amount)?" ".$hide_amount:"").'">'.$textafterprice.'</span>';
}
if (!empty($class)){
	$fieldtype .=" ".$class;
}
?>
<li class="tmcp-field-wrap">
	<label for="<?php echo $id; ?>"></label>
	<select class="<?php echo $fieldtype;?> tm-epo-field tmcp-select" name="<?php echo $name; ?>" data-price="" data-rules="" id="<?php echo $id; ?>" tabindex="<?php echo $tabindex; ?>"  >
	<?php echo $options; ?>
	</select>	
	<span class="amount<?php if (!empty($hide_amount)){echo " ".$hide_amount;} ?>"><?php echo $amount; ?></span>
	<?php echo $textafterprice; ?>
</li>
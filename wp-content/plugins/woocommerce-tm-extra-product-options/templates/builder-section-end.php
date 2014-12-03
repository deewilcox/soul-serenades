<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}
if ($style=="box"){
	echo '</div>';
}
if ($style=="collapse" || $style=="collapseclosed" || $style=="accordion"){
	echo '</div></div>';
}	
if (!empty ($sections_type)){
	echo '</div>';
}
?>
</div>
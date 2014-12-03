<?php
/**
 *
 *   View for displaying saved TM EPOs
 *
 */

// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
    die();
}

global $post, $_TM_Global_Extra_Product_Options, $post_id, $tm_is_ajax, $woocommerce;
$tm_meta_cpf=get_post_meta( $post_id, 'tm_meta_cpf', true );
$tm_meta_cpf_mode=isset($tm_meta_cpf['mode'])?$tm_meta_cpf['mode']:'';
?>
<div id="tm_extra_product_options" class="panel wc-metaboxes-wrapper">
    <div id="tm_extra_product_options_inner">
        <div class="tm_mode_selector">
            <input type="hidden" value="<?php echo $tm_meta_cpf_mode;?>" id="tm_meta_cpf_mode" name="tm_meta_cpf[mode]" >
            <p class="form-field tm_mode_select title"><?php _e( 'Select mode', TM_EPO_TRANSLATION ); ?></p>
            <p class="form-field tm_mode_select">
                <span class="button button-primary button-large tm_select_mode tm_builder_select clearfix"><?php _e( 'BUILDER', TM_EPO_TRANSLATION ); ?></span>
                <span class="button button-primary button-large tm_select_mode tm_local_select clearfix"><?php _e( 'LOCAL', TM_EPO_TRANSLATION ); ?></span>
            </p> 
        </div>
        <div class="tm_mode_builder">
<?php

    $_TM_Global_Extra_Product_Options->tm_form_fields_builder_meta_box($post);

?>
        </div>
        <div class="tm_mode_local">
            <?php include ('html-tm-epo.php'); ?>
        </div>
        <div class="tm_options_group woocommerce_options_panel">
        <p class="form-field">
        <?php
        /* Ouput Exclude */
        $tm_exclude=isset($tm_meta_cpf['exclude'])?$tm_meta_cpf['exclude']:'';
        echo '<label for="tm_meta_cpf_exclude">'.__( 'Exclude from Global Extra Product Options', TM_EPO_TRANSLATION ).'</label>';
        echo '<input type="checkbox" value="1" id="tm_meta_cpf_exclude" name="tm_meta_cpf[exclude]" class="checkbox" '.checked(  $tm_exclude , '1' ,0) .'>';
        ?>
        </p>
        <p class="form-field">
        <?php
        /* Ouput Override */
        $tm_override_display=isset($tm_meta_cpf['override_display'])?$tm_meta_cpf['override_display']:'';
        echo '<label for="tm_meta_cpf_override_display">'.__( 'Override global display', TM_EPO_TRANSLATION ).'</label>';
        echo '<select id="tm_meta_cpf_override_display" name="tm_meta_cpf[override_display]">';
        echo '<option value="" '.selected(  $tm_override_display , '' ,0) .'>' . __( 'Use global setting', TM_EPO_TRANSLATION ) . '</option>';
        echo '<option value="normal" '.selected(  $tm_override_display , 'normal' ,0) .'>' . __( 'Always show', TM_EPO_TRANSLATION ) . '</option>';
        echo '<option value="action" '.selected(  $tm_override_display , 'action' ,0) .'>' . __( 'Show only with action hook', TM_EPO_TRANSLATION ) . '</option>';
        echo '</select>';
        ?>
        </p>
        <p class="form-field">
        <?php
        /* Ouput Override */
        $tm_override_final_total_box=isset($tm_meta_cpf['override_final_total_box'])?$tm_meta_cpf['override_final_total_box']:'';
        echo '<label for="tm_meta_cpf_override_final_total_box">'.__( 'Override Final total box', TM_EPO_TRANSLATION ).'</label>';
        echo '<select id="tm_meta_cpf_override_final_total_box" name="tm_meta_cpf[override_final_total_box]">';
        echo '<option value="" '.selected(  $tm_override_final_total_box , '' ,0) .'>' . __( 'Use global setting', TM_EPO_TRANSLATION ) . '</option>';
        echo '<option value="normal" '.selected(  $tm_override_final_total_box , 'normal' ,0) .'>' . __( 'Show Both Final and Options total box', TM_EPO_TRANSLATION ) . '</option>';
        echo '<option value="final" '.selected(  $tm_override_final_total_box , 'final' ,0) .'>' . __( 'Show only Final box', TM_EPO_TRANSLATION ) . '</option>';
        echo '<option value="hideoptionsifzero" '.selected(  $tm_override_final_total_box , 'hideoptionsifzero' ,0) .'>' . __( 'Show Final box and hide Options if zero', TM_EPO_TRANSLATION ) . '</option>';
        echo '<option value="hide" '.selected(  $tm_override_final_total_box , 'hide' ,0) .'>' . __( 'Hide Final total box', TM_EPO_TRANSLATION ) . '</option>';
        echo '</select>';
        ?>
        </p>
        </div>
    </div>
</div>
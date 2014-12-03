<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
    die();
}

/**
 * Global EPO Administration class
 */
class TM_Global_EPO_Admin {

    var $version        = TM_EPO_VERSION;
    var $_namespace     = 'tm-extra-product-options';
    var $plugin_path;
    var $template_path;
    var $plugin_url;
    var $tm_list_table;

    public function __construct() {
        $this->plugin_path      = untrailingslashit( plugin_dir_path(  dirname( __FILE__ )  ) );
        $this->template_path    = $this->plugin_path.'/templates/';
        $this->plugin_url       = untrailingslashit( plugins_url( '/', dirname( __FILE__ ) ) );

        /**
         *  Add menu action
         */
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );

        /**
         *  Pre-render actions
         */
        add_action( 'admin_init', array( $this, 'tm_admin_init' ), 9 );
        add_action( 'plugins_loaded', array( $this, 'tm_init' ), 100 );

        /**
         *  Save custom screen options.
         */
        add_filter( 'set-screen-option', array( $this, 'tm_set_option' ), 10, 3);

        /**
         *  Add the plugin to WooCommerce screen ids so that
         *  we can load the generic WooCommerce files
         */
        add_filter( 'woocommerce_screen_ids', array( $this, 'woocommerce_screen_ids' ) );

        /**
         *  Add list columns
         */
        add_filter( 'manage_'.TM_EPO_GLOBAL_POST_TYPE.'_posts_columns' , array( $this, 'tm_list_columns' ) );
        add_action( 'manage_'.TM_EPO_GLOBAL_POST_TYPE.'_posts_custom_column' , array( $this, 'tm_list_column' ), 10, 2 );

        /**
         *  Export a form.
         */
        add_action( 'wp_ajax_tm_export', array( $this, 'export' ) );
    }

    /**
     * Export a form.
     */
    public function export(){

        require_once( TM_plugin_path.'/admin/tm-csv.php' );
        
        $csv = new TM_CSV();
        $csv->export('metaserialized');
        
    }

    /**
     * Import a form.
     */
    public function import(){

        require_once( TM_plugin_path.'/admin/tm-csv.php' );
        
        $csv = new TM_CSV();
        $csv->import();
        
    }

    /**
     * Download a form.
     */
    public function download(){

        require_once( TM_plugin_path.'/admin/tm-csv.php' );
        
        $csv = new TM_CSV();
        $csv->download();
        
    }

    /**
     * Extra row actions.
     */
    public function row_actions( $actions, $post ){

        // Get the post type object
        $post_type = get_post_type_object( $post->post_type );
        
        // Clone a form
        $nonce = wp_create_nonce( 'tmclone_form_nonce_'.$post->ID ); 
        $actions['tm_clone_form'] = '<a class="tm-clone-form" rel="'.$nonce.'" href="'.admin_url( "edit.php?post_type=product&amp;page=tm-global-epo&amp;action=clone&amp;post=".$post->ID."&amp;_wpnonce=".$nonce ).'">'.__( 'Clone form', TM_EPO_TRANSLATION ).'</a>';

        // Export a form
        $nonce = wp_create_nonce( 'tmexport_form_nonce_'.$post->ID ); 
        $actions['tm_export_form'] = '<a class="tm-export-form" rel="'.$nonce.'" href="'.admin_url( "edit.php?post_type=product&amp;page=tm-global-epo&amp;action=export&amp;post=".$post->ID."&amp;_wpnonce=".$nonce ).'">'.__( 'Export form', TM_EPO_TRANSLATION ).'</a>';
        ksort($actions);
        return $actions;
    }

    /**
     * Add menus
     */
    public function admin_menu() {
        $page_hook = add_submenu_page( 'edit.php?post_type=product', __( 'TM Global Extra Product Options', TM_EPO_TRANSLATION ), __( 'TM Global Extra Product Options', TM_EPO_TRANSLATION ), 'manage_woocommerce', 'tm-global-epo', array( $this, 'admin_screen' ) );
        
        /*
         *  Restrict loading scripts and functions unless we are on the plugin page
         */
        add_action( 'load-' . $page_hook, array( $this, 'tm_load_admin' ) );
    }

    public function tm_load_scripts(){
        /**
         *  Load javascript files
         */
        add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

        /**
         *  Load css files
         */
        add_action( 'woocommerce_admin_css', array( $this, 'register_admin_styles' ) );
    }

    /**
     * Loads plugin functionality
     */
    public function tm_load_admin(){

        $this->tm_load_scripts();

        /**
         *  Custom action to populate the filter select box.
         */
        add_action( 'tm_restrict_manage_posts', array( $this, 'tm_restrict_manage_posts' ) );

        /**
         *  Add screen option
         */        
        $this->tm_add_option();

        /**
         *  Add meta boxes
         */        
        $this->tm_add_metaboxes();

        /**
         *  Extra row actions
         */
        add_filter( 'post_row_actions', array( $this,'row_actions'), 10, 2 );
        add_filter( 'page_row_actions', array( $this,'row_actions'), 10, 2 );

    }

    /**
     * Add list columns
     */
    public function tm_list_columns($columns){
        $new_columns                = array();
        $new_columns['cb']          = isset($columns['cb'])?$columns['cb']:'<input type="checkbox" />';
        $new_columns['title']       = isset($columns['title'])?$columns['title']:__('Title',TM_EPO_TRANSLATION);
        $new_columns['priority']    = __( 'Priority',TM_EPO_TRANSLATION );
        $new_columns['product_cat'] = __( 'Categories' , TM_EPO_TRANSLATION);
        $new_columns['product_ids'] = __( 'Products' , TM_EPO_TRANSLATION);
        unset($columns['cb']);
        unset($columns['title']);
        return array_merge( $new_columns, $columns );
    }
    
    public function tm_list_column( $column,  $post_id ){
        switch ( $column ) {

            case 'product_cat' :
                $tm_meta= get_post_meta( $post_id , 'tm_meta_disable_categories' , true );
                if ($tm_meta){
                    echo '<span class="tm_color_pomegranate">'.__('Disabled',TM_EPO_TRANSLATION).'</span>';
                }else{
                    $terms = get_the_term_list( $post_id , 'product_cat' , '' , ' , ' , '' );
                    if ( is_string( $terms ) ){
                        echo $terms;
                    }                    
                }
                break;

            case 'priority' :
                $tm_meta= get_post_meta( $post_id , 'tm_meta' , true );
                if (is_array($tm_meta['priority'])){
                    $tm_meta['priority']=$tm_meta['priority'][0];
                }
                echo $tm_meta['priority'];
                break;

            case 'product_ids' :
                $tm_meta= get_post_meta( $post_id , 'tm_meta_product_ids' , true );

                if (!empty($tm_meta)){
                    if (is_array($tm_meta)){
                        if (count($tm_meta)==1){
                                $title=get_the_title( $tm_meta[0] );
                                $tm_meta[0]='<a title="'.esc_attr($title).'" href="'.admin_url( 'post.php?action=edit&post='.$tm_meta[0] ).'">'.$title.'</a>';
                        }else{
                            foreach ($tm_meta as $key => $value) {
                                $title=get_the_title( $value );
                                $tm_meta[$key]='<a class="tm-tooltip" title="'.esc_attr($title).'" href="'.admin_url( 'post.php?action=edit&post='.$value ).'">'.$value.'</a>';
                            }
                        }                        
                        echo implode(" , ", $tm_meta);        
                    }else{
                        echo '';
                    }
                }                
                break;

        }
        
    }

    /**
     * Handle meta boxes
     */
    public function tm_add_metaboxes(){
        // only continue if we are are on add/edit screen
        if (!$this->tm_list_table || !$this->tm_list_table->current_action()){
            return;
        }

        add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );

        // Publish meta box
        add_meta_box("submitdiv", _( 'Publish' ), array( $this, 'tm_post_submit_meta_box' ), null, "side", "core");

        // Taxonomies meta box
        if ($this->tm_list_table){
            foreach ( get_object_taxonomies( $this->tm_list_table->screen->post_type ) as $tax_name ) {
                $taxonomy = get_taxonomy( $tax_name );
                if ( ! $taxonomy->show_ui ){
                    continue;
                }
                if (!property_exists($taxonomy,'meta_box_cb') || false === $taxonomy->meta_box_cb ){
                    if ( $taxonomy->hierarchical ){
                        $taxonomy->meta_box_cb = 'post_categories_meta_box';
                    }else{
                        $taxonomy->meta_box_cb = 'post_tags_meta_box';
		    }
                }
                $label = $taxonomy->labels->name;
                if ( ! is_taxonomy_hierarchical( $tax_name ) ){
                    $tax_meta_box_id = 'tagsdiv-' . $tax_name;
                }else{
                    $tax_meta_box_id = $tax_name . 'div';
                }
                add_meta_box( $tax_meta_box_id, $label, $taxonomy->meta_box_cb, null, 'side', 'core', array( 'taxonomy' => $tax_name ) );
            }
        }

        add_meta_box("tm_product_search", __( 'Products', TM_EPO_TRANSLATION ), array( $this, 'tm_product_search_meta_box' ), null, "side", "core");

        // Description meta box
        add_meta_box("postexcerpt", __('Description', TM_EPO_TRANSLATION), array( $this, 'tm_description_meta_box' ), null, "normal", "core");
        
        // Price rules meta box
        add_meta_box("tmformfieldsbuilder", __('Form Fields Builder', TM_EPO_TRANSLATION), array( $this, 'tm_form_fields_builder_meta_box' ), null, "normal", "core");
    }

    // Description meta box
    public function tm_description_meta_box($post){
        $settings = array(
            'textarea_name' => 'excerpt',
            'quicktags'     => array( 'buttons' => 'em,strong,link' ),
            'tinymce'       => array(
                'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                'theme_advanced_buttons2' => '',
            ),
            'editor_css'    => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
        );

        wp_editor( htmlspecialchars_decode( $post->post_excerpt ), 'excerpt', apply_filters( 'woocommerce_product_short_description_editor_settings', $settings ) );
        echo '<p>'.esc_attr__('The description will appear under the title.', TM_EPO_TRANSLATION ).'</p>';
    }
    
    public function tm_product_search_meta_box($post){
        $meta=$post->tm_meta;?>
        <h3><label for="tm_meta_disable_categories"><?php _e( 'Disable categories', TM_EPO_TRANSLATION ); ?> 
            <input type="checkbox" value="1" id="tm_meta_disable_categories" name="tm_meta_disable_categories" class="meta-disable-categories" <?php checked($meta['disable_categories'] , 1); ?>/>                                                  
        </label></h3>
        <label for="tm_product_ids"><?php _e( 'Select the Product(s) to apply the options', TM_EPO_TRANSLATION ); ?></label>
                <select id="tm_product_ids" name="tm_meta_product_ids[]" class="ajax_chosen_select_tm_product_ids" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', TM_EPO_TRANSLATION ); ?>">
                    <?php 
                        $_ids = isset($meta['product_ids'])?$meta['product_ids']:null;
                        $product_ids = ! empty( $_ids ) ? array_map( 'absint',  $_ids ) : null;
                        if ( $product_ids ) {
                            foreach ( $product_ids as $product_id ) {

                                $product = get_product( $product_id );

                                if ( $product ){
                                    echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . esc_html( $product->get_formatted_name() ) . '</option>';
                                }
                                    
                            }
                        }
                    ?>
                </select><?php
    }

    // Price rules meta box
    public function tm_form_fields_builder_meta_box($post){
        ?>
        <div id="tmformfieldsbuilderwrap" class="tm_wrapper">
        <?php
        global $tm_epo_builder;
        $html = new TM_EPO_HTML();
        $elements=$tm_epo_builder->get_elements();
        echo "<div class='builder_selector'>"
            . '<div class="row">'
            . '<div class="cell col-6">'
            . '<a id="builder_add_section" class="builder_add_section bsbb" href="#"><i class="fa fa-plus-square"></i> '.__("Add section",TM_EPO_TRANSLATION).'</a>'
            . '</div>'
            . '<div class="tm-ajax-info cell col-2">'
            . '</div>'
            . '<div class="cell col-4">'
            . '<a id="builder_fullsize_close" class="tm-button button button-primary button-large builder_fullsize_close" href="#">'.__("Close",TM_EPO_TRANSLATION).'</a>'
            . '<a id="builder_fullsize" class="tm-button button button-primary button-large builder_fullsize clearfix" href="#">'.__("Fullsize",TM_EPO_TRANSLATION).'</a>'
            . '<a id="builder_export" class="tm-button button button-primary button-large builder-export clearfix" href="#">'.__("Export CSV",TM_EPO_TRANSLATION).'</a>'
            . '<a id="builder_import" class="tm-button button button-primary button-large builder-import clearfix" href="#">'.__("Import CSV",TM_EPO_TRANSLATION).'</a>'
            . '<input id="builder_import_file" name="builder_import_file" type="file" class="builder-import-file" />'
            . '</div>'
            . '</div>'
            . "</div>"
            . $tm_epo_builder->print_elements(0)
            . "<div class='builder_layout'>"
            . $tm_epo_builder->print_saved_elements(0,$post->ID)
            . "</div>";
        ?>
        </div>
    <?php
    }
    
    // Publish meta box
    public function tm_post_submit_meta_box($post){
        $meta=$post->tm_meta;
        ?>
        <div class="submitbox" id="submitpost">
            <div id="minor-publishing">
                <div style="display:none;">
                <?php submit_button( __( 'Save', TM_EPO_TRANSLATION ), 'button', 'save' ); ?>
                </div>
                <div id="minor-publishing-actions">
                    <div id="save-action">
                        <span class="spinner"></span>
                    </div>                                              
                    <div class="clear"></div>
                </div>
                <div id="misc-publishing-actions">
                    <div class="misc-pub-section misc-pub-priority" id="priority">
                        <?php echo esc_attr__( 'Priority',TM_EPO_TRANSLATION ); ?>: 
                        <input type="number" value="<?php echo (int) $meta['priority']; ?>" maxlength="3" id="tm_meta_priority" name="tm_meta[priority]" class="meta-priority" min="1" step="1" />                                                  
                    </div>                          
                </div>
                <div class="clear"></div>
            </div>
            <div id="major-publishing-actions">
                <div id="delete-action">
                    <?php
                    if ( current_user_can( "delete_post", $post->ID ) ) {
                        if ( !EMPTY_TRASH_DAYS ){
                            $delete_text = __('Delete Permanently', TM_EPO_TRANSLATION);
                        }else{
                            $delete_text = __('Move to Trash', TM_EPO_TRANSLATION);
                        }
                        ?>
                        <a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
                    } ?>
                </div>
                <div id="publishing-action">
                    <span class="spinner"></span>
                    <?php
                    if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
                        if ( $meta['can_publish'] ) : ?>
                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
                    <?php submit_button( __( 'Publish', TM_EPO_TRANSLATION ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
                        <?php   
                        else : ?>
                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
                    <?php submit_button( __( 'Submit for Review', TM_EPO_TRANSLATION ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
                    <?php
                        endif;
                    } else { ?>
                        <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
                        <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update') ?>" />
                    <?php
                    } ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    <?php        
    }

    /**
     *  Pre-render actions
     */
    public function tm_init(){
        if (!isset($_GET['page']) || ($_GET['page'] != 'tm-global-epo')){
            return;
        }
        /* remove cforms plugin tinymce buttons */
        remove_action('init', 'cforms_addbuttons');

    }
    /**
     *  Pre-render actions
     */
    public function tm_admin_init(){
        /**
         *  Custom filters for the edit and delete links.
         */
        add_filter( 'get_edit_post_link', array( $this, 'tm_get_edit_post_link' ),10,3 );
        add_filter( 'get_delete_post_link', array( $this, 'tm_get_delete_post_link' ),10,3 );

        /*
         *  Check if we are on the plugin page
         */
        if (!isset($_GET['page']) || ($_GET['page'] != 'tm-global-epo')){
            return;
        }

        // save meta data
        add_action( 'save_post', array( $this, 'tm_save_postdata' ), 1, 2);

        global $typenow;
        if ( ! $typenow ){
            wp_die( __( 'Invalid post type', TM_EPO_TRANSLATION ) );
        }
        if (!class_exists('WP_List_Table')){
            wp_die( __( 'Something went wrong with WordPress.' , TM_EPO_TRANSLATION) );
        }

        global $bulk_counts,$bulk_messages,$general_messages;
              
        $post_type = $typenow;
        $post_type_object = get_post_type_object( $post_type );
        if ( ! $post_type_object ){
            wp_die( __( 'Invalid post type' , TM_EPO_TRANSLATION) );
        }
        if ( ! current_user_can( $post_type_object->cap->edit_posts ) ){
            wp_die( __( 'Cheatin&#8217; uh?' , TM_EPO_TRANSLATION) );
        }
        
        $this->tm_list_table    = $this->get_wp_list_table('TM_Global_EPO_Admin_List_Table');
        $post_type              = $this->tm_list_table->screen->post_type;
        $pagenum                = $this->tm_list_table->get_pagenum();
        $parent_file            = "edit.php?post_type=product&page=tm-global-epo";
        $submenu_file           = "edit.php?post_type=product&page=tm-global-epo";
        $post_new_file          = "edit.php?post_type=product&page=tm-global-epo&action=add";
        $doaction               = $this->tm_list_table->current_action();
        $sendback               = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'locked', 'ids'), wp_get_referer() );
        if ( ! $sendback ){
            $sendback = admin_url( $parent_file );
        }
        $sendback = add_query_arg( 'paged', $pagenum, $sendback );

        /**
         * Bulk actions
         */
        if ( $doaction && isset($_REQUEST['tm_bulk'])) {           
            check_admin_referer('bulk-posts');
            
            if ( 'delete_all' == $doaction ) {
                $post_status = preg_replace('/[^a-z0-9_-]+/i', '', $_REQUEST['post_status']);
                if ( get_post_status_object($post_status) ){ // Check if the post status exists first
                    global $wpdb;
                    $post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", $post_type, $post_status ) );
                }
                $doaction = 'delete';
            } elseif ( isset( $_REQUEST['ids'] ) ) {
                $post_ids = explode( ',', $_REQUEST['ids'] );
            } elseif ( !empty( $_REQUEST['post'] ) ) {
                $post_ids = array_map('intval', $_REQUEST['post']);
            }
            if ( !isset( $post_ids ) ) {
                wp_redirect( $sendback );
                exit;
            }

            switch ( $doaction ) {
            case 'trash':
                $trashed = $locked = 0;

                foreach( (array) $post_ids as $post_id ) {
                    if ( !current_user_can( 'delete_post', $post_id) ){
                        wp_die( __('You are not allowed to move this item to the Trash.', TM_EPO_TRANSLATION) );
                    }
                    if ( wp_check_post_lock( $post_id ) ) {
                        $locked++;
                        continue;
                    }

                    if ( !wp_trash_post($post_id) ){
                        wp_die( __('Error in moving to Trash.', TM_EPO_TRANSLATION) );
                    }

                    $trashed++;
                }

                $sendback = add_query_arg( array('from_bulk' => 1,'trashed' => $trashed, 'ids' => join(',', $post_ids), 'locked' => $locked ), $sendback );
                break;
            case 'untrash':
                $untrashed = 0;
                foreach( (array) $post_ids as $post_id ) {
                    if ( !current_user_can( 'delete_post', $post_id) ){
                        wp_die( __('You are not allowed to restore this item from the Trash.', TM_EPO_TRANSLATION) );
                    }

                    if ( !wp_untrash_post($post_id) ){
                        wp_die( __('Error in restoring from Trash.', TM_EPO_TRANSLATION) );
                    }

                    $untrashed++;
                }
                $sendback = add_query_arg( array('from_bulk' => 1, 'untrashed' => $untrashed), $sendback );
                break;
            case 'delete':
                $deleted = 0;
                foreach( (array) $post_ids as $post_id ) {
                    $post_del = get_post($post_id);

                    if ( !current_user_can( 'delete_post', $post_id ) ){
                        wp_die( __('You are not allowed to delete this item.', TM_EPO_TRANSLATION) );
                    }

                    if ( $post_del->post_type == 'attachment' ) {
                        if ( ! wp_delete_attachment($post_id) ){
                            wp_die( __('Error in deleting.', TM_EPO_TRANSLATION) );
                        }
                    } else {
                        if ( !wp_delete_post($post_id) ){
                            wp_die( __('Error in deleting.', TM_EPO_TRANSLATION) );
                        }
                    }
                    $deleted++;
                }
                $sendback = add_query_arg( array('from_bulk' => 1, 'deleted' => $deleted), $sendback ) ;
                 
                break;
            case 'edit':
                if ( isset($_REQUEST['bulk_edit']) ) {
                    
                    $done = bulk_edit_posts($_REQUEST);

                    if ( is_array($done) ) {
                        $done['updated'] = count( $done['updated'] );
                        $done['skipped'] = count( $done['skipped'] );
                        $done['locked'] = count( $done['locked'] );
                        $sendback = add_query_arg( $done, $sendback );
                    }
                }
                break;
            }

            $sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view'), $sendback );

            wp_redirect($sendback);
            exit();
        }

        /**
         * Single actions
         */
        elseif ( $doaction && !isset($_REQUEST['tm_bulk'])) { 

            if ( isset( $_GET['post'] ) ){
                $post_id = $post_ID = (int) $_GET['post'];
            }
            elseif ( isset( $_POST['post_ID'] ) ){
                $post_id = $post_ID = (int) $_POST['post_ID'];
            }
            elseif ( isset( $_REQUEST['ids'] ) ){
                $post_id = $post_ID = (int) $_REQUEST['ids'];
            }else{
                $post_id = $post_ID = 0;
            }

            $post = $post_type = $post_type_object = null;

            if ( $post_id )
                $post = get_post( $post_id );

            if ( $post ) {
                $post_type = $post->post_type;
                if ($post_type!=TM_EPO_GLOBAL_POST_TYPE){
                    $edit_link = admin_url( 'post.php?action=edit&post='.$post_id );
                    wp_redirect($edit_link);
                    exit();
                }
                $post_type_object = get_post_type_object( $post_type );
            }           

            switch ( $doaction ) {
            case 'export':
                $this->tm_export_form_action($post_id);

                wp_redirect( add_query_arg( 'message', 21,  remove_query_arg( array('action','post','_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI']) ) ) );
                break;
            case 'clone':

                $this->tm_clone_form_action($post_id);


                 wp_redirect( remove_query_arg( array('action','post','_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI']) ) );
                 exit;

                break;
            case 'trash':
                check_admin_referer('trash-post_' . $post_id);

                if ( ! $post ){
                    wp_die( __( 'The item you are trying to move to the Trash no longer exists.', TM_EPO_TRANSLATION ) );
                }

                if ( ! $post_type_object ){
                    wp_die( __( 'Unknown post type.' , TM_EPO_TRANSLATION) );
                }

                if ( ! current_user_can( 'delete_post', $post_id ) ){
                    wp_die( __( 'You are not allowed to move this item to the Trash.', TM_EPO_TRANSLATION ) );
                }

                if ( $user_id = wp_check_post_lock( $post_id ) ) {
                    $user = get_userdata( $user_id );
                    wp_die( sprintf( __( 'You cannot move this item to the Trash. %s is currently editing.' , TM_EPO_TRANSLATION), $user->display_name ) );
                }

                if ( ! wp_trash_post( $post_id ) ){
                    wp_die( __( 'Error in moving to Trash.' , TM_EPO_TRANSLATION) );
                }

                wp_redirect( add_query_arg( array('trashed' => 1, 'ids' => $post_id), $sendback ) );
                exit();
                break;

            case 'untrash':
                check_admin_referer('untrash-post_' . $post_id);

                if ( ! $post ){
                    wp_die( __( 'The item you are trying to restore from the Trash no longer exists.', TM_EPO_TRANSLATION ) );
                }

                if ( ! $post_type_object ){
                    wp_die( __( 'Unknown post type.', TM_EPO_TRANSLATION ) );
                }

                if ( ! current_user_can( 'delete_post', $post_id ) ){
                    wp_die( __( 'You are not allowed to move this item out of the Trash.', TM_EPO_TRANSLATION ) );
                }

                if ( ! wp_untrash_post( $post_id ) ){
                    wp_die( __( 'Error in restoring from Trash.', TM_EPO_TRANSLATION ) );
                }

                wp_redirect( add_query_arg('untrashed', 1, $sendback) );
                exit();
                break;

            case 'delete':
                check_admin_referer('delete-post_' . $post_id);

                if ( ! $post ){
                    wp_die( __( 'This item has already been deleted.', TM_EPO_TRANSLATION ) );
                }

                if ( ! $post_type_object ){
                    wp_die( __( 'Unknown post type.' , TM_EPO_TRANSLATION) );
                }

                if ( ! current_user_can( 'delete_post', $post_id ) ){
                    wp_die( __( 'You are not allowed to delete this item.', TM_EPO_TRANSLATION ) );
                }

                $force = ! EMPTY_TRASH_DAYS;
                if ( $post->post_type == 'attachment' ) {
                    $force = ( $force || ! MEDIA_TRASH );
                    if ( ! wp_delete_attachment( $post_id, $force ) ){
                        wp_die( __( 'Error in deleting.', TM_EPO_TRANSLATION ) );
                    }
                } else {
                    if ( ! wp_delete_post( $post_id, $force ) ){
                        wp_die( __( 'Error in deleting.', TM_EPO_TRANSLATION ) );
                    }
                }

                wp_redirect( add_query_arg('deleted', 1, $sendback) );
                exit();
                break;
            case 'editpost':
                check_admin_referer('update-post_' . $post_id);

                $post_id = edit_post();

                // Session cookie flag that the post was saved
                if ( isset( $_COOKIE['wp-saving-post-' . $post_id] ) ){
                    setcookie( 'wp-saving-post-' . $post_id, 'saved' );
                }

                $this->redirect_post($post_id);

                exit();
                break;
            case 'edit':
               if ( empty( $post_id ) ) {
                    wp_redirect( admin_url($parent_file) );
                    exit();
                }

                if ( ! $post ){
                    wp_die( __( 'You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?', TM_EPO_TRANSLATION ) );
                }
                if ( ! $post_type_object ){
                    wp_die( __( 'Unknown post type.' , TM_EPO_TRANSLATION) );
                }
                if ( ! current_user_can( 'edit_post', $post_id ) ){
                    wp_die( __( 'You are not allowed to edit this item.' , TM_EPO_TRANSLATION) );
                }

                if ( 'trash' == $post->post_status ){
                    wp_die( __( 'You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.' , TM_EPO_TRANSLATION) );
                }
                break;
            case 'add':
                $post_type = $this->tm_list_table->screen->post_type;
                $post_type_object = get_post_type_object( $post_type );
                if ( ! current_user_can( $post_type_object->cap->edit_posts ) || ! current_user_can( $post_type_object->cap->create_posts ) ){
                    wp_die( __( 'Cheatin&#8217; uh?' , TM_EPO_TRANSLATION) );
                }

                break;

            case 'import':
                $this->import();
                break;
            case 'download':
                $this->download();
                break;
            }
        } elseif ( ! empty($_REQUEST['_wp_http_referer']) ) {
             wp_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI']) ) );
             exit;
        }
        
        /**
         * We get here if we are in the list view.
         */

        $bulk_counts = array(
            'updated'   => isset( $_REQUEST['updated'] )   ? absint( $_REQUEST['updated'] )   : 0,
            'locked'    => isset( $_REQUEST['locked'] )    ? absint( $_REQUEST['locked'] )    : 0,
            'deleted'   => isset( $_REQUEST['deleted'] )   ? absint( $_REQUEST['deleted'] )   : 0,
            'trashed'   => isset( $_REQUEST['trashed'] )   ? absint( $_REQUEST['trashed'] )   : 0,
            'untrashed' => isset( $_REQUEST['untrashed'] ) ? absint( $_REQUEST['untrashed'] ) : 0,
        );

        $bulk_messages = array();
        $bulk_messages[$post_type] = array(
            'updated'   => _n( '%s post updated.', '%s posts updated.', $bulk_counts['updated'] ),
            'locked'    => _n( '%s post not updated, somebody is editing it.', '%s posts not updated, somebody is editing them.', $bulk_counts['locked'] ),
            'deleted'   => _n( '%s post permanently deleted.', '%s posts permanently deleted.', $bulk_counts['deleted'] ),
            'trashed'   => _n( '%s post moved to the Trash.', '%s posts moved to the Trash.', $bulk_counts['trashed'] ),
            'untrashed' => _n( '%s post restored from the Trash.', '%s posts restored from the Trash.', $bulk_counts['untrashed'] ),
        );
        $bulk_counts = array_filter( $bulk_counts );

        $general_messages = array();
        $general_messages[$post_type] = array(
            21   => __('The selected form does not contain any sections',TM_EPO_TRANSLATION),
        );
        $general_messages = array_filter( $general_messages );

    }
    private function redirect_post($post_id = '') {
        $edit_post_link=admin_url( "edit.php?post_type=product&page=tm-global-epo&action=edit&post=$post_id" );
        if ( isset($_POST['save']) || isset($_POST['publish']) ) {
            $status = get_post_status( $post_id );

            if ( isset( $_POST['publish'] ) ) {
                switch ( $status ) {
                    case 'pending':
                        $message = 8;
                        break;
                    case 'future':
                        $message = 9;
                        break;
                    default:
                        $message = 6;
                }
            } else {
                $message = 'draft' == $status ? 10 : 1;
            }

            $location = add_query_arg( 'message', $message, $edit_post_link );

        } else {
            $location = add_query_arg( 'message', 4, $edit_post_link );
        }

        wp_redirect( apply_filters( 'redirect_post_location', $location, $post_id ) );
        
        exit;
    }

    public function tm_get_delete_post_link($url, $post_id, $foce) {
        // check we're in the right place, otherwise return
        if ( !(
               (isset($_GET['page']) && $_GET['page'] == 'tm-global-epo') 
            || (isset($_POST['screen']) && $_POST['screen'] == TM_EPO_GLOBAL_POST_TYPE)
            )) {
            return $url;
        }
        $vars = array();
        $decoded_url = str_replace("&amp;", "&", $url);
        $decoded_url = str_replace("?", "&", $decoded_url);
        wp_parse_str($decoded_url,$vars);
        if (isset($vars['action']) && isset($vars['_wpnonce'])){
            if($vars['action']=='delete'  ){
                $url=admin_url( "edit.php?post_type=product&amp;page=tm-global-epo&amp;action=delete&amp;post=$post_id&amp;_wpnonce=".$vars['_wpnonce'] );
            }
            if($vars['action']=='trash'  ){
                $url=admin_url( "edit.php?post_type=product&amp;page=tm-global-epo&amp;action=trash&amp;post=$post_id&amp;_wpnonce=".$vars['_wpnonce'] );
            }

        }
        return $url;

    }
    public function tm_get_edit_post_link($url, $post_id, $context) {
        // check we're in the right place, otherwise return
        if ( !(
               (isset($_GET['page']) && $_GET['page'] == 'tm-global-epo') 
            || (isset($_POST['screen']) && $_POST['screen'] == TM_EPO_GLOBAL_POST_TYPE)
            )) {
            return $url;
        }
        $vars = array();
        $decoded_url = str_replace("&amp;", "&", $url);
        $decoded_url = str_replace("?", "&", $decoded_url);
        wp_parse_str($decoded_url,$vars);
        if (isset($vars['action'])){
            if($vars['action']=='edit'  ){
                $url=admin_url( "edit.php?post_type=product&amp;page=tm-global-epo&amp;action=edit&amp;post=$post_id" );
            }
        }
        return $url;
    }

    /**
     *  Populate the filter select box.
     */
    public function tm_restrict_manage_posts() {      
        // check we're in the right place, otherwise return
        if (!isset($_GET['page']) || ($_GET['page']!='tm-global-epo')){
            return;
        }        

        global $typenow, $wp_query;

        $output ='';

        $customPostTaxonomies = get_object_taxonomies(TM_EPO_GLOBAL_POST_TYPE);
        $show_option_all = apply_filters( 'list_cats', __('Select a category', TM_EPO_TRANSLATION) );

        if(count($customPostTaxonomies) > 0){
             foreach($customPostTaxonomies as $tax){
                $output .= "<select name='$tax' id='dropdown_$tax'>\n";

                $selected = (isset($wp_query->query[$tax]) && $wp_query->query[$tax]=='') ? '' : 0;
                $selected = ( '' === $selected ) ? " selected='selected'" : '';
                $output .= "\t<option value=''$selected>$show_option_all</option>\n";

                $terms = get_terms( $tax, 'orderby=name&hide_empty=0' );
                foreach ( $terms as $term ) {
                    $selected = (isset($wp_query->query[$tax]) && $wp_query->query[$tax]==$term->slug) ? $term->slug : '';
                    $selected = (  $term->slug === $selected ) ? " selected='selected'" : '';
                    $output .= "\t<option class='level-0' value='".$term->slug."'$selected>".$term->name."</option>\n";
                }
                $output .= "</select>\n";
             }
        }

        echo $output;
    }

    public function tm_add_option() {
        // only continue if we are are on list screen
        if ($this->tm_list_table && $this->tm_list_table->current_action()){
            return;
        }
        $option = 'per_page';
 
        $args = array(
            'label'     => __('Extra Product Options', TM_EPO_TRANSLATION),
            'default'   => 20,
            'option'    => 'tm_per_page'
        );
        add_screen_option( $option, $args );        
    }

    public function tm_set_option($status, $option, $value) {
        if ( 'tm_per_page' == $option ){
            return $value;
        } 
        return $status;
    }

    /**
     * Adds our custom screen id to WooCommerce so that we can load needed WooCommerce files.
     *
     */
    public function woocommerce_screen_ids( $screen_ids ) {
        $screen_ids[] = 'product_page_tm-global-epo';
        return $screen_ids;
    }

    /**
     * Enqueue plugin css and dequeue unwanted woocommerce css styles
     */
    public function register_admin_styles($override=0) {
        if (empty($override)){
            $screen = get_current_screen();
            if($screen->id != 'product_page_tm-global-epo'){
                return;
            }
            //wp_dequeue_style( 'woocommerce_admin_menu_styles' );            
            wp_dequeue_style( 'jquery-ui-style' );
            wp_dequeue_style( 'wp-color-picker' );
            wp_dequeue_style( 'woocommerce_admin_dashboard_styles' );
        }
        wp_enqueue_style( 'tm-font-awesome', $this->plugin_url .'/external/font-awesome/css/font-awesome.min.css', false, '4.1', 'screen' );
        wp_enqueue_style( 'tm_global_epo_animate_css', $this->plugin_url  . '/assets/css/animate.css' );
        wp_enqueue_style( 'tm_global_epo_admin_css', $this->plugin_url  . '/assets/css/admin/tm-global-epo-admin.css' );
        
        wp_enqueue_style( 'tm_global_epo_admin_font', 'http://fonts.googleapis.com/css?family=Roboto:400,100,300,700,900,400italic,700italic' );
    }

    /**
     * Enqueue plugin scripts and dequeue unwanted woocommerce scripts
     */
    public function register_admin_scripts($override=0) {
        global $wp_query, $post;
        if (empty($override)){
            $screen = get_current_screen();
            if($screen->id !='product_page_tm-global-epo'){
                return;
            }
            wp_dequeue_script( 'woocommerce_admin' );
            wp_dequeue_script( 'iris' );
        }    

        wp_register_script( 'tm-modernizr', $this->plugin_url. '/assets/js/modernizr.js', array(   ), '2.8.2' );
        wp_register_script( 'minicolors', $this->plugin_url. '/external/minicolors/jquery.minicolors.js', array('jquery'), '1.0.0');
        wp_enqueue_style( 'minicolors', $this->plugin_url. '/external/minicolors/jquery.minicolors.css', false, '1.0.0', 'screen' );
        wp_register_script( 'tm-scripts', $this->plugin_url . '/assets/js/tm-scripts.js', '', '1.0' );
        
        wp_register_script( 'tm_jquery_widget', $this->plugin_url. '/external/jquery.fileupload/js/vendor/jquery.ui.widget.js', array('jquery'), '1.10.4');
        wp_register_script( 'tm_iframe_transport', $this->plugin_url. '/external/jquery.fileupload/js/jquery.iframe-transport.js', array('jquery'), '1.8.2');
        wp_register_script( 'tm_fileupload', $this->plugin_url. '/external/jquery.fileupload/js/jquery.fileupload.js', array('jquery','tm_jquery_widget','tm_iframe_transport'), '5.41.0');

        wp_register_script( 'tm_global_epo_admin' , $this->plugin_url . '/assets/js/admin/tm-global-epo-admin.js', array( 'jquery','jquery-ui-droppable','jquery-ui-tabs','minicolors','json2' ,'tm-scripts' ,'tm-modernizr', 'tm_fileupload' ), $this->version );
        $import_url = "edit.php?post_type=product&page=tm-global-epo&action=import";                
        $import_url = admin_url( $import_url );
        $params = array(
            'search_products_nonce' => wp_create_nonce("search-products"),
            'settings_nonce'        => wp_create_nonce("settings-nonce"),
            'export_nonce'          => wp_create_nonce("export-nonce"),
            'ajax_url'              => admin_url('admin-ajax.php'),
            'plugin_url'            => $this->plugin_url,
            'delete_style'          => __( 'Are you sure you want to delete this style?', TM_EPO_TRANSLATION ),    
            'builder_delete'        => __( 'Are you sure you want to delete this item?', TM_EPO_TRANSLATION ),
            'builder_clone'         => __( 'Are you sure you want to clone this item?', TM_EPO_TRANSLATION ),
            'update'                => __( 'Update', TM_EPO_TRANSLATION ),
            'i18n_cancel'           => __( 'Cancel', TM_EPO_TRANSLATION ),
            'edit_settings'         => __( 'Edit settings', TM_EPO_TRANSLATION ),
            'i18n_is'                    => __( 'is', TM_EPO_TRANSLATION ),
            'i18n_is_not'                => __( 'is not', TM_EPO_TRANSLATION ),
            'i18n_is_empty'              => __( 'is empty', TM_EPO_TRANSLATION ),
            'i18n_is_not_empty'          => __( 'is not empty', TM_EPO_TRANSLATION ),
            'cannot_apply_rules'    => __( 'Cannot apply rules on this element or section since there are not any value configured elements on other sections, or no other sections found. ', TM_EPO_TRANSLATION ),
            'invalid_request'       => __( 'Invalid request!', TM_EPO_TRANSLATION ),
            'i18n_populate'         => __( 'Populate', TM_EPO_TRANSLATION ),
            'i18n_invalid_extension' => __( 'Invalid file extension', TM_EPO_TRANSLATION ),
            'i18n_importing'        => __( 'Importing csv...', TM_EPO_TRANSLATION ),
            'i18n_saving'           => __( 'Saving... Please wait.', TM_EPO_TRANSLATION ),
            'import_url'            => $import_url,
            'import_title'          =>__( 'Importing data', TM_EPO_TRANSLATION )
        );
        wp_localize_script( 'tm_global_epo_admin', 'tm_epo_admin', $params );
        wp_enqueue_script( 'tm_global_epo_admin' );                   
    }

    /**
     * Init List table class
     */
    private function get_wp_list_table($class="", $args = array()){        
        require_once( 'class-tm-epo-list-table.php' );
        $args['screen'] =  convert_to_screen( TM_EPO_GLOBAL_POST_TYPE );
        return new $class( $args );
    }
    
    public function import_array_merge( $tm_metas,$import ) {
        $clean_import=array();
        if (!isset($tm_metas['tm_meta']['tmfbuilder'])){
            $tm_metas['tm_meta']['tmfbuilder']=array();
        }
        foreach ($import['tm_meta']['tmfbuilder'] as $key => $value) {
            if (!isset($tm_metas['tm_meta']['tmfbuilder'][$key])){
                $tm_metas['tm_meta']['tmfbuilder'][$key]=array();
            }    
            $tm_metas['tm_meta']['tmfbuilder'][$key]=(array_merge($tm_metas['tm_meta']['tmfbuilder'][$key],$value));
        }
        return $tm_metas;
    }

    /**
     * Save our meta data
     */
    public function tm_save_postdata( $post_id,$post_object ) {
        if ( empty($_POST) || !isset($_POST['post_type']) || TM_EPO_GLOBAL_POST_TYPE != $_POST['post_type'] )  {
            return;
        }
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
            return $post_id;
        }
        if ( $post_object->post_type == 'revision' ){
            return;
        }
        check_admin_referer('update-post_' . $post_id);

        if ( ! current_user_can( 'edit_post', $post_id ) ){
            return $post_id;
        }
        
        if (!isset($_SESSION)){
            session_start();
        }
        $import=false;
        if (isset($_SESSION['import_csv'])){
            $import=$_SESSION['import_csv'];
        }

        if ( isset($_POST['tm_meta_serialized'])){
            $tm_metas = $_POST['tm_meta_serialized'];
            $tm_metas = stripslashes($tm_metas);
            $tm_metas = nl2br($tm_metas);

            $tm_metas = json_decode($tm_metas, true);
            if (!empty($import)){                
                $tm_metas=$this->import_array_merge($tm_metas,$import);
                unset($_SESSION['import_csv']);
            }
            if ( !empty($tm_metas) && is_array($tm_metas) && isset($tm_metas['tm_meta']) && is_array($tm_metas['tm_meta'])){
                $tm_meta=$tm_metas['tm_meta'];

                $old_data = get_post_meta($post_id, 'tm_meta',true);
                $this->tm_save_meta($post_id, $tm_meta, $old_data, 'tm_meta');
            }
        }
        if ( isset($_POST['tm_meta_product_ids']) ){
            $old_data = get_post_meta($post_id, 'tm_meta_product_ids',true);
            $this->tm_save_meta($post_id, $_POST['tm_meta_product_ids'], $old_data, 'tm_meta_product_ids');
        }else{
            $old_data = get_post_meta($post_id, 'tm_meta_product_ids',true);
            $this->tm_save_meta($post_id, array(), $old_data, 'tm_meta_product_ids');            
        }
        if ( isset($_POST['tm_meta_disable_categories']) ){
            $old_data = get_post_meta($post_id, 'tm_meta_disable_categories',true);
            $this->tm_save_meta($post_id, $_POST['tm_meta_disable_categories'], $old_data, 'tm_meta_disable_categories');            
        }else{
            $old_data = get_post_meta($post_id, 'tm_meta_disable_categories',true);
            $this->tm_save_meta($post_id, 0, $old_data, 'tm_meta_disable_categories');
        }

    }

    public function tm_save_meta($post_id, $new_data=false, $old_data=false, $meta_name){
        if(empty($old_data) && $old_data==''){
            $test=add_post_meta($post_id, $meta_name, $new_data, true);
            if (!$test){
                $test=update_post_meta($post_id, $meta_name, $new_data, $old_data);
            }
        }else if($new_data===false || (is_array($new_data) && !$new_data)){
            $test=delete_post_meta($post_id, $meta_name);
        }else if($new_data != $old_data){
            $test=update_post_meta($post_id, $meta_name, $new_data, $old_data);
        }
    }

    /**
     * Init List table class
     */
    public function admin_screen() {
        global $bulk_counts,$bulk_messages,$general_messages;

        $post_type          = $this->tm_list_table->screen->post_type;
        $post_type_object   = get_post_type_object( $post_type );

        $parent_file        = "edit.php?post_type=product&page=tm-global-epo";
        $submenu_file       = "edit.php?post_type=product&page=tm-global-epo";
        $post_new_file      = "edit.php?post_type=product&page=tm-global-epo&action=add";   
        
        $doaction           = $this->tm_list_table->current_action();
        if ($doaction){
            $screen = get_current_screen();
            
            // edit screen
            if ($_REQUEST['action']='edit' && (isset($_REQUEST['post']) || isset( $_POST['post_ID'] )) ){
                if ( isset( $_GET['post'] ) ){
                    $post_id = $post_ID = (int) $_GET['post'];
                }elseif ( isset( $_POST['post_ID'] ) ){
                    $post_id = $post_ID = (int) $_POST['post_ID'];
                }
                if (!empty($post_id)){
                    $editing = true;
                    $post = get_post($post_id, OBJECT, 'edit');
                    if ( $post ) {
                        $post_type          = $post->post_type;
                        $post_type_object   = get_post_type_object( $post_type );
                        $title              = $post_type_object->labels->edit_item;
                        $nonce_action       = 'update-post_' . $post_ID;
                        $_meta              = get_post_meta( $post_ID ,'tm_meta');
                        $_meta_product_ids  = get_post_meta( $post_ID ,'tm_meta_product_ids', true);
                        $_meta_disable_categories  = get_post_meta( $post_ID ,'tm_meta_disable_categories', true);
                        $meta_fields        = array(
                            'priority'      => 10,                            
                            'can_publish'   => current_user_can($post_type_object->cap->publish_posts)
                        );

                        $meta = array();
                        foreach ( $meta_fields as $key=>$value ) {
                            $meta[$key] = isset( $_meta[0][ $key ] ) ? maybe_unserialize( $_meta[0][ $key ] ) : $value;

                        }
                        unset($_meta);
                        $meta['product_ids'] = $_meta_product_ids;
                        $meta['disable_categories'] = $_meta_disable_categories;
                        $post->tm_meta=$meta;
                        unset($meta);
                        wp_enqueue_script('post');
                        include ('views/html-tm-epo-fields-edit.php');
                    }                    
                }
            // add screen
            }elseif ($_REQUEST['action']='add' ){
                $post_type = $this->tm_list_table->screen->post_type;
                $post_type_object = get_post_type_object( $post_type );

                $post = get_default_post_to_edit( $post_type, true );
                if ( $post ) {
                    $post_ID        = $post_id = $post->ID;
                    $title          = $post_type_object->labels->add_new;
                    $nonce_action   = 'update-post_' . $post_ID;
                    
                    $_meta = array();
                    $meta_fields = array(
                        'priority' => 10,
                        'can_publish' => current_user_can($post_type_object->cap->publish_posts)
                    );
                    $meta = array();
                    foreach ( $meta_fields as $key=>$value ) {
                        $meta[$key] = isset( $_meta[0][ $key ] ) ? maybe_unserialize( $_meta[0][ $key ] ) : $value;
                    }
                    unset($_meta);
                    $meta['product_ids'] = array();
                    $meta['disable_categories'] = 0;
                    $post->tm_meta=$meta;
                    unset($meta);
                    wp_enqueue_script('post');
                    include ('views/html-tm-epo-fields-edit.php');
                }
            }
        // list screen            
        }else{
            $this->tm_list_table->prepare_items();
            wp_enqueue_script('inline-edit-post');//list
            add_action( 'tm_list_table_action', array( $this, 'tm_list_table_action' ), 10, 2 );
            include ('views/html-tm-epo-fields.php');
        }
    }

    public function tm_list_table_action($action= "", $args=array() ){        
        if ( !$action ){
            return;
        }
        switch ( $action ){
        case "views":
            $this->tm_list_table->views();
            break;
        case "display":
            $this->tm_list_table->display();
            break;
        case "inline_edit":
            if ( $this->tm_list_table->has_items() ){
                $this->tm_list_table->inline_edit();
            }
            break;
        case "search_box":
            $this->tm_list_table->search_box( $args['text'], $args['input_id'] );
            break;
        default:
            break;            
        }
    }
    public function tm_export_form_action($post=0 ){
        require_once( TM_plugin_path.'/admin/tm-csv.php' );
        
        $csv = new TM_CSV();
        $csv->export_by_id($post);

    }
    public function tm_clone_form_action($post=0 ){        
        // Get access to the database
        global $wpdb;
        
        // Check the nonce
        check_ajax_referer( 'tmclone_form_nonce_'.$post, 'security' );
        
        // Get variables
        $original_id  = $post;
        
        // Get the post as an array
        $duplicate = get_post( $original_id, 'ARRAY_A' );

        // Modify some of the elements
        $duplicate['post_title'] = $duplicate['post_title'].' '.__("Copy",TM_EPO_TRANSLATION);
        
        // Set the status
        $duplicate['post_status'] = 'draft';        

        // Set the post date
        $timestamp = current_time('timestamp',0);
        $duplicate['post_date'] = date('Y-m-d H:i:s', $timestamp);

        // Remove some of the keys
        unset( $duplicate['ID'] );
        unset( $duplicate['guid'] );
        unset( $duplicate['comment_count'] );

        // Insert the post into the database
        $duplicate_id = wp_insert_post( $duplicate );
        
        // Duplicate all the taxonomies/terms
        $taxonomies = get_object_taxonomies( $duplicate['post_type'] );
        foreach( $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $original_id, $taxonomy, array('fields' => 'names') );
            wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
        }

        // Duplicate all the custom fields
        $custom_fields = get_post_custom( $original_id );
        foreach ( $custom_fields as $key => $value ) {
            add_post_meta( $duplicate_id, $key, maybe_unserialize($value[0]) );
        }       

    }

}

?>
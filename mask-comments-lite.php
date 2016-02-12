<?php
/*
Plugin Name: Mask Comments Lite
Description: Hide comments of a specific post and show a predefined text instead. In case of you want to hide comments of your blog posts, or show them to registered members only; probably this is the only plugin that can help you achieve this!
Version: 1.0.1
Author: Nazmul Ahsan
Author URI: http://nazmulahsan.me
Stable tag: 1.0.1
License: GPL2+
Text Domain: MedhabiDotCom
*/

class MDC_Mask_Comments_Lite {

    public function __construct() {
        $this->define_constants();
        $this->hooks();
        $this->inc();
    }

    public function hooks(){
        add_action( 'post_submitbox_misc_actions', array( $this, 'is_comment_mask' ) );
        add_action( 'save_post', array( $this, 'save_is_comment_mask' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_filter( 'get_comment_text', array( $this, 'replace_comment_texts' ), 10, 3 );
        add_filter( 'plugins_loaded', array( $this, 'plugin_textdomain' ), 10, 3 );
    }

    public function define_constants(){
        define('MDC_MASK_PLUGIN_URL', plugin_dir_url( __FILE__ ));
        define('MDC_MASK_PRO_URL', 'http://medhabi.com/product/mask-comments-pro');
    }

    public function inc(){
        require dirname(__FILE__) . '/admin/mdc-mask-comments-settings.php';
    }

    public function admin_enqueue_scripts(){
        wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
        wp_enqueue_style( 'mask-timepicker-addon', MDC_MASK_PLUGIN_URL. 'assets/css/jquery-ui-timepicker-addon.css');   
        wp_enqueue_style( 'mdc-comment-mod-style', MDC_MASK_PLUGIN_URL. 'assets/css/admin.css', '', '1.0.0', 'all' );
        
        wp_enqueue_script( 'jquery-ui', MDC_MASK_PLUGIN_URL. 'assets/js/jquery-ui.js');
        wp_enqueue_script( 'jquery-time-picker' ,  MDC_MASK_PLUGIN_URL. 'assets/js/jquery-ui-timepicker-addon.js',  array('jquery' ));    
        wp_enqueue_script( 'mdc-comment-mod-script', MDC_MASK_PLUGIN_URL. 'assets/js/admin.js', 'jquery', '1.0.0', false );
    }

    public function is_comment_mask() {
        global $post;
        if ( isset(get_option('mask_general')['post_types']) && in_array( get_post_type($post), get_option('mask_general')['post_types']) ){
            
            if( null != get_post_meta( $post->ID, '_is_comment_mask', true ) ){
                $val = get_post_meta( $post->ID, '_is_comment_mask', true );
            } elseif( isset(get_option('mask_defaults')['enable']) ){
                $val = get_option('mask_defaults')['enable'];
            } else{
                $val = 'No';
            }

            $comment_mask_msg = __('This comment is temporarily hidden by the post author!', 'MedhabiDotCom');
            $comment_mask_till = get_date_from_gmt( date( 'Y-m-d H:i:s', time()+31536000 ), 'm/d/Y H:i' );
            
            $output = '';
            wp_nonce_field( plugin_basename(__FILE__), 'is_comment_mask_nonce' );
            ?>
            <div id="comm-mod" class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">
                Mask Comments: <span id="post-comment-mask-display"><?php echo $val; ?></span>
                <a class="edit-comment-mask hide-if-no-js" href="#comment-mask">
                    <span aria-hidden="true" class="change-mod-settings"><?php _e('Edit', 'MedhabiDotCom');?></span>
                    <span class="screen-reader-text"><?php _e('Edit comment-mask', 'MedhabiDotCom');?></span>
                </a>
                <div class="comm-mod-setting" style="display:none">
                    <p>
                        <input type="radio" name="is_comment_mask" class="is_comment_mask" id="is_comment_mask-No" value="No" <?php echo checked($val,'No',false); ?> /> <label for="is_comment_mask-No" class="select-it"><?php _e('No', 'MedhabiDotCom');?></label><br />
                        <input type="radio" name="is_comment_mask" class="is_comment_mask" id="is_comment_mask-Yes" value="Yes" <?php echo checked($val,'Yes',false); ?>  /> <label for="is_comment_mask-Yes" class="select-it"><?php _e('Yes', 'MedhabiDotCom');?></label>
                    </p>
                    <div class="comm-mod-inline-settings" style="display:none">
                        <label for="mask-text"><?php _e('Mask text', 'MedhabiDotCom'); echo  ' (<span><a href="'.MDC_MASK_PRO_URL.'" target="_blank">Pro Feature</a></span>)';;?></label><textarea id="mask-text" name="comment_mask_msg" readonly><?php echo $comment_mask_msg; ?></textarea><br />
                        <label for="mask-till"><?php _e('Keep masked till', 'MedhabiDotCom'); echo  ' (<span><a href="'.MDC_MASK_PRO_URL.'" target="_blank">Pro Feature</a></span>)';;?></label><input id="mask-till" type="date" name="comment_mask_till" value="<?php echo $comment_mask_till; ?>" readonly >
                    </div>
                    <p>
                        <a class="save-post-comment-mask hide-if-no-js button" href="#comment-mask"><?php _e('OK', 'MedhabiDotCom');?></a>
                        <a class="cancel-post-comment-mask hide-if-no-js button-cancel" href="#comment-mask"><?php _e('Cancel', 'MedhabiDotCom');?></a>
                    </p>
                </div>
            </div>
            <?php
        }
    }

    public function save_is_comment_mask($post_id) {

        if ( !isset($_POST['post_type']) ){
            return $post_id;
        }

        if ( !wp_verify_nonce( $_POST['is_comment_mask_nonce'], plugin_basename(__FILE__) ) ){
            return $post_id;
        }

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
            return $post_id;
        }

        if ( isset(get_option('mask_general')['post_types']) && in_array( get_post_type($post), get_option('mask_general')['post_types']) && !current_user_can( 'edit_post', $post_id ) ){
            return $post_id;
        }

        if (!isset($_POST['is_comment_mask'])){
            return $post_id;
        }
        else {
            update_post_meta( $post_id, '_is_comment_mask', $_POST['is_comment_mask'], get_post_meta( $post_id, '_is_comment_mask', true ) );
        }

    }

    public function replace_comment_texts( $text, $comment, Array $args ) {
        global $post;
        $is_moderate = get_post_meta( $comment->comment_post_ID, '_is_comment_mask', true );

        $mask = __( 'This comment is temporarily hidden by the post author!', 'MedhabiDotCom' );
        
        if( $is_moderate == 'Yes' ){
            // mask to logged out users
            if( !is_user_logged_in() ){
                return $mask;
            }
            // mask to logged in users other than commenter or post author
            if( get_current_user_id() != $comment->user_id && get_current_user_id() != $post->post_author ){
                return $mask;
            }
        }

        return $text;
    }

    // i18n
    public function plugin_textdomain() {
        load_plugin_textdomain( 'MedhabiDotCom', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' ); 
    }

    // above this line please
}

if ( ! class_exists('MDC_Mask_Comments_Pro') ) {
    new MDC_Mask_Comments_Lite;
}
<?php
/*
Plugin Name: Mask Comments
Description: Hide comments of a specific post and show a predefined text instead.
Version: 0.0.1
Author: Nazmul Ahsan
Author URI: http://nazmulahsan.me
Stable tag: 0.0.1
License: GPL2+
Text Domain: MedhabiDotCom
*/

class MDC_Mask_Comments {
    
    public function __construct() {
        add_action( 'post_submitbox_misc_actions', array( $this, 'is_comment_mask' ) );
        add_action( 'save_post', array( $this, 'save_is_comment_mask' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_filter( 'get_comment_text', array( $this, 'replace_comment_texts' ), 10, 3 );
    }

    public function admin_enqueue_scripts(){
        wp_enqueue_script( 'mdc-comment-mod-script', plugins_url('assets/admin.js', __FILE__), 'jquery', '1.0.0', false );
        wp_enqueue_style( 'mdc-comment-mod-style', plugins_url('assets/admin.css', __FILE__), '', '1.0.0', 'all' );
    }

    public function is_comment_mask() {
        global $post;
        if ( get_post_type($post) == 'post' ) {
            $val = get_post_meta( $post->ID, '_is_comment_mask', true ) ? get_post_meta( $post->ID, '_is_comment_mask', true ) : 'No';
            $output = '';
            wp_nonce_field( plugin_basename(__FILE__), 'is_comment_mask_nonce' );
            $output .= '
                <div id="comm-mod" class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">
                    Mask Comments: <span id="post-comment-mask-display">'.$val.'</span>
                    <a class="edit-comment-mask hide-if-no-js" href="#comment-mask">
                        <span aria-hidden="true" class="change-mod-settings">Edit</span>
                        <span class="screen-reader-text">Edit comment-mask</span>
                    </a>
                    <div class="comm-mod-setting" style="display:none">
                        <input type="radio" name="is_comment_mask" class="is_comment_mask" id="is_comment_mask-Yes" value="Yes" '.checked($val,'Yes',false).' /> <label for="is_comment_mask-Yes" class="select-it">Yes</label><br />
                        <input type="radio" name="is_comment_mask" class="is_comment_mask" id="is_comment_mask-No" value="No" '.checked($val,'No',false).'/> <label for="is_comment_mask-No" class="select-it">No</label>
                        <p>
                            <a class="save-post-comment-mask hide-if-no-js button" href="#comment-mask">OK</a>
                            <a class="cancel-post-comment-mask hide-if-no-js button-cancel" href="#comment-mask">Cancel</a>
                        </p>
                    </div>
                </div>';

            echo $output;
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

        if ( 'post' == $_POST['post_type'] && !current_user_can( 'edit_post', $post_id ) ){
            return $post_id;
        }

        if (!isset($_POST['is_comment_mask'])){
            return $post_id;
        }
        else {
            $mydata = $_POST['is_comment_mask'];
            update_post_meta( $post_id, '_is_comment_mask', $_POST['is_comment_mask'], get_post_meta( $post_id, '_is_comment_mask', true ) );
        }

    }

    public function replace_comment_texts( $text, $comment, Array $args ) {
        global $post;
        $is_moderate = get_post_meta( $comment->comment_post_ID, '_is_comment_mask', true );
        $mask = __( 'This comment is temporarily hidden by the post author!', 'MedhabiDotCom' );
        if( $is_moderate == 'Yes' ){
            if( !is_user_logged_in() ){
                return $mask;
            }
            else{
                if( get_current_user_id() != $comment->user_id && get_current_user_id() != $post->post_author ){
                    return $mask;
                }
            }
        }

        return $text;
    }

    // above this line please
}

new MDC_Mask_Comments;
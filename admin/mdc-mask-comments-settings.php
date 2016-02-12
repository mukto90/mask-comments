<?php

/**
 * @author Nazmul Ahsan <n.mukto@gmail.com>
 */
require_once dirname( __FILE__ ) . '/class.settings-api.php';

if ( !class_exists('MDC_Mask_Comments_Settings' ) ):
class MDC_Mask_Comments_Settings {

    private $settings_api;

    function __construct() {
        $this->settings_api = new WeDevs_Settings_API;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_menu_page( 'Mask Comments Settings', 'Mask Comments', 'manage_options', 'mask-comments-settings', array($this, 'mask_comments_settings_page'), 'dashicons-welcome-comments', '25.50' );
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'mask_general',
                'title' => __( 'General Settings', 'MedhabiDotCom' )
            ),
            array(
                'id' => 'mask_defaults',
                'title' => __( 'Default Settings', 'MedhabiDotCom' )
            )
        );
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'mask_general' => array(
                array(
                    'name'    => 'user_levels',
                    'label'   => __( 'Who can mask comments', 'MedhabiDotCom' ) . ' (<span><a href="'.MDC_MASK_PRO_URL.'" target="_blank">Pro Feature</a></span>)',
                    'desc'    => __( 'Users who can enable/disable comment mask of a post.', 'MedhabiDotCom' ),
                    'type'    => 'multicheck',
                    'options' => $this->user_levels()
                ),
                array(
                    'name'    => 'always_show',
                    'label'   => __( 'Always show comments to', 'MedhabiDotCom' ) . ' (<span><a href="'.MDC_MASK_PRO_URL.'" target="_blank">Pro Feature</a></span>)',
                    'desc'    => __( 'Force comments to be visible to a user from these user roles. Even if the user is not the author of that post nor the commenter!', 'MedhabiDotCom' ),
                    'type'    => 'multicheck',
                    'options' => $this->user_levels()
                ),
                array(
                    'name'    => 'post_types',
                    'label'   => __( 'Post Types', 'MedhabiDotCom' ),
                    'desc'    => __( 'Allowed post types to mask comments of.', 'MedhabiDotCom' ),
                    'type'    => 'multicheck',
                    'options' => $this->post_types()
                )
            ),
            'mask_defaults' => array(
                array(
                    'name'    => 'text',
                    'label'   => __( 'Mask Text', 'MedhabiDotCom' ) . ' (<span><a href="'.MDC_MASK_PRO_URL.'" target="_blank">Pro Feature</a></span>)',
                    'desc'    => __( 'Default text to be used as a comment mask. This can be overridden by post author(s) for each post individually.', 'MedhabiDotCom' ),
                    'type'    => 'textarea',
                    'default' => 'This comment is temporarily hidden by the post author!'
                ),
                array(
                    'name'    => 'enable',
                    'label'   => __( 'Mask by default', 'MedhabiDotCom' ),
                    'desc'    => __( 'Comment mask will be enabled for new posts automatically. This can be overridden by post author(s) for each post individually.', 'MedhabiDotCom' ),
                    'type'    => 'radio',
                    'options' => array(
                        'No'    =>  'No',
                        'Yes'    =>  'Yes',
                    ),
                    'default' => 'No'
                ),
            ),
        );

        return $settings_fields;
    }

    function mask_comments_settings_page() {
        echo '<div class="wrap mdc-mask-comment free-version">';
        
        echo '<h2><i class="title-dashicon dashicons-before dashicons-welcome-comments hidden"></i>Mask Comment Lite</h2>';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

    public function user_levels(){
        global $wp_roles;
        $roles = $wp_roles->get_names();
        $user_levels = array();
        foreach ($roles as $role_id => $role_name) {
            $user_levels[$role_id]  =  $role_name;
        }
        return $user_levels;
    }

    public function post_types(){
        $args = array(
            'public'    =>  true
            );
        $all_post_types = get_post_types($args);
        $post_types = array();
        foreach ($all_post_types as $cpt_id => $cpt_name) {
            if( 'attachment' != $cpt_name ){    // exclude attachments
                $post_types[$cpt_id]  =  $cpt_name;
            }
        }
        return $post_types;
    }

}
endif;

new MDC_Mask_Comments_Settings();
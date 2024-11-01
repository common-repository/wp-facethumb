<?php

class WP_FaceThumb_Admin {

    private $settings_tabs = array();

    function __construct() {

        add_action('admin_init', array(&$this, 'register_settings'));
        add_action('admin_menu', array(&$this, 'add_admin_menus'));

        $this->wp_facethumb_settings = (array) get_option('wp_facethumb_settings');
    }

    /**
     *  Register the settings 
     */
    function register_settings() {
        $this->settings_tabs['general_settings'] = 'General Settings';
        $this->settings_tabs['manage_gallery'] = 'Manage Gallery';

        register_setting('wp_facethumb_settings', 'wp_facethumb_settings');
        add_settings_section('wp-facethumb-options', 'General Settings', array(&$this, 'generate_fields'), 'wp_facethumb_settings');
        add_settings_field('column', __('<strong>Number of column(s) for the public gallery</strong><br/>You can use this setting to the gallery fit your theme', 'WP-Facethumb'), array(&$this, 'generate_fields'), 'wp_facethumb_settings', 'wp-facethumb-options', array('label_for' => 'column'));
        add_settings_field('line', __('<strong>Number of line(s) for the gallery</strong><br/>You can use this setting to the gallery fit your theme', 'WP-Facethumb'), array(&$this, 'generate_fields'), 'wp_facethumb_settings', 'wp-facethumb-options', array('label_for' => 'line'));
        add_settings_field('link', __('<strong>Choose where to display links</strong>', 'WP-Facethumb'), array(&$this, 'generate_fields'), 'wp_facethumb_settings', 'wp-facethumb-options', array('label_for' => 'link'));
        add_settings_field('moderate', __('<strong>Moderate thumbs</strong><br/>Simply select yes if you want to validate the pictures before they become publicly visible', 'WP-Facethumb'), array(&$this, 'generate_fields'), 'wp_facethumb_settings', 'wp-facethumb-options', array('label_for' => 'moderate'));
        add_settings_field('widget_thumb', __('<strong>Photo to display in widget</strong><br/>Select if you want to show the last thumb or a random one', 'WP-Facethumb'), array(&$this, 'generate_fields'), 'wp_facethumb_settings', 'wp-facethumb-options', array('label_for' => 'widget_thumb'));
        add_settings_field('ajax', __('<strong>Load the widget using ajax</strong><br/>Select if you are using a cache plugin like WP Super Cache or W3 Total Cache. If you don\'t know what is cache you should select no', 'WP-Facethumb'), array(&$this, 'generate_fields'), 'wp_facethumb_settings', 'wp-facethumb-options', array('label_for' => 'ajax'));
        add_settings_field('thumbs_per_page', __('<strong>Number of thumbs per page in admin panel</strong><br/>This setting applies to the Manage Gallery tab', 'WP-Facethumb'), array(&$this, 'generate_fields'), 'wp_facethumb_settings', 'wp-facethumb-options', array('label_for' => 'thumbs_per_page'));
        add_settings_field('support_us', __('<strong>Support us</strong><br/>By selecting yes this will create a link on the widget to help us to be more famous', 'WP-Facethumb'), array(&$this, 'generate_fields'), 'wp_facethumb_settings', 'wp-facethumb-options', array('label_for' => 'support_us'));
        add_settings_field('gallery_id', __('<strong>Gallery ID</strong><br/>Don\'touch this unless you know exactly what you\'re doing!', 'WP-Facethumb'), array(&$this, 'generate_fields'), 'wp_facethumb_settings', 'wp-facethumb-options', array('label_for' => 'gallery_id'));
        }

    /* Generate the different fields */

    function generate_fields($field) {
        switch ($field['label_for']) {

            case 'column':
                $link = array('1', '2', '3', '4', '5', '6');
                echo '<select name="wp_facethumb_settings[column]">';
                foreach ($link as $value) {
                    ?><option value="<?php echo $value ?>"<?php if ($this->wp_facethumb_settings['column'] == $value) {
                        echo 'selected="selected"';
                    } ?>/><?php _e("$value", 'WP-FaceThumb') ?><br/><?php
                }
                break;

            case 'line':
                $link = array('1', '2', '3', '4', '5', '6');
                echo '<select name="wp_facethumb_settings[line]">';
                foreach ($link as $value) {
                    ?><option value="<?php echo $value ?>"<?php if ($this->wp_facethumb_settings['line'] == $value) {
                        echo 'selected="selected"';
                    } ?>/><?php _e("$value", 'WP-FaceThumb') ?><br/><?php
                }
                break;

            case 'link':
                $link = array('gallery', 'widget', 'both', 'none');
                echo '<select name="wp_facethumb_settings[link]">';
                foreach ($link as $value) {
                    ?><option value="<?php echo $value ?>"<?php if ($this->wp_facethumb_settings['link'] == $value) {
                        echo 'selected="selected"';
                    } ?>/><?php _e("$value", 'WP-FaceThumb') ?><br/><?php
                }
                break;

            case 'moderate':
                $link = array('yes', 'no');
                echo '<select name="wp_facethumb_settings[moderate]">';
                foreach ($link as $value) {
                    ?><option value="<?php echo $value ?>"<?php if ($this->wp_facethumb_settings['moderate'] == $value) {
                        echo 'selected="selected"';
                    } ?>/><?php _e("$value", 'WP-FaceThumb') ?><br/><?php
                }
                break;

            case 'widget_thumb':
                $link = array('last', 'random');
                echo '<select name="wp_facethumb_settings[widget_thumb]">';
                foreach ($link as $value) {
                    ?><option value="<?php echo $value ?>"<?php if ($this->wp_facethumb_settings['widget_thumb'] == $value) {
                        echo 'selected="selected"';
                    } ?>/><?php _e("$value", 'WP-FaceThumb') ?><br/><?php
                }
                break;

            case 'ajax':
                $link = array('yes', 'no');
                echo '<select name="wp_facethumb_settings[ajax]">';
                foreach ($link as $value) {
                    ?><option value="<?php echo $value ?>"<?php if ($this->wp_facethumb_settings['ajax'] == $value) {
                        echo 'selected="selected"';
                    } ?>/><?php _e("$value", 'WP-FaceThumb') ?><br/><?php
                }
                break;

            case 'thumbs_per_page':
                $link = array('5', '10', '15', '20', '25', '30');
                echo '<select name="wp_facethumb_settings[thumbs_per_page]">';
                foreach ($link as $value) {
                    ?><option value="<?php echo $value ?>"<?php if ($this->wp_facethumb_settings['thumbs_per_page'] == $value) {
                        echo 'selected="selected"';
                    } ?>/><?php _e("$value", 'WP-FaceThumb') ?><br/><?php
                }
                break;
                
            case 'support_us':
                $link = array('yes', 'no');
                echo '<select name="wp_facethumb_settings[support_us]">';
                foreach ($link as $value) {
                    ?><option value="<?php echo $value ?>"<?php if ($this->wp_facethumb_settings['support_us'] == $value) {
                        echo 'selected="selected"';
                    } ?>/><?php _e("$value", 'WP-FaceThumb') ?><br/><?php
                }
                break;

            case 'gallery_id':
                ?><input value="<?php echo $this->wp_facethumb_settings['gallery_id']; ?>" name="wp_facethumb_settings[gallery_id]" size="3"><?php
                break;
        }
    }

    function add_admin_menus() {
        add_options_page('WP-FaceThumb Settings', 'WP-FaceThumb', 'manage_options', 'wp_facethumb_options', array(&$this, 'wp_facethumb_options_page'));
    }

    function wp_facethumb_options_page() {
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'general_settings';
        if ($tab == 'general_settings') {
            echo '<div class="wrap">';
            $this->options_tabs();
            echo '<form method="post" action="options.php">';
            wp_nonce_field('update-options');
            settings_fields('wp_facethumb_settings');
            do_settings_sections('wp_facethumb_settings');
            submit_button();
            echo '</form>';
            echo '</div>';
        }
        if ($tab == 'manage_gallery') {
            echo '<div class="wrap">';
            $this->options_tabs();
            echo '<form id="entries-gallery" method="post" action="">';
            $this->print_admin_gallery();
            echo '</form>';
            echo '</div>';
        }
    }

    function options_tabs() {
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general_settings';

        screen_icon();
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($this->settings_tabs as $tab_key => $tab_caption) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . 'wp_facethumb_options' . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
        }
        echo '</h2>';
    }

    function print_admin_gallery() {
        require_once 'WP_FaceThumb_Gallery_Admin.php';
        $thumbs_list = new WP_FaceThumb_Gallery_Admin();
        $thumbs_list->prepare_items();
        $thumbs_list->display();
    }

}
?>

<?php

/*
  Plugin Name: WP-FaceThumb
  Plugin URI: http://wordpress.org/extend/plugins/wp-facethumb/
  Description: WP-FaceThumb allows your visitors to take a picture with their webcam and post it on a widget to your blog.
  Version: 1.0
  Author: MNT-TECH
  Author URI: http://www.mnt-tech.fr/
 */

class WP_FaceThumb {

    private $wp_facethumb_settings = array();
    private $wp_facethumb_version = '1.0';

    function __construct() {

        /* Actions */
        add_action('init', array(&$this, 'load_settings'));
        add_action('init', array(&$this, 'load_admin'));
        add_action('init', array(&$this, 'new_thumb'));
        add_action('widgets_init', array(&$this, 'widget_init'));
        add_action('wp_ajax_nopriv_html_thickbox', array($this, 'html_thickbox'));
        add_action('wp_ajax_html_thickbox', array($this, 'html_thickbox'));


        /* Registers */
        register_activation_hook(__FILE__, array(&$this, 'facethumb_install'));

        /* Shortcodes */
        add_shortcode('WPFT', array(&$this, 'manage_shortcode'));

        /* Translation */
        load_plugin_textdomain('WP-FaceThumb', false, dirname(plugin_basename(__FILE__)) . '/lang/');
    }

    function html_thickbox() {
        $upload = site_url() . '/';
        $swf = plugins_url() . '/wp-facethumb/swf/wp_facethumb.swf';
        $div_content = '<script type="text/javascript">';
        $div_content .= 'var flashvars = {};';
        $div_content .= 'flashvars.upload = "' . $upload . '";';
        $div_content .= 'var params = {};';
        $div_content .= 'params.allowScriptAccess = "always";';
        $div_content .= 'swfobject.embedSWF("' . $swf . '", "WP-FaceThumb", "500", "400", "10.0.0", "expressInstall.swf", flashvars, params);';
        $div_content .= '</script>';
        $div_content .= '<style type="text/css">body {margin:0px;text-align:center;}</style>';
        $div_content .= '<div id="WP-FaceThumb">';
        $div_content .= '<h1>' . __('This Plugin requires Flash Player', 'WP-FaceThumb') . '</h1>';
        $div_content .= '<p><a href = "http://www.adobe.com/go/getflashplayer"><img src = "http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt = "Get Adobe Flash player" /></a></p>';
        $div_content .= '</div>';
        $div_id = '#wp-facethumb-container';
        //the array to send via ajax
        $array_response = array("div_id" => $div_id, "div_content" => $div_content);
        //generate the response
        $response = json_encode($array_response);
        //response output
        header("Content-Type: application/json");
        echo $response;
        exit;
    }

    /**
     * Load settings in an array  
     */
    function load_settings() {
        $this->wp_facethumb_settings = (array) get_option('wp_facethumb_settings');
    }

    /**
     * Load admin panel 
     */
    function load_admin() {
        require_once 'classes/WP_FaceThumb_Admin.php';
        $wp_facethumb_admin = new WP_FaceThumb_Admin();
    }

    /**
     * Upload and store the thumb in database  
     */
    function new_thumb() {

        if (isset($_GET['pseudo']) AND isset($_GET['link'])) {

            $link = mysql_real_escape_string(htmlspecialchars($_GET['link']));
            $pseudo = mysql_real_escape_string(htmlspecialchars($_GET['pseudo']));

            /* Will determine the name of the thumb and the time it was taken */
            $time = time();
            $thumb_url = plugins_url() . '/wp-facethumb/uploads/' . $time . '.jpg';
            $thumb_location = WP_PLUGIN_DIR . '/wp-facethumb/uploads/' . $time . '.jpg';
            $thumb = file_get_contents('php://input');
            file_put_contents($thumb_location, $thumb);

            /* Check if the finfo functions exists on the system */
            /* If not, it uses the deprecated mime_content_type function */
            /* If nether mime_content_type exists we set the mime_type */
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $thumb_mime = finfo_file($finfo, $thumb_location);
                finfo_close($finfo);
            } else if (function_exists('mime_content_type')) {
                $thumb_mime = mime_content_type($thumb_location);
            } else {
                $thumb_mime = "image/jpeg";
            }

            /* For security we check the mime type of uploaded file */
            if ($thumb_mime == "image/jpeg") {
                global $wpdb;
                $table = $wpdb->prefix . "facethumb";

                /* Check if the moderate function is activate or not in the settings */
                if ($this->wp_facethumb_settings['moderate'] == 'yes') {
                    $moderate = '0';
                } else {
                    $moderate = '1';
                }

                $wpdb->query("INSERT INTO $table VALUES ('', '$time', '$pseudo', '$thumb_url', '$link', '$moderate')");
            } else {
                unlink($thumb_location);
            }
        }
    }

    /**
     * This function handle the plugin installation : table creation, settings creation, set the db version...
     */
    function facethumb_install() {

        /* load the settings registred if the plugin was previously installed */
        $wp_facethumb_settings = (array) get_option('wp_facethumb_settings');

        /* the array of delault settings is merged with possible previous saved settings */
        $wp_facethumb_settings = array_merge(array(
            'column' => '3',
            'line' => '5',
            'widget_thumb' => 'last',
            'link' => 'both',
            'ajax' => 'no',
            'moderate' => 'no',
            'thumbs_per_page' => '10',
            'support_us' => 'no'
                ), $wp_facethumb_settings);

        /* Store or update the settings */
        update_option('wp_facethumb_settings', $wp_facethumb_settings);


        /* Explicitly set the character set and collation when creating the tables */
        $charset = ( defined('DB_CHARSET' && '' !== DB_CHARSET) ) ? DB_CHARSET : 'utf8';
        $collate = ( defined('DB_COLLATE' && '' !== DB_COLLATE) ) ? DB_COLLATE : 'utf8_general_ci';


        /* Database information */
        global $wpdb;
        $table = $wpdb->prefix . "facethumb";
        $posts = $wpdb->prefix . "posts";

        /* Table structure */
        $sql = "CREATE TABLE " . $table . " (
					id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					time BIGINT(11) NOT NULL,
					pseudo tinytext NOT NULL,
					thumb varchar(100) NOT NULL,
					link varchar(100) DEFAULT 'none' NOT NULL,
                                        display tinyint(1) NOT NULL,
					UNIQUE KEY id (id)
                   )DEFAULT CHARACTER SET $charset COLLATE $collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        /* Create or Update database table */
        dbDelta($sql);

        /* Store the db version */
        add_option("wp_facethumb_db_version", "$this->wp_facethumb_version");

        /* check if a page contains the shortcode [WPFT] */
        $first_install = $wpdb->get_var("SELECT ID FROM $posts WHERE post_content='[WPFT]'");
        if (!($first_install > 0)) {
            $page = array(
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_content' => '[WPFT]',
                'post_name' => 'wp-facethumb-gallery',
                'post_status' => 'publish',
                'post_title' => 'WP-FaceThumb Gallery',
                'post_type' => 'page',
            );

            /* Post the page and store its id */
            $gallery_id = wp_insert_post($page);

            /* Store the id in the options */
            $wp_facethumb_settings = (array) get_option('wp_facethumb_settings');
            $wp_facethumb_settings['gallery_id'] = $gallery_id;
            update_option('wp_facethumb_settings', $wp_facethumb_settings);

            /* Add the first thumb, time() will be used for the name and arrival time of the thumb */
            $time = time();

            /* Directory where the thumbs are located */
            $dir = WP_PLUGIN_DIR . '/wp-facethumb/uploads/';

            /* Rename the first thumb to respect the naming convention */
            rename($dir . 'mnt-tech.jpg', $dir . $time . '.jpg');

            /* The thumb url */
            $thumb = plugins_url() . '/wp-facethumb/uploads/' . $time . '.jpg';

            /* Insert the first entry in the table */
            $wpdb->query("INSERT INTO $table VALUES ('', '$time', 'WP-FaceThumb', '$thumb', 'http://wordpress.org/extend/plugins/wp-facethumb/', '1')");
        }
    }

    /**
     * Manage shortcode
     */
    function manage_shortcode($atts) {
        extract(shortcode_atts(array(
                    'line' => 5,
                    'column' => 3,
                        ), $atts));
        return $this->print_front_gallery();
    }

    /**
     * Print the front end gallery
     */
    function print_front_gallery() {
        require_once 'classes/WP_FaceThumb_Gallery.php';
        $thumbs_gallery = new WP_FaceThumb_Gallery();
        $thumbs_gallery->display_thumbs();
        $thumbs_gallery->enqueue_style();
    }

    /**
     * Start the widget 
     */
    function widget_init() {
        require_once 'classes/WP_FaceThumb_Widget.php';
        register_widget('WP_FaceThumb_Widget');
    }

}

/* Start WP-FaceThumb */
$wp_facethumb = new WP_FaceThumb();
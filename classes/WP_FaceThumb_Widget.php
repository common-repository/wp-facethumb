<?php

class WP_FaceThumb_Widget extends WP_Widget {

    private $wp_facethumb_settings = array();

    function __construct() {
        /* Load settings */
        $this->load_settings();

        if ($this->wp_facethumb_settings['ajax'] == 'yes') {
            //condition to don't load script in admin panel
            if (!is_admin()) {
                //add jquery to the header
                wp_enqueue_script('jquery');
            }
            //declare which function handle the ajax request
            add_action('wp_ajax_nopriv_refresh', array($this, 'refresh'));
            add_action('wp_ajax_refresh', array($this, 'refresh'));
        }


        $widget_ops = array('classname' => 'wp-facethumb-widget', 'description' => __('Displays a picture taken by some of your visitor', 'WP-FaceThumb'));
        $this->WP_Widget('wp-facethumb-widget', 'WP-FaceThumb', $widget_ops);
    }

    function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
    }

    function load_settings() {
        $this->wp_facethumb_settings = (array) get_option('wp_facethumb_settings');
    }

    function widget($args, $instance) {

        /* Scripts and CSS are loaded only when the widget is displayed */
        $this->enqueue_scripts();

        extract($args);
        echo $before_widget;
        $title = apply_filters('widget_title', $instance['title']);
        if (!empty($title)) {
            if ($this->wp_facethumb_settings['support_us'] == 'yes') {
                echo $args['before_title'] . '<a href="http://www.mnt-tech.fr/wp-facethumb/">' . $title . '</a>' . $args['after_title'];
            } else {
                echo $args['before_title'] . $title . $args['after_title'];
            }
        } else {
            if ($this->wp_facethumb_settings['support_us'] == 'yes') {
                echo $args['before_title'] . '<a href="http://www.mnt-tech.fr/wp-facethumb/">' . 'WP-FaceThumb' . '</a>' . $args['after_title'];
            } else {
                echo $args['before_title'] . 'WP-FaceThumb' . $args['after_title'];
            }
        }

        /* Print the widget content directly */
        if ($this->wp_facethumb_settings['ajax'] == 'no') {
            $this->refresh();

            /* Print the js wich will fill the widget via ajax */
        } else {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery.post(
            <?php echo '"' . admin_url('admin-ajax.php') . '"'; ?>,
                    {action : 'refresh'},
                    function( response ) {
                        jQuery(response.widget_id).append(response.widget_content);
                    }
                );
                });
            </script>
            <?php
        }
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = strip_tags($new_instance['title']);

        return $instance;
    }

    function form($instance) {
        /* TODO : add two options here, Load using ajax and what thumb to display */
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = 'WP-FaceThumb';
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <?php
    }

    function refresh() {

        global $wpdb;
        $table = $wpdb->prefix . "facethumb";

        if ($this->wp_facethumb_settings['widget_thumb'] == 'random') {
            $thumb = $wpdb->get_row("SELECT * FROM $table WHERE display = 1 ORDER BY RAND() LIMIT 0, 1");
        } else {
            $thumb = $wpdb->get_row("SELECT * FROM $table WHERE display = 1 ORDER BY id DESC LIMIT 0, 1 ");
        }

        $home = site_url() . '/';
        $flash_url = plugins_url() . '/wp-facethumb/flash.php';
        $gallery_url = $home . "?p=" . $this->wp_facethumb_settings['gallery_id'];

        $out .= '<ul><li><img src="' . $thumb->thumb . '" alt="' . htmlspecialchars($thumb->pseudo) . '"/></li>';
        if (($this->wp_facethumb_settings['link'] == 'widget' OR $this->wp_facethumb_settings['link'] == 'both') AND $thumb->link != "none") {
            $out .= '<li><a href="' . htmlspecialchars($thumb->link) . '">' . htmlspecialchars($thumb->pseudo) . '</a></li>';
        } else {
            $out .= '<li>' . htmlspecialchars($thumb->pseudo) . '</li>';
        }

        $index = plugins_url() . '/wp-facethumb/index.php';

        $out .= '<li><a class="thickbox" href="' . $index . '?ajax_url=' . admin_url('admin-ajax.php') . '&TB_iframe=true&height=400&width=500&modal=true">' . __('Take a snapshot', 'WP-FaceThumb') . '</a></li>';


        $out .= '<li><a href="' . $gallery_url . '">' . __('Gallery', 'WP-FaceThumb') . '</a></li>';
        $out .= '</ul>';

        if ($this->wp_facethumb_settings['ajax'] == 'no') {
            echo $out;
        } else {

            if ($this->wp_facethumb_settings['ajax'] == 'yes') {
                //get the <li> of the widget
                $widget_id = '#' . $this->id;
                //the array to send via ajax
                $array_response = array("widget_id" => $widget_id, "widget_content" => $out);
                //generate the response
                $response = json_encode($array_response);
                //response output
                header("Content-Type: application/json");
                echo $response;
                exit;
            }
        }
    }

}
?>

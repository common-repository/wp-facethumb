<?php

class WP_FaceThumb_Gallery {

    private $wp_facethumb_settings = array();

    function __construct() {
        $this->load_settings();
    }

    function load_settings() {
        $this->wp_facethumb_settings = (array) get_option('wp_facethumb_settings');
    }

    function get_thumbs($pagination_wp_facethumb = 1, $thumbs_per_page = 0) {

        /* Information to connect the database */
        global $wpdb;
        $table = $wpdb->prefix . "facethumb";

        $offset = ($pagination_wp_facethumb - 1) * $thumbs_per_page;

        $query = "SELECT * FROM $table WHERE display = 1 ORDER BY time LIMIT $offset, $thumbs_per_page";
        $cols = $wpdb->get_results($query);

        return $cols;
    }

    function display_thumbs() {

        /* Information to connect the database */
        global $wpdb;
        $table = $wpdb->prefix . "facethumb";

        $column = $this->wp_facethumb_settings['column'];
        $line = $this->wp_facethumb_settings['line'];
        $thumbs_per_page = $column * $line;

        /* Total numbers of visible thumbs */
        $max_visible_thumbs = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE display = 1");

        /* Total number of pages according to the settings */
        $max_pages = ceil($max_visible_thumbs / $thumbs_per_page);

        /* Gallery link for the navigation */
        $gallery_url = home_url() . '/?p=' . $this->wp_facethumb_settings['gallery_id'];

        /* Check the pagination */
        if (isset($_GET['pagination_wp_facethumb']) AND $_GET['pagination_wp_facethumb'] > 0 AND $_GET['pagination_wp_facethumb'] <= $max_pages) {
            $pagination_wp_facethumb = mysql_real_escape_string(htmlspecialchars($_GET['pagination_wp_facethumb']));
        } else {
            $pagination_wp_facethumb = 1;
        }

        /* Get the data from get_thumbs */
        $thumbs = $this->get_thumbs($pagination_wp_facethumb, $thumbs_per_page);

        /* Create an empty array to contain all the data from get_thumbs */
        $data = array();

        /* Loop trough the entries and setup the data to be displayed for each row */
        foreach ($thumbs as $entry) {
            $data[] = array(
                'id' => $entry->id,
                'pseudo' => $entry->pseudo,
                'thumb' => $entry->thumb,
                'time' => $entry->time,
                'link' => $entry->link,
                'display' => $entry->display
            );
        }
        
        /* Number of lines for the current page */
        $line_max = ceil(count($data) / $column);

        /* Counter to switch id in the data array every loop */
        $count = 0;

        /* Initialisation of $display wich will contain all html data to print the gallery */
        $display .= '<div class="wp-facethumb-gallery">' . '<table>';

        /* Check if there is thumbs to print */
        if (empty($data)) {
            $display .= __('Sorry, no photos have been taken yet!', 'WP-FaceThumb');
        } else {
            for ($line_count = 1; $line_count <= $line_max; $line_count++) {
                $display .= '<tr>';
                for ($column_count = 1; $column_count <= $column; $column_count++) {
                    $thumb = $data[$count];
                    if (!empty($thumb['thumb'])) {
                        $display .= '<td>';
                        $display .= '<img src="' . htmlspecialchars($thumb['thumb']) . '" alt="' . htmlspecialchars($thumb['pseudo']) . '" />';
                        $date_format = get_option('date_format');
                        $time_format = get_option('time_format');
                        $display .= '<br/> ' . date_i18n($date_format, $thumb['time']) . __(' at ', 'WP-FaceThumb') . date_i18n($time_format, $thumb['time']) . '<br/>' . __('by ', 'WP-FaceThumb');
                        if (($this->wp_facethumb_settings['link'] == 'gallery' OR $this->wp_facethumb_settings['link'] == 'both') AND $thumb['link'] != "none") {
                            $display .= '<a href="' . htmlspecialchars($thumb['link']) . '">';
                        }
                        $display .= htmlspecialchars($thumb['pseudo']);
                        if (($this->wp_facethumb_settings['link'] == 'gallery' OR $this->wp_facethumb_settings['link'] == 'both') AND $thumb['link'] != "none") {
                            $display .= '</a>';
                        }
                        $display .= '</td>';
                        $count++;
                    }
                }
                $display .= '</tr>';
            }
        }

        $display .= '</table><br/>';

        /* Display the thumbs */
        echo $display;

        /* Navigation */
        echo '<form class="pagination-wp-facethumb" action="#" method="get">';
        ?>
        <a href="<?php echo $gallery_url; ?>" class="first-page <?php
        if ($pagination_wp_facethumb == 1) {
            echo 'disabled';
        }
        ?>" title="First page">&lt;&lt;</a>
        <a href= "<?php
        echo $gallery_url;
        if ($pagination_wp_facethumb > 1) {
            echo '&amp;pagination_wp_facethumb=' . ($pagination_wp_facethumb - 1);
        }
        ?>" class="prev-page <?php
        if ($pagination_wp_facethumb <= 1) {
            echo 'disabled';
        }
        ?>" title="Previous page">&lt;</a>
        <input class="current-page" type="text" size="1" value="<?php echo $pagination_wp_facethumb; ?>" name="pagination_wp_facethumb" title="Current Page">
        <?php
        echo __(' on ', 'WP-FaceThumb');
        echo $max_pages;
        ?>
        <a href="<?php
        echo $gallery_url;
        if ($pagination_wp_facethumb < $max_pages) {
            echo '&amp;pagination_wp_facethumb=' . ($pagination_wp_facethumb + 1);
        }
        ?>" class="next-page <?php
        if ($pagination_wp_facethumb == $max_pages) {
            echo 'disabled';
        }
        ?>" title="Next page">&gt;</a>
        <a href= "<?php
        echo $gallery_url;
        echo '&amp;pagination_wp_facethumb=' . $max_pages;
        ?>" class="last-page <?php
        if ($pagination_wp_facethumb == $max_pages) {
            echo 'disabled';
        }
        ?>" title="Last page">&gt;&gt;</a>
        </form>
        </div>
        <?php
    }

    function enqueue_style() {
        wp_enqueue_style('WP-FaceThumb', plugins_url('/wp-facethumb/css/style.css'), false, '0.1', 'all');
    }

}
?>

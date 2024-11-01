<?php

class WP_FaceThumb_Gallery_Admin extends WP_List_Table {

    function __construct() {
        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'thumb',
            'plural' => 'thumbs',
            'ajax' => false
        ));

        $this->load_settings();
    }

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'thumb' => 'Thumb',
            'pseudo' => 'Pseudo',
            'time' => 'Date',
            'link' => 'Link',
            'display' => 'Visible'
        );
        return $columns;
    }

    function get_entries($orderby = 'date', $order = 'ASC') {
        global $wpdb;
        $table = $wpdb->prefix . "facethumb";

        switch ($orderby) {
            case 'time':
                $order_col = 'time';
                break;
            case 'pseudo':
                $order_col = 'pseudo';
                break;
            case 'thumb':
            case 'link':
            case 'display':
                $order_col = $orderby;
                break;
        }

        $sql_order = sanitize_sql_orderby("$order_col $order");
        $query = "SELECT * FROM $table ORDER BY $sql_order";

        $cols = $wpdb->get_results($query);

        return $cols;
    }

    function get_bulk_actions() {
        $actions = array(
            'delete' => __('Delete', 'WP-FaceThumb'),
            'show' => __('Show on frontend', 'WP-FaceThumb'),
            'hide' => __('Hide on frontend', 'WP-FaceThumb')
        );

        return $actions;
    }

    function process_bulk_action() {

        /* Put the thumb request in an array */
        $thumb_id = ( is_array($_REQUEST['thumb']) ) ? $_REQUEST['thumb'] : array($_REQUEST['thumb']);

        /* Check if the delete action is triggered */
        if ('delete' === $this->current_action()) {

            /* Information for query the database */
            global $wpdb;
            $table = $wpdb->prefix . "facethumb";

            /* Loop over the array */
            foreach ($thumb_id as $id) {

                /* Convert the value to non negative integer */
                $id = absint($id);

                /* Directory where the thumbs are located */
                $dir = WP_PLUGIN_DIR . '/wp-facethumb/uploads/';

                /* Search the thumb to delete by id */
                $thumb_to_delete = $wpdb->get_row("SELECT * FROM $table WHERE id = $id");

                /* Find the thumb location */
                $thumb_location = $dir . $thumb_to_delete->time . '.jpg';

                /* Check if the file exists */
                if (is_file((string) $thumb_location)) {

                    /* If file is sucessfully removed we remove the database entry */
                    if (unlink((string) $thumb_location)) {

                        /* Remove the entry in database */
                        $wpdb->query("DELETE FROM $table WHERE id = $id");
                    }
                }
            }
        }

        if ('hide' === $this->current_action()) {

            /* Information for query the database */
            global $wpdb;
            $table = $wpdb->prefix . "facethumb";

            /* Loop over the array */
            foreach ($thumb_id as $id) {

                /* Convert the value to non negative integer */
                $id = absint($id);

                /* Change the value in database */
                $wpdb->query("UPDATE $table SET display = '0' WHERE id = $id  ");
            }
        }

        if ('show' === $this->current_action()) {

            /* Information for query the database */
            global $wpdb;
            $table = $wpdb->prefix . "facethumb";

            /* Loop over the array */
            foreach ($thumb_id as $id) {

                /* Convert the value to non negative integer */
                $id = absint($id);

                /* Change the value in database */
                $wpdb->query("UPDATE $table SET display = '1' WHERE id = $id  ");
            }
        }

        if ($_GET['action'] === 'editpseudo') {
            $this->edit_pseudo();
        }

        if ($_GET['action'] === 'editlink') {
            $this->edit_link();
        }
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'pseudo' => array('pseudo', false),
            'time' => array('time', true)
        );
        return $sortable_columns;
    }

    function column_pseudo($item) {
        /* Build row actions */
        $actions = array(
            'editpseudo' => sprintf('<a href="?page=%s&tab=%s&paged=%s&action=%s&thumb=%s#%s">' . __('Edit', 'WP-FaceThumb') . '</a>', $_REQUEST['page'], $_REQUEST['tab'], $_REQUEST['paged'], 'editpseudo', $item['id'], $item['id']),
            'delete' => sprintf('<a href="?page=%s&tab=%s&paged=%s&action=%s&thumb=%s">' . __('Delete', 'WP-FaceThumb') . '</a>', $_REQUEST['page'], $_REQUEST['tab'], $_REQUEST['paged'], 'delete', $item['id']),
            'hide' => sprintf('<a href="?page=%s&tab=%s&paged=%s&action=%s&thumb=%s">' . __('Hide', 'WP-FaceThumb') . '</a>', $_REQUEST['page'], $_REQUEST['tab'], $_REQUEST['paged'], 'hide', $item['id']),
            'show' => sprintf('<a href="?page=%s&tab=%s&paged=%s&action=%s&thumb=%s">' . __('Show', 'WP-FaceThumb') . '</a>', $_REQUEST['page'], $_REQUEST['tab'], $_REQUEST['paged'], 'show', $item['id'])
        );
        if ($_GET['action'] === 'editpseudo' AND $_REQUEST['thumb'] == $item['id'] AND !isset($_POST['pseudo'])) {
            return sprintf('</br></br></br><input type="text" value="%1$s" name="pseudo"><input class="save button-primary" type="submit" value="Update">', $item['pseudo']);
        }
        return sprintf('%1$s %2$s', $item['pseudo'], $this->row_actions($actions));
    }

    function column_time($item) {
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        return sprintf('%1$s' . ' at ' . '%2$s', date_i18n($date_format, $item['time']), date_i18n($time_format, $item['time']));
    }

    function column_thumb($item) {
        return sprintf('<img src="%1$s" alt="%2$s" title="%3$s"/>', $item['thumb'], $item['pseudo'], $item['pseudo']);
    }

    function column_link($item) {
        $actions = array(
            'editlink' => sprintf('<a href="?page=%s&tab=%s&paged=%s&action=%s&thumb=%s#%s">' . __('Edit', 'WP-FaceThumb') . '</a>', $_REQUEST['page'], $_REQUEST['tab'], $_REQUEST['paged'], 'editlink', $item['id'], $item['id'])
        );
        if (htmlspecialchars($item['link']) == 'none') {
            return sprintf('');
        } elseif ($_GET['action'] === 'editlink' AND $_REQUEST['thumb'] == $item['id'] AND !isset($_POST['link'])) {
            return sprintf('</br></br></br><input type="text" value="%1$s" name="link"><input class="save button-primary" type="submit" value="Update">', htmlspecialchars($item['link']));
        } else {
            return sprintf('<a target="_blank" href="%1$s">%2$s</a> %3$s', htmlspecialchars($item['link']), htmlspecialchars($item['link']), $this->row_actions($actions));
        }
    }

    function column_display($item) {
        if (htmlspecialchars($item['display']) == 1) {
            return sprintf(__('Yes', 'WP-FaceThumb'));
        } else {
            return sprintf(__('No', 'WP-FaceThumb'));
        }
    }

    function column_cb($item) {
        return sprintf('<input id="%1$s" type="checkbox" name="%2$s[]" value="%3$s" />', $item['id'], $this->_args['singular'], $item['id']);
    }

    function prepare_items() {
        global $wpdb;

        /* How many to show per page */
        $per_page = $this->wp_facethumb_settings['thumbs_per_page'];

        /* Get column headers */
        $columns = $this->get_columns();
        $hidden = array();

        /* Get sortable columns */
        $sortable = $this->get_sortable_columns();

        /* Build the column headers */
        $this->_column_headers = array($columns, $hidden, $sortable);

        /* Handle our bulk actions */
        $this->process_bulk_action();

        /* Set our ORDER BY and ASC/DESC to sort the entries */
        $orderby = (!empty($_REQUEST['orderby']) ) ? $_REQUEST['orderby'] : 'time';
        $order = (!empty($_REQUEST['order']) ) ? $_REQUEST['order'] : 'desc';

        /* Get the sorted entries */
        $entries = $this->get_entries($orderby, $order);

        $data = array();

        /* Loop trough the entries and setup the data to be displayed for each row */
        foreach ($entries as $entry) {
            $data[] =
                    array(
                        'id' => $entry->id,
                        'pseudo' => $entry->pseudo,
                        'thumb' => $entry->thumb,
                        'time' => $entry->time,
                        'link' => $entry->link,
                        'display' => $entry->display
            );
        }

        /* What page are we looking at? */
        $current_page = $this->get_pagenum();

        /* How many entries do we have? */
        $total_items = count($entries);

        /* Calculate pagination */
        $data = array_slice($data, (( $current_page - 1 ) * $per_page), $per_page);

        /* Add sorted data to the items property */
        $this->items = $data;

        /* Register our pagination */
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    function load_settings() {
        $this->wp_facethumb_settings = (array) get_option('wp_facethumb_settings');
    }

    function edit_pseudo() {
        $nonce=$_POST['_wpnonce'];
        if (isset($_GET['thumb']) AND isset($_POST['_wpnonce']) AND wp_verify_nonce($nonce, 'bulk-thumbs')) {

            /* Information for query the database */
            global $wpdb;
            $table = $wpdb->prefix . "facethumb";

            $id = $_REQUEST['thumb'];
            $pseudo = mysql_real_escape_string(htmlspecialchars($_REQUEST['pseudo']));

            /* Change the value in database */
            $wpdb->query("UPDATE $table SET pseudo = '$pseudo' WHERE id = $id  ");
        }
    }

    function edit_link() {
        $nonce=$_POST['_wpnonce'];
        if (isset($_GET['thumb']) AND isset($_POST['_wpnonce']) AND wp_verify_nonce($nonce, 'bulk-thumbs')) {

            /* Information for query the database */
            global $wpdb;
            $table = $wpdb->prefix . "facethumb";

            $id = $_REQUEST['thumb'];
            $link = mysql_real_escape_string(htmlspecialchars($_REQUEST['link']));

            /* Change the value in database */
            $wpdb->query("UPDATE $table SET link = '$link' WHERE id = $id  ");
        }
    }

}

?>
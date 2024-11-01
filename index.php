<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery.post(
                <?php echo '"' . $_GET['ajax_url'] . '"'; ?>,
                {action : 'html_thickbox'},
                function( response ) {
                    jQuery(response.div_id).append(response.div_content); // fill the widget with the fresh data
                }
            );
            });
        </script>
        <script type="text/javascript">
            function close_thickbox() {
                window.opener.location.reload();
                window.parent.tb_remove();
                
            }
        </script>
    </head>
    <body>
        <div id="wp-facethumb-container"></div>
    </body>
</html>

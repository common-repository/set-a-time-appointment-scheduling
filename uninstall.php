<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
delete_option('sat_domain');
delete_option('sat_width');
delete_option('sat_height');

?>
<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Plugin_Name_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
    $plugin_name = 'plugin_name';
      $del_logs = get_option('del_logs');
      if ($del_logs === 'true') {
        $args = array (
            'numberposts' => -1,
            'post_type' => 'logs'
        );
        $logs = get_posts($args);
        if (empty($logs)) return;
      }
    foreach ($logs as $log) {
      wp_delete_post($log->ID, true);
      delete_post_meta($log->ID, $plugin_name.'_log_datetime');
      delete_post_meta($log->ID, $plugin_name.'_user_id');
      delete_post_meta($log->ID, $plugin_name.'_user_ip');
      delete_post_meta($log->ID, $plugin_name.'_log_post_type');
      delete_post_meta($log->ID, $plugin_name.'_log_action');
      delete_post_meta($log->ID, $plugin_name.'_log_metadata');
    }
    
    wp_clear_scheduled_hook('bl_cron_log_retention');
    
	}

}

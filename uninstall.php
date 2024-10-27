<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die('Direct access denied');
}

// Drop plugin tables
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}amoforms_amo_user");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}amoforms_entries");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}amoforms_forms");

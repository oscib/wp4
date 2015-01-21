<?php
if ( !defined('WP_UNINSTALL_PLUGIN') ) {
	exit();
}
if ('uninstall.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct File Access Prohibited</h2>');
/**
 * The plugin uninstallation script
 * 1. Remove the tables
 * 2. Remove files
 * 3. Remove options
 * Done
 */

/**
 * Set Global variable
 * @var wpdb
 */
global $wpdb;

/** Remove databases */
$prefix = '';
if(is_multisite()) {
	$prefix = $wpdb->base_prefix;
	$blogs = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
	$old_blog = get_current_blog_id();
	foreach($blogs as $blog) {
		$settings = get_blog_option( $blog, 'ipt_fsqm_exp_settings', array() );
		$msprefix = $prefix . $blog . '_';
		switch_to_blog( $blog );

		if ( isset( $settings['delete_uninstall'] ) && $settings['delete_uninstall'] === true ) {
			// Delete database
			if ( $wpdb->get_var( "show tables like '" . $msprefix . "fsq_export'" ) ) {
				//delete it
				$wpdb->query( "DROP TABLE IF EXISTS " . $msprefix . "fsq_export" );
			}
			if ( $wpdb->get_var( "show tables like '" . $msprefix . "fsq_exp_raw'" ) ) {
				//delete it
				$wpdb->query( "DROP TABLE IF EXISTS " . $msprefix . "fsq_exp_raw" );
			}

			// Delete files
			$wp_path = wp_upload_dir();
			$path1 = $wp_path['basedir'] . '/fsqm-exp-reports';
			if ( file_exists( $path1 ) && is_dir( $path1 ) ) {
				ipt_fsqm_exp_del_tree( $path1 );
			}
			$path2 = $wp_path['basedir'] . '/fsqm-exp-raw';
			if ( file_exists( $path2 ) && is_dir( $path2 ) ) {
				ipt_fsqm_exp_del_tree( $path2 );
			}

			// Delete option
			delete_blog_option( $blog, 'ipt_fsqm_exp_info' );
			delete_blog_option( $blog, 'ipt_fsqm_exp_settings' );
		} else {
			continue;
		}
	}
	switch_to_blog( $old_blog );
} else {
	$prefix = $wpdb->prefix;
	$settings = get_option( 'ipt_fsqm_exp_settings', array() );

	if ( isset( $settings['delete_uninstall'] ) && $settings['delete_uninstall'] === true ) {
		// Delete database
		if ( $wpdb->get_var( "show tables like '" . $prefix . "fsq_export'" ) ) {
			//delete it
			$wpdb->query( "DROP TABLE IF EXISTS " . $prefix . "fsq_export" );
		}
		if ( $wpdb->get_var( "show tables like '" . $prefix . "fsq_exp_raw'" ) ) {
			//delete it
			$wpdb->query( "DROP TABLE IF EXISTS " . $prefix . "fsq_exp_raw" );
		}

		// Delete files
		$wp_path = wp_upload_dir();
		$path1 = $wp_path['basedir'] . '/fsqm-exp-reports';
		if ( file_exists( $path1 ) && is_dir( $path1 ) ) {
			ipt_fsqm_exp_del_tree( $path1 );
		}
		$path2 = $wp_path['basedir'] . '/fsqm-exp-raw';
		if ( file_exists( $path2 ) && is_dir( $path2 ) ) {
			ipt_fsqm_exp_del_tree( $path2 );
		}

		// Delete options
		delete_option( 'ipt_fsqm_exp_info' );
		delete_option( 'ipt_fsqm_exp_settings' );
	}
}

/**
 * Delete a directory recusrively
 * @param  string $dir Path to directory (absolute)
 * @return bool      TRUE on success, FALSE on error
 * @danger DO NOT USE IT IF YOU DO NOT KNOW WHAT YOU ARE DOING
 */
function ipt_fsqm_exp_del_tree( $dir ) {
	$files = array_diff( scandir( $dir ), array( '.', '..' ) );
	foreach ( $files as $file ) {
		( is_dir( "$dir/$file" ) ) ? ipt_fsqm_exp_del_tree( "$dir/$file" ) : @unlink( "$dir/$file" );
	}
	return @rmdir( $dir );
}

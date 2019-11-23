<?php
/**
 * Plugin Name: CMB2 Post Search field with post type filter
 * Plugin URI: http://oneplusone.xyz
 * Description: Custom field for CMB2 which adds a post-search dialog for searching/attaching other post IDs with post type filter
 * Author: oneplusone
 * Author URI: http://oneplusone.xyz
 * Version: 0.0.1
 * License: GPLv2
*/

if ( ! class_exists( 'CMB2_Post_Search_field_with_post_type_filter_001', false ) ) {
	class CMB2_Post_Search_field_with_post_type_filter_001 {
		const VERSION = '0.0.1';
		const PRIORITY = 9998;
		public function __construct() {
			if ( ! defined( 'CMB2_POST_SEARCH_FIELD_LOADED' ) ) {
				define( 'CMB2_POST_SEARCH_FIELD_LOADED', self::PRIORITY );
			}
			add_action( 'cmb2_post_search_field_load', array( $this, 'include_lib' ), self::PRIORITY );
			add_action( 'after_setup_theme', array( $this, 'do_hook' ) );
		}
		public function do_hook() {
			do_action( 'cmb2_post_search_field_load' );
		}
		public function include_lib() {
			if ( class_exists( 'CMB2_Post_Search_field_with_filters', false ) ) {
				return;
			}

			if ( ! defined( 'CMB2_POST_SEARCH_FIELD_VERSION' ) ) {
				define( 'CMB2_POST_SEARCH_FIELD_VERSION', self::VERSION );
			}

			if ( ! defined( 'CMB2_POST_SEARCH_FIELD_DIR' ) ) {
				define( 'CMB2_POST_SEARCH_FIELD_DIR', dirname( __FILE__ ) . '/' );
			}
			require_once CMB2_POST_SEARCH_FIELD_DIR . 'lib/init.php';
		}
	}
	new CMB2_Post_Search_field_with_post_type_filter_001;
}

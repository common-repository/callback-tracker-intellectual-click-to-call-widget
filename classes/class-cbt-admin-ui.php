<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CBT_Admin_UI {
	
	public function admin_print_scripts() {
		wp_enqueue_style( 'cbt-admin-ui', plugins_url( '/assets/css/admin-ui.css', CBT_BASE_FILE ) );
	}
	
	public function __construct() {
		add_action( 'admin_print_scripts', array( &$this, 'admin_print_scripts' ) );
	}
}

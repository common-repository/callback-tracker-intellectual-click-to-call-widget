<?php
/*
Plugin Name: Callback Tracker Communications Suite
Plugin URI: http://wordpress.org/plugins/callback-tracker-intellectual-click-to-call-widget/
Description: Connect with site visitors via call, chat, email, and more from one tool. Increase their satisfaction and your conversion rates.
Author: CallbackTracker.com
Version: 1.2.0
Author URI: http://www.callbacktracker.com
License: GPLv2 or later

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'CBT_BASE_FILE', __FILE__ );
define( 'CBT_BASE', plugin_basename( CBT_BASE_FILE ) );

include('classes/class-cbt-settings.php');
include('classes/class-cbt-admin-ui.php');
include('classes/class-cbt-front-end.php');


class CBT_Main {

	/**
	 * @var CBT_Settings
	 */
	public $settings;

	/**
	 * @var CBT_Admin_UI
	 */
	public $admin_ui;
	
	/**
	 * @var CBT_Front_End
	 */
	public $front_end;

	public function load_textdomain() {
		load_plugin_textdomain( 'CBT', false, basename( dirname( __FILE__ ) ) . '/language' );
	}
	
	public function __construct() {
		$this->settings = new CBT_Settings();
		$this->admin_ui = new CBT_Admin_UI();
		$this->front_end = new CBT_Front_End();
		
		add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ) );
	}
	
}

global $cbt_main_class;
$cbt_main_class = new CBT_Main();
// EOF
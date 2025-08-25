<?php
/**
 *  Multisite Visibility Manager
 *
 * Plugin Name:     Multisite Visibility Manager
 * Description:     This plugin is responsible for Managing search engine visibility across all subsites in the network from one settings page.
 * Author:          Abiodun Paul Ogunnaike
 * Text Domain:     multisite-visibility-manager
 * Network: 		true
 * Domain Path:     /languages
 * Version:         0.1.0
 * prefix:          Multisite_Visibility_Manager
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.htmls
 * Requires PHP:    7.4
 * Requires at least: 6.0
 *
 *
 *
 * @package         Multisite_Visibility_Manager
 * @author  		Abiodun Paul Ogunnaike <ayo_ogunnaike@yahoo.com>
 *
 */

// If the file is accessed directly abort script.
defined('ABSPATH') || die('Unauthorized Access');


// Include the main plugin class.
require_once plugin_dir_path(__FILE__) . 'includes/class-multisite-visibility-manager.php';

//instantiate the class
$Multisite_Visibility_Manager = new Multisite_Visibility_Manager();

<?php

/**
 * Multisite Visibility Manager
 *
 * @category Plugin
 * @package  Multisite_Visibility_Manager
 * @author   Abiodun Paul Ogunnaike <primastech101@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.txt GPLv2 or later
 * @link     https://github.com/abbeymaniak/multisite-visibility-manager
 *
 * Plugin Name:       Multisite Visibility Manager
 * Description:       Manage search engine visibility across all subsites in the network from one settings page.
 * Plugin URI:        https://github.com/abbeymaniak/multisite-visibility-manager
 * Author:            Abiodun Paul Ogunnaike
 * Author URI:        https://linkedin.com/in/abiodun-paul-ogunnaike
 * Text Domain:       multisite-visibility-manager
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * Network:           true
 * Version:           1.0.0
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * Donate:            https://www.buymeacoffee.com/abbeymaniak
 */

defined('ABSPATH') || die('Unauthorized Access');

// Include the main plugin class.
require_once plugin_dir_path(__FILE__) . 'includes/class-multisite-visibility-manager.php';


//instantiate the class
$Multisite_Visibility_Manager = new Multisite_Visibility_Manager();

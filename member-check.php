<?php
ob_start();

/**
 *
 * Plugin Name:       member-check
 * Plugin URI:        https://plugins-wordpress-osowsky-webdesign.info
 * Description:       Prüft beim Buchen eines Kurse auf eine gültige Mitgliedsnummer und lässte das aktualisieren der Mitgliedinformationen im Admin Bereich zu.
 * Version:           1.1.0
 * Requires at least: 5.8.2
 * Requires PHP:      7.2
 * Author:            Silvio Osowsky <i class="fas fa-heart"></i>
 * Author URI:        https://osowsky-webdesign.de
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       osowsky-design-plugin
 */

define( 'MC_PLUGIN', __FILE__ );
define( 'MC_PLUGIN_PLUGIN_BASENAME', plugin_basename( MC_PLUGIN ) );
define( 'MC_PLUGIN_PLUGIN_DIR', untrailingslashit( dirname( MC_PLUGIN ) ) );
define( 'MC_PLUGIN_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );

/*
* Load required classes
*/

require_once MC_PLUGIN_PLUGIN_DIR .  '/inc/wp-enqueue.php';
require_once MC_PLUGIN_PLUGIN_DIR .  '/main/shortcode.php';

require_once MC_PLUGIN_PLUGIN_DIR .  '/class/import.class.php';
require_once MC_PLUGIN_PLUGIN_DIR .  '/class/db.class.php';
require_once MC_PLUGIN_PLUGIN_DIR .  '/class/utils.class.php';
require_once MC_PLUGIN_PLUGIN_DIR .  '/class/members.table.class.php';

require_once MC_PLUGIN_PLUGIN_DIR .  '/admin/member-check-admin.php';
require_once MC_PLUGIN_PLUGIN_DIR .  '/admin/menus/tools.php';
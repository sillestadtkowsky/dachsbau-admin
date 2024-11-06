<?php
ob_start();

/**
 *
 * Plugin Name:       dachsbau-admin
 * Plugin URI:        https://plugins-wordpress-osowsky-webdesign.info
 * Description:       Prüft beim Buchen eines Kurse auf eine gültige Mitgliedsnummer und lässt das aktualisieren der Mitgliedsnummern im Admin Bereich zu. Des weiteren werden verschiedene Konfigurationen und Bearbeitungslisten angeboten.
 * Version:           2.6.0
 * Requires at least: 6.4.0
 * Requires PHP:      7.2
 * Author:            Silvio Osowsky <i class="fas fa-heart"></i>
 * Author URI:        https://osowsky-webdesign.de
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       osowsky-design-plugin
 */

register_activation_hook( __FILE__, 'so_schedule_booking_activate' );

function so_schedule_booking_activate() {
    wp_clear_scheduled_hook( 'so_remove_old_bookings' );

    if ( ! wp_next_scheduled( 'so_remove_old_bookings' ) ) {
        wp_schedule_event( time(), 'so_every_three_minutes', 'so_remove_old_bookings' );
    }
}

add_action('admin_enqueue_scripts', 'so_schedule_booking_enqueue_scripts');

function so_schedule_booking_enqueue_scripts($hook) {
    if ($hook != 'toplevel_page_schedule-booking') {
        return;
    }
    wp_enqueue_style('wp-list-table');
}

// Erstelle Schedule Timer
add_filter('cron_schedules', 'so_add_every_three_minutes');
function so_add_every_three_minutes($schedules)
{
    $schedules['so_every_three_minutes'] = array(
        'interval' => 180,
        'display' => __('Every 3 Minutes', 'textdomain')
    );
    return $schedules;
}

define( 'MC_PLUGIN', __FILE__ );
define( 'MC_PLUGIN_PLUGIN_BASENAME', plugin_basename( MC_PLUGIN ) );
define( 'MC_PLUGIN_PLUGIN_DIR', untrailingslashit( dirname( MC_PLUGIN ) ) );
define( 'MC_PLUGIN_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );

/*
* Load required classes
*/

require_once MC_PLUGIN_PLUGIN_DIR .  '/inc/wp-enqueue.php';

require_once MC_PLUGIN_PLUGIN_DIR .  '/class/db.class.php';
require_once MC_PLUGIN_PLUGIN_DIR .  '/class/utils.class.php';
require_once MC_PLUGIN_PLUGIN_DIR .  '/class/map_metabox.class.php';
require_once MC_PLUGIN_PLUGIN_DIR .  '/class/members.table.class.php';

// Initialisierung der Klasse
function initialize_map_meta_box() {
    new CustomMetaBox();
}
add_action( 'plugins_loaded', 'initialize_map_meta_box' );

require_once MC_PLUGIN_PLUGIN_DIR .  '/admin/dachsbau-karow-admin.php';

// Fügen Sie diesen Code in Ihre functions.php-Datei ein
add_action('wp', 'so_schedule_booking_cronjob');
function so_schedule_booking_cronjob()
{
    if (!wp_next_scheduled('so_remove_old_bookings')) {
        wp_schedule_event(time(), 'so_every_three_minutes', 'so_remove_old_bookings');
    }
}

add_action('so_remove_old_bookings', 'so_remove_old_bookings_function');
function so_remove_old_bookings_function()
{
    $enabled = get_option('so_scheduler_enabled');

    if ($enabled == '1') {
        // set current time to 01.01.1900 and add the current time as hours and minutes
        date_default_timezone_set('Europe/Berlin');
        $current_time = strtotime('01.01.1900 ' . date('H:i'));
        // get start time from option and add it to 01.01.1900
        $start_time_desc = get_option('so_scheduler_time');
        $start_time = strtotime('01.01.1900 ' . $start_time_desc);
        // set end time to start time plus one hour
        $end_time = strtotime($start_time_desc . ' +1 hour', $start_time);

        // check if current time is between start and end times
        if ($current_time >= $start_time && $current_time <= $end_time) {
            error_log( print_r('Automat gestartet.') );
            require_once 'admin/class/so-kurs-scheduler/remove-booking-cron-class.php';
            $so_schedule_booking_cronjob = new SOScheduleBookingCronJob;
            $result = $so_schedule_booking_cronjob->so_removeOldBookings();
            return $result; // return the result instead of echoing it
        } else {
            return "Automat zum automatischen Löschen von Buchungen wird nur zwischen $start_time_desc und " . date('H:i', $end_time) . " Uhr ausgeführt.";
        }
    } else {
        return "Automat zum automatischen Löschen von Buchungen ist deaktiviert.";
    }
}

function add_roles_on_plugin_activation() {
    add_role( 'trainer-dachs', 'Trainer', array( 'read' => true, 'level_0' => true ) );
}
register_activation_hook( __FILE__, 'add_roles_on_plugin_activation' );



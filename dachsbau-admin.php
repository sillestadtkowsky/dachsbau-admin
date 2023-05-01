<?php
ob_start();

/**
 *
 * Plugin Name:       dachsbau-admin
 * Plugin URI:        https://plugins-wordpress-osowsky-webdesign.info
 * Description:       Prüft beim buchen eines Kurse auf eine gültige Mitgliedsnummer und lässt das aktualisieren der Mitgliedsnummern im Admin Bereich zu.
 * Version:           2.0.15
 * Requires at least: 6.1.1
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
require_once MC_PLUGIN_PLUGIN_DIR .  '/class/members.table.class.php';

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
        error_log( print_r('Automat gestartet.') );
        require_once 'admin/class/so-kurs-scheduler/remove-booking-cron-class.php';
        $so_schedule_booking_cronjob = new SOScheduleBookingCronJob;
        $result = $so_schedule_booking_cronjob->so_removeOldBookings();
        return $result; // return the result instead of echoing it
    }else{
        return "Automat zum automatischen Löschen von Buchungen ist deaktiviert.";
    }
}
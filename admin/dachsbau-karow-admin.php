<?php

/*
Plugin Name: Mein Plugin
*/

require_once( plugin_dir_path( __FILE__ ) . 'class/so-dachsbau-dashboard-class.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class/coach.table.class.php' );
require_once(plugin_dir_path(__FILE__) . 'class/coach/mail_to_user_class.php');

new So_Dachsbau_Dashboard_Widget();

/*
* ###############################
* ADD Admin Menu
* ###############################
*/
function so_DachsbauKarowAdminMenu()
{
    add_menu_page('Dachsbau-Admin', 'Dachsbau-Admin', 'manage_options', 'so_dachsbau-karow-admin-menu', 'so_dachsbau_admin_info_page', 'dashicons-list-view', 5);

    //Add sub-menu pages
    add_submenu_page('so_dachsbau-karow-admin-menu', 'Mitgliederliste bearbeiten', 'Mitgliederliste bearbeiten', 'manage_options', 'so_member-checker-import', 'so_mitgliederliste');
    add_submenu_page('so_dachsbau-karow-admin-menu', 'Mitgliederliste importieren', 'Mitgliederliste importieren', 'manage_options', 'so_member_checker_file_upload', 'so_member_checker_file_upload');
    add_submenu_page('so_dachsbau-karow-admin-menu', 'Aktuelle Buchungen','Aktuelle Buchungen','manage_options','timetable_admin_bookings','timetable_admin_bookings');
    add_submenu_page('so_dachsbau-karow-admin-menu', 'Buchungen exportieren', 'Buchungen exportieren', 'manage_options', 'so_booking_export_page', 'so_booking_export_page');
    add_submenu_page('so_dachsbau-karow-admin-menu', 'Gesicherte Buchungen','Gesicherte Buchungen','manage_options','so_schedule-booking','so_schedule_booking_page');
    add_submenu_page('so_dachsbau-karow-admin-menu', 'Konfiguration','Konfiguration','manage_options','so_dachsbau_admin_config','so_dachsbau_admin_config');
    add_submenu_page('so_dachsbau-karow-admin-menu', 'Coach Kurse','Coach Kurse','manage_options','so_coach_booking_page','so_coach_booking_page');
    //$custom_mail_page = new CustomMailPage(); // Initialisieren Sie die Klasse
    //add_submenu_page('so_dachsbau-karow-admin-menu', 'Mail an Kurteilnehmer', 'Mail an Kurteilnehmer', 'manage_options', 'so_mail_to_user', array($custom_mail_page, 'render_custom_mail_page'));


    if (current_user_can('trainer-dachs')) {
        add_menu_page(
            'Coach Übersicht',
            'Coach Übersicht',
            'trainer-dachs',
            'so_dachsbau-karow-coach-menu',
            'so_coach_booking_page_coach',
            'dashicons-awards', // Hier wird das Icon definiert
            10
        );
    }
}
add_action('admin_menu', 'so_DachsbauKarowAdminMenu');


function so_dachsbau_admin_info_page() {
    ?>
    <style>
        .card:hover {
            background-color: #c6dbff !important;
        }
    </style>
    <div class="wrap">
    <div style="display: flex; align-items: center;">
        <?php $image_url = wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full'); ?>
        <?php if (!empty($image_url)) : ?>
            <img src="<?php echo esc_url($image_url[0]); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" style="width: 200px; height: 150px; margin-right: 10px;">
        <?php endif; ?>
        <h2 style="margin-top: auto; padding-left: 50px;"><?php echo esc_html(get_bloginfo('name')); ?></h2>
    </div>
    <p><?php echo esc_html(get_bloginfo('description')); ?></p>
    <div style="display: flex; flex-wrap: wrap; gap: 30px; margin-top: 30px;">
        <div style="width: 100%;"><hr>
            <h3>Willkommen im Dachsbau Admin Bereich</h3>
            <p>
                <a href="https://osowsky-webdesign.de/#kontakt" target="_blank">Support Kontaktformular</a>&nbsp;|&nbsp;<a href="tel:017647782068" target="_blank">Support Telefon</a>
            </p>
        </div>
            <a href="<?php echo admin_url('admin.php?page=so_member-checker-import'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Mitgliederliste bearbeiten</h3>
                <p>Verwalte hier die Mitgliederliste.</p>
            </a>
            <a href="<?php echo admin_url('admin.php?page=so_member_checker_file_upload'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Mitgliederliste importieren</h3>
                <p>Lade hier eine aktuelle neue Mitgliederliste hoch.</p>
            </a>         
            <a href="<?php echo admin_url('admin.php?page=timetable_admin_bookings'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Aktuelle Buchungen sehen</h3>
                <p>Überblick über aktuell gebuchte Kurse</p>
            </a>   
            <a href="<?php echo admin_url('admin.php?page=so_booking_export_page'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Aktuelle Buchungen exportieren</h3>
                <p>Hier können alte Buchungen, welche noch nicht mit der automatik gelöscht wurden, exportiert werden.</p>
            </a>
            <a href="<?php echo admin_url('admin.php?page=so_schedule-booking'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                 <h3>Gesicherte Buchungen</h3>
                <p>Verwalte hier automatisch gesicherte Buchungen.</p>
            </a>           
            <a href="<?php echo admin_url('admin.php?page=so_dachsbau_admin_config'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Konfiguration</h3>
                <p>Nehme hier optionale Einstellungen vor </p>
            </a>
            <a href="<?php echo admin_url('admin.php?page=so_coach_booking_page'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Trainer Übersicht</h3>
                <p>Hier wird den Trainer ermöglicht, für den aktuell stattfindenden Kurs die Mitglieder als "Anwesende" zu markieren. </p>
            </a>
        </div>
    </div>
    <?php
}



    function so_coach_table_shortcode( $atts ) {
        ob_start();
         include_once( plugin_dir_path( __FILE__ ) . '../class/coach.table.class.php' );
        $template = ob_get_contents();
        ob_end_clean();
        return $template;
    }
    
    add_action( 'wp_enqueue_scripts', 'so_coach_queue_stylesheet' );
    
    function so_coach_queue_stylesheet() {
        wp_enqueue_style( 'so-table-shortcode-style', admin_url( 'css/wp-admin.css' ), array(), '1.0' );
    }

add_shortcode('so_coach_table', 'so_coach_table_shortcode');



function so_dachsbau_post_type_settings()
{
	$timetable_events_settings = get_option("timetable_events_settings");
	if(!$timetable_events_settings)
	{
		$timetable_events_settings = array(
			"slug" => "events",
			"label_singular" => "Event",
			"label_plural" => "Events",
		);
		add_option("timetable_events_settings", $timetable_events_settings);
	}
	return $timetable_events_settings;
}

function so_dachsbau_admin_config() {

    // Fehlermeldungen ausgeben
    if ( ! empty( $_GET['settings-updated'] ) ) {
        $errors = get_settings_errors();
        if ( count( $errors ) > 0 ) {
            echo '<div id="setting-error-settings_updated" class="notice notice-error settings-error">';
            foreach( $errors as $error ) {
                echo '<p>' . $error['message'] . '</p>';
            }
            echo '</div>';
        }
    }

    // Schalter speichern
    if (isset($_POST['submit'])) {
        update_option('so_scheduler_enabled', isset($_POST['so_scheduler_enabled']) ? sanitize_text_field($_POST['so_scheduler_enabled']) : '');
        update_option('so_kurs_strong_group_mail', isset($_POST['so_kurs_strong_group_mail']) ? sanitize_text_field($_POST['so_kurs_strong_group_mail']) : '');
        update_option('so_kurs_close_at', isset($_POST['so_kurs_close_at']) ? sanitize_text_field($_POST['so_kurs_close_at']) : '');
        update_option('so_pdf_export_name', isset($_POST['so_pdf_export_name']) ? sanitize_text_field($_POST['so_pdf_export_name']) : '');
        update_option('so_kurs_online_search_name', isset($_POST['so_kurs_online_search_name']) ? sanitize_text_field($_POST['so_kurs_online_search_name']) : '');
        update_option('so_kurs_strong_group_mail_adresse', isset($_POST['so_kurs_strong_group_mail_adresse']) ? sanitize_text_field($_POST['so_kurs_strong_group_mail_adresse']) : '');
        update_option('so_kurs_names_description', isset($_POST['so_kurs_names_description']) ? sanitize_text_field($_POST['so_kurs_names_description']) : '');
        $so_kurs_online_name_color_background = isset( $_POST['so_kurs_online_name_color_background'] ) ? sanitize_hex_color( $_POST['so_kurs_online_name_color_background'] ) : '';
        update_option( 'so_kurs_online_name_color_background', $so_kurs_online_name_color_background );
        $so_kurs_online_name_color_text = isset( $_POST['so_kurs_online_name_color_text'] ) ? sanitize_hex_color( $_POST['so_kurs_online_name_color_text'] ) : '';
        update_option( 'so_kurs_online_name_color_text', $so_kurs_online_name_color_text );
        $so_kurs_booking_open_time = isset($_POST['so_kurs_booking_open_time']) ? sanitize_text_field($_POST['so_kurs_booking_open_time']) : '';
        $so_scheduler_time = isset($_POST['so_scheduler_time']) ? sanitize_text_field($_POST['so_scheduler_time']) : '';
        
        // Validierung der Buchungsfreigabezeit
        if (preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $so_kurs_booking_open_time) && preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $so_scheduler_time)) {
            update_option('so_kurs_booking_open_time', $so_kurs_booking_open_time);
            update_option('so_scheduler_time', $so_scheduler_time);
            add_settings_error('so_kurs_save_time', 'success', 'Die Einstellungen wurden erfolgreich gespeichert.', 'updated');
        } else {
            add_settings_error('so_kurs_save_time', 'invalid_time_format', 'Bitte gib eine gültige Uhrzeit ein (Format: Stunde und Minute, z.B. 12:00).');
        }


        // Speichern der ausgewählten Kursnamen
        $so_kurs_names = isset($_POST['so_kurs_names']) ? $_POST['so_kurs_names'] : array();
        update_option('so_kurs_names', $so_kurs_names);

        settings_errors();
    }

    // Aktuelle Werte abrufen
    $so_kurs_online_name_color_text = get_option('so_kurs_online_name_color_text', '1');
    $so_kurs_online_name_color_background = get_option('so_kurs_online_name_color_background', '1');
    $so_scheduler_enabled = get_option('so_scheduler_enabled', '1');
    $so_kurs_strong_group_mail = get_option('so_kurs_strong_group_mail', '1');
    $so_kurs_close_at = get_option('so_kurs_close_at', 'so_close_kurs_at_start_time');
    $so_kurs_booking_open_time = get_option('so_kurs_booking_open_time', '12:00');
    $so_scheduler_time  = get_option('so_scheduler_time', '23:00');
    $pdf_export_name = get_option('so_pdf_export_name', 'gesicherte-buchungen');
    $so_kurs_online_search_name = get_option('so_kurs_online_search_name', 'online');
    $so_kurs_strong_group_mail_adresse = get_option('so_kurs_strong_group_mail_adresse', 'info@karowerdachse.de');
    $so_kurs_names_description = get_option('so_kurs_names_description', '** feste Gruppe **');
    $so_kurs_names = get_option('so_kurs_names', array());

    ?>
    <div class="wrap" style="max-width: 800px;">
        <h2>Konfigurationen</h2>
        <p>Hier kannst du diverse Konfigurationen vornehmen.</p>
        <form method="post" style="padding: 20px 0px 20px 0px;">
            <div style="padding: 10px; margin-bottom: 20px; background-color:rgb(235, 235, 235); border-left: 3px solid #012c6d;">
                <h3>Automat zur Buchungssicherung und Verwaltung</h3>
                <div style="display: flex; align-items: flex-start;">
                    <div style="display: inline-block; width: 250px; text-align: left;">
                        <label style="font-weight: bold; vertical-align: top;" for="so_scheduler_enabled">Scheduler aktivieren:</label>
                        <p style="margin-top: 0;">Aktiviert den Timer, um automatisch Buchungen für einen bereits durchgeführten Kurs zu sichern und danach die Buchungen zu löschen.</p>
                    </div>
                    <div style="display: inline-block; vertical-align: top; margin-left: 10px;">
                        <select name="so_scheduler_enabled" id="so_scheduler_enabled">
                            <option value="1" <?php selected('1', $so_scheduler_enabled); ?>>Ja</option>
                            <option value="0" <?php selected('0', $so_scheduler_enabled); ?>>Nein</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start;">
                    <div style="display: inline-block; width: 250px; text-align: left;">
                        <label style="font-weight: bold; vertical-align: top;" for="so_scheduler_time">Uhrzeit:</label>
                        <p style="margin-top: 0;">Uhrzeit für das automatische durchführen der Buchungssicherung.</p>
                    </div>
                    <div style="display: inline-block; vertical-align: top; margin-left: 10px;">
                        <input type="text" name="so_scheduler_time" id="so_scheduler_time" value="<?php echo esc_attr($so_scheduler_time); ?>" style="display: inline-block; width: 100px;" pattern="\d{1,2}:\d{2}">
                        <span style="display: inline-block; margin-left: 5px;">(Format: Stunde und Minute, z.B. 23:00)</span>
                    </div>
                </div>
            </div>
            <div style="padding: 10px; margin-bottom: 20px;  border-left: 3px solid #012c6d;">
                <h3>Steuerung der Kursschliessung</h3>
                <div style="display: flex; align-items: flex-start;">
                    <div style="display: inline-block; width: 250px; text-align: left;">
                        <label style="font-weight: bold; vertical-align: top;" for="so_kurs_close_at" >Uhrzeit der Kursschliessung:</label>
                        <p style="margin-top: 0;">Setzt den Zeitpunkt, ab wann der jeweilge Kurs nicht mehr bebucht werden kann.</p>
                    </div>
                    <div style="display: inline-block; vertical-align: top; margin-left: 10px;">
                        <select name="so_kurs_close_at" id="so_kurs_close_at" style="display: inline-block;">
                            <option value="so_close_kurs_at_start_time" <?php selected('so_close_kurs_at_start_time', $so_kurs_close_at); ?>>Zum Kursbeginn</option>
                            <option value="so_close_kurs_at_15_minutes_before_start_time" <?php selected('so_close_kurs_at_15_minutes_before_start_time', $so_kurs_close_at); ?>>15 Minuten vor Kursbeginn</option>
                            <option value="so_close_kurs_at_30_minutes_before_start_time" <?php selected('so_close_kurs_at_30_minutes_before_start_time', $so_kurs_close_at); ?>>30 Minuten vor Kursbeginn</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="padding: 10px; margin-bottom: 20px; background-color:rgb(235, 235, 235);  border-left: 3px solid #012c6d;">
                <h3>Kennzeichnung fester Gruppen</h3>
                <div style="display: flex; align-items: flex-start;">
                    <div style="display: inline-block; width: 250px; text-align: left;">
                        <label style="font-weight: bold; vertical-align: top;" for="so_kurs_names" >Feste Gruppen:</label>
                        <p style="margin-top: 0;">Wähle die Kursnamen aus, welche feste Kurse sind und <u>keine</u> Buchungsmöglichkeit besitzen sollen.</p>
                    </div>
                    <div style="display: inline-block; vertical-align: top; margin-left: 10px;">
                    <?php
               
                        echo '<select name="so_kurs_names[]" id="so_kurs_names" multiple style="display: inline-block; width: 400px; height: 20em;">';

                        $postTypSlug = so_dachsbau_post_type_settings();
                        $posts = get_posts( array(
                            'post_type' => $postTypSlug["slug"],
                            'orderby' => 'title',
                            'order' => 'ASC',
                            'posts_per_page' => -1 // Alle Posts abrufen, nicht paginieren
                        ) );

                        foreach ( $posts as $post ) {
                            setup_postdata( $post );
                            $event_title = $post->post_title;// Den Titel des Posts abrufen
                            $event_name = $post->post_name; // Den post_name des Posts abrufen
                            $selected = '';
                            if ( isset( $_POST['so_kurs_names'] ) && in_array( $event_name, $_POST['so_kurs_names'] ) ) {
                                $selected = 'selected';
                            } elseif ( ! empty( $so_kurs_names ) && in_array( $event_name, $so_kurs_names ) ) {
                                $selected = 'selected';
                            }
                            echo '<option value="' . $event_name . '" ' . $selected . '>' . $event_title . '</option>';
                        }

                        wp_reset_postdata();
                        echo '</select>';
                    ?>

                    </div>
                </div>
                <div style="display: flex; align-items: flex-start;">
                    <div style="display: inline-block; width: 250px; text-align: left;">
                        <label style="font-weight: bold; vertical-align: top;" for="so_kurs_names_description">Bezeichnung:</label>
                        <p style="margin-top: 0;">Geben Sie hier die Bezeichung der festen Gruppe an.</p>
                    </div>
                    <div style="display: inline-block; vertical-align: top; margin-left: 10px;">
                            <input type="text" name="so_kurs_names_description" id="so_kurs_names_description" value="<?php echo esc_attr($so_kurs_names_description); ?>">
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start;">
                    <div style="display: inline-block; width: 250px; text-align: left;">
                        <label style="font-weight: bold; vertical-align: top;" for="so_kurs_names_description">eMail Info:</label>
                        <p style="margin-top: 0;">Soll ein Link mit einer eMail Adresse gezeigt werden?.</p>
                    </div>
                    <div style="display: block; margin-top: 10px; padding: 0px 5px 0px 10px">
                        <select name="so_kurs_strong_group_mail" id="so_kurs_strong_group_mail">
                            <option value="1" <?php selected('1', $so_kurs_strong_group_mail); ?>>Ja</option>
                            <option value="0" <?php selected('0', $so_kurs_strong_group_mail); ?>>Nein</option>
                        </select>
                    </div>
                    <div style="display: block; margin-top: 10px;">
                        <label style="vertical-align: top; padding: 0px 5px 0px 10px;" for="so_kurs_online_search_name" >eMailadresse:</label>
                        <input type="text" name="so_kurs_strong_group_mail_adresse" id="o_kurs_strong_group_mail_adresse" value="<?php echo esc_attr($so_kurs_strong_group_mail_adresse); ?>">
                    </div>
                </div>
            </div>
            <div style="padding: 10px; margin-bottom: 20px;  border-left: 3px solid #012c6d;">
                <h3>Kennzeichnung online Gruppen</h3>
                <div style="display: flex; align-items: flex-start;">
                    <div style="display: block; width: 250px; text-align: left;">
                        <label style="font-weight: bold; vertical-align: top;" for="so_kurs_names_color" >Onlinekurse:</label>
                        <p style="margin-top: 0;">Hier kannst du festlegen, mit welchen Farben ein Onlinkurs hervorgehoben werden soll. </p>
                    </div>
                    <div style="display: block; margin-top: 10px; padding: 0px 5px 0px 10px;">
                        <label style="vertical-align: top;" for="so_kurs_online_name_color_background" >Hintergrund:</label>
                        <input type="color" name="so_kurs_online_name_color_background" id="so_kurs_online_name_color_background" value="<?php echo esc_attr($so_kurs_online_name_color_background); ?>">
                    </div>
                    <div style="display: block; margin-top: 10px; padding: 0px 5px 0px 10px;">
                        <label style="vertical-align: top;" for="so_kurs_online_name_color_text" >Textfarbe:</label>
                        <input type="color" name="so_kurs_online_name_color_text" id="so_kurs_online_name_color_text" value="<?php echo esc_attr($so_kurs_online_name_color_text); ?>">
                    </div>
                    <div style="display: block; margin-top: 10px;">
                        <label style="vertical-align: top; padding: 0px 5px 0px 10px;" for="so_kurs_online_search_name" >Suchname:</label>
                        <input type="text" name="so_kurs_online_search_name" id="so_kurs_online_search_name" value="<?php echo esc_attr($so_kurs_online_search_name); ?>">
                    </div>
                </div>
            </div>
            <div style="padding: 10px; margin-bottom: 20px; background-color:rgb(235, 235, 235);  border-left: 3px solid #012c6d;">
                <h3>Startzeit erneute Buchungen</h3>
                <div style="display: flex; align-items: flex-start;">
                    <div style="display: inline-block; width: 250px; text-align: left;">
                        <label style="font-weight: bold; vertical-align: top;" for="so_kurs_close_at" >Buchungsfreigabe nächster Tag:</label>
                        <p style="margin-top: 0;">Setzt die Uhrzeit für den Folgetag, ab wann die Kurse wieder buchbar sind.</p>
                    </div>
                    <div style="display: inline-block; vertical-align: top; margin-left: 10px;">
                        <input type="text" name="so_kurs_booking_open_time" id="so_kurs_booking_open_time" value="<?php echo esc_attr($so_kurs_booking_open_time); ?>" style="display: inline-block; width: 100px;" pattern="\d{1,2}:\d{2}">
                        <span style="display: inline-block; margin-left: 5px;">(Format: Stunde und Minute, z.B. 12:00)</span>
                    </div>
                </div>
            </div>
              
            <div style="padding: 10px; margin-bottom: 20px; border-left: 3px solid #012c6d;">
                <h3>Auwertung und Dokumentation</h3>    
                <div style="display: flex; align-items: flex-start;">
                        <div style="display: inline-block; width: 250px; text-align: left;">
                            <label style="font-weight: bold; vertical-align: top;" for="so_kurs_close_at">Name des PDF-Exports:</label>
                            <p style="margin-top: 0;">Geben Sie hier den Namen des PDF-Exports ein. Der Dateityp .csv wird automatisch angehängt.</p>
                        </div>
                        <div style="display: inline-block; vertical-align: top; margin-left: 10px;">
                            <input type="text" name="so_pdf_export_name" id="so_pdf_export_name" value="<?php echo esc_attr($pdf_export_name); ?>">
                            <span style="display: inline-block; margin-left: 5px;">.csv</span>
                        </div>
                    </div>
                </div>
                <!-- Submit-Button -->
            <div style="margin-top: 20px;"  border-left: 3px solid #012c6d;>
                    <button type="submit" name="submit" class="button button-primary" style="background-color: #d0e3ff; color: #2271b1;"><u>ALLE</u> Änderungen speichern</button>
            </div>
        </form>
    </div>
    <?php
}
/*
function so_booking_page(){
    $instance = SP_Bookings::get_instance();
    $instance->screen_option();
}
*/

function so_booking_export_page() {
    SP_Bookings::bookings_export_page();
}

function so_schedule_booking_page() {
    require_once('class/so-kurs-scheduler/booking-save-admin-table-class.php');
    $booking_list_table = new SO_EventBookingTable();
    $booking_list_table->prepare_items();
    ?>
    <div class="wrap">
        <h2>Gesicherte Buchungen verwalten</h2>
        <p>Hier siehst du alle Buchungen, welche automatisch vor der automatischen Wiedereröffung der Buchungen für einen Kurs gelöscht wurden.</p>
        <p>Du kannst Buchungen in Ruhe nach erfolgter Prüfung exportieren und/oder löschen. :)</p>
        <form method="post">
         <input type="hidden" name="page" value="wp_list_table_class" />
        <h3>Vergangene Buchungen </h3>
        <?php $booking_list_table->display(); ?>
        </form>
    </div>
    <?php
}

function so_coach_booking_page() {
    require_once('class/so-kurs-scheduler/booking-current-admin-table-class.php');
    $coach_booking_list_table = new SO_COACH_List_Table();

    // Die Filterdaten auf das Filterfeld anwenden
    echo '<div class="wrap">';
    echo '<h2>Dachse Coach Bereich</h2>';
    echo '<p>Hier wird den Trainern ermöglicht, für die an diesem Tag stattfindenden Kurs die Mitglieder als "Anwesend" oder "Abwesend" zu markieren.</p>';
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="so_coach_booking_page" />';
    echo '<h3>Übersicht</h3>';
    
    
    // Tabelle vorbereiten
    $coach_booking_list_table->prepare_items();
    
    // Die Tabelle anzeigen
    $coach_booking_list_table->display();

    echo '</form>';
    echo '</div>';
}

function so_coach_booking_page_coach() {
    require_once('class/so-kurs-scheduler/booking-current-admin-table-class.php');
    $coach_booking_list_table_coach = new SO_COACH_List_Table();

    // Die Filterdaten auf das Filterfeld anwenden
    echo '<div class="wrap">';
    echo '<h2>Dachse Coach Bereich</h2>';
    echo '<p>Hier wird den Trainern ermöglicht, für die an diesem Tag stattfindenden Kurs die Mitglieder als "Anwesend" oder "Abwesend" zu markieren.</p>';
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="so_dachsbau-karow-coach-menu" />';
    echo '<h3>Übersicht</h3>';
    
    
    // Tabelle vorbereiten
    $coach_booking_list_table_coach->prepare_items();
    
    // Die Tabelle anzeigen
    $coach_booking_list_table_coach->display();

    echo '</form>';
    echo '</div>';
}

function so_current_booking_page() {
    require_once('class/so-kurs-scheduler/booking-current-admin-table-class.php');
    $booking_list_table = new SO_CurrentBookingTable();
    $booking_list_table->prepare_items();
    ?>
    <div class="wrap">
        <h2>Gesicherte Buchungen verwalten</h2>
        <p>Hier siehst du alle Buchungen, welche automatisch vor der automatischen Wiedereröffung der Buchungen für einen Kurs gelöscht wurden.</p>
        <p>Du kannst Buchungen in Ruhe nach erfolgter Prüfung exportieren und/oder löschen. :)</p>
        <form method="post">
         <input type="hidden" name="page" value="wp_list_table_class" />
        <h3>Vergangene Buchungen </h3>
        <?php $booking_list_table->display(); ?>
        </form>
    </div>
    <?php
}

function my_screen_options_show_screen_filter() {
    $current_screen = get_current_screen();
    if ( $current_screen->id === 'dachsbau-admin_page_so_schedule-booking' ) {
        echo '<div id="screen-options-wrap" class="" tabindex="-1" aria-label="Tab „Ansicht anpassen“" style="display: block;">
        <form id="adv-settings" method="post">
                <fieldset class="metabox-prefs">
                <legend>Spalten</legend>
                    <label><input class="hide-column-tog" name="buchungszeit-hide" type="checkbox" id="buchungszeit-hide" value="author" checked="checked">Autor</label>
                    <label><input class="hide-column-tog" name="loeschstatus-hide" type="checkbox" id="loeschstatus-hide" value="categories" checked="checked">Kategorien</label>
                    <label><input class="hide-column-tog" name="kursende-hide" type="checkbox" id="kursende-hide" value="tags" checked="checked">Schlagwörter</label>
                </fieldset>
                <fieldset class="screen-options">
                    <legend>Seitennummerierung</legend>
                        <label for="edit_post_per_page">Einträge pro Seite:</label>
                        <input type="number" step="1" min="1" max="999" class="screen-per-page" name="wp_screen_options[value]" id="edit_post_per_page" maxlength="3" value="20">
                        <input type="hidden" name="wp_screen_options[option]" value="edit_post_per_page">
                </fieldset>
                <p class="submit"><input type="submit" name="screen-options-apply" id="screen-options-apply" class="button button-primary" value="Übernehmen"></p>
        </form>
        </div>';
        return true;
    }
    return true;
}
//add_filter('screen_options_show_screen', 'my_screen_options_show_screen_filter');

function so_member_checker_file_upload() {
    if(isset($_POST["submit"])) {
        global $wpdb;
        
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Name der Tabelle, in die importiert werden soll
        $table_name = $wpdb->prefix . 'mitglieder';
        echo '<input type="hidden" id="table-name" value="' . $table_name . '">';
       
        // Löschen der vorhandenen Einträge in der Tabelle
        $wpdb->query("TRUNCATE TABLE $table_name");

        // Pfad zur CSV-Datei
        $file_path = $_FILES["fileToUpload"]["tmp_name"];

        // Überprüfung, ob eine Datei hochgeladen wurde
        if(empty($file_path)) {
            $error_message = "Es wurde keine Datei hochgeladen.";
        } else {
            // Öffnen der CSV-Datei
            $file = fopen($file_path, 'r');

            // Schleife zum Lesen der CSV-Datei | MitglNr;Anrede;Vorname;Nachname
            while (($data = fgetcsv($file, 0, ';')) !== FALSE) {
                // Einfügen der Daten in die Tabelle
                $wpdb->insert(
                    $table_name,
                    array(
                        'Anrede' => $data[1],
                        'MitglNr' => $data[0],
                        'Vorname' => $data[2],
                        'Nachname' => $data[3]
                    )
                );
            }

            if ($wpdb->last_error) {
                wp_die("Fehler beim Importieren der CSV-Datei: " . $wpdb->last_error);
             }

            // Schließen der CSV-Datei
            fclose($file);

            $success_message = "Die Datei wurde erfolgreich importiert.";
        }
    }

    // HTML-Formular für den Datei-Upload
    ?>
    <div class="wrap">
        <h1>Mitgliederliste Import</h1>    
        <h2>Bitte wählen Sie eine gültige csv Datei aus.</h2>
        <p>Eine gültige Datei <u>muss</u> wie folgt aufgebaut sein:</p>
        <ul>
            <li>mitglNr</li>
            <li>anrede</li>
            <li>vorname</li>
            <li>nachname</li>
        </ul>
        <p>Die Datei muss die oben genannten <b>4 Spalten</b> besitzen. Jede Spalte muss mit einem Semikolon getrennt werden. </p>
        <p>(z.B.) "000004";"Herr";"Stephan";"Christ" </p>
        <h3>!Achtung!</h3>
        <p>Jeder Import LÖSCHT vorab die alte Mitgliederliste aus der Datenbank!</p>
        <?php if(isset($error_message)) { ?>
            <div class="notice notice-error"><p><?php echo $error_message; ?></p></div>
        <?php } ?>
        <?php if(isset($success_message)) { ?>
            <div class="notice notice-success"><p><?php echo $success_message; ?></p></div>
        <?php } ?>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Hochladen" name="submit">
        </form>
    </div>
    <?php
}

function so_mitgliederliste()
{
  ?>
  <div class="wrap">
      <h1>Mitgliederübersicht</h1>
      <?php
      // check user capabilities
      if ( ! current_user_can( 'manage_options' ) ) {
          return;
      }

      $myListTable = new MembersTable();
      echo '<div class="wrap">';

      $requestPage = sanitize_text_field($_REQUEST["page"]);
      $html = '';
      $html .=  '<form id="events-filter" method="get"><input type="hidden" name="page" value="' . sanitize_text_field($requestPage) . '" />';
      $myListTable->prepare_items(); 
      echo '<form method="post">
         <input type="hidden" name="page" value="wp_list_table_class" />';
      $myListTable->search_box('Finden', 'search');
      echo '</form><h3>Mitgliederliste</h3>';
      
      $myListTable->display(); 
      $html .= '</form></div></div>'; 

      echo $html;
  }

  
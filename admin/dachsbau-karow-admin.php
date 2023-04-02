<?php

/*
* ###############################
* ADD Admin Menu
* ###############################
*/
function so_DachsbauKarowAdminMenu()
{
    add_menu_page('Dachsbau-Admin', 'Dachsbau-Admin', 'manage_options', 'so_dachsbau-karow-admin-menu', 'so_dachsbau_admin_info_page', 'dashicons-list-view', 5);

    // Add sub-menu pages
    add_submenu_page('so_dachsbau-karow-admin-menu', 'Mitgliederliste', 'Mitgliederliste', 'manage_options', 'so_member-checker-import', 'so_mitgliederliste');
    add_submenu_page('so_dachsbau-karow-admin-menu', 'Mitgliederliste Import', 'Mitgliederliste Import', 'manage_options', 'so_member_checker_file_upload', 'so_member_checker_file_upload');
    add_submenu_page('so_dachsbau-karow-admin-menu','Gelöscht Buchungen','Gelöscht Buchungen','manage_options','so_schedule-booking','so_schedule_booking_page');
    add_submenu_page('so_dachsbau-karow-admin-menu','Konfiguration','Konfiguration','manage_options','so_dachsbau_admin_config','so_dachsbau_admin_config');

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
        <div style="width: 100%;"><hr><h3>Willkommen im Dachsbau Admin Bereich</h3></div>
            <a href="<?php echo admin_url('admin.php?page=so_dachsbau_admin_config'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Konfiguration</h3>
                <p>Nehme hier optionale Einstellungen vor </p>
            </a>
            <a href="<?php echo admin_url('admin.php?page=so_member-checker-import'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Mitgliederliste</h3>
                <p>Verwalte hier die Mitgliederliste.</p>
            </a>
            <a href="<?php echo admin_url('admin.php?page=so_member_checker_file_upload'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Mitgliederliste Import</h3>
                <p>Lade hier eine aktuelle neue Mitgliederliste hoch.</p>
            </a>
            <a href="<?php echo admin_url('admin.php?page=so_schedule-booking'); ?>" class="card" style="background-color: #d0e3ff; color: #d012c6d; text-align: center; padding: 20px; width: 300px; border-radius: 10px; transition: background-color 0.2s ease;">
                <h3>Gelöschte Buchungen</h3>
                <p>Verwalte hier automatisch gelöschte Buchungen.</p>
            </a>
        </div>
    </div>
    <?php
}

function so_dachsbau_admin_config() {
    require_once('class/so-kurs-scheduler/booking-save-admin-table-class.php');
    $booking_list_table = new SO_EventBookingTable();
    $booking_list_table->prepare_items();
    ?>
    <div class="wrap">
        <h2>Konfigurationen</h2>
        <p>Hier kannst du diverse Konfigurationen vornehmen.</p>
    </div>
    <?php
}

function so_schedule_booking_page() {
    require_once('class/so-kurs-scheduler/booking-save-admin-table-class.php');
    $booking_list_table = new SO_EventBookingTable();
    $booking_list_table->prepare_items();
    ?>
    <div class="wrap">
        <h2>Gelöscht Buchungen verwalten</h2>
        <p>Hier siehst du alle Buchungen, welche automatisch vor der automatischen Wiedereröffung der Buchungen für einen Kurs gelöscht wurden.</p>
        <p>Du kannst Buchungen in Ruhe nach erfolgter Prüfung löschen. :)</p>
        <form method="post">
         <input type="hidden" name="page" value="wp_list_table_class" />
        <?php $booking_list_table->search_box('Finden', 'search');?>
        <h3>Vergangene Buchungen </h3>
        <?php $booking_list_table->display(); ?>
        </form>
    </div>
    <?php
}


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

/* 
* ####################
* ADD Admin Home
* ####################
*/
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
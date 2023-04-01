<?php

/*
* ###############################
* ADD Admin Menu
* ###############################
*/
function member_checker_creator()
{
  add_menu_page('Mitglieder', 'Mitglieder-Admin', 'manage_options', 'member-checker-menu', 'member_checker_home', 'dashicons-list-view', 5);
  add_submenu_page('member-checker-menu', 'Mitgliederliste Import', 'Mitgliederliste Import', 'manage_options', 'member-checker-import', 'member_checker_file_upload');
}
add_action('admin_menu', 'member_checker_creator');


function member_checker_file_upload() {
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
function member_checker_home()
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
<?php

/*
* ###############################
* ADD Admin Menu
* ###############################
*/
function member_checker_creator()
{
  add_menu_page('Mitglieder', 'Mitglieder-Admin', 'manage_options', 'member-checker-menu', 'member_checker_home', 'dashicons-list-view', 5);
  
  // Add new submenu page for file upload
  add_submenu_page( 'member-checker-menu', 'Datei importieren', 'Datei importieren', 'manage_options', 'member-checker-file-upload', 'member_checker_file_upload' );
}
add_action('admin_menu', 'member_checker_creator');


function member_checker_file_upload() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'my_table_name';
  
  if(isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
      $file = $_FILES['csv_file']['tmp_name'];
      $handle = fopen($file, "r");
      $row = 0;
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          if ($row > 0) {
              $wpdb->insert($table_name, array('column1' => $data[0], 'column2' => $data[1]));
          }
          $row++;
      }
      fclose($handle);
      echo '<div class="notice notice-success is-dismissible"><p>CSV-Datei erfolgreich importiert.</p></div>';
  } elseif(isset($_FILES['csv_file'])) {
      echo '<div class="notice notice-error is-dismissible"><p>Es ist ein Fehler beim Importieren der CSV-Datei aufgetreten.</p></div>';
  }
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
      <form enctype="multipart/form-data" method="post">
          <input type="hidden" name="action" value="upload_csv">
          <label for="csv_file">CSV-Datei auswählen:</label>
          <input type="file" name="csv_file" id="csv_file" accept=".csv">
          <input type="submit" value="CSV-Datei importieren" class="button button-primary">
      </form>
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
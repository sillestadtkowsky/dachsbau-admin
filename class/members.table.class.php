<?php

if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class MembersTable extends WP_List_Table
{
  function get_columns()
  {
    $columns = array(
      'mitgliedsnummer'  => 'Mitgliedsnummer',
      'anrede'    => 'Anrede',
      'vorname' => 'Vorname',
      'nachname' => 'Nachname'
    );
    return $columns;
  }

  function extra_tablenav( $which ) {
    if ( 'top' !== $which ) {
      return;
    }
    echo '<input type="button" id="newMember" class="button action" value="Neues Mitglied">';
    echo '<input type="button" id="cancelNewMember" class="button action" value="abbrechen">';
    echo $this->newFields();
}
 

  function prepare_items($search ='')
  {
   
    global $wpdb;
    $searchcol = array(
      'mitgliedsnummer',
      'anrede',
      'vorname',
      'nachname'
    );

    $search = ( isset($_REQUEST['s']) ) ? sanitize_text_field($_REQUEST['s']) : false;
    $do_search = '';
    
    if(null!=$search){
      $do_search .= ( $search ) ? $wpdb->prepare(" WHERE mitglNr LIKE '%%%s%%' OR vorname LIKE '%%%s%%' or nachname LIKE '%%%s%%'", $search , $search, $search) : '';
    }

    $data = MC_DB::getMembersArray($do_search);

    $per_page = 10;
    $current_page = $this->get_pagenum();
    $total_items = count($data);
    $this->set_pagination_args( array('total_items' => $total_items,'per_page' => $per_page ));
    
    if (1 < $current_page) {
      $offset = $per_page * ($current_page - 1);
    } else {
      $offset = 0;
    }

    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    usort($data, array(&$this, 'usort_reorder'));
    $this->items = array_slice($data,(($current_page-1)*$per_page),$per_page);
    $this->process_bulk_action();
  }

  function column_mitgliedsnummer($item)
  {
    $edit_nonce = wp_create_nonce( 'edit_member' );
    $actions = array(
      'bearbeiten'    => '<a href="#" class="editMember" data-categoryid="'. esc_html($item['mitgliedsnummer']) .'">Bearbeiten</a>',
      'löschen'    => sprintf('<a onclick="return confirm(\'Möchtest du den Eintrag wirklich löschen?\');" href="?page=member-checker-menu&action=%s&mitgliedsnummer[]=%s&_wpnonce=%s">löschen</a>', 'delete', esc_html($item['mitgliedsnummer']), sanitize_text_field($edit_nonce))
    );

    $html='';
    $html .= '<div class="editMembers" id="editMembers_'.esc_html($item['mitgliedsnummer']).'">
    <form method="POST" id="editMembers_form">
      <div class="divRow">
        <div class="divCell"><b>Mitgliedsnummer</b>
          <input type="text" name="mitgliedsnummer" id="mitgliedsnummer" value="'.esc_html($item['mitgliedsnummer']).'">
          <input type="hidden" name="mitgliedsnummer_update" id="mitgliedsnummer_update" value="'.esc_html($item['mitgliedsnummer']).'">
        </div>
        <div class="divCell"><b>Anrede</b>
          <input type="text" name="anrede" id="anrede" value="'.esc_html($item['anrede']).'">
        </div>
        <div class="divCell"><b>Vorname</b>
          <input type="text" name="vorname" id="vorname" value="'.esc_html($item['vorname']).'">
        </div>
        <div class="divCell"><b>Nachname</b>
          <input type="text" name="nachname" id="nachname" value="'.esc_html($item['nachname']).'">
        </div>
        <div class="divCell" style="display:flex;">
          <button style=" cursor:pointer;" type="submit" name="submit">speichern</button>
          <button style=" cursor:pointer;" type="submit" name="cancelEditMember">abbrechen</button>
        </div>
      </div>
    </form>
  </div>';
  
  if(isset($_POST['submit'])){
    $id=$_POST['mitgliedsnummer'];
    $idUpdate=$_POST['mitgliedsnummer_update'];
    $anrede=sanitize_text_field($_POST['anrede']);
    $vorname=sanitize_text_field($_POST['vorname']);
    $nachname=sanitize_text_field($_POST['nachname']);


    if(null!=$id && strlen($id) == 6){
      MC_DB::updateMember($id, $anrede, $vorname, $nachname, $idUpdate);
      wp_redirect( esc_url( add_query_arg() ) );
    }
  }
    return sprintf('%1$s %2$s', $item['mitgliedsnummer'], $this->row_actions($actions) ) . $html;
    
  }

  function column_default($item, $column_name)
  {
    switch ($column_name) {
      case 'mitgliedsnummer':
      case 'anrede':
      case 'vorname':
      case 'nachname':
        return $item[$column_name];
      default:
        return print_r($item, false); //Show the whole array for troubleshooting purposes
    }
  }

  function get_sortable_columns()
  {
    $sortable_columns = array(
      'mitgliedsnummer'  => array('mitgliedsnummer', true),
      'anrede' => array('anrede', true),
      'vorname'   => array('vorname', true),
      'nachname'   => array('nachname', true),
    );
    return $sortable_columns;
  }

  function usort_reorder($a, $b)
  {
    $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'mitgliedsnummer';
    $order = (!empty($_GET['order'])) ? sanitize_text_field($_GET['order']) : 'DESC';
    $testresult = strcmp($a[$orderby], $b[$orderby]);
    return ($order === 'desc') ? $testresult : -$testresult;
  }


 public function process_bulk_action()
  {

    //Detect when a bulk action is being triggered...
    if ( 'delete' === $this->current_action() ) {
        if ( false ) {
          die( 'Funktion ist nicht erlaubt' );
        }
        else {
        $delete_ids = $_GET['mitgliedsnummer'];
        
        // loop over the array of record IDs and delete them
        foreach ( $delete_ids as $id ) {
          MC_DB::deleteMembers( sanitize_text_field($id) );
        }
        wp_redirect( esc_url( add_query_arg() ) );
        exit;
      }
    }
  }
  
  function newFields()
  {
    $html='';
    $html .= '<div class="newMember tableContainer">
      <form method="POST">
      <div class="divRow">
        <div class="divCell"><b>Mitgliedsnummer</b>
          <input type="text" name="mitgliedsnummer" id="mitgliedsnummer" value="">
        </div>
        <div class="divCell"><b>Anrede</b>
          <input type="text" name="anrede" id="anrede" value="">
        </div>
        <div class="divCell"><b>Vorname</b>
          <input type="text" name="vorname" id="vorname" value="">
        </div>
        <div class="divCell"><b>Nachname</b>
          <input type="text" name="nachname" id="nachname" value="">
        </div>
        <div class="divCell" style="display:flex;">
          <button style=" cursor:pointer;" type="submit" name="addMember">speichern</button>
        </div>
      </div>
    </form>
  </div>';
  
  if(isset($_POST['addMember'])){
    $id=$_POST['mitgliedsnummer'];
    $anrede=sanitize_text_field($_POST['anrede']);
    $vorname=sanitize_text_field($_POST['vorname']);
    $nachname=sanitize_text_field($_POST['nachname']);

    if(null!=$id && strlen($id) == 6){
      MC_DB::insertMember($id, $anrede, $vorname, $nachname);
    }else{
      echo 'Mitgliedsnummer nicht lang genug';
    }
  }
   return $html;
  }
}

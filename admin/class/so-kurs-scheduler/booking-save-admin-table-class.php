<?php

// WP_List_Table-Klasse laden
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Einbinden von jQuery
wp_enqueue_script('jquery');

// jQuery-Code zum Steuern der Checkboxen
?>

<script>
jQuery(document).ready(function($) {
    // Checkbox in der Spaltenüberschrift auswählen
    var checkAll = $('#check-all');

    // Event-Handler für das Ändern der Checkbox in der Spaltenüberschrift
    checkAll.change(function() {
        // Aktivieren oder Deaktivieren aller Checkboxen in der Spalte ID basierend auf dem Zustand der Checkbox in der Spaltenüberschrift
        $('input[name="booking_id[]"]').prop('checked', this.checked);
    });

    // Event-Handler für das Ändern einer Checkbox in einer Zeile
    $('input[name="booking_id[]"]').change(function() {
        // Überprüfen, ob alle Checkboxen in der Spalte ID ausgewählt sind, und die Checkbox in der Spaltenüberschrift entsprechend aktivieren oder deaktivieren
        checkAll.prop('checked', $('input[name="booking_id[]"]:checked').length == $('input[name="booking_id[]"]').length);
    });
});
</script>

<?php
// Die Klasse erstellen, die von WP_List_Table erbt
class SO_EventBookingTable extends WP_List_Table
{
    /**
     * Definiert die Spaltennamen und Daten der Tabelle.
     *
     * @return Array Ein Array, das die Spaltennamen und die Daten enthält.
     */
    public function get_columns()
    {
        $columns = array(
            'Id' => '<span style="margin-left:4px;"><input type="checkbox" id="check-all"/>&nbsp;&nbsp;Id</span>', // Checkbox-Spalte zum Löschen
            'Kurs' => 'Kurs',
            'Buchungsdatum' => 'Buchungsdatum',
            'Buchungszeit' => 'Buchungszeit',
            'Kursbeginn' => 'Kursbeginn',
            'Kursende' => 'Kursende',
            'Mitgliedsname' => 'Mitgliedsname',
            'Mail' => 'Mail',
            'Loeschdatum' => 'Loeschdatum'
        );
        return $columns;
    }

    /**
     * Definiert die Filterungsoptionen für die Tabelle.
     *
     * @return Array Ein Array, das die Filteroptionen enthält.
     */
    public function get_filterable_columns()
    {
        $filterable_columns = array(
            'Kurs' => 'Kurs',
            'Buchungsdatum' => 'Buchungsdatum',
            'Kursbeginn' => 'Kursbeginn',
            'Mitgliedsname' => 'Mitgliedsname',
            'Mail' => 'Mail'
        );
        return $filterable_columns;
    }

    #
    /**
     * Definiert die Daten, die in der Tabelle angezeigt werden sollen.
     */
    /**
     * Definiert die Daten, die in der Tabelle angezeigt werden sollen.
     */
    public function prepare_items($search ='')
    {
        
        global $wpdb;
        $searchcol = array(
          'Kurs',
          'Buchungsdatum',
          'Kursbeginn',
          'Mitgliedsname'
        );
    
        $do_search = '';
        $search = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';

        if(null!=$search){
            $do_search .= ( $search ) ? $wpdb->prepare(" WHERE bs.booking_id = %s OR p.post_title LIKE '%%%s%%' OR u.user_nicename LIKE '%%%s%%' or u.user_email LIKE '%%%s%%'", $search , $search , $search, $search) : '';
          }

        $query = "SELECT bs.booking_id as Id, p.post_title AS Kurs, 
                    DATE_FORMAT(bs.booking_datetime,'%d.%m.%Y') AS Buchungsdatum,
                    DATE_FORMAT(bs.booking_datetime,'%H:%i') AS Buchungszeit,
                    DATE_FORMAT(ih.start,'%H:%i') AS Kursbeginn,
                    DATE_FORMAT(ih.end,'%H:%i') AS Kursende,
                    u.user_nicename AS Mitgliedsname,
                    u.user_email AS Mail,
                    DATE_FORMAT(bs.booking_delete_datetime,'%d.%m.%Y - %H:%i') AS Loeschdatum
                  FROM {$wpdb->prefix}event_booking_saves AS bs
                  LEFT JOIN {$wpdb->prefix}event_hours AS ih ON ih.event_hours_id=bs.event_hours_id 
                  LEFT JOIN {$wpdb->prefix}posts AS p ON p.id=ih.event_id
                  LEFT JOIN {$wpdb->prefix}users AS u ON u.id=bs.user_id " . $do_search;
        
        // Sortierung hinzufügen
        $orderby = $this->get_orderby();
        $order = $this->get_order();
        if (!empty($orderby)) {
            $query .= " ORDER BY $orderby $order";
        }
    
        // Elemente pro Seite festlegen
        $per_page = 10;
        $current_page = $this->get_pagenum();
    
        // Daten für die Tabelle abrufen
        //$query .= " LIMIT " . ($current_page - 1) * $per_page . ", $per_page";
        $data = $wpdb->get_results($query);
        $total_items = count($data);
    
        // Tabelle konfigurieren
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        usort($data, array(&$this, 'usort_reorder'));
        $this->items = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->process_bulk_action();
    }

    public function column_default( $item, $column_name ) {
        $item = (array) $item; 
        switch ( $column_name ) {
            case 'Id':
            case 'Kurs':
            case 'Buchungsdatum':
            case 'Buchungszeit':
            case 'Kursbeginn':
            case 'Kursende':
            case 'Mitgliedsname':
            case 'Mail':
            case 'Loeschdatum':
                return $item[ $column_name ];
            default:
                return print_r( $item, false ) ;
        }
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'Id' => array('Id', true),
            'Kurs' => array('Kurs', true),
            'Buchungsdatum' => array('Buchungsdatum', true),
            'Buchungszeit' => array('Buchungszeit', true),
            'Kursbeginn' => array('Kursbeginn', true),
            'Kursende' => array('Kursende', true),
            'Mitgliedsname' => array('Mitgliedsname', true),
            'Mail' => array('Mail', true),
            'Loeschdatum' => array('Loeschdatum', true)
        );
        return $sortable_columns;
    }

    function usort_reorder($a, $b)
    {
        $a = (array) $a; 
        $b = (array) $b; 
        $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'Id';
        $order = (!empty($_GET['order'])) ? sanitize_text_field($_GET['order']) : 'DESC';
        $testresult = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'desc') ? $testresult : -$testresult;
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Löschen'
        );
        return $actions;
    }

    public function column_Id($item)
    {
        $item = (array) $item; 
        $delete_nonce = wp_create_nonce('event_booking_delete');
        $title = '<strong>' . $item['Id'] . '</strong>';
        $actions = array(
            'delete' => sprintf('<a href="?page=%s&action=%s&booking_id=%s&_wpnonce=%s">Löschen</a>', esc_attr($_REQUEST['page']), 'event_booking_delete', absint($item['Id']), $delete_nonce)
        );
        return sprintf('<span style="margin-left:9px;"><input type="checkbox" name="booking_id[]" value="%d" />&nbsp;&nbsp;'  .$title ."</span>" , absint($item['Id']));
    }

    public function process_bulk_action()
    {
        global $wpdb;
    
        // Bulk-Action auswerten
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        $bookings = isset($_REQUEST['booking_id']) ? $_REQUEST['booking_id'] : array();
    
        if ('delete' === $action) {
            if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
                foreach ($bookings as $booking_id) {
                    $wpdb->delete("{$wpdb->prefix}event_booking_saves", array('booking_id' => $booking_id));
                }
                // Aktualisiere die Adminseite
                wp_redirect(admin_url('admin.php?page=schedule-booking'));
                exit;
            } else {
                echo '<script>
                    var confirmed = confirm("Are you sure you want to delete the selected bookings?");
                    if (confirmed) {
                        var form = document.createElement("form");
                        form.method = "post";
                        form.action = "' . admin_url('admin.php?page=schedule-booking') . '";
                        form.innerHTML = \'<input type="hidden" name="action" value="delete">\';
                        form.innerHTML += \'<input type="hidden" name="booking_id[]" value="' . implode('"><input type="hidden" name="booking_id[]" value="', $bookings) . '">\';
                        form.innerHTML += \'<input type="hidden" name="confirm_delete" value="yes">\';
                        document.body.appendChild(form);
                        form.submit();
                    }
                </script>';
            }
        }
    }
}
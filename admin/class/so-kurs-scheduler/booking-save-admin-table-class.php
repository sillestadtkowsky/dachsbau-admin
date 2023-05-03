<?php

// WP_List_Table-Klasse laden
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

require_once 'remove-booking-cron-class.php';

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
            'Mitgliedsnummer' => 'Mitgliedsnummer',
            'Teilgenommen' => 'Teilgenommen',
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
            'Mitgliedsnummer' => 'Mitgliedsnummer',
            'Teilgenommen' => 'Teilgenommen',
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
          'Mitgliedsname',
          'Mitgliedsnummer',
          'Teilgenommen',
          'Mail'
        );
    
        $do_search = '';
        $search = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';
        $is_date = strtotime($search);

        if ($is_date !== false) {
            $searchDate = date('Y-m-d', strtotime($search));
            $do_search .= $wpdb->prepare(" WHERE bs.booking_datetime LIKE %s", array( $searchDate.'%' ));
        } else {
            $visited = strtolower($search) === 'nein'? 0 : 1;
            $do_search .= $wpdb->prepare(" WHERE bs.mitgliedsnummer = %s OR bs.booking_id = %s OR p.post_title LIKE '%%%s%%' OR bs.name LIKE '%%%s%%' OR bs.email LIKE '%%%s%%' OR bs.visited = %s", $search, $search, $search, $search, $search, $visited);
        }

        $query = "SELECT bs.booking_id as Id, p.post_title AS Kurs, 
                    DATE_FORMAT(bs.booking_datetime,'%d.%m.%Y') AS Buchungsdatum,
                    DATE_FORMAT(bs.booking_datetime,'%H:%i') AS Buchungszeit,
                    DATE_FORMAT(ih.start,'%H:%i') AS Kursbeginn,
                    DATE_FORMAT(ih.end,'%H:%i') AS Kursende,
                    bs.mitgliedsnummer as Mitgliedsnummer,
                    bs.visited as Teilgenommen,
                    bs.name as Mitgliedsname,
                    CONCAT('<a href=\"mailto:', bs.email, '\">', bs.email, '</a>') as Mail,
                    DATE_FORMAT(bs.booking_delete_datetime,'%d.%m.%Y - %H:%i') AS Loeschdatum
                  FROM {$wpdb->prefix}event_booking_saves AS bs
                  LEFT JOIN {$wpdb->prefix}event_hours AS ih ON ih.event_hours_id=bs.event_hours_id 
                  LEFT JOIN {$wpdb->prefix}posts AS p ON p.id=ih.event_id " . $do_search;
        
        // Sortierung hinzufügen
        $orderby = $this->get_orderby();
        $order = $this->get_order();
        if (!empty($orderby)) {
            $query .= " ORDER BY $orderby $order";
        }
    
        // Elemente pro Seite festlegen
        $per_page = 50;
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
            case 'Mitgliedsnummer':
            case 'Loeschdatum':
                return $item[ $column_name ];
            case 'Teilgenommen':
                if((int)$item[ $column_name ] === 1){
                    return 'Ja';
                }else{
                    return 'Nein';
                }
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
            'Mitgliedsnummer' => array('Mitgliedsnummer', true),
            'Teilgenommen' => array('Teilgenommen', true),
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
            'delete' => 'Löschen',
            'export' => 'Exportieren'
        );
        return $actions;
    }

    public function column_Id($item)
    {
        $item = (array) $item; 
        $delete_nonce = wp_create_nonce('event_booking_delete');
        $title = '<strong>' . $item['Id'] . '</strong>';
        $actions = array(
            'delete' => sprintf('<a href="?page=%s&action=%s&booking_id=%s&_wpnonce=%s">Löschen</a>', esc_attr($_REQUEST['page']), 'event_booking_delete', absint($item['Id']), $delete_nonce),
            'export' => sprintf('<a href="?page=%s&action=%s&booking_id=%s&_wpnonce=%s">Exportieren</a>', esc_attr($_REQUEST['page']), 'event_booking_export', absint($item['Id']), $delete_nonce)
        );
        return sprintf('<span style="margin-left:9px;"><input type="checkbox" name="booking_id[]" value="%d" />&nbsp;&nbsp;'  .$title ."</span>" , absint($item['Id']));
    }

    public function process_bulk_action()
    {
        global $wpdb;
    
        // Bulk-Action auswerten
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        $bookings = isset($_REQUEST['booking_id']) ? $_REQUEST['booking_id'] : array();
    
        if ('export' === $action) {
            self::handle_booking_export($bookings);
            // Aktualisiere die Adminseite
            wp_redirect(admin_url('admin.php?page=so_schedule-booking'));
            echo $bookings;
            exit;
        }

        if ('delete' === $action) {
            if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
                foreach ($bookings as $booking_id) {
                    $wpdb->delete("{$wpdb->prefix}event_booking_saves", array('booking_id' => $booking_id));
                }
                // Aktualisiere die Adminseite
                wp_redirect(admin_url('admin.php?page=so_schedule-booking'));
                exit;
            } else {
                echo '<script>
                    var confirmed = confirm("Du willst die ausgewählten Buchungen aus der Datenbank entfernen?");
                    if (confirmed) {
                        var form = document.createElement("form");
                        form.method = "post";
                        form.action = "' . admin_url('admin.php?page=so_schedule-booking') . '";
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

    public function handle_booking_export($exportBookingIds)
    {
        require_once 'remove-booking-cron-class.php';
        $so_schedule_booking_cronjob = new SOScheduleBookingCronJob;
        $bookings = $so_schedule_booking_cronjob->so_getSaveBookings($exportBookingIds);
    
        // Verhindert das Ausgeben von HTML-Code
        ob_start();
    
        // Öffnen Sie die Ausgabedatei im Schreibmodus
        $output = fopen('php://output', 'w');
        
        $pdfExportName = get_option( 'so_pdf_export_name' );

        // Setzen Sie die Header für die CSV-Datei
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $pdfExportName .'.csv');
        
        // Schleife über alle Buchungen und schreibe sie in die CSV-Datei
        
        foreach($bookings as $booking)
        {
            $data = array(
                $booking->Id,
                $booking->Kurs,
                $booking->Kursbeginn,
                $booking->Kursende,
                $booking->Buchungsdatum,
                $booking->Mitgliedsname,
                $booking->Mitgliedsnummer,
                $booking->Teilgenommen,
                $booking->Mail,               
                $booking->Buchungszeit,
                $booking->Loeschdatum
            );
    
            // Schreibe die Daten in die CSV-Datei
            fputcsv($output, $data);
        }
    
        // Entfernen Sie alle HTML-Tags und -Kommentare aus der Ausgabe
        $output = ob_get_clean();
        $output = preg_replace('/<!--(.*?)-->/', '', $output);
        $output = preg_replace('/<\/?[\w\s]*>|<\s*[\w\s]*\/>/', '', $output);
    
        // Schließen Sie die Ausgabe und speichern Sie sie in einer Variablen
        ob_end_clean();
    
        // Öffnen Sie die Ausgabedatei im Schreibmodus
        $fp = fopen('php://output', 'w');
    
        // Schreiben Sie die Headerzeile in die CSV-Datei
        fputcsv($fp, array('Id', 'Kurs', 'Kursbeginn', 'Kursende', 'Buchungsdatum', 'Mitgliedsname', 'Mitgliedsnummer', 'Teilgenommen', 'Mail', 'Buchungszeit', 'Loeschdatum'));
    
        // Schreiben Sie den gefilterten Inhalt in die CSV-Datei
        fwrite($fp, $output);
    
        // Schließen Sie die Ausgabedatei
        fclose($fp);
    
        // Stop PHP script execution
        exit;
    }
}
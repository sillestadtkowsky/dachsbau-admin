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
            'Kursdatum' => 'Kursdatum',
            'Buchungsdatum' => 'Buchungsdatum',
            'Buchungszeit' => 'Buchungszeit',
            'Kursbeginn' => 'Kursbeginn',
            'Kursende' => 'Kursende',
            'Mitgliedsname' => 'Mitgliedsname',
            'Mail' => 'Mail',
            'Mitgliedsnummer' => 'Mitgliedsnummer',
            'Status' => 'Status',
            'Loeschdatum' => 'Loeschdatum'
        );

                // Get user preferences
        $user_options = get_user_option( 'my_screen_options', array() );

                // Check if user has hidden any columns
        if ( isset( $user_options['columns'] ) ) {
            foreach ( $user_options['columns'] as $column ) {
                unset( $columns[ $column ] );
            }
        }
        
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
            'Kursdatum' => 'Kursdatum',
            'Buchungsdatum' => 'Buchungsdatum',
            'Kursbeginn' => 'Kursbeginn',
            'Mitgliedsname' => 'Mitgliedsname',
            'Mitgliedsnummer' => 'Mitgliedsnummer',
            'Status' => 'Status',
            'Mail' => 'Mail'
        );
        return $filterable_columns;
    }

    function get_views() {
        $views = array();
        $current = isset( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : 'all';
     
        $views['all'] = sprintf( '<a href="%s" %s>%s</a>', remove_query_arg( 'post_status' ), $current === 'all' ? 'class="current"' : '', __( 'All', 'textdomain' ) );
        $views['publish'] = sprintf( '<a href="%s" %s>%s</a>', add_query_arg( 'post_status', 'publish' ), $current === 'publish' ? 'class="current"' : '', __( 'Published', 'textdomain' ) );
        $views['draft'] = sprintf( '<a href="%s" %s>%s</a>', add_query_arg( 'post_status', 'draft' ), $current === 'draft' ? 'class="current"' : '', __( 'Drafts', 'textdomain' ) );
     
        return $views;
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

        $do_search = '';
        $search = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';
        $is_date = strtotime($search);

        $text_kurs_filter = isset($_REQUEST['select-kurs-filter']) ? $_REQUEST['select-kurs-filter'] : '';
        $select_visited_filter = isset($_REQUEST['select-Visited-filter']) ? $_REQUEST['select-Visited-filter'] : '';

        if (!empty($select_visited_filter)) {
            if($select_visited_filter==="gefehlt"){
                $do_search .= $wpdb->prepare(" AND bs.visited = 0" );
            }
            if($select_visited_filter==="teilgenommen"){
                $do_search .= $wpdb->prepare(" AND bs.visited = 1");
            }
        }

        if (!empty($text_kurs_filter)) {
            $do_search .= $wpdb->prepare(" AND ih.event_hours_id = %s", array( $text_kurs_filter ));
        }
        
        if ($is_date !== false) {
            $searchDate = date('Y-m-d', strtotime($search));
            $do_search .= $wpdb->prepare(" AND  (bs.booking_datetime LIKE %s OR bs.eventDate LIKE %s)", array( $searchDate.'%', $searchDate.'%' ));
        } else {
            $do_search .= $wpdb->prepare(" AND  (bs.mitgliedsnummer = %s OR bs.name LIKE '%%%s%%' OR bs.email LIKE '%%%s%%')", $search, $search, $search);
        }


        
        // Sortierung hinzufügen
        $orderby = $this->get_orderby();
        $order = $this->get_order();

    
        // Elemente pro Seite festlegen
        $per_page = 50;
        $current_page = $this->get_pagenum();
    
        // Daten für die Tabelle abrufen
        //$query .= " LIMIT " . ($current_page - 1) * $per_page . ", $per_page";

        $data =  MC_DB::getSaveBookings($do_search, $orderby, $order);
        $total_items = count($data);

        // Zeige die Option zum Anpassen der Ansicht
        $current_screen = get_current_screen();
        $current_screen->render_per_page_options();
        
        // Hole die Werte für Spalten und Zeilen aus der URL
        $per_page = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : 50;
 
        // Rufe die Daten ab und setze sie in die Tabelle ein
        $this->items = $this->get_table_data();
 
        // Setze die Spalten ein
        $this->_column_headers = $this->get_column_info();

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

    function get_hidden_columns() {
        $user_id = get_current_user_id();
        $screen_id = 'wp-list-table'; // Der ID-String für die Tabelle, für die Sie die ausgeblendeten Spalten abrufen möchten.
        $screen_options = get_user_meta( $user_id, 'manage' . $screen_id . 'columnshidden', true );
        if ( empty( $screen_options ) || ! is_array( $screen_options ) ) {
            $screen_options = array();
        }
        return $screen_options;
    }

    public function column_default( $item, $column_name ) {
        $item = (array) $item; 
        switch ( $column_name ) {
            case 'Id':
            case 'Kurs':
            case 'Kursdatum':
            case 'Buchungsdatum':
            case 'Buchungszeit':
            case 'Kursbeginn':
            case 'Kursende':
            case 'Mitgliedsname':
            case 'Mail':
            case 'Mitgliedsnummer':
            case 'Loeschdatum':
                return $item[ $column_name ];
            case 'Status':
                if((int)$item[ $column_name ] === 1){
                    return 'teilgenommen';
                }else{
                    return 'gefehlt';
                }
            default:
                return print_r( $item, false ) ;
        }
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'Kurs' => array('Kurs', true),
            'Kursdatum' => array('Kurs', true),
            'Buchungsdatum' => array('Buchungsdatum', true),
            'Buchungszeit' => array('Buchungszeit', true),
            'Kursbeginn' => array('Kursbeginn', true),
            'Kursende' => array('Kursende', true),
            'Mitgliedsname' => array('Mitgliedsname', true),
            'Mitgliedsnummer' => array('Mitgliedsnummer', true),
            'Status' => array('Status', true),
            'Mail' => array('Mail', true),
            'Loeschdatum' => array('Loeschdatum', true)
        );
        return $sortable_columns;
    }

    function usort_reorder($a, $b)
    {
        $a = (array) $a; 
        $b = (array) $b; 
        $orderby = (!empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'Id';
        $order = (!empty($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'DESC';
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
        if (!isset($_REQUEST['so_save_booking_filter_submit'])) {
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
    }

    public function extra_tablenav($which) {
        if ($which == 'top') {
            $output = '<div class="alignleft actions">';
            $output .= self::soFilterAll();
            $output .= self::soFilterSaveBookings();
            $output .= self::soFilterEventVisited();
            $output .= '<input type="submit" name="so_save_booking_filter_submit" class="button" value="Filtern" />';
            $output .= '</div>';
            echo $output;
        }
    }

    public function soFilterAll(){
        $output = '';
        $output .= '<label class="screen-reader-text" for="search-search-input">Finden:</label>';
        $output .= '<input type="search" id="search-search-input" name="s" value="">';
        return $output;
    }

    public function soFilterEventVisited() {
        $selected = '';
        if (isset($_REQUEST['select-Visited-filter'])) {
            $selected = $_REQUEST['select-Visited-filter'];
        }
        $output = '';
        $output .= '<label for="select-Visited-filter" class="screen-reader-text">Filtern nach Option:</label>';
        $output .= '<select style="width:200px" name="select-Visited-filter" id="select-Visited-filter">';
        $output .= '<option value="">Alle Status</option>';
        
        // Überprüfung für "gefehlt"
        $output .= '<option value="gefehlt"' . ($selected == 'gefehlt' ? ' selected="selected"' : '') . '>gefehlt</option>';
        // Überprüfung für "teilgenommen"
        $output .= '<option value="teilgenommen"' . ($selected == 'teilgenommen' ? ' selected="selected"' : '') . '>teilgenommen</option>';
    
        $output .= '</select>';
        return $output;
    }

    public function soFilterSaveBookings() {
        $output = '';
        $args = [];
        $bookings=  MC_DB::getSaveBookings(null,null,null);

        // Erstelle das $options Array aus der Datenbank-Abfrage

        $output .= '<label for="select-kurs-filter" class="screen-reader-text">Filtern nach Option:</label>';
        $output .= '<select style="width:200px" name="select-kurs-filter" id="select-kurs-filter">';
        $output .= '<option value="">Alle Kurse</option>';
        
        $options = array();
        foreach ($bookings as $booking) {
            $key = $booking->event_hours_id;
            $options[$key] = array(
                'event_hours_id' => $booking -> event_hours_id,
                'Kurs' => $booking -> Kurs,
                'post_title' => $booking -> post_title,
                'Kursbeginn' => $booking -> Kursbeginn,
            );
        }
        $options = array_values($options); // Reindex the array

        // Sortiere das Array nach dem event_title
        usort($options, function($a, $b) {
            $cmp1 = strnatcasecmp($a['Kurs'], $b['Kurs']);
            if ($cmp1 !== 0) {
                return $cmp1;
            }
            $cmp2 = strcmp($a['post_title'], $b['post_title']);
            if ($cmp2 !== 0) {
                return $cmp2;
            }
            $cmp3 = strcmp($a['Kursbeginn'], $b['Kursbeginn']);
            return $cmp3;
        });        

        foreach ($options as $option) {
            $selected = '';
            if (isset($_REQUEST['select-kurs-filter'])) {
                $selected = ($_REQUEST['select-kurs-filter'] == $option['event_hours_id']) ? 'selected="selected"' : '';
            }
            $output .= '<option value="' . esc_html($option['event_hours_id']) . '" ' . $selected . '>' . esc_html($option['Kurs'] . ' | ' . $option['post_title'] . ' | ' . $option['Kursbeginn']) . '</option>';
        }
        $output .= '</select>';
        return $output;
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
                $booking->Kursdatum,
                $booking->Kursbeginn,
                $booking->Kursende,
                $booking->Buchungsdatum,
                $booking->Mitgliedsname,
                $booking->Mitgliedsnummer,
                $booking->visited === '0' ? "gefehlt" : "teilgenommen",
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
        fputcsv($fp, array('Id', 'Kurs', 'Kursbeginn', 'Kursdatum', 'Kursende', 'Buchungsdatum', 'Mitgliedsname', 'Mitgliedsnummer', 'Status', 'Mail', 'Buchungszeit', 'Loeschdatum'));
    
        // Schreiben Sie den gefilterten Inhalt in die CSV-Datei
        fwrite($fp, $output);
    
        // Schließen Sie die Ausgabedatei
        fclose($fp);
    
        // Stop PHP script execution
        exit;
    }
    
}
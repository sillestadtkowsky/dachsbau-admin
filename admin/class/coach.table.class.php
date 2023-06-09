<?php

// WP_List_Table-Klasse laden
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class SO_COACH_List_Table extends WP_List_Table {

    // Konstruktorfunktion
    function __construct() {
        parent::__construct(array(
            'singular' => 'booking',
            'plural' => 'bookings',
            'ajax' => false
        ));
    }

    // Definieren Sie die Spaltenüberschriften
    function get_columns() {
        return array(
            'visited'=>'Status',
            'guest_message' => 'Mitgliedsnummer',
            'user_name' => 'Name',
            'user_email' => 'Email',
            'event_title' => 'Kursname',
            'eventDate'=>'Kurstag',          
            'start' => 'Kursbeginn'
        );
    }

    // Holen Sie sich die Daten aus der Datenbank
    function prepare_items() {

        // Überprüfen, ob das Formular gesendet wurde
        if ( isset( $_POST['booking_id'] ) && isset( $_POST['status'] ) ) {
            $booking_id = $_POST['booking_id'];
            $status = $_POST['status'];
            
            // Führen Sie hier den Code aus, um den Status des Datensatzes mit der angegebenen ID zu aktualisieren
            // Verwenden Sie beispielsweise eine Datenbankabfrage, um den Status zu aktualisieren
            
            // Beispiel für eine Aktualisierungsabfrage mit der WordPress-Datenbank-API
            global $wpdb;
            $table_name = $wpdb->prefix . 'event_hours_booking'; // Ersetzen Sie 'your_table_name' durch den Namen Ihrer Tabelle
            $wpdb->update(
                $table_name,
                array( 'visited' => $status ),
                array( 'booking_id' => $booking_id ),
                array( '%d' ),
                array( '%d' )
            );
            
            // Optional: Weiterleitung zur aktualisierten Seite oder Ausgabe einer Erfolgsmeldung
            wp_redirect( $_SERVER['REQUEST_URI'] ); // Weiterleitung zur aktuellen Seite
            exit;
        }

        $do_search = '';
        $data = $this->get_data_from_database($do_search);

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    
    // Holen Sie sich die Daten aus der Datenbank (ersetzen Sie dies durch Ihren tatsächlichen Datenbankabfragecode)
    function get_data_from_database($do_search) {
        return TT_DB::getBookings($do_search);
    }

    function updateStatus() {
        // Führen Sie hier den Code aus, um den Status des Datensatzes mit der angegebenen ID zu aktualisieren
        // Verwenden Sie beispielsweise eine AJAX-Anfrage, um die Aktualisierung an den Server zu senden
        // Nach der Aktualisierung können Sie die Tabelle aktualisieren, um den neuen Status anzuzeigen
    }

    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            // Andere Spalten hier
            
            case 'visited':
                $status = (int) $item['visited'];
                $id = (int) $item['booking_id'];
                
                $status_text = ( $status === 0 ) ? 'Abwesend' : 'Anwesend';
                $status_back_color = ( $status === 0 ) ? '#ff2600' : '#07b38a';
    
                $output = '<form method="post">
                                <input type="hidden" name="booking_id" value="' . $id . '">
                                <select name="status" onchange="this.form.submit();" style="color:white; background-color: ' . $status_back_color . '">
                                    <option value="0" ' . ( $status === 0 ? 'selected' : '' ) . '>Abwesend</option>
                                    <option value="1" ' . ( $status === 1 ? 'selected' : '' ) . '>Anwesend</option>
                                </select>
                            </form>';
    
                return $output;
                
            default:
                return $item[ $column_name ];
        }
    }
}
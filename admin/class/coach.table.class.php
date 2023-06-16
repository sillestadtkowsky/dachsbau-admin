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
            'event_title' => 'Kursname',
            'guest_message' => 'Mitgliedsnummer',
            'user_name' => 'Name',
            'user_email' => 'Email',
            'eventDate'=>'Kurstag',          
            'start' => 'Kursbeginn'
        );
    }

    // Holen Sie sich die Daten aus der Datenbank
    function prepare_items($search ='') {

        $search = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';
        $select_visited_filter = isset($_POST['select-Visited-filter']) ? $_POST['select-Visited-filter'] : '';
        $select_kurs_filter = isset($_POST['select-kurs-filter']) ? $_POST['select-kurs-filter'] : '';
        $booking_Id = isset($_POST['id']) ? $_POST['id'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        
        $do_search = array('weekday' => MC_UTILS::so_getWeekday());
        $do_search['eventDate'] = MC_UTILS::getNow();
        $existing_search = get_option('so_coach_suchfilter', false);

        if (isset($_POST['so_save_booking_filter_submit'])) {
    
            if (!empty($select_visited_filter)) {
                if($select_visited_filter==="gefehlt"){
                    $do_search['visited'] = 0;
                }
                if($select_visited_filter==="teilgenommen"){
                    $do_search['visited'] = 1;
                }
            }
    
            if (!empty($select_kurs_filter)) {
                    $do_search['event_hours_id'] = $select_kurs_filter;
            }
    
            //if (!(empty($select_kurs_filter) && empty($select_visited_filter))) {
                // Die Option "so_coach_suchfilter" existiert nicht oder der Wert hat sich geändert
                update_option('so_coach_suchfilter', $do_search);
                $existing_search = get_option('so_coach_suchfilter', false);
            //} 
            
            if ($existing_search === false) {
                // Option "so_coach_suchfilter" existiert nicht, füge sie hinzu
                add_option('so_coach_suchfilter', $do_search);
            }

        } elseif (isset($_POST['status'])) {
            // Führen Sie hier den Code aus, um den Status des Datensatzes mit der angegebenen ID zu aktualisieren
            // Verwenden Sie beispielsweise eine Datenbankabfrage, um den Status zu aktualisieren
            
            // Beispiel für eine Aktualisierungsabfrage mit der WordPress-Datenbank-API
            global $wpdb;
            $table_name = $wpdb->prefix . 'event_hours_booking'; // Ersetzen Sie 'your_table_name' durch den Namen Ihrer Tabelle
            $wpdb->update(
                $table_name,
                array( 'visited' => $status ),
                array( 'booking_id' => $booking_Id),
                array( '%d' ),
                array( '%d' )
            );
            
            // Optional: Weiterleitung zur aktualisierten Seite oder Ausgabe einer Erfolgsmeldung
            wp_redirect( $_SERVER['REQUEST_URI'] ); // Weiterleitung zur aktuellen Seite
            exit;
        }

        $data = $this->get_data_from_database($existing_search);
        $total_items = count($data);

        $per_page = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 50;

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));

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

    public function extra_tablenav($which) {
        if ($which == 'top') {
            $output = '<div class="alignleft actions">';
            $output .= self::soFilterSaveBookings();
            $output .= self::soFilterEventVisited();
            $output .= '<input type="submit" name="so_save_booking_filter_submit" class="button" value="Filtern" />';
            $output .= '</div>';
            echo $output;
        }
    }

    public function soFilterSaveBookings() {
        $output = '';
        $selectedKursFilter = get_option('so_coach_suchfilter', false);
        $args = [];
        $args = array('weekday' => MC_UTILS::so_getWeekday());
        $bookings = self::get_data_from_database($args);

        // Erstelle das $options Array aus der Datenbank-Abfrage
        $options = array();
        foreach ($bookings as $booking) {
            $key = $booking['booking_id'];
            if (!isset($options[$key])) {
                $options[$key] = array(
                    'id' => $booking['booking_id'],
                    'event_id' => $booking['event_hours_id'],
                    'Kurs' => $booking['event_title'],
                    'post_title' => $booking['weekday'],
                    'Kursbeginn' => $booking['start'],
                );
            }
        }

        // Sortiere das Array nach event_title, post_title und Kursbeginn
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

        $output .= '<label for="select-kurs-filter" class="screen-reader-text">Filtern nach Option:</label>';
        $output .= '<select style="width:200px" name="select-kurs-filter" id="select-kurs-filter">';
        $output .= '<option value="0">Alle Kurse</option>';

        $uniqueEventIDs = array(); // Array für eindeutige event_id-Werte

        foreach ($options as $option) {
            $eventID = $option['event_id'];

            // Überprüfen, ob die event_id bereits vorhanden ist
            if (!in_array($eventID, $uniqueEventIDs)) {
                $uniqueEventIDs[] = $eventID; // Hinzufügen der event_id zum Array der eindeutigen Werte

                $selected = '';
                if(isset($selectedKursFilter['event_hours_id'])){
                    if ($selectedKursFilter['event_hours_id'] == $eventID) {
                        $selected = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . esc_html($eventID) . '" ' . $selected . '>' . esc_html($option['Kurs'] . ' | ' . $option['post_title'] . ' | ' . $option['Kursbeginn']) . '</option>';
            }
        }

        $output .= '</select>';
        return $output;
    }

    public function soFilterEventVisited() {
        $selectedStatus = get_option('so_coach_suchfilter', false);


        $output = '';
        $output .= '<label for="select-Visited-filter" class="screen-reader-text">Filtern nach Option:</label>';
        $output .= '<select style="width:200px" name="select-Visited-filter" id="select-Visited-filter">';
        $output .= '<option value="">Alle Status</option>';
        
        $selected = '';
        if(isset($selectedStatus['visited'])){
            if((int) $selectedStatus['visited']==0){
                $selected = 'selected="selected"';
                $output .= '<option value="gefehlt" '. $selected . '>gefehlt</option>';
                $output .= '<option value="teilgenommen">teilgenommen</option>';
            }
            if((int) $selectedStatus['visited']==1){
                $selected = 'selected="selected"';
                $output .= '<option value="gefehlt">gefehlt</option>';
                $output .= '<option value="teilgenommen" '. $selected . '>teilgenommen</option>';
            }
        }else{
            $output .= '<option value="gefehlt">gefehlt</option>';
            $output .= '<option value="teilgenommen">teilgenommen</option>';
        }


        $output .= '</select>';
        return $output;
    }    

    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            // Andere Spalten hier
            case 'guest_message':
                $userId = (int) $item['user_id'];
                if($userId == 1){
                    return 'intern';
                }else{
                    return $item['guest_message'];
                }
            case 'user_name':
                $userId = (int) $item['user_id'];
                if($userId == 1){
                    return $item['user_name'];
                }else{
                    return $item['guest_name'];
                }
            case 'user_email':
                $userId = (int) $item['user_id'];
                if($userId == 1){
                    return $item['user_email'];
                }else{
                    return $item['guest_email'];
                }
            case 'visited':
                $status = (int) $item['visited'];
                $id = (int) $item['booking_id'];
                
                $status_back_color = ( $status === 0 ) ? '#ff2600' : '#07b38a';
    
                $output = '<form method="post">
                                <input type="hidden" name="id" value="' . $id . '">
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
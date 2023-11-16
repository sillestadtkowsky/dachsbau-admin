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
            'user_name' => 'Name',
            'guest_message' => 'Mitgliedsnummer',
            'event_title' => 'Kursname'
        );
    }

    // Holen Sie sich die Daten aus der Datenbank
    function prepare_items($search ='') {

        $search = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';
        $select_visited_filter = isset($_REQUEST['select-Visited-filter']) ? $_REQUEST['select-Visited-filter'] : '';
        $select_kurs_filter = isset($_REQUEST['select-kurs-filter']) ? $_REQUEST['select-kurs-filter'] : '';
        $booking_Id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

        
        $existing_search['eventDate'] = MC_UTILS::getNow();
        
        $existing_search['weekday'] = MC_UTILS::so_getWeekday();

    
            if (!empty($select_visited_filter)) {
                if($select_visited_filter==="gefehlt"){
                    $existing_search['visited'] = 0;
                }
                if($select_visited_filter==="teilgenommen"){
                    $existing_search['visited'] = 1;
                }
            }
    
            if (!empty($select_kurs_filter)) {
                $existing_search['event_hours_id'] = $select_kurs_filter;
            }
    

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
            
        $existing_search['orderby'] = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'user_name';
        $existing_search['order'] = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'ASC';

        
        
        $data = $this->get_data_from_database($existing_search);
        $total_items = count($data);
        $per_page = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : 50;

            // Aktualisieren Sie die Filterwerte in der URL, wenn die Tabelle sortiert wird
        $current_url = remove_query_arg(array('orderby', 'order'));
        if (!empty($_REQUEST['s'])) {
            $current_url = add_query_arg('s', $_REQUEST['s'], $current_url);
        }
        if (!empty($_REQUEST['select-Visited-filter'])) {
            $current_url = add_query_arg('select-Visited-filter', $_REQUEST['select-Visited-filter'], $current_url);
        }
        if (!empty($_REQUEST['select-kurs-filter'])) {
            $current_url = add_query_arg('select-kurs-filter', $_REQUEST['select-kurs-filter'], $current_url);
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
            'current_page' => $this->get_pagenum(),
            'current_url' => $current_url,
        ));

        

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));

        $columns = $this->get_columns();
        $hidden = array();

        $_REQUEST['select-Visited-filter'] = $select_visited_filter;
        $_REQUEST['select-kurs-filter'] = $select_kurs_filter;

        $sortable_columns = $this->get_sortable_columns();
   
        $this->_column_headers = array($columns, $hidden, $sortable_columns, 'user_name');
    
       //usort($data, array(&$this, 'usort_reorder'));

        $this->items = $data;

        $output='';
            // Verwenden Sie die aktualisierten Filterwerte beim erneuten Rendern der Tabelle
        $output .= '<input type="hidden" name="s" value="' . esc_attr($search) . '">';
        $output .= '<input type="hidden" name="select-Visited-filter" value="' . esc_attr($select_visited_filter) . '">';
        $output .= '<input type="hidden" name="select-kurs-filter" value="' . esc_attr($select_kurs_filter) . '">';
        $output .= '<input type="hidden" name="orderby" value="' . $existing_search['orderby'] . '">';
        $output .= '<input type="hidden" name="order" value="' . $existing_search['order'] . '">';

        echo $output;
    }

    
    // Holen Sie sich die Daten aus der Datenbank (ersetzen Sie dies durch Ihren tatsächlichen Datenbankabfragecode)
    function get_data_from_database($do_search) {
        return TT_DB::getBookings($do_search);
    }

    function usort_reorder($a, $b)
    {
        $a = (array) $a; 
        $b = (array) $b; 
        $orderby = (!empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'user_name';
        $order = (!empty($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'DESC';
        $testresult = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'desc') ? $testresult : -$testresult;
    }

    
    function get_sortable_columns() {
        $sortable_columns = array(
            'visited' => array('visited', true),
            'user_name' => array('user_name', true)
        );
    
        // Hier sammeln Sie die Filterdaten
        $select_kurs_filter = isset($_REQUEST['select-kurs-filter']) ? sanitize_text_field($_REQUEST['select-kurs-filter']) : '';
    
        // Ändern der Sortierlinks
        foreach ($sortable_columns as $column_key => $column_data) {
            $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? sanitize_text_field($_REQUEST['order']) : 'asc';
    
            // Fügen Sie die Filterdaten zur URL hinzu
            $url = admin_url('admin.php?page=so_coach_booking_page&orderby=' . $column_data[0] . '&order=' . $order . '&select-kurs-filter=' . $select_kurs_filter);
    
            // Spaltentitel-Link erstellen
            $sortable_columns[$column_key][1] = '<a href="' . esc_url($url) . '">' . $column_data[1] . '</a>';
        }
    
        return $sortable_columns;
    }

    public function extra_tablenav($which) {
        if ($which == 'top') {
            $output = '<div class="alignleft actions">';
            $output .= self::soFilterSaveBookings();
            $output .= self::soFilterEventVisited();
            $output .= '<input type="submit" name="so_save_booking_filter_submit" class="button" value="Filtern" />';
            $output .= '</div>';
            $output .= '    
            <style>
            @media screen and (max-width: 782px) {
                .tablenav .actions select{
                    font-size:1em !important;
                }
                .tablenav .view-switch, .tablenav.top .actions {
                    display:block !important;
                }
            }
            </style>';
            echo $output;
        }
    }

    public function soFilterSaveBookings() {
        $output = '';
        $selectedKursFilter = isset($_REQUEST['select-kurs-filter']) ? $_REQUEST['select-kurs-filter'] : '';
        $args = [];
        $args = array('weekday' => MC_UTILS::so_getWeekday());
        $bookings = self::get_data_from_database($args);

        // Erstelle das $options Array aus der Datenbank-Abfrage
        $options = array();
        foreach ($bookings as $booking) {
            $key = $booking['booking_id'];
            if (!isset($options[$key])) {
                $options[$key] = array(
                    'Name' => $booking['user_name'],
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
            $cmp1 = strnatcasecmp($a['Name'], $b['Name']);
            if ($cmp1 !== 0) {
                return $cmp1;
            }
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
                if(isset($selectedKursFilter)){
                    if ($selectedKursFilter == $eventID) {
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
        $selectedStatus = isset($_REQUEST['select-Visited-filter']) ? $_REQUEST['select-Visited-filter'] : '';

        $output = '';
        $output .= '<label for="select-Visited-filter" class="screen-reader-text">Filtern nach Option:</label>';
        $output .= '<select style="width:200px" name="select-Visited-filter" id="select-Visited-filter">';
        $output .= '<option value="">Alle Status</option>';
        
        $selected = '';
        if(!empty($selectedStatus)){
            if($selectedStatus==='gefehlt'){
                $selected = 'selected="selected"';
                $output .= '<option value="gefehlt" '. $selected . '>gefehlt</option>';
                $output .= '<option value="teilgenommen">teilgenommen</option>';
            }
            if($selectedStatus==='teilgenommen'){
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
        $guest = (int) $item['guest_id'];
        switch ( $column_name ) {
            // Andere Spalten hier
            case 'guest_message':
                
                if($guest == 0){
                    return 'intern';
                }else{
                    return $item['guest_message'];
                }
            case 'user_name':
                if($guest == 0){
                    return $item['user_name'];
                }else{
                    return $item['guest_name'];
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
<?php
class So_Dachsbau_Dashboard_Widget {
    public function __construct() {
        add_action( 'wp_dashboard_setup', array( $this, 'so_dachsbau_dashboard_widget' ) );
    }

    public function so_dachsbau_dashboard_widget() {
        wp_add_dashboard_widget(
            'so_dachsbau_dashboard_widget',
            'Dachsbau Admin',
            array( $this, 'so_dachsbau_dashboard_widget_callback' )
        );
    }

    public function so_dachsbau_dashboard_widget_callback() {
        $output = '';
        $output .= $this->so_dachsbau_dashboard_heutigeKurse();
        $output .= '<hr>';
        $output .= $this->so_dachsbau_dashboard_neuigkeiten();
        /* $output .= '<hr>';
            $output .= $this->so_dachsbau_dashboard_aktuelleBuchungen();
        */
        $output .= '<hr>';
        $output .= $this->so_dachsbau_dashboard_footer();
        echo $output;
    }

    public function so_dachsbau_dashboard_aktuelleBuchungen() {
        $output = '<div>';
        $output .= '<h3><b>Aktuelle Buchungen</b></h3>';
        $output .= '<p>';
        $output .= '</p>';
        $output .= '</div>';
        return $output;
    }

    public function so_dachsbau_dashboard_neuigkeiten() {
        $output = '<div>';
        $output .= '<h3><b>News</b></h3>';
        $output .= '<p>';
        $output .= '<strong>';
        $output .= '03.05.2023';
        $output .= '</strong><br>';
        $output .= '<ul>';
        $output .= '<li>- Es wurde ein Fehler bei der Berechnung des Sonntags behoben.</li>';
        $output .= '<li>- Dashboard-Widget entwickelt.</li>';
        $output .= '</ul>';
        $output .= '</p>';
        $output .= '</div>';
        return $output;
    }

    public function so_dachsbau_dashboard_footer(){
        $output = '<div class="e-overview__footer e-divider_top">';
        $output .= '<ul style="display: flex;">';
		$output .= '<li class="help-li-first">';		
        $output .= '<a href="mailto:kontakt@osowsky-webdesign.de" target="_blank">Hilfe: kontakt@osowsky-webdesign.de<span class="screen-reader-text">(Öffnet in neuem Fenster)</span>
                    <span aria-hidden="true"></span></a>';			
        $output .= '</li>';				
        $output .= '<li class="help-li">';		
        $output .= '<a href="tel:017647782068" target="_blank">Telefon: 0176-47782068<span class="screen-reader-text">(Öffnet in neuem Fenster)</span>
                    <span aria-hidden="true"></span></a>';			
        $output .= '</li>';					
        $output .= '</ul>';	
        $output .= '</div>';	
        return $output;
    }

    
    public function so_dachsbau_dashboard_heutigeKurse() {
        global $wpdb;
        $postTypSlug = so_dachsbau_post_type_settings();
        $query = "SELECT p.id, p.post_title AS Kurs, 
                    DATE_FORMAT(ih.start,'%H:%i') AS Kursbeginn,
                    DATE_FORMAT(ih.end,'%H:%i') AS Kursende
                    FROM {$wpdb->prefix}posts AS p 
                    LEFT JOIN {$wpdb->prefix}event_hours AS ih ON p.id = ih.event_id 
                    WHERE p.post_type = '" . $postTypSlug["slug"] . "'
                    AND ih.weekday_id IN (SELECT id FROM {$wpdb->prefix}posts WHERE post_title like '%" . $this->findWeekDayString() . "%') ORDER BY ih.start ASC";
 
        $data = $wpdb->get_results($query);
        $anzahl = count($data);
        $output = '<div>';
        $output .= '<h3><b>Heutige Kurse (' . $anzahl. ')</b></h3>';
        $output .= '<p>';
        if ($anzahl > 0) {
            // Es wurden Ergebnisse zurückgegeben
            $output .= '<ul>';
            foreach ($data as $row) {
                $beginn = strtotime($row->Kursbeginn);
                $ende = strtotime($row->Kursende);
                $now = strtotime('now');
                $status = '';
                if ($now < $beginn) {
                    $status = '<i class="fas fa-circle" style="color: #F0AD87;"></i>';
                } else if ($now > $ende) {
                    $status = '<i class="fas fa-check-circle" style="color: green;"></i>';
                } else {
                    $status = '<i class="fas fa-adjust" style="color: orange;"></i>';
                }
                $output .= '<li>' . $status . '<strong>&nbsp;<a href="' . get_permalink($row->id) . '">' . $row->Kurs . '</a></strong> | Beginn: ' . $row->Kursbeginn . ' | Ende: ' . $row->Kursende . '</li> ';
            }
            
            $output .= '</ul>';
        } else {
            $output .= 'Für heute sind keine Kurse geplant.';
        }

        $output .= '</p>';
        $output .= '</div>';
        return $output;
    }
    function findWeekDayString() {
        $today = so_getDayToday();
        $weekday_names = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
        $weekday_index = (int)$today->format('N') - 1;
        return $weekday_names[$weekday_index];
    }
    
    function so_getDayToday() {
        date_default_timezone_set('Europe/Berlin'); // Set default timezone
        return new DateTime();
    }

    function so_dachsbau_post_type_settings(){
        $timetable_events_settings = get_option("timetable_events_settings");
        if(!$timetable_events_settings)
        {
            $timetable_events_settings = array(
                "slug" => "events",
                "label_singular" => "Event",
                "label_plural" => "Events",
            );
            add_option("timetable_events_settings", $timetable_events_settings);
        }
        return $timetable_events_settings;
    }
}

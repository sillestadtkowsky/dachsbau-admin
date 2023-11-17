<?php

class CustomMailPage {

    public function __construct() {
        // Fügen Sie Aktionen und Filter hinzu
        add_action('admin_enqueue_scripts', array($this, 'custom_mail_page_styles_styles'));
        add_action('admin_menu', array($this, 'add_mail_page_to_menu'));
    }

    public function custom_mail_page_styles_styles() {
        wp_enqueue_style('custom-plugin-styles', plugin_dir_url(__FILE__) . 'css/custom_mail_page-styles.css');
    }

    public function render_custom_mail_page() {
    
            // Die Werte der Formularfelder aus dem Request auslesen und bereinigen
        $selectedCourse = isset($_POST['selected_course']) ? sanitize_text_field($_POST['selected_course']) : '';
        $emailSubject = isset($_POST['email_subject']) ? sanitize_text_field($_POST['email_subject']) : '';
        $emailContent = isset($_POST['email_content']) ? sanitize_textarea_field($_POST['email_content']) : '';
    
        $email_sent = false;
    
        if (isset($_POST['send_email']) && $this->validate_form()) {
            $emailSubject = '';
            $emailContent = '';
            $email_sent = true;
        }
    
        // Hier können Sie das Formular anzeigen
        ?>
        <div class="wrap">
            <h2>Mail an Kurteilnehmer verschicken</h2>
            <?php
                // Wenn die E-Mail erfolgreich versendet wurde, geben Sie eine Erfolgsmeldung aus
                if ($email_sent) {
                    echo '<div class="updated"><p>E-Mail wurde erfolgreich versendet!</p></div>';
                }
            ?>
            <p>Wählen Sie einen Kurs. Erfassen Sie einen Betreff für die eMail und tragen Sie den Mail Inhalt in den Bereich Inhalt ein.</p>
    
            <form method="post" action="">
                <div class="flex-container">
                    <div class="flex-item">
                        <?php
                            echo $this->soFilterSaveBookings($email_sent); // Rufen Sie die Funktion auf und geben Sie den Rückgabewert aus
                        ?>
                    </div>
                    <div class="flex-item">
                        <label for="email_subject">Betreff:</label>
                        <input type="text" name="email_subject" id="email_subject" placeholder="Betreff" value="<?php echo esc_attr($emailSubject); ?>">
                    </div>
                    <div class="flex-item">
                        <label for="email_content">Inhalt:</label>
                        <textarea name="email_content" id="email_content" placeholder="Inhalt"><?php echo esc_textarea($emailContent); ?></textarea>
                    </div>
                    <div class="flex-item">
                        <input type="submit" name="send_email" value="Jetzt versenden">
                    </div>
                </div>
            </form>
        </div>
        <?php
    
        // Anzeigen von Fehler- und Erfolgsmeldungen
        settings_errors('my-plugin-error');
        settings_errors('my-plugin-success');
    }
    
    private function validate_form() {
        $errors = array();
    
        // Validierung für Kursauswahl
        if (empty($_POST['selected_course'])) {
            $errors[] = 'Bitte wählen Sie einen Kurs aus.';
        }
    
        // Validierung für Betreff
        if (empty($_POST['email_subject'])) {
            $errors[] = 'Bitte geben Sie einen Betreff ein.';
        }
    
        // Validierung für Inhalt
        if (empty($_POST['email_content'])) {
            $errors[] = 'Bitte tragen Sie den Mail-Inhalt ein.';
        }
    
        // Fügen Sie hier weitere Validierungslogiken hinzu, z. B. für E-Mail-Format, etc.
    
        if (!empty($errors)) {
            // Wenn es Validierungsfehler gibt, geben Sie die Fehler aus und geben Sie false zurück
            foreach ($errors as $error) {
                echo '<div class="error"><p>' . esc_html($error) . '</p></div>';
            }
            return false;
        }
    
        // Wenn alles validiert wurde, geben Sie true zurück
        return true;
    }

    public function soFilterSaveBookings($send_status) {
        $output = '';
        if($send_status){
            $selectedKursFilter = '0';
        }else{
            $selectedKursFilter = isset($_REQUEST['selected_course']) ? $_REQUEST['selected_course'] : '0';
        }
        
        $args = [];
        //$args = array('weekday' => MC_UTILS::so_getWeekday());
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
        // Definieren Sie die gewünschte Reihenfolge der Wochentage
        $wochentage = array('Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So');
    
        // Sortiere das Array zuerst nach der Reihenfolge der Wochentage im 'post_title'
        usort($options, function($a, $b) use ($wochentage) {
            $wochentagA = substr($a['post_title'], 0, 2); // Extrahieren Sie die ersten beiden Zeichen des Wochentags
            $wochentagB = substr($b['post_title'], 0, 2); // Extrahieren Sie die ersten beiden Zeichen des Wochentags
    
            $indexA = array_search($wochentagA, $wochentage);
            $indexB = array_search($wochentagB, $wochentage);
    
            if ($indexA === false || $indexB === false) {
                // Wenn ein Wochentag nicht in der Reihenfolge gefunden wird, gibt es keine Änderung der Reihenfolge
                return 0;
            }
    
            if ($indexA !== $indexB) {
                // Wenn die Wochentage unterschiedlich sind, sortieren Sie nach der Reihenfolge der Wochentage
                return $indexA - $indexB;
            }
    
            // Wenn die Wochentage gleich sind, sortieren Sie nach dem Uhrzeit-String im Kursbeginn
            $timeA = strtotime($a['Kursbeginn']);
            $timeB = strtotime($b['Kursbeginn']);
    
            if ($timeA === false || $timeB === false) {
                // Wenn die Zeit nicht geparst werden kann, gibt es keine Änderung der Reihenfolge
                return 0;
            }
    
            if ($timeA !== $timeB) {
                // Wenn die Uhrzeiten unterschiedlich sind, sortieren Sie nach der Uhrzeit im Kursbeginn
                return $timeA - $timeB;
            }
    
            // Wenn die Uhrzeiten gleich sind, sortieren Sie nach dem Kurs (alphabetisch)
            return strnatcasecmp($a['Kurs'], $b['Kurs']);
        });
    
        $output .= '<label for="selected_course" class="screen-reader-text">Kurs:</label>';
        $output .= '<select style="width:200px" name="selected_course" id="selected_course">';
        $output .= '<option value="0">Alle Kurse</option>';
    
        $uniqueEventIDs = array(); // Array für eindeutige event_id-Werte
    
        foreach ($options as $option) {
            $eventID = $option['event_id'];
    
            // Überprüfen, ob die event_id bereits vorhanden ist
            if (!in_array($eventID, $uniqueEventIDs)) {
                $uniqueEventIDs[] = $eventID; // Hinzufügen der event_id zum Array der eindeutigen Werte
    
                $selected = '';
                if ($selectedKursFilter == $eventID) {
                    $selected = 'selected="selected"'; // Markieren Sie den ausgewählten Kurs
                }
                $output .= '<option value="' . esc_html($eventID) . '" ' . $selected . '>' . esc_html($option['post_title'] . ' | ' . $option['Kursbeginn'] . ' | ' . $option['Kurs']) . '</option>';
            }
        }
    
        $output .= '</select>';
        return $output;
    }
    

    function get_data_from_database($do_search) {
        return TT_DB::getBookings($do_search);
    }

    public function add_mail_page_to_menu() {
        add_menu_page('Dachsbau-Admin', 'Dachsbau-Admin', 'manage_options', 'so_dachsbau-karow-admin-menu', 'so_dachsbau_admin_info_page', 'dashicons-list-view', 5);
        add_submenu_page('so_dachsbau-karow-admin-menu', 'Mail an Kurteilnehmer', 'Mail an Kurteilnehmer', 'manage_options', 'so_mail_to_user', array($this, 'render_custom_mail_page'));

        // Weitere Untermenüpunkte hier hinzufügen
    }
}
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
            
            $args = [];
            $args = array(
                'hasBookings' => true,
                'orderby' => 'date',
                'event_hours_id' => $selectedCourse,
                'emailSubject' => $emailSubject,
                'emailContent' => $emailContent,
            );

            // Mail an Teilnehmer verschicken
            $this->sendMail($args);
            $emailSubject = '';
            $emailContent = '';
            $email_sent = true;
        }
    
        // Hier können Sie das Formular anzeigen
        ?>
        <div class="wrap">
            <h2>Mail an Kursteilnehmer verschicken</h2>
            <?php
            // Wenn die E-Mail erfolgreich versendet wurde, geben Sie eine Erfolgsmeldung aus
            if ($email_sent) {
                echo '<div class="updated"><p>E-Mail wurde erfolgreich versendet!</p></div>';
            }
            ?>
            <p>Wählen Sie einen Kurs. Erfassen Sie einen Betreff für die E-Mail und tragen Sie den Mail-Inhalt in den Bereich Inhalt ein.</p>

            <form method="post" action="">
                <div class="flex-container">
                    <div class="flex-item">
                        <?php
                        echo $this->soCoachKursAuswahl($email_sent); // Rufen Sie die Funktion auf und geben Sie den Rückgabewert aus
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
                        <input type="submit" class="submitMail" name="send_email" value="Jetzt versenden">
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

    public function soCoachKursAuswahl($send_status) {
        $output = '';
        if ($send_status) {
            $selectedKursFilter = '0';
        } else {
            $selectedKursFilter = isset($_REQUEST['selected_course']) ? $_REQUEST['selected_course'] : '0';
        }
    
        $args = [];
        $args = array(
            'hasBookings' => true,
            'orderby' => 'event'
        );
        
        $bookings = self::get_data_from_database($args);

        // Erstelle das $options Array aus der Datenbank-Abfrage
        $options = array();
        $processedEventIds = array(); // Array, um verarbeitete 'event_id' zu speichern
        
        foreach ($bookings as $booking) {
            $eventID = $booking['event_hours_id'];
        
            // Überprüfen, ob diese 'event_id' bereits verarbeitet wurde
            if (!in_array($eventID, $processedEventIds)) {
                $key = $booking['booking_id'];
                $options[$key] = array(
                    'Name' => $booking['user_name'],
                    'id' => $booking['booking_id'],
                    'event_id' => $eventID,
                    'Kurs' => $booking['event_title'],
                    'post_title' => $booking['weekday'],
                    'eventDate' => $booking['eventDate'],
                    'Kursbeginn' => $booking['start'],
                );
        
                // Füge die 'event_id' zur Liste der verarbeiteten 'event_id' hinzu
                $processedEventIds[] = $eventID;
            }
        }
    
        // Stil für das Dropdown-Menü und die Anzahl der Buchungen
        $dropdownStyle = 'style="width:200px; margin-right: 10px;"'; // Hier können Sie das Styling anpassen
    
        $output .= '<form method="POST" action="">';
        $output .= '<label for="selected_course" class="screen-reader-text">Kurs:</label>';
        
        // Übergeordneter Container für alles
        $output .= '<div class="flex-container">'; // Dies setzt die Elemente untereinander
        
        // Dropdown für die Kursauswahl
        $output .= '<div class="flex-item" ' . $dropdownStyle . '>';
        $output .= '<select name="selected_course" id="selected_course">';
        $output .= '<option value="0">Alle Kurse</option>';
        
        foreach ($options as $option) {
            $eventID = $option['event_id'];
            $selected = ($selectedKursFilter == $eventID) ? 'selected="selected"' : '';
            $output .= '<option value="' . esc_html($eventID) . '" ' . $selected . '>' . esc_html($option['post_title'] . ' | ' . date('d.m.Y', strtotime($option['eventDate'])) . ' | ' . $option['Kursbeginn'] . ' | ' . $option['Kurs']) . '</option>';
        }
        
        $output .= '</select>';
        $output .= '</div>'; // Schließen des Dropdown-Containers
        
        // Teilnehmer-Anzeige
        $output .= '<div class="teilnehmer-content">';
        // Hol dir die Anzahl der gemeldeten Teilnehmer
        $mailCount = $this->getBookingCount($selectedKursFilter);

        // Überprüfen, ob es 1 gemeldeter Teilnehmer ist oder mehrere und den Text entsprechend anpassen
        if ($mailCount == 1) {
            $output .= '<strong class="mailCount">' . $mailCount . '</strong> gemeldeter Teilnehmer wird per Mail informiert. (Kopie an info@karowerdachse.de)';
        } else {
            $output .= '<strong class="mailCount">' . $mailCount . '</strong> gemeldete Teilnehmer werden per Mail informiert. (Kopie an info@karowerdachse.de)';
        }

        $output .= '</div>'; // Schließen der Teilnehmer-Anzeige
        
        $output .= '</div>'; // Schließen des übergeordneten Containers
        $output .= '<script>
                        document.getElementById("selected_course").addEventListener("change", function() {
                            this.form.submit(); // Das Formular automatisch senden, wenn eine Auswahl getroffen wird
                        });
                    </script>';
        $output .= '</form>';
                
        return $output;
    }
    

    function get_data_from_database($arg) {
        return TT_DB::getBookings($arg);

    }

    function getBookingCount($do_search) {
        return TT_DB::getBookingCountForEvent($do_search);
    }

    function sendMail($args){
        global $wpdb;
        $emailSubject = $args['emailSubject'];
        $emailContent = $args['emailContent'];
        $bookings = self::get_data_from_database($args);
        
        // Die Haupt-Empfängeradresse
        $to = get_option('so_coach_mail_to'); 
        $subject = get_option('so_coach_mail_betreff') . ' | ' .$emailSubject;

        $all_emails = array(); // Array für alle E-Mail-Adressen
        
        foreach ($bookings as $booking) {
            $user_email = $booking['user_email'];
            $guest_email = $booking['guest_email'];
        
            // Prüfen Sie, ob die E-Mail-Adressen nicht leer sind und fügen Sie sie dem Array hinzu
            if (!empty($user_email)) {
                $all_emails[] = $user_email;
            }
            if (!empty($guest_email)) {
                $all_emails[] = $guest_email;
            }
        }

        $dateString = $bookings[0]['eventDate'];

        // Erstellen eines DateTime-Objekts aus dem gegebenen Datum im Format YYYY-MM-DD
        $dateObject = DateTime::createFromFormat('Y-m-d', $dateString);
    
        // Umwandeln in das gewünschte Format DD.MM.YYYY
        $formattedDate = $dateObject->format('d.m.Y');
        
        $message = '<div>';
        $message .= '<div><b>Lieber Dachs, </b></div>';
        $message .= '<div>es gibt Informationen zu einem deiner gebuchten Kurse!</div>';
        $message .= '<h3>Betrifft folgende Kursbuchung:</h3>';
        $message .= '<div><b>Kurs: </b>' . $bookings[0]['event_title'] . '</div>';
        $message .= '<div><b>Tag: </b>' . $formattedDate . '</div>';
        $message .= '<div><b>Uhrzeit: </b>' . $bookings[0]['start'] . '</div>';
        $message .= '<h3>Info zu deiner Kursbuchung:</h3>';
        $message .= $emailContent;
        $message .= '</div>';
        
        // Kommaseparierten String erstellen
        $bcc = implode(', ', $all_emails);

        // Wenn Sie zusätzliche Header für BCC-Adressen hinzufügen möchten, können Sie dies tun:
        $headers = 'From: Karowerdachse Trainer <' . $to . '>' . "\r\n";
        $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'Bcc: ' . $bcc . "\r\n";
        

        $message .= '<br>' . nl2br(get_option('so_coach_mail_footer')) . '<br><img id="bild_vorschau" src="' . esc_url(get_option('so_coach_mail_footer_logo_url')) . '" style="max-width: 100px; max-height: 100px;"/>';

        // E-Mail senden
        wp_mail($to, $subject, $message, $headers);
        
    }

    public function add_mail_page_to_menu() {
        add_menu_page('Dachsbau-Admin', 'Dachsbau-Admin', 'manage_options', 'so_dachsbau-karow-admin-menu', 'so_dachsbau_admin_info_page', 'dashicons-list-view', 5);
        add_submenu_page('so_dachsbau-karow-admin-menu', 'Mail an Kursteilnehmer', 'Mail an Kursteilnehmer', 'manage_options', 'so_mail_to_user', array($this, 'render_custom_mail_page'));

        // Weitere Untermenüpunkte hier hinzufügen
    }
}
<?php
    class SOScheduleBookingCronJob {

        function so_getSaveBookings($exportBookingIds){
            global $wpdb;
            $table_name = $wpdb->prefix . 'event_booking_saves';
            $query = "SELECT * FROM $table_name ";
            $query .= "WHERE booking_id IN (" . implode(',', $exportBookingIds) . ")";
            $result = $wpdb->get_results( $query );
            return $result;
        }
        
        function so_removeOldBookings(){
            $output = 'Zu löschende Buchungen: <br> ';
    
            $args = array('interval_minutes' => 30);
            $upcoming_events = self::so_getUpcomingEvents($args);
    
            if (!empty($upcoming_events)) {
                foreach ($upcoming_events as $event) {
                    $booking_id = $event['booking_id'];
                    $output .= "Booking ID: " . $booking_id . "<br>";
                }
            } else {
                $output .= "Keine Buchungen gefunden";
            }
    
            return $output;
        }
    
        function so_getUpcomingEvents($args) {
            $bookings_to_delete = self::so_getBookingsToDelete($args);
            self::so_saveBookingsToTable($bookings_to_delete);
            self::so_deleteBookings($bookings_to_delete);
            return $bookings_to_delete;
        }
        
        function so_getBookingsToDelete($args) {
            
            date_default_timezone_set('Europe/Berlin');
            global $wpdb;
        
            // Get the interval and table names
            $interval_minutes = isset($args['interval_minutes']) ? $args['interval_minutes'] : 15;
            $interval = $interval_minutes . ' MINUTE';
            $table_event_hours = $wpdb->prefix . 'event_hours';
            $table_event_hours_booking = $wpdb->prefix . 'event_hours_booking';
        
            // Get the previous weekday string
            $previous_weekday_string = self::so_getPreviousWeekday();
        
            // Get the bookings to be deleted
            
            $now = date('H:i:s');

            $query = "SELECT b.booking_id, b.event_hours_id, b.user_id, b.booking_datetime, b.guest_id
                      FROM $table_event_hours AS t
                      JOIN {$wpdb->posts} AS p ON t.weekday_id = p.ID
                      JOIN {$table_event_hours_booking} AS b ON t.event_hours_id = b.event_hours_id
                      WHERE CONCAT('1970-01-01 ', TIME(t.start)) < DATE_ADD(CONCAT('1970-01-01 ', '$now'), INTERVAL $interval)
                      AND SUBSTR(p.post_name, 1, 2) = '$previous_weekday_string'";

            $bookings = $wpdb->get_results($query, ARRAY_A);

            foreach ($bookings as &$booking) {
                if ($booking['user_id'] == 0) {
                    $result = self::so_getInfoForGuestUser($booking['guest_id'])[0];
                    $booking['name'] = $result->name;
                    $booking['email'] = $result->email;
                    $booking['mitgliedsnummer'] = $result->message;

                } else{
                    $result = self::so_getInfosForWpUser($booking['user_id'])[0];
                    $booking['name'] = $result->display_name;
                    $booking['email'] = $result->user_email;
                    $booking['mitgliedsnummer'] = 'intern';
                }
            }
            return $bookings;
        }

        function so_getInfosForWpUser($userId){
            global $wpdb;
            $table_name = $wpdb->prefix . 'users';
            $query = "SELECT * FROM $table_name ";
            $query .= "WHERE ID = $userId";
            $result = $wpdb->get_results( $query );
            return $result;
        }

        function so_getInfoForGuestUser($userId){
            global $wpdb;
            $table_name = $wpdb->prefix . 'timetable_guests';
            $query = "SELECT * FROM $table_name ";
            $query .= "WHERE guest_id = $userId";
            $result = $wpdb->get_results( $query );
            return $result;
        }

        function so_saveBookingsToTable($bookings) {
            global $wpdb;
        
            $table_event_booking_saves = $wpdb->prefix . 'event_booking_saves';
        
            // Save the bookings to be deleted to wp_event_booking_saves table
            if (!empty($bookings)) {
                foreach ($bookings as $booking) {
                    $booking_id = $booking['booking_id'];
                    $event_hours_id = $booking['event_hours_id'];
                    $name = $booking['name'];
                    $mitgliedsnummer = $booking['mitgliedsnummer'];
                    $email = $booking['email'];
                    $booking_datetime = $booking['booking_datetime'];
        
                    // Insert the booking into wp_event_booking_saves table
                    $wpdb->insert($table_event_booking_saves, array(
                        'booking_id' => $booking_id,
                        'event_hours_id' => $event_hours_id,
                        'name' => $name,
                        'mitgliedsnummer' => $mitgliedsnummer,
                        'email' => $email,
                        'booking_datetime' => $booking_datetime,
                        'booking_delete_datetime' => current_time('mysql')
                    ));
                }
            }
        }
        
        function so_deleteBookings($bookings) {
            global $wpdb;
        
            $table_event_hours_booking = $wpdb->prefix . 'event_hours_booking';
        
            // Delete the bookings from wp_event_hours_booking table
            if (!empty($bookings)) {
                foreach ($bookings as $booking) {
                    $booking_id = $booking['booking_id'];
                    $wpdb->delete($table_event_hours_booking, array('booking_id' => $booking_id));
                }
            }
        }
        function so_getPreviousWeekday() {
            $timezone = new DateTimeZone('Europe/Berlin'); // Hier die Zeitzone anpassen
            $previousWeekday = (new DateTime('yesterday', $timezone))->format('N');
            $weekdays = array('Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So');
            return $weekdays[$previousWeekday - 1];
        }

    }
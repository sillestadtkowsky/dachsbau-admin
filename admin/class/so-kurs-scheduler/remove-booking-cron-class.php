<?php
    class SOScheduleBookingCronJob {
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
        
            $query = "SELECT b.booking_id, b.event_hours_id, b.user_id, b.booking_datetime
                      FROM $table_event_hours AS t
                      JOIN {$wpdb->posts} AS p ON t.weekday_id = p.ID
                      JOIN {$table_event_hours_booking} AS b ON t.event_hours_id = b.event_hours_id
                      WHERE CONCAT('1970-01-01 ', TIME(t.start)) < DATE_ADD(CONCAT('1970-01-01 ', '$now'), INTERVAL $interval)
                      AND SUBSTR(p.post_name, 1, 2) = '$previous_weekday_string'";
        
            $bookings = $wpdb->get_results($query, ARRAY_A);
        
            return $bookings;
        }
        
        function so_saveBookingsToTable($bookings) {
            global $wpdb;
        
            $table_event_booking_saves = $wpdb->prefix . 'event_booking_saves';
        
            // Save the bookings to be deleted to wp_event_booking_saves table
            if (!empty($bookings)) {
                foreach ($bookings as $booking) {
                    $booking_id = $booking['booking_id'];
                    $event_hours_id = $booking['event_hours_id'];
                    $user_id = $booking['user_id'];
                    $booking_datetime = $booking['booking_datetime'];
        
                    // Insert the booking into wp_event_booking_saves table
                    $wpdb->insert($table_event_booking_saves, array(
                        'booking_id' => $booking_id,
                        'event_hours_id' => $event_hours_id,
                        'user_id' => $user_id,
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
            $weekdays = array('Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So');
            date_default_timezone_set('Europe/Berlin');
            $today = date('N');  // 1 für Mo, 2 für Di, usw. bis 7 für So
            $previous_day = ($today + 6) % 7;
            return $weekdays[$previous_day];
        }

    }
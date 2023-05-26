<?php
class MC_DB
{

	public static function getCurrentBookings($args)
	{
		$args = shortcode_atts(array(
			'booking_id' => 0,
			'bookings_ids' => null,
			'weekdays_ids' => null,
			'event_hours_ids' => null,
			'booked_past' => 0,
			'validation_code' => '',
			'event_id' => 0,
			'events_ids' => null,
			'per_page' => 0,
			'page_number' => 1,
			'order' => 'DESC',
			'orderby' => 'booking',
		), $args);

		global $wpdb;
		
		$query = '';
		$queryArgs = array();
		
		$query .= 
		'SELECT 
			booking.booking_id AS booking_id,
			event_hour.weekday_id AS weekday_id,
			booking.booking_datetime AS booking_datetime,
			booking.validation_code,
			event.ID AS event_id, 
			event.post_title AS event_title, 
			event_hour.event_hours_id,
			TIME_FORMAT(event_hour.start, "%%H:%%i") AS start,
			TIME_FORMAT(event_hour.end, "%%H:%%i") AS end,
			event_hour.before_hour_text as event_description_1, 
			event_hour.after_hour_text as event_description_2, 
			weekday.post_title AS weekday,
			user.ID AS user_id,
			user.user_login,
			user.display_name AS user_name,
			user.user_email, 
			guest.guest_id AS guest_id,
			guest.name AS guest_name,
			guest.email AS guest_email,
			guest.phone AS guest_phone,
			guest.message AS guest_message
		FROM 
			' . $wpdb->prefix . 'event_hours_booking AS booking
		LEFT JOIN 
			' . $wpdb->prefix . 'event_hours AS event_hour 
			ON (event_hour.event_hours_id=booking.event_hours_id)
		LEFT JOIN 
			' . $wpdb->posts . ' AS event 
			ON (event.ID=event_hour.event_id)
		LEFT JOIN 
			' . $wpdb->posts . ' AS weekday 
			ON (weekday.ID=event_hour.weekday_id)
		LEFT JOIN 
			' . $wpdb->users . ' AS user 
			ON (user.ID=booking.user_id)
		LEFT JOIN 
			' . $wpdb->prefix . 'timetable_guests AS guest
			ON (guest.guest_id=booking.guest_id)
		WHERE 1=1 ';
		
		if($args['event_id'])
		{
			$query .= 
			' AND event_hour.event_id=%d';
			$queryArgs[] = (int)$args['event_id'];
		}

		if($args['events_ids'])
		{
			$query .= 
			' AND event.ID IN (';
			foreach($args['events_ids'] as $event_ids)
			{
				$query .=  '%d,';
				$queryArgs[] = (int)$event_ids;
			}
			$query = rtrim($query, ',');
			$query .= ')';
		}
		
		if($args['booking_id'])
		{
			$query .= 
			' AND booking.booking_id=%d';
			$queryArgs[] = (int)$args['booking_id'];
		}
		
		if($args['bookings_ids'])
		{
			$query .= 
			' AND booking.booking_id IN (';
			foreach($args['bookings_ids'] as $booking_id)
			{
				$query .=  '%d,';
				$queryArgs[] = (int)$booking_id;
			}
			$query = rtrim($query, ',');
			$query .= ')';
		}
		
		if($args['validation_code'])
		{
			$query .= 
			' AND booking.validation_code=%s';
			$queryArgs[] = $args['validation_code'];
		}

		if($args['weekdays_ids'])
		{
			$query .= 
			' AND weekday.ID IN (';
			foreach($args['weekdays_ids'] as $weekday_ids)
			{
				$query .=  '%d,';
				$queryArgs[] = (int)$weekday_ids;
			}
			$query = rtrim($query, ',');
			$query .= ')';
		}
		
		if($args['event_hours_ids'])
		{
			$query .= 
			' AND event_hour.event_hours_id IN (';
			foreach($args['event_hours_ids'] as $event_hours_ids)
			{
				$query .=  '%d,';
				$queryArgs[] = (int)$event_hours_ids;
			}
			$query = rtrim($query, ',');
			$query .= ')';
		}
		
		if((int)$args['booked_past'])
		{
			$query .= ' AND booking_datetime<(STR_TO_DATE(CONCAT(CURDATE(), start), "%%Y-%%m-%%d %%H:%%i"))';
		}
		
		$order = ($args && strtolower($args['order'])!='desc' ? 'ASC' : 'DESC');
		
		if($args['orderby'])
		{
			switch($args['orderby'])
			{
				case 'booking':
					$query .= ' ORDER BY booking_datetime ' . $order . ',  booking_id ' . $order;
					break;
				case 'date':
					$query .= ' ORDER BY weekday.menu_order ' . $order . ', start ' . $order . ', end ' . $order . '';
					break;
				case 'event':
					$query .= ' ORDER BY event_title ' . $order;
					break;
				case 'user':
					$query .= ' ORDER BY user_name ' . $order;
					break;
			}
		}
		else
		{
			$query .= ' ORDER BY booking_id ' . $order;
		}
		
		if($args['per_page'])
		{
			$query .= ' LIMIT %d';
			$queryArgs[] = $args['per_page'];
		}
		
		if($offset = ($args['page_number'] - 1) * $args['per_page'])
		{
			$query .= ' OFFSET %d';
			$queryArgs[] = $offset;
		}
		
		$query = $wpdb->prepare($query, $queryArgs);
		$result = $wpdb->get_results($query, 'ARRAY_A');

		return $result;
	}

	public static function getSaveBookings($do_search,$orderby, $order){

		global $wpdb;
		$query = "SELECT bs.booking_id as Id, p.post_title AS Kurs, 
				DATE_FORMAT(bs.booking_datetime,'%d.%m.%Y') AS Buchungsdatum,
				DATE_FORMAT(bs.booking_datetime,'%H:%i') AS Buchungszeit,
				DATE_FORMAT(ih.start,'%H:%i') AS Kursbeginn,
				DATE_FORMAT(ih.end,'%H:%i') AS Kursende,
				ih.event_hours_id,
				bs.mitgliedsnummer as Mitgliedsnummer,
				bs.visited as Status,
				bs.name as Mitgliedsname,
				wd.post_title,
				DATE_FORMAT(bs.eventDate,'%d.%m.%Y') as Kursdatum,
				CONCAT('<a href=\"mailto:', bs.email, '\">', bs.email, '</a>') as Mail,
				DATE_FORMAT(bs.booking_delete_datetime,'%d.%m.%Y - %H:%i') AS Loeschdatum
			FROM {$wpdb->prefix}event_booking_saves AS bs
			LEFT JOIN {$wpdb->prefix}event_hours AS ih ON ih.event_hours_id=bs.event_hours_id 
			LEFT JOIN {$wpdb->prefix}posts AS p ON p.id=ih.event_id 
			LEFT JOIN {$wpdb->prefix}posts AS wd ON wd.ID=ih.weekday_id
			WHERE 1=1 " . $do_search;

		if (!empty($orderby)) {
			$query .= " ORDER BY $orderby $order";
		}
		return $wpdb->get_results($query);

	}
	public static function deleteMembers($id)
	{
		global $wpdb;
		$query = '';

		$query .=
			'DELETE 
		FROM 
			' . $wpdb->prefix . 'mitglieder 
		WHERE
		' . $wpdb->prefix . 'mitglieder.mitglNr = "' . $id .'"';

		$result = $wpdb->get_results($query);
		return '<div class="success">Der Mitarbeiter wurde erfolgreich geslöscht.</div>';
	}

	public static function getMember($id)
	{
		global $wpdb;
		$query = '';

		$query .=
			'SELECT members.mitglNr, members.anrede, members.vorname, members.nachname
		FROM 
			' . $wpdb->prefix . 'mitglieder as members
		WHERE
			members.mitglNr = \'' . $id . '\'';

		return $result = $wpdb->get_results($query);
	}

	public static function updateMember($id, $anrede, $vorname, $nachname, $idUpdate){
		global $wpdb;
		$query = '';

		$query .=
			 ' UPDATE '. $wpdb->prefix . 'mitglieder SET mitglNr = "'.$id.'", anrede = "'.$anrede.'", vorname = "'.$vorname.'", nachname = "'.$nachname.'"
		WHERE
		' . $wpdb->prefix .'mitglieder.mitglNr = "' . $idUpdate . '"';

		$result = $wpdb->get_results($query);
	}

	public static function getMembersArray($where)
	{
		global $wpdb;
		$query = '';

		$query .=
			'SELECT members.mitglNr as mitgliedsnummer, members.anrede, members.vorname, members.nachname
		FROM 
			' . $wpdb->prefix . 'mitglieder as members'
		. $where . '	
		ORDER BY
		members.mitglNr';


		$result = $wpdb->get_results($query, ARRAY_A);
		return esc_sql($result);
	}

	public static function insertMember($id, $anrede, $vorname, $nachname)
	{
		global $wpdb;
		$query = '';

		$query .=
			'INSERT INTO ' . $wpdb->prefix . 'mitglieder (mitglNr, anrede, vorname, nachname) VALUES ("' . $id . '", "' . $anrede . '", "' . $vorname . '", "' . $nachname . '")';

		$result = $wpdb->get_results($query);

		if ($result > 0) {
			return '<div class="success">Der Mitarbeiter ' . $vorname . ' ' . $nachname . ' wurde erfolgreich gespeichert.</div>';
		}
	}
}

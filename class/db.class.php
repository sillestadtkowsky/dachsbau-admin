<?php
class MC_DB
{

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

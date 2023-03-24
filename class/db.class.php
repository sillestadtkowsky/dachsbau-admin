<?php
class MC_DB
{
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

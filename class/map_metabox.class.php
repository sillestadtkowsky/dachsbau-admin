<?php
class CustomMetaBox {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_custom_meta_box'));
        add_action('add_meta_boxes', array($this, 'add_map_metabox'));
        add_action('save_post', array($this, 'save_map_link'));
    }
    // Metadatenfelder für weekdays hinzufügen
    public function add_custom_meta_box() {
        add_meta_box(
            'custom_meta_box', // ID des Meta-Box
            'Adressinformationen', // Titel der Meta-Box
            array($this, 'show_custom_meta_box'), // Funktionsname zum Anzeigen der Metadatenfelder
            'timetable_weekdays', // Inhaltstyp, für den die Metadatenfelder angezeigt werden sollen
            'normal', // Position der Metabox (normal, advanced oder side)
            'high' // Priorität der Metabox (high, core, default oder low)
        );
    }

    // Felder anzeigen
    public function show_custom_meta_box() {
        global $post;
        $meta = get_post_meta($post->ID, 'map_link', true); ?>

        <label for="map_link">Adresselink:</label>
        <input style="width:50%;" type="text" id="map_link" name="map_link" value="<?php echo $meta; ?>">

        <?php 
    }

    // Fügt eine Metabox hinzu
    public function add_map_metabox() {
        add_meta_box(
            'map_metabox',
            'Kartenausschnitt',
            array($this, 'render_map_metabox'),
            'timetable_weekdays', // Hier kannst du den post_type angeben, zu dem du die Metabox hinzufügen möchtest
            'normal',
            'default'
        );
    }


    // Zeigt den Inhalt der Metabox an
    public function render_map_metabox( $post ) {
        // Auslesen des gespeicherten Links aus der Metabox
        $map_link = get_post_meta( $post->ID, 'map_link', true );

        // Wenn ein Link gespeichert wurde, zeige die Karte an
        if ( $map_link ) {
            // Extrahiere die Koordinaten aus dem Link
            $url_components = parse_url( $map_link );
            parse_str( parse_url( $map_link, PHP_URL_FRAGMENT ), $params );
            $coordinates = explode( '/', $params['map'] );
            $lat = $coordinates[1];
            $lng = $coordinates[2];

            // Setze die Koordinaten in mapOptions
            $mapOptions = array(
                'center' => array( $lat, $lng ),
                'zoom' => 17,
            );
            ?>
                <div id="map" style='width: 100%;height: 50vh;'></div>
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
                <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>      
                <script>
                    let mapOptions = <?php echo json_encode( $mapOptions ); ?>;

                    let map = new L.map('map' , mapOptions);

                    let layer = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
                    map.addLayer(layer);

                    let marker = new L.Marker([<?php echo $lat; ?>, <?php echo $lng; ?>]);
                    marker.addTo(map);

                </script>
            <?php
        } else {
            // Wenn kein Link gespeichert wurde, zeige eine leere Metabox an
            echo '<p>Noch kein Kartenausschnitt ausgewählt.</p><p><a target="_blank" href="https://www.openstreetmap.org/#map=17/52.61611/13.49606">Hier wählen und kopieren</a></p> ';
        }
    }

    // Speichert den Wert der Metabox
    public function save_map_link( $post_id ) {
        if ( isset( $_POST['map_link'] ) ) {
            update_post_meta( $post_id, 'map_link', sanitize_text_field( $_POST['map_link'] ) );
        }
    }

}
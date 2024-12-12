function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    
    if(document.getElementById(tabName) !== null){
        document.getElementById(tabName).style.display = "block";
    }

    // Überprüfen, ob ein Event vorhanden ist
    if (evt) {
        evt.currentTarget.className += " active";
    } else {
        // Setzen Sie den aktiven Tab, wenn kein Event vorhanden ist
        for (i = 0; i < tablinks.length; i++) {
            if (tablinks[i].textContent.trim() === 'Automat zur Buchungssicherung und Verwaltung') {
                tablinks[i].className += " active";
                break;
            }
        }
    }
}

jQuery(document).ready(function($){
    console.log('jquery-load');   

    $('#mein_bild_button').click(function(e) {
        e.preventDefault();
        var custom_uploader = wp.media({
            title: 'Bild auswählen',
            button: {
                text: 'Bild verwenden'
            },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#so_coach_mail_footer_logo_url').val(attachment.url); // URL in das versteckte Textfeld setzen
            $('#bild_vorschau').attr('src', attachment.url).show(); // Bildvorschau aktualisieren und anzeigen
            $('#remove_bild_button').show(); // "Bild entfernen"-Button anzeigen
        }).open();
    });

    $('#remove_bild_button').click(function() {
        $('#so_coach_mail_footer_logo_url').val(''); // URL aus dem versteckten Feld entfernen
        $('#bild_vorschau').hide(); // Bildvorschau ausblenden
        $(this).hide(); // "Bild entfernen"-Button ausblenden
    });

    $(".editMembers").css("display", "none");
    $(".newMember").css("display", "none");
    $("#cancelNewMember").css("display", "none");

    $(".editMember").click(function() {
        var id = $(this).attr('data-categoryid');   
        $("#editMembers_" + id).show();
        return false;
    });

    $("#newMember").click(function() {
        var id = $(this).attr('data-categoryid');   
        $(".newMember").show();
        $("#cancelNewMember").css("display", "block");
        $("#newMember").css("display", "none");
        return false;
    });

    $("#cancelNewMember").click(function() {
        var id = $(this).attr('data-categoryid');   
        $(".newMember").show();
        $("#cancelNewMember").css("display", "none");
        $(".newMember").css("display", "none");
        $("#newMember").css("display", "block");
        return false;
    });

    function checkMitgliederNumber(){
        var del=confirm("Are you sure you want to delete this record?");
        if (del==true){
            alert ("record deleted")
        }
        return del;
    }

    $('.status-button').on('click', function() {
        var bookingId = $(this).data('booking-id');
        var status = $(this).data('status');
        
        // Führen Sie hier den Code aus, um den Status umzuschalten
        // Verwenden Sie die Buchungs-ID (bookingId) und den Status (status) für die entsprechenden Aktualisierungen in der Datenbank

        // Beispiel für eine AJAX-Anfrage zum Aktualisieren des Status
        $.ajax({
            url: 'update_status.php', // Pfad zur Datei, die den Status aktualisiert
            type: 'POST',
            data: { bookingId: bookingId, status: status },
            success: function(response) {
                // Erfolgsfall: Aktualisieren Sie die Anzeige oder führen Sie andere erforderliche Aktionen aus
                console.log(response);
                alert (response)
            },
            error: function(xhr, status, error) {
                // Fehlerfall: Verarbeiten Sie den Fehler entsprechend
                console.log(error);
                alert (error)
            }
        });
    });

    setTimeout(function() {
        openTab(null, 'Buchungssicherung');
    }, 100);

}); 
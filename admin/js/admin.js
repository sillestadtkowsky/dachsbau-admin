jQuery(document).ready(function($){
    console.log('jquery-load');   
    
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

}); 
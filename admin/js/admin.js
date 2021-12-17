jQuery(document).ready(function($){
    console.log('jquery-load');   
    
    $(".editMembers").css("display", "none");
    $(".newMember").css("display", "none");

    $(".editMember").click(function() {
        var id = $(this).attr('data-categoryid');   
        $("#editMembers_" + id).show();
        return false;
    });

    $("#newMember").click(function() {
        var id = $(this).attr('data-categoryid');   
        $(".newMember").show();
        return false;
    });

    function confirmationDelete(){
        var del=confirm("Are you sure you want to delete this record?");
        if (del==true){
            alert ("record deleted")
        }
        return del;
        }

}); 
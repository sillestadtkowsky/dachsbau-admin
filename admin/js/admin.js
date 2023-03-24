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

}); 
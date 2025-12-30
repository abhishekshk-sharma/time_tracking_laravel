<?php
session_start();

session_unset();
$d = 0;
    if(session_destroy()){
        // header("location: admin_login.php");
        $d = 1;
    }
    
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    $(document).ready(function(){
        localStorage.clear(); 

        if(<?php echo $d; ?> == 1){
            window.location.href = "admin_login.php";
        }  
    });
</script>

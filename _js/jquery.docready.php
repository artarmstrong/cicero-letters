<?php 
header('Content-type: text/javascript'); 

// Include WordPress 
define('WP_USE_THEMES', false);
require($_SERVER['DOCUMENT_ROOT'].'/Here/wp-load.php');
//require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

?>jQuery(document).ready(function($) {

	// Hide loading container
	$('#ciceroletters_loading_container').hide();
	
	// Address searching
	$("#ciceroletters_search_field_error").hide();
	$(document).on("click","#ciceroletters_search_submit",function(e){
    
        // hide container and show loader
        $('#ciceroletters_search_container').hide();
        $('#ciceroletters_loading_container').show();
        
        // validate and process form here
        var ciceroletters_search_letter_id = $("input#ciceroletters_search_letter_id").val();
        var ciceroletters_search_blog_id = "<?= $blog_id; ?>";
        var ciceroletters_search_field = $("input#ciceroletters_search_field").val();  
        if (ciceroletters_search_field == "") {  
        	$("#ciceroletters_search_field_error").show();  
        	$("input#ciceroletters_search_field").focus();  
          return false;  
        }
        
        // send form to ajax
        var dataString = 'address='+ciceroletters_search_field.replace(/\s/g,"+")+'&letterid='+ciceroletters_search_letter_id+'&blogid='+ciceroletters_search_blog_id;
        $.ajax({
            type: "POST",
            url: "<?= plugins_url(); ?>/cicero-letters/_php/ajax-search-address.php",
            data: dataString,
            success: function(data) {
                $('#ciceroletters_search_container').hide();
                $('#ciceroletters_loading_container').hide();
                $('#ciceroletters_email_container').html(data);
            },
            error: function(data) {
                $('#ciceroletters_search_container').hide();
              	$('#ciceroletters_loading_container').hide();
              	$('#ciceroletters_successerror_container').html("Unfortunately we couldn't find your address.");
            }
        });
        return false;
     
    });
  
  
    // Send the email
    $(document).on("click","#ciceroletters_email_submit",function(e){
        //$("#ciceroletters_email_submit").live("click", function(){
		  
		// hide container and show loader
        $('#ciceroletters_email_container').hide();
        $('#ciceroletters_loading_container').show(); 
		  
        // validate and process form here
        var ciceroletters_email_letter_id = $("input#ciceroletters_search_letter_id").val();
        var ciceroletters_email_blog_id = "<?= $blog_id; ?>";
        var ciceroletters_email_to = $("input#ciceroletters_email_to").val();
        var ciceroletters_email_to_names = $("input#ciceroletters_email_to_names").val();
        var ciceroletters_email_bcc_email = $("input#ciceroletters_email_bcc_email").val();
        var ciceroletters_email_subject = $("input#ciceroletters_email_subject").val();
        if (ciceroletters_email_subject == "") {  
        	$("#ciceroletters_email_subject_error").show();  
        	$("input#ciceroletters_email_subject").focus();  
        	return false;  
        }
    
        var ciceroletters_email_body = $("#ciceroletters_email_body").val();  
        if (ciceroletters_email_body == "") {  
        	$("#ciceroletters_email_body_error").show();  
        	$("input#ciceroletters_email_body").focus();  
        	return false;  
        }
    
        var ciceroletters_email_fname = $("input#ciceroletters_email_fname").val();  
        if (ciceroletters_email_fname == "") {  
        	$("#ciceroletters_email_fname_error").show();  
        	$("input#ciceroletters_email_fname").focus();  
        	return false;  
        }
    
        var ciceroletters_email_lname = $("input#ciceroletters_email_lname").val();  
        if (ciceroletters_email_lname == "") {  
        	$("#ciceroletters_email_lname_error").show();  
        	$("input#ciceroletters_email_lname").focus();  
        	return false;  
        }
    
        var ciceroletters_email_email = $("input#ciceroletters_email_email").val();  
        if (ciceroletters_email_email == "") {  
        	$("#ciceroletters_email_email_error").show();  
        	$("input#ciceroletters_email_email").focus();  
        	return false;  
        }
        
        var ciceroletters_email_city = $("input#ciceroletters_email_city").val();  
        if (ciceroletters_email_city == "") {  
        	$("#ciceroletters_email_city_error").show();  
        	$("input#ciceroletters_email_city").focus();  
          return false;  
        }
        
        // send form to ajax
        var dataString = 'to='+ciceroletters_email_to+'&bccemail='+ciceroletters_email_bcc_email+'&subject='+ciceroletters_email_subject+'&body='+ciceroletters_email_body+'&fname='+ciceroletters_email_fname+'&lname='+ciceroletters_email_lname+'&email='+ciceroletters_email_email+'&city='+ciceroletters_email_city+'&letterid='+ciceroletters_email_letter_id+'&blogid='+ciceroletters_email_blog_id+'&names='+ciceroletters_email_to_names;
        $.ajax({
    	    type: "POST",
    	    url: "<?= plugins_url(); ?>/cicero-letters/_php/ajax-send-email.php",
    	    data: dataString,
    	    success: function(data) {
    	       $('#ciceroletters_email_container').hide();
    	       $('#ciceroletters_loading_container').hide(); 
    	       $('#ciceroletters_successerror_container').html(data);
    	    },
    	    error: function(data) {
    	       $('#ciceroletters_email_container').hide();
    	       $('#ciceroletters_loading_container').hide();
    	       $('#ciceroletters_successerror_container').html("Unfortunately we couldn't send the email.");
    	    }
        });
        return false;
    	  
    });
  
  
});
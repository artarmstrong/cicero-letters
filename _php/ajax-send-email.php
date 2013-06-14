<?php

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Include WordPress 
define('WP_USE_THEMES', false);
require($_SERVER['DOCUMENT_ROOT'].'/Here/wp-load.php');
//require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

// Globals
global $wpdb;

// Test info
/*
$_POST['letterid'] = 2;
$_POST['names'] = "Art, Sam";
$_POST['to'] = "art@avelient.com, arthurjarmstrong@gmail.com";
$_POST['email'] = "art@avelient.com";
$_POST['subject'] = "Test Subject";
$_POST['body'] = "Test Body";
$_POST['fname'] = "Art";
$_POST['lname'] = "Armstrong";
$_POST['bccemail'] = "";
*/

// Get letter
$letter = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ciceroletters WHERE `id` = ".mysql_real_escape_string($_POST['letterid'])." LIMIT 1;");

if($letter != null) {

	// Change this to post elements
	$names = mysql_real_escape_string($_POST['names']);
	$names = explode(",", $names);
	$to = mysql_real_escape_string($_POST['to']);
	$to = explode(",", $to);
	$from = mysql_real_escape_string($_POST['email']); 
    $subject = mysql_real_escape_string($_POST['subject']); 
	if( (is_array($to) && count($to) > 0) && (is_array($names) && count($names) > 0) ){
    	
    	// Sent to multiple people
    	$mail_sent = false;
    	$recipients = array();
    	for($i = 0; $i < count($to); $i++){
        	$recipients[$names[$i]] = $to[$i];
    	}
    	foreach($recipients as $recipient_name => $recipient_email){
        	$body = "
        	<html>
        	<body>
        	<p>Dear ".stripslashes($recipient_name)."</p>
        	<p>".str_replace("\n", "<br />", stripslashes($_POST['body']))."</p>
        	<p>Sincerely,<br />".stripslashes($_POST['fname'])." ".stripslashes($_POST['lname'])."</p>
        	</body>
        	</html>";
        	$headers  = "From: $from\r\n"; 
        	$headers .= "Content-type: text/html\r\n";
        	$headers .= "Bcc: " . $_POST['bccemail'] . "\r\n";
        	 
        	// Now lets send the email. 
        	if(mail($recipient_email, $subject, $body, $headers)){
        		$mail_sent = true;
        	}
    	}
    	
    	// Now lets check the success. 
    	if($mail_sent == true){
    		echo $letter->success_message;
    	}else{
    		echo $letter->error_message;
    	}
    	
	}else{
    	
    	// Send to single person
    	$body = "
    	<html>
    	<body>
    	<p>Dear ".stripslashes($names)."</p>
    	<p>".str_replace("\n", "<br />", stripslashes($_POST['body']))."</p>
    	<p>Sincerely,<br />".stripslashes($_POST['fname'])." ".stripslashes($_POST['lname'])."</p>
    	</body>
    	</html>";
    	$headers  = "From: $from\r\n"; 
    	$headers .= "Content-type: text/html\r\n";
    	$headers .= "Bcc: " . $_POST['bccemail'] . "\r\n";
    	 
    	// Now lets send the email. 
    	if(mail($to, $subject, $body, $headers)){
    		echo $letter->success_message;
    	}else{
    		echo $letter->error_message;
    	}
	}
		
}else{
	
	echo "The letter could not be sent.";
	
}

?>
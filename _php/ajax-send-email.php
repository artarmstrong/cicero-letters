<?php
define('WP_USE_THEMES', false);
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

// Globals
global $wpdb;

// Get letter
$letter = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix. "ciceroletters WHERE `id` = ".$_POST['letterid']." LIMIT 1;");

if ($letter != null) {

    // Change this to post elements
    $names = $_POST['names'];
    $names = explode(",", $names);
    $to = $_POST['to'];
    $to = explode(",", $to);
    $from = $_POST['email'];
    $subject = stripslashes($_POST['subject']);
    if( (is_array($to) && count($to) > 0) && (is_array($names) && count($names) > 0) ){

      // Send to multiple people
      $mail_sent = false;
      $recipients = array();
      for($i = 0; $i < count($names); $i++){
          $recipients[trim($names[$i])] = ($letter->test == "true" ? $to[0] : $to[$i]);
      }
      foreach($recipients as $recipient_name => $recipient_email){
	      $recipient_name_out = "";
        $recipient_name_out = $recipient_name;
        $body = "
      	<html>
      	<body>
      	<p>Dear ".stripslashes($recipient_name_out).",</p>
      	<p>".str_replace("\n", "<br />", stripslashes($_POST['body']))."</p>
      	<p>
    	    Sincerely,<br />
    	    ".stripslashes($_POST['fname'])." ".stripslashes($_POST['lname'])."<br />
    	    ".stripslashes($_POST['city']).", ".stripslashes($_POST['state'])."
        </p>
      	</body>
      	</html>";
        $headers  = "From: $from\r\n";
        $headers .= "Content-type: text/html\r\n";
        $headers .= "Bcc: " . $_POST['bccemail'] . "\r\n";

        // Now lets send the email.
        if(mail($recipient_email, $subject, $body, $headers)){
          $mail_sent = true;

          // DEBUG
          //echo "Mail sent to: $recipient_name_out - $recipient_email<br />";

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
    	<p>Dear ".stripslashes($names).",</p>
    	<p>".str_replace("\n", "<br />", stripslashes($_POST['body']))."</p>
    	<p>
    	    Sincerely,<br />
    	    ".stripslashes($_POST['fname'])." ".stripslashes($_POST['lname'])."<br />
    	    ".stripslashes($_POST['city']).", ".stripslashes($_POST['state'])."
        </p>
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

    // Insert person into report
    $wpdb->insert(
    	$wpdb->prefix.'ciceroletters_users',
    	array(
    		'letter_id' => $letter->id,
    		'name' => $_POST['fname']." ".$_POST['lname'],
    		'email' => $_POST['email']
    	),
    	array(
    		'%d',
    		'%s',
    		'%s'
    	)
    );

} else {
    echo "The letter could not be sent.";
}

?>

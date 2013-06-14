<?php


// Error Reporting
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

// Include WordPress 
define('WP_USE_THEMES', false);
require($_SERVER['DOCUMENT_ROOT'].'/Here/wp-load.php');
//require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

// Function to do our API calls (returns object made from JSON)
function get_response($url, $postfields=''){
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, True);
	if($postfields !== ''):
	    curl_setopt ($ch, CURLOPT_POST, True);
	    curl_setopt ($ch, CURLOPT_POSTFIELDS, $postfields);
	endif;
	$json = curl_exec($ch);
	curl_close($ch);
	return json_decode($json);
}


// Get letter
global $wpdb;
$letter = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ciceroletters WHERE `id` = ".mysql_real_escape_string($_POST['letterid'])." LIMIT 1;");

// Check if its a test, otherwise run cicero
$officials_emails = array();
$officials_names = array();
$officials_state_same = true;
if($letter->test == "true"){
	
	$officials_emails[] = $letter->test_email;
	$officials_names[] = "Test Name";

}else{

	// Some constants that we will use:
	$username = 'julie@hubbellcommunications.com';
	$password = 'Hubbell1';
	//$search_loc = '2104+SE+Morrison+Street,+Portland,+OR+97214';
	//$search_loc = '15515+Mink+Rd+NE,+Woodinville,+WA+98077';
	//$search_loc = '642+Johnson+Street,+Victoria,+BC+V8W+1M6,+Canada';
	$search_loc = str_replace(" ", "+", $_POST['address']);
	
	// Obtain a token:
	$response = get_response('http://cicero.azavea.com/v3.1/token/new.json', "username=$username&password=$password");
	
	// Check to see if the token was obtained okay:
	if($response->success != True):
	    exit('Could not obtain token.');
	endif;
	
	// The token and user obtained are used for other API calls:
	$token = $response->token;
	$user = $response->user;
	
	// Get an official query response
	$official_level = explode(":", $letter->official);
	$official_district_type = $official_level[0];
	$official_role = (isset($official_level[1]) ? $official_level[1] : "");
	
	$query_string = "search_loc=$search_loc&district_type=$official_district_type" . (!empty($official_role) ? "&role=$official_role" : "") . "&token=$token&user=$user&format=json";
	$official_response = get_response("http://cicero.azavea.com/v3.1/official?$query_string");
	
	if(count($official_response->response->results->candidates) == 0):
	
		echo 'No location found for the given address.';
		
	endif;
	
	// Print information for each official:
	foreach($official_response->response->results->candidates[0]->officials as $o):
	
		// Get state district
		if(!isset($o->office->district->state) || $o->office->district->state != $letter->state)
			$officials_state_same = false;
			
		// Get name
		$officials_names[] = $o->office->title." ".$o->first_name." ".$o->last_name;
		
		// Get email and check for validity
	  foreach($o->email_addresses as $e):
	    if(preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $e) > 0){
		    $officials_emails[] = $e;
		    break;
		  }
	  endforeach;
	
	endforeach;

}
  
// If email is available continue
if(!$officials_state_same){
?>
	
	<p>Unfortunately officials matching your address were found.</p>
	
<?php
}elseif(!empty($officials_emails)){

	// Get all emails
	$official_emails_list = implode(",", $officials_emails);
	$official_names_list = implode(" / ", $officials_names);
	?>

	<form id="ciceroletters_email_form" method="post">
	
		<!-- Form hidden info -->
		<?php
		if($letter->test == "true")
			echo "<input type='hidden' id='ciceroletters_email_to' name='ciceroletters_email_to' value='".$letter->test_email."' />";
		else
			echo "<input type='hidden' id='ciceroletters_email_to' name='ciceroletters_email_to' value='".$official_emails_list."' />";
		if($letter->bcc_email != "")
			echo "<input type='hidden' id='ciceroletters_email_bcc_email' name='ciceroletters_email_bcc_email' value='".$letter->bcc_email."' />";
		?>
		<input type='hidden' id='ciceroletters_email_to_names' name='ciceroletters_email_to_names' value='<?= $official_names_list; ?>' />
		
		<strong>Email Information</strong>
		<br /><br />
		<table>
			<tr>
				<td>
					Recipient(s): <?= $official_names_list; ?><br /><br />
				</td>
			</tr>
			<tr>
				<td>
					Subject
					<br />
					<input type='text' id="ciceroletters_email_subject" name='ciceroletters_email_subject' style="width:200px;" value='<?= $letter->subject; ?>' />
				</td>
			</tr>
			<tr>
				<td>
					Editable Text
					<br />
					<textarea id="ciceroletters_email_body" name='ciceroletters_email_body' style="width:200px;height:150px;"><?= str_replace("<br />", "\n", $letter->body); ?></textarea>
					<br />
					<small>If pasting from a word processor please save as plain text first.</small>
				</td>
			</tr>
		</table>
		<br /><br />		
		
		<strong>Sender Information</strong>
		<br /><br />
		<table>
			<tr>
				<td colspan='2'>
					You must provide your contact information. This will only be used to identify you to the recipient.
					<br /><br />
					<span style='color:red'>* = required</span>
					<br /><br />
				</td>
			</tr>
			<tr>
				<td width="70">First Name <span style='color:red'>*</span></td>
				<td><input type='text' id='ciceroletters_email_fname' name='ciceroletters_email_fname' size="30" value='' /></td>
			</tr>
			<tr>
				<td>Last Name <span style='color:red'>*</span></td>
				<td><input type='text' id='ciceroletters_email_lname' name='ciceroletters_email_lname' value='' /></td>
			</tr>
			<tr>
				<td>Email <span style='color:red'>*</span></td>
				<td><input type='text' id='ciceroletters_email_email' name='ciceroletters_email_email' value='' /></td>
			</tr>
			<tr>
				<td>City <span style='color:red'>*</span></td>
				<td><input type='text' id='ciceroletters_email_city' name='ciceroletters_email_city' value='' /></td>
			</tr>
		</table>
		<?php
		if($letter->bcc_email != "" && $letter->bcc_note != "")
			echo "<p>* - ".$letter->bcc_note."</p>";
		?>
		<br /><br />
		
		<input type="submit" name="ciceroletters_email_submit" id="ciceroletters_email_submit" value="Send Email" />
		
	</form>

<?php }else{ ?>
	
	<p>Unfortunately no emails were found relating to the person in that position.</p>

<?php } ?>
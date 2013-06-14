<?php
/*
Plugin Name: Cicero Letters
Plugin URI: http://hubbellcommunications.com
Description: A plugin used to manage letters that are sent with Cicero information.
Version: 1.0
Author: Art Armstrong
Author URI: http://artarmstrong.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// Error checking
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

// Constants
global $wpdb;

// Pages
define('CICEROLETTERS_PAGE_HOME', admin_url("/admin.php?page=ciceroletters"));
define('CICEROLETTERS_PAGE_ADD', admin_url("/admin.php?page=ciceroletters_add"));
define('CICEROLETTERS_PAGE_EDIT', admin_url("/admin.php?page=ciceroletters_edit"));
define('CICEROLETTERS_PAGE_HELP', admin_url("/admin.php?page=ciceroletters_help"));

// Database Tables
define('CICEROLETTERS_DB', $wpdb->prefix.'ciceroletters');

// Actions
add_action('admin_menu', 'ciceroletters_add_pages');

// Hooks
register_activation_hook( __FILE__, 'ciceroletters_install' );
register_deactivation_hook( __FILE__, 'ciceroletters_uninstall' );

// ciceroletters_install() creates the database structure
function ciceroletters_install() {

	global $wpdb;
    
  //Create table
  $structure = "CREATE TABLE IF NOT EXISTS `".CICEROLETTERS_DB."` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` enum('cicero','manual') NOT NULL,
  `test` enum('true','false') NOT NULL default 'false',
  `test_email` varchar(255) NOT NULL,
  `success_message` varchar(255) NOT NULL,
  `error_message` varchar(255) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `recipient_name` varchar(255) default NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `bcc_email` varchar(255) NOT NULL,
  `bcc_note` varchar(255) NOT NULL,
  `country` enum('USA','CAN') NOT NULL,
  `state` varchar(2) NOT NULL,
  `official` varchar(255) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL default '0000-00-00 00:00:00'
	);";
  $wpdb->query($structure);
  
}

// ciceroletters_uninstall() drops the tables
function ciceroletters_uninstall() {

	global $wpdb;

	//Drop Documents table
  //$structure = "DROP TABLE ".CICEROLETTERS_DB.";";
  //$wpdb->query($structure);
  
}

// action function for above hook
function ciceroletters_add_pages() {

  // Add a new top-level menu
  add_menu_page('Cicero Letters', 'Cicero Letters', 'edit_pages', 'ciceroletters', 'ciceroletters_admin_home');
  
  // Add sub-level menus
  add_submenu_page('ciceroletters', __('Letters'), __('Letters'), 'edit_pages', 'ciceroletters', 'ciceroletters_admin_home');
  add_submenu_page('ciceroletters', __('Add Letter'), __('Add Letter'), 'edit_pages', 'ciceroletters_add', 'ciceroletters_admin_add');
  add_submenu_page('ciceroletters', __('Help'), __('Help'), 'edit_pages', 'ciceroletters_help', 'ciceroletters_admin_help');
  add_submenu_page('', __('Edit Letter'), __('Edit Letter'), 'edit_pages', 'ciceroletters_edit', 'ciceroletters_admin_edit');

}

// ciceroletters_admin_documents() displays the page content for the custom Test Toplevel menu
function ciceroletters_admin_home() {

	global $wpdb;	
	
	if( (isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['id']) && $_GET['id'] != "") ){
		
		$letter = $wpdb->get_row("SELECT * FROM ".CICEROLETTERS_DB." WHERE `id` = ".mysql_real_escape_string($_GET['id'])." LIMIT 1;");
		if($letter != null) {
			$wpdb->query("DELETE FROM `".CICEROLETTERS_DB."` WHERE `id` = ".mysql_real_escape_string($_GET['id'])." LIMIT 1;");
			$success_message = "Letter successfully deleted.";
		}
		
	}elseif(isset($_GET['success'])){
	
		$success_id = $_GET['success'];
		switch($success_id){
			case '1':
				$success_message = "Letter added!";
				break;
		}
	
	}
	
	?>	
		
	<div class="wrap">
	
		<div id="icon-link-manager" class="icon32"><br></div>
		<h2>Cicero Letters <a href="<?= CICEROLETTERS_PAGE_ADD; ?>" class="add-new-h2">Add New</a></h2>
				
		<?php if(isset($success_message) && $success_message != "") { ?>
		<div id="message" class="updated below-h2">
			<p><?php echo $success_message; ?></p>
		</div>
		<?php }else{ ?>
		<br />
		<?php } ?>
		
		<div id="poststuff">
			
			<form method="post" action="">
				<table class="widefat" cellspacing="0">
					<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col">Subject</th>
						<th scope="col">Letter Type</th>
						<th scope="col">Shortcode</th>
						<th scope="col" width="180">Updated</th>
					</tr>
					</thead>
					<tbody>
						<?php
						$posts = $wpdb->get_results("SELECT * FROM `".CICEROLETTERS_DB."`;");
		
						if(count($posts) > 0) {
						
							foreach($posts as $post) {
							?>					
							<tr>
								<td><?= $post->id; ?>
								<td>
									<strong><?= $post->subject; ?></strong>
									<div class="row-actions">
										<span class="edit"><a href="<?= CICEROLETTERS_PAGE_EDIT; ?>&id=<?= $post->id; ?>" title="Edit this item">Edit</a> | </span>
										<span class="trash"><a href="<?= CICEROLETTERS_PAGE_HOME; ?>&action=delete&id=<?= $post->id; ?>" onclick="return confirm('Are you sure you want to delete?')" title="Delete this item">Delete</a></span>
								</td>
								<td><?= ucfirst($post->type); ?></td>
								<td>[cicero-letters id='<?= $post->id; ?>']</td>
								<td><?= date('F jS, Y g:ia', strtotime($post->updated)); ?></td>
							</tr>
							<?php
							}
						}else{
						?>
						<tr>
							<td colspan="4" align="center"><strong>No entries found</strong></td>
						</tr>
						<?php
						}
						?>			
					</tbody>
				</table>
			</form>
			
			<br />
			
			<p>...or go here to <a href="<?= CICEROLETTERS_PAGE_ADD; ?>">add a new letter</a>.</p>
			
		</div>
	</div>

<?php
}

// ciceroletters_admin_add() displays the letters add page
function ciceroletters_admin_add() {

	global $wpdb;
	
	if(isset($_POST['add-letter'])){
	
		$errors = array();
		$errors_str = "";
		$page_type            = mysql_real_escape_string(stripslashes($_POST['page_type']));
		$page_test            = (isset($_POST['page_test']) ? mysql_real_escape_string(stripslashes($_POST['page_test'])) : "");
		$page_test_email      = mysql_real_escape_string(stripslashes($_POST['page_test_email']));
		$page_success         = mysql_real_escape_string(stripslashes($_POST['page_success']));
		$page_error           = mysql_real_escape_string(stripslashes($_POST['page_error']));
		
		$email_recipient      = mysql_real_escape_string(stripslashes($_POST['email_recipient']));
		$email_recipient_name = mysql_real_escape_string(stripslashes($_POST['email_recipient_name']));
		$email_subject        = mysql_real_escape_string(stripslashes($_POST['email_subject']));
		$email_body           = mysql_real_escape_string(stripslashes($_POST['email_body']));
		$email_bcc_email      = mysql_real_escape_string(stripslashes($_POST['email_bcc_email']));
		$email_bcc_note       = mysql_real_escape_string(stripslashes($_POST['email_bcc_note']));
		
		$cicero_country       = mysql_real_escape_string(stripslashes($_POST['cicero_country']));
		$cicero_state         = "";
		$cicero_official      = "";
		if($cicero_country == "USA"){
			$cicero_state    = mysql_real_escape_string(stripslashes($_POST['cicero_state_usa']));
			$cicero_official = mysql_real_escape_string(stripslashes($_POST['cicero_official_usa']));
		}elseif($cicero_country == "CAN"){
			$cicero_state    = "";
			$cicero_official = mysql_real_escape_string(stripslashes($_POST['cicero_official_can']));
		}
		
		//Validation
		$error_found = false;
		if($page_type == "" || ($page_type != "cicero" && $page_type != "manual")){
			$errors[] = "Type of Email";
			$error_found = true;
		}
		if($page_type == "manual" && $email_recipient == ""){
			$errors[] = "Recipient";
			$error_found = true;
		}
		if($page_type == "manual" && $email_recipient_name == ""){
			$errors[] = "Recipient Name";
			$error_found = true;
		}
		if($email_subject == ""){
			$errors[] = "Subject";
			$error_found = true;
		}
		if($email_body == ""){
			$errors[] = "Body";
			$error_found = true;
		}
		if($page_type == "cicero" && $cicero_country == ""){
			$errors[] = "Country";
			$error_found = true;
		}
		if($page_type == "cicero" && $cicero_country == "USA" && $cicero_state == ""){
			$errors[] = "State";
			$error_found = true;
		}
		if($page_type == "cicero" && $cicero_country != "" && $cicero_official == ""){
			$errors[] = "Official";
			$error_found = true;
		}
		
		if(!$error_found) {
		
			ciceroletters_install();
								
			$wpdb->query("INSERT INTO `".CICEROLETTERS_DB."`
			( `id`, `type`, `test`, `test_email`, `success_message`, `error_message`, `recipient`,`recipient_name`, `subject`, `body`, `bcc_email`, `bcc_note`, `country`, `state`, `official`, `updated`, `created`) VALUES 
			(NULL, '$page_type', '$page_test', '$page_test_email', '$page_success', '$page_error', '$email_recipient', '$email_recipient_name', '$email_subject', '$email_body', '$email_bcc_email', '$email_bcc_note', '$cicero_country', '$cicero_state', '$cicero_official', NOW(), NOW());");
			
			echo "
			<script type='text/javascript'>
			<!--
			window.location = '".CICEROLETTERS_PAGE_HOME."&success=1';
			//-->
			</script>
			";
			
			$success_message = "Letter added!";
		
		}else{
			
			$error_message = "Please fix the following errors: ".implode(", ", $errors);
			
		}
	}

	?>	
	
	<script type="text/javascript">
	
	jQuery(document).ready(function($) {
		
		// Show recipient field if "manual" is selected
		$('#page_type_select').change(function() {
			var email_type_select_value = $(this).find('option:selected').val();
						
			if(email_type_select_value == "manual") {
			  $('#email_recipient_container').show();
			  $('#email_recipient_name_container').show();
			  $('.cicero_options_box').hide();
			}else{
				$('#email_recipient_container').hide();
				$('#email_recipient_name_container').hide();
				$('.cicero_options_box').show();
			}
			
		});
		
		// Show states on country select
		$('#cicero_country').change(function() {
			var cicero_country_select_value = $(this).find('option:selected').val();
			if(cicero_country_select_value == "USA") {
			   $('#cicero_official_can_container').hide();
			   $('#cicero_state_usa_container').show();
			   $('#cicero_official_usa_container').show();
			}else if(cicero_country_select_value == "CAN") {
			   $('#cicero_state_usa_container').hide();
			   $('#cicero_official_usa_container').hide();
			   $('#cicero_official_can_container').show();
			}else{
				 $('#cicero_state_usa_container').hide();
			   $('#cicero_official_usa_container').hide();
			   $('#cicero_official_can_container').hide();
			}
		});
		
	});
	
	</script>
		
	<div class="wrap">
	
		<div id="icon-link-manager" class="icon32"><br></div>
		<h2>Add Letter</h2>
		
		<br />
		<?php if(isset($success_message) && $success_message != "") { ?>
		<div id="message" class="updated below-h2">
			<p><?php echo $success_message; ?></p>
		</div>
		<?php }elseif(isset($error_message) && $error_message != "") { ?>
		<div id="message" class="updated below-h2">
			<p><?php echo $error_message; ?></p>
		</div>
		<?php } ?>
		
		<div id="poststuff">
			
			<form method="post" action="<?= CICEROLETTERS_PAGE_ADD; ?>" class="validate" style="width:100%">
						
				<div id="namediv" class="stuffbox">
					<h3><label for="link_name">Page Options</label></h3>
					<div class="inside">
					
						<table class="form-table" style="width:100%;" cellspacing="2" cellpadding="5">
							<tbody>
							
							<tr class="form-field">
								<td valign="top" scope="row" width="160"><strong>Type of Email</strong></td>
								<td>
									<select name="page_type" id="page_type_select">
										<option value="">--</option>
										<option value="cicero" <?= (isset($_POST['page_type']) && $_POST['page_type'] == "cicero" ? "selected=\"selected\"" : ""); ?>>Cicero</option>
										<option value="manual" <?= (isset($_POST['page_type']) && $_POST['page_type'] == "manual" ? "selected=\"selected\"" : ""); ?>>Manual</option>
									</select>
								</td>
							</tr>
							
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Is Test?</strong></td>
								<td><input type="checkbox" name="page_test" id="page_test" value="true" <?= (isset($_POST['page_test'])&&$_POST['page_test']=="true"?"checked=\"checked\"":""); ?> /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Test Email</strong></td>
								<td><input type="text" name="page_test_email" id="page_test_email" style="width:300px;" value="<?= (isset($_POST['page_test_email'])?$_POST['page_test_email']:""); ?>" /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Success Message</strong></td>
								<td><input type="text" name="page_success" id="page_success" style="width:400px;" value="<?= (isset($_POST['page_success'])?$_POST['page_success']:""); ?>" /></td>									
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Error Message</strong></td>
								<td><input type="text" name="page_error" id="page_error" style="width:400px;" value="<?= (isset($_POST['page_error'])?$_POST['page_error']:""); ?>" /></td>
							</tr>
							
							</tbody>
						</table>
					
					</div>
				</div>
				
				<div id="namediv" class="stuffbox">
					<h3><label for="link_name">Email Options</label></h3>
					<div class="inside">
					
						<table class="form-table" style="width:100%;" cellspacing="2" cellpadding="5">
							<tbody>
							
							<tr class="form-field" id="email_recipient_container" <?= (isset($_POST['page_type']) && $_POST['page_type'] == "manual" ? "" : "style='display:none;'"); ?>>
								<td valign="top" scope="row" width="180"><strong>Recipient</strong></td>
								<td><input type="text" name="email_recipient" id="email_recipient" style="width:300px;" value="<?= (isset($_POST['email_recipient'])?$_POST['email_recipient']:""); ?>" /></td>
							</tr>
							<tr class="form-field" id="email_recipient_name_container" <?= (isset($_POST['page_type']) && $_POST['page_type'] == "manual" ? "" : "style='display:none;'"); ?>>
								<td valign="top" scope="row" width="180"><strong>Recipient Name</strong></td>
								<td><input type="text" name="email_recipient_name" id="email_recipient_name" style="width:300px;" value="<?= (isset($_POST['email_recipient_name'])?$_POST['email_recipient_name']:""); ?>" /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Subject</strong></td>
								<td><input type="text" name="email_subject" id="email_subject" style="width:400px;" value="<?= (isset($_POST['email_subject'])?$_POST['email_subject']:""); ?>" /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Body</strong></td>
								<td><textarea name="email_body" id="email_body" rows="8"><?= (isset($_POST['email_body'])?$_POST['email_body']:""); ?></textarea></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>BCC Email</strong></td>
								<td><input type="text" name="email_bcc_email" id="email_bcc_email" style="width:300px;" value="<?= (isset($_POST['email_bcc_email'])?$_POST['email_bcc_email']:""); ?>" /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>BCC Page Note</strong></td>
								<td><textarea name="email_bcc_note" id="email_bcc_note" style="width:450px;height:50px;"><?= (isset($_POST['email_bcc_note'])?$_POST['email_bcc_note']:""); ?></textarea></td>
							</tr>
							
							</tbody>
						</table>
					
					</div>
				</div>
				
				<div id="namediv" class="stuffbox cicero_options_box" <?= (isset($_POST['page_type']) && $_POST['page_type'] == "cicero" ? "" : "style='display:none;'"); ?>>
					<h3><label for="link_name">Cicero Options</label></h3>
					<div class="inside">
					
						<table class="form-table" style="width:100%;" cellspacing="2" cellpadding="5">
							<tbody>
							
							<tr class="form-field">
								<td valign="top" scope="row" width="160"><strong>Country</strong></td>
								<td>
									<select name="cicero_country" id="cicero_country">
										<option value="">--</option>
										<option value="USA" <?= (isset($_POST['cicero_country']) && $_POST['cicero_country'] == "USA" ? "selected=\"selected\"" : ""); ?>>United States</option>
										<option value="CAN" <?= (isset($_POST['cicero_country']) && $_POST['cicero_country'] == "CAN" ? "selected=\"selected\"" : ""); ?>>Canada</option>
									</select>
								</td>
							</tr>
							
							<tr class="form-field" id="cicero_state_usa_container" class="cicero_sub_select" style="display:none;">
								<td valign="top" scope="row"><strong>State</strong></td>
								<td>
									<select name="cicero_state_usa" id="cicero_state_usa"> 
										<option value="">--</option> 
										<option value="AL" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "AL" ? "selected=\"selected\"" : ""); ?>>Alabama</option> 
										<option value="AK" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "AK" ? "selected=\"selected\"" : ""); ?>>Alaska</option>  
										<option value="AZ" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "AZ" ? "selected=\"selected\"" : ""); ?>>Arizona</option>  
										<option value="AR" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "AR" ? "selected=\"selected\"" : ""); ?>>Arkansas</option>  
										<option value="CA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "CA" ? "selected=\"selected\"" : ""); ?>>California</option>  
										<option value="CO" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "CO" ? "selected=\"selected\"" : ""); ?>>Colorado</option>  
										<option value="CT" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "CT" ? "selected=\"selected\"" : ""); ?>>Connecticut</option>  
										<option value="DE" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "DE" ? "selected=\"selected\"" : ""); ?>>Delaware</option>  
										<option value="DC" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "DC" ? "selected=\"selected\"" : ""); ?>>District Of Columbia</option>  
										<option value="FL" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "FL" ? "selected=\"selected\"" : ""); ?>>Florida</option>  
										<option value="GA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "GA" ? "selected=\"selected\"" : ""); ?>>Georgia</option>  
										<option value="HI" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "HI" ? "selected=\"selected\"" : ""); ?>>Hawaii</option>  
										<option value="ID" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "ID" ? "selected=\"selected\"" : ""); ?>>Idaho</option>  
										<option value="IL" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "IL" ? "selected=\"selected\"" : ""); ?>>Illinois</option>  
										<option value="IN" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "IN" ? "selected=\"selected\"" : ""); ?>>Indiana</option>  
										<option value="IA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "IA" ? "selected=\"selected\"" : ""); ?>>Iowa</option>  
										<option value="KS" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "KS" ? "selected=\"selected\"" : ""); ?>>Kansas</option>  
										<option value="KY" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "KY" ? "selected=\"selected\"" : ""); ?>>Kentucky</option>  
										<option value="LA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "LA" ? "selected=\"selected\"" : ""); ?>>Louisiana</option>  
										<option value="ME" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "ME" ? "selected=\"selected\"" : ""); ?>>Maine</option>  
										<option value="MD" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MD" ? "selected=\"selected\"" : ""); ?>>Maryland</option>  
										<option value="MA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MA" ? "selected=\"selected\"" : ""); ?>>Massachusetts</option>  
										<option value="MI" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MI" ? "selected=\"selected\"" : ""); ?>>Michigan</option>  
										<option value="MN" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MN" ? "selected=\"selected\"" : ""); ?>>Minnesota</option>  
										<option value="MS" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MS" ? "selected=\"selected\"" : ""); ?>>Mississippi</option>  
										<option value="MO" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MO" ? "selected=\"selected\"" : ""); ?>>Missouri</option>  
										<option value="MT" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MT" ? "selected=\"selected\"" : ""); ?>>Montana</option>  
										<option value="NE" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NE" ? "selected=\"selected\"" : ""); ?>>Nebraska</option>  
										<option value="NV" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NV" ? "selected=\"selected\"" : ""); ?>>Nevada</option>  
										<option value="NH" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NH" ? "selected=\"selected\"" : ""); ?>>New Hampshire</option>  
										<option value="NJ" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NJ" ? "selected=\"selected\"" : ""); ?>>New Jersey</option>  
										<option value="NM" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NM" ? "selected=\"selected\"" : ""); ?>>New Mexico</option>  
										<option value="NY" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NY" ? "selected=\"selected\"" : ""); ?>>New York</option>  
										<option value="NC" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NC" ? "selected=\"selected\"" : ""); ?>>North Carolina</option>  
										<option value="ND" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "ND" ? "selected=\"selected\"" : ""); ?>>North Dakota</option>  
										<option value="OH" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "OH" ? "selected=\"selected\"" : ""); ?>>Ohio</option>  
										<option value="OK" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "OK" ? "selected=\"selected\"" : ""); ?>>Oklahoma</option>  
										<option value="OR" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "OR" ? "selected=\"selected\"" : ""); ?>>Oregon</option>  
										<option value="PA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "PA" ? "selected=\"selected\"" : ""); ?>>Pennsylvania</option>  
										<option value="RI" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "RI" ? "selected=\"selected\"" : ""); ?>>Rhode Island</option>  
										<option value="SC" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "SC" ? "selected=\"selected\"" : ""); ?>>South Carolina</option>  
										<option value="SD" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "SD" ? "selected=\"selected\"" : ""); ?>>South Dakota</option>  
										<option value="TN" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "TN" ? "selected=\"selected\"" : ""); ?>>Tennessee</option>  
										<option value="TX" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "TX" ? "selected=\"selected\"" : ""); ?>>Texas</option>  
										<option value="UT" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "UT" ? "selected=\"selected\"" : ""); ?>>Utah</option>  
										<option value="VT" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "VT" ? "selected=\"selected\"" : ""); ?>>Vermont</option>  
										<option value="VA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "VA" ? "selected=\"selected\"" : ""); ?>>Virginia</option>  
										<option value="WA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "WA" ? "selected=\"selected\"" : ""); ?>>Washington</option>  
										<option value="WV" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "WV" ? "selected=\"selected\"" : ""); ?>>West Virginia</option>  
										<option value="WI" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "WI" ? "selected=\"selected\"" : ""); ?>>Wisconsin</option>  
										<option value="WY" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "WY" ? "selected=\"selected\"" : ""); ?>>Wyoming</option> 
									</select>
								</td>
							</tr>
							<tr class="form-field" id="cicero_official_usa_container" class="cicero_sub_select" style="display:none;">
								<td valign="top" scope="row"><strong>Official</strong></td>
								<td>
									<select name="cicero_official_usa" id="cicero_official_usa"> 
										<option value="">--</option>
										<optgroup label="State">
											<option value="STATE_UPPER" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "STATE_UPPER" ? "selected=\"selected\"" : ""); ?>>Senate</option>  
											<option value="STATE_LOWER" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "STATE_LOWER" ? "selected=\"selected\"" : ""); ?>>Representative</option>  
											<option value="STATE_EXEC:Lieutenant Governor" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "STATE_EXEC:Lieutenant Governor" ? "selected=\"selected\"" : ""); ?>>Lieutenant Governor</option>  
											<option value="STATE_EXEC:Governor" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "STATE_EXEC:Governor" ? "selected=\"selected\"" : ""); ?>>Governor</option> 
										</optgroup>
										<optgroup label="National">
											<option value="NATIONAL_LOWER" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "NATIONAL_LOWER" ? "selected=\"selected\"" : ""); ?>>Senate</option>  
											<option value="NATIONAL_UPPER" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "NATIONAL_UPPER" ? "selected=\"selected\"" : ""); ?>>Representative</option> 
											<option value="NATIONAL_EXEC:Vice President" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "NATIONAL_EXEC:Vice President" ? "selected=\"selected\"" : ""); ?>>Vice President</option> 
											<option value="NATIONAL_EXEC:President of the United States" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "NATIONAL_EXEC:President of the United States" ? "selected=\"selected\"" : ""); ?>>President</option> 
										</optgroup>
									</select>
								</td>
							</tr>
							
							<tr class="form-field" id="cicero_official_can_container" class="cicero_sub_select" style="display:none;">
								<td valign="top" scope="row"><strong>Official</strong></td>
								<td>
									<select name="cicero_official_can" id="cicero_official_can"> 
										<option value="">--</option>
										<optgroup label="National">
											<option value="NATIONAL_UPPER" <?= (isset($_POST['cicero_official_can']) && $_POST['cicero_official_can'] == "NATIONAL_UPPER" ? "selected=\"selected\"" : ""); ?>>Senate</option> 
										</optgroup>
									</select>
								</td>
							</tr>
							
							</tbody>
						</table>

					</div>
				</div>
				
				<div style="padding:10px 0">
					<input type="submit" class="button-primary" name="add-letter" value="Add Letter" style="width:100px;border:0;" />
				</div>
						
			</form>
			
		</div>
	</div>

<?
}


// ciceroletters_admin_edit() displays the letters edit page
function ciceroletters_admin_edit() {

	global $wpdb;
	
	$errors = array();
	$errors_str = "";
	
	if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	
		?>
		
		<script type="text/javascript">
		<!--
		window.location = "<?= CICEROLETTERS_PAGE_HOME; ?>";
		//-->
		</script>
		
		<?php
	
	}else{
	
		if(isset($_POST['edit-letter'])){
	
			$errors = array();
			$errors_str = "";
			
			$page_type 				= mysql_real_escape_string(stripslashes($_POST['page_type']));
			$page_test 				= (isset($_POST['page_test']) ? mysql_real_escape_string(stripslashes($_POST['page_test'])) : "false");
			$page_test_email 	= mysql_real_escape_string(stripslashes($_POST['page_test_email']));
			$page_success 		= mysql_real_escape_string(stripslashes($_POST['page_success']));
			$page_error 			= mysql_real_escape_string(stripslashes($_POST['page_error']));
			
			$email_recipient 	= mysql_real_escape_string(stripslashes($_POST['email_recipient']));
			$email_recipient_name 	= mysql_real_escape_string(stripslashes($_POST['email_recipient_name']));
			$email_subject 		= mysql_real_escape_string(stripslashes($_POST['email_subject']));
			$email_body 			= mysql_real_escape_string(stripslashes($_POST['email_body']));
			$email_bcc_email 	= mysql_real_escape_string(stripslashes($_POST['email_bcc_email']));
			$email_bcc_note 	= mysql_real_escape_string(stripslashes($_POST['email_bcc_note']));
			
			$cicero_country 	= mysql_real_escape_string(stripslashes($_POST['cicero_country']));
			$cicero_state 		= "";
			$cicero_official	= "";
			if($cicero_country == "USA"){
				$cicero_state 		= mysql_real_escape_string(stripslashes($_POST['cicero_state_usa']));
				$cicero_official 	= mysql_real_escape_string(stripslashes($_POST['cicero_official_usa']));
			}elseif($cicero_country == "CAN"){
				$cicero_state 		= "";
				$cicero_official 	= mysql_real_escape_string(stripslashes($_POST['cicero_official_can']));
			}
			
			//Validation
    		$error_found = false;
    		if($page_type == "" || ($page_type != "cicero" && $page_type != "manual")){
    			$errors[] = "Type of Email";
    			$error_found = true;
    		}
    		if($page_type == "manual" && $email_recipient == ""){
    			$errors[] = "Recipient";
    			$error_found = true;
    		}
    		if($page_type == "manual" && $email_recipient_name == ""){
    			$errors[] = "Recipient Name";
    			$error_found = true;
    		}
    		if($email_subject == ""){
    			$errors[] = "Subject";
    			$error_found = true;
    		}
    		if($email_body == ""){
    			$errors[] = "Body";
    			$error_found = true;
    		}
    		if($page_type == "cicero" && $cicero_country == ""){
    			$errors[] = "Country";
    			$error_found = true;
    		}
    		if($page_type == "cicero" && $cicero_country == "USA" && $cicero_state == ""){
    			$errors[] = "State";
    			$error_found = true;
    		}
    		if($page_type == "cicero" && $cicero_country != "" && $cicero_official == ""){
    			$errors[] = "Official";
    			$error_found = true;
    		}
			
			if(!$error_found) {
			
				ciceroletters_install();
				
				$wpdb->query("UPDATE `".CICEROLETTERS_DB."` SET  
				`type` 						= '$page_type',
				`test` 						= '$page_test',
				`test_email` 			= '$page_test_email',
				`success_message` = '$page_success',
				`error_message` 	= '$page_error',
				`recipient` 			= '$email_recipient',
				`recipient_name` 	= '$email_recipient_name',
				`subject` 				= '$email_subject',
				`body` 						= '$email_body',
				`bcc_email` 			= '$email_bcc_email',
				`bcc_note` 				= '$email_bcc_note',
				`country` 				= '$cicero_country',
				`state` 					= '$cicero_state',
				`official` 				= '$cicero_official',
				`updated` 				= NOW( ) ,
				`created` 				= NOW( ) 
				WHERE `id` = ".mysql_real_escape_string($_GET['id']).";");
					
				$success_message = "Letter updated!";
			
			}else{
				
				$error_message = "Please fix the following errors: ".implode(", ", $errors);
				
			}
		}
		
	}
	
	$letter = $wpdb->get_row("SELECT * FROM ".CICEROLETTERS_DB." WHERE `id` = ".mysql_real_escape_string($_GET['id'])." LIMIT 1;");

	?>	
		
	<script type="text/javascript">
	
	jQuery(document).ready(function($) {
		
		// Show recipient field if "manual" is selected
		$('#page_type_select').change(function() {
			var email_type_select_value = $(this).find('option:selected').val();
						
			if(email_type_select_value == "manual") {
			  $('#email_recipient_container').show();
			  $('#email_recipient_name_container').show();
			  $('.cicero_options_box').hide();
			}else{
				$('#email_recipient_container').hide();
				$('#email_recipient_name_container').hide();
				$('.cicero_options_box').show();
			}
			
		});
		
		// Show states on country select
		$('#cicero_country').change(function() {
			var cicero_country_select_value = $(this).find('option:selected').val();
			if(cicero_country_select_value == "USA") {
			   $('#cicero_official_can_container').hide();
			   $('#cicero_state_usa_container').show();
			   $('#cicero_official_usa_container').show();
			}else if(cicero_country_select_value == "CAN") {
			   $('#cicero_state_usa_container').hide();
			   $('#cicero_official_usa_container').hide();
			   $('#cicero_official_can_container').show();
			}else{
				 $('#cicero_state_usa_container').hide();
			   $('#cicero_official_usa_container').hide();
			   $('#cicero_official_can_container').hide();
			}
		});
		
	});
	
	</script>
		
	<div class="wrap">
	
		<div id="icon-link-manager" class="icon32"><br></div>
		<h2>Edit Letter</h2>
		
		<br />
		<?php if(isset($success_message) && $success_message != "") { ?>
		<div id="message" class="updated below-h2">
			<p><?php echo $success_message; ?></p>
		</div>
		<?php }elseif(isset($error_message) && $error_message != "") { ?>
		<div id="message" class="updated below-h2">
			<p><?php echo $error_message; ?></p>
		</div>
		<?php } ?>
		
		<div id="poststuff">
			
			<form method="post" action="<?= CICEROLETTERS_PAGE_EDIT; ?>&id=<?= mysql_real_escape_string($_GET['id']); ?>" class="validate" style="width:100%">
						
				<div id="namediv" class="stuffbox">
					<h3><label for="link_name">Page Options</label></h3>
					<div class="inside">
					
						<table class="form-table" style="width:100%;" cellspacing="2" cellpadding="5">
							<tbody>
							
							<tr class="form-field">
								<td valign="top" scope="row" width="160"><strong>Type of Email</strong></td>
								<td>
									<select name="page_type" id="page_type_select">
										<option value="cicero" <?= (isset($_POST['page_type']) ? ($_POST['page_type'] == "cicero" ? "selected=\"selected\"" : "") : ($letter->type == "cicero" ? "selected=\"selected\"" : "")); ?>>Cicero</option>
										<option value="manual" <?= (isset($_POST['page_type']) ? ($_POST['page_type'] == "manual" ? "selected=\"selected\"" : "") : ($letter->type == "manual" ? "selected=\"selected\"" : "")); ?>>Manual</option>
									</select>
								</td>
							</tr>
							
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Is Test?</strong></td>
								<td><input type="checkbox" name="page_test" id="page_test" value="true" <?= (isset($_POST['page_test']) && $_POST['page_test']=="true" ? "checked=\"checked\"" : ($letter->test == "true" ? "checked=\"checked\"": "")); ?> /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Test Email</strong></td>
								<td><input type="text" name="page_test_email" id="page_test_email" style="width:300px;" value="<?= (isset($_POST['page_test_email'])?$_POST['page_test_email']:$letter->test_email); ?>" /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Success Message</strong></td>
								<td><input type="text" name="page_success" id="page_success" style="width:400px;" value="<?= (isset($_POST['page_success'])?$_POST['page_success']:$letter->success_message); ?>" /></td>									
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Error Message</strong></td>
								<td><input type="text" name="page_error" id="page_error" style="width:400px;" value="<?= (isset($_POST['page_error'])?$_POST['page_error']:$letter->error_message); ?>" /></td>
							</tr>
							
							</tbody>
						</table>
					
					</div>
				</div>
				
				<div id="namediv" class="stuffbox">
					<h3><label for="link_name">Email Options</label></h3>
					<div class="inside">
					
						<table class="form-table" style="width:100%;" cellspacing="2" cellpadding="5">
							<tbody>
							
							<tr class="form-field" id="email_recipient_container" <?= (isset($_POST['page_type']) ? ($_POST['page_type'] == "cicero" ? 'style="display:none;"' : "") : ($letter->type == "cicero" ? 'style="display:none;"' : "")); ?>>
								<td valign="top" scope="row" width="180"><strong>Recipient</strong></td>
								<td><input type="text" name="email_recipient" id="email_recipient" style="width:300px;" value="<?= (isset($_POST['email_recipient'])?$_POST['email_recipient']:$letter->recipient); ?>" /></td>
							</tr>
							<tr class="form-field" id="email_recipient_name_container" <?= (isset($_POST['page_type']) ? ($_POST['page_type'] == "cicero" ? 'style="display:none;"' : "") : ($letter->type == "cicero" ? 'style="display:none;"' : "")); ?>>
								<td valign="top" scope="row" width="180"><strong>Recipient Name</strong></td>
								<td><input type="text" name="email_recipient_name" id="email_recipient_name" style="width:300px;" value="<?= (isset($_POST['email_recipient_name'])?$_POST['email_recipient_name']:$letter->recipient_name); ?>" /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Subject</strong></td>
								<td><input type="text" name="email_subject" id="email_subject" style="width:400px;" value="<?= (isset($_POST['email_subject'])?$_POST['email_subject']:$letter->subject); ?>" /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>Body</strong></td>
								<td><textarea name="email_body" id="email_body" rows="8"><?= (isset($_POST['email_body'])?$_POST['email_body']:$letter->body); ?></textarea></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>BCC Email</strong></td>
								<td><input type="text" name="email_bcc_email" id="email_bcc_email" style="width:300px;" value="<?= (isset($_POST['email_bcc_email'])?$_POST['email_bcc_email']:$letter->bcc_email); ?>" /></td>
							</tr>
							<tr class="form-field">
								<td valign="top" scope="row"><strong>BCC Page Note</strong></td>
								<td><textarea name="email_bcc_note" id="email_bcc_note" style="width:450px;height:50px;"><?= (isset($_POST['email_bcc_note'])?$_POST['email_bcc_note']:$letter->bcc_note); ?></textarea></td>
							</tr>
							
							</tbody>
						</table>
					
					</div>
				</div>
				
				<div id="namediv" class="stuffbox cicero_options_box" <?= (isset($_POST['page_type']) ? ($_POST['page_type'] == "cicero" ? "" : 'style="display:none;"') : ($letter->type == "cicero" ? '' : 'style="display:none;"')); ?>>
					<h3><label for="link_name">Cicero Options</label></h3>
					<div class="inside">
					
						<table class="form-table" style="width:100%;" cellspacing="2" cellpadding="5">
							<tbody>
							
							<tr class="form-field">
								<td valign="top" scope="row" width="160"><strong>Country</strong></td>
								<td>
									<select name="cicero_country" id="cicero_country">
										<option value="">--</option>
										<option value="USA" <?= (isset($_POST['cicero_country']) && $_POST['cicero_country'] == "USA" ? "selected=\"selected\"" : ($letter->country == "USA" ? "selected=\"selected\"" : "")); ?>>United States</option>
										<option value="CAN" <?= (isset($_POST['cicero_country']) && $_POST['cicero_country'] == "CAN" ? "selected=\"selected\"" : ($letter->country == "CAN" ? "selected=\"selected\"" : "")); ?>>Canada</option>
									</select>
								</td>
							</tr>
							
							<tr class="form-field" id="cicero_state_usa_container" class="cicero_sub_select" <?= (isset($_POST['cicero_country']) && $_POST['cicero_country'] == "USA" ? "" : ($letter->country == "USA" ? "" : "style=\"display:none;\"")); ?>>
								<td valign="top" scope="row"><strong>State</strong></td>
								<td>
									<select name="cicero_state_usa" id="cicero_state_usa"> 
										<option value="">--</option> 
										<option value="AL" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "AL" ? "selected=\"selected\"" : ($letter->state == "AL" ? "selected=\"selected\"" : "")); ?>>Alabama</option> 
										<option value="AK" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "AK" ? "selected=\"selected\"" : ($letter->state == "AK" ? "selected=\"selected\"" : "")); ?>>Alaska</option>  
										<option value="AZ" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "AZ" ? "selected=\"selected\"" : ($letter->state == "AZ" ? "selected=\"selected\"" : "")); ?>>Arizona</option>  
										<option value="AR" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "AR" ? "selected=\"selected\"" : ($letter->state == "AR" ? "selected=\"selected\"" : "")); ?>>Arkansas</option>  
										<option value="CA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "CA" ? "selected=\"selected\"" : ($letter->state == "CA" ? "selected=\"selected\"" : "")); ?>>California</option>  
										<option value="CO" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "CO" ? "selected=\"selected\"" : ($letter->state == "CO" ? "selected=\"selected\"" : "")); ?>>Colorado</option>  
										<option value="CT" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "CT" ? "selected=\"selected\"" : ($letter->state == "CT" ? "selected=\"selected\"" : "")); ?>>Connecticut</option>  
										<option value="DE" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "DE" ? "selected=\"selected\"" : ($letter->state == "DE" ? "selected=\"selected\"" : "")); ?>>Delaware</option>  
										<option value="DC" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "DC" ? "selected=\"selected\"" : ($letter->state == "DC" ? "selected=\"selected\"" : "")); ?>>District Of Columbia</option>  
										<option value="FL" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "FL" ? "selected=\"selected\"" : ($letter->state == "FL" ? "selected=\"selected\"" : "")); ?>>Florida</option>  
										<option value="GA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "GA" ? "selected=\"selected\"" : ($letter->state == "GA" ? "selected=\"selected\"" : "")); ?>>Georgia</option>  
										<option value="HI" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "HI" ? "selected=\"selected\"" : ($letter->state == "HI" ? "selected=\"selected\"" : "")); ?>>Hawaii</option>  
										<option value="ID" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "ID" ? "selected=\"selected\"" : ($letter->state == "ID" ? "selected=\"selected\"" : "")); ?>>Idaho</option>  
										<option value="IL" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "IL" ? "selected=\"selected\"" : ($letter->state == "IL" ? "selected=\"selected\"" : "")); ?>>Illinois</option>  
										<option value="IN" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "IN" ? "selected=\"selected\"" : ($letter->state == "IN" ? "selected=\"selected\"" : "")); ?>>Indiana</option>  
										<option value="IA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "IA" ? "selected=\"selected\"" : ($letter->state == "IA" ? "selected=\"selected\"" : "")); ?>>Iowa</option>  
										<option value="KS" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "KS" ? "selected=\"selected\"" : ($letter->state == "KS" ? "selected=\"selected\"" : "")); ?>>Kansas</option>  
										<option value="KY" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "KY" ? "selected=\"selected\"" : ($letter->state == "KY" ? "selected=\"selected\"" : "")); ?>>Kentucky</option>  
										<option value="LA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "LA" ? "selected=\"selected\"" : ($letter->state == "LA" ? "selected=\"selected\"" : "")); ?>>Louisiana</option>  
										<option value="ME" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "ME" ? "selected=\"selected\"" : ($letter->state == "ME" ? "selected=\"selected\"" : "")); ?>>Maine</option>  
										<option value="MD" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MD" ? "selected=\"selected\"" : ($letter->state == "MD" ? "selected=\"selected\"" : "")); ?>>Maryland</option>  
										<option value="MA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MA" ? "selected=\"selected\"" : ($letter->state == "MA" ? "selected=\"selected\"" : "")); ?>>Massachusetts</option>  
										<option value="MI" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MI" ? "selected=\"selected\"" : ($letter->state == "MI" ? "selected=\"selected\"" : "")); ?>>Michigan</option>  
										<option value="MN" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MN" ? "selected=\"selected\"" : ($letter->state == "MN" ? "selected=\"selected\"" : "")); ?>>Minnesota</option>  
										<option value="MS" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MS" ? "selected=\"selected\"" : ($letter->state == "MS" ? "selected=\"selected\"" : "")); ?>>Mississippi</option>  
										<option value="MO" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MO" ? "selected=\"selected\"" : ($letter->state == "MO" ? "selected=\"selected\"" : "")); ?>>Missouri</option>  
										<option value="MT" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "MT" ? "selected=\"selected\"" : ($letter->state == "MT" ? "selected=\"selected\"" : "")); ?>>Montana</option>  
										<option value="NE" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NE" ? "selected=\"selected\"" : ($letter->state == "NE" ? "selected=\"selected\"" : "")); ?>>Nebraska</option>  
										<option value="NV" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NV" ? "selected=\"selected\"" : ($letter->state == "NV" ? "selected=\"selected\"" : "")); ?>>Nevada</option>  
										<option value="NH" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NH" ? "selected=\"selected\"" : ($letter->state == "NH" ? "selected=\"selected\"" : "")); ?>>New Hampshire</option>  
										<option value="NJ" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NJ" ? "selected=\"selected\"" : ($letter->state == "NJ" ? "selected=\"selected\"" : "")); ?>>New Jersey</option>  
										<option value="NM" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NM" ? "selected=\"selected\"" : ($letter->state == "NM" ? "selected=\"selected\"" : "")); ?>>New Mexico</option>  
										<option value="NY" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NY" ? "selected=\"selected\"" : ($letter->state == "NY" ? "selected=\"selected\"" : "")); ?>>New York</option>  
										<option value="NC" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "NC" ? "selected=\"selected\"" : ($letter->state == "NC" ? "selected=\"selected\"" : "")); ?>>North Carolina</option>  
										<option value="ND" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "ND" ? "selected=\"selected\"" : ($letter->state == "ND" ? "selected=\"selected\"" : "")); ?>>North Dakota</option>  
										<option value="OH" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "OH" ? "selected=\"selected\"" : ($letter->state == "OH" ? "selected=\"selected\"" : "")); ?>>Ohio</option>  
										<option value="OK" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "OK" ? "selected=\"selected\"" : ($letter->state == "OK" ? "selected=\"selected\"" : "")); ?>>Oklahoma</option>  
										<option value="OR" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "OR" ? "selected=\"selected\"" : ($letter->state == "OR" ? "selected=\"selected\"" : "")); ?>>Oregon</option>  
										<option value="PA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "PA" ? "selected=\"selected\"" : ($letter->state == "PA" ? "selected=\"selected\"" : "")); ?>>Pennsylvania</option>  
										<option value="RI" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "RI" ? "selected=\"selected\"" : ($letter->state == "RI" ? "selected=\"selected\"" : "")); ?>>Rhode Island</option>  
										<option value="SC" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "SC" ? "selected=\"selected\"" : ($letter->state == "SC" ? "selected=\"selected\"" : "")); ?>>South Carolina</option>  
										<option value="SD" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "SD" ? "selected=\"selected\"" : ($letter->state == "SD" ? "selected=\"selected\"" : "")); ?>>South Dakota</option>  
										<option value="TN" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "TN" ? "selected=\"selected\"" : ($letter->state == "TN" ? "selected=\"selected\"" : "")); ?>>Tennessee</option>  
										<option value="TX" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "TX" ? "selected=\"selected\"" : ($letter->state == "TX" ? "selected=\"selected\"" : "")); ?>>Texas</option>  
										<option value="UT" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "UT" ? "selected=\"selected\"" : ($letter->state == "UT" ? "selected=\"selected\"" : "")); ?>>Utah</option>  
										<option value="VT" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "VT" ? "selected=\"selected\"" : ($letter->state == "VT" ? "selected=\"selected\"" : "")); ?>>Vermont</option>  
										<option value="VA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "VA" ? "selected=\"selected\"" : ($letter->state == "VA" ? "selected=\"selected\"" : "")); ?>>Virginia</option>  
										<option value="WA" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "WA" ? "selected=\"selected\"" : ($letter->state == "WA" ? "selected=\"selected\"" : "")); ?>>Washington</option>  
										<option value="WV" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "WV" ? "selected=\"selected\"" : ($letter->state == "WV" ? "selected=\"selected\"" : "")); ?>>West Virginia</option>  
										<option value="WI" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "WI" ? "selected=\"selected\"" : ($letter->state == "WI" ? "selected=\"selected\"" : "")); ?>>Wisconsin</option>  
										<option value="WY" <?= (isset($_POST['cicero_state_usa']) && $_POST['cicero_state_usa'] == "WY" ? "selected=\"selected\"" : ($letter->state == "WY" ? "selected=\"selected\"" : "")); ?>>Wyoming</option> 
									</select>
								</td>
							</tr>
							<tr class="form-field" id="cicero_official_usa_container" class="cicero_sub_select" <?= (isset($_POST['cicero_country']) && $_POST['cicero_country'] == "USA" ? "" : ($letter->country == "USA" ? "" : "style=\"display:none;\"")); ?>>
								<td valign="top" scope="row"><strong>Official</strong></td>
								<td>
									<select name="cicero_official_usa" id="cicero_official_usa"> 
										<option value="">--</option>
										<optgroup label="State">
											<option value="STATE_UPPER" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "STATE_UPPER" ? "selected=\"selected\"" : ($letter->official == "STATE_UPPER" ? "selected=\"selected\"" : "")); ?>>Senate</option>  
											<option value="STATE_LOWER" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "STATE_LOWER" ? "selected=\"selected\"" : ($letter->official == "STATE_LOWER" ? "selected=\"selected\"" : "")); ?>>Representative</option>  
											<option value="STATE_EXEC:Lieutenant Governor" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "STATE_EXEC:Lieutenant Governor" ? "selected=\"selected\"" : ($letter->official == "STATE_EXEC:Lieutenant Governor" ? "selected=\"selected\"" : "")); ?>>Lieutenant Governor</option>  
											<option value="STATE_EXEC:Governor" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "STATE_EXEC:Governor" ? "selected=\"selected\"" : ($letter->official == "STATE_EXEC:Governor" ? "selected=\"selected\"" : "")); ?>>Governor</option> 
										</optgroup>
										<optgroup label="National">
											<option value="NATIONAL_UPPER" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "NATIONAL_UPPER" ? "selected=\"selected\"" : ($letter->official == "NATIONAL_UPPER" ? "selected=\"selected\"" : "")); ?>>Senate</option>  
											<option value="NNATIONAL_LOWER" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "NATIONAL_LOWER" ? "selected=\"selected\"" : ($letter->official == "NATIONAL_LOWER" ? "selected=\"selected\"" : "")); ?>>Representative</option> 
											<option value="NATIONAL_EXEC:Vice President" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "NATIONAL_EXEC:Vice President" ? "selected=\"selected\"" : ($letter->official == "NATIONAL_EXEC:Vice President" ? "selected=\"selected\"" : "")); ?>>Vice President</option> 
											<option value="NATIONAL_EXEC:President of the United States" <?= (isset($_POST['cicero_official_usa']) && $_POST['cicero_official_usa'] == "NATIONAL_EXEC:President of the United States" ? "selected=\"selected\"" : ($letter->official == "NATIONAL_EXEC:President of the United States" ? "selected=\"selected\"" : "")); ?>>President</option> 
										</optgroup>
									</select>
								</td>
							</tr>
							
							<tr class="form-field" id="cicero_official_can_container" class="cicero_sub_select" <?= (isset($_POST['cicero_country']) && $_POST['cicero_country'] == "CAN" ? "" : ($letter->country == "CAN" ? "" : "style=\"display:none;\"")); ?>>
								<td valign="top" scope="row"><strong>Official</strong></td>
								<td>
									<select name="cicero_official_can" id="cicero_official_can"> 
										<option value="">--</option>
										<optgroup label="National">
											<option value="NATIONAL_UPPER" <?= (isset($_POST['cicero_official_can']) && $_POST['cicero_official_can'] == "NATIONAL_UPPER" ? "selected=\"selected\"" : ($letter->official == "NATIONAL_UPPER" ? "selected=\"selected\"" : "")); ?>>Senate</option> 
										</optgroup>
									</select>
								</td>
							</tr>
							
							</tbody>
						</table>

					</div>
				</div>
				
				<div style="padding:10px 0">
					<input type="submit" class="button-primary" name="edit-letter" value="Submit" style="width:100px;border:0;" />
				</div>
						
			</form>
			
		</div>
	</div>
	
<?
}


// ciceroletters_admin_help() displays the help page
function ciceroletters_admin_help() {

	global $wpdb;

	?>	
		
	<div class="wrap">
	
		<div id="icon-link-manager" class="icon32"><br></div>
		<h2>Help</h2>
		
		<fieldset style="border:1px solid #ccc;margin:0 0 10px 0;padding:0 10px 7px 20px;">
			
			<legend><strong>How do I output the forms?</strong></legend>
			<p>The way that the forms are outputted on the website is by the use of shortcodes. A shortcode is a small piece of code that will take a id for a letter and use that within the shortcode. The general layout of the shortcode will look like this:</p>
			<p><em>[cicero id='123']</em></p>
			<p>This can be found on the "Letters" screen and all that needs to be done is to copy the small bit of code and place it wherever you would like it in the page.</p>
			
		</fieldset>
		
	</div>

<?
}

// [cicero-letters id="XX"]
function ciceroletters_shortcode_func( $atts ) {
	
	// Globals
	global $wpdb;
	
	// Extract data
	extract( shortcode_atts( array(
		'id' => ''
	), $atts ) );
	
	// Get letter
	$letter = $wpdb->get_row("SELECT * FROM ".CICEROLETTERS_DB." WHERE `id` = ".$id." LIMIT 1;");
	
	// Enqueue scrips
	wp_enqueue_script('ciceroletters-jquery-docready', plugins_url('/_js/jquery.docready.php', __FILE__), array('jquery'));
	
	// Check for manual or cicero
	if($letter->type == "manual"){
		
		ob_start();
		?>
		<div id='ciceroletters_email_container'>
			<form id="ciceroletters_email_form" method="post">
			
				<!-- Form hidden info -->
				<?php
				if($letter->test == "true")
					echo "<input type='hidden' id='ciceroletters_email_to' name='ciceroletters_email_to' value='".$letter->test_email."' />";
				else
					echo "<input type='hidden' id='ciceroletters_email_to' name='ciceroletters_email_to' value='".$letter->recipient."' />";
				if($letter->bcc_email != "")
					echo "<input type='hidden' id='ciceroletters_email_bcc_email' name='ciceroletters_email_bcc_email' value='".$letter->bcc_email."' />";
				?>
				<input type='hidden' id='ciceroletters_email_to_names' name='ciceroletters_email_to_names' value='<?= $letter->recipient_name; ?>' />
				<input type='hidden' id='ciceroletters_search_letter_id' name='ciceroletters_search_letter_id' value='<?= $id; ?>' />
				
				<strong>Email Information</strong>
				<br /><br />
				<table>
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
							<textarea id="ciceroletters_email_body" name='ciceroletters_email_body' style="width:200px;height:150px;"><?= $letter->body; ?></textarea>
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
		</div>
		
		<div id='ciceroletters_successerror_container'></div>
		
		<div id='ciceroletters_loading_container' style='display:none;'>
			<img src='<?php echo plugins_url('/_img/ajax-loader.gif', __FILE__); ?>' alt='Loading' />
		</div>

		<?php
		
		$output_message = ob_get_contents();
		ob_end_clean();
		
	}elseif($letter->type == "cicero"){
		
		// Create output
		$output_message = "";
		$output_message .= "
		<div id='ciceroletters_search_container'>
				
			<strong>Search Your Address</strong>
			<br /><br />
			<form id='ciceroletters_search_form'>
				<input type='text' id='ciceroletters_search_field' name='ciceroletters_search_field' size='30' />
				<span id='ciceroletters_search_field_error' style='color:red;margin-bottom:15px;display:none;'>Please enter your address</span>
				<br /><br />
				<input type='submit' id='ciceroletters_search_submit' name='ciceroletters_search_submit' value='Search Address' />
				<input type='hidden' id='ciceroletters_search_letter_id' name='ciceroletters_search_letter_id' value='$id' />
			</form>
			<br /><br />
		
		</div>
		
		<div id='ciceroletters_email_container'></div>
		
		<div id='ciceroletters_successerror_container'></div>
		
		<div id='ciceroletters_loading_container'>
			<img src='".plugins_url('/_img/ajax-loader.gif', __FILE__)."' alt='Loading' />
		</div>
		";
		
	}
	
	// Return
	return $output_message;
	
}
add_shortcode( 'cicero-letters', 'ciceroletters_shortcode_func' );

?>
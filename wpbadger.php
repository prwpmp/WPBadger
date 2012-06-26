<?php
/**
 * @package WPBadger
 */
/*
Plugin Name: WPBadger
Plugin URI: https://github.com/davelester/WPBadger
Description: A lightweight badge issuing platform built using WordPress.
Version: 0.0.5
Author: Dave Lester
Author URI: http://www.davelester.org
*/

add_action('admin_menu', 'wpbadger_admin_menu');
register_activation_hook(__FILE__,'wpbadger_install');

require_once( dirname(__FILE__) . '/includes/badges.php' );
require_once( dirname(__FILE__) . '/includes/awards.php' );

global $wpbadger_db_version;
$wpbadger_db_version = "0.0.5";

function wpbadger_install()
{
	global $wpbadger_db_version;

	add_option("wpbadger_db_version", $wpbadger_db_version);
}

function wpbadger_admin_menu()
{
	add_submenu_page('options-general.php','Configure WPBadger Plugin','WPBadger Config','manage_options','wpbadger_configure_plugin','wpbadger_configure_plugin');
	add_submenu_page('edit.php?post_type=award','WPBadger | Bulk Award Badges','Bulk Award Badges','manage_options','wpbadger_bulk_award_badges','wpbadger_bulk_award_badges');
}

function wpbadger_bulk_award_badges()
{
	// Has to be rewritten to handle custom post types
	
	global $wpdb;

	if ($_POST['save']) {
		if ($_REQUEST['wpbadger_badge_id'] && $_REQUEST['wpbadger_award_email_address']) {

			$badge_id = $_REQUEST['wpbadger_badge_id'];
			$email_address = $_REQUEST['wpbadger_award_email_address'];
			$evidence = $_REQUEST['wpbadger_award_evidence'];
			$expires = $_REQUEST['wpbadger_award_expires'];

			// Start off by accepting one email address at a time..
			// eventually expand this to include batch listings of emails, split and verify correct email address formatting

			// Generate the recipient using the WordPress salt. foo is a placeholder
			$recipient = 'foo';

			// Issued on should be retrieving using unix timestamp (or similar method)
			$issued_on = '';

			$awarded_badges_table_name = $wpdb->prefix . "wpbadger_awarded_badges";

			$wpdb->insert( $awarded_badges_table_name, array( 'badge_id' => $badge_id, 'email_address' => $email_address, 'recipient' => $recipient, 'issued_on' => $issued_on, 'expires' => $expires, 'evidence' => $evidence ) );
			echo "success!!!";
		} else {
			echo "Badge award was unsuccessful. It is necessary to specify a badge and email address.";
		}
	}

	wpbadger_admin_header('Manage Awarded Badges');
?>

	<h2>Award Badges in Bulk</h2>

	<form method="POST" action="" name="wpbadger_config">

	    <table class="form-table">
	        <tr valign="top">
	        <th scope="row">Choose Badge</th>
	        <td>
				<select name="wpbadger_badge_id" id="wpbadger_badge_id">
				<?php
					$badges_table_name = $wpdb->prefix . "wpbadger_badges";
					$badges = $wpdb->get_results("SELECT * FROM $badges_table_name");

					foreach ($badges as $badge) {
						echo "<option id='wpbadger_badge_id' value='$badge->badge_id'>$badge->name (Version $badge->version)</option>";
					}
				?>
				</select>
	        </td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">Email Address</th>
	        <td><input type="text" name="wpbadger_award_email_address" /></td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">Evidence</th>
	        <td><textarea name="wpbadger_award_evidence" id="wpbadger_award_evidence" rows="4" cols="30"></textarea></td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">Expiration Date</th>
	        <td><input type="text" name="wpbadger_award_expires" /></td>
	        </tr>
	    </table>

	    <p class="submit">
	    <input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes') ?>" />
	    </p>

	</form>
	</div>

<?php
}

function wpbadger_admin_header($tab)
{?>
<div class="wrap">
<?php
}

function wpbadger_accept_award_page()
{
	echo "You have been awarded the _____ badge! Choose to accept this badge and add it to your badge backpack, or decline.";
	// On this public page, create div to echo the badge criteria, as well as the PNG of the image
}

function wpbadger_json_assertion() {
	header('Content-Type: application/json');
	// Call database to build a JSON file given the award information of a specified ID. include error JSON if it fails.
}

// Checks two mandatory fields of configured. If options are empty or don't exist, return FALSE
function wpbadger_configured()
{
	if (get_option('wpbadger_config_origin') && get_option('wpbadger_config_name')) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function wpbadger_configure_plugin()
{
if ($_POST['save']) {
	if ($_REQUEST['wpbadger_config_origin']) {
		update_option('wpbadger_config_origin', $_REQUEST['wpbadger_config_origin']);
		$success = TRUE;
	}
	if ($_REQUEST['wpbadger_config_name']) {
		update_option('wpbadger_config_name', $_REQUEST['wpbadger_config_name']);
		$success = TRUE;
	}

	if ($success) {
		echo "Options successfully updated";
	}
}

wpbadger_admin_header('Configure Plugin');
?>

<h2>Configuration</h2>

<form method="POST" action="" name="wpbadger_config">

    <table class="form-table">
        <tr valign="top">
        <th scope="row">Origin</th>
        <td><input type="text" name="wpbadger_config_origin" value="<?php echo get_option('wpbadger_config_origin'); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Issuing Agent Name</th>
        <td><input type="text" name="wpbadger_config_name" value="<?php echo get_option('wpbadger_config_name'); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Issuing Organization</th>
        <td><input type="text" name="wpbadger_config_org" value="<?php echo get_option('wpbadger_config_org'); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Contact Email Address</th>
        <td><input type="text" name="wpbadger_config_contact" value="<?php echo get_option('wpbadger_config_contact'); ?>" /></td>
        </tr>

		<tr valign="top">
		<th scope="row">Badge Award Email Text</th>
		<td><textarea name="wpbadger_config_award_email_text" id="wpbadger_config_award_email_text" rows="4" cols="30"></textarea></td>
		</tr>
    </table>

    <p class="submit">
    <input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>

<?php
}
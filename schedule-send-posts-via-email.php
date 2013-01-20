<?php
/*
Plugin Name: Schedule and Send Posts via Email
Description: 
Author: developdaly
Version: 1.0
Author URI: http://developdaly.com/
Plugin URI: http://wordpress.org/extend/plugins/schedule-send-posts-via-email/

  This plugin is released under version 3 of the GPL:
  http://www.opensource.org/licenses/gpl-3.0.html
*/

require_once( WP_PLUGIN_DIR .'/schedule-send-posts-via-email/inc/MCAPI.class.php' );
require_once( WP_PLUGIN_DIR .'/schedule-send-posts-via-email/inc/config.inc.php' );
require_once( WP_PLUGIN_DIR .'/schedule-send-posts-via-email/email.php' );

add_action( 'admin_menu',				'sspe_add_menu' );
add_action( 'admin_enqueue_scripts',	'sspe_enqueue_scripts' );
add_action( 'sspe_email_videos',		'sspe_schedule_email', 10, 3 );

date_default_timezone_set( get_option( 'timezone_string' ) );

// Add page as submenu to Tools
function sspe_add_menu() {
   add_submenu_page( 'tools.php', 'Schedule and Send Posts via Email', 'Email Posts', 'edit_posts', 'schedule-send-posts-via-email/schedule-send-posts-via-email.php', 'sspe_add_menu_page_callback' );
}

// Load scripts and styles
function sspe_enqueue_scripts($hook) {
	wp_enqueue_style( 'chosen',					plugins_url( '/assets/chosen.css', __FILE__ ) );
  	wp_enqueue_style( 'jquery-ui-datepicker',	plugins_url( '/assets/jquery-ui-1.9.2.custom.min.css', __FILE__ ) );

    wp_enqueue_script( 'chosen',				plugins_url( '/assets/jquery.chosen.min.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'jquery-ui-timepicker',	plugins_url( '/assets/jquery.timepicker.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ) );
    wp_enqueue_script( 'app',					plugins_url( '/assets/app.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-timepicker' ) );
}

// Sets up the action to run when the scheduled event fires (i.e. email users)
function sspe_schedule_email( $post1_id, $post2_id, $post3_id ) {
	
	$sspe_options = get_option( 'sspe_settings' );

	// Mailchimp API Key
	$api = new MCAPI( $sspe_options['sspe_mailchimp_api_key'] );
			
	// Get the main post title	
	$title				= get_the_title( $post1_id );

	// Regular campaign
	$type				= 'regular';
	
	// Campaign title
	$opts['title']		= $sspe_options['sspe_mailchimp_campaign_title'];
		
	// List ID
	$opts['list_id']	= $sspe_options['sspe_mailchimp_list_id'];
	
	// Email subject
	$opts['subject']	= $sspe_options['sspe_mailchimp_email_subject'] .' - ' . $title;
	
	// From email address
	$opts['from_email']	= $sspe_options['sspe_mailchimp_from_email']; 
	
	// From name
	$opts['from_name']	= $sspe_options['sspe_mailchimp_from_name'];

	// To name
	$opts['to_name']	= $sspe_options['sspe_mailchimp_to_name'];
		
	// Tracking options
	$opts['tracking']	= array(
							'opens'			=> true,
							'html_clicks'	=> true,
							'text_clicks'	=> true
						);
	
	// Google Analytics ID
	$opts['analytics']	= array(
							'google' => $sspe_options['sspe_mailchimp_google_analytics']
						);
	
	// Email content
	$content = array(
		'html'			=> sspe_email_template_html( $post1_id, $post2_id, $post3_id ),
		'text'			=> sspe_email_template_text( $post1_id, $post2_id, $post3_id )
	);
	
	// Create the camppaign based on the options above
	$retval = $api->campaignCreate( $type, $opts, $content );
		
	// Email administrator if campaign errors out or send campaign
	if ($api->errorCode){
		$message = "\n\tCode=".$api->errorCode;
		$message .= "\n\tMsg=".$api->errorMessage."\n";
		error_log( '[Schedule and Send Posts via Email Plugin] '. $message );
	} else {
		$api->campaignSendNow( $retval );
	}
		
}

// Display the admin page
function sspe_add_menu_page_callback() {

	// Must be Editor to access the settings page.
    if ( !current_user_can( 'edit_posts' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    // variables for the field and option names
	$sspe_post1		= 'sspe_post1';
	$sspe_post2		= 'sspe_post2';
	$sspe_post3		= 'sspe_post3';
	$sspe_hidden	= 'sspe_hidden';
	$sspe_timestamp	= 'sspe_timestamp';
	$sspe_mailchimp_api_key_field			= 'sspe_mailchimp_api_key';
	$sspe_mailchimp_campaign_title_field	= 'sspe_mailchimp_campaign_title';
	$sspe_mailchimp_list_id_field			= 'sspe_mailchimp_list_id';
	$sspe_mailchimp_email_subject_field		= 'sspe_mailchimp_email_subject';
	$sspe_mailchimp_from_email_field		= 'sspe_mailchimp_from_email';
	$sspe_mailchimp_from_name_field			= 'sspe_mailchimp_from_name';
	$sspe_mailchimp_to_name_field			= 'sspe_mailchimp_to_name';
	$sspe_mailchimp_google_analytics_field	= 'sspe_mailchimp_google_analytics';

	$sspe_options = get_option( 'sspe_settings' );

    // See if the user has posted some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $sspe_hidden ]) && $_POST[ $sspe_hidden ] == 'Y' ) {

		$sspe_options = array(
			$sspe_mailchimp_api_key_field			=> stripslashes( $_POST[ $sspe_mailchimp_api_key_field ] ),
			$sspe_mailchimp_campaign_title_field	=> stripslashes( $_POST[ $sspe_mailchimp_campaign_title_field ] ),
			$sspe_mailchimp_list_id_field			=> stripslashes( $_POST[ $sspe_mailchimp_list_id_field ] ),
			$sspe_mailchimp_email_subject_field		=> stripslashes( $_POST[ $sspe_mailchimp_email_subject_field ] ),
			$sspe_mailchimp_from_email_field		=> stripslashes( $_POST[ $sspe_mailchimp_from_email_field ] ),
			$sspe_mailchimp_from_name_field			=> stripslashes( $_POST[ $sspe_mailchimp_from_name_field ] ),
			$sspe_mailchimp_to_name_field			=> stripslashes( $_POST[ $sspe_mailchimp_to_name_field ] ),
			$sspe_mailchimp_google_analytics_field	=> stripslashes( $_POST[ $sspe_mailchimp_google_analytics_field ] )
		);

		// Save settings
        update_option( 'sspe_settings', $sspe_options );		

		// Define arguments
		$sspe_args = array(
			$_POST[ $sspe_post1 ],
			$_POST[ $sspe_post2 ],
			$_POST[ $sspe_post3 ]
		);
				
		// Schedule event
		if( !empty( $_POST[ $sspe_timestamp ] ) )
			wp_schedule_single_event( strtotime( $_POST[ $sspe_timestamp ] ), 'sspe_email_videos', $sspe_args );
		
	    // Put an settings updated message on the screen
		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'sspe'); ?></strong></p></div>
	<?php } ?>
	
	<style>
		/* css for timepicker */
		.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
		.ui-timepicker-div dl { text-align: left; }
		.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
		.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
		.ui-timepicker-div td { font-size: 90%; }
		.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
		
		.ui-timepicker-rtl{ direction: rtl; }
		.ui-timepicker-rtl dl { text-align: right; }
		.ui-timepicker-rtl dl dd { margin: 0 65px 10px 10px; }
	</style>
		
	<div class="wrap">
	
		<h2><?php echo __( 'Schedule and Send Posts via Email', 'sspe' ) ?></h2>
		
		<form name="sspe" method="post" action="">
			
			<div style="float: left; width: 65%;">
			
				<?php $posts = get_posts( array( 'posts_per_page' => -1 ) ); ?>
			
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_post1; ?>">"Today's Video"</label></th>
							<td>
								<select name="<?php echo $sspe_post1; ?>" data-placeholder="Pick Today's Video..." style="width:350px;" class="chzn-select">
				
									<option></option>
									<?php foreach( $posts as $post ): ?>
									<option value="<?php echo $post->ID; ?>"><?php echo $post->ID; ?> - <?php echo $post->post_title; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_post2; ?>">Expert Video</label></th>
							<td>
								<select name="<?php echo $sspe_post2; ?>" data-placeholder="Pick the expert video..." style="width:350px;" class="chzn-select">
				
									<option></option>
									<?php foreach( $posts as $post ): ?>
									<option value="<?php echo $post->ID; ?>"><?php echo $post->ID; ?> - <?php echo $post->post_title; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_post3; ?>">Spanish Video</label></th>
							<td>
								<select name="<?php echo $sspe_post3; ?>" data-placeholder="Pick the Spanish video..." style="width:350px;" class="chzn-select">
				
									<option></option>
									<?php foreach( $posts as $post ): ?>
									<option value="<?php echo $post->ID; ?>"><?php echo $post->ID; ?> - <?php echo $post->post_title; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_post3; ?>">Schedule</label></th>
							<td>
								<input id="sspe-datepicker" class="regular-text" type="text" placeholder="Choose a date..." name="<?php echo $sspe_timestamp; ?>">
							</td>
						</tr>
					</tbody>
				</table>
				
			</div>

			<div style="float: right; width: 35%;">

				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_mailchimp_api_key_field; ?>">Mailchimp API Key</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $sspe_mailchimp_api_key_field; ?>" value="<?php echo $sspe_options[$sspe_mailchimp_api_key_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_mailchimp_campaign_title_field; ?>">Campaign Title</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $sspe_mailchimp_campaign_title_field; ?>" value="<?php echo $sspe_options[$sspe_mailchimp_campaign_title_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_mailchimp_list_id_field; ?>">List ID</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $sspe_mailchimp_list_id_field; ?>" value="<?php echo $sspe_options[$sspe_mailchimp_list_id_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_mailchimp_email_subject_field; ?>">Email Subject</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $sspe_mailchimp_email_subject_field; ?>" value="<?php echo $sspe_options[$sspe_mailchimp_email_subject_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_mailchimp_from_email_field; ?>">From Email Address</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $sspe_mailchimp_from_email_field; ?>" value="<?php echo $sspe_options[$sspe_mailchimp_from_email_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_mailchimp_from_name_field; ?>">From Name</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $sspe_mailchimp_from_name_field; ?>" value="<?php echo $sspe_options[$sspe_mailchimp_from_name_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_mailchimp_to_name_field; ?>">To Name</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $sspe_mailchimp_to_name_field; ?>" value="<?php echo $sspe_options[$sspe_mailchimp_to_name_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_mailchimp_google_analytics_field; ?>">Google Analytics</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $sspe_mailchimp_google_analytics_field; ?>" value="<?php echo $sspe_options[$sspe_mailchimp_google_analytics_field]; ?>">
							</td>
						</tr>	
					</tbody>
				</table>

				<p class="submit">
					<input type="submit" name="save_settings" class="button" value="<?php esc_attr_e('Save Settings') ?>" />
				</p>
													
			</div>
						
			<div class="clear"></div>
						
			<input type="hidden" name="<?php echo $sspe_hidden; ?>" value="Y">
						
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Schedule Email') ?>" />
			</p>
		
		</form>
	</div>

	<?php
	$cron = _get_cron_array();
	$schedules = wp_get_schedules();
	$date_format = _x( 'M j, Y @ G:i', 'Publish box date format', 'cron-view' );
	?>

	<h3><?php _e('Scheduled Emails', 'cron-view'); ?></h3>

	<table class="widefat fixed">
		<thead>
			<tr>
				<th scope="col"><?php _e('Date/time scheduled', 'sspe'); ?></th>
				<th scope="col"><?php _e('Schedule', 'sspe'); ?></th>
				<th scope="col"><?php _e('Posts', 'sspe'); ?></th>
			</tr>
		</thead>
		<tbody>
			
			<?php foreach ( $cron as $timestamp => $cronhooks ) { ?>
				<?php foreach ( (array) $cronhooks as $hook => $events ) { $i = ''; ?>
					<?php foreach ( (array) $events as $event ) { $i++; ?>
						<?php if( $hook != 'sspe_email_videos' )
							continue;
						?>
						<tr>
							<th scope="row"><?php echo date( 'D M d, Y g:ia T', $timestamp ); ?></th>
							<td>
								<?php echo $timestamp;
									if ( $event[ 'schedule' ] ) {
										echo $schedules [ $event[ 'schedule' ] ][ 'display' ]; 
									} else {
										?><em><?php _e('One-off event', 'cron-view'); ?></em><?php
									}
								?>
							</td>
							<td><?php if ( count( $event[ 'args' ] ) ) { ?>
								<ul>
									<?php
									foreach( $event[ 'args' ] as $key => $value ) {
										$post = get_post( $value );
										if( $post )
											echo '<li><a href="'. get_permalink( $post->ID ) .'">'. $post->post_title .'</a></li>';
									}
									?>
								</ul>
							<?php } ?></td>
						</tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</tbody>
	</table>
	
<?php

}

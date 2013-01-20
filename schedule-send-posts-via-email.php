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
add_action( 'sspe_email_videos',		'sspe_schedule_email' );

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
function sspe_schedule_email( $args ) {
	
	foreach( $args as $arg ) {
		error_log( $arg );
	}

	// Mailchimp API Key
	$api = new MCAPI( 'b3ffb03fa6953923dba0abca020cb3dd-us1' );
			
	// Get the main post title	
	$title				= get_the_title( $args['post1'] );

	// Regular campaign
	$type				= 'regular';
	
	// Campaign title
	$opts['title']		= 'Production Campaign';
		
	// List ID
	$opts['list_id']	= 'f9745d5dbe';
	
	// Email subject
	$opts['subject']	= 'Today\'s ReadyRosie Video - ' . $title;
	
	// From email address
	$opts['from_email']	= 'patrick@developdaly.com'; 
	
	// From name
	$opts['from_name']	= 'ReadyRosie';

	// To name
	$opts['to_name']	= '*|FNAME|*';
		
	// Tracking options
	$opts['tracking']	= array(
							'opens'			=> true,
							'html_clicks'	=> true,
							'text_clicks'	=> true
						);
	
	// Google Analytics ID
	$opts['analytics']	= array(
							'google' => 'UA-33622544-1'
						);
	
	// Email content
	$content = array(
		'html'			=> sspe_email_template_html( $args ),
		'text'			=> sspe_email_template_text( $args )
	);
	
	// Create the camppaign based on the options above
	$retval = $api->campaignCreate( $type, $opts, $content );
	
	// Email administrator if campaign errors out or send campaign
	if ($api->errorCode){
		$message = "\n\tCode=".$api->errorCode;
		$message .= "\n\tMsg=".$api->errorMessage."\n";
		wp_mail( get_option('admin_email'), 'Unable to Send Campaign!', $message );
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
	$sspe_mailchimp_api_key_field	= 'sspe_mailchimp_api_key';

	$sspe_mailchimp_api_key_val = get_option( $sspe_mailchimp_api_key_field );

    // See if the user has posted some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $sspe_hidden ]) && $_POST[ $sspe_hidden ] == 'Y' ) {

		// Define arguments
	    $sspe_args = array(
			$sspe_post1					=> $_POST[ $sspe_post1 ],
			$sspe_post2					=> $_POST[ $sspe_post2 ],
			$sspe_post3					=> $_POST[ $sspe_post3 ],
			$sspe_timestamp				=> strtotime( $_POST[ $sspe_timestamp ] )
		);
		
		// Save settings
        update_option( $sspe_mailchimp_api_key_field, $_POST[ 'sspe_mailchimp_api_key'] );				
		
		// Schedule event
		wp_schedule_single_event( $sspe_timestamp, 'sspe_email_videos', $sspe_args );
		
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
			
				<?php $posts = get_posts( array( 'post_per_page' => -1 ) ); ?>
			
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_post1; ?>">"Today's Video"</label></th>
							<td>
								<select name="<?php echo $sspe_post1; ?>" data-placeholder="Pick Today's Video..." style="width:350px;" class="chzn-select">
				
									<option></option>
									<?php foreach( $posts as $post ): ?>
									<option value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
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
									<option value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
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
									<option value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
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
				
<pre>
<?php print_r( $_POST ); ?>
</pre>

				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $sspe_mailchimp_api_key_field; ?>">Mailchimp API Key</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $sspe_mailchimp_api_key_field; ?>" value="<?php echo get_option( $sspe_mailchimp_api_key_field ); ?>">
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

	<h3><?php _e('Schduled Emails', 'cron-view'); ?></h3>

	<table class="widefat fixed">
		<thead>
			<tr>
				<th scope="col"><?php _e('Date/time scheduled ('. get_option( 'timezone_string' ) .')', 'cron-view'); ?></th>
				<th scope="col"><?php _e('Schedule', 'cron-view'); ?></th>
				<th scope="col"><?php _e('Posts', 'cron-view'); ?></th>
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
							<th scope="row"><?php echo date( 'D M d, Y g:ia', $timestamp ); ?></th>
							<td>
								<?php 
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
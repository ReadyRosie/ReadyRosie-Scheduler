<?php
/*
Plugin Name: ReadyRosie Scheduler
Description: Create video package and schedule emails to Mailchimp lists
Author: developdaly
Version: 2.0
Author URI: http://developdaly.com/

  This plugin is released under version 3 of the GPL:
  http://www.opensource.org/licenses/gpl-3.0.html
*/
error_log( 'loaded' );
require_once( WP_PLUGIN_DIR .'/readyrosie-scheduler/inc/MCAPI.class.php' );
require_once( WP_PLUGIN_DIR .'/readyrosie-scheduler/inc/config.inc.php' );
require_once( WP_PLUGIN_DIR .'/readyrosie-scheduler/email.php' );

add_action( 'init',						'rrs_register_package' );
add_action( 'admin_menu',				'rrs_add_menu' );
add_action( 'admin_enqueue_scripts',	'rrs_enqueue_scripts' );
add_action( 'rrs_email_videos',			'rrs_schedule_email', 10, 3 );

date_default_timezone_set( get_option( 'timezone_string' ) );

function rrs_register_package() {

    $labels = array( 
        'name' => _x( 'Packages', 'package' ),
        'singular_name' => _x( 'Package', 'package' ),
        'add_new' => _x( 'Add New', 'package' ),
        'add_new_item' => _x( 'Add New Package', 'package' ),
        'edit_item' => _x( 'Edit Package', 'package' ),
        'new_item' => _x( 'New Package', 'package' ),
        'view_item' => _x( 'View Package', 'package' ),
        'search_items' => _x( 'Search Packages', 'package' ),
        'not_found' => _x( 'No packages found', 'package' ),
        'not_found_in_trash' => _x( 'No packages found in Trash', 'package' ),
        'parent_item_colon' => _x( 'Parent Package:', 'package' ),
        'menu_name' => _x( 'Packages', 'package' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        
        'supports' => array( 'title' ),
        
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type( 'package', $args );
}

// Add page as submenu to Tools
function rrs_add_menu() {
	add_menu_page( 'ReadyRosie Scheduler', 'Scheduler', 'update_core', 'readyrosie-scheduler/readyrosie-scheduler.php', 'rrs_add_menu_page_callback' );
}

// Load scripts and styles
function rrs_enqueue_scripts($hook) {
	wp_enqueue_style( 'chosen',					plugins_url( '/assets/chosen.css', __FILE__ ) );
  	wp_enqueue_style( 'jquery-ui-datepicker',	plugins_url( '/assets/jquery-ui-1.9.2.custom.min.css', __FILE__ ) );

    wp_enqueue_script( 'chosen',				plugins_url( '/assets/jquery.chosen.min.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'jquery-ui-timepicker',	plugins_url( '/assets/jquery.timepicker.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ) );
    wp_enqueue_script( 'app',					plugins_url( '/assets/app.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-timepicker' ) );
}

// Sets up the action to run when the scheduled event fires (i.e. email users)
function rrs_schedule_email( $rrs_mailchimp_list_id_field, $rrs_args ) {
	error_log( 'running rrs_schedule_email' );
	foreach( $rrs_args as $rrs_arg ) {
		error_log( $rrs_arg );
	}

	if( $rrs_args['rrs_package'] ):
	
		foreach( $rrs_days as $post ): // variable must be called $post (IMPORTANT)

			setup_postdata( $post );
			$video_main		= get_field( 'video_main' );
			$video_spanish	= get_field( 'spanish_main' );
			$video_expert	= get_field( 'expert_main' );
		
				
			$rrs_options = get_option( 'rrs_settings' );
		
			// Mailchimp API Key
			$api = new MCAPI( $rrs_options['rrs_mailchimp_api_key'] );
					
			// Get the main post title	
			$title				= get_the_title( $video_main );
		
			// Regular campaign
			$type				= 'regular';
			
			// Campaign title
			$opts['title']		= $rrs_options['rrs_mailchimp_campaign_title'];
				
			// List ID
			$opts['list_id']	= $list_id;
			
			// Email subject
			$opts['subject']	= $rrs_options['rrs_mailchimp_email_subject'] .' - ' . $title;
			
			// From email address
			$opts['from_email']	= $rrs_options['rrs_mailchimp_from_email']; 
			
			// From name
			$opts['from_name']	= $rrs_options['rrs_mailchimp_from_name'];
		
			// To name
			$opts['to_name']	= $rrs_options['rrs_mailchimp_to_name'];
				
			// Tracking options
			$opts['tracking']	= array(
									'opens'			=> true,
									'html_clicks'	=> true,
									'text_clicks'	=> true
								);
			
			// Google Analytics ID
			$opts['analytics']	= array(
									'google' => $rrs_options['rrs_mailchimp_google_analytics']
								);
			
			// Email content
			$content = array(
				'html'			=> rrs_email_template_html( $video_main, $video_spanish, $video_expert ),
				'text'			=> rrs_email_template_text( $video_main, $video_spanish, $video_expert )
			);
			
			// Don't segment the campaign
			$segment_opts = false;
			
			// Set the days to send on
			$type_opts			= array(
									'days' => $rrs_args['rrs_days']
								);
			
			// Create the camppaign based on the options above
			$retval = $api->campaignCreate( $type, $opts, $content, $segment_opts, $type_opts );
				
			// Email administrator if campaign errors out or send campaign
			if ($api->errorCode){
				$message = "\n\tCode=".$api->errorCode;
				$message .= "\n\tMsg=".$api->errorMessage."\n";
				error_log( '[ReadyRosie Scheduler Plugin] '. $message );
			} else {
				$api->campaignSendNow( $retval );
			}
			
		endforeach;
		
		wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly
	
	endif;
		
}

// Display the admin page
function rrs_add_menu_page_callback() {

	// Must be Editor to access the settings page.
    if ( !current_user_can( 'edit_posts' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
	$rrs_options = get_option( 'rrs_settings' );
	$api = new MCAPI( $rrs_options['rrs_mailchimp_api_key'] );

    // variables for the field and option names
	$rrs_days[]		= 'rrs_days';
	$rrs_package	= 'rrs_package';
	$rrs_hidden		= 'rrs_hidden';
	$rrs_timestamp	= 'rrs_timestamp';
	$rrs_mailchimp_api_key_field			= 'rrs_mailchimp_api_key';
	$rrs_mailchimp_campaign_title_field		= 'rrs_mailchimp_campaign_title';
	$rrs_mailchimp_list_id_field			= 'rrs_mailchimp_list_id';
	$rrs_mailchimp_email_subject_field		= 'rrs_mailchimp_email_subject';
	$rrs_mailchimp_from_email_field			= 'rrs_mailchimp_from_email';
	$rrs_mailchimp_from_name_field			= 'rrs_mailchimp_from_name';
	$rrs_mailchimp_to_name_field			= 'rrs_mailchimp_to_name';
	$rrs_mailchimp_google_analytics_field	= 'rrs_mailchimp_google_analytics';
	
$keys = array_keys($_POST);
 
foreach( $keys as $key => $value ) {
    //echo $key .' + '. $value . "<br/>";
	//print_r( $value );
	foreach( $key as $value ) {
		print_r( $value );
	}
}

    // See if the user has posted some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $rrs_hidden ]) && $_POST[ $rrs_hidden ] == 'Y' ) {

		$rrs_options = array(
			$rrs_mailchimp_api_key_field			=> stripslashes( $_POST[ $rrs_mailchimp_api_key_field ] ),
			$rrs_mailchimp_campaign_title_field		=> stripslashes( $_POST[ $rrs_mailchimp_campaign_title_field ] ),
			$rrs_mailchimp_email_subject_field		=> stripslashes( $_POST[ $rrs_mailchimp_email_subject_field ] ),
			$rrs_mailchimp_from_email_field			=> stripslashes( $_POST[ $rrs_mailchimp_from_email_field ] ),
			$rrs_mailchimp_from_name_field			=> stripslashes( $_POST[ $rrs_mailchimp_from_name_field ] ),
			$rrs_mailchimp_to_name_field			=> stripslashes( $_POST[ $rrs_mailchimp_to_name_field ] ),
			$rrs_mailchimp_google_analytics_field	=> stripslashes( $_POST[ $rrs_mailchimp_google_analytics_field ] )
		);

		// Save settings
        update_option( 'rrs_settings', $rrs_options );
			
		$rrs_days = array();
		
		// Define arguments
		$rrs_args = array(
			$_POST[ $rrs_package ],
			$_POST[ $rrs_mailchimp_list_id_field ],
			$_POST[ 'rrs_days' ]
		);
				
		// Schedule event
		if( !empty( $_POST[ $rrs_timestamp ] ) )
			wp_schedule_single_event( strtotime( $_POST[ $rrs_timestamp ] ), 'rrs_email_videos', $rrs_args );
		
	    // Put an settings updated message on the screen
		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'rrs'); ?></strong></p></div>
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
	
		<h2><?php echo __( 'ReadyRosie Scheduler', 'rrs' ) ?></h2>
		
		<form name="rrs" method="post" action="">
			
			<div style="float: left; width: 65%;">
			<?php print_r( $_POST ); ?>

				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_package; ?>">Package</label></th>
							<td>
								<?php
								$args = array( 'post_type' => 'package', 'numberposts' => -1 );
								$packages = get_posts( $args );
								if( $packages ) : ?>
									
									<select name="<?php echo $rrs_package; ?>" data-placeholder="Chose a package..." style="width:350px;" class="chzn-select">
										<option></option>
									<?php foreach ($packages as $post) : setup_postdata($post); ?> 
										<option id="<?php echo $post->ID; ?>">
											<?php echo $post->post_title; ?>   
										</option>
									<?php endforeach; ?>
									</select>
									
								<?php else : ?>
									
									You don't have any published packages. <a href="<?php echo site_url(); ?>/wp-admin/post-new.php?post_type=package">Add a package</a>
									
								<?php endif; ?>
							</td>							
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_mailchimp_list_id_field; ?>">Mailing List</label></th>
							<td>
								<?php if( !empty( $api ) ) { ?>
								<select name="<?php echo $rrs_mailchimp_list_id_field; ?>" data-placeholder="Chose a mailing list..." style="width:350px;" class="chzn-select">
									<option></option>
									<?php
								
									$api->lists();
								 
									$lists = $api->lists(); 
								
									if ($api->errorCode) {
										$message = "\n\tCode=".$api->errorCode;
										$message .= "\n\tMsg=".$api->errorMessage."\n";
										error_log( '[ReadyRosie Scheduler Plugin] '. $message );
									} else {
										foreach( $lists['data'] as $list ) {
											echo '<option value="'. $list['id'] .'">'. $list['name'] .'</option>';
										}
									}
									?>
								</select>
								<?php } else {
									echo '<p>Enter your Mailchimmp API key first &rarr;';
									}
								?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="rss_days[]">Days</label></th>
							<td>
								<label>S <input type="checkbox" name="rss_days[]" value="1"></label>
								<label>M <input type="checkbox" name="rss_days[]" value="2"></label>
								<label>T <input type="checkbox" name="rss_days[]" value="3"></label>
								<label>W <input type="checkbox" name="rss_days[]" value="4"></label>
								<label>T <input type="checkbox" name="rss_days[]" value="5"></label>
								<label>F <input type="checkbox" name="rss_days[]" value="6"></label>
								<label>S <input type="checkbox" name="rss_days[]" value="7"></label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_timestamp; ?>">Schedule</label></th>
							<td>
								<input id="<?php echo $rrs_timestamp; ?>" class="regular-text" type="text" placeholder="Choose a date..." name="<?php echo $rrs_timestamp; ?>">
							</td>
						</tr>
					</tbody>
				</table>
				
			</div>

			<div style="float: right; width: 35%;">

				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_mailchimp_api_key_field; ?>">Mailchimp API Key</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $rrs_mailchimp_api_key_field; ?>" value="<?php echo $rrs_options[$rrs_mailchimp_api_key_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_mailchimp_campaign_title_field; ?>">Campaign Title</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $rrs_mailchimp_campaign_title_field; ?>" value="<?php echo $rrs_options[$rrs_mailchimp_campaign_title_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_mailchimp_email_subject_field; ?>">Email Subject</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $rrs_mailchimp_email_subject_field; ?>" value="<?php echo $rrs_options[$rrs_mailchimp_email_subject_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_mailchimp_from_email_field; ?>">From Email Address</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $rrs_mailchimp_from_email_field; ?>" value="<?php echo $rrs_options[$rrs_mailchimp_from_email_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_mailchimp_from_name_field; ?>">From Name</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $rrs_mailchimp_from_name_field; ?>" value="<?php echo $rrs_options[$rrs_mailchimp_from_name_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_mailchimp_to_name_field; ?>">To Name</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $rrs_mailchimp_to_name_field; ?>" value="<?php echo $rrs_options[$rrs_mailchimp_to_name_field]; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="<?php echo $rrs_mailchimp_google_analytics_field; ?>">Google Analytics</label></th>
							<td>
								<input class="regular-text" type="text" name="<?php echo $rrs_mailchimp_google_analytics_field; ?>" value="<?php echo $rrs_options[$rrs_mailchimp_google_analytics_field]; ?>">
							</td>
						</tr>	
					</tbody>
				</table>

				<p class="submit">
					<input type="submit" name="save_settings" class="button" value="<?php esc_attr_e('Save Settings') ?>" />
				</p>
													
			</div>
						
			<div class="clear"></div>
						
			<input type="hidden" name="<?php echo $rrs_hidden; ?>" value="Y">
						
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
				<th scope="col"><?php _e('Date/time scheduled', 'rrs'); ?></th>
				<th scope="col"><?php _e('Schedule', 'rrs'); ?></th>
				<th scope="col"><?php _e('Mailing List', 'rrs'); ?></th>
				<th scope="col"><?php _e('Posts', 'rrs'); ?></th>
			</tr>
		</thead>
		<tbody>
			
			<?php foreach ( $cron as $timestamp => $cronhooks ) { ?>
				<?php foreach ( (array) $cronhooks as $hook => $events ) { $i = ''; ?>
					<?php foreach ( (array) $events as $event ) { $i++; ?>
						<?php if( $hook != 'rrs_email_videos' )
							continue;
							print_r( $event );
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
								<?php
								$api = new MCAPI( $rrs_options['rrs_mailchimp_api_key'] );
							
								$filters = array ('list_id' => $event[ 'args' ][3] );
								$api->lists($filters);
							 
								$lists = $api->lists($filters); 
							
								if ($api->errorCode) {
									$message = "\n\tCode=".$api->errorCode;
									$message .= "\n\tMsg=".$api->errorMessage."\n";
									error_log( '[ReadyRosie Scheduler Plugin] '. $message );
								} else {
									echo $lists['data'][0]['name'];
								}
								?>
							<?php } ?></td>
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
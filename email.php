<?php

function sspe_email_template_html( $post1_id, $post2_id, $post3_id ) {
	
	if( $post1_id )
		$post1 = get_post( $post1_id );
	if( $post2_id )
		$post2 = get_post( $post2_id );
	if( $post3_id )
		$post3 = get_post( $post3_id );

	$output = '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<title>Untitled Document</title>
			<style type="text/css">
				.red {
					color: #FF0000;
				}
				.red {
					color: #FF0000;
				}
			</style>
		</head>
	
		<body>
	
			<table align="center" bgcolor="#ffffff" width="600" border="0" cellspacing="0" cellpadding="0" style="padding:0px;font-size:18px;color:#333333;font-family:Verdana, Geneva, sans-serif;">
				<tr>
					<td><!--------- inner table 1 ----------><!--------- header ---------->
					<table width="600" border="0" cellspacing="0" cellpadding="0" style="margin:0px;padding:0px;">
						<tr>
							<td align="left" width="600" style="margin:0px;padding:0px;"><a href="http://www.readyrosie.com/" target="_blank"><img src="http://gallery.mailchimp.com/9ab3e06e1069c5d313b3ca1a9/images/logo.jpg" width="348" height="92" alt="Ready Rosie" title="Ready Rosie"></a></td>
						</tr>
					</table><!--------- inner table 2 ----------><!-------- body --------->
					<table width="600" border="0" cellspacing="0" cellpadding="0" style="margin:0px;padding:0px;">
						<tr>
							<td width="600" height="699" bgcolor="#333333" background="http://gallery.mailchimp.com/9ab3e06e1069c5d313b3ca1a9/images/bgChalkTall.jpg" style="margin:0px;padding:0px;">
							<table width="600" border="0" cellspacing="0" cellpadding="0" style="margin:0px;padding:0px;">
								<tr>
									<td width="600" height="30px" style="margin:0px; padding:0px; font-family: Verdana, Geneva, sans-serif;">&nbsp;</td>
								</tr>
							</table>
							<table width="600" border="0" cellspacing="0" cellpadding="0" style="margin:0px;padding:0px;">
								<tr>
									<td width="30" height="540" style="margin:0px;padding:0px;">&nbsp;</td>
									<td align="center" width="540" height="540" bgcolor="#ffffff" style="margin:0px;padding:0px;">
									<table align="center" width="540" border="0" cellspacing="10" cellpadding="0" style="margin:0px;padding:0px;">
										<tr>
											<td width="500" style="margin:0px;padding:0px;"><img src="http://gallery.mailchimp.com/9ab3e06e1069c5d313b3ca1a9/images/todaysVideo.png" width="183" height="17" alt="Today\'s Video" title="Today\'s Video">'. $post1->post_title .'</td>
										</tr>
										<tr>
											<td width="500" height="281" bgcolor="#cccccc" style="margin:0px;padding:0px;border:10px solid #333333;"><a href="'. get_permalink( $post1->ID ) .'" target="_blank"><img src="'. get_post_meta( $post1->ID, 'wpcf-video', true ) .'" width="500" height="281" alt="Ready Rosie: Daily Video" title="Ready Rosie: Daily Video"></a></td>
										</tr>
									</table>
									<table align="center" width="500" border="0" cellspacing="0" cellpadding="0">
										<tr>
											<td width="500" style="margin:0px; padding:0px; text-align: center; font-family: Verdana, Geneva, sans-serif;"><em> <strong>The Expert & your daily video in Spanish</strong></em></td>
										</tr>
									</table>
									<table align="center" width="500" border="0" cellspacing="10" cellpadding="0" style="margin:0px;padding:0px;">
										<tr>
											<td width="235" height="132" bgcolor="#cccccc" style="margin:0px;padding:0px;border:10px solid #333333;"><a href="'. get_permalink( $post2->ID ) .'" target="_blank"> <img src="'. get_post_meta( $post2->ID, 'wpcf-video', true ) .'" width="235" height="132" alt="The Expert" title="Expert Video"></a></td>
											<td width="235" height="132" bgcolor="#cccccc" style="margin:0px;padding:0px;border:10px solid #333333;"><a href="'. get_permalink( $post3->ID ) .'" target="_blank"><img src="'. get_post_meta( $post3->ID, 'wpcf-video', true ) .'" width="235" height="132" alt="Spanish Video" title="Spanish Video"></a></td>
										</tr>
									</table></td>
									<td width="30" height="540" style="margin:0px;padding:0px;">&nbsp;</td>
								</tr>
							</table>
							<table width="600" border="0" cellspacing="0" cellpadding="0" style="margin:0px;padding:0px;">
								<tr>
									<td width="540" align="center" style="margin:0px;padding:0px;font-size:12px;color:#ffffff;font-family:Verdana, Geneva, sans-serif;">
									<p>
										ReadyRosie is the first and only online school readiness curriculum, which makes high quality preK accessible to all.
									</p></td>
								</tr>
							</table></td>
						</tr>
					</table></td>
				</tr>
			</table>
	
			<center>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
				<table border="0" cellpadding="20" cellspacing="0" width="100%" style="background:#EEEEEE  !important; border-top:1px solid #DDDDDD; clear:both;" id="canspamBarWrapper">
					<tr>
						<td align="center" valign="top" style="padding-bottom:0;">
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="canspamBar">
							<tr>
								<td align="left" valign="top" style="color:#505050 !important;font-family:Verdana,Arial,Sans !important;font-size:11px !important;font-weight:normal !important;font-style:normal !important;text-decoration:none !important;vertical-align:top !important;text-align:left !important;"> Sent to *|EMAIL|* &mdash; <a href="*|ABOUT_LIST|*" style="font-family:Verdana,Arial,Sans !important;font-size:11px !important;font-weight:normal !important;font-style:normal !important;text-decoration:underline !important;color:#404040 !important;"><em>why did I get this?</em></a>
								<br>
								<a href="*|UNSUB|*" style="font-family:Verdana,Arial,Sans !important;font-size:11px !important;font-weight:normal !important;font-style:normal !important;text-decoration:none !important;color:#369 !important;">unsubscribe from this list</a> | <a href="*|UPDATE_PROFILE|*" style="font-family:Verdana,Arial,Sans !important;font-size:11px !important;font-weight:normal !important;font-style:normal !important;text-decoration:none !important;color:#369 !important;">update subscription preferences</a>
								<br>
								*|LIST:ADDRESSLINE|* </td>
								<td align="left" valign="top"> *|REWARDS|*
								<br>
								&nbsp; </td>
							</tr>
						</table></td>
					</tr>
				</table>
			</center>
		</body>
	</html>';

	return $output;
}

function sspe_email_template_text( $post1_id, $post2_id, $post3_id ) {
	
	if( $post1_id )
		$post1 = get_post( $post1_id );
	if( $post2_id )
		$post2 = get_post( $post2_id );
	if( $post3_id )
		$post3 = get_post( $post3_id );
		
	$output = $args .'
	[1]Ready Rosie
	  Links:
	    1. http://www.readyrosie.com
	
	
	[2]Today\'s Video '. get_permalink( $post1->ID ) .'
	[3]Dr. Jane Moore
	[4]Spanish Video 
	  Links:
	    2. '. $args['post1'] . get_permalink( $post1->ID ) .'
	    3. '. $args['post2'] . get_permalink( $post2->ID ) .'
	    4. '. $args['post3'] . get_permalink( $post3->ID ) .'
	
	ReadyRosie is the first and only online school readiness curriculum,
	which makes high quality preK accessible to all.
	
	Sent to *|EMAIL|* — [5]why did I get this?
	[6]unsubscribe from this list | [7]update subscription preferences
	*|LIST_ADDRESSLINE_TEXT|* *|REWARDS_TEXT|*
	  Links:
	    5. *|ABOUT_LIST|*
	    6. *|UNSUB|*
	    7. *|UPDATE_PROFILE|*';
		
	return $output;
}
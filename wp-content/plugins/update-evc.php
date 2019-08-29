<?php
/*
Plugin Name: Update EventCalendar 
Plugin URI: 
Description: Automatic Update Eventcalanedar for repating events
Author: Tobias Bielohlawek
Author URI: 
Version: 1.0

Copyright 2007  Austin Matzko  (email : if.website at gmail.com)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!class_exists('updateevc') ) {
      class updateevc  {
	     var $filename = __FILE__;
	     var $name = 'Update Event Calendar';
	      
      	     function updateevc() {
      	     	if (method_exists($this, '_install')) { register_activation_hook($this->filename, array($this, '_install')); }
		if (method_exists($this, '_uninstall')) { register_deactivation_hook($this->filename, array($this, '_uninstall')); }
      	     	add_action('wp_update_cron_daily', array(&$this, 'update'));
      	     }
      	     
      	     function _install() {
      	     		wp_schedule_event(time() + 60, 'daily', 'wp_update_cron_daily' );
      	     }
      	     	
      	     function _uninstall() {
      	     		remove_action('wp_update_cron_daily', 'wp_update_cron_daily');
      	     		wp_clear_scheduled_hook('wp_update_cron_daily');
      	     }
      	     
      	     function update()
      	     {
      	     	global $wpdb;
		
		$offset = 110;
	        $post_day = date('w'); #update last day
	        $posts = array( 9,8,4,5,6,7,3 ); #Sat,Sun,Mon,Tue,Wed,Thu,Fr
	        $post_id = $posts[$post_day] + $offset;
      	     	
		$date = mktime(2, 5, 0 ) + 60 * 60 * 24 * 6; #sec * min * hour * day 
		$date_s = date("Y-m-d H:i:s", $date );
		$query = "UPDATE wp_ec3_schedule SET start = '$date_s', end = '$date_s' WHERE post_id = $post_id";
		$wpdb->get_results( $query);
		if($post_day == 6 )
		{
			$date = mktime(20, 0, 0 ) + 60 * 60 * 24 * 4; #sec * min * hour * day
			$date_s = date("Y-m-d H:i:s", $date );
			$query = "UPDATE wp_ec3_schedule SET start = '$date_s', end = '$date_s' WHERE post_id = 234";
		         $wpdb->get_results( $query);
		}
		#mail( 'error@c-art-web.de', 'Updated', $query );
      	     }
	     
	     function throw_error($message) {
		print "\n<div style=\"color:#FF0000;\"><strong>Plugin Error in $this->name</strong><br />$message</div>\n";
	     return false;
	     }
	     
	      
	     
      }

}

$updateevc = new updateevc(); 	

function updateevc() {
	global $updateevc;
	print $updateevc->update();
}
?>

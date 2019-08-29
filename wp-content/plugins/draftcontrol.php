<?php
/*
Plugin Name: Draft Control
Plugin URI: http://www.kenvillines.com/archives/000071.html
Description: A simple and flexible Admin control for WordPress Drafts
Version: 1.2
Author: Ken Villines
Author URI: http://www.kenvillines.com/
License: GPL
Last modified: 2006-03-14 9:54am EDT
*/

# -- Don't change these unless you know what you're doing...
define ('RPC_MAGIC', 'tag:www.kenvillines.com/archives/000071.html');
define ('DRAFTCONTROL_VERSION', '1.2');

define ('USER_LEVEL', 1 );

// Is this being loaded from within WordPress 2.0 or later?
if (isset($wp_version) and $wp_version >= 2.0):

# Admin menu
add_action('admin_menu', 'dc_add_pages');

endif;


# -- Admin menu add-ons
function dc_add_pages () {
	add_management_page('Draft Control', 'Draft Control', USER_LEVEL, basename(__FILE__), 'dc_draft_manage_post');
} // function fwp_add_pages () */

function dc_draft_manage_post () {
	?>
	<script type="text/javascript">
	<!--
	function checkAllDelete(form)
	{
		uncheckAllPublish(form);
		
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox" &&
					form.elements[i].name == "deletecheck[]") {    
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
			}
		}
	}
	
	function checkAllPublish(form)
	{
		uncheckAllDelete(form);
		
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox" &&
					form.elements[i].name == "publishcheck[]") {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
			}
		}
	}
	
	function uncheckAllPublish(form)
	{
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox" &&
					form.elements[i].name == "publishcheck[]") 
			{
					form.elements[i].checked = false;
			}
		}
	}
	
	function uncheckAllDelete(form)
	{
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox" &&
					form.elements[i].name == "deletecheck[]") 
			{
					form.elements[i].checked = false;
			}
		}
	}
	
	function selectChangeOwner(theElement)
	{
		var theSelect;
		
		menu_id = theElement;
		
		if (!menu_id)
		{
			return false;
			
		}else
		{
				input_button = confirm("You are about to change this drafts owner.\n  \'Cancel\' to stop, \'OK\' to change.");
			
				if (input_button == true){
					var my_value = menu_id.value;
					var val_array = my_value.split(",");

					form_obj = document.getElementById('draft_posts');
					form_obj.action = "edit.php?page=draftcontrol.php&pagenum="+val_array[2]+"&changeowner=action&draftid="+val_array[0]+"&userid="+val_array[1]+"&postperpage="+val_array[3];
					form_obj.method = "POST";
					form_obj.submit(); 
				}
				
			}
		
		return true;
	}
	
	function selectChangeCat(theElement)
	{
		var theSelect;
		
		menu_id = theElement;
		
		if (!menu_id)
		{
			return false;
			
		}else
		{
				input_button = confirm("You are about to change this drafts Category.\n  \'Cancel\' to stop, \'OK\' to change.");
			
				if (input_button == true){
					var my_value = menu_id.value;
					var val_array = my_value.split(",");

					form_obj = document.getElementById('draft_posts');
					form_obj.action = "edit.php?page=draftcontrol.php&pagenum="+val_array[2]+"&changecat=action&draftid="+val_array[0]+"&catid="+val_array[1]+"&postperpage="+val_array[3];
					form_obj.method = "POST";
					form_obj.submit(); 
				}
				
			}
		
		return true;
	}
	
	//-->
	</script>
	<?php
	global $user_level, $wpdb;
	
	$cont = true;
	if (isset($_REQUEST['action']) || isset($_REQUEST['changeowner'])
			|| isset($_REQUEST['changecat'])):
		if ($_REQUEST['action'] == 'Delete Checked') : $cont = dc_multidelete_post();
		elseif ($_REQUEST['action'] == 'Deleteit') : $cont = dc_delete_post();
		elseif ($_REQUEST['action'] == 'Publish Checked') : $cont = dc_multipublish_post();
		elseif ($_REQUEST['action'] == 'Publishit') : $cont = dc_publish_post();
		elseif ($_REQUEST['changeowner'] == 'action'
						&& isset($_REQUEST['draftid'])
						&& isset($_REQUEST['userid'])) : $cont = dc_change_owner();
		elseif ($_REQUEST['changecat'] == 'action'
						&& isset($_REQUEST['draftid'])
						&& isset($_REQUEST['catid'])) : $cont = dc_change_cat();
		endif;
	endif;
	
	if ($cont){
		// how many rows to show per page
		$posts_per_page = 20;
		
		// if $_REQUEST['$posts_per_page'] defined
		if(isset($_REQUEST['postperpage']))
		{
			$posts_per_page = $_REQUEST['postperpage'];
		}
		
		// Default: Show first page
		$page_num = 1;
		
		// if $_REQUEST['page'] defined, use it as page number
		if(isset($_REQUEST['pagenum']))
		{
			$page_num = $_REQUEST['pagenum'];
		}
		
		// counting the offset
		$offset = ($page_num - 1) * $posts_per_page;
		
		$query   =	"SELECT $wpdb->posts.* FROM $wpdb->posts".
					" WHERE $wpdb->posts.post_status = 'draft' ORDER BY post_date DESC".
					" LIMIT $offset, $posts_per_page";
					
		$draft_posts = $wpdb->get_results($query);
		
		$draftcount = count($draft_posts);
		
		// Deleting or publishing all drafts on final page
		// This code handles moving to the new last page
		if( ((0 == $draftcount) && ($_REQUEST['action'] == 'Delete Checked'))
			|| ((0 == $draftcount) && ($_REQUEST['action'] == 'Publish Checked')) )
		{
			
			if($page_num != 1)
				$page_num = $page_num - 1;
			
			// counting the offset
			$offset = ($page_num - 1) * $posts_per_page;
			
			$query   =	"SELECT $wpdb->posts.* FROM $wpdb->posts".
						" WHERE $wpdb->posts.post_status = 'draft' ORDER BY post_date DESC".
						" LIMIT $offset, $posts_per_page";
						
			$draft_posts = $wpdb->get_results($query);
			
			$draftcount = count($draft_posts);
		}
		
		$query   =	"SELECT COUNT($wpdb->posts.ID) FROM $wpdb->posts ".
		"WHERE $wpdb->posts.post_status = 'draft' ORDER BY post_date DESC";
		
		$total_count = $wpdb->get_var($query);
		
		_e('<div class="wrap" style="margin:20px;">');
		?>
	<h2>Your Unpublished Posts (<?php _e($draftcount)?> of <?php _e($total_count)?>):</h2>
	<form name="viewnum" action="edit.php?page=<?=basename(__FILE__)?>" method="post" style="width: 20em; margin-bottom: 1em;">
		<input type="hidden" name="pagenum" value="<?=$page_num?>">
		<input type="hidden" name="postperpage" value="<?=$posts_per_page?>">
		<fieldset>
		<legend>Drafts to Show...</legend>
	  <select name="postperpage">
	  <option value="05" <?php if($draftcount==5) echo"selected"; ?>>05</option>
		<option value="10" <?php if($draftcount==10) echo"selected"; ?>>10</option>
		<option value="15" <?php if($draftcount==15) echo"selected"; ?>>15</option>
		<option value="20" <?php if($draftcount==20) echo"selected"; ?>>20</option>
		<option value="25" <?php if($draftcount==25) echo"selected"; ?>>25</option>
		<option value="30" <?php if($draftcount==30) echo"selected"; ?>>30</option>
		<option value="40" <?php if($draftcount==40) echo"selected"; ?>>40</option>
		<option value="50" <?php if($draftcount==50) echo"selected"; ?>>50</option>
		<option value="60" <?php if($draftcount==60) echo"selected"; ?>>60</option>
		<option value="80" <?php if($draftcount==80) echo"selected"; ?>>80</option>
		<option value="100" <?php if($draftcount==100) echo"selected"; ?>>100</option>
		</select>
			<input name="submit" value="Set View" type="submit"> 
		</fieldset>
		</form>
				<?php
			$query   =	"SELECT COUNT($wpdb->posts.ID) FROM $wpdb->posts ".
			"WHERE $wpdb->posts.post_status = 'draft' ORDER BY post_date";
				
			$draft_count = $wpdb->get_var($query);
			
			$page_max = ceil ($draft_count/$posts_per_page);
			
			// print the link to access each page
			$self = "edit.php?page=draftcontrol.php";
			$nav  = '';
			
			 for($page = 1; $page <= $page_max; $page++)
			{
			   if ($page == $page_num)
			   {
				  $nav .= " $page "; // no need to create a link to current page
			   }
			   else
			   {
				  $nav .= " <a href=\"$self&pagenum=$page&postperpage=$posts_per_page\">$page</a> ";
			   }
			}	
			
			if ($page_max > 1)
			{
			   $page  = $page_num - 1;
			   $prev  = " <a href=\"$self&pagenum=$page&postperpage=$posts_per_page\">[Prev]</a> ";
			
			   $first = " <a href=\"$self&pagenum=1&postperpage=$posts_per_page\">[First Page]</a> ";
			}
			else
			{
			   $prev  = '&nbsp;'; // we're on page one, don't print previous link
			   $first = '&nbsp;'; // nor the first page link
			}
			
			if ($page_num < $page_max)
			{
			   $page = $page_num + 1;
			   $next = " <a href=\"$self&pagenum=$page&postperpage=$posts_per_page\">[Next]</a> ";
			
			   $last = " <a href=\"$self&pagenum=$page_max&postperpage=$posts_per_page\">[Last Page]</a> ";
			}
			else
			{
			   $next = '&nbsp;'; // we're on the last page, don't print next link
			   $last = '&nbsp;'; // nor the last page link
			}
			
			//if we only have 1 page then we don't show navigation at the top
			if ($page_max > 1){
				// print the navigation link
				echo '<div align="center" style="padding:5px 20px 5px 20px;">'.$first . $prev . $nav . $next . $last.'</div>';
			}
		
		?>
	<form name="draft_posts" id="draft_posts" action="edit.php?page=<?=basename(__FILE__)?>" method="post">
		<?php 
		$alt_row = true;
		if ($draft_posts){  ?>
<br>
    <table width="100%" cellpadding="3" cellspacing="3">
		<tr>
		<th scope="col" width="4%"><?php _e('Post ID') ?></th>
		<th scope="col" width="30%"><?php _e('Title') ?></th>
		<th scope="col" width="10%"><?php _e('Categories') ?></th>
		<th scope="col" width="10%"><?php _e('Author') ?></th>
		<th scope="col" width="2%"><?php _e('Edit') ?></th>
		<th scope="col" colspan="2" width="8%"><?php _e('Publish') ?></th>
		<th scope="col" colspan="2" width="8%"><?php _e('Delete') ?></th>
		</tr>
    <?php
			$i = 0;
			foreach ($draft_posts as $draft) {start_wp();
					$alt_row = !$alt_row;
					$class = ($alt_row? ' class="alternate"':'');
					
					if (0 != $i)
					echo '<tr'.$class.'">';
					$draft->post_title = stripslashes($draft->post_title);
					if ($draft->post_title == '')
						$draft->post_title = sprintf(__('Post #%s'), $draft->ID);
					?>
					<td scope="row"><?=$draft->ID?></td>
					<td><?=$draft->post_title?></td>
					<td>
					<?php if ($user_level > USER_LEVEL && $cats = $wpdb->get_results("SELECT cat_ID, cat_name FROM $wpdb->categories") ) : ?>
						<select name="post_cat_override" id="post_cat_override" onchange="javascript:selectChangeCat(this);">
						<?php 
						foreach ($cats as $o) :
							if ( $draft->post_category == $o->cat_ID) $selected = 'selected="selected"';
							else $selected = '';
							echo "<option value='$draft->ID,$o->cat_ID,$page_num,$posts_per_page' $selected>$o->cat_name</option>";
						endforeach;
						?>
						</select>
					<?php endif; ?>
					</td>
					<td>
					<?php if ($user_level > USER_LEVEL && $users = $wpdb->get_results("SELECT ID, user_login FROM $wpdb->users") ) : ?>
						
						<select name="post_author_override" id="post_author_override" onchange="javascript:selectChangeOwner(this);">
						<?php 
						foreach ($users as $o) :
							$user_info = get_userdata($o->ID);
							
							if( $user_info->user_level > 0 ){ //$user_info->user_level <= $user_level &&
								$usersfname = $wpdb->get_var("SELECT meta_value FROM $wpdb->usermeta WHERE user_id = $o->ID AND meta_key = 'first_name'");
								$userslname = $wpdb->get_var("SELECT meta_value FROM $wpdb->usermeta WHERE user_id = $o->ID AND meta_key = 'last_name'");
								
								if ( $draft->post_author == $o->ID || ( empty($draft->post_author) && $user_ID == $o->ID ) ) $selected = 'selected="selected"';
								else $selected = '';
								echo "<option value='$draft->ID,$o->ID,$page_num,$posts_per_page' $selected>$o->user_login (".$usersfname." ".$userslname.")</option>";
							}
						endforeach;
						?>
						</select>
					<?php endif; ?>		
					</td> 
					<td><?php echo "<a href='post.php?action=edit&amp;post=$draft->ID' title='" . __('Edit this draft') . "'>Edit</a>"; ?></td>
					<td><a href="edit.php?page=<?=basename(__FILE__)?>&amp;draftpost_id=<?=$draft->ID?>&amp;action=Publishit&amp;pagenum=<?=$page_num?>&amp;postperpage=<?=$posts_per_page?>" onclick="return confirm('You are about to publish this post.\n  \'Cancel\' to stop, \'OK\' to publish.');" class="delete"><?php _e('Publish'); ?></a></td>
					<td><input type="checkbox" name="publishcheck[]" value="<?=$draft->ID?>" onClick="uncheckAllDelete(document.getElementById('draft_posts'))" /></td>
					<td><a href="edit.php?page=<?=basename(__FILE__)?>&amp;draftpost_id=<?=$draft->ID?>&amp;action=Deleteit&amp;pagenum=<?=$page_num?>&amp;postperpage=<?=$posts_per_page?>" onclick="return confirm('You are about to delete this post.\n  \'Cancel\' to stop, \'OK\' to delete.');" class="delete"><?php _e('Delete'); ?></a></td>
					<td><input type="checkbox" name="deletecheck[]" value="<?=$draft->ID?>" onClick="uncheckAllPublish(document.getElementById('draft_posts'))" /></td>
					<?php 
					++$i;
					echo '</tr> ';
			}
		?> 
<?php }else
		{ ?>
		  <tr style='background-color: <?php echo $bgcolor; ?>'> 
			<td colspan="8"><?php _e('No posts found.') ?></td> 
		  </tr> 
		<?php
		}
	} 
	echo '</table>';
	
	$query   =	"SELECT COUNT($wpdb->posts.ID) FROM $wpdb->posts ".
	"WHERE $wpdb->posts.post_status = 'draft' ORDER BY post_date";
		
	$draft_count = $wpdb->get_var($query);
	
	$page_max = ceil ($draft_count/$posts_per_page);
	
	// print the link to access each page
	$self = "edit.php?page=draftcontrol.php";
	$nav  = '';
	
	 for($page = 1; $page <= $page_max; $page++)
	{
	   if ($page == $page_num)
	   {
		  $nav .= " $page "; // no need to create a link to current page
	   }
	   else
	   {
		  $nav .= " <a href=\"$self&pagenum=$page&postperpage=$posts_per_page\">$page</a> ";
	   }
	}	
	
	if ($page_max > 1)
	{
	   $page  = $page_num - 1;
	   $prev  = " <a href=\"$self&pagenum=$page&postperpage=$posts_per_page\">[Prev]</a> ";
	
	   $first = " <a href=\"$self&pagenum=1&postperpage=$posts_per_page\">[First Page]</a> ";
	}
	else
	{
	   $prev  = '&nbsp;'; // we're on page one, don't print previous link
	   $first = '&nbsp;'; // nor the first page link
	}
	
	if ($page_num < $page_max)
	{
	   $page = $page_num + 1;
	   $next = " <a href=\"$self&pagenum=$page&postperpage=$posts_per_page\">[Next]</a> ";
	
	   $last = " <a href=\"$self&pagenum=$page_max&postperpage=$posts_per_page\">[Last Page]</a> ";
	}
	else
	{
	   $next = '&nbsp;'; // we're on the last page, don't print next link
	   $last = '&nbsp;'; // nor the last page link
	}
	
	// print the navigation link
	echo '<div align="center" style="padding:20px;">'.$first . $prev . $nav . $next . $last.'</div>';
	
	echo "</div>"; 
	?>
	<div class="wrap" style="margin:20px;">
	<h2>Manage Multiple Draft Posts</h2>
	<div class="submit" style="width: 100%; right:20px;padding:10px 0 10px 0;">
		<table width="300px" cellpadding="0" cellspacing="0" style="right:20px;">
		<tr>
		<td align="right">
		<input type="hidden" name="pagenum" value="<?=$page_num?>">
		<input type="hidden" name="postperpage" value="<?=$posts_per_page?>">
		<input type="submit" class="delete" name="action" value="Publish Checked" />
		</td>
		<td align="right"><input type="submit" class="delete" name="action" value="Delete Checked" /></td>
		</tr><tr>
		<td align="right">
		<a href="#" onclick="checkAllPublish(document.getElementById('draft_posts')); return false; "><?php _e('Toggle Publish Checkboxes') ?></a>
	  </td><td align="right">      
	 	<a href="#" onclick="checkAllDelete(document.getElementById('draft_posts')); return false; "><?php _e('Toggle Delete Checkboxes') ?></a>
		</td></tr></table>
	</div>
	</div>
</form>
	<?php
}

function dc_publish_post(){
	global $wpdb, $user_level;
	check_admin_referer();
	if ($user_level < USER_LEVEL):
		die (__("Cheatin' uh ?"));
	else:
		if (isset($_REQUEST['draftpost_id']) and ($_REQUEST['draftpost_id']!= 0)):
			$draft_id = $_REQUEST['draftpost_id'];
			
			// First, get all of the original fields
			$post = wp_get_single_post($draft_id, ARRAY_A);
			
			if('0000-00-00 00:00:00' == $post['post_date']):
				$timestamp = current_time('mysql');
				$timestamp_gmt = current_time('mysql',1);
			else:
				$timestamp = $post['post_date'];
				$timestamp_gmt = $post['post_date_gmt'];
			endif;
		
			$result = $wpdb->query("UPDATE $wpdb->posts 
					SET post_status = 'publish',
					post_date = '".$timestamp."',
					post_date_gmt = '".$timestamp_gmt."',
						post_modified = '".current_time('mysql')."',
						post_modified_gmt = '".current_time('mysql',1)."' 
					WHERE ID = '$draft_id'");
	
			if ($result):
				$mesg = "The Draft was published from list.";
			else:
				$mesg = "There was a problem publishing the draft from the list. [SQL: ".mysql_error()."]";
			endif;
		else:
			$mesg = "There was a problem publishing the draft from the list. [SQL: ".mysql_error()."]";
		endif;
		echo "<div class=\"updated\"><br />$mesg<br /><br /></div>\n";
	endif;
	return true;
}

function dc_multipublish_post() {
	global $wpdb, $user_level;
	check_admin_referer();
	if ($user_level < USER_LEVEL):
		die (__("Cheatin' uh ?"));
	else:
		if($_REQUEST['publishcheck']):
			
			$arrPub = $_REQUEST['publishcheck'];
			$resCount = 0;
			
			foreach ($arrPub as $checkval) {
				
				// First, get all of the original fields
				$post = wp_get_single_post($checkval, ARRAY_A);
				
				if('0000-00-00 00:00:00' == $post['post_date']):
					$timestamp = current_time('mysql');
					$timestamp_gmt = current_time('mysql',1);
				else:
					$timestamp = $post['post_date'];
					$timestamp_gmt = $post['post_date_gmt'];
				endif;
				
   			$result[$resCount] = $wpdb->query("UPDATE $wpdb->posts 
							SET post_status = 'publish',
							post_date = '".$timestamp."',
							post_date_gmt = '".$timestamp_gmt."',
								post_modified = '".current_time('mysql')."',
								post_modified_gmt = '".current_time('mysql',1)."' 
							WHERE ID = '$checkval'");
   			$resCount = $resCount+1;
			}
			
			foreach ($result as $resultval) {
   			if ($resultval):
					$errornum = 0;
				else:
					$errornum = 49;
					break;
				endif;
			}
	
			if (!$errornum):
				$mesg = "Selected Drafts published from list.";
			else:
				$mesg = "There was a problem publishing the drafts from the list. [SQL: ".mysql_error()."]";
			endif;
		else:
			$mesg = "No drafts were selected to be published.";
		endif;
		
		echo "<div class=\"updated\"><br />$mesg<br /><br /></div>\n";
	endif;
	return true;
}

function dc_delete_post(){
	global $wpdb, $user_level;
	check_admin_referer();
	if ($user_level < USER_LEVEL):
		die (__("Cheatin' uh ?"));
	else:
		if (isset($_REQUEST['draftpost_id']) and ($_REQUEST['draftpost_id']!=0)):
			$draft_id = $_REQUEST['draftpost_id'];
		
			$result = wp_delete_post($draft_id);
	
			if ($result):
				$mesg = "The Draft was deleted from list.";
			else:
				$mesg = "There was a problem deleting the draft from the list. [SQL: ".mysql_error()."]";
			endif;
		else:
			$mesg = "There was a problem deleting the draft from the list. [SQL: ".mysql_error()."]";
		endif;
		echo "<div class=\"updated\"><br />$mesg<br /><br /></div>\n";
	endif;
	return true;
}

function dc_multidelete_post () {
	global $wpdb, $user_level;
	check_admin_referer();
	if ($user_level < USER_LEVEL):
		die (__("Cheatin' uh ?"));
	else:
		if($_REQUEST['deletecheck']):
			$draft_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE ID IN (".implode(',',$_REQUEST['deletecheck']).")");

			if ($draft_ids) {
				foreach ($draft_ids as $draft_id){
					$result = wp_delete_post($draft_id);
				}
				$mesg = "Drafts deleted from list.";
			}else
			{
				$mesg = "There was a problem deleting the drafts from the list. [SQL: ".mysql_error()."]";
			}
		else:
			$mesg = "No drafts were selected to be deleted.";
		endif;
		
		echo "<div class=\"updated\"><br />$mesg<br /><br /></div>\n";
	endif;
	return true;
}

function dc_change_owner(){
	global $wpdb, $user_level;
	check_admin_referer();
	if ($user_level < USER_LEVEL):
		die (__("Cheatin' uh ?"));
	else:
		if (isset($_REQUEST['draftid']) and ($_REQUEST['draftid']!= 0)
		&& isset($_REQUEST['userid']) and ($_REQUEST['userid']!= 0)):
			$draft_id = $_REQUEST['draftid'];
			$user_id = $_REQUEST['userid'];
			
			$result = $wpdb->query("UPDATE $wpdb->posts SET post_author = '$user_id' WHERE ID = '$draft_id'");
	
			if ($result):
				$mesg = "The drafts owner was updated.";
			else:
				$mesg = "There was a problem updating the drafts owner. [SQL: ".mysql_error()."]";
			endif;
		else:
			$mesg = "There was a problem updating the drafts owner. [SQL: ".mysql_error()."] PostID:".$draft_id;
		endif;
		echo "<div class=\"updated\"><br />$mesg<br /><br /></div>\n";
	endif;
	return true;
}

function dc_change_cat(){
	global $wpdb, $user_level;
	check_admin_referer();
	if ($user_level < USER_LEVEL):
		die (__("Cheatin' uh ?"));
	else:
		if (isset($_REQUEST['draftid']) and ($_REQUEST['draftid']!= 0)
		&& isset($_REQUEST['catid']) and ($_REQUEST['catid']!= 0)):
			$draft_id = $_REQUEST['draftid'];
			$cat_id = $_REQUEST['catid'];
			
			$result = $wpdb->query("UPDATE $wpdb->posts SET post_category = '$cat_id' WHERE ID = '$draft_id'");
			
			if(0 <= $wpdb->get_var("SELECT COUNT( * )FROM `wp_post2cat` WHERE `post_id` = $draft_id AND `category_id` = $cat_id")):
					$result2 = $wpdb->query("INSERT INTO $wpdb->post2cat (post_id, category_id)VALUES ($draft_id, $cat_id)");
			else:
					$result2 = 1;
			endif;
	
			if ($result && $result2):
				$mesg = "The drafts category was updated.";
			else:
				$mesg = "There was a problem updating the drafts category. [SQL: ".mysql_error()."]";
			endif;
		else:
			$mesg = "There was a problem updating the drafts category. [SQL: ".mysql_error()."] PostID:".$draft_id;
		endif;
		echo "<div class=\"updated\"><br />$mesg<br /><br /></div>\n";
	endif;
	return true;
}
?>
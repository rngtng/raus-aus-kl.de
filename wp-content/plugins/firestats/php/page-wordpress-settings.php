<script type="text/javascript">
//<![CDATA[

function saveExcludedUsers()
{
    var selected = $F('excluded_users_table');
    var params = 'action=' + 'saveExcludedUsers' + '&list=' +selected;
    sendRequest(params);
}

function saveWpSiteID()
{
	saveWpOption('wp_site_id', 'firestats_site_id','positive_num');
}

//]]>
</script>

<?php
	if (!defined('FS_WORDPRESS_PLUGIN_VER') || FS_WORDPRESS_PLUGIN_VER != 3)
	{
		echo sprintf("Incorrect version of %s detected, you need to update it (did you upgrade FireStats and forgot to copy %s to the WordPress plugins directory?)","firestats-wordpress.php","firestats-wordpress.php");
		return;
	}
?>
<div id="wordpress_config_area" class="configuration_area">
    <table class="config_table">
	<tr>
            <td class="config_cell" colspan="2">
                <h3><?php fs_e('Statistics widget')?></h3>
					<?php
						$href = null;
						if (class_exists('K2SBM'))
						{
							$href = sprintf("<a href='themes.php?page=k2-sbm-modules'>%s</a>",fs_r('here'));
						}
						else
						if (function_exists('register_sidebar_widget'))
						{
				    	    $href = sprintf("<a href='themes.php?page=widgets/widgets.php'>%s</a>",fs_r('here'));
						}
						
						if ($href != null)
						{
							echo sprintf(fs_r('You can configure the sidebar widget from %s'),$href);
						}
						else
						{
							echo  sprintf(fs_r("The statistics widget requires the %s plugin for optimal usage."),
										 sprintf("<a href='%s'>%s</a>",
										 			"http://automattic.com/code/widgets/",
													fs_r("Widgets")));
							echo "<br/>";			 
							echo fs_r("You can also manually add the following code to your theme sidebar:")."<br/>";
							echo "<b>".htmlentities("<?php echo fs_get_stats_box();?>")."</b>";
						}
					?>
            </td>
        </tr>
        <tr>
            <td class="config_cell" colspan="2">
                <h3><?php fs_e('Comments icons')?></h3>

					<?php
						$comment_flags = get_option('firestats_add_comment_flag') == 'true' ? "checked" : "";
					?>
					<input type="checkbox"
							onclick="saveWpOption('enable_comment_flags','firestats_add_comment_flag','boolean')"
							id="enable_comment_flags" <?php echo $comment_flags?>/>
					<?php fs_e('Add flag icon to comments')?><br/>

   					<?php
						$comment_browser_os = get_option('firestats_add_comment_browser_os') == 'true' ? "checked" : "";
					?>
					<input type="checkbox"
							onclick="saveWpOption('enable_comment_browser_os','firestats_add_comment_browser_os','boolean')"
							id="enable_comment_browser_os" <?php echo $comment_browser_os?>/>

					<?php fs_e('Add browser and operating system icons to comments')?><br/>
         </td>
        </tr>
        <tr>
            <td class="config_cell" colspan="2">
                <h3><?php fs_e('Blog footer')?></h3>
   					<?php
						$add_footer = get_option('firestats_show_footer') != 'false' ? "checked" : "";
					?>
					<input type="checkbox"
							onclick="saveWpOption('show_footer','firestats_show_footer','boolean')"
							id="show_footer" <?php echo $add_footer?>/>

					<?php fs_e('Add FireStats footer to blog')?><br/>

   					<?php
						$add_footer_stats = get_option('firestats_show_footer_stats') == 'true' ? "checked" : "";
					?>
					<input type="checkbox"
							onclick="saveWpOption('show_footer_stats','firestats_show_footer_stats','boolean')"
							id="show_footer_stats" <?php echo $add_footer_stats?>/>

					<?php fs_e('Show statistics in footer')?><br/>
         </td>
        </tr>

		<tr>
			<td class="config_cell" width="300">
				<table width="100%">
					<thead>
						<tr><td><h3><?php fs_e('Excluded users')?></h3></td></tr>
					</thead>
					<tr>
						<td>
							<button class="button" onclick="saveExcludedUsers()"><?php fs_e('Save')?></button>
						</td>
					</tr>
					<tr>
						<td>
							<div id="exclude_users_placeholder"><?php echo fs_get_excluded_users_list()?></div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	<tr>
	
	<tr>
		<td class="config_cell" colspan="2">
		<h3><?php fs_e('Advanced')?></h3>
		<?php fs_e('WordPress site ID, every hit From this blog is recorded with this as the source Site ID')?><br />
		<?php fs_e("This should be the same ID as the Site ID in the sites table. you don't normally need to change this.")?><br />
		<input type="text"
			onkeypress="return trapEnter(event,'saveWpSiteID();');"
			id="wp_site_id" style="width:120px"
			value="<?php echo fs_get_local_option('site_id','')?>" />
		<button class="button" onclick="saveWpSiteID()"><?php fs_e('Save')?></button>
		</td>
	</tr>
</table>
</div>

<?php

/**
 * This file contain the html of the firestats admin page.
 */

require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/version-check.php');
require_once(dirname(__FILE__).'/html-utils.php');
require_once(dirname(__FILE__).'/layout.php');
require_once(dirname(__FILE__).'/session.php');

// assume users that get here are authenticated.
$ok = fs_session_start();
if (!$ok)
{
	echo "Error starting session<br/>";
	return;
}

global $FS_SESSION;
$FS_SESSION['authenticated'] = true;
global $FS_CONTEXT;
$FS_SESSION['context'] = $FS_CONTEXT;
// store the session now, to make sure the firestats.js.php will receive the correct session data.
fs_store_session();
?>
<script	type="text/javascript" src='<?php echo fs_url('js/prototype.js')?>'></script>
<script	type="text/javascript" src='<?php echo fs_url('js/firestats.js.php').fs_get_request_suffix()?>'></script>
<script	type="text/javascript" src='<?php echo fs_url('js/mktree.js')?>'></script>


<div id="firestats">
<div id="glasspane" class="fs_glasspane"></div>

<div class="fs_body width_margin <?php echo FS_LANG_DIR?>">


<h1><span class='normal_font' style='float:<?php H_END()?>;margin:10px;'>
<button class="button"
	onclick="openWindow('<?php echo fs_url('dialog.php').fs_get_request_suffix("dialog_id=donation")?>',600,600)">
<?php fs_e('Support FireStats')?></button>
</span> <?php 
$home = FS_HOMEPAGE;
echo "<a style='border-bottom: 0px' href='$home'><img alt='".fs_r('FireStats')."' src='".fs_url("img/firestats-header.png")."'/></a>";
echo '<span class="normal_font" style="padding-left:10px">';
echo sprintf("%s %s\n",FS_VERSION,(defined('DEMO') ? fs_r('Demo') : ''));
echo "<!-- Checking if there is a new FireStats version, if FireStats hangs refresh the page -->\n";flush();
echo fs_get_latest_firestats_version_message()."\n";
echo "<!-- Checking if there is a new IP2Country database, if FireStats hangs refresh the page -->\n";flush();
echo '<span id="new_ip2c_db_notification">'.fs_get_latest_ip2c_db_version_message()."</span>";
echo '</span>';
?></h1>

<!-- Feedback div -->
<div id="feedback_div">
<button class="button" onclick="hideFeedback();"><?php fs_e('Hide');?></button>
<span id="feedback_zone"></span></div>
<!-- feedback_div --> <!-- confirmation dialog -->
<div id="fs_confirmation_dialog">
<div id="fs_confirm_text"></div>
<div id="fs_confirm_control"><input id="fs_confirm_no" type="button"
	value="<?php fs_e("No")?>"></input> <input id="fs_confirm_yes"
	type="button" value="<?php fs_e("Yes")?>"></input></div>
</div>

<div id="network_status"></div>
<?php

$db_valid = fs_db_valid();
if (!$db_valid)
{
	include('page-database.php');
	return;
}
else
{
	fs_maintain_usage_stats();

	// for demo site, monitor access to self
	if (defined('DEMO'))
	{
		require_once(dirname(__FILE__).'/db-hit.php');
		fs_add_hit(null, false);
	}
}
?>
<div class="tabber" id="main_tab_id">
<div id="stats_page_id" class="tabbertab"
	title="<?php fs_e("Statistics")?>"><?php require('page-stats.php')?></div>
<!-- stats_page_id -->

<div id="settings_page_id" class="tabbertab"
	title="<?php fs_e("Settings")?>"><?php require('page-settings.php')?></div>
<!-- settings_page_id --> <?php
if (fs_in_wordpress())
{
	?>
<div id="wordpress_settings_id" class="tabbertab"
	title="<?php fs_e('WordPress settings')?>"><?php require('page-wordpress-settings.php')?>
</div>
<!-- wordpress_settings_id --> <?php 
}
?>


<div id="database_page_id" class="tabbertab"
	title="<?php fs_e("Database")?>"><?php require('page-database.php')?></div>
<!-- database_page_id -->

<div id="import_page_id" class="tabbertab"
	title="<?php fs_e("Import")?>"><?php require('page-import.php')?></div>
<!-- import_page_id -->

<div id="sites_id" class="tabbertab"
	title="<?php fs_e('Sites management')?>"><?php require('page-sites.php')?>
</div>

</div>
<!-- tabber --></div>
<!-- fs_body --> <?php require('footer.php')?></div>
<!-- firestats -->



<?php
require_once(dirname(__FILE__).'/session.php');
$res = fs_resume_existing_session();
if ($res !== true) 
{
	echo 'Session initializaiton failed : '.$res;
	return;
} 

require_once(dirname(__FILE__).'/init.php');
require_once(dirname(__FILE__).'/db-sql.php');

// update last nag time to now.
fs_update_option('last_nag_time',time());

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title><?php fs_e('Support FireStats')?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="<?php echo 'css/base.css';?>" type='text/css'/>
		<script	type="text/javascript" src='<?php echo fs_url('js/prototype.js')?>'></script>
		<script	type="text/javascript" src='<?php echo fs_url('js/firestats.js.php').fs_get_request_suffix()?>'></script>
		<script type="text/javascript">
		//<![CDATA[
		function saveAndClose(key,value)
		{
	        var params = 'action=saveOption&key=' + key + "&value=" + value + '&dest=fs';
			sendRequest(params,
				function(response, silent)
				{
					window.close();
					handleResponse(response,false);
				});
		}
		//]]>
		</script>
	</head>
	<body id="firestats">
	<div class="fs_body width_margin <?php echo FS_LANG_DIR?>">
		<h3><?php fs_e('Support FireStats')?></h3>
		<table id="status_table">
		<tr>
			<td width="33%"><?php fs_e('Page views')?> <p><?php echo fs_get_hit_count()?></p><br/></td>
			<td width="33%"><?php fs_e('Visits')?> <p><?php echo fs_get_unique_hit_count()?></p><br/></td>
			<td width="33%"><?php fs_e('Installed')?> 
			<p>
				<?php 
				$first_run = fs_get_option('first_run_time');
				echo time_since($first_run);
				?>
			</p><br/></td>
		</tr>
		</table>
	<?php
	fs_e("Even though FireStats is free, it takes a lot of time and hard work to develop and maintain.<br/>");
	fs_e("If you like FireStats and would like to show your support for the hard work I put into it, You can make a small Donation. Even a $5 donation would be greatly appreciated.<br/>");
	?>
	<ul>
		<li><button class="button" onclick="openWindow('<?php echo FS_WIKI."Donate"?>',500,500)"><?php fs_e("Yeah, I want to help");?></button></li>
		<li><button class="button" onclick="saveAndClose('donation_status','no')"><?php fs_e("Nah, go away");?></button></li>
		<li><button class="button" onclick="saveAndClose('donation_status','later')"><?php fs_e("Maybe later");?></button></li>
		<li><button class="button" onclick="saveAndClose('donation_status','donated')"><?php fs_e("Already donated");?></button></li>
	</ul>
	</div>
	</body>
</html>

<?php
function time_since($ts) 
{
	$ts=time()-$ts;
	if ($ts<60)
		// <1 minute
		return sprintf(fs_r("%d seconds ago"),$ts);
	elseif ($ts<60*60)
		// <1 hour
		return sprintf(fs_r("%d minutes ago"),floor($ts/60));
	elseif ($ts<60*60*2)
		// <2 hour
		return fs_r("one hour ago");
	elseif ($ts<60*60*24)
		// <24 hours = 1 day
		return sprintf(fs_r("%d hours ago"),floor($ts/(60*60)));
	elseif ($ts<60*60*24*2)
		// <2 days
		return fs_r("one day ago");
	elseif ($ts<60*60*24*7)
		// <7 days = 1 week
		return sprintf(fs_r("%d days ago"),floor($ts/(60*60*24)));
	elseif ($ts<60*60*24*30.5)
		// <30.5 days ~  1 month
		return sprintf(fs_r("%d weeks ago"),floor($ts/(60*60*24*7)));
	elseif ($ts<60*60*24*365)           
		// <365 days = 1 year
		return sprintf(fs_r("%d months ago"),($ts/(60*60*24*30.5)));
	else         
		// more than 1 year
		return sprintf(fs_r("%d years ago"),floor($ts/(60*60*24*7*365)));
};
?>

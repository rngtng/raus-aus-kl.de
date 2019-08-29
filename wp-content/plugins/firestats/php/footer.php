<script type="text/javascript" src="<?php echo fs_url('js/tabber/tabber-minimized.js')?>">
</script>

<?php 
if (fs_db_valid())
{
?>
<script type='text/javascript'>
//<![CDATA[
	// this is done here instead of sending an updated page in the first place
	// to improve startup time.
	updateAllStats();

	toggleAutoRefresh();

	<?php if (fs_get_auto_bots_list_update() == 'true'){?>
	sendRequest2('action=updateBotsList&update=botlist_placeholder,num_excluded&user_initiated=false',false);
	<?php }?>

	<?php if (fs_time_to_nag())
	{
	?>
		openWindow('<?php echo fs_url('dialog.php').fs_get_request_suffix("dialog_id=donation")?>',600,600);
	<?php }?>
//]]>
</script>

<?php }?>

<div class="wrap" style="text-align:center">
	<?php echo fs_r('FireStats').' '.FS_VERSION."<br/>
			   Problems? visit firestats <a href='".FS_HOMEPAGE."' target='_blank'>homepage</a>"?>
</div>

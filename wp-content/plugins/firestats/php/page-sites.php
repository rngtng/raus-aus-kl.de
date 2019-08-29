<script type="text/javascript">
//<![CDATA[
	function editSite(id)
	{
		var ew = $('fs_edit_window');
		var d = ew.style.display;
		if (d == 'none' || d == '')
		{
			$('edit_window_action').value = "edit";
			$('original_site_id').value = $('site_id_'+id).innerHTML;
			$('site_edit_name').value = $('site_name_'+id).innerHTML;
			var type = $('site_type_'+id).innerHTML;
			selectByText('site_edit_type',type);
			clearSiteID();
			showDialog('fs_edit_window');
		}
	}

	function saveSite()
	{
		var orig_sid = $F('original_site_id');
		if (orig_sid == '<?php fs_e('Automatic')?>') orig_sid = 'auto';
		var new_sid = $F('site_edit_id');
		if (new_sid == '<?php fs_e('Automatic')?>') new_sid = 'auto';
		var name = $F('site_edit_name');
		var type = $('site_edit_type').selectedIndex;
		var action = $F('edit_window_action');
		if (action == "new")
		{
			sendRequest('action=createNewSite&new_sid='+new_sid+'&name='+name+'&type='+type+'&update=fs_sites_table,sites_filter_span');
		}
		else
		if (action == "edit")
		{
			sendRequest('action=updateSiteInfo&new_sid='+new_sid+'&orig_sid='+orig_sid+'&name='+name+'&type='+type+'&update=fs_sites_table,sites_filter_span');
		}
		else
		{
			alert("Unknown action");
		}
		hideDialog('fs_edit_window');
	}

	function deleteSite(id)
	{
		$('delete_site_id').value = id;
		$('transfer_site_id').value = "";
		$('delete_type').selectedIndex = 0;
		updateDeleteDialog();
		showDialog('fs_delete_window');
	}
	
	function sendDeleteSite()
	{
		var sid = $F('delete_site_id');
		var action = $F('delete_type');
		var new_sid = $F('transfer_site_id');
		sendRequest('action=deleteSite&site_id='+sid+'&update=fs_sites_table,sites_filter_span&action_code=' + action + "&new_sid=" + new_sid);
		hideDialog('fs_delete_window');
	}

	function newSite()
	{
		$('edit_window_action').value = "new";
		$('original_site_id').value = "<?php fs_e('Automatic')?>";
		$('site_edit_name').value = "";
		$('site_edit_type').selectedIndex = 0;
		clearSiteID();
		showDialog('fs_edit_window');
		$('fs_edit_label').focus();
	}

	function activationHelp(type,id)
	{
		var url = '<?php echo fs_url('php/help-window.php')?>?TYPE=' + type + "&SITE_ID=" + id;
		openWindow(url,600,600);
	}
	
	function clearSiteID()
	{
		$('fs_clear_site_id').style.display='none';
		$('site_edit_id').value = $('original_site_id').value;
	}
	
	function updateDeleteDialog()
	{
		var dt = $('delete_type');
		var del = dt.selectedIndex == 0;
		var s = (del ? "none" : "block");
		$('transfer_option').style.display = s;
		var text = del ? "<?php fs_e("Delete")?>" : "<?php fs_e("Transfer")?>";
		$('fs_site_delete_button').value = text;
	}
//]]>
</script>

<div id="fs_sites_div">

<!-- Use table for base layout -->
<table><tr><td>

<div id="fs_sites_table_holder" class="wrap">
	<h2><?php fs_e('Manage sites')?></h2>
	<div id="fs_sites_table">
		<?php echo fs_get_sites_manage_table()?>
	</div>

	<div id="fs_edit_window" class="dialog_window">
		<div id="fs_edit_label"	class="fs_edit_label">
			<?php fs_e('ID')?>
			<input type="hidden" id="edit_window_action" value=""/>	
			<input type="hidden" id="original_site_id" value=""/>
			<input type="text" id="site_edit_id" value=""  onkeypress="$('fs_clear_site_id').style.display='inline'" onfocus="this.select()"/>
			<input type='image' class='img_btn' 
				src="<?php echo fs_url("img/help.blue.png")?>"
				onclick="openWindow('<?php echo FS_WIKI.'SiteID'?>',600,600);"
			/>
			<input id="fs_clear_site_id" style="display:none" type='image' title="<?php fs_e('Clear')?>" class='img_btn' src="<?php echo fs_url("img/clear.png")?>"
				onclick="clearSiteID()"
			/>
		</div>
		<div class="fs_edit_label"><?php fs_e('Name')?>	<input type="text" id="site_edit_name" value=""/></div>
		<div class="fs_edit_label"><?php fs_e('Type')?>	<select id="site_edit_type"><?php echo fs_get_site_type_options()?></select></div>
		<div class="fs_bottom_panel">
			<input type="button" value="<?php fs_e("Close")?>" onclick="hideDialog('fs_edit_window')"/>
			<input type="button" value="<?php fs_e("Save")?>" onclick="saveSite()"/>
		</div>
	</div>
	
	<div id="fs_delete_window" class="dialog_window">
		<div id="fs_confirm_delete">
			<span style="margin: 3px 3px 3px 3px;">
			<span class="notice"><?php fs_e("This will delete this site from the database, the operation is irreversible!")?></span><br/>
			<?php fs_e("What do you want to do with the site hits?")?><br/>
			</span>
			<select id="delete_type" onchange="updateDeleteDialog()">
				<option value="delete"><?php fs_e('Delete all the hits')?></option>
				<option value="change"><?php fs_e('Transfer the hits to another site')?></option>
			</select><br/>
			<span id="transfer_option" class="hidden">
			<?php fs_e("Enter an existing site ID to transfer the hits to")?>
			<input type="text" id="transfer_site_id" value=""/>
			</span>
			
			<input type="hidden" id="delete_site_id" value=""/>
		</div>
		<div class="fs_bottom_panel">
			<input type="button" value="<?php fs_e("Close")?>" onclick="hideDialog('fs_delete_window')"/>
			<input id="fs_site_delete_button" type="button" value="" onclick="sendDeleteSite()"/>
		</div>
	</div>	
	
</div>

</td><td>

<div id="fs_sites_tab_help" class="wrap">
	<h2><?php fs_e('Help')?></h2>
	<b><?php fs_e('Warning, you can really mess things up from here, be careful!')?></b><br/>
	<br/>
	<?php fs_e('FireStats can collect statistics from multiple sites (on the same server).')?><br/>
	<ul>
		<li><?php fs_e('The site need to be registered in the sites table')?></li>
		<li><?php fs_e('The site should be configured to use its ID when reporting a hit, click on the help button next to the site you created for more information')?></li>
		<li>
			<?php 
				echo sprintf(fs_r("Click %s for more information"),
				sprintf("<a target='_blank' href='%s'>%s</a>",FS_MULTIPLE_SITES_INFO_URL,fs_r('here')))?>
		</li>
	</ul>
</div>

</td></tr></table>

</div>

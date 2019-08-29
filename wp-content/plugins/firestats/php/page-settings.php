<script type="text/javascript">
//<![CDATA[

function addBot()
{
    var bot = $F('bot_wildcard');
    $('bot_wildcard').value = '';
    var params = 'action=' + 'addBot' + '&wildcard=' + bot;
    sendRequest(params);
}

function removeBot()
{
    var index = $('botlist').selectedIndex;
    if (index == -1)
    {
        showError('<?php fs_e('You need to select an bot you want to remove from the table')?>');
    }
    else
    {
        var bot_id = $('botlist').item(index).value;
        var params = 'action=' + 'removeBot' + '&bot_id=' +bot_id;
        sendRequest(params);
    }

}

function addExcludedIP()
{
    var ip = $F('excluded_ip_text');
    if (validateIP(ip))
    {
        $('excluded_ip_text').value = '';
        var params = 'action=' + 'addExcludedIP' + '&ip=' +ip;
        sendRequest(params);
    }
    else
    {
        showError('<?php fs_e("Invalid IP address")?>' + ": " + ip);
    }
}

function removeExcludedIP()
{
    var index = $('exclude_ip_table').selectedIndex;
    if (index == -1)
    {
        showError("<?php fs_e('You need to select an IP address you want to remove from the table')?>");
    }
    else
    {
        var ip = $F('exclude_ip_table');
        var params = 'action=' + 'removeExcludedIP' + '&ip=' +ip;
        sendRequest(params);
    }
}


function changeLanguage()
{
    sendRequest('action=changeLanguage&language=' + $F('language_code'));
}

function changeTimeZone()
{
    saveOption('user_timezone','user_timezone','string','records_table');
}

//]]>
</script>

<div id="configuration_area" class="configuration_area">
	<table class="config_table">
		<tr>
			<td class="config_cell" colspan="2">
				<h3><?php fs_e('Automatic version check')?></h3>
				<?php
					$version_check_enabled = fs_get_version_check_enabled();
					$version_check_enabled = $version_check_enabled == 'true' ? "checked=\"checked\"" : "";
				?>
				<input type="checkbox" 
						onclick="saveOption('check_new_version','firestats_version_check_enabled','boolean')" 
						id="check_new_version" <?php echo $version_check_enabled?>/>
				<?php fs_e('Automatically check if there is a new version of FireStats (recommended)')?>
			</td>
		</tr>
		<tr>
			<td class="config_cell" colspan="2">
				<?php
					$ip2c_ver_check = fs_get_auto_ip2c_ver_check();
					$ip2c_ver_check = $ip2c_ver_check == 'true' ? "checked=\"checked\"" : "";
				?>
				<h3><?php fs_e('IP-to-country database')?></h3>
				<ul>
				<li>
				<?php echo sprintf(fs_r('IP-to-country database version : %s'),'<b id="ip2c_database_version">'.fs_get_current_ip2c_db_version().'</b>')?><br/>
				</li><li>
				<input type="checkbox" 
						onclick="saveOption('check_ip2c_update','ip-to-country-db_version_check_enabled','boolean')" 
						id="check_ip2c_update" <?php echo $ip2c_ver_check?>/>
				<?php fs_e('Automatically check if there is a new version of IP-to-country database')?><br/>
				</li><li>
				<?php fs_e('Update IP-to-country database now (only if needed)')?>
				<button class="button" onclick="sendRequest('action=updateIP2CountryDB')">
					<?php fs_e('Update');?>
				</button>
				</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td class="config_cell" colspan="2">
				<h3><?php fs_e('Options')?></h3>
				<?php fs_e('Select language');
					$langs = fs_get_languages_list();
				?>:
				<select id="language_code">
					<?php echo $langs?>
				</select>
				<button class="button" onclick="changeLanguage()"><?php fs_e('Save');?></button>
				<?php echo sprintf("<a href=\"%s\" target='_blank'>%s</a>",
									FS_HOMEPAGE_TRANSLATE, fs_r('How to translate to a new language'))?><br/>
				<?php
					$db_support_tz = (ver_comp("4.1.3",fs_mysql_version()) <= 0);
					if ($db_support_tz)
					{
				?>
				<?php fs_e('Select your time zone')?>
				<select id='user_timezone'>
					<?php echo fs_get_timezone_list()?>
				</select>
				<button class="button" onclick="changeTimeZone()"><?php fs_e('Save');?></button><br/>
				<?php
					}
					else
					{
						echo "<br/>";
						echo "<b>".sprintf(fs_r('Time zone selection requires %s or newer'), "Mysql 4.1.13")."</b>";
					}
				?>
				<br/>
				<?php fs_e('Select WHOIS provider')?>
				<select id="whois_providers">
					<?php echo fs_get_whois_options()?>
				</select>
				<button class="button" 
					onclick="saveOption('whois_providers','whois_provider','string','records_table')">
					<?php fs_e('Save');?>
				</button>
				<input type='image' class='img_btn'
					src="<?php echo fs_url("img/help.blue.png")?>" 
					onclick="openWindow('<?php echo FS_WIKI.'WhoisProviders'?>',800,600)"
				/>
		<br/>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<h3><?php fs_e('Exclude hits')?></h3>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="config_cell">
				<ul>
					<li>
						<?php
							$save_excluded_records= fs_get_save_excluded_records();
							$save_excluded_records = $save_excluded_records == 'true' ? "checked=\"checked\"" : "";
						?>
				
						<input type="checkbox" 
							onclick="saveOption('save_excluded_hits','save_excluded_records','boolean')" 
							id="save_excluded_hits" <?php echo $save_excluded_records?>/>
						<?php fs_e('Save excluded records (not recommended)')?><br/>
					</li>
					<li>
						<?php fs_e('Purge excluded records stored in the database')?>
						(<b id="num_excluded"><?php echo fs_get_num_excluded()?></b>)
						<button class="button" onclick="sendRequest('action=purgeExcludedHits')">
							<?php fs_e('Purge');?>
						</button>
					</li>
				</ul>
			</td>
		</tr>
		<tr> <!-- TODO : move style stuff to CSS -->
			<td class="config_cell" width="300">
				<table>
					<thead>
						<tr><td><h3><?php fs_e('Bots list')?></h3></td></tr>
					</thead>
					<tr>
						<td>
							<input type="text" onkeypress="return trapEnter(event,'addBot();');" 
								id="bot_wildcard" style="width:110px"/>
							<button class="button" onclick="addBot()"><?php fs_e('Add')?></button>
							<button class="button" onclick="removeBot()"><?php fs_e('Remove')?></button>
							<?php fs_cfg_button('more_bots_options')?>
						</td>
					</tr>
					<tr>
						<td>
							<span id="more_bots_options" class="normal_font hidden">
								<?php
									$auto_bots_list_update = fs_get_auto_bots_list_update();
									$auto_bots_list_update = $auto_bots_list_update == 'true' ? "checked=\"checked\"" : "";
								?>
								<input type="checkbox" 
									onclick="saveOption('auto_bots_list_update','auto_bots_list_update','boolean')"
									id="auto_bots_list_update" <?php echo $auto_bots_list_update?>/>
								<?php fs_e('Automatic update')?><br/>
								<button class="button" 
									onclick="sendRequest('action=updateBotsList&amp;update=botlist_placeholder,num_excluded')">
									<?php fs_e('Update now')?>
								</button>
								<button class="button" 
									onclick="openWindow('<?php echo fs_url('dialog.php').fs_get_request_suffix("dialog_id=import_bots")?>',300,300)">
									<?php fs_e('Import')?>
								</button>
								<button class="button" 
									onclick="window.location.href='<?php echo fs_url('php/export-bots-list.php')?>'">
									<?php fs_e('Export')?>
								</button>
							</span>
						</td>

					</tr>
					<tr>
						<td>
							<div id="botlist_placeholder"><?php echo fs_get_bot_list()?></div>
						</td>
					</tr>
				</table>
			</td>
			<td class="config_cell" width="300">
				<table>
					<thead>
						<tr><td><h3><?php fs_e('Excluded IPs')?></h3></td></tr>
					</thead>
					<tr>
						<td>
							<input type="text" onkeypress="return trapEnter(event,'addExcludedIP();');" id="excluded_ip_text" style="width:120px"/>
							<button class="button" onclick="addExcludedIP()"><?php fs_e('Add')?></button>
							<button class="button" onclick="removeExcludedIP()"><?php fs_e('Remove')?></button>
						</td>
					</tr>
					<tr>
						<td>
							<div id="exclude_ip_placeholder"><?php echo fs_get_excluded_ips_list()?></div>
						</td>
					</tr>
				</table>
			</td>
	</tr>
	</table> <!-- config_table -->
</div> <!-- configuration area -->

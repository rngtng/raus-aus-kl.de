<script type="text/javascript">
//<![CDATA[
function updateSitesFilter()
{
	sendRequest("action=updateSitesFilter&sites_filter="+$F("sites_filter"));
}

function applyFilters()
{
	saveOptions('ht_ip_filter,ht_url_filter,ht_referrer_filter,ht_useragent_filter','records_table');
}

function saveAutoRefreshInterval()
{
    saveOption('auto_refresh_interval','firestats_auto_refresh_interval','positive_num');
}


function saveNumEntries()
{
    saveOption('num_to_show','firestats_num_entries_to_show','positive_num','records_table');
}

function saveRecentPopularConfig()
{
    saveOptions('num_max_recent_popular,recent_popular_pages_days_ago','popular_pages');
}

function saveRecentReferrersConfig()
{
    saveOptions('num_max_recent_referers,recent_referers_days_ago','fs_recent_referers');
}

function saveCountriesConfig()
{
    saveOptions('countries_list_days_ago,max_countries_in_list','countries_list');
}

function saveBrowsersDaysAgo()
{
	saveOptions('browsers_tree_days_ago','fs_browsers_tree');
}

function saveOSDaysAgo()
{
	saveOptions('os_tree_days_ago','fs_os_tree');
}

var autoRefreshTimerID;
var timeLeftToRefresh;

function updateRefreshTimer()
{
    if (timeLeftToRefresh <= 0)
    {
        sendRequest('action=getAllStats') // TODO : optimize, dont send back anything if nothing changed
        toggleAutoRefresh();
    }
    else
    {
        var min = parseInt(timeLeftToRefresh / 60);
        var sec = timeLeftToRefresh - min * 60;
        if (sec < 10) sec = '0' + sec;
        autoRefreshTimerID = setTimeout("updateRefreshTimer()", 1000);
        var b = $('refresh_button');
        if (b)
        {
            b.innerHTML = "<?php fs_e('Refresh statistics')?>" + " ("+min+":"+sec+")";
        }
    }
    timeLeftToRefresh--;
} 


function toggleAutoRefresh()
{
    if (!$('auto_refresh_checkbox'))
    {
        return;
    }

    var on = $F('auto_refresh_checkbox');
    if (on == 'on')
    {
        if (autoRefreshTimerID) clearTimeout(autoRefreshTimerID);
        autoRefreshTimerID = setTimeout("updateRefreshTimer()", 0);
        timeLeftToRefresh = $F('auto_refresh_interval');
        var n = parseInt(timeLeftToRefresh);
        if(!n || n <= 0)
        {
            showError("<?php print fs_r("Not a positive number : ") ?>" + timeLeftToRefresh);
            $('auto_refresh_checkbox').checked = false;
        }
        else
        {
            timeLeftToRefresh = n * 60;
        }
    }
    else
    {
        clearTimeout(autoRefreshTimerID);
        $('refresh_button').innerHTML = "<?php fs_e('Refresh statistics')?>";
    }

}
function updateAllStats()
{
	sendRequest('action=updateFields&update=stats_total_count,stats_total_unique,stats_total_count_last_day,stats_total_unique_last_day;fs_recent_referers,popular_pages;countries_list,fs_browsers_tree,fs_os_tree;records_table');
}
//]]>
</script>

<div id="stats_area" class="stats_area">
<div class="wrap">
<button class="button" id="refresh_button"
	onclick="updateAllStats();toggleAutoRefresh()">
<?php fs_e('Refresh statistics');?></button>
<?php fs_cfg_button('refresh_button_config')?>

	<span id="sites_filter_span">
	<?php 
		echo fs_get_sites_list();
	?>
	</span>

	<br/>
	<span id="refresh_button_config" class="normal_font hidden">
		<?php
			$auto_refresh_enabled = fs_get_option('firestats_auto_refresh_enabled','true');
			$auto_refresh_interval  = fs_get_option('firestats_auto_refresh_interval','5');
			$auto_refresh_checked = $auto_refresh_enabled == 'true' ? "checked=\"checked\"" : "";
		?>

		<input type="checkbox" 
			onclick="saveOption('auto_refresh_checkbox','firestats_auto_refresh_enabled','boolean');toggleAutoRefresh()" 
			id="auto_refresh_checkbox" <?php echo $auto_refresh_checked?>/>
	<?php fs_e('Auto refresh every')?>
		<input type="text" onkeypress="return trapEnter(event, 'saveAutoRefreshInterval();');"
			id="auto_refresh_interval" size="1" value="<?php echo $auto_refresh_interval?>"/>
			<?php fs_e('minutes')?>
		<button class="button" onclick="saveAutoRefreshInterval()"><?php fs_e('Apply');?></button>
	</span>
</div> <!-- wrap --> 

<div class="wrap">
<h2><?php fs_e('Status');?></h2>
<table id="status_table">
  <tr>
    <td width="25%"><?php fs_e('Page views')?> <p id="stats_total_count">--</p><br/></td>
    <td width="25%"><?php fs_e('Visits')?> <p id="stats_total_unique">--</p><br/></td>    
	<td width="25%"><?php fs_e('Page views in last 24 hours')?><p id="stats_total_count_last_day">--</p><br/></td>
    <td width="25%"><?php fs_e('Visits in last 24 hours')?><p id="stats_total_unique_last_day">--</p><br/></td>
  </tr>
</table>
</div> <!-- warp -->

<div class="wrap">
	<h2><?php fs_e('Recent referrers')?>
		<?php fs_cfg_button('recent_referers_id')?>
		<span id="recent_referers_id" class="normal_font hidden">
			<span>
				<?php fs_e('Maximum')?> 
				<input type="text" 
					onkeypress="return trapEnter(event, 'saveRecentReferrersConfig()');" 
					size="4" id="num_max_recent_referers" value="<?php echo fs_get_max_referers_num()?>"
				/>
			</span>
			<span>
				<?php fs_e('Days ago')?> 
				<input type="text" 
					onkeypress="return trapEnter(event, 'saveRecentReferrersConfig()');" 
					size="4" id="recent_referers_days_ago" value="<?php echo fs_get_recent_referers_days_ago()?>"
				/>
				<button class="button" onclick="saveRecentReferrersConfig()"><?php fs_e('Apply');?></button>
			</span>
		</span>
	</h2>
	<div id="fs_recent_referers" class="tree_container">
		<div id='referrers_tree_id'>--</div>
	</div>
</div> <!-- warp -->

<div class="wrap">
	<h2><?php fs_e('Recent popular pages')?>
		<?php fs_cfg_button('recent_popular_config')?>
		<span id="recent_popular_config" class="normal_font hidden">
			<span>
				<?php fs_e('Maximum')?> 
				<input type="text" 
					onkeypress="return trapEnter(event, 'saveRecentPopularConfig()');" 
					size="4" id="num_max_recent_popular" value="<?php echo fs_get_max_popular_num()?>"
				/>
				<?php fs_e('Days ago')?> 
				<input type="text" 
					onkeypress="return trapEnter(event, 'saveRecentPopularConfig()');" 
					size="4" id="recent_popular_pages_days_ago" value="<?php echo fs_get_recent_popular_pages_days_ago()?>"
				/>
			</span>
			<button class="button" onclick="saveRecentPopularConfig()"><?php fs_e('Apply');?></button>
		</span>
	</h2>
	<div id="popular_pages" class="tree_container">
		--
	</div>
</div> <!-- warp -->

<div class="wrap">
	<h2><?php fs_e('Browsers')?>
		<span>
			<?php fs_cfg_button('browsers_config')?>
			<span id="browsers_config" class="normal_font hidden">
				<span>
					<?php fs_e('Days ago')?> 
					<input type="text" 
						onkeypress="return trapEnter(event, 'saveBrowsersDaysAgo()');" 
						size="4" id="browsers_tree_days_ago" value="<?php echo fs_browsers_tree_days_ago()?>"
					/>
					<button class="button" onclick="saveBrowsersDaysAgo()"><?php fs_e('Save');?></button>
				</span>
			</span>
		</span>
	</h2>
	<div id="fs_browsers_tree" class="tree_container">
		<div id="browsers_tree_id">--</div>
	</div>
</div> <!-- warp -->

<div class="wrap">
	<h2><?php fs_e('Operating systems')?>
		<span>
			<?php fs_cfg_button('os_config')?>
			<span id="os_config" class="normal_font hidden">
				<span>
					<?php fs_e('Days ago')?> 
					<input type="text" 
						onkeypress="return trapEnter(event, 'saveOSDaysAgo()');" 
						size="4" id="os_tree_days_ago" value="<?php echo fs_os_tree_days_ago()?>"
					/>
					<button class="button" onclick="saveOSDaysAgo()"><?php fs_e('Save');?></button>
				</span>
			</span>
		</span>
	</h2>
	<div id="fs_os_tree" class="tree_container">
		<div id="os_tree_id">--</div>
	</div>
</div> <!-- warp -->

<div class="wrap">
	<h2><?php fs_e('Countries')?>
		<?php fs_cfg_button('countries_config')?>
		<span id="countries_config" class="normal_font hidden">
			<span>
				<?php fs_e('Maximum')?> 
				<input type="text" 
					onkeypress="return trapEnter(event, 'saveCountriesConfig()');" 
					size="4" id="max_countries_in_list" value="<?php echo fs_get_max_countries_num()?>"
				/>
			</span>
			<span>
				<?php fs_e('Days ago')?> 
				<input type="text" 
					onkeypress="return trapEnter(event, 'saveCountriesConfig()');" 
					size="4" id="countries_list_days_ago" value="<?php echo fs_countries_list_days_ago()?>"
				/>
				<button class="button" onclick="saveCountriesConfig()"><?php fs_e('Apply');?></button>
			</span>
		</span>
	</h2>
	<div id="countries_list">--</div>
</div> <!-- warp -->

<div class="wrap">
	<h2><?php fs_e('Hits table')?>
		<?php fs_cfg_button('records_table_config')?>
		<span id="records_table_config" class="normal_font hidden">
			<span>
				<?php fs_e('Number of hits to show')?> 
				<input type="text" 
					onkeypress="return trapEnter(event, 'saveNumEntries();');" 
					size="4" id="num_to_show" value="<?php echo fs_get_num_hits_in_table()?>"
				/>
				<button class="button" onclick="saveNumEntries()"><?php fs_e('Save');?></button>
			</span>
		</span>
	</h2>
	<div style="border:1px solid #f0f0fa">
		
		<table>
		<tr>
			<th></th>	
			<th><?php fs_e('IP')?></th>
			<th><?php fs_e('URL')?></th>
			<th><?php fs_e('Referrer')?></th>
			<th><?php fs_e('User agent')?></th>
			<th></th>
		</tr>
		<tr>
			<td><span class="bold"><?php fs_e('Filter')?>:</span></td>
			<td>
				<input type="text" id="ht_ip_filter" style="width:120px" 
					onkeypress="return trapEnter(event, 'applyFilters()');"
					value="<?php echo fs_get_option('ht_ip_filter')?>"/>
			</td>
			<td>
				<input type="text" id="ht_url_filter" style="width:190px"
					onkeypress="return trapEnter(event, 'applyFilters()');"
					value="<?php echo fs_get_option('ht_url_filter')?>"/>
			</td>
			<td>
				<input type="text" id="ht_referrer_filter" style="width:190px"
					onkeypress="return trapEnter(event, 'applyFilters()');"
					value="<?php echo fs_get_option('ht_referrer_filter')?>"/>
			</td>
			<td>
				<input type="text" id="ht_useragent_filter" style="width:190px"
					onkeypress="return trapEnter(event, 'applyFilters()');"
					value="<?php echo fs_get_option('ht_useragent_filter')?>"/>
			</td>
			<td>
				<button class="button" 
					onclick="clearOptions('ht_ip_filter,ht_url_filter,ht_referrer_filter,ht_useragent_filter',true,'records_table')">
					<?php fs_e('Clear');?>
				</button>
				<button class="button"
					onclick="applyFilters()"
				>
				<?php fs_e('Apply');?>
				</button>
			</td>
		</tr>
		</table>
		

	</div>
	<div id="records_table">--</div>
</div> <!-- warp -->
</div> <!-- stats_area -->


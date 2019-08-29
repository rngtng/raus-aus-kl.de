<?php
require_once(dirname(__FILE__).'/browsniff.php');
require_once(dirname(__FILE__).'/db-sql.php');
require_once(dirname(__FILE__).'/fs-gettext.php');

function fs_cfg_button($toggle_id)
{
?>
<input type='image' class="img_btn config_img" src='<?php echo fs_url("img/configure.png")?>' 
onclick="toggle_div_visibility('<?php echo $toggle_id?>')"/>
<?php
}

function fs_get_whois_options()
{
	$selected = fs_get_option('whois_provider','');
	$res = "";
	$providers = fs_get_whois_providers();
	foreach($providers as $p=>$v)
	{
		$res .= "<option ".($p == $selected ? "selected=\"selected\" " : "")."value='$p'>$p</option>";
	}
	return $res;
}

function fs_get_sites_manage_table()
{
	$sites = fs_get_sites();
	$res = "<table>";
	$idH = fs_r('ID');
	$nameH = fs_r('Name');
	$typeH = fs_r("Type");
	$views = fs_r("Page views");
	$res .= "<tr>
		<th style='width:5%'>$idH</th>
		<th style='width:45%'>$nameH</th>
		<th style='width:25%'>$typeH</th>
		<th style='width:5%'>$views</th>
		<th style='width:20%'></th>
		</tr>";
	if (count($sites) > 0)
	{
		foreach($sites as $site)
		{
			$id = $site['id'];
			$hits = fs_get_hit_count(null, $id);
			$name = $site['name'];
			$type = $site['type'];
			$typeStr = fs_get_site_type_str($type);
			$tip = fs_r('How to integrate with this site');
			$text = "<tr id='site_row_$id'>
				<td id='site_id_$id'>$id</td>
				<td id='site_name_$id'>$name</td>
				<td id='site_type_$id'>$typeStr</td>
				<td id='site_hits_$id'>$hits</td>
				<td>
				<input type='image' class='img_btn' src='".fs_url("img/edit.png")."' onclick='editSite(\"$id\")'/>
				<input type='image' class='img_btn' src='".fs_url("img/delete.png")."' onclick='deleteSite(\"$id\")'/>
				<input type='image' class='img_btn' title='$tip' src='".fs_url("img/help.blue.png")."' onclick='activationHelp($type,$id)'/>
				</td>
				</tr>";
			$res .= "$text\n";
		}
	}

	$orphans = fs_get_orphan_site_ids();
	if ($orphans != false && count($orphans) > 0)
	{
		foreach($orphans as $site)
		{
			$id = $site['id'];
			$hits = fs_get_hit_count(null, $id);
			$name = fs_r("Orphaned hits");
			$text = "<tr id='site_row_$id'>
				<td id='site_id_$id'>$id</td>
				<td id='site_name_$id'>$name</td>
				<td id='site_type_$id'></td>
				<td id='site_hits_$id'>$hits</td>
				<td></td>
				</tr>";
			$res .= "$text\n";
		}
	}
	
	$add_new = fs_r('Add a new site');
	$res .= "<tr><td colspan='5'>
		<input type='image' class='img_btn' src='".fs_url("img/add.png")."' onclick='newSite()'/>  $add_new
		</td></tr></table>";

	return $res;

}

function fs_get_site_type_options()
{
	$a = array();
	$a[] = FS_SITE_TYPE_GENERIC;
	$a[] = FS_SITE_TYPE_WORDPRESS;
	$a[] = FS_SITE_TYPE_DJANGO;
	$a[] = FS_SITE_TYPE_DRUPAL;
	$a[] = FS_SITE_TYPE_GREGARIUS;
	$a[] = FS_SITE_TYPE_JOOMLA;
	$a[] = FS_SITE_TYPE_MEDIAWIKI;
	$a[] = FS_SITE_TYPE_TRAC;
	$res = '';
	foreach($a as $v)
	{
		$str = fs_get_site_type_str($v);
		$res .= "<option value='$v'>$str</option>";
	}

	return $res;
}

function fs_get_site_type_str($type)
{
	switch($type)
	{
		case FS_SITE_TYPE_GENERIC:
			return fs_r("Generic PHP site");
		case FS_SITE_TYPE_WORDPRESS:
			return fs_r("Wordpress");
		case FS_SITE_TYPE_DJANGO:
			return fs_r("Django");
		case FS_SITE_TYPE_DRUPAL:
			return fs_r("Drupal");
		case FS_SITE_TYPE_GREGARIUS:
			return fs_r("Gregarius");
		case FS_SITE_TYPE_JOOMLA:
			return fs_r("Joomla");
		case FS_SITE_TYPE_MEDIAWIKI:
			return fs_r("MediaWiki");
		case FS_SITE_TYPE_TRAC:
			return fs_r("Trac");
	}
	return fs_r("Unknown");
}

function fs_get_timezone_list()
{
	$zones = file(FS_ABS_PATH.'/php/timezones.txt');
	$current_tz = fs_get_option('user_timezone','system');
	$res = '';
	foreach($zones as $zone)
	{
		$zone = trim($zone);
		if ($zone[0] == '#') continue;
		if ($current_tz != $zone)
		{
			$res .= "<option value='$zone'>$zone</option>\n";
		}
		else
		{
		    $res .= "<option selected=\"selected\" value='$zone'>$zone</option>\n";
		}
	}
	return $res;
}

function fs_get_languages_list()
{
	$current_lang = fs_get_option('current_language');
	$dir = FS_ABS_PATH.'/i18n';
	$dh  = opendir($dir);
    $res = '<option'.($current_lang == '' ? " selected=\"selected\"" : "").' value="en_US">English</option>';

	$list = array();
    while (false !== ($filename = readdir($dh)))
    {
        if (fs_ends_with($filename, '.po'))
        {
        	$r = sscanf($filename,"firestats-%s");
			if (isset($r[0]))
			{
				$code = $r[0];
				$len = strlen($code);
				$code = substr($code, 0, $len - 3);
				$name = fs_get_lang_name($dir.'/'.$filename);
				$d = new stdClass;
				$d->valid = true;
				$d->code = $code;
				$d->lname = $name;
				$list[] = $d;
			}
			else
			{
				$d = new stdClass;
				$d->valid = false;
				$d->fname = $filename;
				$list[] = $d;
			}
        }
    }

    $foo = create_function('$a, $b',
    '
		if ($a->valid && $b->valid)
		{
			return strcmp($a->lname,$b->lname);
		}
		else
		{
			if (!$a->valid && !$b->valid) return 0;
			if ($a->valid && !$b->valid) return -1;
			if (!$a->valid && $b->valid) return 1;
			return 0;
		}
	');
    uasort($list,$foo);
    foreach($list as $lang)
    {
    	if ($lang->valid)
    	{
    		$code = $lang->code;
			$name = $lang->lname;
			$res .= "<option value='$code'".($current_lang == $code ? " selected='selected'" : "").">$name</option>";
		}
		else
		{
			$filename = $lang->fname;
			$res .= "<option>".fs_r('Invalid').": $filename"."</option>";
		}
	}

    return $res;
}

function fs_get_sites_list()
{
	$sites= fs_get_sites();
	if (count($sites) < 2) return '';

	$all = fs_r("All");
	$str = fs_r('Show statistics from')."<select id='sites_filter' onchange='updateSitesFilter()'>";
	$str .= "<option value='all'>$all</option>";
	$selected_site = fs_get_local_option('sites_filter');

	foreach($sites as $site)
	{
		$selected = "";
		$id = $site['id'];
		$name = $site['name'];
		if ($id == $selected_site) $selected = "selected='selected'";
		$str .= "<option $selected value='$id'>$name</option>";
	}
    $str .= "</select>";
	return $str;
}

// replace special url character with xml friendly escape codes.
function fs_xmlentities ( $string )
{
	return str_replace ( array ( '&', '"', "'", '<', '>'), 
						 array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;'), 
						 $string );
}


function fs_format_link($url)
{
	if ($url == "unknown")
	{
		return fs_r('unknown');
	}
	else
	{
		// if the url is relative, make it absoulte. 
	    $full_url = fs_get_absolute_url($url);
	    // for the display, use the relative and line splited version.
   	    $text = fs_prepare_string(fs_get_relative_url($full_url), 30);
   	    
   	    // ' tends to mess up the url, encode it. (not using full urlencode because this really makes a mess in this case).
		$url = str_replace (array ( '\''),array ( '%27'),$full_url);
	    return "<a target='_blank' href='$url'>$text</a>";
	}
}

function fs_get_referer_link($entry)
{
	return fs_format_link($entry->referer);
} 

function fs_get_url_link($entry)
{
	return fs_format_link($entry->url);
}

function fs_get_current_whois_provider_url()
{
	$name = fs_get_option('whois_provider','ARIN');
	$providers = fs_get_whois_providers();
	return isset($providers[$name]) ? $providers[$name] : '';
}

function fs_get_whois_link($entry)
{
	$whois = fs_get_current_whois_provider_url();
	$ip = $entry->ip;
	// if provider is not specified just return the ip address as is.
	if (empty($whois)) return $ip;
	$url = sprintf("$whois",$ip);
	return "<a target='_blank' href='$url'>$ip</a>";
}



function fs_get_records_table()
{
	require_once(dirname(__FILE__).'/ip2country.php');
	$res = "";
	$entries = fs_getentries();	
	if ($entries === false)
	{
		return fs_db_error();
	}
	else
	if ($entries)
	{

		$i = 0;
		$res = 
'<table>
	<thead>
		<tr>
			<td class="records_table_row2">'.fs_r('IP')   	 	.'</td>
			<td class="records_table_row3">'.fs_r('TimeStamp')	.'</td>
			<td class="records_table_row4">'.fs_r('URL')      	.'</td>
			<td class="records_table_row5">'.fs_r('Referrer')	.'</td>
			<td class="records_table_row6">'.fs_r('Image')    	.'</td>
			<td class="records_table_row7">'.fs_r('UserAgent')	.'</td>
		</tr>
	</thead>
	<tbody>';

		foreach ($entries as $entry)
		{
			$i++;	
			$res .= 
		'<tr'.($i%2 ? ' class="alternate"' : "").'>
			<td class="records_table_row2">'.fs_get_whois_link($entry).'</td>
			<td class="records_table_row3">'.$entry->timestamp.'</td>
			<td class="records_table_row4">'.fs_get_url_link($entry).'</td>
			<td class="records_table_row5">'.fs_get_referer_link($entry).'</td>
			<td class="records_table_row6">'.fs_pri_browser_images($entry->useragent).fs_get_country_flag_url($entry->country_code).'</td>
			<td class="records_table_row7">'.fs_prepare_string($entry->useragent, 50).'</td>
		</tr>';
		} // for loop
	}
	else
	{
		$res .= fs_r('No data yet, go get some hits');
	}

	$res .=
'	</tbody>
</table>';
	return $res;
}

function fs_prepare_string($text, $break_at = null, $newline = "<br/>")
{
	$break = $break_at != null;
	if ($break)
	{
		// since encode will encode our line breaks if we insert it now
		// we are doing a little trick here:
		// first put a place holder for the line break
		// \255 sounds like a character that is not supposed to be in urls.
		$text = wordwrap($text, $break_at,"\255",1);
	}
	
	// fix up any magic characters in the url.
    $text = str_replace (array ( '<', '>'),array ( '&lt;' , '&gt;'),$text);

	if ($break)
	{
		// now we can replace the \255 by a line break.
		$text = str_replace(array("\255"),array($newline),$text);
	}
	return $text;	
}

function fs_get_browsers_tree($days_ago = NULL)
{
	if (!$days_ago) $days_ago = fs_browsers_tree_days_ago();
	return fs_get_stats_tree(fs_get_browser_statistics($days_ago),'browsers_tree_id');
}

function fs_get_os_tree($days_ago = NULL)
{
	if (!$days_ago) $days_ago = fs_os_tree_days_ago();
	return fs_get_stats_tree(fs_get_os_statistics($days_ago),'os_tree_id');
}


function fs_get_stats_tree($stats, $id)
{
	if ($stats === false)
	{
		return fs_db_error();
	}
	$stats_data = $stats;
	
	$res = "<div id='$id'>";
	if (!$stats_data) // no data yet
	{
		$res .= fs_r('No data yet, go get some hits');
	}	
	else
	{
		foreach ($stats_data as $code => $stats)
		{
			$img=isset($stats['image']) ? $stats['image'] : "";
			$name=$stats['name'];
			$count=$stats['count'];

			$browser_percent=sprintf("%.1f",$stats['percent']);
			$res .= "<ul class=\"mktree\">";
			$res .= "<li>$img $name <b>$browser_percent%</b>";
			$res .= "<ul>";
			$sublist = $stats['sublist'];
			if ($sublist == null) continue;
			foreach($sublist as $ver => $vstats)
			{
				if ($code == 'others')	
				{
					$others = $vstats['sublist'];
					foreach($others as $okey => $other)
					{
						//var_dump($other);
						$img=isset($other['image']) ? $other['image'] : "";
						$name=$other['name'];
						$ua = fs_prepare_string($other['useragent']);
						$version_percent=sprintf("%.1f",$other['percent']);
						$res .= "<li>$img $name $okey <b>$version_percent%</b>";
						$res .="<ul><li>$ua</li></ul>";
						$res .= "</li>\n";
					}
				}	
				else
				{		
					$ua = fs_prepare_string($vstats['useragent']);
					$version_percent=sprintf("%.1f",$vstats['percent']);
					$res .= "<li>$img $name $ver <b>$version_percent%</b>";
					$res .="<ul><li>$ua</li></ul>";
					$res .= "</li>\n";
				}
			}
			$res .= "</ul>";
			$res .= "</li>";
			$res .= "</ul>\n";
		}
	}

	$res .= "</div>";

	return $res;
}

function fs_get_excluded_ips_list()
{
	$list = fs_get_excluded_ips();
	if ($list === false) return fs_db_error();
	$c = count($list);
	$res = 
"<select class='full_width' size=\"10\" id=\"exclude_ip_table\" ".($c == 0 ? "disabled=\"disabled\"" : "")." >";	
	if ($c == 0)
	{
		$res .= "	<option>".fs_r('Empty')."</option>";
	}
	else
	{
		foreach ($list as $row)
		{
			$res .= 
"	<option>".$row['ip']."</option>\n";
		}
	}
	$res .= "</select>\n";
	return $res;
}

function fs_get_excluded_users_list()
{
	$users = fs_get_users();
	if ($users === false) return fs_db_error();
	$excluded_users=explode(",",fs_get_local_option('excluded_users',''));
	$c = count($users);
	$res = 
"<select class='full_width' multiple='multiple' size='10' id='excluded_users_table' ".($c == 0 ? "disabled='disabled'" : "")." >";	
	if ($c == 0)
	{
		$res .= '	<option>'.fs_r('Empty').'</option>'."\n";
	}
	else
	{
		foreach($users as $u)	
		{
			$in = in_array($u['id'],$excluded_users);
			$selected = $in ? "selected='selected'"  : "";
			$append =   $in ? " (".fs_r("excluded").")" : "";
			$res .= 
"	<option value='".$u['id']."' $selected>".$u['name'].$append."</option>\n";
		}	
	}
	$res .= "</select>\n";
	return $res;

}

function fs_get_bot_list()
{
	$list = fs_get_bots();
	if ($list === false) return fs_db_error();
	$c = count($list);
	$res = "<select class='full_width' size='10' id=\"botlist\" ".($c == 0 ? "disabled='disabled'" : "")." >";	
	if ($c == 0)
	{
		$res .= '<option>'.fs_r('Empty').'</option>';
	}
	else
	{	
		foreach ($list as $row)
		{
			$res .= "<option value='".$row['id']."'>".$row['wildcard']."</option>";
		}
	}
	$res .= "</select>";
	return $res;
}

function fs_get_recent_referers_tree($max_num = null, $days_ago = null)
{
	if (!$max_num)
	{
		 $max_num = fs_get_max_referers_num();
	}
	if (!$days_ago)
	{
		 $days_ago = fs_get_recent_referers_days_ago();
	}
	
	$refs = fs_get_recent_referers($max_num, $days_ago);
	if($refs === false) return fs_db_error();
	$rr = array();

	$res ="<div id='referrers_tree_id'>\n";
	if (!$refs) // no data yet
	{
		$res .= fs_r('No data yet, go get some hits');
	}
	else
	{
		foreach($refs as $r)
		{
			$referer = $r->referer;
			$count = $r->refcount;
			$p = @parse_url($referer);
			$host = isset($p['host']) ? $p['host'] : fs_r('Unknown');
			if(!isset($rr[$host]['last_referer'])) $rr[$host]['last_referer'] = $referer;
			fs_ensure_initialized($rr[$host]['count']);		

			$rr[$host]['count'] += $count;
			$rr[$host]['list'][$referer]['url'] = $referer;
			$rr[$host]['list'][$referer]['count'] = $count;
		}

		foreach ($rr as $host=>$site)
		{
			$last_url = fs_xmlentities(urldecode($site['last_referer']));
			$last = substr($last_url,0,80);
			if(strlen($last) != strlen($last_url)) $last .= "...";

			$site_count = $site['count'];
			$list = $site['list'];
			$num_urls = count($list);
			if ($num_urls > 1)
			{
				$title_line = "$last <b dir='ltr'>".sprintf(fs_r("(%d hits) (%d more from %s)"),$site_count,$num_urls-1,$host)."</b>";
				$res .= "<ul class='mktree'>\n";
			}
			else
			{
				$title_line = "$last <b dir='ltr'>(".sprintf(fs_r("%d hits"),$site_count).")</b>";
				$res .= "<ul>";
			}

			$res .= "<li><a target='_blank' title='$last_url' href='$last_url'>$title_line</a>\n";

			if ($num_urls > 1)
			{
				$res .= "	<ul>";
				$first = true;
				foreach($list as $ref)
				{	
					if ($first) {$first = false;continue;}
					$count = $ref['count'];
					$url = fs_xmlentities(urldecode($ref['url']));
					$line = substr($url,0,80);
					if(strlen($url) != strlen($line)) $line .= "...";
					$title = sprintf(fs_r('%d hits from %s'),$count,$url);
					$res .= "
		<li>
			<a href='$url' title='$title' target='_blank'> 
				$line<b dir='ltr'>($count)</b>
			</a>
		</li>\n";
				}	
				$res .= "	</ul>\n";
			}
			$res .= "</li>\n";
			$res .= "</ul>\n";
		}
	}
	$res .= "</div>\n";

	return $res;
}

function fs_get_popular_pages_tree($max_num = null, $days_ago = null)
{
	if (!$max_num)
	{
		 $max_num = fs_get_max_popular_num();
	}

	if (!$days_ago)
	{
		 $days_ago = fs_get_recent_popular_pages_days_ago();
	}
	
	
	$urls = fs_get_popular_pages($max_num, $days_ago);
	if($urls === false) return fs_db_error();

	$res = "<div>\n";
	if (!$urls) // no data yet
	{
		$res .= fs_r('No data yet, go get some hits');
	}
	else
	{
		$res .= "	<ul>";
		foreach($urls as $r)
		{
			$url = $r->url;
			$count = $r->c;
			$rr = array();
			fs_ensure_initialized($rr[$url]['count']);		
			$url = fs_xmlentities(urldecode($url));
			$url_text = substr($url,0,80);
			if(strlen($url_text) != strlen($url)) $url_text .= "...";
			$res .= "
		<li>
			<a target='_blank' title='$url' href='$url'>$url_text</a><b>($count)</b>
		</li>\n";
		}
		$res .= 
"	</ul>\n";
	}
	$res .= "</div>\n";

	return $res;
}

function fs_get_countries_list()
{
	$countries = fs_get_country_codes_percentage(fs_get_max_countries_num(), fs_countries_list_days_ago());
	if($countries === false) return fs_db_error();
	$res = '';

	if (count($countries) == 0 )
	{
		$res .= fs_r('No data yet, go get some hits');
	}
	else
	{
		$res = "<ul>";
		foreach($countries as $country)
		{
			$name = $country->name;
			$flag = $country->img;
			$percentage = sprintf("%.2F", $country->percentage);
			$res .= "<li>$flag $name <b>$percentage%</b></li>\n";
		}
		$res .= "</ul>";
	}
	return $res;
}
?>

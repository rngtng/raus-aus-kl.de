<?php
require_once(dirname(__FILE__).'/init.php');
require_once(dirname(__FILE__).'/db-common.php');
require_once(dirname(__FILE__).'/utils.php');
require_once(dirname(__FILE__).'/db-config-utils.php');

function fs_register_site()
{
	$fsdb = &fs_get_db_conn();
	$sites = fs_sites_table();
	$sql = "INSERT INTO `$sites`
			 ( `id` , `type` , `name` ) VALUES   (NULL , '0', '')";
	$r = $fsdb->query($sql);
	if ($r === false)
	{
		return false;
	}

	return $fsdb->insert_id;
}

function fs_create_new_site($new_sid, $name, $type)
{
	if (empty($name)) return fs_r('Site name not specified');
	$fsdb = &fs_get_db_conn();
	$sites = fs_sites_table();

	if ($new_sid == 'auto')
	{
		$newSite = true;
		$new_sid = fs_register_site();
		if ($new_sid === false) return fs_db_error();
	}
	else
	{
		if (!is_numeric($new_sid) || (int)($new_sid) <= 0) return fs_r('Site ID must be a positive number');
		$exists = fs_site_exists($new_sid);
		if ($exists === null) return fs_db_error();
		if ($exists === true) return sprintf(fs_r("A site with the ID %s already exists"),$new_sid);
	}

	$new_sid = $fsdb->escape($new_sid);
	$type = $fsdb->escape($type);
	$name = $fsdb->escape($name);
	$sql = "REPLACE INTO `$sites` (`id`,`type`,`name`) VALUES ($new_sid,$type,$name)";
	$r = $fsdb->query($sql);
	if ($r === false) return fs_db_error();
	return true;
}

function fs_update_site_params($new_sid,$orig_sid, $name,$type)
{
	if (empty($name)) return fs_r('Site name not specified');
	if (empty($orig_sid)) return "Uspecified site id";

	$changing_sid = $new_sid != $orig_sid;
	if (!is_numeric($new_sid) || (int)($new_sid) <= 0) return fs_r('Site ID must be a positive number');

	$fsdb = &fs_get_db_conn();
	$sites = fs_sites_table();

	$exists = fs_site_exists($orig_sid);
	if ($exists === null) return fs_db_error();
	if ($exists === false) return sprintf(fs_r("No site with the id %s exists"),$new_sid);

	if ($fsdb->query("START TRANSACTION") === false) return fs_db_error();


	if ($changing_sid)
	{
		$exists = fs_site_exists($new_sid);
		if ($exists === null) return fs_db_error();
		if ($exists === true) return sprintf(fs_r("A site with the ID %s already exists"),$new_sid);

		$r = fs_transfer_site_hits($orig_sid, $new_sid);
		if ($r === false) return fs_db_error();
	}

	$orig_sid = $fsdb->escape($orig_sid);
	$new_sid = $fsdb->escape($new_sid);
	$type = $fsdb->escape($type);
	$name = $fsdb->escape($name);

	$sql = "UPDATE `$sites` SET `type` = $type, `name` = $name, `id` = $new_sid WHERE `id` = $orig_sid";
	$r = $fsdb->query($sql);
	if ($r === false) return fs_db_error();
	if($fsdb->query("COMMIT") === false) return fs_db_error();
	return true;
}

function fs_site_exists($site_id)
{
	$fsdb = &fs_get_db_conn();
	$sites = fs_sites_table();
	$site_id = $fsdb->escape($site_id);
	$sql = "SELECT count(*) FROM `$sites` WHERE `id` = $site_id";
	$r = $fsdb->get_var($sql);
	if ($r === false)
	{
		return null;
	}
	return $r != "0";
}

function fs_delete_site($site_id, $action, $new_sid)
{
	if (empty($site_id)) return "Uspecified site id";
	$fsdb = &fs_get_db_conn();
	$sites = fs_sites_table();
	$hits = fs_hits_table();
	if ($fsdb->query("START TRANSACTION") === false) return fs_db_error();

	$exists = fs_site_exists($site_id);
	if ($exists === null) return fs_db_error();
	if ($exists === false) return sprintf(fs_r("No site with the id %s exists"),$site_id);

	if ($action == "delete")
	{
		$id = $fsdb->escape($site_id);
		$sql = "DELETE FROM `$hits` WHERE site_id = $id";
		$r = $fsdb->query($sql);
		if ($r === false) return fs_db_error();
	}
	else
	if ($action == "change")
	{
		if (empty($new_sid)) return "New site_id must not be empty";

		$exists = fs_site_exists($new_sid);
		if ($exists === null) return fs_db_error();
		if ($exists === false) return sprintf(fs_r("No site with the id %s exists"),$new_sid);

		$r = fs_transfer_site_hits($site_id, $new_sid);
		if ($r === false) return fs_db_error();
	}
	else
	{
		return "Unknown action $action";
	}
	$id = $fsdb->escape($site_id);
	$sql = "DELETE FROM `$sites` WHERE `id` = $id";
	$r = $fsdb->query($sql);
	if ($r === false) return fs_db_error();

	if($fsdb->query("COMMIT") === false) return fs_db_error();
	return true;
}

function fs_transfer_site_hits($old_sid, $new_sid)
{
	$fsdb = &fs_get_db_conn();
	$hits = fs_hits_table();
	$sql = "UPDATE `$hits` SET `site_id` = '$new_sid' WHERE `site_id` = $old_sid";
	return $fsdb->query($sql);
}

function fs_get_orphan_site_ids()
{
	$fsdb = &fs_get_db_conn();
	$hits = fs_hits_table();
	$sites = fs_sites_table();
	$sql = "SELECT DISTINCT `site_id` AS `id` FROM `$hits` WHERE `site_id` NOT IN (SELECT `id` FROM `$sites`)";
	return $fsdb->get_results($sql,ARRAY_A);
}

// adds an ip address to exclude.
// returns an error message, or an empty string if okay.
function fs_add_excluded_ip($ip)
{
	$fsdb = &fs_get_db_conn();
	$v = ip2long($ip);
	if ($v == false || $v == -1)
	{
		return sprintf(fs_r("Invalid IP address: %s"),$ip);
	}
	else
	{
		$ip = $fsdb->escape($ip);
		$ips = fs_excluded_ips_table();
		if ($fsdb->query("START TRANSACTION") === false) return fs_db_error();
		$sql = "SELECT DISTINCT ip FROM `$ips` WHERE `ip` LIKE $ip";
		$r = $fsdb->get_var($sql);
		if($r === false) return fs_db_error();
		if ($r != null)
		{
			return sprintf(fs_r("The IP address %s is already in the database"),$ip);
		}
		$sql = "INSERT INTO `$ips` (`id`, `ip`) VALUES (NULL, $ip)";
		if($fsdb->query($sql) === false)
		{
			return fs_db_error();
		}
		else
		{
			$hits = fs_hits_table();
			$sql = "UPDATE `$hits` SET `excluded_ip`='1' WHERE `ip`=$ip";
			if($fsdb->query($sql)===false) return fs_db_error();
			if($fsdb->query("COMMIT") === false) return fs_db_error();
			return "";
		}
	}
}

function fs_remove_excluded_ip($ip)
{
	$fsdb = &fs_get_db_conn();

	// this is a bit nasty, but it let us return a resonable error when the stupid user try to remove the 'empty' string.
	$v = ip2long($ip);
	if ($v == false || $v == -1)
	return sprintf(fs_r("Invalid IP address: %s"),$ip);

	$exip = fs_excluded_ips_table();
	$hits = fs_hits_table();
	$ip = $fsdb->escape($ip);

	if ($fsdb->query("START TRANSACTION") === false)
	return fs_db_error();

	if($fsdb->query("DELETE from `$exip` WHERE ip = $ip") === false)
	{
		return fs_db_error();
	}
	else
	{
		if ($fsdb->query("UPDATE `$hits` SET `excluded_ip`='0' WHERE `ip`=$ip") === false)
		return fs_db_error();
		if ($fsdb->query("COMMIT") === false)
		return fs_db_error();
		return "";
	}

}


function fs_add_bot($wildcard1, $fail_if_exists = true)
{
	$fsdb = &fs_get_db_conn();
	$wildcard = $fsdb->escape(trim($wildcard1));
	$bots_table = fs_bots_table();
	$hits_table = fs_hits_table();
	$ua_table = fs_useragents_table();

	// check for duplicate wildcard
	if ($fsdb->get_var("SELECT DISTINCT wildcard FROM `$bots_table` WHERE `wildcard` = $wildcard") != null)
	{
		if ($fail_if_exists) return sprintf(fs_r("The bot wildcard %s is already in the database"),$wildcard);
		else return "";
	}

	if ($fsdb->query("START TRANSACTION") === false) return fs_db_error();
	// insert wildcard to table
	if ($fsdb->query("INSERT INTO `$bots_table` (`wildcard`) VALUES ($wildcard)") === false)
	{
		return fs_db_error();
	}
	else
	{
		$search_wildcard = $fsdb->escape(trim($wildcard1));
		if ($fsdb->query("UPDATE `$ua_table`
			SET match_bots=match_bots+1 
			WHERE useragent REGEXP $search_wildcard") === false)
		{
			return fs_db_error();
		}
		if ($fsdb->query("COMMIT") === false) return fs_db_error();
		return "";
	}
}

function fs_remove_bot($bot_id)
{
	$fsdb = &fs_get_db_conn();
	$bot_id = $fsdb->escape($bot_id);
	$bots_table = fs_bots_table();
	$ua_table = fs_useragents_table();
	if ($fsdb->query("START TRANSACTION") === false) return fs_db_error();

	$wildcard = $fsdb->get_var("SELECT `wildcard` FROM `$bots_table` WHERE `id`='$bot_id'");
	if ($wildcard === false) return fs_db_error();
	$wildcard = $fsdb->escape($wildcard);
	if ($fsdb->query("UPDATE `$ua_table`  SET match_bots=match_bots-1 WHERE useragent REGEXP $wildcard") === false)
	{
		return fs_db_error();
	}

	if ($fsdb->query("DELETE from `$bots_table` WHERE `id` = '$bot_id'") === false) return fs_db_error();
	if ($fsdb->query("COMMIT") === false) return fs_db_error();
	return "";
}

function fs_clear_bots_list()
{
	$res = fs_get_bots();
	if ($res)
	{
		foreach($res as $r)
		{
			$id = $r['id'];
			$res1 = fs_remove_bot($id);
			if ($res1 != '') return $res1;
		}
	}
	return '';
}

function fs_get_unique_hit_count($days_ago = NULL)
{
	$fsdb = &fs_get_db_conn();
	if ($days_ago != null) $days_ago = $fsdb->escape($days_ago);
	$hits = fs_hits_table();
	$ua = fs_useragents_table();
	$not_excluded = not_excluded();

	if (ver_comp("4.1.0",fs_mysql_version()) > 0)
	{
		$sql = "SELECT DATE_FORMAT(`timestamp`,'%Y-%m-%d') date, ip, count(*) c from `$hits` h,`$ua` u WHERE h.useragent_id=u.id AND $not_excluded";
		if ($days_ago)
		{
			$sql .= " AND timestamp >= DATE_SUB(NOW(), INTERVAL ".($days_ago*24)." HOUR)";
			$sql .= "";
		}
		$sql.= " group by ip,date";
		$res = $fsdb->get_results($sql);
		if ($res === false) return fs_db_error();
		return count($res);
	}
	else
	{
		$sql = "SELECT COUNT(u.c) FROM (SELECT DATE(`timestamp`) date, ip, count(*) c from `$hits` h,`$ua` u WHERE h.useragent_id=u.id AND $not_excluded";
		if ($days_ago)
		{
			$sql .= " AND timestamp >= DATE_SUB(NOW(), INTERVAL ".($days_ago*24)." HOUR)";
			$sql .= "";
		}
		$sql.= " group by ip,date) as u";
		$res = $fsdb->get_var($sql);
		if ($res === false) return fs_db_error();
		return $res;
	}
}

function fs_get_num_excluded()
{
	$fsdb = &fs_get_db_conn();
	$hits = fs_hits_table();
	$ua = fs_useragents_table();
	$not_excluded = not_excluded(false);
	$sql = "SELECT COUNT(ip) FROM `$hits` h,`$ua` u WHERE h.useragent_id=u.id AND NOT ($not_excluded)";
	$res = $fsdb->get_var($sql);
	if ($res === false) return fs_db_error();
	return $res;
}

function fs_get_hit_count($days_ago = NULL, $site_id = null)
{
	$fsdb = &fs_get_db_conn();
	if ($days_ago != null) $days_ago = $fsdb->escape($days_ago);
	$hits = fs_hits_table();
	$ua = fs_useragents_table();
	
	if (!is_numeric($site_id))
	{
		$not_excluded = not_excluded();
	}
	else
	{
		$not_excluded = not_excluded($site_id);
	}
	
	$sql = "SELECT COUNT(ip) FROM `$hits` h,`$ua` u WHERE h.useragent_id=u.id AND $not_excluded";
	if ($days_ago)
	{
		$sql .= " AND timestamp >= DATE_SUB(NOW(), INTERVAL ".($days_ago*24)." HOUR)";
	}

	$res = $fsdb->get_var($sql);
	if ($res === false) return fs_db_error();
	return $res;
}

function fs_not_filtered()
{
	$fsdb = &fs_get_db_conn();
	$arr = array();
	$arr['ht_ip_filter'] 	= 'ip';
	$arr['ht_url_filter'] 	= 'url';
	$arr['ht_referrer_filter'] 	= 'referer';
	$arr['ht_useragent_filter'] = 'useragent';
	$res = "";
	foreach($arr as $k=>$v)
	{
		$param = fs_get_option($k);
		if (!empty($param))
		{
			$param = $fsdb->escape($param);
			$cond = "`$v` REGEXP $param";
			if ($res == "") $res = $cond;
			else
			$res .= " AND $cond";
		}
	}

	if ($res == "")
	$res = "1";

	return $res;
}

/**
 * returns a query string to match currently not excluded hits.
 * $exclude_by_site : 
 * 	true to exclude all sites but the one in the sites_filter option.
 * 	false to include all sites.
 * 	a specific number to exclude all other sites (number is site id to include).
 */
function not_excluded($exclude_by_site = true)
{
	$and_site_ex = "";
	if (is_numeric($exclude_by_site))
	{
		$and_site_ex = "AND `site_id` = '$exclude_by_site'";
	}
	else
	{
		if ($exclude_by_site)
		{
			$site = fs_get_local_option('sites_filter','all');
			if ($site != 'all')
			{
				$and_site_ex = "AND `site_id` = '$site'";
			}
		}
	}

	return "`excluded_ip` = '0'
			AND `excluded_by_user` = '0' 
			AND `excluded_user` = '0' 
			AND `match_bots`='0'
			$and_site_ex";
}

function fs_purge_excluded_entries()
{
	$fsdb = &fs_get_db_conn();
	$hits = fs_hits_table();
	$ua = fs_useragents_table();
	$not_excluded = not_excluded(false);
	$sql = "DELETE `$hits` FROM `$hits` ,`$ua` u WHERE $hits.useragent_id=u.id AND NOT ($not_excluded)";
	$res = $fsdb->get_var($sql);
	return $res;
}

# Fetches entries in DB
function fs_getentries()
{
	$amount = fs_get_option('firestats_num_entries_to_show',50);
	$timezone = fs_get_option('user_timezone','system');
	$db_support_tz = (ver_comp("4.1.3",fs_mysql_version()) <= 0);

	$ts = $db_support_tz && $timezone != 'system' ? "CONVERT_TZ(`timestamp`,'system','$timezone')" : "timestamp";
	if ($amount === false) return false;

	$hits = fs_hits_table();
	$ua = fs_useragents_table();
	$urls = fs_urls_table();
	$referers = fs_referers_table();
	$not_excluded = not_excluded();
	$not_filtered = fs_not_filtered();
	$sql = "SELECT hits.id,ip,useragent,referer,url,$ts as timestamp,country_code
					FROM `$hits` AS hits,`$ua` AS agents,`$urls` AS urls,`$referers` AS referers
					WHERE 
						hits.useragent_id = agents.id AND 
						hits.url_id = urls.id AND 
						hits.referer_id = referers.id 
						AND $not_excluded 
						AND $not_filtered
					ORDER BY timestamp DESC";
	$sql .= " LIMIT $amount";

	$fsdb = &fs_get_db_conn();
	return $fsdb->get_results($sql);
}

function fs_get_excluded_ips()
{
	$fsdb = &fs_get_db_conn();
	return $fsdb->get_results("SELECT ip from ".fs_excluded_ips_table(), ARRAY_A);
}

function fs_get_bots()
{
	$fsdb = &fs_get_db_conn();
	return $fsdb->get_results("SELECT id,wildcard from ".fs_bots_table(). " ORDER BY wildcard", ARRAY_A);
}

function fs_ensure_initialized(&$x)
{
	if (!isset($x)) $x = 0;
}

function fs_group_others($list)
{
	$MIN = 2;
	$others = array();
	$others['name'] = 'Others'; // not translated, cause tree layout problems with hebrew
	$others['image'] = fs_pri_get_image_url('others', 'Others');
	$others['count'] = 0;
	$others['percent'] = 0;
	foreach ($list as $code=>$data)
	{
		if ($data['percent'] < 2)
		{
			$others['count'] += $data['count'];
			$others['percent'] += $data['percent'];
			$others['sublist'][$code]=$data;
			unset($list[$code]);
		}
	}
	if ($others['count'] > 0)
	{
		$list['others'] = $others;
	}
	return $list;
}


function fs_get_useragents_count($days_ago = NULL)
{
	$hits = fs_hits_table();
	$useragents = fs_useragents_table();
	$not_excluded = not_excluded();
	$sql = "SELECT DISTINCT useragent,useragent_id,count(useragent_id) AS c
			FROM `$hits` hits JOIN `$useragents` agents 
			WHERE hits.useragent_id = agents.id AND $not_excluded";

	if ($days_ago)
	{
		$sql .= " AND timestamp >= DATE_SUB(NOW(), INTERVAL ".($days_ago*24)." HOUR)";
	}
	$sql .= " GROUP BY `useragent` ORDER BY c DESC";

	$fsdb = &fs_get_db_conn();
	$results = $fsdb->get_results($sql,ARRAY_A);
	if ($results === false) return false;
	return $results;
}

function fs_get_site($id)
{
	$fsdb = &fs_get_db_conn();
	$sites = fs_sites_table();
	$id = $fsdb->escape($id);
	$sql = "SELECT * FROM $sites WHERE id=$id";
	return $fsdb->get_row($sql,ARRAY_A);
}

function fs_get_sites($id = null)
{
	$fsdb = &fs_get_db_conn();
	$sites = fs_sites_table();
	$sql = "SELECT * FROM $sites";
	return $fsdb->get_results($sql,ARRAY_A);
}

function fs_stats_sort($stats_data)
{
	$foo = create_function('$a, $b', 'return $b["count"] - $a["count"];');
	uasort($stats_data,$foo);
	$size=count($stats_data);

	foreach($stats_data as $key=>$value)
	{
		$ar = $value['sublist'];
		if ($ar != NULL)
		{
			uasort($ar, $foo);
		}
	}
	return $stats_data;
}

// TODO:
// use classes here for the tree model. this is getting silly.
function fs_get_os_statistics($days_ago = NULL)
{
	$results = fs_get_useragents_count($days_ago);
	if ($results !== false && count($results) > 1)
	{
		$total = 0;
		foreach ($results as $r)
		{
			$total += $r['c'];
		}

		foreach ($results as $r)
		{
			$ua = $r['useragent'];
			$count = $r['c'];

			$a = fs_pri_detect_browser($ua);
			$os_name 	= $a[3];$os_code 	= $a[4];$os_ver		= $a[5];

			$os_img = fs_pri_get_image_url($os_code != '' ? $os_code : 'unknown', $os_name);

			fs_ensure_initialized($os[$os_code]['count']);
			fs_ensure_initialized($os[$os_code]['sublist'][$os_ver]['count']);

			// operating systems
			$os[$os_code]['name']=$os_name != '' ? $os_name : fs_r('Unknown');
			$os[$os_code]['image']=$os_img;
			$os[$os_code]['count'] += (int)$count;
			$os_total = $os[$os_code]['count'];
			$os[$os_code]['percent'] = (float)($os_total / $total) * 100;
			$os[$os_code]['sublist'][$os_ver]['count'] += (int)$count;
			$os_ver_count = $os[$os_code]['sublist'][$os_ver]['count'];
			$os[$os_code]['sublist'][$os_ver]['percent'] = (float)($os_ver_count / $total) * 100;
			$os[$os_code]['sublist'][$os_ver]['useragent'] = $ua;
			$os[$os_code]['sublist'][$os_ver]['name'] = $os_name;
			$os[$os_code]['sublist'][$os_ver]['image'] = $os_img;
		}
		return fs_stats_sort(fs_group_others($os));
	}
	else
	{
		return null;
	}
}

// TODO:
// use classes here for the tree model. this is getting silly.
function fs_get_browser_statistics($days_ago = NULL)
{
	$results = fs_get_useragents_count($days_ago);
	if ($results !== false && count($results) > 1)
	{
		$total = 0;
		foreach ($results as $r)
		{
			$total += $r['c'];
		}

		foreach ($results as $r)
		{
			$ua = $r['useragent'];
			$count = $r['c'];

			$a = fs_pri_detect_browser($ua);
			$br_name 	= $a[0];$br_code 	= $a[1];$br_ver		= $a[2];

			$br_img = fs_pri_get_image_url($br_code != '' ? $br_code : 'unknown', $br_name);

			fs_ensure_initialized($br[$br_code]['count']);
			fs_ensure_initialized($br[$br_code]['sublist'][$br_ver]['count']);

			$br[$br_code]['name'] = $br_name != '' ? $br_name : fs_r('Unknown');
			$br[$br_code]['image'] = $br_img;

			// browsers
			$br[$br_code]['count'] += (int)$count;
			$browser_total = $br[$br_code]['count'];
			$br[$br_code]['percent'] = (float)($browser_total / $total) * 100;
			$br[$br_code]['sublist'][$br_ver]['count'] += (int)$count;
			$br_ver_count = $br[$br_code]['sublist'][$br_ver]['count'];
			$br[$br_code]['sublist'][$br_ver]['percent'] = (float)($br_ver_count / $total) * 100;
			$br[$br_code]['sublist'][$br_ver]['useragent'] = $ua;
			$br[$br_code]['sublist'][$br_ver]['name'] = $br_name;
			$br[$br_code]['sublist'][$br_ver]['image'] = $br_img;
		}

		return fs_stats_sort(fs_group_others($br));
	}
	else
	{
		return null;
	}
}

function fs_save_excluded_users($list)
{
	$fsdb = &fs_get_db_conn();
	$hits = fs_hits_table();
	if($fsdb->query("START TRANSACTION") === false) return fs_db_error();
	$sql = "UPDATE `$hits` SET `excluded_user`=IF(`user_id` IS NOT NULL AND `user_id` in (".($list ? $list : 'NULL')."),'1','0')";
	if($fsdb->query($sql) === false) return fs_db_error();
	if(fs_update_local_option('excluded_users', $list) === false) return fs_db_error();
	if($fsdb->query("COMMIT") === false) return fs_db_error();
}

function fs_get_recent_referers($max_limit, $days_ago = null)
{
	$fsdb = &fs_get_db_conn();
	$max_limit = $fsdb->escape($max_limit);
	$hits = fs_hits_table();
	$referers = fs_referers_table();
	$ua = fs_useragents_table();
	$not_excluded = not_excluded();

	$sql = "SELECT referer,MAX(timestamp) ts,count(referer) refcount
					FROM `$hits` h,`$ua` ua,`$referers` r 
					WHERE h.referer_id = r.id AND h.useragent_id = ua.id
					AND $not_excluded AND referer != 'unknown'";

	if ($days_ago)
	{
		$sql .= " AND timestamp >= DATE_SUB(NOW(), INTERVAL ".($days_ago*24)." HOUR)";
	}

	$sql .= " GROUP BY referer ORDER BY ts DESC".($max_limit ? " LIMIT $max_limit" : "");
	return $fsdb->get_results($sql);
}

function fs_get_popular_pages($num_limit, $days_ago = null)
{
	$fsdb = &fs_get_db_conn();
	$num_limit = $fsdb->escape($num_limit);
	$hits = fs_hits_table();
	$urls = fs_urls_table();
	$ua = fs_useragents_table();
	$not_excluded = not_excluded();

	$sql = "SELECT url, count(url) c
					FROM `$hits` h,`$ua` ua,`$urls` u 
					WHERE h.url_id = u.id AND h.useragent_id = ua.id
					AND $not_excluded AND url != 'unknown'";
	if ($days_ago)
	{
		$sql .= " AND timestamp >= DATE_SUB(NOW(), INTERVAL ".($days_ago*24)." HOUR)";
	}

	$sql .= " GROUP BY `url` ORDER BY c DESC".($num_limit ? " LIMIT $num_limit" : "");
	return $fsdb->get_results($sql);
}

function fs_get_country_codes($days_ago = null)
{
	$fsdb = &fs_get_db_conn();
	$hits = fs_hits_table();
	$ua = fs_useragents_table();
	$not_excluded = not_excluded();

	$sql = "SELECT `country_code`, count(`country_code`) c
					FROM `$hits` h,`$ua` ua
					WHERE ua.id = h.useragent_id AND 
						$not_excluded AND 
						`country_code` IS NOT NULL 
						AND `country_code` != ''";
	if ($days_ago)
	{
		$sql .= " AND timestamp >= DATE_SUB(NOW(), INTERVAL ".($days_ago*24)." HOUR)";
	}

	$sql .= " GROUP BY `country_code` ORDER BY c DESC";
	return $fsdb->get_results($sql);
}


function fs_get_country_codes_percentage($num_limit, $days_ago)
{
	require_once(dirname(__FILE__).'/ip2country.php');
	$codes = fs_get_country_codes($days_ago);
	if ($codes === false) return false;
	if (count($codes) == 0) return array();

	$total = 0;
	foreach ($codes as $code)
	{
		$total += $code->c;
	}

	$t = 0;
	$res = array();
	$tp = 0;
	foreach ($codes as $code)
	{
		if ($t == $num_limit) break;
		$t++;
		$percentage = $code->c / (float)$total * 100;
		$code->percentage = $percentage;
		$code->name = fs_get_country_name($code->country_code);
		$code->img = fs_get_country_flag_url($code->country_code);
		$res[] = $code;
		$tp += $percentage;
	}

	if ($tp < 100)
	{
		$last = new stdClass;
		$last->percentage = 100 - $tp;
		$last->name = fs_r('Others');
		$last->img = fs_get_flag_img_tag($last->name, fs_url("img/others.png"));
		$res[] = $last;
	}

	return $res;
}


/**
	store some usage FireStats usage information
	this can be used for several things, like asking a wordpress user to post a few words about FireStats in his blog, to help spread the word.
	*/
function fs_maintain_usage_stats()
{
	$first_run_time = fs_get_option('first_run_time');
	if (!$first_run_time)
	{
		fs_update_option('first_run_time',time());
	}

	$firestats_id = fs_get_option('firestats_id');
	if (!$firestats_id)
	{
		fs_update_option('firestats_id',mt_rand());
	}
}


function fs_db_error()
{
	$fsdb = &fs_get_db_conn();
	return sprintf(fs_r('Database error: %s'), $fsdb->last_error).'<br/>'.
	sprintf('SQL: %s', $fsdb->last_query);
}


function fs_get_users()
{
	if (!fs_in_wordpress())
	{
		echo "not in wp";
		return array(); // currently users are only suppored when installed under wordpress
	}
	$wpdb =& $GLOBALS['wpdb'];
	$sql = "SELECT ID,display_name FROM $wpdb->users";
	$users = $wpdb->get_results($sql,ARRAY_A);
	if ($users === false) return false;
	foreach($users as $u)
	{
		$res[] = array('id'=>$u['ID'],'name'=>$u['display_name']);
	}
	return $res;
}

function fs_botlist_import_url($url, $remove_existing)
{
	$error = '';
	$data = fs_fetch_http_file($url, $error);
	if (!empty($error)) return $error;
	return fs_botlist_import_array(explode("\n",$data), $remove_existing);

}

function fs_botlist_import($file, $remove_existing)
{
	$lines = @file($file);
	if ($lines === false) return sprintf(fs_r('Error opening file : %s'),"<b>$file</b>");
	return fs_botlist_import_array($lines, $remove_existing);
}

function fs_botlist_import_array($lines, $remove_existing)
{
	if ($remove_existing)
	{
		$res = fs_clear_bots_list();
		if ($res != '')
		{
			return $res;
		}
	}

	foreach($lines as $line)
	{
		$l = trim($line);
		if (strlen($l) > 0 && $l[0] != '#')
		{
			$ok = fs_add_bot($line, false);
			if ($ok != '') return $ok;
		}
	}
	return '';
}

?>

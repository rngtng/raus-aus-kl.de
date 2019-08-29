<?php
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/utils.php');
require_once(dirname(__FILE__).'/db-config-utils.php');

function &fs_get_db_conn($force_new = false,$clear = false)
{
	static $fsdb;
	if ($clear) 
	{
		unset($fsdb);
	}
	else
	{
		if (!isset($fsdb) || $force_new)
		{
			fs_load_config();
			global $fs_config;
			require_once dirname(__FILE__)."/ezsql/shared/ez_sql_core.php";
			require_once dirname(__FILE__)."/ezsql/mysql/ez_sql_mysql.php";
			$fsdb = fs_create_db_conn(	$fs_config['DB_USER'], 
					$fs_config['DB_PASS'], 
					$fs_config['DB_NAME'], 
					$fs_config['DB_HOST']);
		}
	}

	return $fsdb;
}

function fs_create_db_conn($user, $pass, $dbname, $dbhost)
{
	$conn = new fs_ezSQL_mysql($user,$pass,$dbname,$dbhost);
	$conn->hide_errors();
	$conn->connect();
	return $conn;
}


function fs_get_db_status($fsdb = null)
{
	if (fs_get_db_config_type() == FS_DB_CONFIG_UNAVAILABLE)
	{
		return array('status'=>FS_DB_NOT_CONFIGURED,'ver'=>0);
	}

	if (!$fsdb)
	{
		$fsdb = &fs_get_db_conn();
	}
	if (!$fsdb)
	{
		return array('status'=>FS_DB_NOT_INSTALLED,'ver'=>0);
	}

	if (!$fsdb->is_connected())
	{
		return array('status'=>FS_DB_CONNECTION_ERROR,'ver'=>0);
	}

  	$version_table = fs_version_table();
  	$sql = "SHOW TABLES LIKE '$version_table'";
	
  	$results = $fsdb->query($sql);
  	if ($results === FALSE)
  	{
  		return array('status'=>FS_DB_GENERAL_ERROR,'ver'=>0);
	}

	if ($results == 0)
		return array('status'=>FS_DB_NOT_INSTALLED,'ver'=>0);
	
	$ver = (int)$fsdb->get_var("SELECT `version` FROM `$version_table`");
	if ($ver == 0)
 	{
 		return array('status'=>FS_DB_NOT_INSTALLED,'ver'=>0);
	}
	else
	if ($ver == FS_REQUIRED_DB_VERSION)
	{
		return array('status'=>FS_DB_VALID,'ver'=>$ver);
	}
	else
	if ($ver < FS_REQUIRED_DB_VERSION)
	{
		return array('status'=>FS_DB_NEED_UPGRADE,'ver'=>$ver);
	}
	else
	if ($ver > FS_REQUIRED_DB_VERSION)
	{
		return array('status'=>FS_DB_IS_NEWER_THAN_CODE,'ver'=>$ver);
	}

	die('Logic is broken, life sucks');
}

function fs_db_valid()
{
	$db = fs_get_db_status();
	$res = $db['status'] == FS_DB_VALID;
	return $res;
}

function fs_get_database_status_message()
{
	$fsdb = &fs_get_db_conn();
	$db_status_array = fs_get_db_status($fsdb);
	$db_status = $db_status_array['status'];

	$msg = '';
	switch ($db_status)
	{
		case FS_DB_VALID:
			$msg = fs_r('FireStats is properly installed in the database');
		break;
		case FS_DB_NOT_CONFIGURED:
			$msg = fs_r('FireStats is not configured');
		break;
		case FS_DB_GENERAL_ERROR:
			$msg = fs_r('Database error, check your configuration');
		break;
		case FS_DB_NOT_INSTALLED:
			$msg = fs_r('FireStats is not installed in the database');
		break;
		case FS_DB_NEED_UPGRADE:
			$msg = fs_r('FireStats database need to be upgraded');
		break;
		case FS_DB_IS_NEWER_THAN_CODE:
			$msg = fs_r('The FireStats database version is newer this code version, you need to upgrade FireStats');
		break;
		case FS_DB_CONNECTION_ERROR:
			$msg = fs_r('Error connecting to database');
		break;
		default:
			$msg = fs_r('Unknown database status code');
	}
	return $msg;
}

$fs_mysql_version = null;
function fs_mysql_version()
{
	$fsdb = &fs_get_db_conn();
	global $fs_mysql_version;
	if (!isset($fsdb))
	{		
		return false;
	}
	else
	{
		if ($fs_mysql_version == null)
		{
			$fs_mysql_version = $fsdb->get_var("select version()");
			if ($fs_mysql_version == null)
			{
				return false;
			}
		}

		return $fs_mysql_version;
	}
}

if (!isset($GLOBALS['fs_options_cache']))
	$GLOBALS['fs_options_cache'] = array();

function fs_get_option($key, $default=null)
{
	global $options_cache;
	if (isset($options_cache[$key]))
	{
		return $options_cache[$key];
	}
	else
	{
		$fsdb = &fs_get_db_conn();
		if (!$fsdb->is_connected()) trigger_error('Database not connected');
		$key = $fsdb->escape($key);
		$options_table = fs_options_table();
		$sql = "SELECT `option_value` FROM `$options_table` WHERE `option_key`=$key"; 
		$val = $fsdb->get_var($sql);
		if (!($val === null))
		{
			$options_cache[$key] = $val;
		}

		if ($val === null && $default) $val = $default;
		return $val;
	}
}

function fs_update_option($key, $value)
{
	global $options_cache;
	if (isset($options_cache[$key]) && $options_cache[$key] == $value) return; // nothing to do, already in cache.
	$fsdb = &fs_get_db_conn();
	if (!$fsdb->is_connected()) trigger_error('Database not connected');
	$key = $fsdb->escape($key);
	$value = $fsdb->escape($value);
	$options_table = fs_options_table();
	$res = $fsdb->query("REPLACE INTO `$options_table` (`option_key`,`option_value`) VALUES($key,$value)") !== false;
	if ($res) $options_cache[$key] = $value;
	return $res;
}

function fs_get_local_options_list()
{
	static $fs_local_options_list;
	if (!isset($fs_local_options_list))
	{
		// a list of local keys that we are allowed to save into the hosting system.
		// this is the last line of defense againt hack attempts trying to save crap to the hosting platform.
		$fs_local_options_list = array();
		$fs_local_options_list[] = 'sites_filter';
		$fs_local_options_list[] = 'excluded_users';
		$fs_local_options_list[] = 'site_id';
	}
	return $fs_local_options_list;
}

// if we are in the context of site (like when viewing from within wordpress) 
// save the value in the storage system of that site
// else use firestats options storage.
function fs_update_local_option($key, $value)
{
	$fs_local_options_list = fs_get_local_options_list();
	if (!in_array($key, $fs_local_options_list))
	{
		echo "Not allowed to store ".$key;
		return;
	}

	if(function_exists('fs_update_local_option_impl'))
	{
		fs_update_local_option_impl("firestats_".$key,$value);
	}
	else
	{
		fs_update_option($key,$value);
	}
}

// if we are in the context of site (like when viewing from within wordpress) 
// try to get the value from the storage system of that site
// if its not there, try the firestats options storage, and if its not there, return the default.
function fs_get_local_option($key, $default=null)
{
	$fs_local_options_list = fs_get_local_options_list();
	if (!in_array($key, $fs_local_options_list))
	{
		echo "Not allowed to access ".$key;
		return;
	}

	if(function_exists('fs_get_local_option_impl'))
	{
		$value = fs_get_local_option_impl("firestats_".$key);
		if (empty($value)) 
			return fs_get_option($key,$default);
		else 
			return $value;
	}
	else
	{
		return fs_get_option($key,$default);
	}
}


/**
 * This function kicks the cache columns in the nuts, and recalculate them.
 * its normally called after an import.
 */
function fs_recalculate_db_cache()
{
	$fsdb = &fs_get_db_conn();
	$useragents = fs_useragents_table();
	$urls = fs_urls_table();
	$referers = fs_referers_table();
	$hits = fs_hits_table();
	$bots = fs_bots_table();
	
	$res = $fsdb->get_results("SELECT `useragent_id`,COUNT(useragent_id) c FROM `$hits` GROUP BY `useragent_id`");
	if ($res === false) return $fsdb->last_error;
	if (count($res) == 0) return '';
	foreach($res as $r)
	{	
		$useragent_id = $r->useragent_id;
		$count = $r->c;
		if ($fsdb->query("UPDATE $useragents SET count='$count' WHERE id='$useragent_id'") === false)
		{
			return $fsdb->last_error;
		}
	}


	return fs_recalculate_match_bots();
}

function fs_recalculate_match_bots()
{
	$fsdb = &fs_get_db_conn();
	$useragents = fs_useragents_table();
	$bots = fs_bots_table();

	$res = $fsdb->get_results("SELECT ua.id id,count(wildcard) c
								FROM $bots RIGHT JOIN $useragents ua ON useragent 
								REGEXP wildcard GROUP BY useragent");
	if ($res === false) return $fsdb->last_error;
	foreach($res as $r)
	{	
		$useragent_id = $r->id;
		$count = $r->c;
		if ($fsdb->query("UPDATE $useragents SET match_bots='$count' WHERE id='$useragent_id'") === false)
		{
			return $fsdb->last_error;
		}
	}
	return "";
}

function fs_version_table()
{
	return fs_table_prefix().'firestats_version';
}

function fs_hits_table()
{
	return fs_table_prefix().'firestats_hits';
}

function fs_useragents_table()
{
	return fs_table_prefix().'firestats_useragents';
}

function fs_urls_table()
{
	return fs_table_prefix().'firestats_urls';
}

function fs_referers_table()
{
	return fs_table_prefix().'firestats_referers';
}

function fs_hit2class_table()
{
	return fs_table_prefix().'firestats_hit2class';
}

function fs_excluded_ips_table()
{
	return fs_table_prefix().'firestats_excluded_ips';
}

function fs_bots_table()
{
	return fs_table_prefix().'firestats_useragent_classes';
}

function fs_temp_table()
{
	return fs_table_prefix().'firestats_temp';
}

function fs_options_table()
{
	return fs_table_prefix().'firestats_options';
}

function fs_ip2country_table()
{
	return fs_table_prefix().'firestats_ip2country';
}

function fs_sites_table()
{
	return fs_table_prefix().'firestats_sites';
}

function fs_ip2country_tmp_table()
{
	return fs_table_prefix().'firestats_ip2country_tmp';
}


function fs_table_prefix()
{
	global $fs_config;
	return $fs_config['DB_PREFIX'];
}

/*
 * Option getters 
 */
function fs_get_save_excluded_records()
{
	return fs_get_option('save_excluded_records','false');
}

function fs_get_max_referers_num()
{
    return fs_get_option('num_max_recent_referers', 10);
}

function fs_get_recent_referers_days_ago()
{
    return fs_get_option('recent_referers_days_ago', 30);
}

function fs_get_max_popular_num()
{
    return fs_get_option('num_max_recent_popular', 10);
}

function fs_get_recent_popular_pages_days_ago()
{
    return fs_get_option('recent_popular_pages_days_ago', 30);
}

function fs_get_num_hits_in_table()
{
    return fs_get_option('firestats_num_entries_to_show',50);
}

function fs_countries_list_days_ago()
{
    return fs_get_option('countries_list_days_ago', 30);
}

function fs_get_max_countries_num()
{
    return fs_get_option('max_countries_in_list', 5);
}

function fs_os_tree_days_ago()
{
    return fs_get_option('os_tree_days_ago', 30);
}

function fs_browsers_tree_days_ago()
{
    return fs_get_option('browsers_tree_days_ago', 30);
}

function fs_get_auto_ip2c_ver_check()
{
	return fs_get_option('ip-to-country-db_version_check_enabled','true');
}

function fs_get_version_check_enabled()
{
	return fs_get_option('firestats_version_check_enabled','true');
}

function fs_get_auto_bots_list_update()
{
	return fs_get_option('auto_bots_list_update','true');
}

?>

<?PHP
define('FS_AJAX_HANDLER',true);

require_once(dirname(__FILE__).'/session.php');

/**
 * Restoring the session BEFORE including the rest of the files.
 * the is nessecary because those files depends on the context to be established.
 */
$session_specified = true;
$got_session = false;
if (empty($_POST['sid']))
{
	$session_specified = false;
}
else
{
	$got_session  = fs_session_start($_POST['sid']);
	global $FS_SESSION;
	if ($got_session)
	{
		global $FS_CONTEXT;
		$FS_CONTEXT = $FS_SESSION['context'];
	}
}

require_once(dirname(__FILE__).'/json/JSON.php');
require_once(dirname(__FILE__).'/db-config-utils.php');
require_once(dirname(__FILE__).'/db-common.php');
require_once(dirname(__FILE__).'/db-setup.php');
require_once(dirname(__FILE__).'/html-utils.php');

$json = new Services_JSON();

if (isset($_POST['action']))
{
	ob_start(); // capture output. if there is output it means there is an error.

	global $session_specified;
	global $got_session;
	$authenticated = false;
	$response['status']='error';
	if (!$session_specified)
	{
		$response['message'] = 'Session id not specified';
	}
	else
	if (!$got_session)
	{
		$response['status']='session_expired';
	}
	else
	if (!fs_authenticated($response))
	{
		$response['message'] = 'Session not authenticated';
	}
	else
	{
		$authenticated = true;
	}
	
	if ($authenticated)
	{
		$action = $_POST['action'];
		$response['action'] = $action;
		$response['status']='ok';
		
		switch ($action)
		{
			case 'saveOption':
				fs_ajax_saveOption($response);
				break;
			case 'saveOptions':
				fs_ajax_saveOptions($response);
				break;
			case 'getAllStats';
			fs_ajax_get_all_stats($response);
			break;
			case 'addExcludedIP':
				fs_ajax_addExcludedIP($response);
				break;
			case 'removeExcludedIP':
				fs_ajax_removeExcludedIP($response);
				break;
			case 'saveExcludedUsers':
				fs_ajax_saveExcludedUsers($response);
				break;
			case 'addBot':
				fs_ajax_addBot($response);
				break;
			case 'removeBot':
				fs_ajax_removeBot($response);
				break;
			case 'importCounterize':
				fs_ajax_importCounterize($response);
				break;
			case 'testDBConnection':
				fs_ajax_test_db_connection($response);
				break;
			case 'attachToDatabase':
				fs_ajax_attach_to_database($response);
				break;
			case 'useWordpressDB':
				fs_ajax_useWordpressDB($response);
				break;
			case 'installDBTables':
				fs_ajax_install_db_tables($response);
				break;
			case 'createNewDatabase':
				fs_ajax_create_new_database($response);
				break;
			case 'upgradeDatabase':
				fs_ajax_upgrade_database($response);
				break;
			case 'unlockDBConfig':
				fs_ajax_db_unlock($response);
				break;
			case 'lockDBConfig':
				fs_ajax_db_lock($response);
				break;
			case 'changeLanguage':
				fs_ajax_change_language($response);
				break;
			case 'reclaculateDBCache':
				fs_ajax_recalculate_db_cache($response);
				break;
			case 'updateIP2CountryDB':
				fs_ajax_update_ip_to_country($response);
				break;
			case 'purgeExcludedHits':
				fs_ajax_purge_excluded_hits($response);
				break;
			case 'updateFields':
				fs_ajax_send_update($response);
				break;
			case 'updateBotsList':
				fs_ajax_update_bots_list($response);
				break;
			case 'updateSitesFilter':
				fs_ajax_update_sites_filter($response);
				break;
			case 'updateSiteInfo':
				fs_ajax_update_sites_info($response);
				break;
			case 'createNewSite':
				fs_ajax_create_new_site($response);
				break;
			case 'deleteSite':
				fs_ajax_delete_site($response);
				break;
			default:
				$response['status']='error';
				$response['message'] = 'AJAX: '.sprintf(fs_r('Unsupported action code : %s'),$action);
		}
	}

	$output = ob_get_clean();
	if ($output != '')
	{
		$response['status']='error';
		if (empty($response['message'])) $response['message'] = '';
		$response['message'] = '<br/><br/>'.sprintf(fs_r('Unexpexted output: %s'),$output);
	}
	print $json->encode($response);
}

function fs_ajax_get_message($for_event, $key, $value)
{
	if ($for_event == 'saved')
	{
		switch ($key)
		{
			case 'firestats_num_entries_to_show':
				return sprintf(fs_r('Saved %d entries to show'), $value);
			default:
				return fs_r('Saved');
		}
	}
	return "UNKNOWN";
}

function fs_ajax_importCounterize(&$response)
{
	require_once(dirname(__FILE__).'/db-import.php');
	$r = fs_import_counterize();
	if ($r['status'] == 'ok')
	{
		$response['message'] = sprintf(fs_r('Imported %d records from Counterize'),$r['count']);
		$response['fields']['import_counterize'] = fs_r('Import');
	}
	else
	{
		$response['status']='error';
		$response['message'] = sprintf(fs_r('An Error occured while importing from Counterize : %s'),$r['message']);
	}

}

function fs_ajax_addExcludedIP(&$response)
{
	$ip = $_POST['ip'];
	$res = fs_add_excluded_ip($ip);
	if ($res == '')
	{
		$response['message'] = sprintf(fs_r('Added %s to exclude list'),$ip);
		$response['fields']['exclude_ip_placeholder'] = addslashes(fs_get_excluded_ips_list());
		$response['fields']['num_excluded'] = fs_get_num_excluded();
	}
	else
	{
		$response['status']='error';
		$response['message'] = $res;
	}
}

function fs_ajax_removeExcludedIP(&$response)
{
	$ip = $_POST['ip'];
	$res = fs_remove_excluded_ip($ip);
	if ($res == '')
	{
		$response['message'] = sprintf(fs_r('Removed %s from exclude list'),$ip);
		$response['fields']['exclude_ip_placeholder'] = addslashes(fs_get_excluded_ips_list());
		$response['fields']['num_excluded'] = fs_get_num_excluded();
	}
	else
	{
		$response['status']='error';
		$response['message'] = $res;
	}
}

function fs_ajax_saveOptions(&$response)
{
	if (!fs_check_database($response)) return;
	$dest = $_POST['dest'];
	$list = $_POST['list'];
	$pairs = explode(";",$list);
	foreach($pairs as $pair)
	{
		$pp = explode(",",$pair);
		if (count($pp) > 1)
		{
			$key = rawurldecode($pp[0]);
			$value = rawurldecode($pp[1]);
			fs_ajax_saveSingleOption($key,$value,$dest);
		}
	}
	fs_ajax_send_update($response);
}

function fs_ajax_saveOption(&$response)
{
	if (!fs_check_database($response)) return;
	$key = $_POST['key'];
	$value = $_POST['value'];
	$dest = $_POST['dest'];
	fs_ajax_saveSingleOption($key, $value, $dest);
	$response['message'] = fs_ajax_get_message('saved',$key,$value);
	fs_ajax_send_update($response);
}

function fs_ajax_saveSingleOption($key, $value, $dest)
{
	switch($dest)
	{
		case 'fs':
			fs_update_option($key, $value);
			break;
		case 'wp':
			{
				switch ($key)
				{
					case "firestats_add_comment_flag":
					case "firestats_add_comment_browser_os":
					case "firestats_show_footer":
					case "firestats_show_footer_stats":
					case "firestats_site_id":
						break;
					default:
						echo "Not allowed to save wordpress option " . $key;
						return;
				}
				if (fs_check_is_demo($response)) return;
				update_option($key, $value);
			}
			break;
					default:
						echo "Unknown dest id ".$dest;
	}
}

function fs_ajax_get_all_stats(&$response)
{
	if (!fs_check_database($response)) return;
	$response['fields']['fs_browsers_tree']	= addslashes(fs_get_browsers_tree());
	$response['fields']['fs_os_tree'] 		= addslashes(fs_get_os_tree());
	$response['fields']['fs_recent_referers'] = addslashes(fs_get_recent_referers_tree());
	$response['type']['fs_browsers_tree']= 'tree';
	$response['type']['fs_os_tree']= 'tree';
	$response['type']['fs_recent_referers']= 'tree';
	$response['fields']['stats_total_count'] = fs_get_hit_count();
	$response['fields']['stats_total_unique'] = fs_get_unique_hit_count();
	$response['fields']['stats_total_count_last_day'] = fs_get_hit_count(1);
	$response['fields']['stats_total_unique_last_day'] = fs_get_unique_hit_count(1);
	$response['fields']['records_table'] = fs_get_records_table();
	$response['fields']['popular_pages'] = fs_get_popular_pages_tree();
	$response['fields']['countries_list'] = fs_get_countries_list();
}

function fs_ajax_saveExcludedUsers(&$response)
{
	$list = $_POST['list'];
	$res = fs_save_excluded_users($list);
	if ($res == '')
	{
		$response['fields']['exclude_users_placeholder'] = addslashes(fs_get_excluded_users_list());
		$response['message'] = fs_r('Excluded users list saved');
	}
	else
	{
		$response['status']='error';
		$response['message'] = $res;
	}

}

function fs_ajax_addBot(&$response)
{
	$wildcard = $_POST['wildcard'];
	if ($wildcard != '')
	{
		$res = fs_add_bot($wildcard);
		if ($res == '')
		{
			$response['message'] = sprintf(fs_r('Added %s to bots list'),$wildcard);
			$response['fields']['botlist_placeholder'] = addslashes(fs_get_bot_list());
			$response['fields']['num_excluded'] = fs_get_num_excluded();
		}
		else
		{
			$response['status']='error';
			$response['message'] = $res;
		}
	}
	else
	{
		$response['status']='error';
		$response['message'] = fs_r('Empty string is not allowed');
	}
}

function fs_ajax_removeBot(&$response)
{
	$bot_id = $_POST['bot_id'];
	$res = fs_remove_bot($bot_id);
	if ($res == '')
	{
		$response['message'] = sprintf(fs_r('Removed'));
		$response['fields']['botlist_placeholder'] = addslashes(fs_get_bot_list());
		$response['fields']['num_excluded'] = fs_get_num_excluded();
	}
	else
	{
		$response['status']='error';
		$response['message'] = $res;
	}

}

function fs_ajax_test_db_connection(&$response)
{
	$host 	= $_POST['host'];
	$user 	= $_POST['user'];
	$pass 	= $_POST['pass'];
	$dbname	= $_POST['dbname'];
	$table_prefix = $_POST['table_prefix'];

	$res = fs_test_db_connection($host, $user, $pass,$dbname,$table_prefix);
	$status = $res['status'];
	$response['db_status'] = $status;
	$response['styles']['advanced_feedback']['color'] = $res['color'];
	$response['fields']['advanced_feedback'] = $res['message'];
	$response['fields']['new_db_feedback'] = '';

	$response['styles']['install_tables_id']['display'] = 'none';
	$response['styles']['use_database_id']['display'] = 'none';
	$response['styles']['create_db_id']['display'] = 'none';

	switch ($status)
	{
		case 'other_db_detected':
			$response['styles']['use_database_id']['display'] = 'block';
			break;
		case 'tables_missing':
			$response['styles']['install_tables_id']['display'] = 'block';
			break;
		case'database_missing':
			$response['styles']['create_db_id']['display'] = 'block';
			break;
	}
}


function fs_ajax_useWordpressDB(&$response)
{
	if (fs_get_db_config_type() != FS_DB_CONFIG_FILE)
	{
		$response['status']='error';
		$response['message'] = fs_r('Not using configuration file');
		return;
	}

	if (!fs_in_wordpress())
	{
		$response['status']='error';
		$response['message'] = fs_r('Not installed inside Wordpress');
		return;
	}

	ob_start();
	$res = unlink('fs-config.php');
	$output = ob_get_clean();

	if (!$res)
	{
		$response['status']='error';
		$response['message'] = sprintf(fs_r('Failed to delete fs-config.php : %s'), $output);
	}
	else
	{
		$response['db_status'] = 'ok';
		fs_sendDBConfig($response);
		$response['styles']['switch_to_external_system']['display'] = 'none';
	}
}

function fs_ajax_attach_to_database(&$response)
{
	$host 	= $_POST['host'];
	$user 	= $_POST['user'];
	$pass 	= $_POST['pass'];
	$dbname	= $_POST['dbname'];
	$table_prefix = $_POST['table_prefix'];
	$res = fs_save_config_file($host,$user,$pass,$dbname,$table_prefix);
	if ($res != '')
	{
		$response['status']='error';
		$response['message'] = fs_r('Error creating config file: ').$res;
		return false;
	}
	else
	{
		$response['db_status'] = 'ok';
		fs_sendDBConfig($response);
		if(fs_should_show_use_wp_button())
		{
			$response['styles']['switch_to_external_system']['display'] = 'block';
		}
		return true;
	}
}

function fs_ajax_upgrade_database(&$response)
{
	ob_start();
	$res = fs_install();
	$output = ob_get_clean();
	if (!$res)
	{
		$response['status']='error';
		$response['message'] = fs_r('Error upgrading tables').': '.$output;
	}
	else
	{
		$response['refresh'] = 'true';
	}
}


function fs_ajax_install_db_tables(&$response)
{
	if (!fs_ajax_attach_to_database($response))
	{
		return;
	}

	# force databae connection to be re-initialized
	fs_get_db_conn(true);

	ob_start();
	$res = fs_install();
	$output = ob_get_clean();
	if (!$res)
	{
		$response['status']='error';
		$response['message'] = fs_r('Error installing tables').': '.$output;
	}
	else
	{
		$response['db_status'] = 'ok';
		fs_sendDBConfig($response);
	}
}

function fs_ajax_create_new_database(&$response)
{
	$host 	= $_POST['host'];
	$admin_user = $_POST['admin_user'];
	$admin_pass	= $_POST['admin_pass'];
	$user 	= $_POST['user'];
	$pass 	= $_POST['pass'];
	$dbname	= $_POST['dbname'];
	$table_prefix = $_POST['table_prefix'];
	$res = fs_create_new_database($host, $admin_user, $admin_pass, $user, $pass, $dbname, $table_prefix);
	$status = $res['status'];

	$response['db_status'] = $status;
	$response['fields']['new_db_feedback'] = $res['message'];
	$response['styles']['new_db_feedback']['color'] = '';
	switch ($status)
	{
		case 'ok':
			$response['styles']['new_db_feedback']['color'] = 'blue';
			break;
		case 'error':
			$response['styles']['new_db_feedback']['color'] = 'red';
			break;
		default:
			$response['fields']['new_db_feedback'] = "Unexpected status: ".$status;
			$response['styles']['new_db_feedback']['color'] = 'red';
	}

}

function fs_ajax_db_unlock(&$response)
{
	$pass = $_POST['password'];
	$res = fs_unlock_db_config($pass);
	$response['status'] = $res['status'];
	$response['message'] = $res['message'];
	fs_send_lock_status_update($response);
}


function fs_ajax_db_lock(&$response)
{
	$pass = $_POST['password'];
	$res = fs_lock_db_config($pass);
	$response['status'] = $res['status'];
	$response['message'] = $res['message'];
	fs_send_lock_status_update($response);
}

function fs_send_lock_status_update(&$response)
{
	$locked = fs_db_config_locked();
	$response['styles']['database_unlock_panel_div']['display'] = !$locked ? 'none' : 'block';
	$response['styles']['lock_help_panel_div']['display'] = !$locked ? 'none' : 'block';
	$response['styles']['database_lock_panel_div']['display'] = $locked ? 'none' : 'block';
	$response['styles']['database_help_panel_div']['display'] = $locked ? 'none' : 'block';
	$response['styles']['database_table_config_div']['display'] = $locked ? 'none' : 'block';
	if (!$locked)
	{
		fs_sendDBConfig($response);
	}
}

function fs_sendDBConfig(&$response)
{
	fs_load_config();
	global $fs_config;
	$response['fields']['config_source'] = fs_get_config_source_desc();
	$response['fields']['text_database_host'] = $fs_config['DB_HOST'];
	$response['fields']['text_database_name'] = $fs_config['DB_NAME'];
	$response['fields']['text_database_user'] = $fs_config['DB_USER'];
	$response['fields']['text_database_pass'] = ''; // don't send password, its too risky.
	$response['fields']['text_database_prefix'] = $fs_config['DB_PREFIX'];
	// clear the fields
	$response['fields']['advanced_feedback'] = '';
	$response['fields']['new_db_feedback'] = '';
	// hide the buttons
	$response['styles']['install_tables_id']['display'] = 'none';
	$response['styles']['use_database_id']['display'] = 'none';
	$response['styles']['create_db_id']['display'] = 'none';
}


function fs_ajax_change_language(&$response)
{
	if (fs_check_is_demo($response)) return;
	$language = $_POST['language'];
	$current = fs_get_option('current_language');
	if ($current != $language)
	{
		fs_update_option('current_language', $language);
		$response['refresh'] = 'true';
	}
}

function fs_ajax_recalculate_db_cache(&$response)
{
	if (fs_check_is_demo($response)) return;
	$res = fs_recalculate_db_cache();
	if($res != '')
	{
		$response['status'] = 'error';
		$response['message'] = fs_r('Error rebuilding database cache').' :'.$res;
	}
	else
	{
		$response['message'] = fs_r('Database cache rebuilt successfully');
	}
}


function fs_ajax_send_update(&$response)
{
	if (!isset($_POST['update'])) return;

	$update_blocks = explode(';',$_POST['update']);
	// if we have no more blocks return.
	if (count($update_blocks) == 0) return;

	// pop the first block.
	$update = array_shift($update_blocks);

	if (count($update_blocks) > 0)
	{
		// push the remaining items to the response, so the client will be able to send antoher request with the rest.
		$response['send_request'] = "action=updateFields&update=".implode(";", $update_blocks);
	}

	$updates = explode(',',$update);
	foreach($updates as $update)
	{
		switch ($update)
		{
			case 'popular_pages':
				$response['fields'][$update] = addslashes(fs_get_popular_pages_tree());
				break;
			case 'records_table':
				$response['fields'][$update] = fs_get_records_table();
				break;
			case 'countries_list':
				$response['fields'][$update] = fs_get_countries_list();
				break;
			case 'fs_recent_referers':
				$response['fields'][$update] = addslashes(fs_get_recent_referers_tree());
				$response['type'][$update]= 'tree';
				break;
			case 'fs_browsers_tree':
				$response['fields'][$update] = fs_get_browsers_tree();
				$response['type'][$update]= 'tree';
				break;
			case 'fs_os_tree':
				$response['fields'][$update] = fs_get_os_tree();
				$response['type'][$update]= 'tree';
				break;
			case 'botlist_placeholder':
				$response['fields']['botlist_placeholder'] = addslashes(fs_get_bot_list());
				break;
			case 'num_excluded':
				$response['fields']['num_excluded'] = fs_get_num_excluded();
				break;
			case 'stats_total_count':
				$response['fields']['stats_total_count'] = fs_get_hit_count();
				break;
			case 'stats_total_unique':
				$response['fields']['stats_total_unique'] = fs_get_unique_hit_count();
				break;
			case 'stats_total_count_last_day':
				$response['fields']['stats_total_count_last_day'] = fs_get_hit_count(1);
				break;
			case 'stats_total_unique_last_day':
				$response['fields']['stats_total_unique_last_day'] = fs_get_unique_hit_count(1);
				break;
			case 'fs_sites_table':
				$response['fields']['fs_sites_table'] = fs_get_sites_manage_table();
				break;
			case 'sites_filter_span':
				$response['fields']['sites_filter_span'] = fs_get_sites_list();
				break;
			default:
				$response['status']='error';
				$response['message'] = sprintf("AJAX:".fs_r('Unkown field: %s'),$update);
		}
	}
}

function fs_ajax_update_ip_to_country(&$response)
{
	require_once(dirname(__FILE__).'/version-check.php');
	$file_type = '';
	$url = '';
	$version = '';
	$info = null;
	$error = null;
	$need_update = fs_is_ip2country_db_need_update($url,$file_type, $version, $info, $error);
	if ($need_update)
	{
		require_once(dirname(__FILE__).'/ip2country.php');
		$res = fs_update_ip2country_db($url,$file_type, $version);
		$ok = $res['status'] == 'ok';
		if ($ok)
		{
			$response['status'] = 'ok';
			$response['message'] = $res['message'];
			$response['fields']['ip2c_database_version'] = fs_get_current_ip2c_db_version();
			$response['fields']['new_ip2c_db_notification'] = '';
		}
		else
		{
			$response['status'] = 'error';
			$error = $res['message'];
		}
	}
	else
	{
		$response['status'] = 'ok';
		$response['message'] = fs_r("IP-to-country database is already up-to-date");
	}
	
	if (!empty($error))
	{
		$response['status'] = 'error';
		$ip2c_dir = dirname(__FILE__).'/ip2c/';
		$response['message'] = fs_r('An error has occured while trying to update the IP-to-country database')."<br/>";
		if (isset($info['ip-to-country-db']['zip_url']))
		{
			$url = $info['ip-to-country-db']['zip_url'];
			$href = sprintf("<a href='$url'>%s</a>",fs_r('file'));
			$response['message'] .= 
			sprintf(fs_r('You can update the database manually by downloading this %s and extracting it into %s'), $href,$ip2c_dir);
		}
		else
		{
			$url = FS_IP2COUNTRY_DB_VER_CHECK_URL;
			$href = sprintf("<a href='$url'>%s</a>",fs_r('this'));
			$response['message'] .= 
			sprintf(fs_r('You can update the database manually by opening %s and downloading the <b>zip_url</b>, and extracting it into %s'), $href,$ip2c_dir);
		}
		$response['message'] .= '</b><br/><br/>'.fs_r('Error').': '.$error;
	}
}

function fs_ajax_update_bots_list(&$response)
{
	require_once(dirname(__FILE__).'/version-check.php');
	// don't use cached version
	$force_check = true;
	$user_initiated = true;
	if (isset($_POST['user_initiated']))
	{
		$user_initiated = $_POST['user_initiated'] == 'true';
		$force_check = $user_initiated;
	}
	$url = '';
	$md5 = '';
	$error = '';
	$updated = fs_is_botlist_updated($url, $md5, $error,$force_check);
	if (!empty($error))
	{
		$response['status'] = 'error';
		$response['message'] = sprintf(fs_r("Error updating bots list: %s"),$error);
	}
	else
	{
		// if user initiated the request update regardless of current status.
		if ($user_initiated || !$updated)
		{
			// don't replace exiting bots, just add new ones.
			$remove_existing = false;
			$res = fs_botlist_import_url($url, $remove_existing);
			if ($res == '')
			{
				$response['message'] = fs_r("Successfully updated bots list");
				fs_update_option('botlist_version_hash',$md5);
				fs_ajax_send_update($response);
			}
			else
			{
				$response['status'] = 'error';
				$response['message'] = sprintf(fs_r("Error updating bots list: %s"),$res);
			}
		}
	}
}



function fs_ajax_purge_excluded_hits(&$response)
{
	if (fs_check_is_demo($response)) return;
	$res = fs_purge_excluded_entries();
	if ($res === false)
	{
		$response['status']='error';
		$response['message'] = sprintf(fs_r('Error purging excluded records: %s'),fs_db_error());

	}
	else
	{
		$response['message'] = fs_r('Purged excluded records');
		$response['fields']['num_excluded'] = fs_get_num_excluded();
	}
}

function fs_ajax_update_sites_filter(&$response)
{
	$sites = $_POST['sites_filter'];
	fs_update_local_option('sites_filter',$sites);
	fs_ajax_get_all_stats($response);
}

function fs_ajax_create_new_site(&$response)
{
	if (fs_check_is_demo($response)) return;
	$new_sid = $_POST['new_sid'];
	$name = $_POST['name'];
	$type = $_POST['type'];
	$res = fs_create_new_site($new_sid, $name, $type);
	if ($res === true)
	{
		fs_ajax_send_update($response);
	}
	else
	{
		$response['status']='error';
		$response['message'] = $res;
	}
}

function fs_ajax_update_sites_info(&$response)
{
	if (fs_check_is_demo($response)) return;
	$new_sid = $_POST['new_sid'];
	$orig_sid = $_POST['orig_sid'];
	$name = $_POST['name'];
	$type = $_POST['type'];
	$res = fs_update_site_params($new_sid,$orig_sid, $name,$type);
	if ($res === true)
	{
		fs_ajax_send_update($response);
	}
	else
	{
		$response['status']='error';
		$response['message'] = $res;
	}
}

function fs_ajax_delete_site(&$response)
{
	if (fs_check_is_demo($response)) return;
	$sid = $_POST['site_id'];
	$action = $_POST['action_code'];
	$new_sid = isset($_POST['new_sid']) ? $_POST['new_sid'] : null;
	$res = fs_delete_site($sid, $action, $new_sid);
	if ($res === true)
	{
		// if the deleted site was selected in the filter, update the filter
		$current_selected = fs_get_local_option('sites_filter');
		if ($current_selected == $sid)
		{
			// reset filter to 'all'.
			fs_update_local_option('sites_filter','all');
			fs_ajax_get_all_stats($response);
		}
		// and also send whatever the client requested.
		fs_ajax_send_update($response);
	}
	else
	{
		$response['status']='error';
		$response['message'] = $res;
	}
}

function fs_check_database(&$response)
{
	$fsdb = &fs_get_db_conn();
	if (!$fsdb->is_connected())
	{
		$response['status']='error';
		$response['message'] = fs_r('Error connecting to database');
		return false;
	}
	return true;
}

function fs_check_is_demo(&$response)
{
	if (defined('DEMO'))
	{
		$response['status']='error';
		$response['message'] = 'This operation is not permitted in demo mode';
		return true;
	}
	return false;
}

function fs_authenticated(&$response)
{
	global $FS_SESSION;
	return isset($FS_SESSION['authenticated']) && $FS_SESSION['authenticated'];
}
?>

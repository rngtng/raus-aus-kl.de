<?php

// general
define('FS_VERSION','1.2.4-stable');
define('FS_HOMEPAGE','http://firestats.cc');
define('FS_FIRESTATS_VER_CHECK_URL','http://files.firestats.cc/firestats.latest?version='.FS_VERSION);
define('FS_IP2COUNTRY_DB_VER_CHECK_URL','http://files.firestats.cc/ip2c/ip-to-country.latest');
define('FS_HOMEPAGE_TRANSLATE','http://firestats.cc/wiki/TranslateFireStats');
define('FS_MULTIPLE_SITES_INFO_URL','http://firestats.cc/wiki/MultipleSites');
define('FS_WIKI','http://firestats.cc/wiki/');

// database related constants
define('FS_DB_VALID', 0);
define('FS_DB_NOT_INSTALLED', -1);
define('FS_DB_NEED_UPGRADE', -2);
define('FS_DB_IS_NEWER_THAN_CODE', -3);
define('FS_DB_GENERAL_ERROR', -4);
define('FS_DB_NOT_CONFIGURED', -5);
define('FS_DB_CONNECTION_ERROR', -6);

// the database schema version this code works with
define('FS_REQUIRED_DB_VERSION',8);

// site type constants
define('FS_SITE_TYPE_GENERIC'	,0);
define('FS_SITE_TYPE_WORDPRESS'	,1);
define('FS_SITE_TYPE_DJANGO'	,2);
define('FS_SITE_TYPE_DRUPAL'	,3);
define('FS_SITE_TYPE_GREGARIUS'	,4);
define('FS_SITE_TYPE_JOOMLA'	,5);
define('FS_SITE_TYPE_MEDIAWIKI'	,6);
define('FS_SITE_TYPE_TRAC'		,7);

define('FS_ABS_PATH',dirname(dirname(__FILE__)));

if (file_exists(dirname(__FILE__).'/../demo'))
{
    define('DEMO','true');
}

global $FS_CONTEXT;
if (!isset($FS_CONTEXT))
{
	detect_context();
}

// if we run in wordpress, load its config to gain access to the api and configuration
if (fs_in_wordpress())
{
	global $FS_CONTEXT;
	$config_path = $FS_CONTEXT['WP_PATH'];
	require_once($config_path);
}

function fs_in_wordpress()
{
	global $FS_CONTEXT;
	return $FS_CONTEXT['TYPE'] == 'WORDPRESS';
}

// this is a pretty ugly function that tries to autoamtically detect and set the context
function detect_context()
{
	global $FS_CONTEXT;
	$FS_CONTEXT = array();
	$wpc = fs_priv_get_wp_config_path();
	if ($wpc != false)
	{
		$FS_CONTEXT['TYPE'] = 'WORDPRESS';
		$FS_CONTEXT['WP_PATH'] = $wpc;
	}
	else
	{
		$FS_CONTEXT['TYPE'] = 'GENERIC';
	}
}

function fs_priv_get_wp_config_path()
{
    $base = dirname(__FILE__);
    $path = false;

    if (file_exists($base."/../../../../wp-config.php"))
		$path = dirname(dirname(dirname(dirname($base))))."/wp-config.php";
    else
    if (file_exists($base."/../../../wp-config.php"))
		$path = dirname(dirname(dirname($base)))."/wp-config.php";
    else
        $path = false;

    if ($path != false)
    {
        $path = str_replace("\\", "/", $path);
    }
    return $path;
}


/**
 * There are two methods to install FireStats:
 * 1. Standalone: where its installed somewhere on the server (independent) and  serves
 *    a few systems on the same machine.
 *    For example: it can serve several blogs and a trac site.
 * 2. Hosted: as a subsystem of another system, like Wordpress.
 *	  In this mode, FireStats is actually installed inside the hosting system, and 
 *    its also uses the host database and database configuration.
 */
function fs_is_hosted()
{
	if (function_exists('fs_full_installation'))
	{
		return fs_full_installation();
	}
	else
	{
		return false; // default to standalone
	}
}
	
?>

<?php
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/db-common.php');
require_once(dirname(__FILE__).'/fs-gettext.php');


if (!isset($GLOBALS['fs_initialized']))
{
	// will trigger the initialization of the database connection
	$fsdb = &fs_get_db_conn();
	require_once(dirname(__FILE__).'/utils.php');
	
	$current_lang = '';
	if ($fsdb->is_connected())
	{
		$current_lang = fs_get_option('current_language');
	}

	if (empty($current_lang) && fs_in_wordpress() && defined('WPLANG'))
	{
		$current_lang = WPLANG;
		if ($fsdb->is_connected())
		{
			fs_update_option('current_language',$current_language);
		}
	}
	
	$transfile = FS_ABS_PATH.'/i18n/firestats-'.$current_lang.'.po';
	if (file_exists($transfile))
	{
		$fs_gettext = new fs_gettext($transfile);
	}
	else
	{
		$fs_gettext = new fs_gettext();
	}
	$GLOBALS['fs_gettext'] = $fs_gettext;

	if ($current_lang == 'he_IL' || $current_lang == 'ar_AR')
	{
		define('FS_LANG_DIR','rtl');
	}
	else
	{
		define('FS_LANG_DIR','ltr');
	}

	$GLOBALS['fs_initialized'] = true;

}

?>

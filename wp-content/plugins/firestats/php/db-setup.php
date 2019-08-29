<?php
require_once(dirname(__FILE__).'/init.php');
require_once(dirname(__FILE__).'/db-common.php');

function fs_comment($str)
{
	$fs_mysql_version = fs_mysql_version();
	if (ver_comp("4.1.0",$fs_mysql_version) > 0)
	{
		return ""; // no comment.
	}
	else
	{
		return "COMMENT '".$str."'";	
	}
}

function fs_engine($engine)
{
	$fs_mysql_version = fs_mysql_version();
	if (ver_comp("4.0.18",$fs_mysql_version) > 0 || ver_comp("4.1.2",$fs_mysql_version) > 0)
	{
		return " TYPE=$engine";
	}
	else
	{
		return " ENGINE=$engine";
	}
}



# Used for first-time initialization - create table if not present...
function fs_install_into($user, $pass, $dbname, $dbhost)
{
	return fs_install_impl(fs_create_db_conn($user,$pass,$dbname, $dbhost));
}

function fs_install()
{
	$fsdb = &fs_get_db_conn();
	if (!isset($fsdb)) die('db object not initialized');
	return fs_install_impl($fsdb);
}


function fs_install_impl($fsdb)
{
	$db_status_arr = fs_get_db_status($fsdb);
	if ($db_status_arr === false) return false;

	$db_status = $db_status_arr['status'];
	$db_version = $db_status_arr['ver'];
	if ($db_status == FS_DB_GENERAL_ERROR)
	{	
		$fsdb->debug();
		return false;
	}
	else
	if ($db_status == FS_DB_NOT_INSTALLED || $db_status == FS_DB_NOT_CONFIGURED)
	{
		if (!fs_db_install($fsdb)) return false;
	}
	else
	if ($db_status == FS_DB_NEED_UPGRADE)
	{	
		if (!fs_db_upgrade($fsdb,$db_version)) return false;
	}

	// finally, if we got that far, update the version of the database.
	$version_table = fs_version_table();
	if ($fsdb->query("REPLACE INTO `$version_table` ( `version` ) VALUES ('".FS_REQUIRED_DB_VERSION."')") === false)	
	{
		$fsdb->debug();
		return false;
	}
	return true;
}


function fs_db_upgrade($fsdb, $db_version)
{
	$upgraded = false;
	$fsdb->hide_errors();
	$version_table = fs_version_table();
	// a nice little convert loop.
	$useragents = fs_useragents_table();
	$hits = fs_hits_table();

	// upgrade to version 2
	if ($db_version < 2)
	{
		if (!fs_create_options_table($fsdb)) return false;
		$upgraded = true;
	}


	// convert charsets, this is instead of collate which does not work on mysql 4.0
	if ($db_version < 3)
	{
		if (ver_comp("4.1.0",fs_mysql_version()) < 0)
		{
			$sqls = array("ALTER TABLE `$useragents` DROP INDEX `unique`",
			"ALTER TABLE `$useragents` ADD `md5` CHAR( 32 ) NOT NULL AFTER `useragent`",
			"UPDATE `$useragents` SET `md5` = MD5( `useragent` )",
			"ALTER TABLE `$useragents` ADD UNIQUE (`md5`)",
			"ALTER TABLE `$hits` CHANGE `timestamp` `timestamp` DATETIME NOT NULL");
			foreach ($sqls as $sql)
			{
				if ($fsdb->query($sql) === false)
				{	
					$fsdb->debug();
					return false;
				}
			}

			// convert tables charset to utf-8
			$tables = array(fs_excluded_ips_table(),fs_hits_table(),
				fs_bots_table(),fs_options_table(),
				fs_referers_table(),fs_urls_table(),
				fs_version_table(), fs_useragents_table());

			foreach ($tables as $table)
			{
				$sql = "ALTER TABLE `$table` CONVERT TO CHARSET utf8";
				if ($fsdb->query($sql) === false)
				{	
					$fsdb->debug();
					return false;
				}
			}

		
			$upgraded = true;
		}
		else
		{
			$upgraded = true;
		}
	}

	if ($db_version < 4)
	{
		if (fs_recalculate_db_cache() === false)
		{
			$fsdb->debug();
			return false;
		}
		$upgraded = true;
	}

	if ($db_version < 5)
	{
		 
		if ($fsdb->query("ALTER TABLE `$hits` ADD `country_code` BLOB NULL DEFAULT NULL AFTER `user_id`") === false)	
		{
			$fsdb->debug();
			return false;
		}

		$upgraded = true;
	}

	if ($db_version < 6)
	{
		require_once(dirname(__FILE__).'/db-sql.php');
		$res = fs_botlist_import(dirname(__FILE__).'/botlist.txt',true);
		if ($res != '')
		{
			echo $res;
			return;
		}
		// bots are now matched using regular expressions. need to recalculate.
		fs_recalculate_match_bots();

		$upgraded = true;
	}

	if ($db_version < 7)
	{
		if ($fsdb->query("ALTER TABLE `$hits` ADD `site_id` INT NOT NULL DEFAULT 1 AFTER `id`") === false)	
		{
			$fsdb->debug();
			return false;
		}

		if ($fsdb->query("ALTER TABLE `$hits` ADD INDEX (`site_id`)") === false)	
		{
			$fsdb->debug();
			return false;
		}

	}

	if ($db_version < 8)
	{
		if (!fs_create_sites_table($fsdb)) return false;
		$upgraded = true;
	}

	if ($upgraded)
	{
		$c = $fsdb->get_var("SELECT count(*) FROM `$version_table`");
		if ($c === false)
		{
			$fsdb->debug();
			return false;
		}

		// in some wierd cases, there is more than one version record in the versions table
		// in those cases, we delete them first.
		if ((int)$c > 1) 
		{
			if ($fsdb->query("DELETE FROM `$version_table`") === false)
			{
				$fsdb->debug();
				return false;
			}
			if ($fsdb->query("REPLACE INTO `$version_table` ( `version` ) 
							  VALUES ('".FS_REQUIRED_DB_VERSION."')") === false)
			{
				$fsdb->debug();
				return false;
			}
		}
		else
		{
			if ($fsdb->query("UPDATE `$version_table` SET `version`='".FS_REQUIRED_DB_VERSION."' WHERE 1") === FALSE)
			{
				$fsdb->debug();
				return false;
			}
		}

		
		// after a db upgrade (major releases only) reset donation status for users that didn't donate	
	    $donation =  fs_get_option('donation_status');
		if ($donation != 'donated')
		{
			fs_update_option('donation_status','');
			fs_update_option('last_nag_time',time());
		}
	}

	return true;
}

function fs_db_install($fsdb)
{
	$fsdb->hide_errors();
	$version_table = fs_version_table();


	$hits_table = fs_hits_table();
	$useragents_table = fs_useragents_table();
	$sql = "
		CREATE TABLE IF NOT EXISTS $hits_table
		(
			`id` INTEGER PRIMARY KEY AUTO_INCREMENT ".fs_comment('Primary key').",
			`site_id` INTEGER default 1 ".fs_comment('Source site ID, defaults to site 1').",
			`ip` VARCHAR(40) NOT NULL DEFAULT 'unknown' ".fs_comment('IP Address of hit source').",
			`timestamp` DATETIME NOT NULL ".fs_comment('Hit timestamp').",
			`url_id` INTEGER ".fs_comment('Hit URL ID').",
			`referer_id` INTEGER ".fs_comment('Referer URL id').",
			`useragent_id` INTEGER ".fs_comment('UserAgent ID').",
			`session_id` VARCHAR(30) ".fs_comment('Client session ID').",
			`user_id` INTEGER default NULL ".fs_comment('User ID in the enclosing system, NULL for unknown user').",
			`country_code` BLOB NULL default NULL ".fs_comment('Country code of IP address or NULL if unknown').",
			`excluded_by_user` TINYINT(1) DEFAULT 0 ".fs_comment('1 if user explicitly excluded record, 0 otherwise').",
			`excluded_ip` TINYINT(1) DEFAULT 0 ".fs_comment('1 if the ip is in the excluded ips table, 0 otherwise').",
			`excluded_user` TINYINT(1) DEFAULT 0 ".fs_comment('1 if the user id is in the excluded users table, 0 otherwise').",
			UNIQUE(`ip`,`timestamp`,`url_id`,`referer_id`,`useragent_id`),
			INDEX (`site_id`)
		)
		".fs_comment('Hits table').fs_engine("InnoDB");
	if ($fsdb->query($sql) === FALSE)	
	{
		$fsdb->debug();
		return false;
	}
	
	$r = $fsdb->query(
		"CREATE TABLE IF NOT EXISTS `$useragents_table`
		(
			id INTEGER PRIMARY KEY AUTO_INCREMENT ".fs_comment('Primary key').", 
			useragent TEXT ".fs_comment('Useragent string').",
			md5 CHAR(32) NOT NULL,
			count INTEGER DEFAULT 0 NOT NULL ".fs_comment('Number of hits from this user agent').",
			match_bots INTEGER DEFAULT 0 ".fs_comment('Number of matching bots (useragent wildcards), if 0 the useragent is not exluded').",
			UNIQUE(`md5`)
		) ".fs_comment('User-Agents table').fs_engine("InnoDB"));

	if ($r === FALSE)
	{
		$fsdb->debug();
		return false;
	}

	$urls_table = fs_urls_table();
	$r = $fsdb->query(
		"CREATE TABLE IF NOT EXISTS $urls_table 
		(
			id INTEGER PRIMARY KEY AUTO_INCREMENT ".fs_comment('Primary key').",
			url VARCHAR(255),
			UNIQUE(`url`)
		) ".fs_comment('URLs table').fs_engine("InnoDB"));

	if ($r === FALSE)
	{
		$fsdb->debug();
		return false;
	}

	$referers_table = fs_referers_table();
	$r = $fsdb->query(
		"CREATE TABLE IF NOT EXISTS $referers_table 
		(
			id INTEGER PRIMARY KEY AUTO_INCREMENT ".fs_comment('Primary key').",
			referer VARCHAR(255),
			UNIQUE(`referer`)
		) ".fs_comment('Referers table').fs_engine("InnoDB"));
	if ($r === FALSE)
	{
		$fsdb->debug();
		return false;
	}

	$excluded_ip_table = fs_excluded_ips_table();
	$r = $fsdb->query(
		"CREATE TABLE IF NOT EXISTS $excluded_ip_table 
		(
			id INTEGER PRIMARY KEY AUTO_INCREMENT ".fs_comment('Primary key').",
			ip VARCHAR(16) NOT NULL
		) ".fs_comment('List of excluded ips').fs_engine("InnoDB"));
	if ($r === FALSE)
	{
		$fsdb->debug();
		return false;
	}

	$bots_table = fs_bots_table();
	$r = $fsdb->query(
		"CREATE TABLE IF NOT EXISTS $bots_table 
		(
			id INTEGER PRIMARY KEY AUTO_INCREMENT ".fs_comment('Primary key').",
			wildcard VARCHAR(100) NOT NULL ".fs_comment('Bots wildcard')."
		) ".fs_comment('Bots table').fs_engine("InnoDB"));
	if ($r === FALSE)
	{
		$fsdb->debug();
		return false;
	}

	if (!fs_create_options_table($fsdb)) return false;
	if (!fs_create_sites_table($fsdb)) return false;

	$r = $fsdb->query("CREATE TABLE IF NOT EXISTS `$version_table` 
	(
			version INTEGER NOT NULL PRIMARY KEY
	)".fs_comment('FireStats datbase schema version').fs_engine("InnoDB"));
	if ($r === FALSE)
	{	
		$fsdb->debug();
		return false;
	}
	return true;
}

function fs_create_options_table($fsdb)
{
	$options_table = fs_options_table();
	$r = $fsdb->query("CREATE TABLE IF NOT EXISTS `$options_table` (
		id INTEGER PRIMARY KEY AUTO_INCREMENT ".fs_comment('Primary key').",
		option_key VARCHAR(100) NOT NULL,
		option_value TEXT NOT NULL,	
		UNIQUE(`option_key`)
	) ".fs_comment('FireStats options table').fs_engine("InnoDB"));
	if ($r === FALSE)
	{
		$fsdb->debug();
		return false;
	}
	
	return true;
}

function fs_create_sites_table($fsdb)
{
	$sites = fs_sites_table();
	$sql = "CREATE TABLE IF NOT EXISTS `$sites` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ".fs_comment('Site ID').",
		`type` INT NOT NULL DEFAULT '0' ".fs_comment('Site type').",
		`name` VARCHAR( 100 ) NOT NULL ".fs_comment('Site name')."
		)".fs_comment('FireStats options table').fs_engine("InnoDB");
	$r = $fsdb->query($sql);
	if ($r === false)
	{
		$fsdb->debug();
		return false;
	}
	return true;
}

?>

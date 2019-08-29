<?php
require_once(dirname(__FILE__).'/ip2c/ip2c.php');

if (!isset($GLOBALS['fs_ip2c']))
{
	// during update, the file may not be there temporarily
	$__fs_ip2c_file = dirname(__FILE__).'/ip2c/ip-to-country.bin';
	if (file_exists($__fs_ip2c_file)) 
	{
		$GLOBALS['fs_ip2c'] = new fs_ip2country($__fs_ip2c_file);
		$GLOBALS['fs_ip2c_country_cache'] = array();
	}
}

function fs_ip2c($ip)
{
	if (isset($GLOBALS['fs_ip2c']))
	{
		$ip2c = $GLOBALS['fs_ip2c'];
		$ip2c_res = $ip2c->get_country($ip);
		if ($ip2c_res != false)
		{
			return $ip2c_res['id2'];
		}
	}

	return false;
}

function fs_get_country_name($country_code)
{
	if (isset($GLOBALS['fs_ip2c_country_cache']))
	{
		$cache = $GLOBALS['fs_ip2c_country_cache'];
		$res = isset($cache[$country_code]) ? $cache[$country_code] : false;
		if (!$res)
		{
			$ip2c = $GLOBALS['fs_ip2c'];
			$res = $ip2c->find_country($country_code);
			$cache[$country_code] = $res;
		}
		return $res['name'];
	}
	else
	{
		return '';
	}
}


function fs_get_country_flag_url($country_code)
{
    if (!$country_code) return "";
    $code = strtolower($country_code);
    $flag_url = fs_url("img/flags/$code.png");
    $name = fs_get_country_name($code);
	return fs_get_flag_img_tag($name, $flag_url);
}

function fs_get_flag_img_tag($name, $img_url)
{
    return "<img src='$img_url' alt='$name' title ='$name' width='16' height='11' class='fs_flagicon'/>";
}

function fs_echo_country_flag_url($country_code)
{
    echo fs_get_country_flag_url($country_code);
}



/**
 * Downloads a zip file containing the ip2country database and import it.
 */
function fs_update_ip2country_db($url, $file_type, $new_version)
{
	$cant_write = fs_ip2c_database_writeable();
	if ($cant_write != '')
	{
		return array('status'=>'error','message'=>$cant_write);
	}

	require_once(dirname(__FILE__).'/utils.php');

	$tempName = tempnam('/tmp',"fs_");
	if (!$tempName)
		return array('status'=>'error','message'=>fs_r('Error creating temporary file'));

	$temp = fopen($tempName,"w");
	$res = fs_create_http_conn($url);
	$http = $res['http'];
	$args = $res['args'];
	$error=$http->Open($args);
	if (!empty($error))
	{
		return array('status'=>'error','message'=>sprintf(fs_r('Error opening connection to %s'),$url));
	}
	$error = $http->SendRequest($args);
	if (!empty($error))
	{
		return array('status'=>'error','message'=>sprintf(fs_r('Error sending request to %s'),$url));
	}

	$output = ob_get_clean();
	// this is a little hack to keep outputing stuff while we download, it should help 
	// with the server killing the script due to inactivity.
	echo "/* Downloading IP-to-country database. if you see this, your server didn't give this script enough time to complete.<br/>";

	$content = '';
	for(;;)
	{
		$body = "";
		$error=$http->ReadReplyBody($body,10000);
		if($error != "") 
			return array('status'=>'error','message'=>sprintf(fs_r('Error reading data from %s : %s'),$url, $error));

		echo "*";

		if ($body == '') break;

		fwrite($temp, $body);
	}
	echo "*/";
	ob_start();
	echo $output;

	if ($file_type == 'bin')
	{
		return fs_install_bin_ip2c_database($new_version,$temp);
	}
	else
	if ($file_type == 'zip')
	{
		$bin_file = '';
		$res = fs_extract_zip_ip2c_database($tempName,$bin_file);
		if ($res == '')
		{
			return  fs_install_bin_ip2c_database($new_version, $bin_file);
		}
		else
		{
			return array('status'=>'error','message'=>sprintf(fs_r("Error extracting IP-to-country database: %s"),$res));
		}
	}
	else
	{
		return array('status'=>'error','message'=>sprintf(fs_r('Unsupported file type : %s'), $file_type));
	}

}


function fs_ip2c_database_writeable()
{
	$ip2c_dir = dirname(__FILE__).'/ip2c/';
	$bin_file = dirname(__FILE__).'/ip2c/ip-to-country.bin';
	$ver_file = dirname(__FILE__).'/ip2c/db.version';

	if (!is_writable($ip2c_dir))
		return sprintf(fs_r("can't update ip-to-country database, read-only directory : %s"),$ip2c_dir);
	if (file_exists($bin_file) && !is_writable($bin_file))
		return sprintf(fs_r("can't update ip-to-country database, read-only file : %s"),$bin_file);
	if (file_exists($ver_file) && !is_writable($ver_file))
		return sprintf(fs_r("can't update ip-to-country database, read-only file : %s"),$ver_file);
	return '';
}

function fs_install_bin_ip2c_database($version, $new_bin_file)
{
	$ip2c_dir = dirname(__FILE__).'/ip2c/';
	$bin_file = dirname(__FILE__).'/ip2c/ip-to-country.bin';
	$ver_file = dirname(__FILE__).'/ip2c/db.version';

	if (file_exists($bin_file))	unlink($bin_file);
	if (file_exists($ver_file))	unlink($ver_file);
 
	if (copy($new_bin_file, $bin_file) === false)
	{
		return array('status'=>'error','message'=>fs_r("Error installing new IP-to-country database"));
	}
	
	$res = fs_set_current_ip2c_db_version($version);
	if ($res == '')
	{
		return array('status'=>'ok','message'=>fs_r("Successfuly updated IP-to-country database"));
	}
	else
	{
		return array('status'=>'error','message'=>sprintf(fs_r("Error updating version file : %s"),$res));
	}
}

function fs_extract_zip_ip2c_database($zipFile,&$bin_file)
{
	require_once dirname(__FILE__)."/unzip/dUnzip2.inc.php";
	$zip = new dUnzip2($zipFile);
	$zip->unzipAll('/tmp/');

	$tempName2 = tempnam('/tmp',"fs_");
	if (!$tempName2)
		return fs_r('Error creating temporary file');
	
	ob_start();
	$zip->unzip("ip-to-country.bin", $tempName2);
	$output = ob_get_clean();
	if ($output != '')
	{
		return $output;
	}

	$bin_file = $tempName2;
	return '';
}


?>

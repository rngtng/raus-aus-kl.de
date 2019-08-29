<?php

require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/errors.php');

function fs_ends_with( $str, $sub )
{
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
}


// echo translated text
function fs_e($txt)
{
	global $fs_gettext;
	if (isset($fs_gettext))
	echo $fs_gettext->get($txt);
	else echo $txt;
}

// return translated text
function fs_r($txt)
{
	global $fs_gettext;
	if (isset($fs_gettext)) return $fs_gettext->get($txt);
	else return $txt;
}

function fs_url($file)
{
	global $fs_base_url;
	
	if (!isset($fs_base_url))
	{
		if (function_exists('fs_override_base_url'))
		{
			$fs_base_url = fs_override_base_url();
		}
		else
		{
			$fs_base_url = "";
		}
	}
	
	return $fs_base_url.$file;
}

function fs_get_request_suffix($append = "")
{
	require_once(dirname(__FILE__).'/session.php');
	$t = '?sid='.fs_get_session_id();
	if ($append)
	{
		$t .= "&".$append;
	}
	return $t;
}

function fs_get_whois_providers()
{
	static $whois_providers;
	if (!isset($whois_providers))
	{
		$providers = file(FS_ABS_PATH.'/php/whois.txt');
		foreach($providers as $line)
		{
			$r = sscanf($line,"%s %s");
			$whois_providers[$r[0]] = $r[1];
		}
	}
	return $whois_providers;
}


/*
 Function to replace PHP's parse_ini_file() with much fewer restritions, and
 a matching function to write to a .INI file, both of which are binary safe.

 Version 1.0

 Copyright (C) 2005 Justin Frim <phpcoder@cyberpimp.pimpdomain.com>

 Sections can use any character excluding ASCII control characters and ASCII
 DEL.  (You may even use [ and ] characters as literals!)

 Keys can use any character excluding ASCII control characters, ASCII DEL,
 ASCII equals sign (=), and not start with the user-defined comment
 character.

 Values are binary safe (encoded with C-style backslash escape codes) and may
 be enclosed by double-quotes (to retain leading & trailing spaces).

 User-defined comment character can be any non-white-space ASCII character
 excluding ASCII opening bracket ([).

 readINIfile() is case-insensitive when reading sections and keys, returning
 an array with lower-case keys.
 writeINIfile() writes sections and keys with first character capitalization.
 Invalid characters are converted to ASCII dash / hyphen (-).  Values are
 always enclosed by double-quotes.

 writeINIfile() also provides a method to automatically prepend a comment
 header from ASCII text with line breaks, regardless of whether CRLF, LFCR,
 CR, or just LF line break sequences are used!  (All line breaks are
 translated to CRLF)
 */

function fs_readINIfile ($filename, $commentchar)
{
	return fs_readInitArray(file($filename),$commentchar);
}

function fs_readINIArray ($array1, $commentchar)
{
	$section = '';
	foreach ($array1 as $filedata)
	{
		$dataline = trim($filedata);
		$firstchar = substr($dataline, 0, 1);
		if ($firstchar!=$commentchar && $dataline!='')
		{
			//It's an entry (not a comment and not a blank line)
			if ($firstchar == '[' && substr($dataline, -1, 1) == ']')
			{
				//It's a section
				$section = strtolower(substr($dataline, 1, -1));
			}
			else
			{
				//It's a key...
				$delimiter = strpos($dataline, '=');
				if ($delimiter > 0)
				{
					//...with a value
					$key = strtolower(trim(substr($dataline, 0, $delimiter)));
					$value = trim(substr($dataline, $delimiter + 1));
					if (substr($value, 1, 1) == '"' && substr($value, -1, 1) == '"')
					{
						$value = substr($value, 1, -1);
					}
					$array2[$section][$key] = stripcslashes($value);
				}
				else
				{
					//...without a value
					$array2[$section][strtolower(trim($dataline))]='';
				}
			}
		}else
		{
			//It's a comment or blank line.  Ignore.
		}
	}
	return $array2;
}

function fs_writeINIfile ($filename, $array1, $commentchar, $commenttext) {
	$handle = fopen($filename, 'wb');
	if ($commenttext!='') {
		$comtext = $commentchar.
		str_replace($commentchar, "\r\n".$commentchar,
		str_replace ("\r", $commentchar,
		str_replace("\n", $commentchar,
		str_replace("\n\r", $commentchar,
		str_replace("\r\n", $commentchar, $commenttext)
		)
		)
		)
		)
		;
		if (substr($comtext, -1, 1)==$commentchar && substr($comtext, -1, 1)!=$commentchar) {
			$comtext = substr($comtext, 0, -1);
		}
		fwrite ($handle, $comtext."\r\n");
	}
	foreach ($array1 as $sections => $items) {
		//Write the section
		if (isset($section)) { fwrite ($handle, "\r\n"); }
		//$section = ucfirst(preg_replace('/[\0-\37]|[\177-\377]/', "-", $sections));
		$section = ucfirst(preg_replace('/[\0-\37]|\177/', "-", $sections));
		fwrite ($handle, "[".$section."]\r\n");
		foreach ($items as $keys => $values) {
			//Write the key/value pairs
			//$key = ucfirst(preg_replace('/[\0-\37]|=|[\177-\377]/', "-", $keys));
			$key = ucfirst(preg_replace('/[\0-\37]|=|\177/', "-", $keys));
			if (substr($key, 0, 1)==$commentchar) { $key = '-'.substr($key, 1); }
			$value = ucfirst(addcslashes($values,''));
			fwrite ($handle, '    '.$key.' = "'.$value."\"\r\n");
		}
	}
	fclose($handle);
}

/**
 Compare versions like 0.1.2[-beta]
 where -beta is optional.

 return 0 if ver1 = ver2
 -1 if ver1 < ver2
 1 if ver1 > ver2
 */
function ver_comp($ver1, $ver2)
{
	$r1 = sscanf($ver1,"%d.%d.%d-%s");
	$r2 = sscanf($ver2,"%d.%d.%d-%s");
	if ($r1[0] == $r2[0])
	{
		if ($r1[1] == $r2[1])
		{
			if ($r1[2] == $r2[2])
			{
				if ($r1[3] == $r2[3]) return 0;
				if ($r1[3] == null) return 1;
				if ($r2[3] == null) return -1;
				return strcmp($r1[3],$r2[3]);
			}
			else
			{
				return $r1[2] - $r2[2] < 0 ? -1 : 1;
			}
		}
		else
		{
			return $r1[1] - $r2[1] < 0 ? -1 : 1;
		}
	}
	else
	{
		return $r1[0] - $r2[0] < 0 ? -1 : 1;
	}
}

function ver_suffix($version)
{
	$r = sscanf($version,"%d.%d.%d-%s");
	return count($r) == 4 ? $r[3] : false;
}



function fs_create_http_conn($url)
{
	require_once(dirname(__FILE__).'/http/http.php');
	@set_time_limit(0);
	$http=new fs_http_class;
	$http->timeout=10;
	$http->data_timeout=15;
	$http->user_agent= 'FireStats/'.FS_VERSION.' ('.FS_HOMEPAGE.')';
	$http->follow_redirect=1;
	$http->redirection_limit=5;
	$arguments = "";
	$error = $http->GetRequestArguments($url,$arguments);
	return array('status'=>(empty($error)?"ok" : $error ),"http"=>$http, "args"=>$arguments);
}


function fs_fetch_http_file($url, &$error)
{
	$res = fs_create_http_conn($url);
	if ($res['status'] != 'ok')
	{
		$error = $res['status'];
		return null;
	}
	else
	{

		$http = $res['http'];
		$args = $res['args'];
		$error=$http->Open($args);
		if (!empty($error))
		{
			return false;
		}

		$error = $http->SendRequest($args);
		if (!empty($error))
		{
			return false;
		}

		$content = '';
		for(;;)
		{
			$body = "";
			$error=$http->ReadReplyBody($body,1000);
			if($error!="" || strlen($body)==0)
			break;
			$content .= $body;
		}
		return $content;
	}
}

function fs_time_to_nag()
{
	/*
	 if donation status is not no or donated
		if last nag time > now - 32 days
		nag
		*/

	$status = fs_get_option('donation_status');
	$last_nag_time = fs_get_option('last_nag_time');
	if (!$last_nag_time)
	{
		$last_nag_time = fs_get_option('first_run_time');
	}
	if ($status != 'no' && $status != 'donated')
	{
		return time() - $last_nag_time > 60*60*24*32;

	}

	return false;
}

function fs_authenticate()
{
	global $FS_SESSION;
	return (isset($FS_SESSION['authenticated']) && $FS_SESSION['authenticated']);
}


function fs_get_relative_url($url)
{
	$text = $url;
	if ($text == "") return $text;
    $p = @parse_url($url);
    if ($p != false)
    {
        if (isset($p['scheme'])) // absolute
        {
            if ($p['host'] == $_SERVER['SERVER_NAME'])
            {
                if (isset($p['path']))		$text = $p['path'];
                if (isset($p['query'])) 	$text .= "?".$p['query'];
                if (isset($p['fragment'])) 	$text .= "#".$p['fragment'];
            }
        }
    }
    return $text;
}

function fs_get_absolute_url($path)
{
	$result = $path;
	if ($result == "") return $result;
	$p = @parse_url($path);
	if ($p === false) return $path;

	if (!isset($p['scheme'])) // relative
	{
		if (isset($_SERVER))
		{
			$port = $_SERVER['SERVER_PORT'];
			$portstr = $port == "80" ? "" : ":".$port;
			$host = $_SERVER['HTTP_HOST'];
			if ( !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on' )
			{
				$schema = "http";
			}
			else
			{
				$schema = "https";
			}

			$result = $schema."://".$host.$portstr.$path;
		}
	}
	return $result;
}
?>

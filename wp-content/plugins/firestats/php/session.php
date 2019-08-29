<?php
/**
 * This file contains API that allow usage of sessions independent of the HTTP header state.
 * in other words, you are not required to initialize the session only before any data was 
 * was sent in the http body.
 * however, you are responsible to transmit the session id to the client, and
 * to restore the session with the session id recieved from the client using the function
 * fs_session_start($sid).
 */

// minimum time between garbage collection
define('GC_INTERVAL_SECONDS',60*30);

// number of seconds before timing out a session.
define('SESSION_TIMEOUT',60*60);

// sessions dir
define('SESSIONS_DIR','fs_sessions');

// true to activate debug output on errors.
define('FS_SESSION_DEBUG',false);


/**
 * Regisrer fs_store_session as a shutdown function, to ensure the session is saved on shutdown.
 */
register_shutdown_function('fs_store_session');

/**
 * ensure sys_get_temp_dir is available even on php4
 */
fs_ensure_sys_get_temp_dir_available();

/**
 * Initializes the session temp dir.
 */
fs_initialize_session_dir();

/**
 * Get the session ID of the current session.
 */
function fs_get_session_id()
{
	global $FS_SESSION;
	if (isset($FS_SESSION))
	{
		return $FS_SESSION['sid'];
	}
	return false;
}

/**
 * initializes the sesssion.
 * if $sid is not supplied to the function (or if its null), the function will create a fresh session.
 * if $sid is supplied, the function will attempt to load the session from the storage.
 * returns : true if the session was initialized, false in case of an error.
 */
function fs_session_start($sid = null)
{
	global $FS_SESSION;
	if (isset($FS_SESSION['sid']))
	{
		return true;
	}

	$dir = $GLOBALS['FS_TEMP_DIR'];
	if (empty($sid))
	{
		$sid = '';
		$tries = 10;
		do
		{
			$tries--;
			$rand = mt_rand();
			$now = microtime();
			$sid = md5($rand."_".$now);
			$fname = "$dir/session_$sid";
			if (file_exists($fname)) continue;
			$handle = fopen($fname, "w+");
		}
		while ($handle === false && $tries > 0);
		fclose($handle);

		if ($tries == 0)
		{
			// failed to start session.
			return false;
		}

		$session = array();
		$session['sid'] = $sid;
		$session['accessed'] = time();
		$GLOBALS['FS_SESSION'] = $session;
		// store the session now,
		// to make sure its already available to sub-scripts that attempt to
		// access the session information before this script has terminated.
		fs_store_session();
		return true;
	}
	else // need to load existing session.
	{
		// garbage collect first.
		fs_session_gc();

		if (file_exists("$dir/session_$sid"))
		{
			$handle = @fopen("$dir/session_$sid","r");
			if ($handle != false)
			{
				$fresh = false;		
				$str = @fgets($handle);
				fclose($handle);
				if ($str != false)
				{
					$session = unserialize($str);
					$accessed = isset($session['accessed'])? (int)$session['accessed'] : 0;
					$fresh = time() - $accessed < SESSION_TIMEOUT;
					if ($fresh)
					{
						$GLOBALS['FS_SESSION'] = $session;
					}
				}
				return $fresh;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
}

/**
 * Stores a session object, called automatically when the script processing is complete.
 */
function fs_store_session()
{
	if (isset($GLOBALS['FS_SESSION']))
	{
		$session = $GLOBALS['FS_SESSION'];
		$session['accessed'] = time();
		$sid = $session['sid'];
		$dir = $GLOBALS['FS_TEMP_DIR'];
		$handle = fopen("$dir/session_$sid","w+");
		fputs($handle,serialize($session));
		fclose($handle);
	}
}

/**
 * Garbage collect stale session.
 * this is called automatically and perform its operation at most every GC_INTERVAL_SECONDS.
 */
function fs_session_gc()
{
	$last_gc = 0;
	$dir = $GLOBALS['FS_TEMP_DIR'];
	$fname = "$dir/last_gc";
	if (file_exists($fname))
	{
		$f = file($fname);
		if (isset($f[0]) && is_numeric($f[0])) $last_gc = (int)$f[0];
	}

	if (time() - $last_gc > GC_INTERVAL_SECONDS)
	{
		//echo "running gc</br>";
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh)))
		{
			if ($filename != 'last_gc' && $filename != '.' && $filename != '..')
			{
				$rotten = false;
				
				$fn = "$dir/$filename";
				$fd = @fopen($fn, "r");
				if ($fd == false) 
				{
					if (FS_SESSION_DEBUG) echo "Error opening $fn";
					// error opening the file, assume invalid and (try to) gc it.
					$rotte = true;
				}
				else
				{
					$str = fgets($fd);
					fclose($fd);
					if ($str == false) 	
					{
						// error reading the  file, assume invalid and gc it.
						$rotten = true;	
					}
					else
					{
						$session = @unserialize($str);
						
						if ($session != false)
						{
							$accessed = (int)$session['accessed'];
							//echo "elapsed " . (time() - $accessed) . "<br/>";
							$rotten = time() - $accessed >= SESSION_TIMEOUT;
						}
						else
						{
							if (FS_SESSION_DEBUG) echo "Error unserialzing $fn";
							// bad file.
							$rotten = true;
						}
					}
				}

				
				if ($rotten)
				{
					//echo "unlinking $filename</br>";
					if(!@unlink($fn))
					{
						if (FS_SESSION_DEBUG) echo "Error unlinking $fn";
					}
				}
			}
		}

		$fd = fopen($fname,"w+");
		fputs($fd,time());
		fclose($fd);
	}
}

function fs_initialize_session_dir()
{
	$help_url = "http://firestats.cc/wiki/ErrorInitializingSessionsDir";
	$text = sprintf("<h3>Error initializing sessions directory, read <a href='$help_url'>this<a/> for help</h3><br/><span style='color:red'>%%s<span>");

	$temp_dir = sys_get_temp_dir();
	
	$home_temp_dir = dirname(dirname(__FILE__));
	$home_temp_dir .= "/".SESSIONS_DIR;

	// in one of the following coditions is true try to use sessions directory under firestats directory:
	// 1. home_temp exists 
	// 2. can't detect temp directory 
	// 3. php is running in safe mode 
	// 4. temp is not writable.
	if(is_dir($home_temp_dir) || ini_get('safe_mode') == 1 || $temp_dir === false || !fs_is_writable($temp_dir))
	{
		// sessions dir not found?
		// we require that the user create the directory because
		// if we create it the user will not be able to delete it.
		if (!is_dir($home_temp_dir)) 
		{
			die(sprintf($text,"Directory ,'<b>$home_temp_dir</b>' does not exist"));
		}
		
		if (!fs_is_writable($home_temp_dir))
		{
			die(sprintf($text,"Directory ,'<b>$home_temp_dir</b>' is not writable by the PHP user"));
		}
		$temp_dir = $home_temp_dir;
	}
	else // temp directory exists, normal flow.
	{
		// make sure the dir ends with /
		$last = substr($temp_dir, strlen( $temp_dir ) - 1 );
		if ($last != "/" && $last != '\\') $temp_dir .= "/";
		$temp_dir .= SESSIONS_DIR;	
		if (!is_dir($temp_dir)) // sessions dir not found?
		{
			if (!fs_is_writable($temp_dir))
			{
				die(sprintf($text,"Directory ,'<b>$temp_dir</b>' must be writable"));
			}
			
			if (!mkdir($temp_dir, 0700))
			{
				die(sprintf($text,"Failed to create '<b>$temp_dir</b>'"));
			}
			
		}
	}
	
	// unfortunatelly, not all systems has posix_getuid, also - this cause problems when safe_mode is turned on.
	// if we run on a system that does not have it, and also happens to
	// have multiple (system) users accessing firestats, we are screwed. 
	if (function_exists("posix_getuid") && ini_get('safe_mode') != 1)
	{
		$temp_dir .= "/".posix_getuid();
	}
	
	if (!@file_exists($temp_dir) && !mkdir($temp_dir, 0700))
	{
		die(sprintf($text,"Failed to create '<b>$temp_dir</b>'"));
	}	
	
	$GLOBALS['FS_TEMP_DIR'] = $temp_dir;
}

function fs_ensure_sys_get_temp_dir_available()
{
	if ( !function_exists('sys_get_temp_dir') )
	{
		// Based on http://www.phpit.net/
		// article/creating-zip-tar-archives-dynamically-php/2/
		function sys_get_temp_dir()
		{
			// Try to get from environment variable
			if ( !empty($_ENV['TMP']) )
			{
				return realpath( $_ENV['TMP'] );
			}
			else if ( !empty($_ENV['TMPDIR']) )
			{
				return realpath( $_ENV['TMPDIR'] );
			}
			else if ( !empty($_ENV['TEMP']) )
			{
				return realpath( $_ENV['TEMP'] );
			}
			// Detect by creating a temporary file
			else
			{
				// Try to use system's temporary directory
				// as random name shouldn't exist
				$temp_file = @tempnam( md5(uniqid(rand(), TRUE)), '' );
				if ( $temp_file )
				{
					$temp_dir = realpath( dirname($temp_file) );
					if (!@unlink( $temp_file )) return FALSE;
					return $temp_dir;
				}
				else
				{
					return FALSE;
				}
			}
		}
	}
}

function fs_is_writable($path) 
{
	//will work in despite of Windows ACLs bug
	//NOTE: use a trailing slash for folders!!!
	//see http://bugs.php.net/bug.php?id=27609
	//see http://bugs.php.net/bug.php?id=30931

	if ($path{strlen($path)-1}=='/') // recursively return a temporary file path
		return fs_is_writable($path.uniqid(mt_rand()).'.tmp');
	else if (is_dir($path))
		return fs_is_writable($path.'/'.uniqid(mt_rand()).'.tmp');
	// check tmp file for read/write capabilities
	$rm = file_exists($path);
	$f = @fopen($path, 'a');
	if ($f===false)
		return false;
	fclose($f);
	if (!$rm)
		@unlink($path);
	return true;
}


function fs_resume_existing_session()
{
	if (!isset($_REQUEST['sid'])) 
	{
		return "sid not specified";
	}
	
	$got_session  = fs_session_start($_REQUEST['sid']);
	global $FS_SESSION;
	if ($got_session)
	{
		global $FS_CONTEXT;
		$FS_CONTEXT = $FS_SESSION['context'];
		return true;
	}
	else
	{
		return "Session expired";
	}
}


?>

<?php
	/*
		This file contains FireStats external APIs.
		if no api function is called, the footprint of the api is minimal - 
		as the real code is only included when an API function is called.
	*/

define ('FS_API','enabled');

/*
	Returns the number of pages displayed in the specified time period.
	days_ago is an optional parameter which specifies how many days ago to start counting.
	if days_ago is not specified, the count will begin when you installed FireStats.
*/
function fs_api_get_hit_count($days_ago = NULL)
{
	require_once(dirname(__FILE__).'/db-sql.php');
	return fs_get_hit_count($days_ago);
}

/*
	Returns the number of unique hits in the specified time period.
	days_ago is an optional parameter which specifies how many days ago to start counting.
	if days_ago is not specified, the count will begin when you installed FireStats.
*/
function fs_api_get_unique_hit_count($days_ago = NULL)
{
	require_once(dirname(__FILE__).'/db-sql.php');
	return fs_get_unique_hit_count($days_ago);
}

/*
	Returns image tags of images representing the useragent
	3 Icons may be returned:
	* OS Icon
	* Browser Icon
	* PDA Icon (if the useagent is of a phone)

	To access the user agent of the current user in PHP use $_SERVER['HTTP_USER_AGENT']
*/ 
function fs_api_get_browser_and_os_images($useragent)
{
	require_once(dirname(__FILE__).'/browsniff.php');
	return fs_pri_browser_images($useragent);
}

/*
	Returns an image tag with the flag of the country this ip_address blonged to.
	if unknown, an empty string is returned.
*/
function fs_api_get_country_flag_image($ip_address)
{
	require_once(dirname(__FILE__).'/ip2country.php');
	$code = fs_ip2c($ip_address);
	if ($code != false) return fs_get_country_flag_url($code);
	else return '';
}

/*
	Returns a two characters country code of the country this ip address is belonged to.
	if unknown, false is returned.
*/
function fs_api_get_country_code($ip_address)
{
	require_once(dirname(__FILE__).'/ip2country.php');
	return fs_ip2c($ip_address);
}

?>

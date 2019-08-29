<?php
/*
 * Counterize detected? (wordpress specific)
 */
function fs_counterize_detected()
{
	if (fs_in_wordpress() && isset($GLOBALS) && array_key_exists('table_prefix',$GLOBALS))
	{
		$fsdb = &fs_get_db_conn();
		$sql = "SHOW TABLES LIKE '".$GLOBALS['table_prefix']."Counterize'";
		return $fsdb->query($sql) != 0;
	}
	else return false;
}


/**
	import from counterize database.
	return codes:
  -1: counterize database not found
*/
function fs_import_counterize()
{
	if (!fs_counterize_detected()) return array('status'=>'error','message'=>fs_r('Counterize not detected'));
	$fsdb = &fs_get_db_conn();
	$fsdb->hide_errors();
	$counterize = $GLOBALS['table_prefix']."Counterize";
	$useragents = fs_useragents_table();
	$urls = fs_urls_table();
	$referers = fs_referers_table();
	$hits = fs_hits_table();
	$bots = fs_bots_table();
	$temp = fs_temp_table();

	$r = $hits_before = $fsdb->get_var("SELECT COUNT(id) FROM $hits");
	if($r === FALSE) return array('status'=>'error','message'=>$fsdb->last_error);

	$r = $fsdb->query("INSERT IGNORE INTO `$useragents` (`useragent`,`md5`) 
						SELECT DISTINCT `useragent`,MD5(`useragent`) FROM $counterize");
	if($r === FALSE) return array('status'=>'error','message'=>$fsdb->last_error);

	$r = $fsdb->query("INSERT IGNORE INTO `$urls` (`url`) SELECT DISTINCT `url` FROM $counterize");
	if($r === FALSE) return array('status'=>'error','message'=>$fsdb->last_error);
	
	$r = $fsdb->query("INSERT IGNORE INTO `$referers` (`referer`) SELECT DISTINCT `referer` FROM $counterize");
	if($r === FALSE) return array('status'=>'error','message'=>$fsdb->last_error);
	
	$r = $fsdb->query("INSERT IGNORE INTO `$hits` 
						(
							ip,
							timestamp,
							url_id,
							referer_id,
							useragent_id
						)
						SELECT 	c.ip, 
										c.timestamp, 
										urls.id, 
										referers.id, 
										uas.id
						FROM $counterize c,$useragents uas,$urls urls,$referers referers
						WHERE c.useragent = uas.useragent AND c.url = urls.url AND c.referer = referers.referer");

	
	if (fs_recalculate_db_cache() === false) return array('status'=>'error','message'=>$fsdb->last_error);
	if($r === FALSE) return array('status'=>'error','message'=>$fsdb->last_error);


	$r = $hits_after = $fsdb->get_var("SELECT COUNT(id) FROM $hits");
	if($r === FALSE) return array('status'=>'error','message'=>$fsdb->last_error);

	return array('status'=>'ok','count'=>($hits_after - $hits_before));

}


?>

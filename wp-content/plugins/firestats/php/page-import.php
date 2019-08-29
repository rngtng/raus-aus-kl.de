<script type="text/javascript">
//<![CDATA[
function recalculateDBCache()
{
    var params = 'action=reclaculateDBCache';
    sendRequest(params);
}

function importCounterize()
{
    var b = $('import_counterize');
    b.disabled = true;
    b.innerHTML = "<?php fs_e('Import in progress')?>";
    sendRequest('action=importCounterize');
}

//]]>
</script>

<?php
require_once(dirname(__FILE__).'/db-import.php');

fs_e('You can import your hits from another statistics system here.');
?>
<h3><?php fs_e('Recalculate database cache') ?></h3>
<?php fs_e('In case of data consistency problems, The database cache may need to be rebuilt.')?><br/>
<?php fs_e('Its not normally needed, but it does not cause any damage')?><br/>
<button class="button" id="rebuild_db_cache" onclick="recalculateDBCache()"><?php echo fs_r("Recalculate database cache")?></button><br/>
<br/>
<h3><?php fs_e('Counterize')?></h3>

<?php 
if (fs_counterize_detected())
{	
	echo sprintf(fs_r('%s detected'),fs_r('Counterize')).'<br/>';
	echo '<button class="button" id="import_counterize" onclick="importCounterize()">'.fs_r("Import").'</button><br/>';

}
else
{
	echo sprintf(fs_r('%s was not detected'),fs_r('Counterize'));
}
?>

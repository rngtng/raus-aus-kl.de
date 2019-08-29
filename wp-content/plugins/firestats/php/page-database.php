<script type="text/javascript">
//<![CDATA[
function testDBConnection()
{
    var host    = $F('text_database_host');
    var user    = $F('text_database_user');
    var pass    = $F('text_database_pass');
    var dbname  = $F('text_database_name');
    var prefix  = $F('text_database_prefix');
    var params = 'action=testDBConnection&host=' + host + "&user=" + user + "&pass="+pass+"&dbname="+dbname+"&table_prefix="+prefix;
    sendRequest(params);
}

function installDBTables()
{
    var host    = $F('text_database_host');
    var user    = $F('text_database_user');
    var pass    = $F('text_database_pass');
    var dbname  = $F('text_database_name');
    var prefix  = $F('text_database_prefix');
    var params = 'action=installDBTables&host=' + host + "&user=" + user + "&pass="+pass+"&dbname="+dbname+"&table_prefix="+prefix;
    sendRequest(params);
}


function attachToDatabase()
{
    var host    = $F('text_database_host');
    var user    = $F('text_database_user');
    var pass    = $F('text_database_pass');
    var dbname  = $F('text_database_name');
    var prefix  = $F('text_database_prefix');
    var params = 'action=attachToDatabase&host=' + host + "&user=" + user + "&pass="+pass+"&dbname="+dbname+"&table_prefix="+prefix;
    sendRequest(params);
}

function createNewDatabase()
{
    var user        = $F('text_database_firestats_user');
    var pass        = $F('text_database_firestats_pass');
    var host        = $F('text_database_host');
    var admin_user  = $F('text_database_user');
    var admin_pass  = $F('text_database_pass');
    var dbname      = $F('text_database_name');
    var prefix      = $F('text_database_prefix');
    var params      =   'action=createNewDatabase&host=' + host +
                        "&user=" + user + "&pass=" + pass +
                        "&dbname=" + dbname + "&table_prefix="+prefix +
                        "&admin_user=" + admin_user + "&admin_pass=" + admin_pass;
    sendRequest(params);

}

function useWordpressDB()
{
    var params = 'action=useWordpressDB';
    sendRequest(params);
}

function upgradeDatabase()
{
    var params = 'action=upgradeDatabase';
    sendRequest(params);
}

function unlockDBConfig()
{
    sendRequest('action=unlockDBConfig&password=' + $F('db_unlock_password'));
}

function lockDBConfig()
{
    sendRequest('action=lockDBConfig&password=' + $F('db_lock_password'));
}

//]]>
</script>


<?php
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/db-config-utils.php');
require_once(dirname(__FILE__).'/db-common.php');


$db_config_type = fs_get_db_config_type();
$cfg_source = fs_get_config_source_desc();
?>


<?php 
fs_load_config();
$fs_db_config_locked = fs_db_config_locked();
$GLOBALS['fs_db_config_locked'] = $fs_db_config_locked;
?>
<div>
<table>
	<?php if (!fs_db_valid())
	{?>
	<tr>
		<td>
		<?php
		echo fs_r('Database status')." : <b style='color:red'>".fs_get_database_status_message()."</b>";
		?>
		</td>
	</tr>
	<?php 
	}?>
	<tr>
		<td>
		<?php fs_output_database_table()?>
		</td>
		<td style="vertical-align:top">
		<?php fs_output_database_help()?>
		</td>
	</tr>
</table>
</div>

<?php



function fs_output_database_table()
{
global $fs_db_config_locked;
?>
<table>
	
	<?php
	$st = fs_get_db_status();
	if ($st['status'] == FS_DB_NEED_UPGRADE)
	{
	?>
	<tr>
	<td colspan="2">
		<div class="wrap" id="database_upgrade_div">
		<span class="notice"><?php fs_e('Click to upgrade')?></span>
		<button class="button" onclick="upgradeDatabase()"><?php fs_e('Upgrade');?></button>
		</div> <!-- wrap -->
	</td>
	</tr>
	<?php
	}
	?>

	<tr>
		<td colspan="2">
			<?php fs_output_lock_panel()?>
		</td>
	</tr>
</table>


<div id="database_table_config_div" style='display:<?php echo ($fs_db_config_locked ? 'none' : 'block') ?>'>
<table>


	<tr>
		<td colspan="2">
			<div class="wrap">
			<?php
				$cfg_source = fs_get_config_source_desc();
				$cfg_source_div = "<div id='config_source'><b>$cfg_source</b></div>";
				echo sprintf(fs_r('Configuration source : %s'),$cfg_source_div);
				echo '<div id="switch_to_external_system">';
				if (fs_should_show_use_wp_button())
				{
				?>
					<br/>
					<?php fs_e('To use wordpress database, click this button.')?><br/>
					<?php echo '<div class="notice">'.fs_r('FireStats database configuration will be lost.').'</div>'?><br/>
					<button class="button" onclick="useWordpressDB()"><?php fs_e('Use Wordpress database');?></button>	
				<?php
				}
				echo '</div>';
			?>
			</div> <!-- wrap -->
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="wrap">
				<?php fs_e('To perform one of the following actions:')?>
				<ul>
					<li><?php fs_e('Attach to an existing FireStats installation')?></li>
					<li><?php fs_e('Install FireStats into an existing database')?></li>
					<li><?php fs_e('Create a new database and install FireStats there')?></li>
				</ul>
				<?php fs_e('Follow instructions in the help')?>
			</div>	
		</td>
	</tr>
	<tr>
		<?php 
			global $fs_config;
			global $fs_db_config_locked;
		?>
		<td><?php fs_e('Database host')?></td>
		<td id="holder_database_host">
			<input type="text" size="30" name="text_database_host" id="text_database_host" value="<?php if(!$fs_db_config_locked) print $fs_config['DB_HOST'] ?>"/>
		</td>
	</tr>
	<tr>
		<td><?php fs_e('Database name')?></td>
		<td id="holder_database_name">
			<input type="text" size="30" name="text_database_name" id="text_database_name" value="<?php if(!$fs_db_config_locked) print $fs_config['DB_NAME'] ?>"/>
		</td>
	</tr>
	<tr>
		<td><?php fs_e('Database user name')?></td>
		<td id="holder_database_user">
			<input type="text" size="30" name="text_database_user" id="text_database_user" value="<?php if(!$fs_db_config_locked) print $fs_config['DB_USER']?>"/>
		</td>
	</tr>
	<tr>
		<td><?php fs_e('Database password')?></td>
		<td id="holder_database_pass">
			<input type="password" size="30" name="text_database_pass" id="text_database_pass" value="<?php // don't send password, too risky?>"/>
		</td>
	</tr>
	<tr>
		<td><?php fs_e('Tables prefix')?></td>
		<td id="holder_database_prefix">
			<input type="text" size="30" name="text_database_prefix" id="text_database_prefix" value="<?php if(!$fs_db_config_locked) print $fs_config['DB_PREFIX']?>"/>
		</td>
	</tr>
</table>

<table>
	<tr>
		<td><button class="button" onclick="testDBConnection()"><?php fs_e('Test connection');?></button></td>
		<td><div id="advanced_feedback"></div></td>
	</tr>
</table>

</div>

<div style="display:none" id="install_tables_id" class="wrap">	
<table>
	<tr>
		<td><?php fs_e('Click to install and switch to new FireStats tables')?></td>
	</tr>
	<tr>
		<td><button class="button" onclick="installDBTables()"><?php fs_e('Install tables');?></button></td>
	</tr>
</table>
</div>


<div id="use_database_id" style="display:none" class="wrap">	
<table>
	<tr>
		<td><?php fs_e('Click to use this FireStats database')?></td>
	</tr>
	<tr>
		<td>
			<button class="button" onclick="attachToDatabase()"><?php fs_e('Use this database');?></button>
		</td>
	</tr>
</table>
</div>


<div id="create_db_id" style="display:none" class="wrap">
<table>
	<tr>
		<td colspan="2">
			<?php
			echo fs_r('To create a new database, an administrator user should be used in the above fields.').'<br/>';
			echo fs_r('However, For security reason, it is recommanded not to use a database administrator user for day to day operations.').'<br/>';
			echo fs_r('Enter new user name and password for FireStats, this user will only have access to the FireStats database').'<br/>';
			echo fs_r('If you will not specify user and password the Admin user and password will be used.').'<br/>';
			?>
		</td>
	</tr>
</table>
<table>
    <tr>
        <td><?php fs_e('FireStats User name')?></td>
        <td><input type="text" size="30" id="text_database_firestats_user" value=""/></td>
    </tr>
    <tr>
        <td><?php fs_e('FireStats password')?></td>
        <td><input type="password" size="30" id="text_database_firestats_pass" value=""/></td>
    </tr>
</table>
<table>
	<tr>
		<td><button class="button" onclick="createNewDatabase()"><?php fs_e('Create database');?></button></td>
		<td><div id="new_db_feedback"></div></td>
	</tr>
</table>
</div>
<?php
}

function fs_output_database_help()
{
global $fs_db_config_locked;
?>
<div class="wrap" id="database_help_panel_div" style='display:<?php echo ($fs_db_config_locked ? 'none' : 'block') ?>'>
	<h2><?php fs_e('Help')?></h2>
	<h3><?php fs_e('Attach to an existing FireStats database')?></h3>
	<?php echo sprintf(fs_r('Enter the parameters in the table, press %s, and then %s.'),'<b>'.fs_r('Test connection').'</b>','<b>'.fs_r('Use this database').'</b>')?>
	<br/>
	<h3><?php fs_e('Install FireStats into an existing database')?></h3>
	<?php echo sprintf(fs_r('Enter the parameters in the table, press %s, and then %s.'),'<b>'.fs_r('Test connection').'</b>','<b>'.fs_r('Install tables').'</b>')?>
	<br/>
	<h3><?php fs_e('Create a new database and install FireStats there')?></h3>
	<?php echo sprintf(fs_r('Enter the parameters into the table, <u>you need to enter a database administrator user</u>. press %s, and then enter FireStats database user and password, and finally press %s.'),'<b>'.fs_r('Test connection').'</b>','<b>'.fs_r('Create database').'</b>')?>
</div>
<div class="wrap" id="lock_help_panel_div" style='display:<?php echo (!$fs_db_config_locked ? 'none' : 'block') ?>'>
	<h2><?php fs_e('Help')?></h2>
	<h3><?php fs_e('Configuration locked')?></h3>
	<?php echo fs_r('To change the database configuration you need to unlock it, please enter the password.').'<br/>'?>
	<?php echo sprintf(fs_r('if you forgot your password you can simply delete the file %s from firestats/php directory.'),'<b>fs-config-lock.php</b>')?>
	<br/>
</div>


<?php
}

function fs_output_lock_panel()
{
global $fs_db_config_locked;
?>
<div class="wrap" id="database_unlock_panel_div" style='display:<?php echo (!$fs_db_config_locked ? 'none' : 'block') ?>'>
	<h3><?php fs_e('Configuration locked')?></h3>
	<?php fs_e('Database configuration is locked, to unlock for 5 minutes enter the lock password.')?><br/>
	<div>
	<?php fs_e('Password')?>: <input id="db_unlock_password" name="db_unlock_password" type="password" size="10"></input>
	<button class="button" onclick="unlockDBConfig()"><?php fs_e('Unlock');?></button>	
	</div>
</div>
<div class="wrap" id="database_lock_panel_div" style='display:<?php echo ($fs_db_config_locked ? 'none' : 'block') ?>'>
	<h3><?php fs_e('Configuration unlocked')?></h3>
	<?php fs_e('Database configuration is open for changes, enter a password to lock.')?><br/>
	<div>
	<?php fs_e('Password')?>: <input id="db_lock_password" name="db_lock_password" type="password" size="10"></input>
	<button class="button" onclick="lockDBConfig()"><?php fs_e('Lock');?></button>	
	</div>
</div>

<?php
}

?>

<?php
/*
Plugin Name: FireStats 
Plugin URI: http://firestats.cc
Description: Statistics plugin for WordPress.
Version: 1.2.4-stable
Author: Omry Yadan
Author URI: http://blog.firestats.cc
*/

global $FS_CONTEXT;
$FS_CONTEXT = array();
$FS_CONTEXT['TYPE'] = 'WORDPRESS';
$FS_CONTEXT['WP_PATH'] = fs_get_wp_config_path();

// this is an internal version of this file that is used by firestats core to 
// detect if the correct file is installed. 
// (there can in be inconsistencies in case of a satelitte installation).
define('FS_WORDPRESS_PLUGIN_VER','3');

add_action('wp_head', 'fs_add_wordpress', 1);

add_action('admin_footer', 'fs_admin_footer');
add_action('admin_menu', 'fs_add_page');
add_action('admin_head', 'fs_output_css');
add_action('admin_menu', 'fs_add_options');
add_action('widgets_init', 'fs_widget_init');


if (get_option('firestats_add_comment_flag') == 'true')
{
	add_filter('get_comment_author_link', 'fs_add_comment_flag');
}

if (get_option('firestats_add_comment_browser_os') == 'true')
{
	add_filter('get_comment_author_link', 'fs_add_comment_browser_os');
}

// show footer by default
if (get_option('firestats_show_footer') != 'false')
{
	add_action('wp_footer','fs_echo_footer');
}

function fs_full_installation()
{
    return file_exists(dirname(__FILE__).'/php/db-hit.php');
}

function fs_override_base_url()
{
	if (fs_full_installation())
	{
		$site_url = get_option("siteurl");

		// make sure the url ends with /
		$last = substr($site_url, strlen( $site_url ) - 1 );
		if ($last != "/") $site_url .= "/";
		
		// calculate base url based on current directory.
		$base_len = strlen(ABSPATH);
		$suffix = substr(dirname(__FILE__),$base_len)."/";
		// fix windows path sperator to url path seperator.
		$suffix = str_replace("\\","/",$suffix);
		$base_url = $site_url . $suffix;
		return $base_url;
	}
	else // not full installation == satelite of a standlone firestats.
	{
		$url = get_option('firestats_url');
		// make sure the url ends with /
		$last = substr($url, strlen( $url ) - 1 );
		if ($last != "/") $url .= "/";
		return $url;
	}
}

function fs_get_firestats_path()
{
	$page = $_SERVER['SCRIPT_FILENAME'];
	// if the page is plugins.php dont output anything because we are being activated now and it fuck things up
	//$quiet = fs_endsWith($page,'plugins.php');
	if (!fs_full_installation())
	{
		$path = get_option('firestats_path');
		if ($path == null || $path == '')
		{
			//if (!$quiet) _e('You need to configure the FireStats plugin in the options tab<br/>');
			return false;
		}
		else
			if (!file_exists($path.'/php/db-hit.php'))
			{
				//if (!$quiet) echo sprintf(__("FireStats was not detected at %s"),"<b>$path</b>")."<br/>";
				return false;
			}
			else
			{
				return $path;
			}
	}
	else
	{
		return dirname(__FILE__);
	}
}

$GLOBALS['__path'] = fs_get_firestats_path();
global $__path;
if (!$__path) return;

require_once($__path.'/php/db-hit.php');
// in a transition stage beween old and new version, this might blow up if the api file does not exist.
// hence, the check.
if (file_exists($__path.'/php/api.php')) require_once($__path.'/php/api.php');

# Small info on DashBoard-page
function fs_admin_footer()
{
	$admin = dirname($_SERVER['SCRIPT_FILENAME']);
	$admin = substr($admin, strrpos($admin, '/')+1);
	$query = $_SERVER["QUERY_STRING"];
	if ($admin == 'wp-admin' && basename($_SERVER['SCRIPT_FILENAME']) == 'index.php' && $query == '')
	{
		global $__path;
		if (!$__path) return;
		require_once($__path.'/php/db-sql.php');
		$url = fs_get_firestats_url();
		$title = "<h3>".fs_r("FireStats"). $url."</h3><span id ='firestats_span'>".fs_r('Loading...')."</span>";
		print 
			'<script language="javascript" type="text/javascript"> 
				var e = document.getElementById("zeitgeist");
				if (e)
				{
					var div = document.createElement("DIV");
					div.id = div.innerHTML = "'.$title.'";
					e.appendChild(div);
				} 
			</script> ';
		flush();
		
		$count = fs_get_hit_count();
		$unique = fs_get_unique_hit_count();
		$last_24h_count= fs_get_hit_count(1);
		$last_24h_unique = fs_get_unique_hit_count(1);

		echo "<!-- admin = $admin, script =  ".basename($_SERVER['SCRIPT_FILENAME'])."  -->";	
		$content.= sprintf(fs_r("Total : %s page views and %s visits"),'<strong>'.$count.'</strong>','<strong>'.$unique.'</strong>').'<br/>';
		$content.= sprintf(fs_r("Last 24 hours : %s page views and %s visits"),'<strong>'.$last_24h_count.'</strong>','<strong>'.$last_24h_unique.'</strong>').'<br/>';
		print 
		'<script language="javascript" type="text/javascript"> 
			var e = document.getElementById("firestats_span");
			if (e)
			{
				e.innerHTML = "'.$content.'";
			} 
			</script> ';
		}
}

function fs_get_firestats_url($txt = null)
{
	$txt = $txt ? $txt  : "&raquo;";
	if (fs_full_installation())
	{
		// hack around stupid wp bug under windows
		if (fs_is_windows())
		{
		    $link = "index.php?page=firestats%5Cfirestats-wordpress.php";
		    
		}
		else
		{
		    $link = "index.php?page=firestats/firestats-wordpress.php";
		}
		$url = "<a href='$link'>$txt</a>";
	}
	else
	{
		$url = "<a href='index.php?page=firestats-wordpress.php'>$txt</a>";
	}
	return $url;
}

# Do the installation stuff, if the plugin is marked to be activated...
$install = (basename($_SERVER['SCRIPT_NAME']) == 'plugins.php' && isset($_GET['activate']));;
if ($install)
{
	require_once($__path.'/php/db-setup.php');
	fs_install();
	fs_register_wordpress();
}


function fs_page()
{
	$path = fs_get_firestats_path();
	if ($path)
	{
		require_once($path.'/php/utils.php');
		if (current_user_can('manage_options') || defined('DEMO'))
		{
			require_once($path.'/php/tabbed-pane.php');
		}
		else
		{
			fs_e("Only blog administrators can access FireStats");
		}
	}
	else
	{
		$href = sprintf("<a href='options-general.php?page=firestats-wordpress.php'>%s</a>",__('Options'));
		echo sprintf(__('You need to configure FireStats in the %s menu'),$href);
	}
}

function fs_endsWith( $str, $sub ) {
   return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
}

function fs_output_css()
{
	$path = fs_get_firestats_path();
	if ($path)
	{
		$name = $_SERVER["QUERY_STRING"];
		if (fs_endsWith($name,'firestats-wordpress.php'))
		{
		?>
		<link rel="stylesheet" href="<?php echo fs_url('css/base.css');?>" type='text/css'/>
		<link rel="stylesheet" href="<?php echo fs_url('css/mktree.css.php');?>" type='text/css'/>
		<link rel="stylesheet" href="<?php echo fs_url('css/page-sites.css.php');?>" type='text/css'/>
		<!--[if lt IE 7]>
			<link rel="stylesheet" href="<?php echo fs_url('css/ie6-hacks.css');?>" type="text/css" />
		<![endif]-->
		<!--[if IE]>
			<link rel="stylesheet" href="<?php echo fs_url('css/ie-hacks.css');?>" type="text/css" />
		<![endif]-->
		<?php
		}
	}
}


function fs_add_wordpress()
{
	global $__path;
	if (!$__path) return;
	if (is_404()) return; // don't log 404.
	require_once($__path.'/php/db-hit.php');
	$firestats_site_id = get_option('firestats_site_id');
	// extract user ID in a wordpress specific method.
	global $user_ID;
	get_currentuserinfo();
 	// add with the user ID (or with null)
	fs_add_site_hit($firestats_site_id, $user_ID, false);
}

function fs_add_options() 
{
	if (!fs_full_installation())
	{
		add_options_page('FireStats', 'FireStats', 1, __FILE__, 'fs_options_page');
	}
}

# Add a sub-menu to the "manage"-page.
function fs_add_page()
{
	add_submenu_page('index.php', 'FireStats', 'FireStats', 1, __FILE__, 'fs_page');
}


function fs_options_page()
{
	$path = get_option('firestats_path');
	$url = get_option('firestats_url');
	?>
	<div class="wrap">
	<h2><?php _e('FireStats configuration')?></h2>
	<form method="post" action="options.php">
	<?php if (function_exists('wp_nonce_field')) wp_nonce_field('update-options') ?>
	<input type="hidden" name="action" value="update" /> 
	<input type="hidden" name="page_options" value="firestats_path,firestats_url" />
	<?php
	$path_good = false;
	$url_good = false;
	$path_version = '';
	$url_version = '';
	if (ini_get('safe_mode') == 1)
	{
		?>
		<div class="error"><?php _e("Your PHP is configured in safe mode, FireStats may be impossible to configure in satellite mode.")?></div>	
		<?php
	}

	if (!empty($path))
	{
		$len = strlen($path);
		if ($path[$len - 1] != '/' && $path[$len - 1] != '\\')
		{
			$path .= '/';
		}
		
		if (file_exists($path.'firestats.info'))
		{?>
		<div class="updated fade">
			<?php
				echo sprintf(__("FireStats detected at %s"),"<b>$path</b>").'<br/>';
				$path_good = true;
				$info = file($path.'firestats.info');
				$path_version = $info[0];
			?>
		</div>
		<?php
		}
		else
		{?>
		<div class="error"><?php echo sprintf(__("FireStats was not found at %s"),"<b>$path</b>")?></div>
		<?php
		}
	}
	else
	{
		echo '<div class="error">'.__("Enter the directory that contains FireStats").'</div>';
	}

	if (!empty($url))
	{
		ob_start();
		$file = file($url.'/firestats.info');
		$output = ob_clean();
		if ($file !== false)
		{?>
		<div class="updated fade">
			<?php
				echo sprintf(__("FireStats detected at %s"),"<b>$url</b>").'<br/>';
				$url_good = true;
				$url_version = $file[0];
			?>
		</div>
		<?php
		}
		else
		{?>
		<div class="error"><?php echo sprintf(__("FireStats was not found at %s"),"<b>$url</b>")?></div>
		<?php
		}
	}
	else
	{
		echo '<div class="error">'. __("Enter FireStats url").'</div>';
	}

	if ($path_good && $url_good)
	{
		if ($url_version == $path_version)
		{
			fs_register_wordpress();

			echo '<div class="updated fade">'.sprintf(__('Everything is okay, click %s to open FireStats'),'<b>'.fs_get_firestats_url('here').'</b>').'</div>';
		}
		else
		{
			?>
		<div class="error"><?php echo sprintf(__("Version mismatch between FireStats at url (%s) and FireStats at path (%s)"),$url_version, $path_version)?></div>
			<?php
		}
	}

	?>
	<table>
		<tr>
			<td><?php _e('FireStats path : ')?></td>
			<td><input type="text" class="code" id="firestats_path" name="firestats_path" size="60" value="<?php echo $path?>"/> <?php _e('Example: <b>/var/www/firestats/</b>')?></td>
		</tr>
			<td><?php _e('FireStats URL : ')?></td>
			<td><input type="text" class="code" id="firestats_url" name="firestats_url" size="60" value="<?php echo $url?>"/> <?php _e('Example: <b>http://your_site.com/firestats</b>')?></td>
		</tr>
	</table>
	<p class="submit">
	<input type="submit" name="Submit" value="<?php _e('Update options')?>&raquo;" /> 
	</p>
	</form>
	</div>
	<?php
}

function fs_widget_init()
{
    // Check for the required plugin functions. This will prevent fatal
    // errors occurring when you deactivate the dynamic-sidebar plugin.
    if ( !function_exists('register_sidebar_widget') )
        return;
	
    function fs_widget($args) 
	{
        // $args is an array of strings that help widgets to conform to
        // the active theme: before_widget, before_title, after_widget,
        // and after_title are the array keys. Default tags: li and h2.
        extract($args);

        // Each widget can store its own options. We keep strings here.
        $options = get_option('widget_firestats');
		$title = empty($options['title']) ? 'Statistics' : $options['title'];

        // These lines generate our output. Widgets can be very complex
        // but as you can see here, they can also be very, very simple.
        echo $before_widget . $before_title . $title . $after_title;
		echo fs_get_stats_box($options);
        echo $after_widget;
    }

    function fs_widget_control()
	{
		// Get our options and see if we're handling a form submission.
        $options = get_option('widget_firestats');
        if ( !is_array($options) )
            $options = array('title'=>'');
        if ( $_POST['firestats-submit']) 
		{
            // Remember to sanitize and format use input appropriately.
            $options['title'] = strip_tags(stripslashes($_POST['firestats-title']));
            update_option('widget_firestats', $options);
        }

        // Be sure you format your options to be valid HTML attributes.
        $title = htmlspecialchars($options['title'], ENT_QUOTES);
        $buttontext = htmlspecialchars($options['buttontext'], ENT_QUOTES);

        // Here is our little form segment. Notice that we don't need a
        // complete form. This will be embedded into the existing form.
		?>
        <p style="text-align:right;">
			<label for="firestats-title"><?php _e('Title:')?>
			<input style="width: 200px;" 
					id="firestats-title" 
					name="firestats-title" 
					type="text" 
					value="<?php echo $title?>"/>
			</label></p>
	        <input type="hidden" id="firestats-submit" name="firestats-submit" value="1" />
		<?php
    }

    global $__path;
    if ($__path)
	{
		require_once($__path.'/php/utils.php');
		$img = fs_url("img/firestats-icon.png");
		$name = "<img alt='FireStats icon' src='$img'/>FireStats";
	}
	else
	{
		$name = "FireStats (Not configured)";
	}


    // This registers our widget so it appears with the other available
    // widgets and can be dragged and dropped into any active sidebars.
    register_sidebar_widget(array($name, 'widgets'), 'fs_widget');

    // This registers our optional widget control form. Because of this
    // our widget will have a button that reveals a 300x100 pixel form.
    register_widget_control(array($name, 'widgets'), 'fs_widget_control', 300, 100);

}

function fs_get_stats_box()
{
   	global $__path;
	if (!$__path) return "FireStats is not configured yet";
	require_once($__path.'/php/db-sql.php');

    $powered = fs_get_powered_by('fs_footer');

	$count = fs_get_hit_count();
	$unique = fs_get_unique_hit_count();
	$last_24h_count= fs_get_hit_count(1);
	$last_24h_unique = fs_get_unique_hit_count(1);

	$total_visits  = fs_r("Pages displayed : ")."<b>$count</b>";
	$total_uniques = fs_r("Unique visitors : ")."<b>$unique</b>";
	$visits_today  = fs_r("Pages displayed in last 24 hours : ")."<b>$last_24h_count</b>";
	$uniques_today = fs_r("Unique visitors in last 24 hours : ")."<b>$last_24h_unique</b>";


	$res = "
<!-- You can customize the sidebox by playing with your theme css-->
<ul class='firestats_sidebox'> 
	<li>$total_visits</li>
	<li>$total_uniques</li>
	<li>$visits_today</li>
	<li>$uniques_today</li>
</ul>
	$powered
";
	return $res;
}

function fs_add_comment_flag($link)
{
   	global $__path;
	if (!$__path) return;
	require_once($__path.'/php/ip2country.php');
	$ip = get_comment_author_IP();
	$code = fs_ip2c($ip);
	if (!$code) return $link;
	return $link .' '. fs_get_country_flag_url($code);
}

function fs_add_comment_browser_os($link)
{
	global $comment;
	$ua = $comment->comment_agent;
	if (!$ua) return $link;
   	global $__path;
	if (!$__path) return;
	require_once($__path.'/php/browsniff.php');
	return $link . ' '.fs_pri_browser_images($ua);
}

function fs_echo_footer()
{
    global $__path;
    if (!$__path) return;
    require_once($__path.'/php/db-sql.php');

	$stats = get_option('firestats_show_footer_stats') == 'true';
	if ($stats)
	{
		$count = fs_get_hit_count();
		$unique = fs_get_unique_hit_count();
		$last_24h_count= fs_get_hit_count(1);
		$last_24h_unique = fs_get_unique_hit_count(1);
		echo $count  .' '.fs_r('pages viewed')  . ", $last_24h_count "	. fs_r('today')."<br/>";
		echo $unique .' '.fs_r('visits') 		. ", $last_24h_unique "	. fs_r('today')."<br/>";
	}
	echo fs_get_powered_by('fs_footer');
}

function fs_get_powered_by($css_class)
{
	$img = fs_url("img/firestats-icon.png");
	$firestats_url = FS_HOMEPAGE;
	$powered = "<img alt='FireStats icon' src='$img'/><a href='$firestats_url'>".fs_r("Powered by FireStats").'</a>';
	return "<span class='$css_class'>$powered</span>";
}



function fs_get_wp_config_path()
{
	$base = dirname(__FILE__);
	$path = false;

	if (file_exists($base."/../../../wp-config.php"))
	$path = dirname(dirname(dirname($base)))."/wp-config.php";
	else
    if (file_exists($base."/../../wp-config.php"))
        $path = dirname(dirname($base))."/wp-config.php";
	else
	    $path = false;
	
	if ($path != false)
	{
		$path = str_replace("\\", "/", $path); 
	}
	return $path;
}

/**
 * Registers this instance of wordpress with FireStats.
 * This is requires so that if there is more than one blog/system that works with 
 * the same FireStats instance it will be possible to filter the stats per site.
 */
function fs_register_wordpress()
{	
    global $__path;
    if (!$__path) return;
    require_once($__path.'/php/db-sql.php');

	$firestats_site_id = get_option('firestats_site_id');
	if ($firestats_site_id == null)
	{
		// the function may not exist in a transition stage between 1.1 to 1.2
		if (!function_exists('fs_register_site')) return;
		$firestats_site_id = fs_register_site();
		if (firestats_site_id === false)
		{
			return;
		} 
		update_option('firestats_site_id',$firestats_site_id);
	}

	$name = get_settings('blogname');
	$type = FS_SITE_TYPE_WORDPRESS;
	$res = fs_update_site_params($firestats_site_id,$firestats_site_id, $name,$type);
	if ($res != true)
	{
		echo $res;
	}

	// update the filter to show us this blog by default after the installation
	update_option('firestats_sites_filter',$firestats_site_id);
}


/*
Local option storage for wordpress, used by fs_update_local_option to update wordpress value in a generic way.
*/
function fs_update_local_option_impl($key, $value)
{
	update_option($key,$value);
}

/*
Local option storage for wordpress, used by fs_get_local_option to get wordpress value in a generic way.
*/
function fs_get_local_option_impl($key)
{
	return get_option($key);
}

function fs_is_windows()
{
	if (!isset($_ENV['OS'])) return false; // assume not windows.
	if (strpos(strtolower($_ENV['OS']), "windows") === false) return false;
	return true;
}
?>

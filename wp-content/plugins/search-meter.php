<?php
/*
Plugin Name: Search Meter
Plugin URI: http://www.thunderguy.com/semicolon/wordpress/search-meter-wordpress-plugin/
Description: Keeps track of what your visitors are searching for. After you have activated this plugin, you can check the <a href="index.php?page=search-meter.php">Search Meter Statistics</a> page to see what your visitors are searching for on your blog.
Version: 2.1
Author: Bennett McElwee
Author URI: http://www.thunderguy.com/semicolon/

$Revision$


INSTRUCTIONS

1. Copy this file into the plugins directory in your WordPress installation (wp-content/plugins).
2. Log in to WordPress administration. Go to the Plugins page and Activate this plugin.

To see search statistics, log in to WordPress Admin, go to the Dashboard page and click Search Meter.
To control search statistics, log in to WordPress Admin, go to the Options page and click Search Meter.


TEMPLATE TAGS

sm_list_popular_searches()

	Show a list of the five most popular search terms that have produced
	hits at your site during the last 30 days. Readers can click the
	search term to repeat the search.

sm_list_popular_searches('<li><h2>Popular Searches</h2>', '</li>')

	Show the list as above, with the heading "popular Searches". If there
	have been no searches, then this tag displays nothing. This form of
	the tag should be used in the default WordPress theme. Put it in the
	sidebar.php file.

sm_list_popular_searches('<li><h2>Popular Searches</h2>', '</li>', 10)

	As above, but show the ten most popular searches.

sm_list_recent_searches()

	Show a list of the five most recent searches that produced hits at
	your site. Readers can click the search term to repeat the search.
	This tag has the same options as sm_list_popular_searches().

	
THANKS

Kaufman (http://www.terrik.com/wordpress/) for valuable coding suggestions.
The many other users who have offered suggestions.


Copyright (C) 2005-06 Bennett McElwee (bennett at thunderguy dotcom)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
The license is also available at http://www.gnu.org/copyleft/gpl.html
*/


if (!is_plugin_page()) :


// Parameters (you can change these if you know what you're doing)


$tguy_sm_history_size = 500;
// The number of recent searches that will be saved. The table can
// contain up to 100 more rows than this number 

$tguy_sm_allow_empty_referer = false;
// Searches with an empty referer header are often bogus requests
// from Google's AdSense crawler or something similar, so they are
// excluded. Set this to true to record all such searches.

$tguy_sm_allow_duplicate_saves = false;
// It may be that the filter gets called more than once for a given
// request. Search Meter ignores these duplicates. Set this to true
// to record duplicates (the fact that it's a dupe will be recorded
// in the details). This will mess up the stats, but could be useful
// for troubleshooting.


// Template Tags


function sm_list_popular_searches($before = '', $after = '', $count = 5) {
// List the most popular searches in the last month in decreasing order of popularity.
	global $wpdb, $table_prefix;
	$count = intval($count);
	// This is a simpler query than the report query, and may produce
	// slightly different results. This query returns searches if they
	// have ever had any hits, even if the last search yielded no hits.
	// This makes for a more efficient search -- important if this
	// function will be used in a sidebar.
	$results = $wpdb->get_results(
		"SELECT `terms`,
			SUM( `count` ) AS countsum
		FROM `{$table_prefix}searchmeter`
		WHERE DATE_SUB( CURDATE( ) , INTERVAL 30 DAY ) <= `date`
		AND 0 < `last_hits`
		GROUP BY `terms`
		ORDER BY countsum DESC, `terms` ASC
		LIMIT $count");
	if (count($results)) {
		echo "$before\n<ul>\n";
		foreach ($results as $result) {
			echo '<li><a href="'. get_settings('home') . '/search/' . urlencode($result->terms) . '">'. htmlspecialchars($result->terms) .'</a></li>'."\n";
		}
		echo "</ul>\n$after\n";
	}
}

function sm_list_recent_searches($before = '', $after = '', $count = 5) {
// List the most recent successful searches.
	global $wpdb, $table_prefix;
	$count = intval($count);
	$results = $wpdb->get_results(
		"SELECT `terms`, `datetime`
		FROM `{$table_prefix}searchmeter_recent`
		WHERE 0 < `hits`
		ORDER BY `datetime` DESC
		LIMIT $count");
	if (count($results)) {
		echo "$before\n<ul>\n";
		foreach ($results as $result) {
			echo '<li><a href="'. get_settings('home') . '/search/' . urlencode($result->terms) . '">'. htmlspecialchars($result->terms) .'</a></li>'."\n";
		}
		echo "</ul>\n$after\n";
	}
}


// Hooks


if (function_exists('register_activation_hook')) {
	register_activation_hook(__FILE__, 'tguy_sm_init');
} else {
	add_action('init', 'tguy_sm_init');
}
add_filter('the_posts', 'tguy_sm_save_search', 20); // run after other plugins
add_action('admin_head', 'tguy_sm_stats_css');
add_action('admin_menu', 'tguy_sm_add_admin_pages');


// Functionality


function tguy_sm_init() {
	tguy_sm_create_summary_table();
	tguy_sm_create_recent_table();
}

// Keep track of how many times SM has been called for this request.
// Normally we only record the first time.
$tguy_sm_action_count = 0;

function tguy_sm_save_search(&$posts) {
// Check if the request is a search, and if so then save details.
// This is a filter but does not change the posts.
	global $wpdb, $wp_query, $table_prefix,
		$tguy_sm_history_size, $tguy_sm_allow_empty_referer, $tguy_sm_allow_duplicate_saves,
		$tguy_sm_action_count;

	++$tguy_sm_action_count;
	if (is_search()
	&& !is_paged() // not the second or subsequent page of a previuosly-counted search
	&& !is_admin() // not using the administration console
	&& (1 == $tguy_sm_action_count || $tguy_sm_allow_duplicate_saves)
	&& ($_SERVER['HTTP_REFERER'] || $tguy_sm_allow_empty_referer) // proper referrer (otherwise could be search engine, cache...)
	) {
		// Get all details of this search
		// search string is the raw query
		$search_string = $wp_query->query_vars['s'];
		if (get_magic_quotes_gpc()) {
			$search_string = stripslashes($search_string);
		}
		// search terms is the words in the query
		$search_terms = $search_string;
		$search_terms = preg_replace('/[," ]+/', ' ', $search_terms);
		$search_terms = trim($search_terms);
		// This actually only returns a maximum of the number of posts per page
		$hit_count = count($posts);
		// Other useful details of the search
		$details = '';
		$options = get_option('tguy_search_meter');
		if ($options['sm_details_verbose']) {
			if ($tguy_sm_allow_duplicate_saves) {
				$details .= "Search Meter action count: $tguy_sm_action_count\n";
			}
			foreach (array('REQUEST_URI','REQUEST_METHOD','QUERY_STRING','REMOTE_ADDR','HTTP_USER_AGENT','HTTP_REFERER')
			         as $header) {
				$details .= $header . ': ' . $_SERVER[$header] . "\n";
			}
		}

		// Sanitise as necessary
		$search_string = addslashes($search_string);
		$search_terms = addslashes($search_terms);
		$details = addslashes($details);

		// Save the individual search to the DB
		$query = "INSERT INTO `{$table_prefix}searchmeter_recent` (`terms`,`datetime`,`hits`,`details`)
		VALUES ('$search_string',NOW(),$hit_count,'$details')";
		$success = mysql_query($query);
		// If it failed, maybe the table was never created.
		// Try to create it and then try again.
		if (!$success) {
			if (tguy_sm_create_recent_table()) {
				$success = mysql_query($query);
			}
		}
		if ($success) {
			// Ensure table never grows larger than $tguy_sm_history_size + 100
			$rowcount = $wpdb->get_var(
				"SELECT count(`datetime`) as rowcount
				FROM `{$table_prefix}searchmeter_recent`");
			if (($tguy_sm_history_size + 100) < $rowcount) {
				// find time of ($tguy_sm_history_size)th entry
				$dateZero = $wpdb->get_var(
					"SELECT `datetime`
					FROM `{$table_prefix}searchmeter_recent`
					ORDER BY `datetime` DESC LIMIT $tguy_sm_history_size, 1");
				$query = "DELETE FROM `{$table_prefix}searchmeter_recent` WHERE `datetime` < '$dateZero'";
				$success = mysql_query($query);
			}
		}
		// Save search summary into the DB. Usually this will be a new query, so try to insert first
		$query = "INSERT INTO `{$table_prefix}searchmeter` (`terms`,`date`,`count`,`last_hits`)
		VALUES ('$search_terms',CURDATE(),1,$hit_count)";
		$success = mysql_query($query);
		if (!$success) {
			$query = "UPDATE `{$table_prefix}searchmeter` SET
				`count` = `count` + 1,
				`last_hits` = $hit_count
			WHERE `terms` = '$search_terms' AND `date` = CURDATE()";
			$success = mysql_query($query);
			// Table should always exist, so don't try to create again
		}
	}
	return $posts;
}

function tguy_sm_create_summary_table() {
// Create the table if not already there. Return true if we had to create the table.
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . "searchmeter";
	if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta("CREATE TABLE `{$table_name}` (
				`terms` VARCHAR(50) NOT NULL,
				`date` DATE NOT NULL,
				`count` INT(11) NOT NULL,
				`last_hits` INT(11) NOT NULL,
				PRIMARY KEY (`terms`,`date`)
				);
			");
		return true;
	} else {
		return false;
	}
}

function tguy_sm_create_recent_table() {
// Create the table if not already there. Return true if we had to create the table.
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . "searchmeter_recent";
	if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta("CREATE TABLE `{$table_name}` (
				`terms` VARCHAR(50) NOT NULL,
				`datetime` DATETIME NOT NULL,
				`hits` INT(11) NOT NULL,
				`details` TEXT NOT NULL,
				KEY `datetimeindex` (`datetime`)
				);
			");
		return true;
	} else {
		return false;
	}
}

function tguy_sm_reset_stats() {
	global $wpdb, $table_prefix;
	tguy_sm_create_recent_table();
	// Delete all records
	$wpdb->query("DELETE FROM `{$table_prefix}searchmeter`");
	$wpdb->query("DELETE FROM `{$table_prefix}searchmeter_recent`");
}

function tguy_sm_add_admin_pages() {
	add_submenu_page('index.php', 'Search Meter Statistics', 'Search Meter', 1, __FILE__, 'tguy_sm_stats_page');
	add_options_page('Search Meter', 'Search Meter', 10, __FILE__, 'tguy_sm_options_page');
}


// Display information


function tguy_sm_stats_css() {
?>
<style type="text/css">
#search_meter_menu { 
	margin: 0;
	padding: 0; 
}
#search_meter_menu li { 
	display: inline; list-style-type: none; list-style-image: none; list-style-position: outside; text-align: center;
	margin: 0;
	line-height: 170%;
}
#search_meter_menu li.current { 
	font-weight: bold; 
	background-color: #fff;
	color: #000;
	padding: 5px;
}
#search_meter_menu a {
	background-color: #fff;
	color: #69c; 
	padding: 4px;
	font-size: 12px;
	border-bottom: none;
}
#search_meter_menu a:hover {
	background-color: #69c;
	color: #fff; 
}
#search_meter_menu + .wrap {
	margin-top: 0;
}
div.sm-stats-table {
	float: left;
	padding-right: 5em;
	padding-bottom: 3ex;
}
div.sm-stats-table h3 {
	margin-top: 0;
}
div.sm-stats-table .left {
	text-align: left;
}
div.sm-stats-table .right {
	text-align: right;
}
div.sm-stats-clear {
	clear: both;
}
</style>
<?php
}

function tguy_sm_stats_page() {
	$recent_count = intval($_GET['recent']);
	if (0 < $recent_count) {
		$do_show_details = intval($_GET['details']);
		tguy_sm_recent_page($recent_count, $do_show_details);
	} else {
		tguy_sm_summary_page();
	}
}

function tguy_sm_summary_page() {
	global $wpdb, $table_prefix;
	// Delete old records
	$result = $wpdb->query(
	"DELETE FROM `{$table_prefix}searchmeter`
	WHERE `date` < DATE_SUB( CURDATE() , INTERVAL 30 DAY)");
	echo "<!-- Search Meter: deleted $result old rows -->\n";
	?>
	<div class="wrap">
		<ul id="search_meter_menu">
		<li class="current">Summary</li>
		<li><a href="<?php echo $_SERVER['PHP_SELF'] . "?page=" . $_REQUEST['page'] . "&amp;recent=100" ?>">Last 100 Searches</a></li>
		<li><a href="<?php echo $_SERVER['PHP_SELF'] . "?page=" . $_REQUEST['page'] . "&amp;recent=500" ?>">Last 500 Searches</a></li>
		</ul>

		<h2>Search summary</h2>

		<p>These tables show the most popular searches on your blog for the given time periods. <strong>Term</strong> is the text that was searched for; you can click it to see which posts contain that term. (This won't be counted as another search.) <strong>Searches</strong> is the number of times the term was searched for. <strong>Results</strong> is the number of posts that were returned from the <em>last</em> search for that term.</p>

		<div class="sm-stats-table">
		<h3>Yesterday and today</h3>
		<?php tguy_sm_summary_table($results, 1, true); 	?>
		</div>
		<div class="sm-stats-table">
		<h3>Last 7 days</h3>
		<?php tguy_sm_summary_table($results, 7, true); ?>
		</div>
		<div class="sm-stats-table">
		<h3>Last 30 days</h3>
		<?php tguy_sm_summary_table($results, 30, true); ?>
		</div>
		<div class="sm-stats-clear"></div>

		<h2>Unsuccessful search summary</h2>

		<p>These tables show only the search terms for which the last search yielded no results. People are searching your blog for these terms; maybe you should give them what they want.</p>

		<div class="sm-stats-table">
		<h3>Yesterday and today</h3>
		<?php tguy_sm_summary_table($results, 1, false); ?>
		</div>
		<div class="sm-stats-table">
		<h3>Last 7 days</h3>
		<?php tguy_sm_summary_table($results, 7,false); 	?>
		</div>
		<div class="sm-stats-table">
		<h3>Last 30 days</h3>
		<?php tguy_sm_summary_table($results, 30, false); ?>
		</div>
		<div class="sm-stats-clear"></div>

		<h2>Notes</h2>

		<p>To manage your search statistics, go to your <a href="<?php bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=search-meter.php">Search Meter Options page</a>.</p>

		<p>For information and updates, see the <a href="http://www.thunderguy.com/semicolon/wordpress/search-meter-wordpress-plugin/">Search Meter home page</a>. You can also offer suggestions, request new features or report problems.</p>

	</div>
	<?php
}

function tguy_sm_summary_table($results, $days, $do_include_successes = false) {
	global $wpdb, $table_prefix;
	// Explanation of the query:
	// We group by terms, because we want all rows for a term to be combined.
	// For the search count, we simply SUM the count of all searches for the term.
	// For the result count, we only want the number of results for the latest search. Each row
	// contains the result for the latest search on that row's date. So for each date,
	// CONCAT the date with the number of results, and take the MAX. This gives us the
	// latest date combined with its hit count. Then strip off the date with SUBSTRING.
	// This Rube Goldberg-esque procedure should work in older MySQL versions that
	// don't allow subqueries. It's inefficient, but that doesn't matter since it's
	// only used in admin pages and the tables involved won't be too big.
	$hits_selector = $do_include_successes ? '' : 'HAVING hits = 0';
	$results = $wpdb->get_results(
		"SELECT `terms`,
			SUM( `count` ) AS countsum,
			SUBSTRING( MAX( CONCAT( `date` , ' ', `last_hits` ) ) , 12 ) AS hits
		FROM `{$table_prefix}searchmeter`
		WHERE DATE_SUB( CURDATE( ) , INTERVAL $days DAY ) <= `date`
		GROUP BY `terms`
		$hits_selector
		ORDER BY countsum DESC, `terms` ASC
		LIMIT 20");
	if (count($results)) {
		?>
		<table cellpadding="3" cellspacing="2">
		<tbody>
		<tr class="alternate"><th class="left">Term</th><th>Searches</th>
		<?php
		if ($do_include_successes) {
			?><th>Results</th><?php
		}
		?></tr><?php
		$class= '';
		foreach ($results as $result) {
			?>
			<tr class="<?php echo $class ?>">
			<td><a href="<?php echo get_bloginfo('wpurl').'/wp-admin/edit.php?s='.urlencode($result->terms).'&submit=Search' ?>"><?php echo htmlspecialchars($result->terms) ?></a></td>
			<td class="right"><?php echo $result->countsum ?></td>
			<?php
			if ($do_include_successes) {
				?>
				<td class="right"><?php echo $result->hits ?></td></tr>
				<?php
			}
			$class = ($class == '' ? 'alternate' : '');
		}
		?>
		</tbody>
		</table>
		<?php
	} else {
		?><p>No searches recorded for this period.</p><?php
	}
}

function tguy_sm_recent_page($max_lines, $do_show_details) {
	global $wpdb, $table_prefix;
	tguy_sm_create_recent_table();

	$options = get_option('tguy_search_meter');
	$is_details_available = $options['sm_details_verbose'];
	$this_url_base = $_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page'];
	$this_url_recent_arg = '&amp;recent=' . $max_lines;
	?>
	<div class="wrap">
		<ul id="search_meter_menu">
		<li><a href="<?php echo $this_url_base ?>">Summary</a></li>
		<?php if (100 == $max_lines) : ?>
			<li class="current">Last 100 Searches</li>
		<?php else : ?>
			<li><a href="<?php echo $this_url_base . '&amp;recent=100' ?>">Last 100 Searches</a></li>
		<?php endif ?>
		<?php if (500 == $max_lines) : ?>
			<li class="current">Last 500 Searches</li>
		<?php else : ?>
			<li><a href="<?php echo $this_url_base . '&amp;recent=500' ?>">Last 500 Searches</a></li>
		<?php endif ?>
		</ul>

		<h2>Recent searches</h2>

		<p>This table shows the last <?php echo $max_lines; ?> searches on this blog. <strong>Term</strong> is the text that was searched for; you can click it to see which posts contain that term. (This won't be counted as another search.) <strong>Results</strong> is the number of posts that were returned from the search.
		</p>

		<div class="sm-stats-table">
		<?php
		$query = 
			"SELECT `datetime`, `terms`, `hits`, `details`
			FROM `{$table_prefix}searchmeter_recent`
			ORDER BY `datetime` DESC, `terms` ASC
			LIMIT $max_lines";
		$results = $wpdb->get_results($query);
		if (!$results) {
			if (tguy_sm_create_recent_table()) {
				$results = $wpdb->get_results($query);
			}
		}
		if (count($results)) {
			?>
			<table cellpadding="3" cellspacing="2">
			<tbody>
			<tr class="alternate"><th class="left">Date &amp; time</th><th class="left">Term</th><th class="right">Results</th>
			<?php if ($do_show_details) { ?>
				<th class="left">Details</th>
			<?php } else if ($is_details_available) { ?>
				<th class="left"><a href="<?php echo $this_url_base . $this_url_recent_arg . '&amp;details=1' ?>">Show details</a></th>
			<?php } ?>
			</tr>
			<?php
			$class= '';
			foreach ($results as $result) {
				?>
				<tr valign="top" class="<?php echo $class ?>">
				<td><?php echo $result->datetime ?></td>
				<td><a href="<?php echo get_bloginfo('wpurl').'/wp-admin/edit.php?s='.urlencode($result->terms).'&submit=Search' ?>"><?php echo htmlspecialchars($result->terms) ?></a></td>
				<td class="right"><?php echo $result->hits ?></td>
				<?php if ($do_show_details) : ?>
					<td><?php echo str_replace("\n", "<br />", htmlspecialchars($result->details)) ?></td>
				<?php endif ?>
				</tr>
				<?php
				$class = ($class == '' ? 'alternate' : '');
			}
			?>
			</tbody>
			</table>
			<?php
		} else {
			?><p>No searches recorded.</p><?php
		}
		?>
		</div>
		<div class="sm-stats-clear"></div>

		<h2>Notes</h2>

		<p>To manage your search statistics, go to your <a href="<?php bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=search-meter.php">Search Meter Options page</a>.</p>

		<p>For information and updates, see the <a href="http://www.thunderguy.com/semicolon/wordpress/search-meter-wordpress-plugin/">Search Meter home page</a>. You can also offer suggestions, request new features or report problems.</p>

	</div>
	<?php
}


endif; // if (!is_plugin_page())


function tguy_sm_options_page() {
	// See if user has submitted form
	if (isset($_POST['submitted'])) {
		$new_options = array();
		// Remember to put all options into the array or they'll get lost!
		$new_options['sm_details_verbose'] = (bool)($_POST['sm_details_verbose']);
		update_option('tguy_search_meter', $new_options);
		echo '<div id="message" class="updated fade"><p><strong>Plugin settings saved.</strong></p></div>';
	} else if (isset($_POST['tguy_sm_reset'])) {
		tguy_sm_reset_stats();
		echo '<div id="message" class="updated fade"><p><strong>Statistics have been reset.</strong></p></div>';
	}
	$options = get_option('tguy_search_meter');
	?>
	<div class="wrap">

		<h2>Search Meter Options</h2>

		<form name="searchmeter" action="<?php echo $action_url; ?>" method="post">
			<input type="hidden" name="submitted" value="1" />

			<fieldset class="options">
				<ul>
					<li>
					<label for="sm_details_verbose">
						<input type="checkbox" id="sm_details_verbose" name="sm_details_verbose" <?php echo ($options['sm_details_verbose']==true?"checked=\"checked\"":"") ?> />
						Keep detailed information about recent searches (taken from HTTP headers)
					</label>
					</li>
				</ul>
			</fieldset>
			<p class="submit">
			<input type="submit" name="Submit" value="Save changes &raquo;" />
			</p>
		</form>

		<h2>Reset statistics</h2>

		<p>Click this button to reset all search statistics. This will delete all information about previuos searches.</p>

		<form name="tguy_sm_admin" action="<?php echo $action_url; ?>" method="post">
			<p class="submit">
			<input type="submit" name="tguy_sm_reset" value="Reset statistics &raquo;" onclick="return confirm('You are about to delete all saved search statistics.\n  \'Cancel\' to stop, \'OK\' to delete.');" />
			</p>
		</form>

		<h2>Notes</h2>

		<p>To see your search statistics, go to your <a href="<?php bloginfo('wpurl'); ?>/wp-admin/index.php?page=search-meter.php">Search Meter Statistics page</a>.</p>

		<p>For information and updates, see the <a href="http://www.thunderguy.com/semicolon/wordpress/search-meter-wordpress-plugin/">Search Meter home page</a>. At that page, you can also offer suggestions, request new features or report problems.</p>

	</div>
	<?php
}

<?php get_header(); ?>
<?
$error = $_SERVER['REDIRECT_STATUS'];
$referring_url = $_SERVER['HTTP_REFERER'];
$requested_url = $_SERVER['REQUEST_URI'];
$referring_ip = $_SERVER['REMOTE_ADDR'];
$server_name = $_SERVER['SERVER_NAME'];
?>

	<div id="static">

    	<div class="post">
		<h3 class="storytitle">Error 404</h3>
			<div class="entrytext">

	<p>Sorry, http://<?=$server_name?><?=$requested_url?> cannot be found.
		</p>

		<p>Please start from the <a href="/index.php">home page</a> or you can search this site:

		<form method="get" id="searchform" action="/index.php">
		<input type="text" value="" name="s" id="s" />
		<br>
		<input type="submit" id="searchsubmit" value="Search" />
</form>
			</div>
		</div>


	</div>



<?php get_footer(); ?>









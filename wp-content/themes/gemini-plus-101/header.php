<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
  <meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats please -->

<script src="<?php bloginfo('url'); ?>/wp-content/themes/Gemini-Plus/js_quicktags-mini.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php bloginfo('template_directory')?>/style<?php colour(); ?>.css" type="text/css" media="screen" />

	<!-- link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>"/ -->
   <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

  <?php wp_get_archives('type=monthly&format=link'); ?>
	<?php //comments_popup_script(); // off by default ?>
	<?php wp_head(); ?>




		<style type="text/css">

	#masthead {

<?php headerURL() ?>
<?php headerheight() ?>
<?php headerwidth() ?>

<?php marginleft() ?>


<?php headertextsize() ?>
<?php headerborder() ?>
	}
<?php mastheadcolour() ?>

<?php headertextalign() ?>
<?php overallwidth() ?>

body {
	<?php backgroundURL() ?>
	<?php fontsize() ?>
	<?php fontcolour() ?>
	}

<?php sidebarheadercolour() ?>

#nav a {
	<?php sidebarlinks() ?>
	}

<?php sidebarlinkhover() ?>
<?php sidebarbullets() ?>
<?php innerbackgroundcolour() ?>
<?php postlinks() ?>
<?php hnavbackgroundcolour() ?>
<?php hnavlinkcolour() ?>
<?php hnavhover() ?>
<?php sidebarleftheader() ?>

</style>


</head>

<body>

<div id="farouter">
<div id="outer">
<div id="rap">
<div id="masthead"  onclick="location.href='<?php bloginfo('url'); ?>';" style="cursor: pointer;">
<?php headertext1() ?>
<h1 id="header"><a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></h1>
<h2 id="tagline"><?php bloginfo('description'); ?></h2>
<?php headertext2() ?>
</div>

<div id="main">

<div id="content">
<!-- end header -->

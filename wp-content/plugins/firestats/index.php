<?php
/*
	This file is the standalone entry point for FireStats
	Its not called when FireStats is installed inside WordPress or other systems
*/

require_once(dirname(__FILE__).'/php/utils.php');

?>
<?php 
if (fs_in_wordpress())
{
	// security check.
	// prevent uncontroled access to FireStats installed inside a wordpress blog
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php fs_e('FireStats')?></title>
</head>
<body>
	<?php fs_e('FireStats is installed inside WordPress')?><br/>
	<?php fs_e('Please access it from the WordPress admin interface')?><br/>
</body>
</html>
<?php
return;
}
// end if in wordpress ?> 




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
require_once(dirname(__FILE__).'/php/html-utils.php');
?>
	<title><?php fs_e('FireStats');?></title>
	<link rel="stylesheet" href="<?php echo fs_url('css/base.css');?>" type="text/css" /> 
	<link rel="stylesheet" href="<?php echo fs_url('css/mktree.css.php');?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo fs_url('css/page-sites.css.php');?>" type="text/css" />
	<!--[if lt IE 7]>
		<link rel="stylesheet" href="<?php echo fs_url('css/ie6-hacks.css');?>" type="text/css" />
	<![endif]-->
	<!--[if IE]>
		<link rel="stylesheet" href="<?php echo fs_url('css/ie-hacks.css');?>" type="text/css" />
	<![endif]-->
</head>
<body>
<?php
include('php/tabbed-pane.php');
?>
</body>
</html>

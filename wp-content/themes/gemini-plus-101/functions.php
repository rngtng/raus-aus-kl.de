<?php
/*
File Name: Wordpress Theme Toolkit
Version: 1.0
Author: Ozh
Author URI: http://planetOzh.com/
*/

/************************************************************************************
 * THEME USERS : don't touch anything !! Or don't ask the theme author for support :)
 ************************************************************************************/

include(dirname(__FILE__).'/themetoolkit.php');

/************************************************************************************
 * THEME AUTHOR : edit the following function call :
 ************************************************************************************/
$directory = get_bloginfo('stylesheet_directory');
themetoolkit(
	'geminiplus', /* Make yourself at home :
			* Name of the variable that will contain all the options of
			* your theme admin menu (in the form of an array)
			* Name it according to PHP naming rules (http://php.net/variables) */

	array(     /* Variables used by your theme features (i.e. things the end user will
			* want to customize through the admin menu)
 			* Syntax :
			* 'option_variable' => 'Option Title ## optionnal explanations',
			* 'option_variable' => 'Option Title {radio|value1|Text1|value2|Text2} ## optionnal explanations',
			* 'option_variable' => 'Option Title {textarea|rows|cols} ## optionnal explanations',
			* 'option_variable' => 'Option Title {checkbox|option_varname1|value1|Text1|option_varname2|value2|Text2} ## optionnal explanations',
			* Examples :
			* 'your_age' => 'Your Age',
			* 'cc_number' => 'Credit Card Number ## I can swear I will not misuse it :P',
			* 'gender' => 'Gender {radio|girl|You are a female|boy|You are a male} ## What is your gender ?'
			* Dont forget the comma at the end of each line ! */


	'colour_scheme_header' => 'Basic colour scheme {separator}',
	'colour' => 'Colour {radio|-pink|pink<br /><img src="'.$directory.'/images-pink/screenshot.jpg" /><hr>
	|-red|red<br /><img src="'.$directory.'/images-red/screenshot.jpg" /><hr>
	|-blue|blue<br /><img src="'.$directory.'/images-blue/screenshot.jpg" /><hr>
	|-black|black<br /><img src="'.$directory.'/images-black/screenshot.jpg" /><hr>
	|-tan|tan<br /><img src="'.$directory.'/images-tan/screenshot.jpg" /><hr>
	|-green|green<br /><img src="'.$directory.'/images-green/screenshot.jpg" /><hr>
	|-bales|bales<br /><img src="'.$directory.'/images-bales/screenshot.jpg" /><hr>
	|-sunset|sunset<br /><img src="'.$directory.'/images-sunset/screenshot.jpg" /><hr>
	|-cow|sinister cow<br /><img src="'.$directory.'/images-cow/screenshot.jpg" /><hr>
	|-night|night horizon<br /><img src="'.$directory.'/images-night/screenshot.jpg" />
	<hr>} ##',
    'header_header' => 'Header settings {separator}',
    'headerURL' => 'URL of header ## Eg http://flickr.com/photos/youraccount/header-photo.jpg<br />Or leave blank to use default headers',
    'noheaderimage' => 'Use header image {radio|yes|yes|no|no} ## If you would prefer not to use a header image and use a plain colour instead you can specify here.',
    'headerwidth' => 'Width of header in pixels ## eg 780',
    'headerheight' => 'Height of header in pixels ## eg 150',
 	'headertext' => 'Hide header text {checkbox|header_no|yes|Hide text} ## If you want to hide the blog name and tagline text in the header click this box. If you are using your own header image you might not want to display the text.',
	'headertextalign' => 'Alignment of header text {radio|left|left|centre|centre|right|right} ## Select alignment of header text.',
	'headertextsize' => 'Size of header text {radio|large|large|medium|medium|small|small} ## Select size of header text.',
	'mastheadcolour' => 'Font colour of masthead ## Eg #ff0000 or red, blue etc.',
	'headerborder' => 'Border around header {radio|0px|no border|1px|thin|2px|medium|3px|thick} ## Choose thickness of border around header.',

	'background_heading' => 'Background settings {separator}',
	'backgroundURL' => 'URL of background',
	'backgroundcolour' => 'Colour of main background ## Eg #ff0000 or red, blue etc. This is the colour of the outer area. Will only show if \'Use background image\' is set to \'No\' below.',
	'innerbackgroundcolour' => 'Inner background colour ## Eg #ff0000 or red, blue etc. Background colour of posts and sidebar.',
	'nobackgroundimage' => 'Use background image {radio|yes|yes|no|no} ## If you would prefer not to use a background image and use a plain colour instead select here. Colour of main background must be set above.',

	'font_header' => 'Font settings {separator}',
	'fontsize' => 'Font size ## Eg 10px or 105% or .8em (default is 12px)',
	'fontcolour' => 'Font colour ## Eg #ff0000 or red, blue etc.',
	'sidebarheadercolour' => 'Font colour of sidebar headers ## Eg #ff0000 or red, blue etc.',
	'sidebarlinks' => 'Font colour of sidebar links ## Eg #ff0000 or red, blue etc.',
	'sidebarlinkhover' => 'Font colour of sidebar links hover ## Eg #ff0000 or red, blue etc.',
	'sidebarbullets' => 'Sidebar bullets {radio|yes|yes|no|no} ## Choose whether or not to display graphic bullet points on sidebar links.',
	'postlinks' => 'Font colour of post and page links and page titles ## Eg #ff0000 or red, blue etc.',

	'navigation_bar_header' => 'Horizontal navigation bar settings {separator}',
	'navbar' => 'Show horizontal navigation bar {radio|yes|yes|no|no} ## Choose whether to display the horizontal navigation bar just below the header.',
	'hnavbackgroundcolour' => 'Navigation bar background colour ## Background colour of horizontal navigation bar.',
	'hnavlinkcolour' => 'Navigation bar text colour ## Colour of text on horizontal navigation bar.',
	'hnavhoverlinkcolour' => 'Navigation bar hover text colour ## Colour of hover text on horizontal navigation bar.',
	'hnavhoverbackground' => 'Navigation bar hover background colour ## Colour of hover background on horizontal navigation bar.',
	'overallwidth_header' => 'Setting for width of theme {separator}',
	'overallwidth' => 'Overall width ## The theme is set to a width of 800px. If you want to change that you can do so here. If you don\'t understand what this means it is probably best to leave this blank, otherwise you might get unexpected results. Please enter a number only for the width in pixels.',

	'sidebarposition' => 'Position of sidebar {radio|right|right|left|left} ## Choose whether you want sidebar positioned on the right or left.',

// 'debug' => 'debug',




	 	/* this is a fake entry that will activate the "Programmer's Corner"
			 * showing you vars and values while you build your theme. Remove it
			 * when your theme is ready for shipping */
	),
	__FILE__	 /* Parent. DO NOT MODIFY THIS LINE !
			  * This is used to check which file (and thus theme) is calling
			  * the function (useful when another theme with a Theme Toolkit
			  * was installed before */
);

// Set defaults
$directory = get_bloginfo('stylesheet_directory');
 if (!$geminiplus->is_installed()) {



	$set_defaults['colour'] = '-bales';
	$set_defaults['headertextsize'] = 'medium';
	$set_defaults['noheaderimage'] = 'yes';
	$set_defaults['sidebarbullets'] = 'yes';
	$set_defaults['navbar'] = 'no';
	$result = $geminiplus->store_options($set_defaults);



 }

/************************************************************************************
 * THEME AUTHOR : Congratulations ! The hard work is all done now :)
 *
 * From now on, you can create functions for your theme that will use the array
 * of variables $mytheme->option. For example there will be now a variable
 * $mytheme->option['your_age'] with value as set by theme end-user in the admin menu.
 ************************************************************************************/

/***************************************
 * Additionnal Features and Functions
 *
 * Create your own functions using the array
 * of user defined variables $mytheme->option.
 *
 **************************************/



function colour() {
	global $geminiplus;
	print $geminiplus->option['colour'];
}


function headerURL() {
	global $geminiplus;
	$url=$geminiplus->option['headerURL'];
	$noheaderimage=$geminiplus->option['noheaderimage'];
	$colour=$geminiplus->option['colour'];
	$directory = get_bloginfo('stylesheet_directory');
	if ($noheaderimage=="no"):
	print "background:url();\n";
	elseif ($url!=""):
	print "background:url($url);\n";
	endif;

}

function headerimagecopyright() {
	global $geminiplus;
	$url=$geminiplus->option['headerURL'];
	$noheaderimage=$geminiplus->option['noheaderimage'];
	$colour=$geminiplus->option['colour'];
	if ($noheaderimage=="yes" && $url=="" && $colour!="-pink"):
	print "<br />Header photograph &copy; copyright <a href=\"http://www.wordyblog.com\">WordyBlog</a> 2006, used with permission provided credit remains intact.";
	endif;
}

function headerwidth() {
	global $geminiplus;
	$headerwidth=$geminiplus->option['headerwidth'];
	$overallwidth=$geminiplus->option['overallwidth'];
	$url=$geminiplus->option['headerURL'];
	if ($url=="" || $headerwidth==""):
	print "";
	else:
	print "width: ".$headerwidth."px !important;\n";
	endif;
	if ($overallwidth!="" && $headerwidth=="" && $overallwidth > 800):
	$marginadjust=(($overallwidth-800)/2)+10;
	print "margin-left: ".$marginadjust."px !important;
	margin-right: ".$marginadjust."px !important;
	\n";
	endif;
}

function headerheight() {
	global $geminiplus;
	$headerheight=$geminiplus->option['headerheight'];
	if ($headerheight!=""):
	print "height: ".$headerheight."px !important;\n";
	endif;
}


function headerborder() {
	global $geminiplus;
	$headerborder=$geminiplus->option['headerborder'];
	if ($headerborder!=""):
	print "border: $headerborder solid #333 !important;";
	endif;
}

function marginleft() {
	global $geminiplus;
	$headerwidth=$geminiplus->option['headerwidth'];
	$overallwidth=$geminiplus->option['overallwidth'];
	if ($overallwidth!=""):
	$margin=$overallwidth;
	else:
	$margin=800;
	endif;
	$geminiplus_margin=($margin-$headerwidth)/2;
	$url=$geminiplus->option['headerURL'];
	if ($url=="" || $headerwidth==""):
	print "";
	else:
	print "margin-left: ".$geminiplus_margin."px !important;\n
	margin-right: ".$geminiplus_margin."px !important;\n";
	endif;
}


function headertext1() {
	global $geminiplus;
	$headertext=$geminiplus->option['header_no'];
	if ($headertext==""):
	print "";
	else:
	print "<!--";
	endif;
}
function headertext2() {
	global $geminiplus;
	$headertext=$geminiplus->option['header_no'];
	if ($headertext==""):
	print "";
	else:
	print "-->";
	endif;
}

function headertextalign() {
	global $geminiplus;
	$headertextalign=$geminiplus->option['headertextalign'];
	if ($headertextalign=="left"):
	print "h1 {text-align: left !important; }
	h2#tagline {text-align: left !important; }";
	elseif ($headertextalign=="centre"):
	print "h1 {text-align: center !important; }
	h2#tagline {text-align: center !important; }";
	elseif ($headertextalign=="right"):
	print "h1 {text-align: right !important; }
	h2#tagline {text-align: right !important; }";
	endif;
}


function headertextsize() {
	global $geminiplus;
	$headertextsize=$geminiplus->option['headertextsize'];
	if ($headertextsize=="large"):
	print "font-size: 150% !important;";
	elseif ($headertextsize=="medium"):
	print "font-size: 125% !important;";
	elseif ($headertextsize=="small"):
	print "font-size: 100% !important;";
	endif;
}



function backgroundURL() {
	global $geminiplus;
	$backgroundurl=$geminiplus->option['backgroundURL'];
	$nobackgroundimage=$geminiplus->option['nobackgroundimage'];
	$colour=$geminiplus->option['colour'];
	$directory = get_bloginfo('stylesheet_directory');
	$backgroundcolour=$geminiplus->option['backgroundcolour'];
	if ($backgroundurl!="" && $nobackgroundimage=="yes"):
	print "background:url($url);\n";
	elseif ($nobackgroundimage=="no" && $backgroundcolour!=""):
	print "background: $backgroundcolour !important;\n";
	endif;
}
if ($noheaderimage=="no"):
	print "background:url();\n";
	elseif ($url!=""):
	print "background:url($url);\n";
	endif;

function navbar1() {
	global $geminiplus;
	$navbar=$geminiplus->option['navbar'];
	if ($navbar=="no"):
	print "<!--";
	endif;
}

function navbar2() {
	global $geminiplus;
	$navbar=$geminiplus->option['navbar'];
	if ($navbar=="no"):
	print "-->";
	endif;
}

function fontsize() {
	global $geminiplus;
	$fontsize=$geminiplus->option['fontsize'];
	if ($fontsize!=""):
	print "font-size: $fontsize !important;";
	endif;
}

function fontcolour() {
	global $geminiplus;
	$fontcolour=$geminiplus->option['fontcolour'];
	if ($fontcolour!=""):
	print "color: $fontcolour !important;";
	endif;
}

function mastheadcolour() {
	global $geminiplus;
	$mastheadcolour=$geminiplus->option['mastheadcolour'];
	if ($mastheadcolour!=""):
	print "#header a {
	color: $mastheadcolour !important;
	}
	h2#tagline {
	color: $mastheadcolour !important;
	}";
	endif;
}

function sidebarheadercolour() {
	global $geminiplus;
	$sidebarheadercolour=$geminiplus->option['sidebarheadercolour'];
	if ($sidebarheadercolour!=""):
	print "h2 {
	color: $sidebarheadercolour !important;
	}";
	endif;
}

function sidebarlinks() {
	global $geminiplus;
	$sidebarlinks=$geminiplus->option['sidebarlinks'];
	if ($sidebarlinks!=""):
	print "color: $sidebarlinks !important;";
	endif;
}

function sidebarlinkhover() {
	global $geminiplus;
	$sidebarlinkhover=$geminiplus->option['sidebarlinkhover'];
	if ($sidebarlinkhover!=""):
	print "#nav a:hover {
	color: $sidebarlinkhover !important;
	}";
	endif;
}

function sidebarbullets() {
	global $geminiplus;
	$sidebarbullets=$geminiplus->option['sidebarbullets'];
	$directory = get_bloginfo('stylesheet_directory');
	$colour=$geminiplus->option['colour'];
	if ($sidebarbullets=="yes"):
	print "#nav a {
	padding: 3px;
	padding-left: 17px;
	padding-top: 0;
	margin-top: 0px;
	margin-bottom: 8px;
	background-image: url($directory/images$colour/icon.png);
	background-repeat: no-repeat;
background-position: 0px 2px;
	}
	#nav a:hover {
	background-image: url($directory/images$colour/icon2.png) ;

	}";
	endif;
}


function innerbackgroundcolour() {
	global $geminiplus;
	$innerbackgroundcolour=$geminiplus->option['innerbackgroundcolour'];
	if ($innerbackgroundcolour!=""):
	print "#farouter {
	background: $innerbackgroundcolour !important;
	}

#rap {
	background: $innerbackgroundcolour !important;
	}
	#nav a {
		background-color: $innerbackgroundcolour !important;
	}
	";
	endif;
}

function postlinks() {
	global $geminiplus;
	$postlinks=$geminiplus->option['postlinks'];
	if ($postlinks!=""):
	print ".post a {
	color: $postlinks !important;
	}

h3 {
	color: $postlinks !important;
	}

.post a:visited {
	color: $postlinks !important;
	}

.post a:active {
	color: $postlinks !important;
	}

.post a:hover {
	color: $postlinks !important;
	}
#commentform a {
	color: $postlinks !important;
	}

#commentform a:visited {
	color: $postlinks !important;
	}

#commentform a:active {
	color: $postlinks !important;
	}

#commentform a:hover {
	color: $postlinks !important;
	}
";
	endif;
}


function hnavbackgroundcolour() {
	global $geminiplus;
	$hnavbackgroundcolour=$geminiplus->option['hnavbackgroundcolour'];
	if ($hnavbackgroundcolour!=""):
	print "#hnav {
	background: $hnavbackgroundcolour !important;
	}
	#hmenu {
		background: $hnavbackgroundcolour !important;
	}
	";
	endif;
}



function hnavlinkcolour() {
	global $geminiplus;
	$hnavlinkcolour=$geminiplus->option['hnavlinkcolour'];
	if ($hnavlinkcolour!=""):
	print "#hnav ul li a {
	color: $hnavlinkcolour !important;
	}
	";
	endif;
}


function hnavhover() {
	global $geminiplus;
	$hnavhoverlinkcolour=$geminiplus->option['hnavhoverlinkcolour'];
	$hnavhoverbackground=$geminiplus->option['hnavhoverbackground'];
	if ($hnavlinkcolour!="" || $hnavhoverbackground!=""):
	print "#hnav ul li a:hover {
	background: $hnavhoverbackground !important;
	color: $hnavhoverlinkcolour !important;
	}
	";
	endif;
}

function overallwidth() {
	global $geminiplus;
	$overallwidth=$geminiplus->option['overallwidth'];
	$adjust=260;
	$px='px';
	$contentwidth=$overallwidth-$adjust;
	if ($overallwidth!=""):
	print "#farouter {
	width: $overallwidth$px !important;
	}
	#content {
		width: $contentwidth$px;
		}
	";
	endif;
}

function sidebarlefttop() {
	global $geminiplus;
	$sidebarposition=$geminiplus->option['sidebarposition'];
	if ($sidebarposition=="left"):
	get_sidebar();
	endif;
}

function sidebarleftheader() {
	global $geminiplus;
	$sidebarposition=$geminiplus->option['sidebarposition'];
	if ($sidebarposition=="left"):
	print "#menu {
	float: left !important;
	border-right: 1px dashed #c0c0c0  !important;
	border-left: none !important;
	margin-right: 10px;
	padding-right: 10px;
	}";
	endif;
}




if ( function_exists('register_sidebars') )
	register_sidebars(1);


?>
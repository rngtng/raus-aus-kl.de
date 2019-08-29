<?php 
header('Content-Type: text/css');
require_once(dirname(__FILE__).'/../php/layout.php');
?>

#fs_sites_div
{
    position: relative;
    top: 0; left: 0;
	width: 100%;
}

#fs_sites_table_holder
{
    width:400px;
}

#fs_sites_tab_help
{
    width:400px;
}

.dialog_window
{
	display:none;
	background:white;
	border:1px solid #000;
	position: absolute;
	<?php H_BEGIN()?>:50px;
	z-index: 101;
}

#fs_edit_window
{
	width:300px;
	height:200px;
	top:50px;
	z-index: 101;
}

#fs_delete_window
{
	width:330px;
	height:250px;
	top:10px;
	z-index: 101;
}


#fs_sites_table th
{
	font: bold 11px "Trebuchet MS", Verdana, Arial, Helvetica,sans-serif;
	color: #6D929B;
	border-left: 1px solid #C1DAD7;
	border-right: 1px solid #C1DAD7;
	border-bottom: 1px solid #C1DAD7;
	border-top: 1px solid #C1DAD7;
	letter-spacing: 2px;
	padding: 6px 6px 6px 12px;
	background: #CAE8EA;
}

#fs_sites_table td 
{
	border-right: 1px solid #C1DAD7;
	border-bottom: 1px solid #C1DAD7;
	background: #fff;
	padding: 6px 6px 6px 12px;
	color: #6D929B;
}

.fs_edit_label
{
    padding-top: 5px;
    padding-right: 5px;
    padding-bottom: 5px;
    padding-left: 25px;
}

.fs_bottom_panel
{
    width:100%; height:40px;
    position:absolute;bottom:0px
}



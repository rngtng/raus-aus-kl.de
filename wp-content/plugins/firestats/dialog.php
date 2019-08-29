<?php
/**
 * This is a relativly clean hack to make dialogs from /php be relative to the root directory.
 */
if (isset($_REQUEST['dialog_id']))
{
	switch($_REQUEST['dialog_id'])
	{
		case "donation":
			require "php/donation-window.php";
			break;
		case "import_bots":
			include "php/import-bots-list.php";
			break;
		default:
			echo "Invalid dialog_id";
	}
}
else
{
	echo "missing dialog_id";
}
?>
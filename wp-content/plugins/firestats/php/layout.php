<?php
require_once(dirname(__FILE__).'/init.php');
// horizontal begin, left for ltr layout and right for rtl layout
function H_BEGIN()
{
	if (FS_LANG_DIR == 'rtl') echo "right";
	else echo "left";
}

function H_END()
{
	if (FS_LANG_DIR == 'rtl') echo "left";
	else echo "right";
}


?>

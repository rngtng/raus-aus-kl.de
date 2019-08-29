<?php
// ** MySQL settings ** //
define('DB_NAME', 'db114853_7');    // Der Name der Datenbank, die du benutzt.
define('DB_USER', 'db114853_7');     // Dein MySQL Datenbank Benutzername.
define('DB_PASSWORD', 'db07rakl'); // Dein MySQL Passwort
define('DB_HOST', 'mysql4.jump-around.eu');    // 99% Chance, dass du hier nichts ändern musst.


// Wenn du verschiedene Präfixe benutzt kannst du innerhalb einer Datenbank
// verschiedene WorPress Installationen betreiben
$table_prefix  = 'wp_';   // Nur Zahlen, Buchstaben und Unterstriche bitte!

// Hier kannst du einstellen welche Sprachdatei benutzt werden soll
// Wenn du nichts einträgst wird Englisch genommen.
define ('WPLANG', 'de_DE');


/* Das war`s, ab hier bitte nichts mehr editieren! Happy blogging. */
define('ABSPATH', dirname(__FILE__).'/');
require_once(ABSPATH.'wp-settings.php');
?>

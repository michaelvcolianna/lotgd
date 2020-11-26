<?php
require_once("lib/installer/installer_functions.php");
$values = [
    'DB_HOST' => $_ENV['DB_HOST'] ?? htmlentities($session['dbinfo']['DB_HOST'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")),
    'DB_USER' => $_ENV['DB_USER'] ?? htmlentities($session['dbinfo']['DB_USER'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")),
    'DB_PASS' => $_ENV['DB_PASS'] ?? htmlentities($session['dbinfo']['DB_PASS'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")),
    'DB_NAME' => $_ENV['DB_NAME'] ?? htmlentities($session['dbinfo']['DB_NAME'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")),
];
rawoutput("<form action='installer.php?stage=4' method='POST'>");
output("`@`c`bDatabase Connection Information`b`c`2");
output("In order to run Legend of the Green Dragon, your server must have access to a MySQL database.");
output("If you are not sure if you meet this need, talk to server's Internet Service Provider (ISP), and make sure they offer MySQL databases.");
output("If you are running on your own machine or a server under your control, you can download and install MySQL from <a href='http://www.mysql.com/' target='_blank'>the MySQL website</a> for free.`n",true);
if (file_exists("dbconnect.php")){
	output("There appears to already be a database setup file (dbconnect.php) in your site root, you can proceed to the next step.");
}else{
	output("`nIt looks like this is a new install of Legend of the Green Dragon.");
	output("First, thanks for installing LoGD!");
	output("In order to connect to the database server, I'll need the following information.");
	output("`iIf you are unsure of the answer to any of these questions, please check with your server's ISP, or read the documentation on MySQL`i`n");

	output("`nWhat is the address of your database server?`n");
	rawoutput("<input name='DB_HOST' value=\"".$values['DB_HOST']."\">");
	tip("If you are running LoGD from the same server as your database, use 'localhost' here.  Otherwise, you will have to find out what the address is of your database server.  Your server's ISP might be able to provide this information.");

	output("`nWhat is the username you use to connect to the database server?`n");
	rawoutput("<input name='DB_USER' value=\"".$values['DB_USER']."\">");
	tip("This username does not have to be the same one you use to connect to the database server for administrative reasons.  However, in order to use this installer, and to install some of the modules, the account you provide here must have the ability to create, modify, and drop tables.  If you want the installer to create a new database for LoGD, the account will also have to have the ability to create databases.  Finally, to run the game, this account must at a minimum be able to select, insert, update, and delete records, and be able to lock tables.  If you're uncertain, grant the account the following privileges: SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, and ALTER.");

	output("`nWhat is the password for this username?`n");
	rawoutput("<input name='DB_PASS' value=\"".$values['DB_PASS']."\">");
	tip("The password is necessary here in order for the game to successfully connect to the database server.  This information is not shared with anyone, it is simply used to configure the game.");

	output("`nWhat is the name of the database you wish to install LoGD in?`n");
	rawoutput("<input name='DB_NAME' value=\"".$values['DB_NAME']."\">");
	tip("Database servers such as MySQL can control many different databases.  This is very useful if you have many different programs each needing their own database.  Each database has a unique name.  Provide the name you wish to use for LoGD in this field.");

	rawoutput("<select hidden name='DB_USEDATACACHE'>");
	rawoutput("<option value=\"0\">".translate_inline("No")."</option>");
	rawoutput("</select>");

	rawoutput("<input type='hidden' name='DB_DATACACHEPATH' value=\"0\">");

	/*
		$yes = translate_inline("Yes");
		$no = translate_inline("No");
		output("`nShould I attempt to create this database if it does not exist?`n");
		rawoutput("<select name='DB_CREATE'><option value='1'>$yes</option><option value='0'>$no</option></select>");
		tip("If this database doesn't exist, I'll try to create it for you if you like.");
	*/
	$submit="Test this connection information.";
	output_notl("`n`n<input type='submit' value='$submit' class='button'>",true);
}
rawoutput("</form>");
?>

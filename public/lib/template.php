<?php
// translator ready
// addnews ready
// mail ready

function templatereplace($itemname,$vals=false){
    global $template;
    if (!isset($template[$itemname]))
        output("`bWarning:`b The `i%s`i template part was not found!`n", $itemname);
    $out = $template[$itemname];
    if (!is_array($vals)) return $out->render([$vals => $vals]);

    return $out->render($vals);
}

function prepare_template($force=false){
    if (!$force) {
        if (defined("TEMPLATE_IS_PREPARED")) return;
        define("TEMPLATE_IS_PREPARED",true);
    }

     global $templatename, $templatemessage, $template, $session, $y, $z, $y2, $z2, $copyright, $lc, $x, $templatetags;
    if (!isset($_COOKIE['template'])) $_COOKIE['template']="";
    $templatename="";
    $templatemessage="";
    if ($_COOKIE['template']!="")
        $templatename=$_COOKIE['template'];
    if ($templatename=="" || !file_exists("templates/$templatename"))
        $templatename=getsetting("defaultskin", "jade.htm");
    if ($templatename=="" || !file_exists("templates/$templatename"))
        $templatename="jade.htm";
    $template = loadtemplate($templatename);

    $y = 0;
    $z = $y2^$z2;
    if ($session['user']['loggedin'] && $x > ''){
        $$z = $x;
    }
    $$z = $lc . $$z . "<br />";

    $template['lotgd'] = Template::load('lotgd.html.twig');
}
?>

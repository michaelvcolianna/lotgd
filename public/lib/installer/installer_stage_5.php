<?php
require_once("lib/installer/installer_functions.php");
if (httppostisset("DB_PREFIX") > ""){
    $session['dbinfo']['DB_PREFIX'] = httppost("DB_PREFIX");
}
if ($session['dbinfo']['DB_PREFIX'] > "" && substr($session['dbinfo']['DB_PREFIX'],-1)!="_")
$session['dbinfo']['DB_PREFIX'] .= "_";

$descriptors = descriptors($session['dbinfo']['DB_PREFIX']);
$unique=0;
$game=0;
$missing=0;
$conflict = array();

$sql = "SHOW TABLES";
$result = db_query($sql);
while ($row = db_fetch_assoc($result)){
    $val = current($row);
    next($row);
    if (isset($descriptors[$val])){
        $game++;
        array_push($conflict,$val);
    }else{
        $unique++;
    }
}
$missing = count($descriptors)-$game;
if ($missing*10 < $game){
    //looks like an upgrade
    $upgrade=true;
}else{
    $upgrade=false;
}
if (httpget("type")=="install") $upgrade=false;
if (httpget("type")=="upgrade") $upgrade=true;
$session['dbinfo']['upgrade']=$upgrade;
    if ($upgrade){
    output("`@This looks like a game upgrade.");
    output("`^If this is not an upgrade from a previous version of LoGD, <a href='installer.php?stage=5&type=install'>click here</a>.",true);
    output("`2Otherwise, continue on to the next step.");
}else{
    //looks like a clean install
    $upgrade=false;
    output("`@This looks like a fresh install.");
    output("`2If this is not a fresh install, but rather an upgrade from a previous version of LoGD, chances are that you installed LoGD with a table prefix.  If that's the case, enter the prefix below.  If you are still getting this message, it's possible that I'm just spooked by how few tables are common to the current version, and in which case, I can try an upgrade if you <a href='installer.php?stage=5&type=upgrade'>click here</a>.`n",true);
    if (count($conflict)>0){
        output("`n`n`\$There are table conflicts.`2");
        output("If you continue with an install, the following tables will be overwritten with the game's tables.  If the listed tables belong to LoGD, they will be upgraded, otherwise all existing data in those tables will be destroyed.  Once this is done, this cannot be undone unless you have a backup!`n");
        output("`nThese tables conflict: `^".join(", ",$conflict)."`2`n");
        if (httpget("op")=="confirm_overwrite") $session['sure i want to overwrite the tables']=true;
        if (!$session['sure i want to overwrite the tables']){
            $session['stagecompleted']=4;
            output("`nIf you are sure that you wish to overwrite these tables, <a href='installer.php?stage=5&op=confirm_overwrite'>click here</a>.`n",true);
        }
    }
}
?>

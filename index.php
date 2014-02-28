<?php

require 'inc/func.php';

$OUT = $_REQUEST['out'];
if(!isset($_REQUEST['lim'])){
    $LIM = 5;
}else{
    $LIM = (int) $_REQUEST['lim'];
}
$W = (int) $_REQUEST['w'];
if(!$W) $W = 450;
$H = (int) $_REQUEST['h'];
if(!$H) $H = 180;

$KEY = $_REQUEST['key'];
if(!$KEY) die('no key given');


// select

switch($KEY){
    case 'page_size':
        $sql = "SELECT CONCAT(ROUND(`value`/(1024)),'KB') as `val`, COUNT(*) as cnt
                  FROM popularity
                 WHERE `key` = '".addslashes($KEY)."'
              GROUP BY `val`
              ORDER BY cnt DESC";
        break;
    case 'media_size':
        $sql = "SELECT ROUND(`value`/(1024*1024)) as `val`, COUNT(*) as cnt
                  FROM popularity
                 WHERE `key` = '".addslashes($KEY)."'
              GROUP BY `val`
              ORDER BY cnt DESC";
        break;
    case 'webserver':
        $sql = "SELECT substring_index(`value`,'/',1) as `val`, COUNT(*) as cnt
                  FROM popularity
                 WHERE `key` = 'webserver'
              GROUP BY `val`
              ORDER BY cnt DESC";
        break;
    case 'php_version':
        $sql = "SELECT CONCAT('PHP ',SUBSTRING(`value`,1,3)) as `val`, COUNT(*) as cnt
                  FROM popularity
                 WHERE `key` = 'php_version'
              GROUP BY `val`
              ORDER BY cnt DESC";
        break;
    default:
        $sql = "SELECT `value` as `val`, COUNT(*) as cnt
                  FROM popularity
                 WHERE `key` = '".addslashes($KEY)."'
              GROUP BY `val`
              ORDER BY cnt DESC";
        break;
}
$res = mysql_query($sql,$db);

// output
switch($OUT){
    case 'rss':
        out_rss($res);
        break;
    case 'pie':
        out_pie($res);
        break;
    case 'line':
        out_line($res);
        break;
    default:
        out_html($res);
        break;
}


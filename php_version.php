<?php

require 'inc/func.php';


// select
$sql = "SELECT CONCAT('PHP ',SUBSTRING(`value`,1,3)) as `val`, COUNT(*) as cnt
          FROM popularity
         WHERE `key` = 'php_version'
      GROUP BY `val`
      ORDER BY cnt DESC";
if($lim) $sql .= " LIMIT $lim";
$res = mysql_query($sql,$db);


// output
#if($out == 'html'){
#    out_html($res);
#}

out_pie($res);


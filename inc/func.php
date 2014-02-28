<?php
define('NL',"\n");
require dirname(__FILE__).'/gchart/gChart2.php';
require dirname(__FILE__).'/GphpChart.class.php';
require dirname(__FILE__).'/GoogChart.class.php';
require dirname(__FILE__).'/../conf.php';

// connect
$db = mysql_connect($conf['host'],$conf['user'],$conf['pass']);
if(!$db) die('Could not connect: ' . mysql_error());
mysql_select_db($conf['name'], $db) || die ('Can\'t use db: ' . mysql_error());
mysql_query('set names utf8',$db);

// select maximum number
$max_res = mysql_query('SELECT COUNT(DISTINCT uid) as cnt FROM popularity',$db);
$max_row = mysql_fetch_assoc($max_res);
$MAX = $max_row['cnt'];
mysql_free_result($max_res);
unset($max_res);
unset($max_row);



function out_pie($res){
    global $MAX;
    global $LIM;
    global $W,$H;
    $data  = array();
    $label = array();
    $other = 0;

    $cnt = 0;
    while ($row = mysql_fetch_assoc($res)) {
        if(++$cnt > $LIM){
            $other += $row['cnt'];
        }else{
            $data[]  = sprintf('%.1f',$row['cnt']*100/$MAX);
            $label[] = $row['val'].sprintf('  %.1f%%',$row['cnt']*100/$MAX);
        }
    }
    $data[]  = sprintf('%.1f',$other*100/$MAX);
    $label[] = $row['val'].sprintf('other  %.1f%%',$other*100/$MAX);
    $label = array_map('rawurlencode',$label);

    $url  = 'http://chart.apis.google.com/chart?cht=p&chs='.$W.'x'.$H.'&chco=4d89f9&chf=bg,s,ffffff00';
    $url .= '&chd=t:'.join(',',$data);
    $url .= '&chl='.join('|',$label);

    header('Location: '.$url);
}

function out_line($res){
    global $MAX;
    global $LIM;
    $data  = array();
    $label = array();
    $other = 0;

    $cnt = 0;
    while ($row = mysql_fetch_assoc($res)) {
        $data[ $row['val']]  = $row['cnt'];
    }
/*
    $piChart = new gLineChart;
    $piChart->width = 450;
    $piChart->addDataSet(array_values($data));
//    $piChart->valueLabels = $label;
    $piChart->dataColors = array("4d89f9");
    echo '<img src="'.$piChart->getUrl().'" />';*/

    $chart = new GoogChart();
    $chart->setChartAttrs( array(
    'type' => 'line',
    'data' => $data,
    'size' => array( 400, 300 ),
    ));
    echo $chart;
}


function out_html($res){
    global $MAX;

    echo '<table>';
    while ($row = mysql_fetch_assoc($res)) {
        echo '<tr>';
        echo '<td>'.htmlspecialchars($row['val']).'</td>';
        echo '<td>'.sprintf('  %.1f%%',$row['cnt']*100/$MAX).'</td>';
        echo '</tr>';
    }
    echo '</table>';
}

function out_rss($res,$link=''){
    global $MAX;
    global $LIM;

    $rowno = 0;
    $other = 0;
    header('Content-Type: text/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="utf-8"?>'.NL;
    echo '<rss version="0.91">'.NL;
    echo '<channel>'.NL;
    while ($row = mysql_fetch_assoc($res)) {
        if($rowno++ >= $LIM){
	    $other += $row['cnt'];
	}else{
            echo '  <item>'.NL;
            echo '      <title>'.sprintf('%.1f%% ',$row['cnt']*100/$MAX).htmlspecialchars($row['val']).'</title>'.NL;
            if($link) printf('      <link>'.$link.'</link>'.NL,raw_urlencode($row['value']));
            echo '  </item>'.NL;
        }
    }
    if($other){
        echo '  <item>'.NL;
        echo '      <title>'.sprintf('%.1f%% other',$other*100/$MAX).'</title>'.NL;
        echo '  </item>'.NL;
    }
    echo '</channel>';
    echo '</rss>';
}



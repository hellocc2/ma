<?php
ini_set('display_errors', 0);
$graph_pv = '<hidden>1</hidden>';
$graph_ip = '<hidden>1</hidden>';
$graph_uv = '<hidden>1</hidden>';
$graph_newUv = '<hidden>1</hidden>';
$graph_timeOnSite = '<hidden>1</hidden>';

if(isset($_GET['type'])) {
	$type = $_GET['type'];
} else {
	$type = 'pv';
}
	switch ($type) {
	case 'pv':
		$graph_pv ='';
	    break;
	case 'ip':
		$graph_ip ='';
		break;
	case 'uv':
		$graph_uv ='';
		break;
	case 'newUv':
		$graph_newUv ='';
		break;
	case 'timeOnSite':
		$graph_timeOnSite ='';
		break;
	}

header('Content-Type: text/xml');	
echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
	<settings>	
	  <grid>
	    <x>
	      <alpha>10</alpha>
	      <approx_count>24</approx_count>
	    </x>
	    <y_left>
	      <alpha>10</alpha>
	    </y_left>
	  </grid>
	  <legend>
	    <max_columns>5</max_columns>
	    <values>
	      <width>78</width>
	      <align>left</align>
	    </values>
	  </legend>
	  <reload_data_interval>60</reload_data_interval>
	  <digits_after_decimal>0</digits_after_decimal>
	  <values>
	    <x>
	      <tick_length>5</tick_length>
	      <width>1</width>	
	      <enabled>0</enabled>
	      <alpha>10</alpha>
	      <dashed>1</dashed>
		  <approx_count>24</approx_count>	  
	    </x>
	  </values>  
	  <context_menu>
	  </context_menu>
	  <indicator>
	    <x_balloon_enabled>0</x_balloon_enabled>
	    <color>0D8ECF</color>
	    <x_balloon_text_color>FFFFFF</x_balloon_text_color>
	    <line_alpha>50</line_alpha>
	    <selection_color>0D8ECF</selection_color>
	    <selection_alpha>20</selection_alpha>
	  </indicator>  
	  <graphs>
	    <graph gid="1">
	      <title>PV</title>
	      <color>8CC540</color>
	      $graph_pv
	      <bullet>round</bullet>
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>
	    <graph gid="2">
	      <title>UV</title>
	      <color>33AAEE</color>
		  $graph_uv
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>
	    <graph gid="3">
	      <title>IP</title>
	      <color>FF9933</color>
		  $graph_ip
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>
	    <graph gid="4">
	      <title>新增独立访客</title>
	      <color>CC0000</color>
		  $graph_newUv
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>
	    <graph gid="5">
	      <title>平均停留时间</title>
	      <color>BEBEBE</color>
		  $graph_timeOnSite
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>	
	    <graph gid="6">
	      <title>PV</title>
	      <color>371740</color>
	      $graph_pv	      
	      <bullet>round</bullet>
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>
	    <graph gid="7">
	      <title>UV</title>
	      <color>4E7178</color>
	      $graph_uv
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>
	    <graph gid="8">
	      <title>IP</title>
	      <color>4FA9B8</color>
		  $graph_ip
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>
	    <graph gid="9">
	      <title>新增独立访客</title>
	      <color>74C0CF</color>
		  $graph_newUv
	    </graph>
	    <graph gid="10">
	      <title>平均停留时间</title>
	      <color>A49A87</color>
		  $graph_timeOnSite
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>	
	  </graphs>
	  <labels>
	
	    </label>
	  </labels>
	</settings>
XML;
?>
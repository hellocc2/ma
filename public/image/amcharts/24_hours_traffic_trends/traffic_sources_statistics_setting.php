<?php
ini_set('display_errors', 0);

if(isset($_GET['filter_name'])) {
	$filter_name = $_GET['filter_name'];
}

header('Content-Type: text/xml');	
echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
	<settings>
	  <reload_data_interval>60</reload_data_interval>
	  <digits_after_decimal>0</digits_after_decimal>
	  <context_menu>
	  </context_menu>
	  <graphs>
	    <graph gid="1">
	      <title>$filter_name</title>
	      <color>8CC540</color>
	      <bullet>round</bullet>
		  <balloon_text><![CDATA[{title} <b>{value}</b><br>{description}]]></balloon_text>
	    </graph>	
	  </graphs>
	  <labels>
	    <label lid="0">
	      <!--- <text><![CDATA[<b></b>]]></text> -->
	      <y>20</y>
	      <width>520</width>
	      <align>center</align>
	
	    </label>
	  </labels>
	</settings>
XML;
?>
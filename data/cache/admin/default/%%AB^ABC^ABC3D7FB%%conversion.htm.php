<?php /* Smarty version 2.6.18, created on 2014-08-26 03:48:10
         compiled from conversion.htm */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "reference.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<style>
	#rightnow ul li {
		float: left;
		margin-left: -1px;
	}
	#rightnow ul li a {
		display: block;
		width: 30px;
		height: 30px;
		line-height: 30px;
		text-align: center;
		border: 1px solid #ddd;
		background-color: #f1f1f1;
	}
</style>
<script src="<?php echo $this->_tpl_vars['javascript_url']; ?>
amcharts.js" type="text/javascript"></script>

<body>
	<input id="frame_url" type="hidden" value="<?php echo $_SERVER['REQUEST_URI']; ?>
"/>
	<div id="container">
		<div id="wrapper">
			<div id="content">
				<div id="rightnow">
					<h3 class="reallynow"><span>统计</span> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "time_lang.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
					<br />
					</h3>
				</div>

				<div id="rightnow">

					<div style="float:right;width:120px;clear:both;">
						<ul style="list-style-type:none;">
							<li >
								<a href="?module=conversion&action=index">日</a>
							</li>
							<li>
								<a href="?module=conversion&action=index&time=week">周</a>
							</li>
							<li>
								<a href="?module=conversion&action=index&time=month">月</a>
							</li>
						</ul>
					</div>
					<div style="clear:both;height:1px;overflow:hidden;"></div>
				</div>

				<div id="infowrap">
					<div id="infobox">
						<script type="text/javascript">
							var chart;

							var chartData = [
							<?php $_from = $this->_tpl_vars['conversion']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['list']):
?>
							{
							"date":"<?php echo $this->_tpl_vars['list']['time']; ?>
",
							"url":"?module=Conversion&action=hourrate&date=<?php echo $this->_tpl_vars['list']['time']; ?>
",
							"conversion_rate":<?php echo $this->_tpl_vars['list']['rate']; ?>

							},
							<?php endforeach; endif; unset($_from); ?>];

							AmCharts.ready(function() {
								// SERIAL CHART
								chart = new AmCharts.AmSerialChart();
								chart.dataProvider = chartData;
								chart.categoryField = "date";
								chart.startDuration = 1;
								chart.backgroundColor = '#FFFFFF';
								chart.backgroundAlpha = 0.95;
								chart.addTitle("转化率统计", 15);
								chart.addListener('clickGraphItem', function(a, b) {
									//alert(a.item);
								})
								// AXES
								// category
								var categoryAxis = chart.categoryAxis;
								//categoryAxis.labelRotation = 45; // this line makes category values to be rotated
								categoryAxis.gridAlpha = 0;
								categoryAxis.fillAlpha = 1;
								categoryAxis.fillColor = "#FAFAFA";
								categoryAxis.gridPosition = "start";

								// value
								var valueAxis = new AmCharts.ValueAxis();
								valueAxis.dashLength = 5;
								valueAxis.title = "转化率"
								valueAxis.axisAlpha = 0;
								chart.addValueAxis(valueAxis);

								// GRAPH
								var graph = new AmCharts.AmGraph();
								graph.valueField = "conversion_rate";
								graph.fillColors = "#2E7824";
								graph.labelText = "[[conversion_rate]]%";
								graph.balloonText = "[[date]]: [[conversion_rate]]%";
								graph.type = "column";
								//graph.urlField = "url";
								//graph.urlTarget = "_blank";
								graph.lineAlpha = 0;
								graph.fillAlphas = 1;
								chart.addGraph(graph);

								// WRITE
								chart.write("chartdiv");
							});
						</script>
						<p id="chartdiv" style="width:90%; height:350px;"></p>
					</div>
					<div id="infobox" class="margin-left">
						<script type="text/javascript">
							var chart;

							var chartData2 = [
							<?php $_from = $this->_tpl_vars['conversion']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['list']):
?>
							{
							"date":"<?php echo $this->_tpl_vars['list']['time']; ?>
",
							"url":"?module=Conversion&action=hourrate&date=<?php echo $this->_tpl_vars['list']['time']; ?>
",
							"conversion_rate":<?php echo $this->_tpl_vars['list']['regrate']; ?>

							},
							<?php endforeach; endif; unset($_from); ?>];

							AmCharts.ready(function() {
								// SERIAL CHART
								chart = new AmCharts.AmSerialChart();
								chart.dataProvider = chartData2;
								chart.categoryField = "date";
								chart.startDuration = 1;
								chart.backgroundColor = '#FFFFFF';
								chart.backgroundAlpha = 0.95;
								chart.addTitle("注册率统计", 15);
								chart.addListener('clickGraphItem', function(a, b) {
									//alert(a.item);
								})
								// AXES
								// category
								var categoryAxis = chart.categoryAxis;
								//categoryAxis.labelRotation = 45; // this line makes category values to be rotated
								categoryAxis.gridAlpha = 0;
								categoryAxis.fillAlpha = 1;
								categoryAxis.fillColor = "#FAFAFA";
								categoryAxis.gridPosition = "start";

								// value
								var valueAxis = new AmCharts.ValueAxis();
								valueAxis.dashLength = 5;
								valueAxis.title = "注册率"
								valueAxis.axisAlpha = 0;
								chart.addValueAxis(valueAxis);

								// GRAPH
								var graph = new AmCharts.AmGraph();
								graph.valueField = "conversion_rate";
								graph.fillColors = "#00BBCC";
								graph.labelText = "[[conversion_rate]]%";
								graph.balloonText = "[[date]]: [[conversion_rate]]%";
								graph.type = "column";
								//graph.urlField = "url";
								//graph.urlTarget = "_blank";
								graph.lineAlpha = 0;
								graph.fillAlphas = 1;
								chart.addGraph(graph);

								// WRITE
								chart.write("chartdiv2");
							});
						</script>
						<p id="chartdiv2" style="width: 90%; height: 350px;"></p>
					</div>
					<div id="infobox">


					<div id="infobox">
						<div style="margin-top: 20px; text-align:center; vertical-align:middle; line-height:24px"><a style="font-family:Verdana;font-size:15px;font-weight:bold;" href="index.php?module=sale&action=payment">支付率统计(点击查看详细支付统计)</a></div>
						<p id="chartdiv3" style="width: 90%; height: 350px;"></p>
					
											<script type="text/javascript">
							var chart;

							var chartData3 = [
							<?php $_from = $this->_tpl_vars['conversion']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['list']):
?>
							{
							"date":"<?php echo $this->_tpl_vars['list']['time']; ?>
",
							"url":"?module=Conversion&action=hourrate&date=<?php echo $this->_tpl_vars['list']['time']; ?>
",
							"conversion_rate":<?php echo $this->_tpl_vars['list']['payrate']; ?>

							},
							<?php endforeach; endif; unset($_from); ?>];

							AmCharts.ready(function() {
								// SERIAL CHART
								chart = new AmCharts.AmSerialChart();
								chart.dataProvider = chartData3;
								chart.categoryField = "date";
								chart.startDuration = 1;
								chart.backgroundColor = '#FFFFFF';
								chart.backgroundAlpha = 0.95;
								// chart.addTitle("支付率统计", 15);
								chart.addListener('clickGraphItem', function(a, b) {
									//alert(a.item);
								})
								// AXES
								// category
								var categoryAxis = chart.categoryAxis;
								//categoryAxis.labelRotation = 45; // this line makes category values to be rotated
								categoryAxis.gridAlpha = 0;
								categoryAxis.fillAlpha = 1;
								categoryAxis.fillColor = "#FAFAFA";
								categoryAxis.gridPosition = "start";

								// value
								var valueAxis = new AmCharts.ValueAxis();
								valueAxis.dashLength = 5;
								valueAxis.title = "支付率"
								valueAxis.axisAlpha = 0;
								chart.addValueAxis(valueAxis);

								// GRAPH
								var graph = new AmCharts.AmGraph();
								graph.valueField = "conversion_rate";
								graph.fillColors = "#FF0F00";
								graph.labelText = "[[conversion_rate]]%";
								graph.balloonText = "[[date]]: [[conversion_rate]]%";
								graph.type = "column";
								graph.urlField = "index.php?module=sale&action=payment";
								//graph.urlTarget = "_blank";
								graph.lineAlpha = 0;
								graph.fillAlphas = 1;
								chart.addGraph(graph);

								// WRITE
								chart.write("chartdiv3");
							});
						</script>
					
					</div>

					<div id="infobox" class="margin-left">
						<script type="text/javascript">
							var chart;

							var chartData4 = [
							<?php $_from = $this->_tpl_vars['conversion']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['list']):
?>
							{
							"date":"<?php echo $this->_tpl_vars['list']['time']; ?>
",
							"url":"?module=Conversion&action=hourrate&date=<?php echo $this->_tpl_vars['list']['time']; ?>
",
							"conversion_rate":<?php echo $this->_tpl_vars['list']['visitdepth']; ?>

							},
							<?php endforeach; endif; unset($_from); ?>];

							AmCharts.ready(function() {
								// SERIAL CHART
								chart = new AmCharts.AmSerialChart();
								chart.dataProvider = chartData4;
								chart.categoryField = "date";
								chart.startDuration = 1;
								chart.backgroundColor = '#FFFFFF';
								chart.backgroundAlpha = 0.95;
								chart.addTitle("访问深度", 15);
								chart.addListener('clickGraphItem', function(a, b) {
									//alert(a.item);
								})
								// AXES
								// category
								var categoryAxis = chart.categoryAxis;
								//categoryAxis.labelRotation = 45; // this line makes category values to be rotated
								categoryAxis.gridAlpha = 0;
								categoryAxis.fillAlpha = 1;
								categoryAxis.fillColor = "#FAFAFA";
								categoryAxis.gridPosition = "start";

								// value
								var valueAxis = new AmCharts.ValueAxis();
								valueAxis.dashLength = 5;
								valueAxis.title = "访问深度"
								valueAxis.axisAlpha = 0;
								chart.addValueAxis(valueAxis);

								// GRAPH
								var graph = new AmCharts.AmGraph();
								graph.valueField = "conversion_rate";
								graph.fillColors = "#058DC7";
								graph.labelText = "[[conversion_rate]]";
								graph.balloonText = "[[date]]: [[conversion_rate]]";
								graph.type = "column";
								//graph.urlField = "url";
								//graph.urlTarget = "_blank";
								graph.lineAlpha = 0;
								graph.fillAlphas = 1;
								chart.addGraph(graph);

								// WRITE
								chart.write("chartdiv4");
							});
						</script>
						<p id="chartdiv4" style="width: 90%; height: 350px;"></p>
					</div>

				</div>
			</div>

		</div>

	</div>
</body>

</html>
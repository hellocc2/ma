<?php /* Smarty version 2.6.18, created on 2015-08-08 14:39:32
         compiled from operate_chart.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_headnav', 'operate_chart.html', 35, false),array('function', 'html_account', 'operate_chart.html', 52, false),array('function', 'html_siderbar', 'operate_chart.html', 56, false),)), $this); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Charts - Bootstrap Admin</title>
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />    
    
    <link href="<?php echo $this->_tpl_vars['media']; ?>
/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo $this->_tpl_vars['media']; ?>
/css/bootstrap-responsive.min.css" rel="stylesheet" />
    
    
    <link href="<?php echo $this->_tpl_vars['media']; ?>
/css/font-awesome.css" rel="stylesheet" />
    
    <link href="<?php echo $this->_tpl_vars['media']; ?>
/css/adminia.css" rel="stylesheet" /> 
    <link href="<?php echo $this->_tpl_vars['media']; ?>
/css/adminia-responsive.css" rel="stylesheet" /> 
    
    
    <link href="<?php echo $this->_tpl_vars['media']; ?>
/css/jquery.visualize.css" rel="stylesheet" /> 

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
	
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>

<body>
	
<div class="navbar navbar-fixed-top">
	
	<div class="navbar-inner">
		
	<?php echo smarty_function_html_headnav(array(), $this);?>
 <!-- /container -->
		
	</div> <!-- /navbar-inner -->
	
</div> <!-- /navbar -->




<div id="content">
	
	<div class="container">
		
		<div class="row">
			
			<div class="span3">
				
				<?php echo smarty_function_html_account(array(), $this);?>
 <!-- /account-container -->
				
				<hr />
				
				<?php echo smarty_function_html_siderbar(array(), $this);?>
		
				
				<hr />
				
				<div class="sidebar-extra">
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud.</p>
				</div> <!-- .sidebar-extra -->
				
				<br />
		
			</div> <!-- /span3 -->
			
			
			
			<div class="span9">
				
				<h1 class="page-title">
					<i class="icon-signal"></i>
					Charts					
				</h1>
				
				
				
				
				<div class="widget">
					
					<div class="widget-header">
						<h3>Area Chart</h3>
					</div> <!-- /widget-header -->
														
					<div class="widget-content">
						
						<div id="area-chart" class="chart-holder"></div> <!-- /area-chart -->
						
						
										
					</div> <!-- /widget-content -->
					
				</div> <!-- /widget -->
				
				
				
				
				<div class="widget">
					
					<div class="widget-header">
						<h3>Line Chart</h3>
					</div> <!-- /widget-header -->
														
					<div class="widget-content">
						
						<div id="line-chart" class="chart-holder"></div> <!-- /donut-chart -->
						
						
										
					</div> <!-- /widget-content -->
					
				</div> <!-- /widget -->
				
				
				
				<div class="widget">
					
					<div class="widget-header">
						<h3>Bar Chart</h3>
					</div> <!-- /widget-header -->
														
					<div class="widget-content">
						
						<div id="bar-chart" class="chart-holder"></div> <!-- /donut-chart -->
						
						
										
					</div> <!-- /widget-content -->
					
				</div> <!-- /widget -->
				
				
				
				
				<div class="widget">
					
					<div class="widget-header">
						<h3>Pie Chart</h3>
					</div> <!-- /widget-header -->
														
					<div class="widget-content">
						
						<div id="pie-chart" class="chart-holder"></div> <!-- /donut-chart -->
						
						
										
					</div> <!-- /widget-content -->
					
				</div> <!-- /widget -->
				
				
				
			</div> <!-- /span9 -->
			
			
		</div> <!-- /row -->
		
	</div> <!-- /container -->
	
</div> <!-- /content -->
					
	
<div id="footer">
	
	<div class="container">				
		<hr />
		<p>&copy; 2012 Go Ideate.More Templates <a href="http://www.cssmoban.com/" target="_blank" title="模板之家">模板之家</a> - Collect from <a href="http://www.cssmoban.com/" title="网页模板" target="_blank">网页模板</a></p>
	</div> <!-- /container -->
	
</div> <!-- /footer -->


    

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/excanvas.min.js"></script>
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/jquery.flot.js"></script>
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/jquery.flot.pie.js"></script>
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/jquery.flot.orderBars.js"></script>
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/jquery.flot.resize.js"></script>


<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/bootstrap.js"></script>
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/charts/bar.js"></script>
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/charts/area.js"></script>
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/charts/line.js"></script>
<script src="<?php echo $this->_tpl_vars['media']; ?>
/js/charts/pie.js"></script>



  </body>
</html>
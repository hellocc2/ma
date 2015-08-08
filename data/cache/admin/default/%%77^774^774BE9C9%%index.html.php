<?php /* Smarty version 2.6.18, created on 2015-08-08 14:07:08
         compiled from index.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_headnav', 'index.html', 34, false),array('function', 'html_account', 'index.html', 51, false),array('function', 'html_siderbar', 'index.html', 56, false),)), $this); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>经验总结</title>
    
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
/css/pages/dashboard.css" rel="stylesheet" /> 
    

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
	
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>

<body>
	
<div class="navbar navbar-fixed-top">
	
	<div class="navbar-inner">
		
		<?php echo smarty_function_html_headnav(array(), $this);?>

		
	</div> <!-- /navbar-inner -->
	
</div> <!-- /navbar -->


<div class="copyrights">Collect from CAICAI2015</div>

<div id="content">
	
	<div class="container">
		
		<div class="row">
			
			<div class="span3">
				
				<?php echo smarty_function_html_account(array(), $this);?>

				<!-- /account-container -->
				
				<hr />
				
				<?php echo smarty_function_html_siderbar(array(), $this);?>

				
				<hr />
				
				<br />
		
			</div> <!-- /span3 -->
			
			
			
			<div class="span9">
				
				<h1 class="page-title">
					<i class="icon-home"></i>
					经验总结					
				</h1>
				
				<div class="widget" id="widget">
										
					<div class="widget-content">
						<form class="form-inline" align="right">
						  <div class="form-group">
							<input type="text" class="form-control" id="articleName" placeholder="文章标题...">
						    <button type="submit" class="btn btn-default">查找</button>
							<button type="button" class="btn btn-default">添加</button>
					      </div>
					  	</form>
						<ol class="faq-list">
							<li>
									<h4>今天星期几？</h4>
									<p>周一空，周二多，周三看阻力点，周四与周二反，周五空  2015-6-3</p>	
									
							</li>
							
							<li>
									<h4>是否在起涨起跌点做的单？</h4>
									<p>通常支撑和压力位在起涨起跌点</p>	
									
							</li>
							
							<li>
									<h4>是否在起涨起跌点做的单？</h4>
									<p>通常支撑和压力位在起涨起跌点</p>	
									
							</li>
							
							<li>
									<h4>是否在起涨起跌点做的单？</h4>
									<p>通常支撑和压力位在起涨起跌点</p>	
									
							</li>
							
							<li>
									<h4>是否在起涨起跌点做的单？</h4>
									<p>通常支撑和压力位在起涨起跌点</p>	
									
							</li>
							
							<li>
									<h4>是否在起涨起跌点做的单？</h4>
									<p>通常支撑和压力位在起涨起跌点</p>	
									
							</li>
							
							<li>
									<h4>是否在起涨起跌点做的单？</h4>
									<p>通常支撑和压力位在起涨起跌点</p>	
									
							</li>
							
						</ol>
										
					</div>
					
				</div> <!-- /widget -->
				
			</div> <!-- /span9 -->
			
			
		</div> <!-- /row -->
		
	</div> <!-- /container -->
	
</div> <!-- /content -->
					
	
<div id="footer">
	
	<div class="container">				
		<hr />
		<p>&copy; CAICAI2015</p>
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

  </body>
</html>
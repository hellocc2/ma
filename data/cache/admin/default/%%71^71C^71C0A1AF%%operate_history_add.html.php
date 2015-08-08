<?php /* Smarty version 2.6.18, created on 2015-08-08 15:35:55
         compiled from operate_history_add.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_headnav', 'operate_history_add.html', 35, false),array('function', 'html_account', 'operate_history_add.html', 52, false),array('function', 'html_siderbar', 'operate_history_add.html', 56, false),array('modifier', 'date_format', 'operate_history_add.html', 86, false),)), $this); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>添加行情</title>
    
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
/css/pages/plans.css" rel="stylesheet" /> 

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
					<i class="icon-th-large"></i>
					添加行情				
				</h1>
				
				
				<div class="row">
					
					<div class="span9">
				
						<div class="widget">
							
							<div class="widget-header">
								<h3><?php echo ((is_array($_tmp=$this->_tpl_vars['time'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d %H:%I:%S") : smarty_modifier_date_format($_tmp, "%Y-%m-%d %H:%I:%S")); ?>
</h3>
							</div> <!-- /widget-header -->
									
							<div class="widget-content">
								
								
								
								<div class="tabbable">
											
						<br />
						
							<div class="tab-content">
								<div class="tab-pane active" id="1">
								<form id="edit-profile" class="form-horizontal" />
									<fieldset>
										
										<div class="control-group">											
											<label class="control-label" for="firstname">日期</label>
											<div class="controls">
												<input type="text" class="input-medium" id="firstname" value="Rod" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<div class="control-group">											
											<label class="control-label" for="firstname">星期</label>
											<div class="controls">
												<input type="text" class="input-medium" id="firstname" value="Rod" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<div class="control-group">											
											<label class="control-label" for="lastname">方向</label>
											<div class="controls">
											<input type="radio" name="trend" value="1"/>涨&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="radio" name="trend" value="2"/>跌											
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<div class="control-group">											
											<label class="control-label" for="email">点位</label>
											<div class="controls">
												<input type="text" class="input-large" id="email" value="rod.howard@example.com" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<div class="control-group">											
											<label class="control-label" for="password1">幅度</label>
											<div class="controls">
												<input type="password" class="input-medium" id="password1" value="password" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<div class="control-group">											
											<label class="control-label" for="password2">备注</label>
											<div class="controls">
												<input type="password" class="input-medium" id="password2" value="password" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										<div class="control-group">											
											<label class="control-label" for="password2">事件</label>
											<div class="controls">
												<input type="password" class="input-medium" id="password2" value="password" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
											
											<br />
										
											
										<div class="form-actions">
											<button type="submit" class="btn btn-primary">保存</button> 
											<button class="btn">取消</button>
										</div> <!-- /form-actions -->
									</fieldset>
								</form>
								</div>
								
								
								
							</div>
						  
						  
						</div>
								
							
								
							</div> <!-- /widget-content -->
							
						</div> <!-- /widget -->
						
					</div> <!-- /span9 -->
					
				</div> <!-- /row -->
				
				
				
				
				
				
				
				
				
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
/js/bootstrap.js"></script>

  </body>
</html>
<?php /* Smarty version 2.6.18, created on 2015-08-08 14:39:29
         compiled from member_account.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_headnav', 'member_account.html', 35, false),array('function', 'html_account', 'member_account.html', 52, false),array('function', 'html_siderbar', 'member_account.html', 56, false),)), $this); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>User Account - Bootstrap Admin</title>
    
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
					User Account					
				</h1>
				
				
				<div class="row">
					
					<div class="span9">
				
						<div class="widget">
							
							<div class="widget-header">
								<h3>Basic Information</h3>
							</div> <!-- /widget-header -->
									
							<div class="widget-content">
								
								
								
								<div class="tabbable">
						<ul class="nav nav-tabs">
						  <li class="active">
						    <a href="#1" data-toggle="tab">Profile</a>
						  </li>
						  <li><a href="#2" data-toggle="tab">Settings</a></li>
						</ul>
						
						<br />
						
							<div class="tab-content">
								<div class="tab-pane active" id="1">
								<form id="edit-profile" class="form-horizontal" />
									<fieldset>
										
										<div class="control-group">											
											<label class="control-label" for="username">Username</label>
											<div class="controls">
												<input type="text" class="input-medium disabled" id="username" value="goideate" disabled="" />
												<p class="help-block">Your username is for logging in and cannot be changed.</p>
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<div class="control-group">											
											<label class="control-label" for="firstname">First Name</label>
											<div class="controls">
												<input type="text" class="input-medium" id="firstname" value="Rod" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<div class="control-group">											
											<label class="control-label" for="lastname">Last Name</label>
											<div class="controls">
												<input type="text" class="input-medium" id="lastname" value="Howard" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<div class="control-group">											
											<label class="control-label" for="email">Email Address</label>
											<div class="controls">
												<input type="text" class="input-large" id="email" value="rod.howard@example.com" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<br /><br />
										
										<div class="control-group">											
											<label class="control-label" for="password1">Password</label>
											<div class="controls">
												<input type="password" class="input-medium" id="password1" value="password" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
										<div class="control-group">											
											<label class="control-label" for="password2">Confirm</label>
											<div class="controls">
												<input type="password" class="input-medium" id="password2" value="password" />
											</div> <!-- /controls -->				
										</div> <!-- /control-group -->
										
										
											
											<br />
										
											
										<div class="form-actions">
											<button type="submit" class="btn btn-primary">Save</button> 
											<button class="btn">Cancel</button>
										</div> <!-- /form-actions -->
									</fieldset>
								</form>
								</div>
								
								<div class="tab-pane" id="2">
									<form id="edit-profile2" class="form-horizontal" />
										<fieldset>
											
											
											<div class="control-group">
												<label class="control-label" for="accounttype">Account Type</label>
												<div class="controls">
													<label class="radio">
														<input type="radio" name="accounttype" value="option1" checked="checked" id="accounttype" />
														POP3
													</label>
													<label class="radio">
														<input type="radio" name="accounttype" value="option2" />
														IMAP
													</label>
												</div>
											</div>
											<div class="control-group">
												<label class="control-label" for="accountusername">Account Username</label>
												<div class="controls">
													<input type="text" class="input-large" id="accountusername" value="rod.howard@example.com" />
													<p class="help-block">Leave blank to use your profile email address.</p>
												</div>
											</div>
											<div class="control-group">
												<label class="control-label" for="emailserver">Email Server</label>
												<div class="controls">
													<input type="text" class="input-large" id="emailserver" value="mail.example.com" />
												</div>
											</div>
											<div class="control-group">
												<label class="control-label" for="accountpassword">Password</label>
												<div class="controls">
													<input type="text" class="input-large" id="accountpassword" value="password" />
												</div>
											</div>
											
																						
											
											
											<div class="control-group">
												<label class="control-label" for="accountadvanced">Advanced Settings</label>
												<div class="controls">
													<label class="checkbox">
														<input type="checkbox" name="accountadvanced" value="option1" checked="checked" id="accountadvanced" />
														User encrypted connection when accessing this server
													</label>
													<label class="checkbox">
														<input type="checkbox" name="accounttype" value="option2" />
														Download all message on connection
													</label>
												</div>
											</div>

											
											<br />
											
											<div class="form-actions">
												<button type="submit" class="btn btn-primary">Save</button> <button class="btn">Cancel</button>
											</div>
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
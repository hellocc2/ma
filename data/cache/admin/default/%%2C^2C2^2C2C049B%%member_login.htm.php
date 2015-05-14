<?php /* Smarty version 2.6.18, created on 2014-08-26 03:32:32
         compiled from member_login.htm */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['image_url']; ?>
login.css" />
	<body>
		<center>
			<form name="form1" method="post" action="">
				<div class="loginplan">
					<div class="loginname"><img src="<?php echo $this->_tpl_vars['image_url']; ?>
logo.png">
					</div>
					<div style="height:10px;padding:4px 0 0 2px;"></div>
					<div >
						<strong>登录系统</strong> <BR />
						<span style="color:#e16c2c" id="showtime"><?php echo $this->_tpl_vars['error']; ?>
</span>
					</div>
					<div style="height:10px;padding:4px 0 0 2px;"></div>
					<div style="height:25px;">
						<label class="column"> 　　帐　号:</label>
						<input name="username" id="username" tabindex="1" maxlength="20" onfocus="this.value=''" autocomplete="off" type="text">
					</div>
					<div style="height:25px;margin:8px 0 0 0;">
						<label class="column"> 　　密　码:</label>
						<input name="userpass" id="userpass" tabindex="2" value="" maxlength="20" onfocus="this.value=''" autocomplete="off" type="password">
					</div>
					<div style="padding:12px 0 0 56px;clear:both;height:27px!important;height:;">
						<input class="btn" style="font: bold 12px Verdana; padding-top: 2px ! important;" value="登 录" name="btlogin" tabindex="4" type="submit">
					</div>
				</div>
			</form>
			<div class="wd">
				<div class="navPageBottom">
					Powered by <a href="http://www.milanoo.com/" target="_blank"><b>Milanoo.com</b></a>
					&nbsp;© 2007-2009
					<br>
				</div>
			</div>
		</center>
	</body>
</html>
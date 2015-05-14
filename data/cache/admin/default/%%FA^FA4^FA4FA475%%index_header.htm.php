<?php /* Smarty version 2.6.18, created on 2014-08-26 03:48:10
         compiled from index_header.htm */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<body>
<div id="container">
	<div id="header">
		<h2>Milanoo Analytics</h2>
		<div id="topmenu">
			<ul>
				<li><a href="http://ht.milanoo.com/milanooht/" target="_blank">后台</a></li>
				<li><a href="http://boss.milanoodeals.com/" target="_blank">商品库</a></li>
				<li class="current"><a href="#" >MA</a></li>
				<li ><a href="http://oa.milanoo.cn" target="_blank">OA</a></li>
			</ul>
		</div>
		
		<div id="website-select">
			<form action="" method="GET">
				<select id="website-options" name="website-options">
				    <option  <?php if ($this->_tpl_vars['websiteId'] == '666'): ?>selected="selected"<?php endif; ?> value="&websiteId=666">全站</option>
					<option  <?php if ($this->_tpl_vars['websiteId'] == '1'): ?>selected="selected"<?php endif; ?> value="&websiteId=1">Milanoo</option>
					<option  <?php if ($this->_tpl_vars['websiteId'] == '7'): ?>selected="selected"<?php endif; ?> value="&websiteId=7">milanoo.fr</option>
					<option  <?php if ($this->_tpl_vars['websiteId'] == '101'): ?>selected="selected"<?php endif; ?> value="&websiteId=101">Wap</option>
					<option  <?php if ($this->_tpl_vars['websiteId'] == '201'): ?>selected="selected"<?php endif; ?> value="&websiteId=201">iPad</option>					
					<option  <?php if ($this->_tpl_vars['websiteId'] == '2'): ?>selected="selected"<?php endif; ?> value="&websiteId=2">Dressinwedding</option>
					<option  <?php if ($this->_tpl_vars['websiteId'] == '3'): ?>selected="selected"<?php endif; ?> value="&websiteId=3">Lolitashow</option>
					<option  <?php if ($this->_tpl_vars['websiteId'] == '4'): ?>selected="selected"<?php endif; ?> value="&websiteId=4">Cosplay</option>
					<option  <?php if ($this->_tpl_vars['websiteId'] == '5'): ?>selected="selected"<?php endif; ?> value="&websiteId=5">Costumeslive</option>
				</select>
			</form>
		</div>
		<div id="country-select">
				<form action=""  method="GET">
					<select id="country-options" name="country-options">
						<option  <?php if ($this->_tpl_vars['lang'] == 'all'): ?>selected="selected"<?php endif; ?> value="&lang=all">全站</option>
						<option  <?php if ($this->_tpl_vars['lang'] == 'EN'): ?>selected="selected"<?php endif; ?> value="&lang=EN">EN</option>
						<option  <?php if ($this->_tpl_vars['lang'] == 'JP'): ?>selected="selected"<?php endif; ?> value="&lang=JP">JP</option>
						<option  <?php if ($this->_tpl_vars['lang'] == 'FR'): ?>selected="selected"<?php endif; ?> value="&lang=FR">FR</option>
						<option  <?php if ($this->_tpl_vars['lang'] == 'ES'): ?>selected="selected"<?php endif; ?> value="&lang=ES">ES</option>
						<option  <?php if ($this->_tpl_vars['lang'] == 'DE'): ?>selected="selected"<?php endif; ?> value="&lang=DE">DE</option>
						<option  <?php if ($this->_tpl_vars['lang'] == 'IT'): ?>selected="selected"<?php endif; ?> value="&lang=IT">IT</option>
						<option  <?php if ($this->_tpl_vars['lang'] == 'RU'): ?>selected="selected"<?php endif; ?> value="&lang=RU">RU</option>
						<option  <?php if ($this->_tpl_vars['lang'] == 'PT'): ?>selected="selected"<?php endif; ?> value="&lang=PT">PT</option>
					</select>
				</form>
			</div>
		<div id="logout"><a href="?module=logout&action=index">注销</a></div>
		
	</div>

</div>
</body>

</html>
<?php /* Smarty version 2.6.18, created on 2014-08-26 05:54:39
         compiled from category_management.htm */ ?>
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
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['image_url']; ?>
jquery.treeview.css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['image_url']; ?>
screen.css" />
<script type="text/javascript" src="<?php echo $this->_tpl_vars['javascript_url']; ?>
jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['javascript_url']; ?>
jquery.treeview.js"></script>
<body>
<input id="frame_url" type="hidden" value="<?php echo $_SERVER['REQUEST_URI']; ?>
"/>
<style type="text/css">
ul,ol,li{
	margin:auto;
	padding: 0 15px;
	list-style-position: inside;
}

</style>
<div id="container">
	<div id="wrapper">
		<div id="content">
			<div id="rightnow">
			<table width="100%" border="0" cellpadding="2" cellspacing="6"><tr><td><table width="100%" border="0" cellpadding="0" cellspacing="0" class="guide"><tr><td></td></tr></table>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
			<tr><td class="altbg1">
			<?php $_from = $this->_tpl_vars['Website_array']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['Website']):
?>
				<option value="<?php echo $this->_tpl_vars['key']; ?>
" <?php if ($this->_tpl_vars['WebsiteId'] == $this->_tpl_vars['key']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['Website']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
			</select>
			<a href="index.php?module=statistics&action=category&menu_action=add&WebsiteId=<?php echo $this->_tpl_vars['WebsiteId']; ?>
" title="添加一级分类">[添加一级分类]</a>
			<br />
			<br />
			<form method="post" action="" target="main">
			<input type="hidden" name="module_id" value="<?php echo $this->_tpl_vars['module_id']; ?>
">
			<input type="hidden" name="module_action" value="action">
			<input type="hidden" name="categroy_action" value="categroy_action">
			<input type="hidden" name="menu_action" value="order">
			
			<ul id="navigation">
			<?php echo $this->_tpl_vars['promotionurl_all_print']; ?>

			</ul>
			<br />
			
			</form>
			</td></tr></table>
			</td></tr></table>
			</div>
		</div>
	</div>
</div>

<script>

$(document).ready(function(){
	// second example
	$("#navigation").treeview({
		persist: "location",
		collapsed: true,
		unique: true
	});
});

jq(document).ready(function() {
	jq("#WebsiteId").change(function(){
		var websiteId = jq("#WebsiteId").val();
		var url = get_current_url();
		var module_id = '<?php echo $this->_tpl_vars['module_id']; ?>
';
		var ajax_url = url + "?module_id="+module_id+"&WebsiteId="+websiteId+"&menu_action=add";
		window.location.href=ajax_url;
	});
});

function get_current_url(){
	var ajax_url = "http://"+ window.location.hostname + window.location.pathname;
	return ajax_url;
}


</script>

</body>
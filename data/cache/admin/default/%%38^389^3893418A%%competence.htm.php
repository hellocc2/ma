<?php /* Smarty version 2.6.18, created on 2014-08-26 06:35:37
         compiled from competence.htm */ ?>
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
#rightnow ul li {float:left;margin-left:-1px;}
#rightnow ul li a {display:block;width:30px;height:30px;line-height:30px;text-align:center;border:1px solid #ddd;background-color:#f1f1f1;}
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
                    <h3 class="reallynow">
                        <span>权限组管理</span>
                    </h3>
			  </div>
			  
			  <div id="rightnow">
                  
			  </div>			  
			  
              <div id="infowrap">
	              <div id="infobox">
						<div class="mini-layout" style="margin:20px;width:190px;">
							<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
							<tr class="header">
								<td width="30%" align="center">名称:</td>
							  <td align="center" width="30%">操作</td>
							</tr>
							<?php unset($this->_sections['competence_all']);
$this->_sections['competence_all']['name'] = 'competence_all';
$this->_sections['competence_all']['loop'] = is_array($_loop=$this->_tpl_vars['competence_all']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['competence_all']['show'] = true;
$this->_sections['competence_all']['max'] = $this->_sections['competence_all']['loop'];
$this->_sections['competence_all']['step'] = 1;
$this->_sections['competence_all']['start'] = $this->_sections['competence_all']['step'] > 0 ? 0 : $this->_sections['competence_all']['loop']-1;
if ($this->_sections['competence_all']['show']) {
    $this->_sections['competence_all']['total'] = $this->_sections['competence_all']['loop'];
    if ($this->_sections['competence_all']['total'] == 0)
        $this->_sections['competence_all']['show'] = false;
} else
    $this->_sections['competence_all']['total'] = 0;
if ($this->_sections['competence_all']['show']):

            for ($this->_sections['competence_all']['index'] = $this->_sections['competence_all']['start'], $this->_sections['competence_all']['iteration'] = 1;
                 $this->_sections['competence_all']['iteration'] <= $this->_sections['competence_all']['total'];
                 $this->_sections['competence_all']['index'] += $this->_sections['competence_all']['step'], $this->_sections['competence_all']['iteration']++):
$this->_sections['competence_all']['rownum'] = $this->_sections['competence_all']['iteration'];
$this->_sections['competence_all']['index_prev'] = $this->_sections['competence_all']['index'] - $this->_sections['competence_all']['step'];
$this->_sections['competence_all']['index_next'] = $this->_sections['competence_all']['index'] + $this->_sections['competence_all']['step'];
$this->_sections['competence_all']['first']      = ($this->_sections['competence_all']['iteration'] == 1);
$this->_sections['competence_all']['last']       = ($this->_sections['competence_all']['iteration'] == $this->_sections['competence_all']['total']);
?>
							<tr align="center"><td class="altbg1"><?php echo $this->_tpl_vars['competence_all'][$this->_sections['competence_all']['index']]['name']; ?>
</td><td class="altbg2"><a href="?module=competence&action=Index&operate=edit&id=<?php echo $this->_tpl_vars['competence_all'][$this->_sections['competence_all']['index']]['id']; ?>
">[编辑]</a>
							<a href="?module=competence&action=Index&act=del&id=<?php echo $this->_tpl_vars['competence_all'][$this->_sections['competence_all']['index']]['id']; ?>
" onClick="javascript: return confirm('删除权限组将所有该权限下的系统用户改变为自定义权限；\r\n且用户没有任务系统操作权限；\r\n\r\n确定要删除选中项吗？'); ">[删除]</a>
							</td></tr>
							<?php endfor; endif; ?>
							</table>
						</div>	     
	              </div>
              </div>
            </div>
            
      </div>
        
</div>
</body>

</html>
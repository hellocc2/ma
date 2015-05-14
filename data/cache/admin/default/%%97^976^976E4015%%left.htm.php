<?php /* Smarty version 2.6.18, created on 2014-08-26 03:48:10
         compiled from left.htm */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<body style="background-color:#F5FAFF;">
	<div id="container">
        <div id="wrapper"> 
            <div id="sidebar">
  				<ul>
					<?php unset($this->_sections['big']);
$this->_sections['big']['name'] = 'big';
$this->_sections['big']['loop'] = is_array($_loop=$this->_tpl_vars['all_id']['0']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['big']['show'] = true;
$this->_sections['big']['max'] = $this->_sections['big']['loop'];
$this->_sections['big']['step'] = 1;
$this->_sections['big']['start'] = $this->_sections['big']['step'] > 0 ? 0 : $this->_sections['big']['loop']-1;
if ($this->_sections['big']['show']) {
    $this->_sections['big']['total'] = $this->_sections['big']['loop'];
    if ($this->_sections['big']['total'] == 0)
        $this->_sections['big']['show'] = false;
} else
    $this->_sections['big']['total'] = 0;
if ($this->_sections['big']['show']):

            for ($this->_sections['big']['index'] = $this->_sections['big']['start'], $this->_sections['big']['iteration'] = 1;
                 $this->_sections['big']['iteration'] <= $this->_sections['big']['total'];
                 $this->_sections['big']['index'] += $this->_sections['big']['step'], $this->_sections['big']['iteration']++):
$this->_sections['big']['rownum'] = $this->_sections['big']['iteration'];
$this->_sections['big']['index_prev'] = $this->_sections['big']['index'] - $this->_sections['big']['step'];
$this->_sections['big']['index_next'] = $this->_sections['big']['index'] + $this->_sections['big']['step'];
$this->_sections['big']['first']      = ($this->_sections['big']['iteration'] == 1);
$this->_sections['big']['last']       = ($this->_sections['big']['iteration'] == $this->_sections['big']['total']);
?>
					<?php $this->assign('dig', $this->_tpl_vars['all_id']['0'][$this->_sections['big']['index']]); ?>
					<?php if (in_array ( $this->_tpl_vars['dig'] , $this->_tpl_vars['rule'] )): ?>
						<li><h3><a href="#" class="house"><?php echo $this->_tpl_vars['name'][$this->_tpl_vars['dig']]; ?>
</a></h3>
		 					<ul>
							<?php unset($this->_sections['dig']);
$this->_sections['dig']['name'] = 'dig';
$this->_sections['dig']['loop'] = is_array($_loop=$this->_tpl_vars['all_id'][$this->_tpl_vars['dig']]) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['dig']['show'] = true;
$this->_sections['dig']['max'] = $this->_sections['dig']['loop'];
$this->_sections['dig']['step'] = 1;
$this->_sections['dig']['start'] = $this->_sections['dig']['step'] > 0 ? 0 : $this->_sections['dig']['loop']-1;
if ($this->_sections['dig']['show']) {
    $this->_sections['dig']['total'] = $this->_sections['dig']['loop'];
    if ($this->_sections['dig']['total'] == 0)
        $this->_sections['dig']['show'] = false;
} else
    $this->_sections['dig']['total'] = 0;
if ($this->_sections['dig']['show']):

            for ($this->_sections['dig']['index'] = $this->_sections['dig']['start'], $this->_sections['dig']['iteration'] = 1;
                 $this->_sections['dig']['iteration'] <= $this->_sections['dig']['total'];
                 $this->_sections['dig']['index'] += $this->_sections['dig']['step'], $this->_sections['dig']['iteration']++):
$this->_sections['dig']['rownum'] = $this->_sections['dig']['iteration'];
$this->_sections['dig']['index_prev'] = $this->_sections['dig']['index'] - $this->_sections['dig']['step'];
$this->_sections['dig']['index_next'] = $this->_sections['dig']['index'] + $this->_sections['dig']['step'];
$this->_sections['dig']['first']      = ($this->_sections['dig']['iteration'] == 1);
$this->_sections['dig']['last']       = ($this->_sections['dig']['iteration'] == $this->_sections['dig']['total']);
?>
							<?php $this->assign('zig', $this->_tpl_vars['all_id'][$this->_tpl_vars['dig']][$this->_sections['dig']['index']]); ?>
								<?php if (in_array ( $this->_tpl_vars['zig'] , $this->_tpl_vars['rule'] )): ?><li><a href="?module=<?php echo $this->_tpl_vars['module'][$this->_tpl_vars['zig']]; ?>
&action=<?php echo $this->_tpl_vars['action'][$this->_tpl_vars['zig']]; ?>
" target="mainFrame" class="report_seo"><?php echo $this->_tpl_vars['name'][$this->_tpl_vars['zig']]; ?>
</a></li><?php endif; ?>
							<?php endfor; endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php endfor; endif; ?>
  				</ul>                                       
   
          </div>
      </div>
</div>
</body>
</html>


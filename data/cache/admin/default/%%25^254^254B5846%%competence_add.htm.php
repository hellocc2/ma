<?php /* Smarty version 2.6.18, created on 2014-08-26 06:35:41
         compiled from competence_add.htm */ ?>
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
	ul {list-style:none;margin:0px;}
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
<script>
$(document).ready(function(){
    $('.check:button').toggle(function(){
        $('.idarray').attr('checked','checked');
        $(this).val('不选')
    },function(){
        $('.idarray').removeAttr('checked');
        $(this).val('全选');
    })
}) 

function checked(){ 
        var isChecked = false; 
        $(".idarray").each(function(){ 
                    if($(this).attr("checked")==true || $(this).attr("checked")=="checked"){ 
                    isChecked=true; 
                    return;} 
            })
        return isChecked; 
    } 
</script>
<body>
	<input id="frame_url" type="hidden" value="<?php echo $_SERVER['REQUEST_URI']; ?>
"/>
	<div id="container">
		<div id="wrapper">
			<div id="content">
				<div id="rightnow">
					<h3 class="reallynow"><span>权限组管理</span></h3>
				</div>

				<div id="rightnow">

				</div>

				<div id="infowrap">
					<div id="infobox">
						<div class="mini-layout" style="margin:20px;width:790px;">
							<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
								<tbody id="menu_tip" style="display:<?php if (in_array ( 'tip' , $this->_tpl_vars['menu_cookie'] )): ?>none<?php endif; ?>">
									<tr>
										<td>
										<ul>
											<li>
												权限组操作为在栏目后的复框进行勾选，勾中代表有此权限。
											</li>
										</ul></td>
									</tr>
								</tbody>
							</table>

							<br />
							<form method="post" action="" name="form1">
								<input type="hidden" name="act" value="<?php echo $this->_tpl_vars['action_name']; ?>
post">
								<input type="hidden" name="id" value="<?php echo $this->_tpl_vars['competence_id']; ?>
">
								<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
									<tr class="header">
										<td colspan="2"><?php echo $this->_tpl_vars['action_lang'][$this->_tpl_vars['action_name']]; ?>
权限组</td>
									</tr>
									<tr>
										<td class="altbg1" width="32%"><B>名称:</B>
										<BR>
										<SPAN class=smalltxt>权限组名称</SPAN></td>
										<td class="altbg2" width="68%">
										<input type="text" name="competence_name" value="<?php echo $this->_tpl_vars['edit_array']['name']; ?>
" size="20">
										</td>
									</tr>
									<tr>
										<td width="32%" valign="top" class="altbg1"><B>权限组说明:</B>
										<BR>
										<SPAN class=smalltxt>权限组使用人群及其它的说明</SPAN></td>
										<td width="68%" class="altbg2">
											<TEXTAREA id=note name=competence_note rows="6" cols="50"><?php echo $this->_tpl_vars['edit_array']['note']; ?>
</TEXTAREA>
										</td>
									</tr>
								</table>
								<br />
								<script language="javascript">
									var	menu_exit_id = new Array;
									menu_exit_id[0]='<?php echo $this->_tpl_vars['exit_id'][0]; ?>
';
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
									menu_exit_id[<?php echo $this->_tpl_vars['dig']; ?>
]='<?php echo $this->_tpl_vars['exit_id'][$this->_tpl_vars['dig']]; ?>
';
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
									//menu_exit_id[<?php echo $this->_tpl_vars['zig']; ?>
]='<?php echo $this->_tpl_vars['exit_id'][$this->_tpl_vars['zig']]; ?>
';

									menu_exit_id[<?php echo $this->_tpl_vars['zig']; ?>
]='<?php unset($this->_sections['competence']);
$this->_sections['competence']['name'] = 'competence';
$this->_sections['competence']['loop'] = is_array($_loop=$this->_tpl_vars['competence'][$this->_tpl_vars['zig']]) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['competence']['show'] = true;
$this->_sections['competence']['max'] = $this->_sections['competence']['loop'];
$this->_sections['competence']['step'] = 1;
$this->_sections['competence']['start'] = $this->_sections['competence']['step'] > 0 ? 0 : $this->_sections['competence']['loop']-1;
if ($this->_sections['competence']['show']) {
    $this->_sections['competence']['total'] = $this->_sections['competence']['loop'];
    if ($this->_sections['competence']['total'] == 0)
        $this->_sections['competence']['show'] = false;
} else
    $this->_sections['competence']['total'] = 0;
if ($this->_sections['competence']['show']):

            for ($this->_sections['competence']['index'] = $this->_sections['competence']['start'], $this->_sections['competence']['iteration'] = 1;
                 $this->_sections['competence']['iteration'] <= $this->_sections['competence']['total'];
                 $this->_sections['competence']['index'] += $this->_sections['competence']['step'], $this->_sections['competence']['iteration']++):
$this->_sections['competence']['rownum'] = $this->_sections['competence']['iteration'];
$this->_sections['competence']['index_prev'] = $this->_sections['competence']['index'] - $this->_sections['competence']['step'];
$this->_sections['competence']['index_next'] = $this->_sections['competence']['index'] + $this->_sections['competence']['step'];
$this->_sections['competence']['first']      = ($this->_sections['competence']['iteration'] == 1);
$this->_sections['competence']['last']       = ($this->_sections['competence']['iteration'] == $this->_sections['competence']['total']);
?>details_<?php echo $this->_tpl_vars['zig']; ?>
_<?php echo $this->_tpl_vars['competence'][$this->_tpl_vars['zig']][$this->_sections['competence']['index']]['table']; ?>
,<?php endfor; endif; ?>';<?php endfor; endif; ?><?php endfor; endif; ?>
								</script>
								<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
									<tr class="header">
										<td>栏目名称</td>
										
										<td align="center">
										<input type="button" class="check" value="全选">
										</td>
										<td>权限设置</td>
									</tr>
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
									<tr>
										<td width="26%" class="altbg1">&nbsp;<strong>●</strong>&nbsp;<strong><?php echo $this->_tpl_vars['name'][$this->_tpl_vars['dig']]; ?>
</strong></td>
										<td width="3%" align="center" class="altbg1">
										<input id="menu_<?php echo $this->_tpl_vars['dig']; ?>
" name="competence_menu[]" class="idarray" type="checkbox" value="<?php echo $this->_tpl_vars['dig']; ?>
" <?php if (@ in_array ( $this->_tpl_vars['dig'] , $this->_tpl_vars['edit_menu_ip'] )): ?>checked<?php endif; ?>>
										</td>
										<td class="altbg2" width="71%">&nbsp;</td>
									</tr>
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
									<tr id="menu_<?php echo $this->_tpl_vars['dig']; ?>
">
										<td width="26%" class="altbg1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>○</strong>&nbsp;<?php echo $this->_tpl_vars['name'][$this->_tpl_vars['zig']]; ?>
</td>
										<td width="3%" align="center" class="altbg1">
										<input id="menu_<?php echo $this->_tpl_vars['zig']; ?>
" class="idarray" name="competence_menu[]" type="checkbox" value="<?php echo $this->_tpl_vars['zig']; ?>
" <?php if (@ in_array ( $this->_tpl_vars['zig'] , $this->_tpl_vars['edit_menu_ip'] )): ?>checked<?php elseif (@ in_array ( $this->_tpl_vars['dig'] , $this->_tpl_vars['edit_menu_ip'] )): ?> <?php else: ?><?php endif; ?>>
										</td>
										<td class="altbg2" width="71%">&nbsp;<?php unset($this->_sections['competence']);
$this->_sections['competence']['name'] = 'competence';
$this->_sections['competence']['loop'] = is_array($_loop=$this->_tpl_vars['competence'][$this->_tpl_vars['zig']]) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['competence']['show'] = true;
$this->_sections['competence']['max'] = $this->_sections['competence']['loop'];
$this->_sections['competence']['step'] = 1;
$this->_sections['competence']['start'] = $this->_sections['competence']['step'] > 0 ? 0 : $this->_sections['competence']['loop']-1;
if ($this->_sections['competence']['show']) {
    $this->_sections['competence']['total'] = $this->_sections['competence']['loop'];
    if ($this->_sections['competence']['total'] == 0)
        $this->_sections['competence']['show'] = false;
} else
    $this->_sections['competence']['total'] = 0;
if ($this->_sections['competence']['show']):

            for ($this->_sections['competence']['index'] = $this->_sections['competence']['start'], $this->_sections['competence']['iteration'] = 1;
                 $this->_sections['competence']['iteration'] <= $this->_sections['competence']['total'];
                 $this->_sections['competence']['index'] += $this->_sections['competence']['step'], $this->_sections['competence']['iteration']++):
$this->_sections['competence']['rownum'] = $this->_sections['competence']['iteration'];
$this->_sections['competence']['index_prev'] = $this->_sections['competence']['index'] - $this->_sections['competence']['step'];
$this->_sections['competence']['index_next'] = $this->_sections['competence']['index'] + $this->_sections['competence']['step'];
$this->_sections['competence']['first']      = ($this->_sections['competence']['iteration'] == 1);
$this->_sections['competence']['last']       = ($this->_sections['competence']['iteration'] == $this->_sections['competence']['total']);
?>
										<?php echo $this->_tpl_vars['competence'][$this->_tpl_vars['zig']][$this->_sections['competence']['index']]['name']; ?>

										<input id="menu_details_<?php echo $this->_tpl_vars['zig']; ?>
_<?php echo $this->_tpl_vars['competence'][$this->_tpl_vars['zig']][$this->_sections['competence']['index']]['table']; ?>
" name="competence_details[<?php echo $this->_tpl_vars['zig']; ?>
][]" type="checkbox" class="checkbox" value="<?php echo $this->_tpl_vars['competence'][$this->_tpl_vars['zig']][$this->_sections['competence']['index']]['table']; ?>
" 
										<?php if (@ in_array ( $this->_tpl_vars['competence'][$this->_tpl_vars['zig']][$this->_sections['competence']['index']]['table'] , $this->_tpl_vars['edit_details_ip'][$this->_tpl_vars['zig']] )): ?>
											checked<?php elseif (@ in_array ( $this->_tpl_vars['zig'] , $this->_tpl_vars['edit_menu_ip'] )): ?>
										<?php else: ?>
											disabled
										<?php endif; ?>>
										<?php endfor; endif; ?></td>
									</tr>

									<?php endfor; endif; ?>
									<?php endfor; endif; ?>
								</table>
								<br />
								<br>
								<center>
									<input class="button" type="submit" name="submit" value="保 存">
								</center>
							</form>
							<br />
							</td></tr></table>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>
</body>

</html>
<?php /* Smarty version 2.6.18, created on 2014-08-29 07:26:40
         compiled from otc_management.htm */ ?>
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
<body>
	<input id="frame_url" type="hidden" value="<?php echo $_SERVER['REQUEST_URI']; ?>
"/>
	<link href="image/w_stronglist.css" rel="stylesheet" type="text/css" />
	<script src="javascript/htcommon.js" type="text/javascript"></script>
	<style type="text/css">
		.catebutton {
			-moz-box-shadow: inset 50px 50px 50px 50px #ffffff;
			-webkit-box-shadow: inset 50px 50px 50px 50px #ffffff;
			box-shadow: inset 50px 50px 50px 50px #ffffff;
			background-color: #f9f9f9;
			-webkit-border-top-left-radius: 9px;
			-moz-border-radius-topleft: 9px;
			border-top-left-radius: 9px;
			-webkit-border-top-right-radius: 9px;
			-moz-border-radius-topright: 9px;
			border-top-right-radius: 9px;
			-webkit-border-bottom-right-radius: 9px;
			-moz-border-radius-bottomright: 9px;
			border-bottom-right-radius: 9px;
			-webkit-border-bottom-left-radius: 9px;
			-moz-border-radius-bottomleft: 9px;
			border-bottom-left-radius: 9px;
			text-indent: 0;
			border: 1px solid #dcdcdc;
			display: inline-block;
			color: #666666;
			font-family: Arial;
			font-size: 8px;
			font-weight: bold;
			font-style: normal;
			height: 22px;
			line-height: 22px;
			width: 68px;
			text-decoration: none;
			text-align: center;
			text-shadow: 1px 1px 0px #ffffff;
		}
		.catebutton:hover {
			background-color: #e9e9e9;
		}
		.catebutton:active {
			position: relative;
			top: 1px;
		}
	</style>
	<style type="text/css">
		ul, ol, li {
			margin: auto;
			padding: 0 15px;
			list-style-position: inside;
		}
	</style>

	<style>
		.PromotionName {
			display: inline-block;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			width: 340px;
		}
		.custom-name {
			position: relative;
			display: inline-block;
		}
		.custom-name-toggle {
			position: absolute;
			top: 0;
			bottom: 0;
			margin-left: -1px;
			padding: 0;
			/* support: IE7 */
			*height: 1.7em;
			*top: 0.1em;
		}
		.custom-name-input {
			margin: 0;
			padding: 0.3em;
			width: 300px;
		}

		.ui-autocomplete {
			max-height: 200px;
			overflow-y: auto;
		}
	</style>
	<script>
		(function($) {
			$.widget("custom.name", {
				_create : function() {
					this.wrapper = $("<span>").addClass("custom-name").insertAfter(this.element);

					this.element.hide();
					this._createAutocomplete();
					this._createShowAllButton();
				},

				_createAutocomplete : function() {
					var selected = this.element.children(":selected"), value = selected.val() ? selected.text() : "";

					this.input = $("<input>").appendTo(this.wrapper).val(value).attr("title", "").addClass("custom-name-input ui-widget ui-widget-content ui-state-default ui-corner-left").autocomplete({
						delay : 0,
						minLength : 0,
						source : $.proxy(this, "_source")
					}).tooltip({
						tooltipClass : "ui-state-highlight"
					});

					this._on(this.input, {
						autocompleteselect : function(event, ui) {
							ui.item.option.selected = true;
							this._trigger("select", event, {
								item : ui.item.option
							});
						},

						autocompletechange : "_removeIfInvalid"
					});
				},

				_createShowAllButton : function() {
					var input = this.input, wasOpen = false;

					$("<a>").attr("tabIndex", -1).attr("title", "显示全部选项").tooltip().appendTo(this.wrapper).button({
						icons : {
							primary : "ui-icon-triangle-1-s"
						},
						text : false
					}).removeClass("ui-corner-all").addClass("custom-name-toggle ui-corner-right").mousedown(function() {
						wasOpen = input.autocomplete("widget").is(":visible");
					}).click(function() {
						input.focus();

						// Close if already visible
						if (wasOpen) {
							return;
						}

						// Pass empty string as value to search for, displaying all results
						input.autocomplete("search", "");
					});
				},

				_source : function(request, response) {
					var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
					response(this.element.children("option").map(function() {
						var text = $(this).text();
						if (this.value && (!request.term || matcher.test(text) ))
							return {
								label : text,
								value : text,
								option : this
							};
					}));
				},

				_removeIfInvalid : function(event, ui) {

					// Selected an item, nothing to do
					if (ui.item) {
						return;
					}

					// Search for a match (case-insensitive)
					var value = this.input.val(), valueLowerCase = value.toLowerCase(), valid = false;
					this.element.children("option").each(function() {
						if ($(this).text().toLowerCase() === valueLowerCase) {
							this.selected = valid = true;
							return false;
						}
					});

					// Found a match, nothing to do
					if (valid) {
						return;
					}

					// Remove invalid value
					this.input.val("").attr("title", value + " 没有找到匹配的选项默认上次的结果").tooltip("open");
					this.element.val("");
					this._delay(function() {
						this.input.tooltip("close").attr("title", "");
					}, 2500);
					this.input.data("ui-autocomplete").term = "";
				},

				_destroy : function() {
					this.wrapper.remove();
					this.element.show();
				}
			});
		})(jQuery);
	</script>

	<script>
		(function($) {
			$(function() {
				$("#categoryId").name();
				$(".categorylist").name();
				$('.custom-name-input').on("click", function() {
					$old_value = $(this).val();
					$(this).val("");
				});
				$(".custom-name-input").on("blur", function() {
					if ($(this).val() == '') {
						$(this).val($old_value);
					}
				});
			});
		})(jQuery);
	</script>

	<div id="container">
		<div id="wrapper">
			<div id="content">
				<!-- <div class="mini-layout">
				<div><a href="?module_id=<?php echo $this->_tpl_vars['module_id']; ?>
&menu_switch=delzero" onclick="return confirm('是否确定执行此操作？')">自动设置所属分类</a>（根据外链分类规则）&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
				</div> -->
				<div class="mini-layout">
					<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
						<tr class="header">
							<!-- <td colspan="4"><div style="float:left; margin-left:0px; padding-top:8px"></div><div style="float:right; margin-right:4px; padding-bottom:9px"></div></td> -->
						</tr>
						<tbody id="menu_userso" style="display:<?php if (in_array ( 'userso' , $this->_tpl_vars['menu_cookie'] )): ?>none<?php endif; ?>">
							<form method="post" action="index.php?module=statistics&action=Otc" name="form">
								<tr>
									<td width="16%" class="altbg1"><B>外链标记：</B>
										<select id="search_type" name="search_type"> 
							  				<option <?php if ($this->_tpl_vars['search_type'] == 'wildcards'): ?>selected<?php endif; ?> value="wildcards">通配符</option>
							  				<option <?php if ($this->_tpl_vars['search_type'] == 'regex'): ?>selected<?php endif; ?> value="regex">正则表达式</option>search_type
										</select>
									</td>
									<td width="34%" class="altbg2">
										<input type="text" name="PromotionName" value="<?php echo $this->_tpl_vars['PromotionName']; ?>
" />
									</td>
									<td width="16%" class="altbg1"><B>所属分类：</B></td>
									<td width="34%" class="altbg2"><span class="select_div">
										<select id="categoryId" name="categoryId" style="width:234px;color: #006699;">
											<option value="0">不筛选分类</option>
											<?php echo $this->_tpl_vars['class']; ?>

										</select> </span></td>
								</tr>

								<tr>
									<td width="16%" class="altbg1"><B>商品分类：</B></td>
									<td width="34%" class="altbg2">
										<div class="jt_bar" style="margin-top:5px;margin-left: -102px;float:left;clear:left;">
											<input type="hidden" name="filter_categoryId" id="filter_categoryId" value="<?php echo $this->_tpl_vars['filter_category']; ?>
">
											<div style="margin-top:-3px;margin-left:5px;float:left;" >
												<p style="margin-top:3px;margin-left:5px;float:left;">包括子分类 </p>
											</div>
											<div style="margin-top:5px;float:left;" >
												<input type="checkbox" name="addition" <?php if ($this->_tpl_vars['addition']): ?>checked="checked"<?php endif; ?> value="1">
											</div>
										</div>	
									</td>
									
									<td width="16%" class="altbg1"><B>只显示未绑定分类：</B></td>
									<td width="34%" class="altbg2">
										<div class="select_div" style="margin-top:5px;">
											<input type="checkbox" name="is_show_c" <?php if ($this->_tpl_vars['is_show_c']): ?>checked="checked"<?php endif; ?>>
										</div>	
									</td>
									
								<tr>
									<td width="16%" class="altbg1"><B>只显示未绑定商品分类：</B></td>
									<td width="34%" class="altbg2">
										<div class="select_div" style="margin-top:5px;">
											<input type="checkbox" name="is_show_cid" <?php if ($this->_tpl_vars['is_show_cid']): ?>checked="checked"<?php endif; ?>>
										</div>	
									</td>	
									
									<td width="16%" class="altbg1"><B>只显示带来销量的外链标：</B></td>
									<td width="34%" class="altbg2">
										<div class="select_div" style="margin-top:5px;">
											<input type="checkbox" name="is_sales" <?php if ($this->_tpl_vars['is_sales']): ?>checked="checked"<?php endif; ?>>
										</div>	
									</td>									
								</tr>
								<tr> 
									<td colspan="4">
									<div align="center">
										<input class="button" type="submit" name="submit" value="查 询" >
										&nbsp;
									</div></td>
								</tr>
							</form>
						</tbody>
					</table>
				</div>
				<div class="mini-layout">

					<?php if ($this->_tpl_vars['promotionurl_all']): ?>
					<div class="pagination">
						<?php echo $this->_tpl_vars['page']; ?>

					</div>
					<div class="mini-layout">
						<form method="post" action="" name="form">
							<input type="hidden" name="menu_action" value="binding">
							<input type="hidden" name="PromotionName" value="<?php echo $this->_tpl_vars['PromotionName']; ?>
" />
							<input type="hidden" name="url_jump" value="<?php echo $this->_tpl_vars['reload']; ?>
&page=<?php echo $this->_tpl_vars['c_page']; ?>
" />
							<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder" style="table-layout: fixed">
								<tr align="center" class="header">
									<th width="380px" >外链标记名字</th>
									<th width="380px" >所属分类</th>
									<th width="280px" >商品分类</th>
								</tr>
								<tr align="center" class="header">
									<?php unset($this->_sections['promotionurl_all']);
$this->_sections['promotionurl_all']['name'] = 'promotionurl_all';
$this->_sections['promotionurl_all']['loop'] = is_array($_loop=$this->_tpl_vars['promotionurl_all']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['promotionurl_all']['show'] = true;
$this->_sections['promotionurl_all']['max'] = $this->_sections['promotionurl_all']['loop'];
$this->_sections['promotionurl_all']['step'] = 1;
$this->_sections['promotionurl_all']['start'] = $this->_sections['promotionurl_all']['step'] > 0 ? 0 : $this->_sections['promotionurl_all']['loop']-1;
if ($this->_sections['promotionurl_all']['show']) {
    $this->_sections['promotionurl_all']['total'] = $this->_sections['promotionurl_all']['loop'];
    if ($this->_sections['promotionurl_all']['total'] == 0)
        $this->_sections['promotionurl_all']['show'] = false;
} else
    $this->_sections['promotionurl_all']['total'] = 0;
if ($this->_sections['promotionurl_all']['show']):

            for ($this->_sections['promotionurl_all']['index'] = $this->_sections['promotionurl_all']['start'], $this->_sections['promotionurl_all']['iteration'] = 1;
                 $this->_sections['promotionurl_all']['iteration'] <= $this->_sections['promotionurl_all']['total'];
                 $this->_sections['promotionurl_all']['index'] += $this->_sections['promotionurl_all']['step'], $this->_sections['promotionurl_all']['iteration']++):
$this->_sections['promotionurl_all']['rownum'] = $this->_sections['promotionurl_all']['iteration'];
$this->_sections['promotionurl_all']['index_prev'] = $this->_sections['promotionurl_all']['index'] - $this->_sections['promotionurl_all']['step'];
$this->_sections['promotionurl_all']['index_next'] = $this->_sections['promotionurl_all']['index'] + $this->_sections['promotionurl_all']['step'];
$this->_sections['promotionurl_all']['first']      = ($this->_sections['promotionurl_all']['iteration'] == 1);
$this->_sections['promotionurl_all']['last']       = ($this->_sections['promotionurl_all']['iteration'] == $this->_sections['promotionurl_all']['total']);
?>
									<td class="altbg2" align="center" ><span title="<?php echo $this->_tpl_vars['promotionurl_all'][$this->_sections['promotionurl_all']['index']]['PromotionName']; ?>
" class="PromotionName"><?php echo $this->_tpl_vars['promotionurl_all'][$this->_sections['promotionurl_all']['index']]['PromotionName']; ?>
</span></td>
									<td class="altbg2" align="center">
									<select class="categorylist" name="category[<?php echo $this->_tpl_vars['promotionurl_all'][$this->_sections['promotionurl_all']['index']]['id']; ?>
]">
										<option value="0">没有属分类</option>
										<?php echo $this->_tpl_vars['promotionurl_all'][$this->_sections['promotionurl_all']['index']]['class_all']; ?>

									</select></td>
									<td>
									<div class="jt_bar" style="margin-top:5px;margin-left:35px;float:left;clear:left;">
										<input type="hidden" id="Categories_id_<?php echo $this->_tpl_vars['promotionurl_all'][$this->_sections['promotionurl_all']['index']]['id']; ?>
" name="Categories_id[<?php echo $this->_tpl_vars['promotionurl_all'][$this->_sections['promotionurl_all']['index']]['id']; ?>
]" value="<?php echo $this->_tpl_vars['promotionurl_all'][$this->_sections['promotionurl_all']['index']]['category_id']; ?>
">
									</div><td>
								</tr>
								<?php endfor; endif; ?>
								<tr class="smalltxt">
									<td align="center" class="altbg1" ><?php echo $this->_tpl_vars['zl']['Click']; ?>
</td>
									<td align="center" class="altbg1" ><?php echo $this->_tpl_vars['zl']['member']; ?>
</td>
									<td align="center" class="altbg1" ><?php echo $this->_tpl_vars['zl']['mail']; ?>
</td>
									<td class="altbg2" align="center" ><a href="?module_id=73&up_promotion_id=<?php echo $this->_tpl_vars['categoryId']; ?>
<?php if ($this->_tpl_vars['so_array']['endtime'] || $this->_tpl_vars['so_array']['starttime']): ?>&starttime=<?php echo $this->_tpl_vars['so_array']['starttime']; ?>
&endtime=<?php echo $this->_tpl_vars['so_array']['endtime']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['PromotionName']): ?>&PromotionName=<?php echo $this->_tpl_vars['PromotionName']; ?>
<?php endif; ?>"><?php echo $this->_tpl_vars['zl']['Orders']; ?>
<?php if ($this->_tpl_vars['zl']['Orders2'] != "" && $this->_tpl_vars['zl']['Orders2'] != 0): ?> [<?php echo $this->_tpl_vars['zl']['Orders2']; ?>
]<?php endif; ?></a></td>
									<?php if ($this->_tpl_vars['so_array']['endtime'] != "" || $this->_tpl_vars['so_array']['starttime'] != ""): ?>
									<td align="center" class="altbg1"><?php echo $this->_tpl_vars['zl']['atm1']; ?>
</td>
									<td class="altbg2" align="center"><?php echo $this->_tpl_vars['zl']['atm2']; ?>
</td>
									<?php endif; ?>
									<td align="center" class="altbg1"><?php if ($this->_tpl_vars['so_array']['endtime'] != "" || $this->_tpl_vars['so_array']['starttime'] != ""): ?><?php echo $this->_tpl_vars['zl']['atm1']+$this->_tpl_vars['zl']['atm2']; ?>
<?php endif; ?></td>
								</tr>
							</table>

							<?php endif; ?>
							<div style="margin:10px;" align="center">
								<input class="button" type="submit" name="submit" value="绑定分类" onclick="javascript: return confirm('确定要绑定当前分类吗？'); ">
							</div>

						</form>
					</div>
					<div class="pagination">
						<?php echo $this->_tpl_vars['page']; ?>

					</div>
				</div>
			</div>
		</div>
		
		<script>
			(function($) {
				$('#jump').on('keyup', function() {
   				 var jump = this.value;
   				 var jumptag = $("#jumptag").attr("href");
   				 var url=jumptag.replace(/&page=\d{0,}/,'&page='+jump);
   				 $("#jumptag").attr("href", url);
				});
			})(jQuery);
		</script>
		
		<script>
			(function($) {
				jq(document).ready(function() {
					jq("#WebsiteId").change(function() {
						var websiteId = jq("#WebsiteId").val();
						var url = get_current_url();
						var module_id = '<?php echo $this->_tpl_vars['module_id']; ?>
';
						var ajax_url = url + "?module_id=" + module_id + "&WebsiteId=" + websiteId + "&menu_action=add";
						window.location.href = ajax_url;
					});
				});

				function get_current_url() {
					var ajax_url = "http://" + window.location.hostname + window.location.pathname;
					return ajax_url;
				}

			})(jQuery);
		</script>

		<script>
			(function($) {
				
				$('#filter_categoryId').w_stronglist({key:'categories_zh-cn',nodeClick:addtypevalue,treewidth:222,WebsiteId:1});
				
				<?php unset($this->_sections['promotionurl_all']);
$this->_sections['promotionurl_all']['name'] = 'promotionurl_all';
$this->_sections['promotionurl_all']['loop'] = is_array($_loop=$this->_tpl_vars['promotionurl_all']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['promotionurl_all']['show'] = true;
$this->_sections['promotionurl_all']['max'] = $this->_sections['promotionurl_all']['loop'];
$this->_sections['promotionurl_all']['step'] = 1;
$this->_sections['promotionurl_all']['start'] = $this->_sections['promotionurl_all']['step'] > 0 ? 0 : $this->_sections['promotionurl_all']['loop']-1;
if ($this->_sections['promotionurl_all']['show']) {
    $this->_sections['promotionurl_all']['total'] = $this->_sections['promotionurl_all']['loop'];
    if ($this->_sections['promotionurl_all']['total'] == 0)
        $this->_sections['promotionurl_all']['show'] = false;
} else
    $this->_sections['promotionurl_all']['total'] = 0;
if ($this->_sections['promotionurl_all']['show']):

            for ($this->_sections['promotionurl_all']['index'] = $this->_sections['promotionurl_all']['start'], $this->_sections['promotionurl_all']['iteration'] = 1;
                 $this->_sections['promotionurl_all']['iteration'] <= $this->_sections['promotionurl_all']['total'];
                 $this->_sections['promotionurl_all']['index'] += $this->_sections['promotionurl_all']['step'], $this->_sections['promotionurl_all']['iteration']++):
$this->_sections['promotionurl_all']['rownum'] = $this->_sections['promotionurl_all']['iteration'];
$this->_sections['promotionurl_all']['index_prev'] = $this->_sections['promotionurl_all']['index'] - $this->_sections['promotionurl_all']['step'];
$this->_sections['promotionurl_all']['index_next'] = $this->_sections['promotionurl_all']['index'] + $this->_sections['promotionurl_all']['step'];
$this->_sections['promotionurl_all']['first']      = ($this->_sections['promotionurl_all']['iteration'] == 1);
$this->_sections['promotionurl_all']['last']       = ($this->_sections['promotionurl_all']['iteration'] == $this->_sections['promotionurl_all']['total']);
?>
					$('#Categories_id_<?php echo $this->_tpl_vars['promotionurl_all'][$this->_sections['promotionurl_all']['index']]['id']; ?>
').w_stronglist({key:'categories_zh-cn',nodeClick:addtypevalue,treewidth:222,WebsiteId:1});
				<?php endfor; endif; ?>
			})(jQuery);
		</script>
		

</body>
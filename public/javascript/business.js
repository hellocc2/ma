		
			function getFnStrByIdStr(s){
				var array=s.split('-');
				$.each(array, function(i, item){
					array[i]=$.upFirstLetter(item);
				});
				return ''+array.join('');
			}

			function preRenderDialogs(){
				window.DIALOG={};
				window.DIALOG_RENDER={};
				var digs=$('div[id$="-dialog"]');
				$.each(digs,function(i, digid){
					$(digid).hide();
					var id=''+$(digid).attr('id');
					var dig=$(digid);
					$(dig).attr('render','false');
					DIALOG[id]=dig;
					var renderStr='render'+getFnStrByIdStr(id);
					DIALOG_RENDER[id]=window[renderStr];
				});
			}
	
 			function orderItemFormatter(index,row,columns){
				var s='';
				var itemList=row['orderItemList'];
				var cls=columns;
				
				//返回空字符的情况
				if(!itemList  || itemList.length<1  || !cls || cls.length<1){
					return s;
				}
				
					//表头
					s='<table class="order-item"><thead><tr>';
					$.each(cls,function(i,cl){
						if(!cl.title){cl.title=cl.field;}
						if(cl.width){
							s+='<td width="'+cl.width+'" field="'+cl.field+'">'+cl.title+'</td>';
						}else{
							s+='<td>'+cl.title+'</td>';
						}
					});
					s+='</tr></thead><tbody>';
					
					var is='';
					//表身
					$.each(itemList, function(i,item){
						
						is+='<tr item-id="'+item.id+'">';
						$.each(cls,function(i,cl){
							if(cl.field=='action'){
								var s='';
								$.each(cl.actions, function(i,action){
									s+= ('<a href="#" onclick="'+action.handler+'('+item.id+')">['+action.text+']</a> ');
								});
								item[cl.field]=s;
							}else if(item[cl.field]==null){
								item[cl.field]='';
							}else if(cl.mapper!=null){
								if(SystemData[cl.mapper]!=null){
									item[cl.field]=$(SystemData[cl.mapper]).getNameById(item[cl.field]);//每次映射，费事呀
								}else{
									cl.mapper=null;//如果找不到系统数据，干脆下次就不要映射了,直接显示id
								}
							}//有映射的情况
							is+=('<td field="'+cl.field+'">'+item[cl.field]+'</td>');
						});
						is+='</tr>';
						s+=is;
						is = '';
					});	
					
					s+='</tbody></table>';
					return s;
			}
	
			function getOrderGrid(dg){
				
				//列处理，如果有overrideFields,就用overrideFields, 默认就直接用下面的。
				var fields=[];
				if(dg.overrideFields!=null){
					fields=dg.overrideFields;
				}else{
				
				
					
					//fields处理，分五部分，一是checkField，二是actionField 三是prependFields，四是fields，五是appendFields.
					var checkField=[{field:'checked',width:60,checkbox:true}];
					var actionField=[];
					if(dg.actions!=null){
						var w=130;
						if(dg.actions.width){
							w=dg.actions.width;
						}
						actionField=
							{field:'action',title:'操作',align:'center',width:w,
									formatter:function(value,row,index){
									var s='';
									$.each(dg.actions,function(i, action){
										var handler=''+action.handler;
										s+= ('<a href="#" id="'+action.id+'" onclick="'+handler+'('+row.id+')">['+action.text+']</a> ');
									});
									s+='<a href="#" onclick="handleOpenViewOrderDetailPage('+row.id+')">[订单详情]</a>';
									return s;
								}
							}
					}else{
						actionField=dg.actionField;
					}
					
					var prependFields=[];
					if(dg.prependFields!=null){
						prependFields=dg.prependFields;
					}
					fields=[
								{field:'id',title:'流水号',width:30,sortable:true,filter:true,hidden:true},
								{field:'merchantOrderId',title:'订单号',width:90,sortable:true,filter:true},
								{field:'email',title:'邮箱',width:90,filter:true},
								{field:'owner',title:'店铺',width:50,filter:true,hover:false},
								{field:'paymentReceiveTime',title:'付款时间',width:80,sortable:true},
								{field:'currencyCode', title:'币种', width:30, align:'center',hover:false,filter:true},
								{field:'totalAmount',title:'金额',width:40,align:'center',hover:false},
								{field:'customerName', title:'客户姓名', width:50,filter:true},
								{field:'customerId', title:'客户Id', width:50,filter:true},
								{field:'deliveryCountry', title:'发货国家', width:90,filter:true},
								{field:'itemStr', title:'产品', width:80},
							];
					var appendFields=[];
					if(dg.appendFields!=null){
						appendFields=dg.appendFields;
					}
					
					
					//把上面所有的列拼起来，结果放在fields里面
					fields=checkField.concat(actionField, prependFields, fields, appendFields );
				}
				
				
				var ops={
				  	width:'auto',
				  	height:'auto',
				  	pagination:true,
				  	pageSize:'20',
				  	queryParams:{},
				  	//pageList:[1,2,3,4,5],
				  	sortName:'id',
				  	sortOrder:'desc',
				  	url:SystemURL['order'],
				  	toolbar:[],
				  	onDblClickRow:function(index,row){
						 $(this).datagrid('expandRow', index);
						 $(this).datagrid('fixRowHeight',index);
			 		},
			 		onLoadSuccess:function(){
				  		dg.datagrid('addCellHoverBar');
				  	},
					columns:[fields],
			    	view:detailview,
			    	detailFormatter:function(index,row){
			 			var columns=[];
			 			//订单行已经有了dg.detailFields，就直接用，如果没有，才用下面的,【可以外加】。
			 			if(dg.detailFields!=null){
			 				columns=dg.detailFields;
			 			}else{
			 				columns=[{title:'sku', field:'sku',width:120},
									{title:'别名', field:'skuAlias',width:50},
									{title:'itemId', field:'merchantProductId',width:100},
									{title:'状态', field:'state',width:50},
									{title:'数量', field:'quantity',width:40},
									{title:'单价', field:'price',width:40},
									{title:'总价', field:'amount',width:40},
									{title:'名称', field:'sellName'}
									//{title:'仓库', field:'inventoryId',width:80,mapper:'warehouse'}
									];
			 			}
			 			//订单行操作处理
			 			if(dg.subItemActions!=null){
			 				var w=80;
							if(dg.subItemActions.width!=null){
								w=dg.subItemActions.width;
							}
			 				var a={field:'action',title:'操作',align:'center',width:w,actions:dg.subItemActions};
			 				columns.push(a);
			 			}
			 			//生成最后的订单行显示
						return orderItemFormatter(index,row,columns);
					}
			    };
				
				//订单行的函数处理
				if(dg.detailFormatter!=null){
					ops.detailFormatter=dg.detailFormatter;
				}
				
				
				
				//dg的toolbar处理
				if(dg.toolbar!=null){
					ops.toolbar=dg.toolbar;
				}
				
			   
			   //参数处理
			    if(dg.state!=null){
			    	$.extend(ops.queryParams,dg.state);
			    }
			    
			    
			    
			    //dg render
			   dg.datagrid(ops);
			   
			   
			   
			   //render之后的处理
			   dg.datagrid('prependExpander');
			   dg.datagrid('addColumnSearch');
			   return dg;
				
			}
			
			function renderPageToolbar(dg, id, param) {
				var page=$(id/*'#page-toolbar'*/);
				//绑定事件
				$(page).find('input#search').click(_searchHandler);
				$(page).find('input').keydown(function(event) {
					if (event.which==13){_searchHandler();}
				});
				$(page).find(' input#reset').click(_resetHandler);


				
				//toolbar展开关闭处理
				$(page).find(' input#toolbar-expand').click(function(){
					  $(page).animate({"height": "+=160px"}, "fast",function(){
						  $(page).find(' input#toolbar-expand').hide();
						  $(page).find(' input#toolbar-collapse').show();
						  });
				});
				$(page).find(' input#toolbar-collapse').click(function(){
					  $(page).animate({"height": "-=160px"}, "fast",function(){
						  $(page).find(' input#toolbar-collapse').hide();
						  $(page).find(' input#toolbar-expand').show();
						  });
				});
				
				
				//特殊input render处理
				$(page).find('form').form('renderCombobox');
				$(page).find('input.date').dateinput({
					format: 'yyyy-mm-dd'	// the format displayed for the user
				});
				


				function _searchHandler(){
					if(param&&param.onBeforeSearch){
						param.onBeforeSearch();
					}
					//收集数据，column search的数据也要拿来
					var queryParams = dg.datagrid('options').queryParams;
					
					var arrayB=$(dg).datagrid('getPanel').find('table tr#search-bar-row  input').toArray();
					var arrayA=$(page).find('input[searchkey="true"]').toArray();
					var searchInputs=arrayA.concat(arrayB);
					
					$(searchInputs).each(function () {
				    if ($(this).val() == null || $(this).val() == ''){
				    	delete queryParams[$(this).attr('name')];
				    } else {
					   queryParams[$(this).attr('name')] = $(this).val(); 
				    }
					});
					
					dg.datagrid('options').queryParams = queryParams;
					
					dg.datagrid('reload');
					//dg.datagrid('load');
				}
				
				function _resetHandler(){
					//清空pagetoolbar搜索项
					$(page).find(' input[type="text"]').val('');
					//清空列搜索
					$($(dg).datagrid('getPanel').find('table tr#search-bar-row  input')).val('');
				}
				
			}



////////////////////////////////////////////////////////////////////////////
			function handleOpenViewOrderDetailPage(id){
				//$.messager.alert('查看订单详情','id是'+id);
				if(!id){
					alert('订单id有误');
					return false;
				}
				window.open('/supplierback/page/orderDetail.jsp?id='+id);
			}
			
			

			//只能处理一层的，
			function getNVPString(array){
				var s='{';
				var lastIndex=array.length-1;
				$.each(array, function(index, o){
					if(o.valueIsArray){
						s+='"'+o.name+'":'+o.value+'';
					}else{
						s+='"'+o.name+'":"'+o.value+'"';
					}
					if(index!=lastIndex){s+=',';}
				});
				s+='}';
				return s;
			}

			function getFormArray(formDomCollection){
				var formArray=new Array();
				var n;
				var v;

				$.each(formDomCollection, function(index, o){
					n=$(o).attr('name');
					v=$(o).val();
					formArray.push({name:n,value:v});	
				});

				return formArray;
			}


			///////生成JSON格式的string, 生成name:value格式的字符串，后面参数为最后插入的字符串
			function getJSONString(array, insertedItemString){
				var s='{';
				$.each(array, function(index, o){
					s+='"'+o.name+'":"'+o.value+'",'
				});
				s+=insertedItemString;
				s+='}';
				return s;
			}
			///////生成JSON格式的string, 生成name:value格式的字符串，后面参数为最后插入的字符串
			//注意easyui的表单对象收集不到
			//一般用$('form#id .collect')
			function getFormJSONString(formDomCollection, insertedItemString){
				var s='{';
				$.each(formDomCollection, function(index, o){
					s+='"'+$(o).attr('name')+'":"'+$(o).val()+'",'
				});
				s+=insertedItemString;
				s+='}';
				return s;
			}












			function getOrderRequestDatagrid(dg){
				 dg.datagrid({
					  	width:'auto',
					  	height:'auto',
					  	pagination:'true',
					  	pageSize:'20',
					  	sortName:'id',
					  	sortOrder:'desc',
						columns:[[
									{field:'checked',width:60,checkbox:true},
									{field:'id',title:'流水号',width:30,hidden:true},
									{field:'merchantOrderId',title:'订单号',width:80},
									{field:'merchantName',title:'商户名称',width:50},
									{field:'customerId',title:'顾客Id',width:60},
									{field:'currencyCode',title:'币种',width:40},
									{field:'receiveAmount',title:'实收金额',width:55},
									{field:'calculateAmount',title:'建议金额',width:55},
									{field:'realAmount',title:'退款金额',width:55},
									{field:'customerName',title:'客户姓名',width:60},
									{field:'orderType', title:'类型', width:50, 
										formatter:function(value, row, index){
											if(value==1){return '原始订单';}else
												if(value==2){return '补发订单';}
										}
									},
									{field:'creator',title:'创建人',width:60},
									{field:'createTime',title:'创建时间',width:80}
								]],
				    	view: detailview,
						detailFormatter: function(rowIndex, rowData){
							var itemList=rowData.requestItemList;
							if(itemList.length!=0){
								var output='<table class="order-item">'+
								'<thead><tr>'+
								'<td width="100">sku</td>'+
								'<td width="80">别名</td>'+
								'<td width="80">jsin</td>'+
								'<td width="80">状态</td>'+
								'<td width="30">数量</td>'+
								'<td width="40">单价</td>'+
								'<td width="60">产品总价</td>'+
								'<td width="60">退款金额</td>'+
								'<td width="120">产品名称</td>'+
								'<td width="100">属性</td>'+
								'</tr></thead>';
								
								$.each(itemList, function(itemIndex, item) { 
									var temp=
									'<tbody><tr>'+
									'<td>'+item.sku+'</td>'+
									'<td>'+item.skuAlias+'</td>'+
									'<td>'+item.jsin+'</td>'+
									'<td>'+item.state+'</td>'+
									'<td>'+item.productQuantity+'</td>'+
									'<td>'+item.productPrice+'</td>'+
									'<td>'+item.productAmount+'</td>'+
									'<td>'+item.requestAmount+'</td>'+
									'<td>'+item.productSellName+'</td>'+
									'<td>'+item.productAttribute+'</td>'+
									'</tr></tbody>';
									output+=temp;
									
									});		
								output+='</table>';
								return output;
							}else{
								return null;
							}
						}	
				    });	
				 return dg;
			}




			
			function commonDialogOpenHandler(dlg, dg){
				$(dlg).find('form input').val('');
				$(dlg).find('from textarea').val('');
				
				switch(dlg.dialogType)
				   {
				   case 'edit':
						//加载数据
						var row=dg.datagrid('getRowById',dlg.id);
						row['supplierId'] = pageParams['supplierId'];
						var dlgForm=$(dlg).find('form');
						$(dlgForm).form('qiangdaload',row);
						$(dlgForm).attr('postidvalue', row.id);
						$(dlg).dialog('setTitle', dlg.title);
				     break;
				   case 'new':
						$(dlg).dialog('setTitle', dlg.title);
						$(dlg).find('input[type="text"]').val('');
						$(dlg).find('textarea').val('');
						var row={};
						row['supplierId']=pageParams['supplierId'];
						var dlgForm=$(dlg).find('form');
						$(dlgForm).form('load',row);
						$(dlgForm).attr('postidvalue','');
				     break;
				   default:
				     alert('dialog error');
				   }
			}



			function getProcureOrderItemList(row, operation){
				var itemList=row.procureOrderItem;
				var output = null;
				if(itemList.length!=0){
					output='<table class="order-item">'+
					'<thead><tr>'+
					'<td width="70">sku</td>'+
					'<td width="90">产品名称</td>'+
					'<td width="80">属性</td>'+
					'<td width="80">状态</td>'+
					'<td width="40">数量</td>'+
					'<td width="40">单价</td>'+
					'<td width="40">总价</td>';
					if(operation){
						output+=operation.title;
					}
					output+= '</tr></thead>';
					
					$.each(itemList, function(itemIndex, item) { 
						var temp=
						'<tbody><tr>'+
						'<td>'+item.sku+'</td>'+
						'<td>'+item.productName+'</td>'+
						'<td>'+item.attribute+'</td>'+
						'<td>'+PROCURE_ORDER_ITEM_STATE[item.state]+'</td>'+
						'<td>'+item.quantity+'</td>'+
						'<td>'+item.price+'</td>'+
						'<td>'+item.amount+'</td>';
						if(operation){
							temp+='<td><a href="#" onclick="handleEditItem('+item.id+')">[编辑]</a> '+
					   			'<a href="#" onclick="handleRejectItem('+item.id+')">[取消]</a></td>';
						}
						temp+='</tr></tbody>';
						output+=temp;
						});		
					output+='</table>';
			}
			return output;
		}











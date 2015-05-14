
function renderPageToolbar(dg){

	//获取searchbar
	if(!dg){
		alert('datagrid not found');
		return;
	}
	var searchbar=dg.attr('searchbar');
	//var jqSearchbar=$('#'+searchbar);
	var searchForm=$('#'+searchbar+' form.search-form');
	
	$(searchForm).find('input#expandAll').click(_expandAllHandler);
	$(searchForm).find('input#collapseAll').click(_collapseAllHandler);
	$(searchForm).find('input#batchAction').click(_batchHandler);
	
	$(searchForm).find('input#search').click(_searchHandler);


	
	$(searchForm).find('input').keydown(function(event) {
		if (event.which==13){_searchHandler();}
	});
	$(searchForm).find(' input#reset').click(_resetHandler);
	$(searchForm).find(' input#expandAll').show();
	$(searchForm).find(' input#collapseAll').hide();
	/*
	var merchantFlag=$(searchForm).find('input[name="merchantId"]').length;
	if(merchantFlag>0){
		renderMerchantCombo();
	}
	*/
	$(searchForm).form('renderCombobox');
	
	//toolbar expand
	var pageToolbar=$('#page-toolbar');
	$(pageToolbar).find(' input#toolbar-expand').click(function(){
		  $(pageToolbar).animate({"height": "+=160px"}, "fast",function(){
			  $(pageToolbar).find(' input#toolbar-expand').hide();
			  $(pageToolbar).find(' input#toolbar-collapse').show();
			  });
	});


	
	$(pageToolbar).find(' input#toolbar-collapse').click(function(){
		  $(pageToolbar).animate({"height": "-=160px"}, "fast",function(){
			  $(pageToolbar).find(' input#toolbar-collapse').hide();
			  $(pageToolbar).find(' input#toolbar-expand').show();
			  });
	});
	
	//renderMerchantCombo();
	
	
	function _batchHandler(){
		alert('批量操作，待指定');  //[fix]
	}
	
	
	function _expandAllHandler(){
		dg.datagrid('expandAllRows');
		$(searchForm).find(' input#expandAll').hide();
		$(searchForm).find(' input#collapseAll').show();
	}
	
	function _collapseAllHandler(){
		dg.datagrid('collapseAllRows');
		$(searchForm).find(' input#expandAll').show();
		$(searchForm).find(' input#collapseAll').hide();
	}
	
	
	function _searchHandler(){
		var queryParams = dg.datagrid('options').queryParams;
		if(!queryParams){
			queryParams={};
		}
		$(searchForm).find('input[type="text"][searchkey="true"]').each(function () {
				   if ($(this).val() == null || $(this).val() == ''){
					   delete queryParams[this.name];
				   } else {
				    	queryParams[this.name] = $(this).val(); 
				   }
				});
		dg.datagrid('options').queryParams=queryParams;
		dg.datagrid('reload');
	}
	
	
	function _resetHandler(){
		$(searchForm).find(' input[type="text"]').val('');
	}
	
	
	function renderMerchantCombo(){
		$.post('/jporder/page/dict_getMerchants.do',function(data){
			$(searchForm).find('input[name="merchantId"]').combobox({
				data:data,
				valueField:'id',
				textField:'name',
				onSelect:function(record){
					$(this).val(record.id);
				}
			});
		});
	}
	
}


/*
 * 2012-03-18
 * 简单交互对话框，
 * 待继承对象(optional)：handleDialogClose, handleDialogOpen, sf,都放在参数param里面, 
 * form写在html里
 * 表单的id处理在form posturl, postidvalue, postidname里面
 * 提交用jpSubmit,发送的是json data,标准form
 * title没处理，open时要处理
 */



function getSimpleDialog(dig){
		
		var param=dig.handler;
		//dialog render
		dig.dialog({
					title:dig.title,
					width:'auto',
					height:'auto',
					closed:true,
					modal:true,
					buttons:[
					         {plain:false, text:'确认', id:'commit', handler:_handleDialogCommit},
					         {plain:false, text:'取消', id:'cancel', handler:_handleDialogCancel}
					],
					onOpen:function(){
						_handleDialogOpen();
					},
					onClose:function(){
						_handleDialogClose();
					}
		     });
			//dialogopen
			function _handleDialogOpen(){
				$(dig).find('form').attr('postidvalue',dig.id);
				if(param.handleDialogOpen){
					param.handleDialogOpen();
				}
			}
			//dialogclose
			function _handleDialogClose(){
				$(dig).find('textarea').val('');
				$(dig).find('input[type="text"]').val('');
				dig.id=null;
				if(param.handleDialogClose){
					param.handleDialogClose();
				}
			}

			
		 //dialogcommit
		     function _handleDialogCommit(){
				$(dig).find('form').form('jpSubmit',{
					sf:function(result){
						alert(result.msg);
						if(result.success){
							$(dig).dialog('close');
							if(param.sf){param.sf();	}
						}
					},
					bf:function(){
						$(dig).mask('数据提交中....');
					},
					cf:function(){
						$(dig).unmask();
					}

				});
				 
		     }
			//dialogcancel
		     function _handleDialogCancel(){
				$(dig).dialog('close');
		     }  
			
}



//passdialog
/*
 * 要准备好html，里面的form是标准的jpSubmit的格式，
 * form只处理input 和textarea,  不考虑有combobox,
 * title已经处理
 * 不加载数据
 * id先从row.id取，然后传到dig.id，然后传到form的postidvalue,然后通过jpSubmit发送
 * 
 * 可选的三个handler:  是后处理的，
 * dig.openHander
 * dig.closeHandler
 * dig.sf
 */
function getPassDialog(dig){

		dig.title='id['+dig.id+']审核通过';

		dig.dialog({
			width:'auto',
			height:'auto',
			modal:true,
			closed:true,
			title:dig.title,
			buttons:[
				         {plain:false, text:'确认', id:'commit', handler:_handleDialogCommit},
				         {plain:false, text:'取消', id:'cancel', handler:_handleDialogCancel}
				],
			onOpen:function(){
					_handleDialogOpen();
					if(dig.openHandler){
						dig.openHandler();
					}
			},
			onClose:function(){
					_handleDialogClose();
					if(dig.closeHandler){
						dig.closeHandler();
					}
			}
		});
		return dig;

		
		//dialogopen
		function _handleDialogOpen(){
			//title
				dig.title='id['+dig.id+']审核通过';
				$(dig).dialog('setTitle',dig.title);
			//form postidvalue
				$(dig).find('form').attr('postidvalue',dig.id);
		}
		
		//dialogclose
		function _handleDialogClose(){
			//data clear
			$(dig).find('textarea').val('');
			$(dig).find('input[type="text"]').val('');
			dig.id=null;
		}

		
		//dialogcommit
	     function _handleDialogCommit(){
	    	 var ops={
						sf:function(result){
				 		 			alert(result.msg);
									if(result.success){
										$(dig).dialog('close');
										if(dig.sf){dig.sf();}
									}
				 	 		},
				 	 	bf:function(){
				 	 		$(dig).mask('数据提交中....');
				 	 	},
				 	 	cf:function(){
				 	 		$(dig).unmask();
				 	 	}
				 	 };
	    	 
	    	 $(dig).find('form').form('jpSubmit',ops);
			 
	     }
		//dialogcancel
	     function _handleDialogCancel(){
			dig.dialog('close');
	     }  
		 
		 
	}//[endrenderdig]



function getVerifyDialog(dig){
	var digHTML='';
	digHTML+='<div class="verify-dialog" id="">';
	digHTML+='<form class="validate" posturl="procure_executeOrder.do" postidname="id" postidvalue=""><table>';
	digHTML+='<tr><td>备注</td><td><textarea name="comment" class="wide-comment"></textarea></td></tr>';
	digHTML+='</table></form></div>';
	


	updateTitle();

	dig.dialog({
		width:520,
		height:260,
		modal:true,
		closed:true,
		title:dig.title,
		buttons:[
			         {plain:false, text:'确认', id:'commit', handler:_handleDialogCommit},
			         {plain:false, text:'取消', id:'cancel', handler:_handleDialogCancel}
			],
		onOpen:function(){
				_handleDialogOpen();
		},
		onClose:function(){
				_handleDialogClose();
		}
	});
	return dig;
	
	
	function updateTitle(){
		if(dig.passFlag){
			dig.title='id['+dig.id+']审核通过';
		}else{
			dig.title='id['+dig.id+']审核不通过';
		}
		$(dig).dialog('setTitle',dig.title);
	}
	
	//dialogopen
	function _handleDialogOpen(){
		//title
			updateTitle();
		//form postidvalue
			$(dig).find('form').attr('postidvalue',dig.id);
	}
	
	//dialogclose
	function _handleDialogClose(){
		//data clear
		$(dig).find('textarea').val('');
		$(dig).find('input[type="text"]').val('');
		dig.id=null;
	}

	
	//dialogcommit
     function _handleDialogCommit(){
    	 $(dig).find('form').form('jpSubmit',{
				sf:function(result){
    		 		alert(result.msg);
					if(result.success){
						$(dig).dialog('close');
						dig.sf();
					}
    	 		},
    	 		bf:function(){
		 	 		$(dig).mask('数据提交中....');
		 	 	},
		 	 	cf:function(){
		 	 		$(dig).unmask();
		 	 	}
    	 });
		 
     }
	//dialogcancel
     function _handleDialogCancel(){
		dig.dialog('close');
     }  
	 
	 
}//[endrenderdig]

function getRejectDialog(dig){

	dig.title='id['+dig.id+']审核不通过';

	dig.dialog({
		width:'auto',
		height:'auto',
		modal:true,
		closed:true,
		title:dig.title,
		buttons:[
			         {plain:false, text:'确认', id:'commit', handler:_handleDialogCommit},
			         {plain:false, text:'取消', id:'cancel', handler:_handleDialogCancel}
			],
		onOpen:function(){
				_handleDialogOpen();
				if(dig.openHandler){
						dig.openHandler();
					}
		},
		onClose:function(){
				_handleDialogClose();
				if(dig.closeHandler){
						dig.closeHandler();
					}
		}
	});
	return dig;

	
	//dialogopen
	function _handleDialogOpen(){
		//title
			dig.title='id['+dig.id+']审核不通过';
			$(dig).dialog('setTitle',dig.title);
		//form postidvalue
			$(dig).find('form').attr('postidvalue',dig.id);
	}
	
	//dialogclose
	function _handleDialogClose(){
		//data clear
		$(dig).find('textarea').val('');
		$(dig).find('input[type="text"]').val('');
		dig.id=null;
	}

	
	//dialogcommit
     function _handleDialogCommit(){
    	 $(dig).find('form').form('jpSubmit',{
				sf:function(result){
    		 		alert(result.msg);
					if(result.success){
						$(dig).dialog('close');
						if(dig.sf){
						dig.sf();
						}
					}
    	 		},
    	 		bf:function(){
		 	 		$(dig).mask('数据提交中....');
		 	 	},
		 	 	cf:function(){
		 	 		$(dig).unmask();
		 	 	}
    	 });
		 
     }
	//dialogcancel
     function _handleDialogCancel(){
		dig.dialog('close');
     }  
	 
	 
}//[endrenderdig]




function getProcurementBillDatagrid(dg,params){
	
	if(!params){
		params={};
		params.actionColumn=null;
		params.queryParams=null;
	}
	
	var columnArray=[
			          //字段名待指定
						{field:'checked',width:60,checkbox:true},
						{field:'id',title:'采购单号',width:70,filter:true},
						{field:'purchaseNo',title:'采购批次',width:80,filter:true},
						//{field:'customerId',title:'供应商编号',width:60},
						{field:'supplierName',title:'供应商名称',width:200,filter:true,sortable:true},
						{field:'paymethod',title:'付款条件',width:80, 
							formatter:function(value, row, index){
								return value.name;
							}
						},
						/*{field:'paymethod',title:'结算方式',width:55,
							formatter:function(value, row, index){
								return value.name;
							}
						},*/
						//{field:'customerName',title:'帐期',width:60},
						//{field:'currencyCode',title:'币种',width:55},
						{field:'paidAmount',title:'已付金额',width:80},
						{field:'totalAmount',title:'总金额',width:55},
						{field:'payFlag',title:'付款状态',width:80,/* filter:true, filterType:'combo',combodata:[
								{id:1, name:'未付'},
								{id:2, name:'已付首款'},
								{id:3, name:'全部已付'}
								],*/
							formatter:function(value, row, index){
								if(value==1){
									return '未付';
								}else if(value==2){
									return '已付首款';
								}else if(value==3){
									return '全部已付';
								}else{
									return '';
								}
							}
						},
						{field:'state', title:'状态', width:50, filter:true},
						{field:'creator',title:'创建人',width:60},
						{field:'createTime',title:'创建时间',width:80}
						
						
					];
	if(params.actionColumn!=null){
		columnArray.push(params.actionColumn);
	}
	if(!dg.toolbar){
		dg.toolbar=[];
	}
	
		var warehouseURL='dict_getAllWarehouse.do';
		var warehouseData;
		$.post(warehouseURL, function(result){
			warehouseData=result;
		});
	
	 var ops={
		  	width:'auto',
		  	height:'auto',
		  	pagination:'true',
		  	pageSize:'20',
		  	sortName:'id',
		  	sortOrder:'desc',
		  	toolbar:dg.toolbar,
		  	queryParams:params.queryParams,
		  	url:'procure_list.do',
		  	traditional:true,
			columns:[columnArray],
				 	view:detailview,
					detailFormatter:function(rowIndex, rowData){
						var itemList=rowData.procureItemList;
						if(itemList.length!=0){
							var output='<table class="order-item">'+
							'<thead><tr>'+
							'<td>sku</td>'+
							'<td width="80">别名</td>'+
							'<td>产品名称</td>'+
							'<td width="80">属性</td>'+
							'<td width="80">状态</td>'+
							'<td width="40">数量</td>'+
							'<td width="40">单价</td>'+
							'<td width="40">总价</td>'+
							'<td width="40">紧急</td>'+
							'<td width="80">关联付款</td>'+
							'</tr></thead>';
							
							$.each(itemList, function(itemIndex, item) { 
								
									$.each(warehouseData, function(index, wh){
										if(item.warehouseId==wh.id){item.warehouse=wh.name;}
									});
									if(!item.warehouse){
										item.warehouse='';
									}
									
								var emeger = '';
								if (item.emergency) {
									emeger = '<td class="red">'+item.emergency+'</td>';
								} else {
									emeger = '<td>'+item.emergency+'</td>';
								}
								
								var temp=
								'<tbody><tr>'+
								'<td>'+item.sku+'</td>'+
								'<td>'+item.skuAlias+'</td>'+
								'<td>'+item.stockName+'</td>'+
								'<td>'+item.attribute+'</td>'+
								'<td>'+item.state+'</td>'+
								'<td>'+item.quantity+'</td>'+
								'<td>'+item.price+'</td>'+
								'<td>'+item.amount+'</td>'+
								emeger +
								'<td>'+item.paying+'</td>'+
								'</tr></tbody>';
								output+=temp;
								
								});		
							output+='</table>';
							return output;
						}else{
							return null;
						}
					}	
	    };
	 dg.datagrid(ops);
	 return dg;
}





/*
 * 
 * 付款单datagrid
 * paymentListDatagrid
 * @dg.params不同状态参数：可选的
 * 		1.在付款审核时，状态是Pending
 * 		2.在录入付款时，状态是Confirmed
 * 
 * 
 * create: 2012-03-19 yishu
 */


function getPaymentDatagrid(dg){
	
	if(!dg.params){
		dg.params={};
	}
	var dgurl='procurePayment_list.do';
	if(dg.url){
		dgurl=dg.url;
	}
	if(!dg.traditionalPost){
		dg.traditionalPost=false;
	}
	
		var columnArray=[
						{field:'checked',width:60,checkbox:true},
						{field:'id',title:'流水号',width:30},
						
						{field:'supplierName',title:'供应商名称',width:160,filter:true},
						{field:'state',title:'状态',width:60},
						//{field:'province',title:'付款单号',width:80},
						//{field:'city',title:'供应商编号',width:80},
						//{field:'addressType',title:'供应商名称',width:80},
						{field:'bank',title:'银行',width:60,filter:true},
						{field:'account',title:'账号',width:160},
						//{field:'postCode',title:'户名',width:80},
						{field:'receiver',title:'收款人',width:60,filter:true},
						{field:'currencyCode',title:'币种',width:60},
						{field:'paidAmount',title:'已付金额',width:60},
						{field:'amount',title:'金额',width:60},
						{field:'createTime',title:'创建时间',width:80}
					]
		var actionField={};
		if (dg.actionField) {
			actionField=dg.actionField;
			columnArray.push(actionField);
		} 
		if(!dg.toolbar){
			dg.toolbar=[];
		}
		
		dg.datagrid({
		  	width:'auto',
		  	height:'auto',
		  	method:'post',
		  	pagination:true,
		  	pageSize:'20',
		  	sortName:'id',
		  	sortOrder:'desc',
		  	url:dgurl,
		  	toolbar:dg.toolbar,
		  	traditional:dg.traditionalPost,
			queryParams:dg.params,
			columns:[columnArray],
			view:detailview,
			detailFormatter:function(rowIndex, rowData){
			var itemList=rowData.procureItemList;
			if(itemList.length!=0){
				var output='<table class="order-item">'+
				'<thead><tr>'+
				'<td>收货时间</td>'+
				'<td>sku</td>'+
				'<td width="80">别名</td>'+
				'<td>产品名称</td>'+
				'<td width="80">属性</td>'+
				'<td width="80">状态</td>'+
				'<td width="40">数量</td>'+
				'<td width="40">单价</td>'+
				'<td width="40">总价</td>'+
				'</tr></thead>';
				
				$.each(itemList, function(itemIndex, item) { 
					
					var temp=
					'<tbody><tr>'+
					'<td>'+item.receiveTime+'</td>'+
					'<td>'+item.sku+'</td>'+
					'<td>'+item.skuAlias+'</td>'+
					'<td>'+item.stockName+'</td>'+
					'<td>'+item.attribute+'</td>'+
					'<td>'+item.state+'</td>'+
					'<td>'+item.quantity+'</td>'+
					'<td>'+item.price+'</td>'+
					'<td>'+item.amount+'</td>'+
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
	    dg.datagrid('addColumnSearch');
	    dg.datagrid('prependExpander');
	}














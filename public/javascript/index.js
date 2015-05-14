
	renderLayout();
	renderChangePasswordDialog();

	function renderLayout(){
		var linksHTML='';
		var tempLinkHTML='';
		var liClass='';
		
		$.post("/supplierback/menu_list.do", function(navs) {

			
			//页面最顶部导航处理
			$.each(navs, function(nav_index, navItem){
				tempLinkHTML='<li>'+navItem.name+'</li>';
				linksHTML+=tempLinkHTML;
			});
			$('#header ul#main').append(linksHTML);	//导航贴上去

			
			//页面左边菜单处理
			$.each(navs, function(nav_index, navItem){
				if(navItem.subMenus.length>0){
					var menuHTML='<div class="menu-category">';
					$.each(navItem.subMenus, function(menu_class_index, menuClass){
						menuHTML+='<li><a class="expanded heading">'+menuClass.name+'</a><ul class="navigation">';
								$.each(menuClass.subMenus, function( menu_index,menu){
										menu.link=menu.link.replace(/.jsp/, ".html");
										menuHTML+='<li><a href="'+menu.link+'" title="'+menu.name+'">'+menu.name+'</a></li>';
								});		
			           menuHTML+='</ul></li>';
					});
					menuHTML+='</div>';
					$('#leftside ul#nav').append(menuHTML);
				}//end if 
			});//end each navs
			
			
			//顶部菜单事件处理
			$('#header ul#main li').click(function(e){handleLoadMenu(e);});
			//左边菜单事件处理
			$('#leftside ul.navigation li a').click(function(e){handleLoadPageContent(e);});

			clickFirstTab();
		

			
		});//end $.post
	}//end fuction
	
	
	function handleLoadMenu(e){
		e.preventDefault();
		$('#header ul#main li').removeClass('selected');
		$(e.target).addClass('selected');
		
		
		var index = $('#header ul#main li').index($(e.target));// console.log(index);
		$('#leftside .menu-category').removeClass('current');
		$('#leftside .menu-category').eq(index).addClass('current');
	}
	
	function handleLoadPageContent(e){
		e.preventDefault();
		var url=$(e.target).attr('href') + '?d=' + new Date().getTime();
		var sf=function(responseText, textStatus, XMLHttpRequest){
			var result = {};
			try {
			 result = ($.parseJSON(responseText));
			} catch(err){};
			if(result.action=='login'){
				$.openURL('/supplierback/login.jsp');
				return false;
			}
			if(XMLHttpRequest.status=='404'){
				alert('找不到页面链接'+url);
			}else{
				$('#homepage ul#nav ul.navigation li').removeClass('selected');
				$(e.target).parent('li').addClass('selected');
			}
			
		}
		$('#homepage #rightside').load(url,sf);
		/*
		var sf=function(result){
			if(result.action=='login'){
				$.openURL('/supplierback/login.jsp');
			}else{
				$('#homepage ul#nav ul.navigation li').removeClass('selected');
				$(e.target).parent('li').addClass('selected');
				$('#homepage #rightside').html(result);
			}
		}
		$.post(url,,sf);
		*/
	}
	
	function clickFirstTab(){
		$('#header ul#main li:first').click();
	}

	function handleOpenSelf(url){
		window.open(url, _self);
	}


	
	function handleOpenChangePasswordDialog(){
		changePasswordDialog.dialog('open');
	}

	function renderChangePasswordDialog(){
		window.changePasswordDialog=$('#change-password-dialog');
		var dig=changePasswordDialog;
		dig.beforeSubmit=function(){
			var np=$(dig).find('input[name=newPwd]').val();
			var cp=$(dig).find('input[name=confirmPwd]').val();
			if(np!=cp){
				alert('两次密码不同');
				return false;
			}else{
				return true;
			}
		}
		dig.dialog('simple');

	}

	function handleLogout() {
		$.post('/supplierback/auth_logout.do',function(result){//
			if(result!=null && result.success){
				window.location.href = '/supplierback/login.jsp';
			}else{
				$.messager.alert('退出失败',result.msg);
			}
		},'json');
	}

	

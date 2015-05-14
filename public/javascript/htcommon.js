var jq = $.noConflict();
if(typeof(jQuery)!='undefined'){
	//增加选择的名称到结果框中
	function addseletypevalue($el,arr,cbfun){
		cbfun=cbfun || 0;
		var strid=$el.attr('id');
		var $tdiv=jq('#'+strid+'_w_valuediv');
		if($tdiv.length==0){
			var $tdiv=jq('<div id="'+strid+'_w_valuediv" class="typevaluediv">').insertBefore($el);
		}
		for(var i=0;i<arr.length;i++){
			var tcid=arr[i][0];
			var c_name;
			//alert(arr[i][1].split('/'));
			jq.each(arr[i][1].split('/'), function ( index, value ) { c_name = value; });
			//jq('<a href="javascript:void(0);" title="单击删除此类别" val="'+arr[i][0]+'">'+arr[i][1]+'</a>')
			jq('<a href="javascript:void(0);" title="单击删除此类别" val="'+arr[i][0]+'">'+c_name+'</a>')
				.click(function(){
					if(cbfun){cbfun.call($el,tcid);}
					jq(this).remove();
					var strval=(','+$el.val()+',').replace(','+jq(this).attr('val')+',',',');
					
					if(strval.length>1){
						var tempValue = strval.substring(1,strval.length-1);
						
						var tempArr = toArray( tempValue );
						
						$el.val( tempArr.length ? ',' + tempArr.join(',') + ',' : '' );
					}else{
						$el.val('');
					}
				})
				.appendTo($tdiv);
		}
	}
	
	(function($){
		$.fn.showE=function(a,b,c){var e=this;var f=arguments.length;if(f==1){var g=$.extend({},$.fn.showE.defaults,a);e.css({position:'absolute','z-index':999});$('#'+g.btnclose).bind('click',close);if(g.shade){showshade()}setlocation();setevent()}else{setlocation2();$(window).resize(setlocation2)}e.show();return e;function close(){e.hide();$(window).unbind("resize",setlocation);$(window).unbind("scroll",setlocation)};function showshade(){$('html').css('overflow','hidden');$('body').css('overflow','hidden')};function setlocation(){var a=g.position.split(' ');if(a.length<2){a[1]='center'}var b=inttop=0;switch(a[0]){case'center':b=($(window).width()-e.outerWidth())/2;break;default:b=a[0]}switch(a[1]){case'center':inttop=($(window).height()-e.outerHeight())/2+$(window).scrollTop();break;default:inttop=Number(a[1])+$(window).scrollTop()}e.css({left:b,top:inttop})};function setlocation2(){var o=a.offset();var t=0;var l=0;b=b||3;c=c||0;if(b==1){t=-c-e.outerHeight();if(o.top+t<0){t=c+a.outerHeight()}}if(b==2){l=c+a.outerWidth()}if(b==3){t=c+a.outerHeight();var d=o.left+e.outerWidth()-$(window).width();if(d>0){l=-d}}if(b==4){l=-c-e.outerWidth()}e.css({top:o.top+t,left:o.left+l})};function setevent(){$(window).resize(setlocation);$(window).scroll(setlocation)}};$.fn.showE.defaults={position:'center',btnclose:'',shade:0}
		$.fn.w_stronglist = function(q) {
		    if (!q.keysea) {
		        q.keysea = q.key
		    }
		    var r = $(this).data('oldval', ''),
		    keydownval = 0;
		    if (r.data('attrinit') != 1) {
		        var s = $.extend({},
		        $.fn.w_stronglist.defaults, q);
		        if (s.searchaim) {
		            var t = $('#' + s.searchaim)
		        } else {
		            var t = $('<input type="text" autocomplete="off" class="cateinput" />').insertAfter(r).data('oldval', '')
		        }
		        if (s.hide) {
		            var u = $('<div href="#" class="catebutton">选择分类</div>').insertAfter(t.hide())
		        }
		        if (s.treeaim != 0) {
		            var v = $('#' + s.treeaim).css({
		                width: s.treewidth + s.selewidth + (s.selediv == 1 ? 5 : 2),
		                height: s.height + 2
		            });
		            var w = v.children('.catetree').css({
		                width: s.treewidth,
		                height: s.height
		            });
		            if (q.selediv == 1) {
		                var x = v.children('.catesele').css({
		                    width: s.selewidth,
		                    height: s.height
		                })
		            }
		        } else {
		            var v = $('<div class="catetocid" style="z-index: 9999;">').css({
		                width: s.treewidth + s.selewidth + (s.selediv == 1 ? 5 : 2),
		                height: s.height + 2
		            });
		            var w = $('<div class="catetree">').css({
		                width: s.treewidth,
		                height: s.height
		            }).appendTo(v);
		            if (q.selediv == 1) {
		                var x = $('<div class="catesele">').css({
		                    width: s.selewidth,
		                    height: s.height
		                }).appendTo(v)
		            }
		            v.appendTo('body')
		        }
		        $(document).keydown(function(e) {
		            if (e.keyCode == '16') {
		                keydownval = 16
		            }
		        }).keyup(function() {
		            keydownval = 0
		        });
		        if (s.hide) {
		            t.focus(function() {
		                v.showE(t, 3, 1)
		            }).click(function() {
		                v.showE(t, 3, 1)
		            });
		            u.click(function() {
		                u.hide();
		                t.show().focus()
		            });
		            $(document).keydown(function(e) {
		                if (e.keyCode == '27') {
		                    close()
		                }
		            });
		            $(document).mousedown(function(e) {
		                if ($(e.target).index(t) < 0 && $(e.target).parents().index(v) < 0) {
		                    close()
		                }
		            })
		        }
		        r.data('attrinit', 1);
		        w.data('sele', {
		            cid: '',
		            cname: ''
		        });
		        //alert(q.cids)
		        if (r.val() != '') {
		            initselevalue()
		        }
		    }
		    if (q == 'val') {
		        return w.data('val')
		    } else if (q == 'vals') {
		        return x.data('val')
		    } else if (q.sele != undefined) {
		        if (q.sele == "") {
		            return
		        }
		        w.data('sele').cid = q.sele;
		        if (q.key == '') {
		            return
		        }
		        $.ajax({
		            cache: false,
		            timeout: 30000,
		            type: "GET",
		            url: '/PersonActionsPagedSorted.php?menu_action=' + q.key + '&cids=' + q.sele + '&WebsiteId='+q.WebsiteId,
		            success: function(a) {
		                if (a.substr(0, 3) == 'var') {
		                    eval(a);
		                    var b;
		                    var c = [];
		                    var d = [];
		                    for (var i = 0; i < result.length; i++) {
		                        c[i] = result[i][0];
		                        d[i] = result[i][1];
		                        b = $('<dl cid="' + result[i][0] + '">' + result[i][1] + '</dl>');
		                        b.data('cval', {
		                            cid: result[i][0],
		                            cname: result[i][1],
		                            up: result[i][2]
		                        }).appendTo(x)
		                    }
		                    c = c.join(',');
		                    d = d.join(',');
		                    w.data('sele', {
		                        cid: c,
		                        cname: d
		                    })
		                }
		            }
		        })
		    }
		    var q = $.extend({},
		    $.fn.w_stronglist.defaults, q);
		    if (!q.root && !q.auto) {
		        q.auto = 1
		    }
		    autoloadroot();
		    var y = 0;
		    t.keydown(function(e) {
		        clearTimeout(y)
		    }).keyup(function(e) {
		        if (e.which >= 49 || e.which == 32 || e.which == 8) {
		            clearTimeout(y);
		            y = setTimeout(searchvalue, 500)
		        }
		    });
		    w.click(function(e) {
		        var a = e.target.tagName.toLowerCase();
		        if (e.target.className.toLowerCase().indexOf('catetree') >= 0) {
		            return false
		        };
		        if (a == 'span' || a == 'input') {
		            var b = $(e.target).parents('dl').eq(0)
		        } else {
		            var b = $(e.target)
		        }
		        var c = b.attr('cid');
		        if (a == 'dl') {
		            var d = b.next();
		            var f = b.attr('key');
		            if (b.hasClass('Tminus')) {
		                b[0].className = 'Tplus';
		                d.addClass('t_close')
		            } else if (b.hasClass('Tplus')) {
		                b[0].className = 'Tminus';
		                d.removeClass('t_close');
		                d.show();
		                if (b.attr('va') != '1') {
		                    if (f == '' || f == 'undefined') {
		                        f = q.key
		                    }
		                    loadnode(d, c, b, w, f)
		                }
		            }
		        } else {
		            if (a == 'span') {
		                var g = {
		                    el: b,
		                    cid: b.attr('cid'),
		                    cname: $('span', b).text(),
		                    up: b.attr('up'),
		                    lcname: getlangname(b)
		                };
		                w.data('val', g);
		                if (b.attr('nc') == 0 && q.nodeClick) {
		                    q.nodeClick.call(this, r, g, q.charborder);
		                    close()
		                }
		            } else if (a == 'input') {
		                var h = $(e.target);
		                var i = h.attr('checked');
		                var j = b;
		                if (keydownval == 16) {
		                    var k = h.closest('dl');
		                    var l = k.parent().children('dl[cid]');
		                    var m = l.filter('dl.Tsele');
		                    var n = l.index(m);
		                    if (n != -1) {
		                        var o = l.index(k);
		                        if (n > o) {
		                            var p = o;
		                            o = n;
		                            n = p
		                        }
		                        j = l.slice(n, o + 1);
		                        h = j.find('input:checkbox').attr('checked', i)
		                    }
		                }
		                var g = new Array();
		                j.each(function() {
		                    tmpel = $(this);
		                    g.push({
		                        cid: tmpel.attr('cid'),
		                        cname: $('span', tmpel).text(),
		                        up: tmpel.attr('up')
		                    })
		                });
		                if (q.selediv) {
		                    selechangenode(h, g, x, w, i)
		                }
		                changeval(w, x, g, i)
		            }
		            if (w.data('old')) {
		                w.data('old').removeClass('Tsele')
		            }
		            b.addClass('Tsele');
		            w.data('old', b)
		        }
		    }).mousemove(function(e) {
		        var a = e.target.tagName.toLowerCase();
		        if (a == 'span' || a == 'input') {
		            var b = $(e.target).parents('dl').eq(0)
		        } else {
		            var b = $(e.target)
		        }
		        if (b.hasClass('Tminus') || b.hasClass('Tplus') || b.hasClass('Tno')) {
		            b.addClass('Tmov')
		        }
		    }).mouseout(function(e) {
		        var a = e.target.tagName.toLowerCase();
		        if (a == 'span' || a == 'input') {
		            var b = $(e.target).parents('dl').eq(0)
		        } else {
		            var b = $(e.target)
		        }
		        b.removeClass('Tmov')
		    });
		    if (q.selediv == 1) {
		        x.dblclick(function(e) {
		            var a = $(e.target);
		            seledelnode(a.data('ein'), a.data('cval'), x, w)
		        }).mousemove(function(e) {
		            var a = $(e.target);
		            a.addClass('Tmov')
		        }).mouseout(function(e) {
		            var a = $(e.target);
		            a.removeClass('Tmov')
		        })
		    }
		    function close() {
		        if (q.hide) {
		            v.hide();
		            t.hide();
		            q.hide && u.show()
		        }
		    }
		    function autoloadroot() {
		        if (q.root) {
		            w.html('<dl cid="' + q.up + '" class="' + ((q.auto) ? 'Tminus': 'Tplus') + ' root">顶部选项</dl><dl class="Ino root_l"></dl>')
		        }
		        if (q.auto) {
		            if (q.root) {
		                var a = $('.root_l', w);
		                loadnode(a, a.attr('cid'), $('.root', w), w, q.key)
		            } else {
		                loadnode(w, q.up, 0, w, q.key)
		            }
		        }
		    }
		    function initselevalue() {
		        $.ajax({
		            cache: false,
		            timeout: 30000,
		            type: "GET",
		            url: '/PersonActionsPagedSorted.php?menu_action=' + q.key + '&cids=' + r.val() + '&WebsiteId='+q.WebsiteId,
		            success: function(a) {
		                if (a.substr(0, 3) == 'var') {
		                    eval(a);
		                    if (q.valueInitFun) {
		                        q.valueInitFun.call(this, r, result)
		                    }
		                }
		            }
		        })
		    }
		    function selechangenode(b, c, d, e, f) {
		        if (typeof(c) != 'object') {
		            return
		        }
		        var i = 0;
		        b.each(function(i) {
		            var a = $('dl[cid=' + c[i].cid + ']', d);
		            if (f) {
		                if (a.length <= 0) {
		                    var a = $('<dl cid="' + c[i].cid + '"></dl>').appendTo(d);
		                    a.text(c[i].cname).data('cval', c[i]).data('ein', $(this))
		                }
		            } else {
		                if (a.length > 0) {
		                    a.remove()
		                }
		            }
		        })
		    };
		    function seledelnode(a, b, c, d) {
		        if (typeof(b) != 'object') {
		            return
		        }
		        var e = $('dl[cid=' + b.cid + ']', c);
		        changeval(d, c, [{
		            cid: b.cid,
		            cname: b.cname
		        }], false);
		        if (e.length > 0) {
		            e.remove();
		            if (a) {
		                a[0].checked = false
		            } else {
		                a = $('dl[cid=' + b.cid + ']', d).find('input');
		                if (a.length > 0) {
		                    a[0].checked = false
		                }
		            }
		        }
		    };
		    function changeval(a, b, c, d) {
		        var e = a.data('sele').cid;
		        var f = a.data('sele').cname;
		        for (var i = 0; i < c.length; i++) {
		            if (d) {
		                if (e.indexOf(',' + c[i].cid + ',') == -1) {
		                    e += ',' + c[i].cid + ',';
		                    f += ',' + c[i].cname + ','
		                }
		            } else {
		                e = ',' + e + ',';
		                f = ',' + f + ',';
		                e = e.replace(',' + c[i].cid + ',', ',');
		                f = f.replace(',' + c[i].cname + ',', ',')
		            }
		        }
		        a.data('sele', {
		            cid: e,
		            cname: f
		        });
		        var g = e.split(',');
		        var h = Array();
		        for (var i = 0; i < g.length; i++) {
		            if (g[i]) {
		                h.push(g[i])
		            }
		        }
		        var g = f.split(',');
		        var j = Array();
		        for (var i = 0; i < g.length; i++) {
		            if (g[i]) {
		                j.push(g[i])
		            }
		        }
		        if (q.nodeChange) {
		            q.nodeChange.call(this, {
		                cids: h,
		                cnames: j
		            })
		        }
		    };
		    function loadnode(h, j, k, l, m) {

		        var n = l.data('sele').cid;
		        h.html('正在加载栏目……');
		        $.ajax({
		            cache: false,
		            timeout: 30000,
		            type: "GET",
		            url: '/PersonActionsPagedSorted.php?menu_action=' + m + '&cid=' + j + '&WebsiteId='+q.WebsiteId,
		            success: function(a) {
		                if (a.substr(0, 3) == 'var') {
		                    eval(a);
		                    var b = [];
		                    var c = "";
		                    var d = "";
		                    for (var i = 0; i < result.length; i++) {
		                        c = (parseInt(result[i][3]) > 0) ? 'Tplus': 'Tno';
		                        var e = result[i][5] == 'undefined' ? '': result[i][5];
		                        var f = e == 'c' || e == 'cn' ? 1 : 0;
		                        var g = e == 'n' || e == 'cn' ? 1 : 0;
		                        if (q.box && f == 0) {
		                            d = '<input type="checkbox" ' + checknode(result[i][0], n) + ' />'
		                        } else {
		                            d = ''
		                        }
                                if(m == 'w_SuppliersName'){
                                    b[i] = '<dl class="' + c + '" cid="' + result[i][0] + '" up="' + result[i][2] + '" key="' + result[i][4] + '" nc="' + g + '"><span>' + d + result[i][1] + '<font color="red">[' +result[i][2]+ ']</font></span></dl>';
                                }if(m == 'hot_categories_zh-cn'){
                                    if(result[i][3]=='0'){
                                        b[i] = '<dl class="' + c + '" cid="' + result[i][0] + '" up="' + result[i][2] + '" key="' + result[i][4] + '" nc="' + g + '"><span>' + d + result[i][1] + '</span></dl>';
                                    }else{
                                        b[i] = '<dl class="' + c + '" cid="' + result[i][0] + '" up="' + result[i][2] + '" key="' + result[i][4] + '" nc="' + g + '">' + d + result[i][1] + '</dl>';
                                    }
                                     
                                    
                                }else{
                                    b[i] = '<dl class="' + c + '" cid="' + result[i][0] + '" up="' + result[i][2] + '" key="' + result[i][4] + '" nc="' + g + '"><span>' + d + result[i][1] + '</span></dl>';
                                }
		                        if (c == 'Tplus') {
		                            b[i] += '<dl class="Ino t_close"></dl>'
		                        }
		                    }
		                    c = b.join("");
		                    h.html(c);
		                    if (k) {
		                        k.attr('va', '1')
		                    }
		                } else {
		                    loaderror(h, k)
		                }
		            },
		            error: function() {
		                loaderror(h, k)
		            }
		        })
		    }
		    function searchvalue() {
		        //return false;
		        if (t.val() == t.data('oldval')) {
		            return false
		        }
		        t.data('oldval', t.val());
		        if (t.val() == '') {
		            autoloadroot()
		        } else {
		            var d = w.data('sele').cid;
		            $.ajax({
		                cache: false,
		                timeout: 30000,
		                type: "GET",
		                url: '/PersonActionsPagedSorted.php',
		                data: {
		                    menu_action: q.keysea,
		                    seach: t.val(),
							WebsiteId : q.WebsiteId
		                },
		                success: function(a) {
		                    if (a.substr(0, 3) == 'var') {
		                        eval(a);
		                        var b = [];
		                        var c = "";
		                        for (var i = 0; i < result.length; i++) {
		                            if (q.box) {
		                                c = '<input type="checkbox" ' + checknode(result[i][0], d) + ' />'
		                            } else {
		                                c = ''
		                            }
		                            b[i] = '<dl class="Tno" cid="' + result[i][0] + '" up="' + result[i][2] + '" nc="0"><span>' + c + result[i][1] + '</span></dl>'
		                        }
		                        w.html(b.join(""))
		                    } else {
		                        loaderror(w)
		                    }
		                },
		                error: function() {
		                    loaderror(w)
		                }
		            })
		        }
		    }
		    function getlangname(a) {
		        var b = Array(a.children('span').text());
		        if (a.parent('dl.Ino').length > 0) {
		            a.parents('dl.Ino').each(function() {
		                b.unshift($(this).prev().children('span').text())
		            });
		            b = '/ ' + b.join(' / ')
		        } else {
		            b = b.join('')
		        }
		       	jq.each(b.split('/'), function ( index, value ) { b = value; });
		        return b
		    }
		};
		function checknode(a, b) {
		    if ((',' + b + ',').indexOf(',' + a + ',') < 0) {
		        return ''
		    } else {
		        return 'checked="checked"'
		    }
		};
		function loaderror(a, b) {
		    if (b) {
		        a.html('加载栏目失败').fadeOut(800,
		        function() {
		            a.addClass('t_close');
		            b[0].className = 'Tplus'
		        })
		    } else {
		        a.html('加载栏目失败')
		    }
		};
		$.fn.w_stronglist.defaults = {
		    root: 0,
		    auto: 1,
		    box: 0,
		    up: 0,
		    sele: '',
		    nodeClick: 0,
		    nodeChange: 0,
		    selediv: 0,
		    hide: 1,
		    key: '',
		    keysea: '',
		    treewidth: 300,
		    selewidth: 0,
		    height: 200,
		    treeaim: 0,
		    searchaim: 0,
		    charborder: 0,
		    valueInitFun: addseletypevalue
		};
		$.fn.w_getInputs=function(){var c=$.fn.w_getInputs.rule;var d={},exit=true;this.find('input[name]').each(function(){var a={},tmp,el=$(this),atype=el.attr('type'),aname=el.attr('name'),rule=el.attr('rule');switch(atype){case"hidden":case"password":case"text":d[aname]=el.val();break;case"checkbox":d[aname]=(el.attr('checked'))?el.val():0;break;case"radio":if(el.attr('checked')){d[aname]=el.val()}break}if(rule!=undefined){rule=rule.split('|');var b=rule[0];for(var i=1;i<rule.length;i++){tmp=rule[i].split(':');if(tmp.length==1){a[tmp[0]]=1}else{a[tmp[0]]=tmp[1]}}$.each(a,function(n,v){if(c[n]){exit=c[n].apply(el,[b,d[aname],v]);if(exit==false){el.focus()}else{exit=true}return exit}});if(!exit){return false}}});if(!exit){return false}this.find('select[name]').each(function(){var a=$(this);var b=a.attr('name');d[b]=a.val()});this.find('textarea[name]').each(function(){var a=$(this);var b=a.attr('name');d[b]=a.val()});return d};$.fn.w_getInputs.rule={nonull:function(a,b){if($.trim(b)==''){alert(a+' 不能为空！');return false}},minsize:function(a,b,v){if($.trim(b).length<v){alert(a+' 的长度不能少于'+v+'位！');return false}},charnum:function(a,b){if((/^[0-9_]+$/).test(b)||(/^[a-zA-Z_]+$/).test(b)){alert(a+' 必须由字母和数字组合！');return false}}};
		$.fn.w_nullInputState=function(s,c){if(c==undefined){var c=''}return this.each(function(){if(this.value==''){this.value=s;c!=''&&$(this).addClass(c)}$(this).focus(function(){if(this.value==s){this.value='';c!=''&&$(this).removeClass(c)}}).blur(function(){if(this.value==''){this.value=s;c!=''&&$(this).addClass(c)}})})}
		$.fn.isselbox=function(a){a=a||'请选择在需要操作的记录前打钩';var b=false;this.each(function(){if($(this).is(':checked')){b=true;return false}});if(!b)alert(a);return b}
	})(jQuery);
	
	jQuery.cookie=function(a,b,c){if(typeof(b)!='undefined'){c=c||{};if(b===null){b='';c=$.extend({},c);c.expires=-1}var d='';if(c.expires&&(typeof c.expires=='number'||c.expires.toUTCString)){var e;if(typeof c.expires=='number'){e=new Date();e.setTime(e.getTime()+(c.expires*24*60*60*1000))}else{e=c.expires}d='; expires='+e.toUTCString()}var f=c.path?'; path='+(c.path):'';var g=c.domain?'; domain='+(c.domain):'';var h=c.secure?'; secure':'';document.cookie=[a,'=',encodeURIComponent(b),d,f,g,h].join('')}else{var j=null;if(document.cookie&&document.cookie!=''){var k=document.cookie.split(';');for(var i=0;i<k.length;i++){var l=jQuery.trim(k[i]);if(l.substring(0,a.length+1)==(a+'=')){j=decodeURIComponent(l.substring(a.length+1));break}}}return j}};
	//扩展功能
	//增加结果到输入框前面
	function addtypevalue($el,data,cb,cbfun){
		cbfun=cbfun || 0;
		var strid=$el.attr('id');
		var $tdiv=jq('#'+strid+'_w_valuediv');
		if($tdiv.length==0){
			var $tdiv=jq('<div id="'+strid+'_w_valuediv" class="typevaluediv">').insertBefore($el);
		}
		var tmp2=cb?',':'';
		$el.val(tmp2+data.cid+tmp2);
		jq('<a href="javascript:void(0);" title="单击删除此类别">'+data.lcname+'</a>')
			.click(function(){
				if(cbfun){cbfun.call($el,data.cid);}
				jq(this).remove();
				$el.val('');
			})
			.appendTo($tdiv.text(''));
	}

	function addtypevalue_and_auto_fill($el,data,cb,cbfun,zz){
		cbfun=cbfun || 0;
		var strid=$el.attr('id');
		var $tdiv=jq('#'+strid+'_w_valuediv');
		if($tdiv.length==0){
			var $tdiv=jq('<div id="'+strid+'_w_valuediv" class="typevaluediv">').insertBefore($el);
		}
		var tmp2=cb?',':'';
		$el.val(tmp2+data.cid+tmp2);
		var sitelang = jq("#language").val();
		var WebsiteId = jq("#WebsiteId").val();
//		alert(jq("#language").val());
		
		var as_url = '/PersonActionsPagedSorted.php?menu_action=get_childrenList&_language_station='+ sitelang +'&_c_1_select_id='+data.cid+''+'&WebsiteId='+WebsiteId;
		console.log(as_url);
		jq('<a href="javascript:void(0);" title="单击删除此类别">'+data.lcname+'</a>').click(function(){
				if(cbfun){cbfun.call($el,data.cid);}
				jq(this).remove();
				$el.val('');
			}).appendTo($tdiv.text(''));

		jq.getJSON(as_url, function(json) {
			jq("#textalias").val(json.bn);
			jq("#textfront").val(json.fn);
			jq("#textseo").val(json.seo);
			jq("#texturl").val(json.urln);
		    });
		
	}	
	
	function toArray( str, sign ) {
		var arr = [], s = sign ? sign : ',';
		
		var tempArr = str.split( s );
		
		for( var i = tempArr.length - 1; i >= 0; i -- ) {
			var t = tempArr[ i ].replace( /^(\s|\u00A0)+|(\s|\u00A0)+$/g, '' );
			if( t !== '' ) {
				arr.push( t );
			}
		}
		
		return arr;
	}	
	
	//增加结果到输入框前面 多选
	function addtypevalues($el,data,cb){
		var strid=$el.attr('id');
		var $tdiv=jq('#'+strid+'_w_valuediv');
		if($tdiv.length==0){
			var $tdiv=jq('<div id="'+strid+'_w_valuediv" class="typevaluediv">').insertBefore($el);
		}
		
		if(cb){
			$el.val()
		}
		var tmp1=cb?'':',',tmp2=cb?',':'';
		if((tmp1+$el.val()+tmp1).indexOf(','+data.cid+',')==-1){
			var strval=$el.val()!=''?$el.val()+tmp1+data.cid+tmp2:tmp2+data.cid+tmp2;
			
			$el.val( ',' + toArray( strval ).join(',') + ',' );
			
			jq('<a href="javascript:void(0);" title="单击删除此类别" val="'+data.cid+'">'+data.lcname+'</a>')
				.click(function(){
					jq(this).remove();
					var strval=(tmp1+$el.val()+tmp1).replace(','+jq(this).attr('val')+',',',');
					if(strval.length>1){
						var tempValue = cb?strval:strval.substring(1,strval.length-1);
						
						var tempArr = toArray( tempValue );
						
						$el.val( tempArr.length ? ',' + tempArr.join(',') + ',' : '' );
					}else{
						$el.val('');
					}
				})
				.appendTo($tdiv);
		}
	}	

	function addtypevalues_and_ajax($el,data,cb){
		var strid=$el.attr('id');
		var $tdiv=jq('#'+strid+'_w_valuediv');
		if($tdiv.length==0){
			var $tdiv=jq('<div id="'+strid+'_w_valuediv" class="typevaluediv">').insertBefore($el);
		}
		
		if(cb){
			$el.val()
		}
		var tmp1=cb?'':',',tmp2=cb?',':'';
		if((tmp1+$el.val()+tmp1).indexOf(','+data.cid+',')==-1){
			var strval=$el.val()!=''?$el.val()+tmp1+data.cid+tmp2:tmp2+data.cid+tmp2;
			
			$el.val( ',' + toArray( strval ).join(',') + ',' );
			
			var lang = jq("#language_station").find("option:selected").val();
			
			/*var as_url = '/PersonActionsPagedSorted.php?menu_action=getProductElevate&_value='+ lang +'&_categoryId='+data.cid+'';
			
			jq.getJSON(as_url, function(json){
				var as_textarea = jq("#as_textarea").val();
				if(json != null) {
					if(as_textarea.length) {
						jq("#as_textarea").val(as_textarea +','+json);
					} else {
						jq("#as_textarea").val(json);
					}					
				}

			});*/
			
			jq('<a href="javascript:void(0);" title="单击删除此类别" val="'+data.cid+'">'+data.lcname+'</a>')
				.click(function(){
				    var catId = jq(this).attr('val');
                   /* var del_url = '/PersonActionsPagedSorted.php?menu_action=getProductElevate&_value='+ lang +'&_categoryId='+catId;
                    
                    jq.getJSON(del_url, function(result){
				        var as_textarea2 = jq("#as_textarea").val();
				        if(result != null) {
	                       		var newdata = as_textarea2.replace(result,'');
                                var dataNew = trimdh(newdata);
                                jq("#as_textarea").val(dataNew);
                                			
				        }

			         });*/
                     
					jq(this).remove();
					var strval=(tmp1+$el.val()+tmp1).replace(','+jq(this).attr('val')+',',',');
					if(strval.length>1){
						var tempValue = cb?strval:strval.substring(1,strval.length-1);
						
						var tempArr = toArray( tempValue );
						
						$el.val( tempArr.length ? ',' + tempArr.join(',') + ',' : '' );
					}else{
						$el.val('');
					}
				})
				.appendTo($tdiv);
		}
	}
    
    //去掉字符串首尾逗号
    function trimdh(text){
        {
            return (text || "").replace(/^\,+|\,+$/g, "");
        } 
    }	
	
	//将层全屏化，并可还原大小
	function fullwindiv(a,str,fun)
	{
		var obj=jq('#'+str);
		var el=jq(a);
		if(el.attr('fullwin')=='1'){
			var tmpd=obj.data('oldoff');
			obj.width(tmpd.width).height(tmpd.height)
				.css({position:'static'})
				.appendTo(tmpd.parent);
			el.text(el.data('oldtext')).attr('fullwin',0);
		}else{
			obj.data('oldoff',{width:obj.width(),height:obj.height(),parent:obj.parent()})
				.width(jq(window).width()).height(jq(window).height())
				.css({position:'absolute',top:jq(window).scrollTop(),left:0,'z-index':999})
				.appendTo('body');
			el.data('oldtext',el.text()).text('退出全屏').attr('fullwin',1);
		}
		fun({width:obj.width(),height:obj.height()});
	}

	//新窗口预览代码效果
	function winpreviewcode(str)
	{
		var win = window.open(" ");
		win.document.write(jq('#'+str).val());
	}

	//复制内容到剪贴板
	function syscopycontent(str)
	{
		var obj=jq('#'+str)[0];
		obj.select();
		obj.createTextRange().execCommand("Copy");
		alert('已经将模板内容复制到剪贴板!');
	}
	
	function stopDefault( e )
	{
		if ( e && e.preventDefault ){
			e.preventDefault();
		}else{

			window.event.returnValue = false;
		}
		return false;
	}

	//ajax执行相应的页面，并回调提供的函数
	//fun可为函数或者为触发者要改变的名称
	function runtoajax(e,a,fun,href)
	{
		var $a=jq(a);
		jq.ajax({
			url: (href?href:$a.attr('href')),
			data:{ajaxtrue:1},
			cache:false,
			dataType:'html',
			success: function(data){
				if(data.substr(0,3)=='var'){
					var dd=data.substr(3);
					if(typeof(fun)=='function'){
						fun.call($a,dd);
					}else{
						$a.text(fun[dd]);
					}
				}else{
					alert('操作失败');
				}
			}
		}); 
		e && stopDefault(e);
	};
}


function add_att(id, text){
	var length = parseInt($(id).value)+ 1;
	$(id).value = length;
	var str = text + '<br /><input type="text" size="20" name="' + id +'[' + length  + ']" />';
	var cdiv = document.createElement('div');
	cdiv.innerHTML = str;
	$('d_'+id).appendChild(cdiv);	
}

function add_custom(dl){
	//在TABLE增加一行TR
	var custom_frame = document.getElementById('custom_table');
	var custom_length = parseInt(document.getElementById('length').value) + 1;
	document.getElementById('length').value = custom_length;
	var str = document.getElementById('template').innerHTML;
	var newTR = custom_frame.insertRow(custom_frame.rows.length);
	newTR.id = custom_length;
	
	//newTR.innerHTML = innerHTMl//无效
	//很郁闷，TD的innerHTML竟然是只读的， 只能先添加TD后改变TD的innerHTML	
	var newTD0=newTR.insertCell(0);
	newTD0.className = 'altbg1';
	newTD0.innerHTML = '<select sytle="width:150px;" onchange="select_custom(this.value, \'custom_' + custom_length +'\');">' + str +  '</select>&nbsp;&nbsp;<img onmouseover="this.style.cursor=\'pointer\'" style="cursor: pointer;" onclick="add_custom()" src="./image/common/zoomin.gif">&nbsp;&nbsp;<img src="./image/common/zoomout.gif" onclick="delete_custom('+ custom_length +')" style="cursor: pointer;" onmouseover="this.style.cursor=\'pointer\'">';
	
	var newTD1=newTR.insertCell(1);
	newTD1.colSpan='3';
	newTD1.className = 'altbg2';
	newTD1.innerHTML = '<div id="custom_' + custom_length + '"></div>';
}

function delete_custom(id){
	var daleter_tr = document.getElementById(id);
	var custom_frame = document.getElementById('custom_table');

	//获取将要删除的行的Index
	var rowIndex = daleter_tr.rowIndex;

	//删除指定Index的行
	custom_frame.deleteRow(rowIndex);
}

//加强checkbox属性框
//blurNone 隐藏时清空内容
function CheckboxExt(els,op){
	ops={blurNone:0};
	op=jq.extend({},ops,op);
	els.bind('click',op,CheckboxExt_elclick);
	els.each(function(){
		var el=jq(this),aim=el.attr('aim');
		if(el.is(':checked')){
			jq('#'+aim).show();
		}else{
			jq('#'+aim).hide();
		}
	});
}
function CheckboxExt_elclick(e){
	var el=jq(this),aim=el.attr('aim'),bn=e.data.blurNone;
	if(el.is(':checked')){
		jq('#'+aim).show();
	}else{
		jq('#'+aim).hide();
		if(bn){jq('#'+aim).val('');}
	}
}

/**
 * 打印调用URL的内容 
 * @param {Object} url
 */
function printUrlContent(url) {
	jq.get(url,function(data){
		  	var headstr = "<html><head><title></title></head><body>";
			var footstr = "</body>";
		  	var newstr = data;
			var oldstr = document.body.innerHTML;
			document.body.innerHTML = headstr+newstr+footstr;
			window.print();
			setTimeout(function(){
				document.body.innerHTML = oldstr;
			}, 1000 );
			return false;
	})
	
}

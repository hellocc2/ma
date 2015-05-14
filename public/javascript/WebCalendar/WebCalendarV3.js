/**
 * 日历类 (调用方法查看最下方)
 * @version 3
 * @author skey_chen 2009-12-01 10:20
 * @email skey_chen@163.com
 */
//配置信息
var __WebCalendarConfig__ = {
	//当前js被引用的src目录地址
	contentPath:"" + document.getElementsByTagName("script")[document.getElementsByTagName("script").length - 1].src.substring(0, document.getElementsByTagName("script")[document.getElementsByTagName("script").length - 1].src.lastIndexOf("/") + 1),
	//皮肤列表,其中name值为皮肤文件夹名称
	skinList:[
		{name:"default"},//必须存在default
		{name:"gray"},
		{name:"lightGreen"}
	]
};
//加载所有皮肤
if(true) {
	var _headList = document.getElementsByTagName("head");
	for(var i = 0; i < __WebCalendarConfig__.skinList.length; i++) {
		var fileUrl = __WebCalendarConfig__.contentPath + "skin/" + __WebCalendarConfig__.skinList[i].name + "/WebCalendar.css";
		/*
		if(i == 0) {
			document.write('<link rel="stylesheet" type="text/css" skinType="WebCalendar"' + ' href="' + fileUrl + '" skin="' + __WebCalendarConfig__.skinList[i].name + '"/>');
		}
		else {
			document.write('<link rel="stylesheet" type="text/css" skinType="WebCalendar" disabled="true"' + ' href="' + fileUrl + '" skin="' + __WebCalendarConfig__.skinList[i].name + '"/>');
		}
		*/
		var fileref = document.createElement("link");
		fileref.setAttribute("rel", "stylesheet");
		fileref.setAttribute("type", "text/css");
		fileref.setAttribute("skinType", "WebCalendar");
		fileref.setAttribute("disabled", "true");
		if(i == 0) {fileref.disabled = false;}//默认加载default皮肤
		fileref.setAttribute("href", fileUrl);
		fileref.setAttribute("skin", __WebCalendarConfig__.skinList[i].name);
		_headList[_headList.length - 1].appendChild(fileref);
	}
}

function CalendarHelper() {
	this.skinLoad = true;//设置是否可动态加载皮肤(true可设置, false只使用默认皮肤)
	//private
	this.pickMode = {"second":1, "minute":2, "hour":3, "day":4, "month":5, "year":6};
	this.skinList = new Array();
	//语言包，可自由扩展
	this.language = {
		"year":[
			[""],
			[""]
		],
		"months":[
			["一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"],
			["JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC"]
		],
		"weeks":[
			["日","一","二","三","四","五","六"],
			["SUN","MON","TUR","WED","THU","FRI","SAT"]
		],
		//考虑到每个地方的季节分布不一样(1月-12月)比如中国春季是3-5月 etc.
		"quarter":[
			["冬", "冬", "春", "春", "春", "夏", "夏", "夏", "秋", "秋", "秋", "冬"],
			["SPRING", "SPRING", "SPRING", "SUMMER", "SUMMER", "SUMMER", "AUTUMN", "AUTUMN", "AUTUMN", "WINTER", "WINTER", "WINTER"]
		],
		"hour":[
			["时"],
			["H"]
		],
		"minute":[
			["分"],
			["M"]
		],
		"second":[
			["秒"],
			["S"]
		],
		"clear":[
			["清空"],
			["CLS"]
		],
		"today":[
			["今天"],
			["TODAY"]
		],
		//精确到年、月时把今天变成“确定”
		"pickTxt":[
			["确定"],
			["OK"]
		],
		"close":[
			["关闭"],
			["CLOSE"]
		]
	};
	//public 初始化
	//日历当前显示的时间
	this.date = new Date();
	this.year = this.date.getFullYear();
	this.month = this.date.getMonth();
	this.day = this.date.getDate();
	this.hour = this.date.getHours();
	this.minute = this.date.getMinutes();
	this.second = this.date.getSeconds();
	
	this.left = 0;
	this.top = 0;
	this.isFocus = false;//是否为焦点
	this.beginYearLoad = this.year - 30;
	this.endYearLoad = this.year + 20;
	this.beginYear = this.beginYearLoad;
	this.endYear = this.endYearLoad;
	this.DateMode = this.pickMode["day"];//复位
	this.lang = 0;//0(中文) | 1(英文)
	this.sample = "yyyy-MM-dd";//yyyy-MM-dd HH:mm:ss
	this.format = "yyyy-MM-dd";//yyyy-MM-dd HH:mm:ss
	this.selectTime = new Date();
	this.dateControl = null;
	this.panel = null;
	this.container = null;
}

CalendarHelper.prototype = {
	/**
	 * 返回日期
	 * @param d the delimiter
	 * @param p the pattern of your date
	 */
	toDate:function(str, style) {
		if(str == null) return new Date();
		try {
			if(str.length == style.length)
			{
				var y = str.substring(style.indexOf('yyyy'), style.indexOf('yyyy') + 4);//年
				var M = str.substring(style.indexOf('MM'), style.indexOf('MM') + 2);//月
				var d = str.substring(style.indexOf('dd'), style.indexOf('dd') + 2);//日
				var H = str.substring(style.indexOf('HH'), style.indexOf('HH') + 2);//时
				var m = str.substring(style.indexOf('mm'), style.indexOf('mm') + 2);//分
				var s = str.substring(style.indexOf('ss'), style.indexOf('ss') + 2);//秒
				if((s == null || s == "" || isNaN(s))) {s = new Date().getSeconds();}
				if((m == null || m == "" || isNaN(m))) {m = new Date().getMinutes();}
				if((H == null || H == "" || isNaN(H))) {H = new Date().getHours();}
				if((d == null || d == "" || isNaN(d))) {d = new Date().getDate();}
				if((M == null || M == "" || isNaN(M))) {M = new Date().getMonth()+1;}
				if((y == null || y == "" || isNaN(y))) {y = new Date().getFullYear();}
				if(y < 1000) {y = new Date().getFullYear();}
				var dt;
				eval("dt = new Date('" + y + "', '" + (M - 1) + "','" + d + "','" + H + "','" + m + "','" + s + "')");
				//dt.setMilliseconds(new Date().getMilliseconds());
				return dt;
			}
			return new Date();
		}
		catch(e) {
			//alert(e.name + e.message);
			return new Date();
		}
	},
	//取出指定日期是该年的第几周
	getWeekOfYear:function(currDate) {
		var cd = new Date(currDate.getFullYear(), 0, 1);//当前1月1日
		var cDay = parseInt(Math.abs(currDate - cd)/(1000 * 60 * 60 * 24)) + 1;//计算当前时间是今年第几天
		var _w = (cDay + cd.getDay()) / 7;//补上今年第一周不足七天的时间
		var _wInt = parseInt(_w);
		return ((_w > _wInt) ? (_wInt + 1) : _wInt);//返回当前是今年第几周
	},
	/**
	 * 格式化日期
	 * @param d the delimiter
	 * @param p the pattern of your date
	 */
	formatDate:function(date, style) {
		var o = {
			"M{2}":((this.DateMode <= 5) ? (date.getMonth() + 1) : "MM"),
			"d{2}":((this.DateMode <= 4) ? (date.getDate()) : "dd"),
			"H{2}":((this.DateMode <= 3) ? (date.getHours()) : "HH"),
			"m{2}":date.getMinutes(),
			"s{2}":date.getSeconds(),
			"S{3}":(new Date().getMilliseconds()),
			"W{1}":((this.DateMode <= 4) ? (this.getWeekOfYear(date)) : "W"),
			"w{1}":((this.DateMode <= 4) ? (this.language["weeks"][this.lang][date.getDay()]) : "w"),
			"q{1}":((this.DateMode <= 5) ? (this.language["quarter"][this.lang][date.getMonth()]) : "q")
		};
		if(/(y{4})/.test(style)) {
			style = style.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
		}
		for(var k in o) {
			if(new RegExp("("+ k +")").test(style)) {
				style = style.replace(RegExp.$1,
						RegExp.$1.length == 1 ? o[k] :
							(RegExp.$1.length == 3 ? ("000" + o[k]).substr(("" + o[k]).length) :
									("00" + o[k]).substr(("" + o[k]).length)
							)
						);
			}
		}
		return style;
	},
	//初始化容器
	InitContainerPanel:function() {
		var str = '<div id="__WebCalendarPanel__" style="position:absolute;display:none;z-index:9999;" class="CalendarPanel"></div>';
		if(document.all) {
			str += '<iframe style="position:absolute; z-index:2000; width:expression(this.previousSibling.offsetWidth); ';
			str += 'height:expression(this.previousSibling.offsetHeight); ';
			str += 'left:expression(this.previousSibling.offsetLeft); top:expression(this.previousSibling.offsetTop); ';
			str += 'display:expression(this.previousSibling.style.display); " scrolling="no" frameborder="no"></iframe>';
		}
		var div = document.createElement("div");
		div.innerHTML = str;
		div.id = "__WebCalendarContainerPanel__";
		div.style.display = "none";
		document.body.appendChild(div);//确保日历容器节点在 body 最后，否则 FireFox 中不能出现在最上方
	},
	//返回所选日期
	ReturnDate:function(dt, sd) {
		if(this.dateControl != null) {
			this.dateControl.value = dt;
			this.dateControl.showTime = sd;
		}
		this.hide();
		if(this.dateControl.onchange == null) {return;}
		//将onchange转成其它函数，以免触发验证事件
		var ev = this.dateControl.onchange.toString();//找出函数的字串
		ev = ev.substring(((ev.indexOf("ValidatorOnChange(); ") > 0) ? ev.indexOf("ValidatorOnChange();") + 20 : ev.indexOf("{") + 1), ev.lastIndexOf("}"));//去除验证函数 ValidatorOnChange();
		var fun = new Function(ev);//重新定义函数
		this.dateControl.changeEvent = fun;
		this.dateControl.changeEvent();//触发自定义 changeEvent 函数
	},
	draw:function() {
		var calendar = this;
		var mvAry = [];
		mvAry[mvAry.length] = '<div style="margin: 0px; ">';
		//start
		//------------------------------放置上一月、年、月、下一月按钮------------------------------
		mvAry[mvAry.length] = '<table width="100%" cellpadding="0" cellspacing="1" class="CalendarTop">';
		mvAry[mvAry.length] = '<tr class="title">';
		
		mvAry[mvAry.length] = '<th align="left" class="prevMonth"><input style="';
		if(calendar.DateMode > calendar.pickMode["month"]) {mvAry[mvAry.length] = 'display:none; ';}//精确到年时隐藏“月”
		mvAry[mvAry.length] ='" id="__WebCalendarPrevMonth__" type="button" value="&lt;" /></th>';
		
		mvAry[mvAry.length] = '<th align="center" width="98%" nowrap="nowrap" class="YearMonth">';
		mvAry[mvAry.length] = '<select id="__WebCalendarYear__" class="Year"></select>';
		mvAry[mvAry.length] = '<select id="__WebCalendarMonth__" class="Month" style="';
		if(calendar.DateMode > calendar.pickMode["month"]) {mvAry[mvAry.length] = 'display:none;';}//精确到年时隐藏“月”
		mvAry[mvAry.length] = '"></select></th>';
		
		mvAry[mvAry.length] = '<th align="right" class="nextMonth"><input style="';
		if(calendar.DateMode > calendar.pickMode["month"]) {mvAry[mvAry.length] = 'display:none;';}//精确到年时隐藏“月”
		mvAry[mvAry.length] ='" id="__WebCalendarNextMonth__" type="button" value="&gt;" /></th>';
		
		mvAry[mvAry.length] = '</tr>';
		mvAry[mvAry.length] = '</table>';
		
		//------------------------------放置日期------------------------------
		mvAry[mvAry.length] = '<table id="__WebCalendarTable__" width="100%" class="CalendarDate" style="';
		if(calendar.DateMode >= calendar.pickMode["month"]) {mvAry[mvAry.length] = 'display:none;';}//精确到年、月时隐藏“天”
		mvAry[mvAry.length] = '" cellpadding="0" cellspacing="1">';
		mvAry[mvAry.length] = '<tr class="title">';
		for(var i = 0; i < 7; i++) {
			mvAry[mvAry.length] = '<th>' + calendar.language["weeks"][calendar.lang][i] + '</th>';
		}
		mvAry[mvAry.length] = '</tr>';
		for(var i = 0; i < 6; i++) {
			mvAry[mvAry.length] = '<tr align="center" class="date" style="display:;">';
			for(var j = 0; j < 7; j++) {
				if(j == 0) {
					mvAry[mvAry.length] = '<td class="sun" tdname="tdSun" class="sun"></td>';
				}
				else if(j == 6) {
					mvAry[mvAry.length] = '<td class="sat" tdname="tdSat" class="sat"></td>';
				}
				else {
					mvAry[mvAry.length] = '<td class="day" tdname="tdDay" class="day"></td>';
				}
			}
			mvAry[mvAry.length] = '</tr>';
		}
		mvAry[mvAry.length] = '</table>';
		//------------------------------放置时间的行------------------------------
		mvAry[mvAry.length] = '<table width="100%" class="CalendarTime" style="';
		if(calendar.DateMode >= calendar.pickMode["day"]) {mvAry[mvAry.length] = 'display:none;';}//精确到时日隐藏“时间”
		mvAry[mvAry.length] = '" cellpadding="0" cellspacing="1">';
		mvAry[mvAry.length] = '<tr><td align="center" colspan="7">';
		mvAry[mvAry.length] = '<select id="__WebCalendarHour__" class="Hour"></select>' + calendar.language["hour"][calendar.lang];
		mvAry[mvAry.length] = '<span style="';
		if(calendar.DateMode >= calendar.pickMode["hour"]) {mvAry[mvAry.length] = 'display:none;';}//精确到小时时隐藏“分”
		mvAry[mvAry.length] = '"><select id="__WebCalendarMinute__" class="Minute"></select>' + calendar.language["minute"][calendar.lang]+'</span>';
		mvAry[mvAry.length] = '<span style="';
		if(calendar.DateMode >= calendar.pickMode["minute"]) {mvAry[mvAry.length] = 'display:none;';}//精确到小时、分时隐藏“秒”
		mvAry[mvAry.length] = '"><select id="__WebCalendarSecond__" class="Second"></select>'+ calendar.language["second"][calendar.lang]+'</span>';
		mvAry[mvAry.length] = '</td></tr>';
		mvAry[mvAry.length] = '</table>';
		
		mvAry[mvAry.length] = '<div align="center" class="CalendarButtonDiv">';
		mvAry[mvAry.length] = '<input id="__WebCalendarClear__" type="button" value="' + calendar.language["clear"][calendar.lang] + '"/> ';
		mvAry[mvAry.length] = '<input id="__WebCalendarToday__" type="button" value="';
		mvAry[mvAry.length] = (calendar.DateMode == calendar.pickMode["day"]) ? calendar.language["today"][calendar.lang] : calendar.language["pickTxt"][calendar.lang];
		mvAry[mvAry.length] = '" /> ';
		mvAry[mvAry.length] = '<input id="__WebCalendarClose__" type="button" value="' + calendar.language["close"][calendar.lang] + '" />';
		mvAry[mvAry.length] = '</div>';
		
		mvAry[mvAry.length] = '</div>';
		//end
		calendar.panel.innerHTML = mvAry.join("");
		
		var obj = calendar.getElementById("__WebCalendarPrevMonth__");
		obj.onclick = function() {calendar.goPrevMonth(calendar);};
		obj.onblur = function() {calendar.onblur();};
		calendar.prevMonth = obj;
		
		obj = calendar.getElementById("__WebCalendarNextMonth__");
		obj.onclick = function() {calendar.goNextMonth(calendar);};
		obj.onblur = function() {calendar.onblur();};
		calendar.nextMonth= obj;
		
		obj = calendar.getElementById("__WebCalendarClear__");
		obj.onclick = function() {calendar.ReturnDate("", "");};
		calendar.calendarClear = obj;
		
		obj = calendar.getElementById("__WebCalendarClose__");
		obj.onclick = function() {calendar.hide();};
		calendar.calendarClose = obj;
		
		obj = calendar.getElementById("__WebCalendarYear__");
		obj.onchange = function() {calendar.update(calendar);};
		obj.onblur = function() {calendar.onblur();};
		calendar.calendarYear = obj;
		
		obj = calendar.getElementById("__WebCalendarMonth__");
		with(obj) {
			onchange = function() {calendar.update(calendar);};
			onblur = function() {calendar.onblur();};
		}
		calendar.calendarMonth = obj;
		
		obj = calendar.getElementById("__WebCalendarHour__");
		obj.onchange = function() {calendar.hour = this.options[this.selectedIndex].value;};
		obj.onblur = function() {calendar.onblur();};
		calendar.calendarHour = obj;
		
		obj = calendar.getElementById("__WebCalendarMinute__");
		obj.onchange = function() {calendar.minute = this.options[this.selectedIndex].value;};
		obj.onblur = function() {calendar.onblur();};
		calendar.calendarMinute = obj;
		
		obj = calendar.getElementById("__WebCalendarSecond__");
		obj.onchange = function() {calendar.second = this.options[this.selectedIndex].value;};
		obj.onblur = function() {calendar.onblur();};
		calendar.calendarSecond = obj;
		
		obj = calendar.getElementById("__WebCalendarToday__");
		obj.onclick = function() {
			var today = 
			(calendar.DateMode != calendar.pickMode["day"])
					? new Date(calendar.year, calendar.month, calendar.day, calendar.hour, calendar.minute, calendar.second)
					: new Date();
			calendar.ReturnDate(calendar.formatDate(today, calendar.format), calendar.formatDate(today, calendar.sample));
		};
		calendar.calendarToday = obj;
	},
	//年份下拉框绑定数据
	bindYear:function() {
		var cy = this.calendarYear;
		cy.length = 0;
		for(var i = this.beginYear; i <= this.endYear; i++) {
			cy.options[cy.length] = new Option(i + this.language["year"][this.lang], i);
		}
	},
	//月份下拉框绑定数据
	bindMonth:function() {
		var cm = this.calendarMonth;
		cm.length = 0;
		for(var i = 0; i < 12; i++) {
			cm.options[cm.length] = new Option(this.language["months"][this.lang][i], i);
		}
	},
	//小时下拉框绑定数据
	bindHour:function() {
		var ch = this.calendarHour;
		if(ch.length > 0) {return;}
		var H;
		for(var i = 0; i < 24; i++) {
			H = ("00" + i + "").substr(("" + i).length);
			ch.options[ch.length] = new Option(H, H);
		}
	},
	//分钟下拉框绑定数据
	bindMinute:function() {
		var cM = this.calendarMinute;
		if(cM.length > 0) {return;}
		var M;
		for(var i = 0; i < 60; i++) {
			M = ("00" + i + "").substr(("" + i).length);
			cM.options[cM.length] = new Option(M, M);
		}
	},
	//秒钟下拉框绑定数据
	bindSecond:function() {
		var cs = this.calendarSecond;
		if(cs.length > 0) {return;}
		var s;
		for(var i = 0; i < 60; i++) {
			s = ("00" + i + "").substr(("" + i).length);
			cs.options[cs.length] = new Option(s, s);
		}
	},
	//向前一月
	goPrevMonth:function(e) {
		if(this.year == this.beginYear && this.month == 0) {return;}
		this.month--;
		if(this.month == -1) {
			this.year--;
			this.month = 11;
		}
		this.date = new Date(this.year, this.month, 1);
		this.changeSelect();
		this.bindData();
	},
	//向后一月
	goNextMonth:function(e) {
		if(this.year == this.endYear && this.month == 11) {return;}
		this.month++;
		if(this.month == 12) {
			this.year++;
			this.month = 0;
		}
		this.date = new Date(this.year, this.month, 1);
		this.changeSelect();
		this.bindData();
	},
	//改变SELECT选中状态
	changeSelect:function() {
		var calendar = this;
		var cy = calendar.calendarYear;
		var cm = calendar.calendarMonth;
		var ch = calendar.calendarHour;
		var cM = calendar.calendarMinute;
		var cs = calendar.calendarSecond;
		//当初始值为空时,若有效年份并不包括今天时将有可能超出索引位置
		if(calendar.date.getFullYear() - calendar.beginYear < 0 || calendar.date.getFullYear() - calendar.beginYear >= cy.length) {
			cy[0].selected = true;
		}
		else {
			cy[calendar.date.getFullYear() - calendar.beginYear].selected = true;
		}
		cm[calendar.date.getMonth()].selected = true;
		//初始化时间的值
		ch[calendar.hour].selected = true;
		cM[calendar.minute].selected = true;
		cs[calendar.second].selected = true;
	},
	//更新年、月
	update:function(e) {
		this.year = e.calendarYear.options[e.calendarYear.selectedIndex].value;
		this.month = e.calendarMonth.options[e.calendarMonth.selectedIndex].value;
		this.date = new Date(this.year, this.month, 1);
		this.bindData();
	},
	//绑定数据到月视图
	bindData:function() {
		var calendar = this;
		if(calendar.DateMode >= calendar.pickMode["month"]) {return;}
		var dateArray = calendar.getMonthViewArray(calendar.date.getFullYear(), calendar.date.getMonth());
		var tds = calendar.getElementById("__WebCalendarTable__").getElementsByTagName("td");
		for(var i = 0; i < tds.length; i++) {
			tds[i].onclick = function() {return;};
			tds[i].onmouseover = function() {return;};
			tds[i].onmouseout = function() {return;};
			if(i > dateArray.length - 1) break;
			tds[i].innerHTML = dateArray[i];
			//还原样式
			if(tds[i].getAttribute("tdname") == "tdSun") {
				tds[i].className = "sun";
			}
			else if(tds[i].getAttribute("tdname") == "tdSat") {
				tds[i].className = "sat";
			}
			else {
				tds[i].className = "day";
			}
			
			if(dateArray[i] != "  ") {
				var cur = new Date();
				tds[i].isToday = false;//初始化
				if(cur.getFullYear() == calendar.date.getFullYear() && cur.getMonth() == calendar.date.getMonth() && cur.getDate() == dateArray[i]) {
					//是今天的单元格
					tds[i].className = "today";
					tds[i].isToday = true;
				}
				if(calendar.dateControl != null) {
					if(calendar.selectTime.getDate() == dateArray[i]) {
						//是已被选中的单元格
						calendar.selectedDayTD = tds[i];
						tds[i].className = "selDay";
					}
				}
				tds[i].onclick = function() {
					if(calendar.DateMode == calendar.pickMode["day"]) {//当选择日期时，点击格子即返回值
						var tmpDate = new Date(calendar.date.getFullYear(), calendar.date.getMonth(), this.innerHTML);
						calendar.ReturnDate(calendar.formatDate(tmpDate, calendar.format), calendar.formatDate(tmpDate, calendar.sample));
					}
					else {
						if(calendar.selectedDayTD != null) {//清除已选中的背景色
							if(calendar.selectedDayTD.isToday) {
								calendar.selectedDayTD.className = "today";
							}
							else {
								if(calendar.selectedDayTD.getAttribute("tdname") == "tdSun") {
									calendar.selectedDayTD.className = "sun";
								}
								else if(calendar.selectedDayTD.getAttribute("tdname") == "tdSat") {
									calendar.selectedDayTD.className = "sat";
								}
								else {
									calendar.selectedDayTD.className = "day";
								}
							}
						}
						this.className = "selDay";
						calendar.day = this.innerHTML;
						calendar.selectedDayTD = this;//记录已选中的日子
					}
				};
				tds[i].onmouseover = function() {
					this.className = "dayOver";
				};
				tds[i].onmouseout = function() {
					if(calendar.selectedDayTD != this) {
						if(this.isToday) {
							this.className = "today";
						}
						else {
							if(this.getAttribute("tdname") == "tdSun") {
								this.className = "sun";
							}
							else if(this.getAttribute("tdname") == "tdSat") {
								this.className = "sat";
							}
							else {
								this.className = "day";
							}
						}
					}
					else {
						this.className = "selDay";
					}
				};
				tds[i].onblur = function() {calendar.onblur();};
			}
		}
	},
	//根据年、月得到月视图数据(数组形式)
	getMonthViewArray:function(y, m) {
		var mvArray = [];
		var dayOfFirstDay = new Date(y, m, 1).getDay();
		var daysOfMonth = new Date(y, m + 1, 0).getDate();
		for(var i = 0; i < 42; i++) {
			mvArray[i] = "  ";
		}
		for(var i = 0; i < daysOfMonth; i++) {
			mvArray[i + dayOfFirstDay] = i + 1;
		}
		return mvArray;
	},
	//扩展 document.getElementById(id) 多浏览器兼容性
	getElementById:function(id) {
		if(typeof(id) != "string" || id == "") return null;
		if(document.getElementById) return document.getElementById(id);
		if(document.all) return document.all(id);
		try {return eval(id);}
		catch(e) {return null;}
	},
	//扩展 object.getElementsByTagName(tagName)
	getElementsByTagName:function(object, tagName) {
		if(document.getElementsByTagName) return document.getElementsByTagName(tagName);
		if(document.all) return document.all.tags(tagName);
	},
	//取得HTML控件绝对位置
	getAbsPoint:function(e) {
		var x = e.offsetLeft;
		var y = e.offsetTop;
		while(e = e.offsetParent){
			x += e.offsetLeft;
			y += e.offsetTop;
		}
		return {"x": x, "y": y};
	},
	reset:function(args) {
		//补充未定义的args
		if(args.beginYear == null) {args.beginYear = this.beginYearLoad;}
		if(args.endYear == null) {args.endYear = this.endYearLoad;}
		if(args.lang == null) {args.lang = 0;}
		if(args.left == null) {args.left = 0;}
		if(args.top == null) {args.top = 0;}
		if(args.format == null) {args.format = "yyyy-MM-dd";}
		if(args.sample == null) {args.sample = "yyyy-MM-dd";}
		args.format = args.format + "";
		args.sample = args.sample + "";
		return args;
	},
	setSkin:function(args) {
		var hasSkin = false;
		if(args.skin == null) args.skin = "default";
		args.skin = args.skin + "";
		//初始化加载所有皮肤对象到数组中，避免每次都进行循环
		if(this.skinList.length == 0)
		{
			var linkList = document.getElementsByTagName("link");
			for(var i = 0; i < linkList.length; i++) {
				if(linkList[i].getAttribute("skinType") == "WebCalendar") {
					this.skinList[this.skinList.length] = linkList[i];
				}
			}
		}
		for(var i = 0; i <this.skinList.length; i++) {
			if(this.skinList[i].getAttribute("skin") == args.skin) {//加载过该皮肤
				this.skinList[i].disabled = false;
				hasSkin = true;//标记已匹配一种皮肤
			}
			else {
				this.skinList[i].disabled = true;
			}
		}
		if(!hasSkin) {//该皮肤未在skinList中找到，则显示默认皮肤
			for(var i = 0; i <this.skinList.length; i++) {
				if(this.skinList[i].getAttribute("skin") == "default") {//加载过该皮肤
					this.skinList[i].disabled = false;
					break;
				}
			}
		}
	},
	//设置日历显示内容
	setPickMode:function(sample) {
		if(sample.indexOf('ss') > -1) {this.DateMode = this.pickMode["second"];return true;}//秒
		if(sample.indexOf('mm') > -1) {this.DateMode = this.pickMode["minute"];return true;}//分
		if(sample.indexOf('HH') > -1) {this.DateMode = this.pickMode["hour"];return true;}//时
		if(sample.indexOf('dd') > -1) {this.DateMode = this.pickMode["day"];return true;}//日
		if(sample.indexOf('MM') > -1) {this.DateMode = this.pickMode["month"];return true;}//月
		if(sample.indexOf('yyyy') > -1) {this.DateMode = this.pickMode["year"];return true;}//年
		this.DateMode = this.pickMode["day"];//复位
		return true;
	},
	//更新值并判断是否需要初始化
	doDraw:function(args) {
		if(this.skinLoad) this.setSkin(args);//加载皮肤CSS,注释掉即使用默认皮肤
		var isChange = false;
		args = this.reset(args);
		if(this.beginYear != args.beginYear
				|| this.endYear != args.endYear
				|| this.lang != args.lang
				|| this.sample != args.sample
				|| this.format != args.format) {//判断是否有值出现变动
			isChange = true;
		}
		this.left = args.left;
		this.top = args.top;
		if(isChange) {//更新值
			this.beginYear = args.beginYear;
			this.endYear = args.endYear;
			this.lang = args.lang;
			this.setPickMode(args.sample);//设置日历显示内容
			this.format = args.format;
			this.sample = args.sample;
		}
		return isChange;
	},
	//显示日历
	showCalendar:function(dateObj, args, popControl) {
		if(document.getElementById("__WebCalendarContainerPanel__") == null) {
			this.InitContainerPanel();
			//初始化
			this.panel = this.getElementById("__WebCalendarPanel__");
			this.container = this.getElementById("__WebCalendarContainerPanel__");
		}
		var isChange = this.doDraw(args);
		if(dateObj == null) {
			throw new Error("arguments[0] is necessary");
		}
		if(dateObj.showTime == null) dateObj.showTime = "";//初始化一个时间,用于记录选择日历的时间
		this.dateControl = dateObj;
		
		var hasTime = ((dateObj.value.length > 0) ? (this.toDate(dateObj.value, this.sample)) : (new Date()));//若不为空则尝试初始化当前内容
		this.selectTime = ((dateObj.showTime.length > 0) ? (this.toDate(dateObj.showTime ,this.sample)) : hasTime);//若不为空则初始化日期
		
		this.date = this.selectTime;//初始化时显示的时间
		if(this.panel.innerHTML == "" || isChange) {//构造表格，若请示的样式改变，则重新初始化
			this.draw();
			this.bindYear();
			this.bindMonth();
			this.bindHour();
			this.bindMinute();
			this.bindSecond();
		}
		this.year = this.date.getFullYear();
		this.month = this.date.getMonth();
		this.day = this.date.getDate();
		this.hour = this.date.getHours();
		this.minute = this.date.getMinutes();
		this.second = this.date.getSeconds();
		this.changeSelect();
		this.bindData();
		if(popControl == null) {
			popControl = dateObj;
		}
		var xy = this.getAbsPoint(popControl);
		this.panel.style.left = (xy.x + this.left) + "px";//自定义偏移量
		this.panel.style.top = (xy.y + this.top + dateObj.offsetHeight) + "px";
		this.panel.style.display = "";
		this.container.style.display = "";
		
		var calendar = this;
		if(!calendar.dateControl.isTransEvent) {
			calendar.dateControl.isTransEvent = true;
			//保存主文本框的 onblur ，使其原本的事件不被覆盖
			if(calendar.dateControl.onblur != null) {
				calendar.dateControl.blurEvent = calendar.dateControl.onblur;
			}
			calendar.dateControl.onblur = function() {
				calendar.onblur();
				if(typeof(this.blurEvent) == 'function') {
					this.blurEvent();
				}
			};
		}
		calendar.container.onmouseover = function() {calendar.isFocus = true;};
		calendar.container.onmouseout = function() {calendar.isFocus = false;};
	},
	//隐藏日历
	hide:function() {
		this.panel.style.display = "none";
		this.container.style.display = "none";
		this.isFocus = false;
	},
	//焦点转移时隐藏日历
	onblur:function() {
		if(!(this.isFocus)) {this.hide();}
	},
	//画出日历：
	show:function(args0, args1) {
		//优先调用args1中的对象
		if(args1.object != null){
			this.showCalendar(args1.object, args1);
			return true;
		}
		if(args1.id != null) {
			args1.id = args1.id + "";
			var obj = document.getElementById(args1.id);
			if(obj != null) {
				this.showCalendar(obj, args1);
				return true;
			}
		}
		if(typeof(args0) == 'object'){
			this.showCalendar(args0, args1);
			return true;
		}
		else if(typeof(args0) == 'string') {
			var obj = document.getElementById(args0);
			if(obj == null) {return false;}
			//obj.focus();if(obj.click != null) {obj.click();} else {this.showCalendar(obj, args);}
			this.showCalendar(obj, args1);
			return true;
		}
		return false;
	}
}

/**
 * 调用方法：
 * 生成一个对象，默认已经生成下面对象
 * var __Calendar__ = new CalendarHelper();
 * 调用方式：__Calendar__.show(args1, args2);
 * 1、参数args1：目前支持参数：input对象 或 'input的ID'
 * 2、参数args2：默认为{}
 * 目前支持参数：
 * 其中日历类中的参数与其默认值：{skin:"default",beginYear:1950,endYear:2050,lang:0,format:"yyyy-MM-dd HH:mm:ss",left:0,top:0,simple:"yyyy-MM-dd HH:mm:ss"}
 * 日期选择显示格式：yyyy→年，MM→月，dd→天，HH→24小时制，mm→分钟，ss→秒
 * @param object/id args1替代参数：{object:input对象} 或 {id:'input的ID'}上面未列出
 * @param skin String "default" 值为变量__WebCalendarConfig__的skinList中配置的皮肤
 * @param sample String 取值 "yyyy-MM-dd HH:mm:ss" 或 "yyyy-MM-dd HH:mm" 或 "yyyy-MM-dd HH" 或 "yyyy-MM-dd" 或 "yyyy-MM" 或 "yyyy"
 * @param beginYear Integer 大于1000小于9999 如1950
 * @param endYear Integer 大于1000小于9999 如2050
 * @param lang Integer 0(中文)|1(英语) 可自由扩充
 * @param left Integer 相对X坐标,相对于文本框的横向偏移量
 * @param top Integer 相对Y坐标,相对于文本框的纵向偏移量
 * @param format String "yyyy-MM-dd HH:mm:ss.SSS w q W"
 * 格式（注意大小写并且存在多个相同格式时只匹配第一个）：yyyy→年，MM→月，dd→天，HH→24小时制，mm→分钟，ss→秒，SSS→毫秒，w→周几，W→当年的第几周，q→第(1/2/3/4)季
 * 例子：
 *    <input type="text" onclick="__Calendar__.show(this, {})" />
 *    <input type="text" onclick="__Calendar__.show(this, {lang:1, beginYear:2000, endYear:2050})" />
 *    <input type="text" onclick="__Calendar__.show({object:this})" />
 *    <input type="text" onclick="__Calendar__.show({object:this, skin:'gray', left:10, top:-25})" />
 */
var __Calendar__ = new CalendarHelper();
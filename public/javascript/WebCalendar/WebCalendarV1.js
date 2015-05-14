/**
 * ������ (���÷����鿴���·�)
 * @version 1
 * @author skey_chen 2009-03-20 02:48
 * @email skey_chen@163.com
 */
function CalendarHelper() {
	//private
	this.pickMode = {"second":1, "minute":2, "hour":3, "day":4, "month":5, "year":6};
	//���԰�����������չ
	this.language = {
		"year":[
			[""],
			[""]
		],
		"months":[
			["һ��","����","����","����","����","����","����","����","����","ʮ��","ʮһ��","ʮ����"],
			["JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC"]
		],
		"weeks":[
			["��","һ","��","��","��","��","��"],
			["SUN","MON","TUR","WED","THU","FRI","SAT"]
		],
		//���ǵ�ÿ���ط��ļ��ڷֲ���һ��(1��-12��)�����й�������3-5�� etc.
		"quarter":[
			["��", "��", "��", "��", "��", "��", "��", "��", "��", "��", "��", "��"],
			["SPRING", "SPRING", "SPRING", "SUMMER", "SUMMER", "SUMMER", "AUTUMN", "AUTUMN", "AUTUMN", "WINTER", "WINTER", "WINTER"]
		],
		"hour":[
			["ʱ"],
			["H"]
		],
		"minute":[
			["��"],
			["M"]
		],
		"second":[
			["��"],
			["S"]
		],
		"clear":[
			["���"],
			["CLS"]
		],
		"today":[
			["����"],
			["TODAY"]
		],
		//��ȷ���ꡢ��ʱ�ѽ����ɡ�ȷ����
		"pickTxt":[
			["ȷ��"],
			["OK"]
		],
		"close":[
			["�ر�"],
			["CLOSE"]
		]
	};
	//public ��ʼ��
	this.date = new Date();
	this.year = this.date.getFullYear();
	this.month = this.date.getMonth();
	this.day = this.date.getDate();
	this.hour = this.date.getHours();
	this.minute = this.date.getMinutes();
	this.second = this.date.getSeconds();
	this.left = 0;
	this.top = 0;
	this.isFocus = false;//�Ƿ�Ϊ����
	this.beginYearLoad = this.year - 30;
	this.endYearLoad = this.year + 20;
	this.beginYear = this.beginYearLoad;
	this.endYear = this.endYearLoad;
	this.DateMode = this.pickMode["second"];//��λ
	this.lang = 0;//0(����) | 1(Ӣ��)
	this.format = "yyyy-MM-dd";
	this.dateControl = null;
	this.panel = null;
	this.container = null;
}

CalendarHelper.prototype = {
	//toDate�������ӷ���,�����ַ��滻��"w"
	replaceWeeks:function(str, style) {
		var wIndex = style.indexOf("w");
		var wlen = this.language["weeks"][this.lang].length;
		var _tmp = -1;//��ʱ����
		for(var i = 0; i < wlen; i++) {
			_tmp = str.indexOf(this.language["weeks"][this.lang][i]);
			if(_tmp > -1)
			{
				if(_tmp == wIndex) {
					var _tmpLength = this.language["weeks"][this.lang][i].length;
					return str.substring(0, wIndex) + "w" + str.substring(wIndex + _tmpLength, str.length);
				}
			}
		}
		return str;
	},
	//toDate�������ӷ���,�������ַ��滻��"q"
	replaceQuarter:function(str, style) {
		var qlen = this.language["quarter"][this.lang].length;
		var qIndex = style.indexOf("q");
		var _tmp = -1;//��ʱ����
		for(var i = 0; i < qlen; i++) {
			_tmp = str.indexOf(this.language["quarter"][this.lang][i]);
			if(_tmp > -1)
			{
				if(_tmp == qIndex) {
					var _tmpLength = this.language["quarter"][this.lang][i].length;
					return str.substring(0, qIndex) + "q" + str.substring(qIndex + _tmpLength, str.length);
				}
			}
		}
		return str;
	},
	/**
	 * ��������
	 * @param d the delimiter
	 * @param p the pattern of your date
	 */
	toDate:function(str, style) {
		if(str == null) return new Date();
		try {
			//�������ܻ򼾽�ʱ�ַ���������format��ʽ�ĳ���һ��������ͬ�����ܻ򼾽ڻ�ԭΪ��ĸ
			if(style.indexOf("w") > -1 || style.indexOf("q") > -1) {
				var wIndex = style.indexOf("w");
				var qIndex = style.indexOf("q");
				var _tmp = -1;//��ʱ����
				var _tmpLength = 0;//��ʱ����
				if(wIndex > -1 && qIndex > -1) {//ͬʱ����
					if(wIndex > qIndex) {//������ǰ��ʱ
						str = this.replaceQuarter(str, style);
						str = this.replaceWeeks(str, style);
					}
					else {
						str = this.replaceWeeks(str, style);
						str = this.replaceQuarter(str, style);
					}
				}
				else if(wIndex > -1) {
					str = this.replaceWeeks(str, style);
				}
				else if(qIndex > -1) {
					str = this.replaceQuarter(str, style);
				}
			}
			if(str.length == style.length)
			{
				var y = str.substring(style.indexOf('yyyy'), style.indexOf('yyyy') + 4);//��
				var M = str.substring(style.indexOf('MM'), style.indexOf('MM') + 2);//��
				var d = str.substring(style.indexOf('dd'), style.indexOf('dd') + 2);//��
				var H = str.substring(style.indexOf('HH'), style.indexOf('HH') + 2);//ʱ
				var m = str.substring(style.indexOf('mm'), style.indexOf('mm') + 2);//��
				var s = str.substring(style.indexOf('ss'), style.indexOf('ss') + 2);//��
				if((s == null || s == "" || isNaN(s))) {s = new Date().getSeconds();}
				if((m == null || m == "" || isNaN(m))) {m = new Date().getMinutes();}
				if((H == null || H == "" || isNaN(H))) {H = new Date().getHours();}
				if((d == null || d == "" || isNaN(d))) {d = new Date().getDate();}
				if((M == null || M == "" || isNaN(M))) {M = new Date().getMonth()+1;}
				if((y == null || y == "" || isNaN(y))) {y = new Date().getFullYear();}
				if(y < 1000) {y = new Date().getFullYear();}
				var dt ;
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
	/**
	 * ��ʽ������
	 * @param d the delimiter
	 * @param p the pattern of your date
	 */
	formatDate:function(date, style) {
		var o = {
			"w{1}":this.language["weeks"][this.lang][date.getDay()],//week��
			"q{1}":this.language["quarter"][this.lang][date.getMonth()],//quarter����
			"M{2}":date.getMonth() + 1,//month
			"d{2}":date.getDate(),//day
			"H{2}":date.getHours(),//hour
			"m{2}":date.getMinutes(),//minute
			"s{2}":date.getSeconds(),//second
			"S{3}":(new Date().getMilliseconds())//millisecond
		}
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
	//ȷ�����������ڵ��� body ��󣬷��� FireFox �в��ܳ��������Ϸ�
	//��ʼ������
	InitContainerPanel:function() {
		var str = '<div id="calendarPanel" style="position:absolute;display:none;z-index:9999;" class="CalendarPanel"></div>';
		if(document.all) {
			str += '<iframe style="position:absolute; z-index:2000; width:expression(this.previousSibling.offsetWidth); ';
			str += 'height:expression(this.previousSibling.offsetHeight); ';
			str += 'left:expression(this.previousSibling.offsetLeft); top:expression(this.previousSibling.offsetTop); ';
			str += 'display:expression(this.previousSibling.style.display); " scrolling="no" frameborder="no"></iframe>';
		}
		var div = document.createElement("div");
		div.innerHTML = str;
		div.id = "ContainerPanel";
		div.style.display = "none";
		document.body.appendChild(div);
	},
	//������ѡ����
	ReturnDate:function(dt) {
		if(this.dateControl != null) {this.dateControl.value = dt;}
		this.hide();
		if(this.dateControl.onchange == null) {return;}
		//��onchangeת���������������ⴥ����֤�¼�
		var ev = this.dateControl.onchange.toString();//�ҳ��������ִ�
		ev = ev.substring(((ev.indexOf("ValidatorOnChange(); ") > 0) ? ev.indexOf("ValidatorOnChange();") + 20 : ev.indexOf("{") + 1), ev.lastIndexOf("}"));//ȥ����֤���� ValidatorOnChange();
		var fun = new Function(ev);//���¶��庯��
		this.dateControl.changeEvent = fun;
		this.dateControl.changeEvent();//�����Զ��� changeEvent ����
	},
	draw:function() {
		var calendar = this;
		var mvAry = [];
		mvAry[mvAry.length] = '<div name="calendarForm" style="margin: 0px; ">';
		//start
		//------------------------------������һ�¡��ꡢ�¡���һ�°�ť------------------------------
		mvAry[mvAry.length] = '<table width="100%" cellpadding="0" cellspacing="1" class="CalendarTop">';
		mvAry[mvAry.length] = '<tr class="title">';
		
		mvAry[mvAry.length] = '<th align="left" class="prevMonth"><input style="';
		if(calendar.DateMode > calendar.pickMode["month"]) {mvAry[mvAry.length] = 'display:none; ';}//��ȷ����ʱ���ء��¡�
		mvAry[mvAry.length] ='" id="prevMonth" name="prevMonth" type="button" value="&lt;" /></th>';
		
		mvAry[mvAry.length] = '<th align="center" width="98%" nowrap="nowrap" class="YearMonth">';
		mvAry[mvAry.length] = '<select name="calendarYear" id="calendarYear" class="Year"></select>';
		mvAry[mvAry.length] = '<select name="calendarMonth" id="calendarMonth" class="Month" style="';
		if(calendar.DateMode > calendar.pickMode["month"]) {mvAry[mvAry.length] = 'display:none;';}//��ȷ����ʱ���ء��¡�
		mvAry[mvAry.length] = '"></select></th>';
		
		mvAry[mvAry.length] = '<th align="right" class="nextMonth"><input style="';
		if(calendar.DateMode > calendar.pickMode["month"]) {mvAry[mvAry.length] = 'display:none;';}//��ȷ����ʱ���ء��¡�
		mvAry[mvAry.length] ='" id="nextMonth" name="nextMonth" type="button" value="&gt;" /></th>';
		
		mvAry[mvAry.length] = '</tr>';
		mvAry[mvAry.length] = '</table>';
		
		//------------------------------��������------------------------------
		mvAry[mvAry.length] = '<table id="calendarTable" width="100%" class="CalendarDate" style="';
		if(calendar.DateMode >= calendar.pickMode["month"]) {mvAry[mvAry.length] = 'display:none;';}//��ȷ���ꡢ��ʱ���ء��족
		mvAry[mvAry.length] = '" cellpadding="0" cellspacing="1">';
		mvAry[mvAry.length] = '<tr class="title">';
		for(var i = 0; i < 7; i++) {
			mvAry[mvAry.length] = '<th>' + calendar.language["weeks"][calendar.lang][i] + '</th>';
		}
		mvAry[mvAry.length] = '</tr>';
		for(var i = 0; i < 6; i++) {
			mvAry[mvAry.length] = '<tr align="center" class="date">';
			for(var j = 0; j < 7; j++) {
				if(j == 0) {
					mvAry[mvAry.length] = '<td class="sun" name="tdSun" class="sun"></td>';
				}
				else if(j == 6) {
					mvAry[mvAry.length] = '<td class="sat" name="tdSat" class="sat"></td>';
				}
				else {
					mvAry[mvAry.length] = '<td class="day" name="tdDay" class="day"></td>';
				}
			}
			mvAry[mvAry.length] = '</tr>';
		}
		mvAry[mvAry.length] = '</table>';
		//------------------------------����ʱ�����------------------------------
		mvAry[mvAry.length] = '<table width="100%" class="CalendarTime" style="';
		if(calendar.DateMode >= calendar.pickMode["day"]) {mvAry[mvAry.length] = 'display:none;';}//��ȷ��ʱ�����ء�ʱ�䡱
		mvAry[mvAry.length] = '" cellpadding="0" cellspacing="1">';
		mvAry[mvAry.length] = '<tr><td align="center" colspan="7">';
		mvAry[mvAry.length] = '<select id="calendarHour" name="calendarHour" class="Hour"></select>' + calendar.language["hour"][calendar.lang];
		mvAry[mvAry.length] = '<span style="'
		if(calendar.DateMode >= calendar.pickMode["hour"]) {mvAry[mvAry.length] = 'display:none;';}//��ȷ��Сʱʱ���ء��֡�
		mvAry[mvAry.length] = '"><select id="calendarMinute" name="calendarMinute" class="Minute"></select>' + calendar.language["minute"][calendar.lang]+'</span>';
		mvAry[mvAry.length] = '<span style="'
		if(calendar.DateMode >= calendar.pickMode["minute"]) {mvAry[mvAry.length] = 'display:none;';}//��ȷ��Сʱ����ʱ���ء��롱
		mvAry[mvAry.length] = '"><select id="calendarSecond" name="calendarSecond" class="Second"></select>'+ calendar.language["second"][calendar.lang]+'</span>';
		mvAry[mvAry.length] = '</td></tr>';
		mvAry[mvAry.length] = '</table>';
		
		mvAry[mvAry.length] = '<div align="center" class="CalendarButtonDiv">';
		mvAry[mvAry.length] = '<input id="calendarClear" name="calendarClear" type="button" value="' + calendar.language["clear"][calendar.lang] + '"/> ';
		mvAry[mvAry.length] = '<input id="calendarToday" name="calendarToday" type="button" value="'
		mvAry[mvAry.length] = (calendar.DateMode == calendar.pickMode["day"]) ? calendar.language["today"][calendar.lang] : calendar.language["pickTxt"][calendar.lang];
		mvAry[mvAry.length] = '" /> ';
		mvAry[mvAry.length] = '<input id="calendarClose" name="calendarClose" type="button" value="' + calendar.language["close"][calendar.lang] + '" />';
		mvAry[mvAry.length] = '</div>';
		
		mvAry[mvAry.length] = '</div>';
		//end
		calendar.panel.innerHTML = mvAry.join("");
		
		var obj = calendar.getElementById("prevMonth");
		obj.onclick = function() {calendar.goPrevMonth(calendar);};
		obj.onblur = function() {calendar.onblur();};
		calendar.prevMonth= obj;
		
		obj = calendar.getElementById("nextMonth");
		obj.onclick = function() {calendar.goNextMonth(calendar);};
		obj.onblur = function() {calendar.onblur();};
		calendar.nextMonth= obj;
		
		obj = calendar.getElementById("calendarClear");
		obj.onclick = function() {calendar.ReturnDate("");};
		calendar.calendarClear = obj;
		
		obj = calendar.getElementById("calendarClose");
		obj.onclick = function() {calendar.hide();};
		calendar.calendarClose = obj;
		
		obj = calendar.getElementById("calendarYear");
		obj.onchange = function() {calendar.update(calendar);};
		obj.onblur = function() {calendar.onblur();};
		calendar.calendarYear = obj;
		
		obj = calendar.getElementById("calendarMonth");
		with(obj) {
			onchange = function() {calendar.update(calendar);};
			onblur = function() {calendar.onblur();};
		}
		calendar.calendarMonth = obj;
		
		obj = calendar.getElementById("calendarHour");
		obj.onchange = function() {calendar.hour = this.options[this.selectedIndex].value;};
		obj.onblur = function() {calendar.onblur();};
		calendar.calendarHour = obj;
		
		obj = calendar.getElementById("calendarMinute");
		obj.onchange = function() {calendar.minute = this.options[this.selectedIndex].value;};
		obj.onblur = function() {calendar.onblur();};
		calendar.calendarMinute = obj;
		
		obj = calendar.getElementById("calendarSecond");
		obj.onchange = function() {calendar.second = this.options[this.selectedIndex].value;};
		obj.onblur = function() {calendar.onblur();};
		calendar.calendarSecond = obj;
		
		obj = calendar.getElementById("calendarToday");
		obj.onclick = function() {
			var today = 
			(calendar.DateMode != calendar.pickMode["day"])
					? new Date(calendar.year, calendar.month, calendar.day, calendar.hour, calendar.minute, calendar.second)
					: new Date();
			calendar.ReturnDate(calendar.formatDate(today, calendar.format));
		};
		calendar.calendarToday = obj;
	},
	//��������������
	bindYear:function() {
		var cy = this.calendarYear;
		cy.length = 0;
		for(var i = this.beginYear; i <= this.endYear; i++) {
			cy.options[cy.length] = new Option(i + this.language["year"][this.lang], i);
		}
	},
	//�·������������
	bindMonth:function() {
		var cm = this.calendarMonth;
		cm.length = 0;
		for(var i = 0; i < 12; i++) {
			cm.options[cm.length] = new Option(this.language["months"][this.lang][i], i);
		}
	},
	//Сʱ�����������
	bindHour:function() {
		var ch = this.calendarHour;
		if(ch.length > 0) {return;}
		var H;
		for(var i = 0; i < 24; i++) {
			H = ("00" + i + "").substr(("" + i).length);
			ch.options[ch.length] = new Option(H, H);
		}
	},
	//���������������
	bindMinute:function() {
		var cM = this.calendarMinute;
		if(cM.length > 0) {return;}
		var M;
		for(var i = 0; i < 60; i++) {
			M = ("00" + i + "").substr(("" + i).length);
			cM.options[cM.length] = new Option(M, M);
		}
	},
	//���������������
	bindSecond:function() {
		var cs = this.calendarSecond;
		if(cs.length > 0) {return;}
		var s;
		for(var i = 0; i < 60; i++) {
			s = ("00" + i + "").substr(("" + i).length);
			cs.options[cs.length] = new Option(s, s);
		}
	},
	//��ǰһ��
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
	//���һ��
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
	//�ı�SELECTѡ��״̬
	changeSelect:function() {
		var calendar = this;
		var cy = calendar.calendarYear;
		var cm = calendar.calendarMonth;
		var ch = calendar.calendarHour;
		var cM = calendar.calendarMinute;
		var cs = calendar.calendarSecond;
		//����ʼֵΪ��ʱ,����Ч��ݲ�����������ʱ���п��ܳ�������λ��
		if(calendar.date.getFullYear() - calendar.beginYear < 0 || calendar.date.getFullYear() - calendar.beginYear >= cy.length) {
			cy[0].selected = true;
		}
		else {
			cy[calendar.date.getFullYear() - calendar.beginYear].selected = true;
		}
		cm[calendar.date.getMonth()].selected = true;
		//��ʼ��ʱ���ֵ
		ch[calendar.hour].selected = true;
		cM[calendar.minute].selected = true;
		cs[calendar.second].selected = true;
	},
	//�����ꡢ��
	update:function(e) {
		this.year = e.calendarYear.options[e.calendarYear.selectedIndex].value;
		this.month = e.calendarMonth.options[e.calendarMonth.selectedIndex].value;
		this.date = new Date(this.year, this.month, 1);
		this.bindData();
	},
	//�����ݵ�����ͼ
	bindData:function() {
		var calendar = this;
		if(calendar.DateMode >= calendar.pickMode["month"]) {return;}
		var dateArray = calendar.getMonthViewArray(calendar.date.getFullYear(), calendar.date.getMonth());
		var tds = calendar.getElementById("calendarTable").getElementsByTagName("td");
		for(var i = 0; i < tds.length; i++) {
			tds[i].onclick = function() {return;};
			tds[i].onmouseover = function() {return;};
			tds[i].onmouseout = function() {return;};
			if(i > dateArray.length - 1) break;
			tds[i].innerHTML = dateArray[i];
			if(dateArray[i] != "  ") {
				if(tds[i].getAttribute("name") == "tdSun") {
					tds[i].className = "sun";
				}
				else if(tds[i].getAttribute("name") == "tdSat") {
					tds[i].className = "sat";
				}
				else {
					tds[i].className = "day";
				}
				var cur = new Date();
				tds[i].isToday = false;//��ʼ��
				if(cur.getFullYear() == calendar.date.getFullYear() && cur.getMonth() == calendar.date.getMonth() && cur.getDate() == dateArray[i]) {
					//�ǽ���ĵ�Ԫ��
					tds[i].className = "today";
					tds[i].isToday = true;
				}
				if(calendar.dateControl != null) {
					cur = calendar.toDate(calendar.dateControl.value, calendar.format);
					if(cur.getFullYear() == calendar.date.getFullYear() && cur.getMonth() == calendar.date.getMonth()&& cur.getDate() == dateArray[i]) {
						//���ѱ�ѡ�еĵ�Ԫ��
						calendar.selectedDayTD = tds[i];
						tds[i].className = "selDay";
					}
				}
				tds[i].onclick = function() {
					if(calendar.DateMode == calendar.pickMode["day"]) {//��ѡ������ʱ��������Ӽ�����ֵ
						calendar.ReturnDate(calendar.formatDate(new Date(calendar.date.getFullYear(), calendar.date.getMonth(), this.innerHTML), calendar.format));
					}
					else {
						if(calendar.selectedDayTD != null) {//�����ѡ�еı���ɫ
							if(calendar.selectedDayTD.isToday) {
								calendar.selectedDayTD.className = "today";
							}
							else {
								if(calendar.selectedDayTD.getAttribute("name") == "tdSun") {
									calendar.selectedDayTD.className = "sun";
								}
								else if(calendar.selectedDayTD.getAttribute("name") == "tdSat") {
									calendar.selectedDayTD.className = "sat";
								}
								else {
									calendar.selectedDayTD.className = "day";
								}
							}
						}
						this.className = "selDay";
						calendar.day = this.innerHTML;
						calendar.selectedDayTD = this;//��¼��ѡ�е�����
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
							if(this.getAttribute("name") == "tdSun") {
								this.className = "sun";
							}
							else if(this.getAttribute("name") == "tdSat") {
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
	//�����ꡢ�µõ�����ͼ����(������ʽ)
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
	//��չ document.getElementById(id) �������������
	getElementById:function(id) {
		if(typeof(id) != "string" || id == "") return null;
		if(document.getElementById) return document.getElementById(id);
		if(document.all) return document.all(id);
		try {return eval(id);}
		catch(e) {return null;}
	},
	//��չ object.getElementsByTagName(tagName)
	getElementsByTagName:function(object, tagName) {
		if(document.getElementsByTagName) return document.getElementsByTagName(tagName);
		if(document.all) return document.all.tags(tagName);
	},
	//ȡ��HTML�ؼ�����λ��
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
		//����δ�����args
		if(args.beginYear == null) {args.beginYear = this.beginYearLoad;}
		if(args.endYear == null) {args.endYear = this.endYearLoad;}
		if(args.lang == null) {args.lang = 0;}
		if(args.left == null) {args.left = 0;}
		if(args.top == null) {args.top = 0;}
		if(args.format == null) {args.format = "yyyy-MM-dd";}
		args.format = args.format + "";
		return args;
	},
	//����ֵ���ж��Ƿ���Ҫ��ʼ��
	doDraw:function(args) {
		var isChange = false;
		args = this.reset(args);
		if(this.beginYear != args.beginYear
				|| this.endYear != args.endYear
				|| this.lang != args.lang
				|| this.format != args.format) {//�ж��Ƿ���ֵ���ֱ䶯
			isChange = true;
		}
		this.left = args.left;
		this.top = args.top;
		//����ֵ
		if(isChange) {
			this.beginYear = args.beginYear;
			this.endYear = args.endYear;
			this.lang = args.lang;
			if(args.format != "yyyy-MM-dd") {
				if(args.format.indexOf('ss') < 0) {this.DateMode = this.pickMode["minute"];}//����Ϊ��
				if(args.format.indexOf('mm') < 0) {this.DateMode = this.pickMode["hour"];}//����Ϊʱ
				if(args.format.indexOf('HH') < 0) {this.DateMode = this.pickMode["day"];}//����Ϊ��
				if(args.format.indexOf('dd') < 0) {this.DateMode = this.pickMode["month"];}//����Ϊ��
				if(args.format.indexOf('MM') < 0) {this.DateMode = this.pickMode["year"];}//����Ϊ��
				if(args.format.indexOf('yyyy') < 0) {this.DateMode = this.pickMode["second"];}//Ĭ�Ͼ���Ϊ��
			}
			else {
				this.DateMode = this.pickMode["second"];//��λ
			}
			this.format = args.format;
		}
		return isChange;
	},
	//��ʾ����
	showCalendar:function(dateObj, args, popControl) {
		if(document.getElementById("ContainerPanel") == null) {
			this.InitContainerPanel();
			//��ʼ��
			this.panel = this.getElementById("calendarPanel");
			this.container = this.getElementById("ContainerPanel");
		}
		var isChange = this.doDraw(args);
		if(dateObj == null) {
			throw new Error("arguments[0] is necessary")
		}
		this.dateControl = dateObj;
		var now = new Date();
		this.date = (dateObj.value.length > 0) ? new Date(this.toDate(dateObj.value ,this.format)) : this.toDate(this.formatDate(now, this.format), this.format);//����Ϊ�������format��ʼ������
		if(this.panel.innerHTML == "" || isChange) {//����������ʾ����ʽ�ı䣬�����³�ʼ��
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
		this.panel.style.left = (xy.x + this.left) + "px";//�Զ���ƫ����
		this.panel.style.top = (xy.y + this.top + dateObj.offsetHeight) + "px";
		this.panel.style.display = "";
		this.container.style.display = "";
		
		var calendar = this;
		if( !calendar.dateControl.isTransEvent) {
			calendar.dateControl.isTransEvent = true;
			//�������ı���� onblur ��ʹ��ԭ�����¼���������
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
	//��������
	hide:function() {
		this.panel.style.display = "none";
		this.container.style.display = "none";
		this.isFocus = false;
	},
	//����ת��ʱ��������
	onblur:function() {
		if(!(this.isFocus)) {this.hide();}
	},
	//����������
	show:function(args0, args1) {
		//���ȵ���args1�еĶ���
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
			//obj.focus();if(obj.onclick != null) {obj.onclick();} else {this.showCalendar(obj, args);}
			this.showCalendar(obj, args1);
			return true;
		}
		return false;
	}
}

/**
 * ���÷�����
 * ����һ������Ĭ�������������
 * var __Calendar__ = new CalendarHelper();
 * ���÷�ʽ��__Calendar__.show(args1, args2);
 * 1������args1��Ŀǰ֧�ֲ�����input���� �� 'input��ID'
 * 2������args2��Ĭ��Ϊ{}
 * Ŀǰ֧�ֲ�����
 * �����������еĲ�����{beginYear:1950,endYear:2050,lang:0,format:"yyyy-MM-dd",left:0,top:0}
 * ��ʾ���ݵĲ��������������args1��{object:input����} �� {id:'input��ID'}
 * @param format String "yyyy-MM-dd HH-mm-ss"
 * @param beginYear Integer ����1000С��9999 ��1950
 * @param endYear Integer ����1000С��9999 ��2050
 * @param lang Integer 0(����)|1(Ӣ��) ����������
 * @param left Integer ���X����,������ı���ĺ���ƫ����
 * @param top Integer ���Y����,������ı��������ƫ����
 * ��ʽ��ע���Сд���Ҵ��ڶ����ͬ��ʽʱֻƥ���һ������yyyy���꣬MM���£�dd���죬HH��24Сʱ�ƣ�mm�����ӣ�ss���룬SSS�����룬w���ܣ�q����(1/2/3/4)��
 * ���ӣ�<input type="text" onclick="__Calendar__.show(this, {})" />
 */
var __Calendar__ = new CalendarHelper();

//���·���ֻ��Ϊ�˼���ԭ���Ĵ���,����ɾ��
function SelectDate(obj, args) {
	__Calendar__.show(obj, args);
}
function SelectDateById(id, args) {
	__Calendar__.show(id, args);
}
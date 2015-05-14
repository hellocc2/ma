<?php /* Smarty version 2.6.18, created on 2014-08-26 06:04:06
         compiled from trafficsources.htm */ ?>
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
<head>
<link href="scripts/jtable/themes/metro/darkgray/jtable.css"
	rel="stylesheet" type="text/css" />
<link href="image/w_stronglist.css" rel="stylesheet" type="text/css" />

<script src="scripts/jtable/jquery.jtable.js" type="text/javascript"></script>
<script src="javascript/htcommon.js" type="text/javascript"></script>
<style type="text/css">
.alert-box {
    color:#555;
    border-radius:10px;
    font-family:Tahoma,Geneva,Arial,sans-serif;font-size:11px;
    padding:10px 10px 10px 10px;
    margin:0px 0px 10px 0px;
}
.alert-box span {
    font-weight:bold;
    text-transform:uppercase;
}
.error {
    background:#ffecec ;
    border:1px solid #f5aca6;
}

.catebutton {
	-moz-box-shadow:inset 50px 50px 50px 50px #ffffff;
	-webkit-box-shadow:inset 50px 50px 50px 50px #ffffff;
	box-shadow:inset 50px 50px 50px 50px #ffffff;
	background-color:#f9f9f9;
	-webkit-border-top-left-radius:9px;
	-moz-border-radius-topleft:9px;
	border-top-left-radius:9px;
	-webkit-border-top-right-radius:9px;
	-moz-border-radius-topright:9px;
	border-top-right-radius:9px;
	-webkit-border-bottom-right-radius:9px;
	-moz-border-radius-bottomright:9px;
	border-bottom-right-radius:9px;
	-webkit-border-bottom-left-radius:9px;
	-moz-border-radius-bottomleft:9px;
	border-bottom-left-radius:9px;
	text-indent:0;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#666666;
	font-family:Arial;
	font-size:8px;
	font-weight:bold;
	font-style:normal;
	height:22px;
	line-height:22px;
	width:68px;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px #ffffff;
}.catebutton:hover {
	background-color:#e9e9e9;
}.catebutton:active {
	position:relative;
	top:1px;
}</style>
<style>
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

}

.ui-autocomplete {
	max-height: 200px;
	width: 450px;
	overflow-y: auto;
}

.jt_bar {
position: relative;
float: left;
}
</style>

<script>
(function( $ ) {
   $.widget( "custom.name", {
     _create: function() {
       this.wrapper = $( "<span>" )
         .addClass( "custom-name" )
         .insertAfter( this.element );

       this.element.hide();
       this._createAutocomplete();
       this._createShowAllButton();
     },

     _createAutocomplete: function() {
       var selected = this.element.children( ":selected" ),
         value = selected.val() ? selected.html().replace(/&nbsp;|&gt;/g,'').replace(/&amp;/, "&"): "";
       this.input = $( "<input>" )
         .appendTo( this.wrapper )
         .val( value )
         .attr( "size", value.replace(/[^\x00-\xff]/g, 'xx').length )
         .addClass( "custom-name-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
         .autocomplete({
           delay: 0,
           minLength: 0,
           source: $.proxy( this, "_source" )
         })
         .tooltip({
           tooltipClass: "ui-state-highlight"
         });

       this._on( this.input, {
         autocompleteselect: function( event, ui ) {
           ui.item.option.selected = true;
           this._trigger( "select", event, {
             item: ui.item.option
           });
           $('.custom-name-input').attr( "size", $('#name option:selected').html().replace(/&nbsp;|&gt;|/g,'').replace(/[^\x00-\xff]/g, 'xx').replace(/&amp;/, "&").length);//自动修改 INPUT 大小
         },
		 
         autocompletechange: "_removeIfInvalid"
       });
     },

     _createShowAllButton: function() {
       var input = this.input,
         wasOpen = false;

       $( "<a>" )
         .attr( "tabIndex", -1 )
         .attr( "title", "显示全部选项" )
         .tooltip()
         .appendTo( this.wrapper )
         .button({
           icons: {
             primary: "ui-icon-triangle-1-s"
           },
           text: false
         })
         .removeClass( "ui-corner-all" )
         .addClass( "custom-name-toggle ui-corner-right" )
         .mousedown(function() {
           wasOpen = input.autocomplete( "widget" ).is( ":visible" );
         })
         .click(function() {
           input.focus();

           // Close if already visible
           if ( wasOpen ) {
             return;
           }

           // Pass empty string as value to search for, displaying all results
           input.autocomplete( "search", "" );
         });
     },

     _source: function( request, response ) {
       var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
       response( this.element.children( "option" ).map(function() {
         var text = $( this ).text();
         var inupt_value = $( this ).html().replace(/&nbsp;|&gt;/g,'').replace(/&amp;/, "&");
         if ( this.value && ( !request.term || matcher.test(text) ) )
           return {
             label: text,
             value: inupt_value,
             option: this
           };
       }) );
       
     },
	 
     _removeIfInvalid: function( event, ui ) {
       // Selected an item, nothing to do
       if ( ui.item ) {
         return;
       }
		
       // Search for a match (case-insensitive)
       var value = this.input.val(),
         valueLowerCase = value.toLowerCase(),
         valid = false;
       this.element.children( "option" ).each(function() {
         if ( $( this ).text().toLowerCase() === valueLowerCase ) {
           this.selected = valid = true;
           return false;
         }
       });

       // Found a match, nothing to do
       if ( valid ) {
         return;
       }

       // Remove invalid value
       this.input
         .val( "" )
         .attr( "title", value + " 没有找到匹配的选项默认上次的结果" )
         .tooltip( "open" );
       this.element.val( "" );
       this._delay(function() {
         this.input.tooltip( "close" ).attr( "title", "" );
       }, 2500 );
       this.input.data( "ui-autocomplete" ).term = "";
     },

     _destroy: function() {
       this.wrapper.remove();
       this.element.show();
     }
   });
 })( jQuery );
</script>

<script>
(function($){
	$(function() {
		$( "#name" ).name();
		$('.custom-name-input').on("click",function() {
			$old_value = $(this).val();
			$(this).val("");
		});
	    $(".custom-name-input").on("blur", function(){
	   	if ($(this).val() == '') {
	   		$(this).val($old_value);
	   	}
	   });
	 });
})(jQuery);
</script>
</head>
<body>
	<input id="frame_url" type="hidden"
		value="<?php echo $_SERVER['REQUEST_URI']; ?>
" />
	<script src="<?php echo $this->_tpl_vars['javascript_url']; ?>
amcharts.js" type="text/javascript"></script>
	<input id="frame_url" type="hidden" type="hidden"
		value="<?php echo $_SERVER['HTTP_HOST']; ?>
<?php echo $_SERVER['REQUEST_URI']; ?>
" />
	<div id="container">
		<div id="wrapper">
			<div id="content">

				<div id="rightnow">
					<h3 class="reallynow">
						<span>统计（天）</span>
						<form name="form" id="form" action="" target="mainFrame"
							method="post">
							<div id="date_lang"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "time_lang.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
						</form>
						<br />
					</h3>
				</div>
				<div id="infowrap">
					<div>
						<p id="chartdiv2" style="width: 100%; height: 350px;"></p>
					</div>
				</div>
				<br />
				
				<?php if ($this->_tpl_vars['nu_roi'] > 0): ?>
				<div class="alert-box error">
					<span>注意: </span>这里有 <?php echo $this->_tpl_vars['nu_roi']; ?>
 个 SEM 投放效果不佳的关键词. （ ROI 小于 -0.3 ） <button type="submit" id="ROIRecordsButton" >查询</button>
				</div>
				<?php endif; ?>

				<div style="overflow:hidden;" class="filtering">
					<form>
						<div class="jt_bar" style="margin:5px;">外链标记: 
							<select id="search_type" name="search_type"> 
							  <option <?php if ($this->_tpl_vars['e'] == 'wildcards'): ?>selected<?php endif; ?> value="wildcards">通配符</option>
							  <option <?php if ($this->_tpl_vars['e'] == 'regex'): ?>selected<?php endif; ?> value="regex">正则表达式</option>search_type
							</select>
							<input size="32" type="text" name="PromotionName" id="PromotionName" value="<?php echo $this->_tpl_vars['a']; ?>
" />
						</div>
						<div class="jt_bar" style="margin:4px;">分类: <select id="name" name="name"> <option value="0">全部</option> <?php echo $this->_tpl_vars['class']; ?>
 </select></div>
						<div style="margin-left: 10px;float: right;"><button type="submit" id="LoadRecordsButton" >查询</button></div>
						<div style="margin-left: 10px;float: right;"><button type="submit" id="SEMRecordsButton" style="height: 26px;">SEM 数据导出</button></div>
						<div class="jt_bar" style="margin-top:5px; margin-left:35px;float:left;">
							<div class="jt_bar" style="margin:5px; margin-left: 0px;float:left;">商品分类: </div>
							<input type="hidden" name="Categories_id" id="Categories_id" value="<?php echo $this->_tpl_vars['c']; ?>
">
							<div class="jt_bar" style="margin-top: 5px;float:left;margin-left: 10px;">包括子分类:</div><div style="margin: 7px;float:left;"><input id="addition" <?php if ($this->_tpl_vars['d'] == 1): ?>checked="checked"<?php endif; ?> type="checkbox" name="addition" value="1" /></div>
						</div> 
					</form>
				</div>

				<div id="PeopleTableContainer" style="width: auto;"></div>

			</div>
		</div>
	</div>

	<script>

var chart;
var chartData_2 =<?php echo $this->_tpl_vars['data_array']; ?>
;
var average ='4';
AmCharts.ready(function () {

	// SERIAL CHART    
	chart = new AmCharts.AmSerialChart();
	//chart.pathToImages = "../amcharts/images/";
	chart.pathToImages = "../javascript/images/";
	chart.zoomOutButton = {
	   backgroundColor: '#000000',
	   backgroundAlpha: 0.15
	};
	chart.backgroundColor= '#FFFFFF';
	chart.backgroundAlpha= 0.95;
	chart.dataProvider = chartData_2;
	chart.categoryField = "time";
	//chart.addTitle("日期范围分析", 15);
	// AXES
	// category
	var categoryAxis = chart.categoryAxis;
	categoryAxis.labelRotation = 45;
	categoryAxis.dashLength = 1;
	categoryAxis.gridAlpha = 0.15;
	categoryAxis.axisColor = "#DADADA";

	// value                
	var valueAxis = new AmCharts.ValueAxis();
	valueAxis.axisColor = "#DADADA";
	valueAxis.dashLength = 1;
   // valueAxis.logarithmic = true; // this line makes axis logarithmic
	chart.addValueAxis(valueAxis);

	// GUIDE for average
	var guide = new AmCharts.Guide();
	guide.value = average;
	guide.lineColor = "#CC0000";
	guide.dashLength = 4;
	guide.label = "平均值";
	guide.inside = true;
	guide.lineAlpha = 1;
	valueAxis.addGuide(guide);
	
	// GRAPH 1
	var graph = new AmCharts.AmGraph();
	graph.type = "smoothedLine";
	//graph.bullet = "round";
	//graph.bulletColor = "#FFFFFF";
	//graph.bulletBorderColor = "#00BBCC";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "IP : [[ip]]";
	graph.title = "IP";
	graph.valueField = "ip";
	graph.lineThickness = 2;
	graph.lineColor = "#00BBCC";
	chart.addGraph(graph);
				
				// GRAPH 2
	var graph = new AmCharts.AmGraph();
	graph.type = "smoothedLine";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "PV : [[pv]]";
	graph.title = "pv";
	graph.valueField = "pv";
	graph.lineThickness = 2;
	graph.lineColor = "#2E7824";
	chart.addGraph(graph);
	
	// GRAPH 3
	var graph = new AmCharts.AmGraph();
	graph.type = "smoothedLine";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "UV : [[uv]]";
	graph.title = "uv";
	graph.valueField = "uv";
	graph.lineThickness = 2;
	graph.lineColor = "#FF9E01";
	chart.addGraph(graph);

	// GRAPH 4
	var graph = new AmCharts.AmGraph();
	graph.bulletBorderColor = "#69A55C";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "newUv : [[newUv]]";
	graph.title = "newUv";
	graph.valueField = "newUv";
	graph.lineThickness = 2;
	graph.lineColor = "#69A55C";
	chart.addGraph(graph);

	var graph = new AmCharts.AmGraph();
	graph.bulletBorderColor = "#401c44";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "支付订单数: [[payorder]]";
	graph.title = "支付订单数";
	graph.valueField = "payorder";
	graph.lineThickness = 2;
	graph.lineColor = "#401c44";
	chart.addGraph(graph);

	var graph = new AmCharts.AmGraph();
	graph.bulletBorderColor = "#FF001D";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "未支付订单数 : [[unpayorder]]";
	graph.title = "未支付订单数";
	graph.valueField = "unpayorder";
	graph.lineThickness = 2;
	graph.lineColor = "#FF001D";
	chart.addGraph(graph);	
	
	var graph = new AmCharts.AmGraph();
	graph.bulletBorderColor = "#401c44";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "订单渠道转换率 : [[purate]]%";
	graph.title = "订单渠道转换率  （%）";
	graph.valueField = "purate";
	graph.lineThickness = 2;
	graph.lineColor = "#86FF0D";
	chart.addGraph(graph);	
	
	var graph = new AmCharts.AmGraph();
	graph.bulletBorderColor = "#401c44";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "支付率 : [[paymentrate]]%";
	graph.title = "支付率  （%）";
	graph.valueField = "purate";
	graph.lineThickness = 2;
	graph.lineColor = "#000000";
	chart.addGraph(graph);
	/*
	var graph = new AmCharts.AmGraph();
	graph.bulletBorderColor = "#401c44";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "ROI : [[ROI]]%";
	graph.title = "ROI （%）";
	graph.valueField = "ROI";
	graph.lineThickness = 2;
	graph.lineColor = "#98FB98";
	chart.addGraph(graph);		
	*/
	
	// CURSOR
	var chartCursor = new AmCharts.ChartCursor();
	chartCursor.cursorPosition = "mouse";
	chart.addChartCursor(chartCursor);

	// SCROLLBAR
	var chartScrollbar = new AmCharts.ChartScrollbar();
	chart.addChartScrollbar(chartScrollbar);
		
	 // LEGEND
	var legend = new AmCharts.AmLegend();
	legend.markerType = "circle";
	chart.addLegend(legend);
	
	// WRITE
	chart.write("chartdiv2");
});
</script>

	<script type="text/javascript">
(function($) {
	$(function() {
		$('.Loadamchart').live("click",function() {
			var promotion_name_row = $(this).html();
		    jQuery.getJSON('PersonActionsPagedSorted.php?action=chart&promotion_name_row='+encodeURIComponent(promotion_name_row)+'&websiteId=<?php echo $this->_tpl_vars['websiteId']; ?>
<?php if ($this->_tpl_vars['islang'] == 'all'): ?><?php else: ?>&islike=1&lang=<?php echo $this->_tpl_vars['isLang']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['s_range']): ?>&s_range=<?php echo $this->_tpl_vars['s_range']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['e_range']): ?>&e_range=<?php echo $this->_tpl_vars['e_range']; ?>
<?php endif; ?>', function (data) {
		        chart.dataProvider = data;
		        chart.validateData();
		      });
		});
	});
})(jQuery);
</script>

	<!--jTable-->
	<script type="text/javascript">
(function($) {
	$(document).ready(function () {
	    //Prepare jTable
		$('#PeopleTableContainer').jtable({
			title: '外链访问统计',
			paging: true,
			pageSize: 20,
			sorting: true,
			defaultSorting: 'pv DESC',
			actions: {
				listAction: 'PersonActionsPagedSorted.php?action=list&websiteId=<?php echo $this->_tpl_vars['websiteId']; ?>
<?php if ($this->_tpl_vars['islang'] == 'all'): ?><?php else: ?>&lang=<?php echo $this->_tpl_vars['isLang']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['s_range']): ?>&s_range=<?php echo $this->_tpl_vars['s_range']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['e_range']): ?>&e_range=<?php echo $this->_tpl_vars['e_range']; ?>
<?php endif; ?>'
			},
			fields: {
				PersonId: {
					key: true,
					create: false,
					edit: false,
					list: false
				},	
				/*id: {
					title: 'ID',
					width: '5%'
				},*/				
				PromotionName: {
					title: '外链标记',
					width: 'auto',
					display: function (data) {
				        var $link = $('<div style="width:295px;height:24px;cursor: pointer;line-height:24px; font-size:13px; color:#6699ff;overflow:hidden;text-overflow:ellipsis;" title="'+ decodeURIComponent(data.record.PromotionName) +'" class="Loadamchart" href="#">' + decodeURIComponent(data.record.PromotionName) + '</div>');
				        $link.click(function(){ /* do something on click */ });
				        return $link;
				    }
				},
				pv: {
					title: 'PV',
					width: '5%'
				},
				uv: {
					title: 'UV',
					width: '5%'
				},
				ROI: {
					title: 'ROI',
					width: 'auto',
					display: function (data) {
				        var $link = $('<div >' + data.record.ROI + '%</div>');
				        $link.click(function(){ /* do something on click */ });
				        return $link;
				    }
				},
				impressions: {
					title: '展示',
					width: 'auto'
				},
				adClicks: {
					title: '点击',
					width: 'auto'
				},
				adcostUsd: {
					title: '花费',
					width: 'auto'
				},
				CP: {
					title: 'CP',
					width: 'auto'
				},
				name: {
					title: '分类 ',
					width: '12%',
				},
				purate: {
					title: '转率',
					width: 'auto',
					display: function (data) {
				        var $link = $('<div >' + data.record.purate + '%</div>');
				        $link.click(function(){ /* do something on click */ });
				        return $link;
				    }
				},		
				orders: {
					title: '总',
					width: 'auto'
				},				
				RecordDate: {
					title: '付',
					width: 'auto',
					sorting: false,
					display: function (data) {
						if( $('#total_payorder').length == 0 ) {
							$('.jtable-title-text').append('<span id="total_payorder" style="display: block; font-size: 12px; font-weight: normal;">总订单：' + data.record.PayorderTotal +'</span>');
						} else {
							var purate = (data.record.PayorderTotal/data.record.uvTotal)*100;
							$('#total_payorder').html('订单总数：' +data.record.orderTotal+ ' | 订金：$' +data.record.payamountTotal+ ' | 支付数：' + data.record.PayorderTotal +  ' | 未支付数：' + data.record.unPayorderTotal + ' | 订单转化率：' + purate.toFixed(2) + '% | IP：'+data.record.ipTotal+ ' | PV：'+data.record.pvTotal+ ' | UV：'+data.record.uvTotal + ' | 会员：'+data.record.regmemberTotal+' | 订阅：'+data.record.subscribersTotal+'<br />展示：'+data.record.impressionsTotal + ' | 点击：'+data.record.adClicksTotal + ' | 花费：'+data.record.adcostUsdTotal );
						}

				        var $link = $('<a href="http://ht.milanoo.com/milanooht/index.php?module_id=73<?php if (ss_range): ?>&starttime=<?php echo $this->_tpl_vars['ss_range']; ?>
<?php endif; ?><?php if (ee_range): ?>&endtime=<?php echo $this->_tpl_vars['ee_range']; ?>
<?php endif; ?>&OrdersPay=1&ordersSite=1&WebsiteId=<?php if ($this->_tpl_vars['websiteId'] == '101'): ?>1&device_type=2<?php elseif ($this->_tpl_vars['websiteId'] == '201'): ?>1&device_type=5<?php elseif ($this->_tpl_vars['websiteId'] == '666'): ?>&1=1<?php else: ?><?php echo $this->_tpl_vars['websiteId']; ?>
&device_type=1<?php endif; ?>&Promotion=' + data.record.PromotionName + '" target="_blank">' + data.record.payorder + '</a>');
				        $link.click(function(){ /* do something on click */ });
				        return $link;
				    }
				},
				RecordDate2: {
					title: '未',
					width: 'auto',
					sorting: false,
					display: function (data) {
						if( $('#total_payorder').length == 0 ) {
							$('.jtable-title-text').append('<span id="total_payorder" style="display: block; font-size: 12px; font-weight: normal;">总订单：' + data.record.PayorderTotal +'</span>');
						} else {
							var purate = (data.record.PayorderTotal/data.record.uvTotal)*100;
							$('#total_payorder').html('订单总数：' +data.record.orderTotal+ ' | 订金：$' +data.record.payamountTotal+ ' | 支付数：' + data.record.PayorderTotal +  ' | 未支付数：' + data.record.unPayorderTotal + ' | 订单转化率：' + purate.toFixed(2) + '% | IP：'+data.record.ipTotal+ ' | PV：'+data.record.pvTotal+ ' | UV：'+data.record.uvTotal + ' | 会员：'+data.record.regmemberTotal+' | 订阅：'+data.record.subscribersTotal+'<br />展示：'+data.record.impressionsTotal + ' | 点击：'+data.record.adClicksTotal + ' | 花费：'+data.record.adcostUsdTotal );
						}

				        var $link = $('<a href="http://ht.milanoo.com/milanooht/index.php?module_id=73<?php if (ss_range): ?>&starttime=<?php echo $this->_tpl_vars['ss_range']; ?>
<?php endif; ?><?php if (ee_range): ?>&endtime=<?php echo $this->_tpl_vars['ee_range']; ?>
<?php endif; ?>&OrdersPay=0&ordersSite=1&WebsiteId=<?php if ($this->_tpl_vars['websiteId'] == 101): ?>1&device_type=2<?php elseif ($this->_tpl_vars['websiteId'] == '201'): ?>1&device_type=5<?php elseif ($this->_tpl_vars['websiteId'] == '666'): ?>&1=1<?php else: ?><?php echo $this->_tpl_vars['websiteId']; ?>
&device_type=1<?php endif; ?>&Promotion=' + data.record.PromotionName + '" target="_blank">' + data.record.unpayorder + '</a>');
				        $link.click(function(){ /* do something on click */ });
				        return $link;
				    }
				},
				payamount: {
					title: '金额 ',
					width: 'auto'
				},				
				regmember: {
					title: '会员',
					width: 'auto'
				},
				subscribers: {
					title: '订阅',
					width: 'auto'
				},				

			}
		});

		//Load person list from server
		//$('#PeopleTableContainer').jtable('load');
		//Re-load records when user click 'load records' button.
		
        $('#LoadRecordsButton').click(function (e) {
            e.preventDefault();
            var search_type = $('#search_type').val();
            var promotion_name_row = $('#PromotionName').val();
            var category_chart = $('#name').val();
            var categories_id = $('#Categories_id').val();
            var addition = $('#addition:checked').val();
            
            $('#PeopleTableContainer').jtable('load', {
            	search_type: search_type,
            	PromotionName: promotion_name_row,
            	name: category_chart,
            	categories_id: categories_id,
            	addition: addition
            });
			
			document.cookie="search_type="+search_type;
            document.cookie="promotion_name_row="+promotion_name_row;
            document.cookie="category_chart="+category_chart;
            document.cookie="categories_id="+categories_id;
            document.cookie="addition="+addition;
            if (search_type) {
            	var search_type = '&search_type='+search_type;
            }
            if (category_chart) {
            	var category_chart_url = '&category='+category_chart;
            }
            if (categories_id) {
            	var categories_id_url = '&categories_id='+categories_id;
            } else{
            	var categories_id_url = '';
            }
            
            if (addition) {
            	var addition_url = '&addition='+addition;
            }else{
            	addition_url = '';
            }
            if (promotion_name_row || category_chart_url || categories_id_url) {
    		    jQuery.getJSON('PersonActionsPagedSorted.php?action=chart'+search_type+category_chart_url+categories_id_url+addition_url+'&promotion_name_row='+encodeURIComponent(promotion_name_row)+'&websiteId=<?php echo $this->_tpl_vars['websiteId']; ?>
<?php if ($this->_tpl_vars['islang'] == 'all'): ?><?php else: ?>&lang=<?php echo $this->_tpl_vars['isLang']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['s_range']): ?>&s_range=<?php echo $this->_tpl_vars['s_range']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['e_range']): ?>&e_range=<?php echo $this->_tpl_vars['e_range']; ?>
<?php endif; ?>', function (data) {
	   		        chart.dataProvider = data;
	   		        chart.validateData();
    		    });
            }
        });
 
        $('#ROIRecordsButton').click(function (e) {
            e.preventDefault();
            var search_type = $('#search_type').val();
            var promotion_name_row = '';
            var category_chart = '<?php echo $this->_tpl_vars['SEM_id']; ?>
';
            var categories_id = '';
            var addition = '';
            
            $('#PeopleTableContainer').jtable('load', {
            	search_type: search_type,
            	PromotionName: promotion_name_row,
            	name: category_chart,
            	categories_id: categories_id,
            	ROI: 1
            });

            if (search_type) {
            	var search_type = '&search_type='+search_type;
            }
            if (category_chart) {
            	var category_chart_url = '&category='+category_chart;
            }
            if (categories_id) {
            	var categories_id_url = '&categories_id='+categories_id;
            } else{
            	var categories_id_url = '';
            }
            
            if (addition) {
            	var addition_url = '&addition='+addition;
            }else{
            	addition_url = '';
            }
            if (promotion_name_row || category_chart_url || categories_id_url) {
    		    jQuery.getJSON('PersonActionsPagedSorted.php?action=chart&ROI=1'+search_type+category_chart_url+categories_id_url+addition_url+'&promotion_name_row='+encodeURIComponent(promotion_name_row)+'&websiteId=<?php echo $this->_tpl_vars['websiteId']; ?>
<?php if ($this->_tpl_vars['islang'] == 'all'): ?><?php else: ?>&lang=<?php echo $this->_tpl_vars['isLang']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['s_range']): ?>&s_range=<?php echo $this->_tpl_vars['s_range']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['e_range']): ?>&e_range=<?php echo $this->_tpl_vars['e_range']; ?>
<?php endif; ?>', function (data) {
	   		        chart.dataProvider = data;
	   		        chart.validateData();
    		    });
            }
        }); 
 
        $('#SEMRecordsButton').click(function (e) {
            e.preventDefault();
            var search_type = $('#search_type').val();
            var promotion_name_row = $('#PromotionName').val();
            var category_chart = '<?php echo $this->_tpl_vars['SEM_id']; ?>
';
            var categories_id = $('#Categories_id').val();
            var addition = $('#addition:checked').val();

            if (category_chart) {
            	var category_chart_url = '&category='+category_chart;
            }

            if (category_chart) {
    			document.location.href ='PersonActionsPagedSorted.php?action=list&is_export_csv=1'+category_chart_url+'&websiteId=<?php echo $this->_tpl_vars['websiteId']; ?>
<?php if ($this->_tpl_vars['islang'] == 'all'): ?><?php else: ?>&lang=<?php echo $this->_tpl_vars['isLang']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['s_range']): ?>&s_range=<?php echo $this->_tpl_vars['s_range']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['e_range']): ?>&e_range=<?php echo $this->_tpl_vars['e_range']; ?>
<?php endif; ?>';
            }
        });
 
        //Load all records when page is first shown
        $('#LoadRecordsButton').click();
	});
})(jQuery);
</script>
<script>
(function($) {
	//$('#Categories_id').w_stronglist({key:'categories_zh-cn',nodeClick:addtypevalue,treewidth:222,WebsiteId:1});
	$('#Categories_id').w_stronglist({key:'categories_zh-cn',nodeClick:addtypevalue,treewidth:222,WebsiteId:<?php if ($this->_tpl_vars['websiteId'] == '666' || $this->_tpl_vars['websiteId'] == '101' || $this->_tpl_vars['websiteId'] == '201'): ?>1<?php else: ?><?php echo $this->_tpl_vars['websiteId']; ?>
<?php endif; ?>});
})(jQuery);
</script>
	<!--jTable end-->
</body>

</html>
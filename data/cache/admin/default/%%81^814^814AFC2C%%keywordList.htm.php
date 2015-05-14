<?php /* Smarty version 2.6.18, created on 2014-08-29 09:28:19
         compiled from keywordList.htm */ ?>
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

				<div style="overflow:hidden;" class="filtering">
					<form>
						<div class="jt_bar" style="margin:5px;">关键词: 
							<select id="search_type" name="search_type"> 
							  <option <?php if ($this->_tpl_vars['e'] == 'wildcards'): ?>selected<?php endif; ?> value="wildcards">通配符</option>
							  <option <?php if ($this->_tpl_vars['e'] == 'regex'): ?>selected<?php endif; ?> value="regex">正则表达式</option>search_type
							</select>
							<input size="32" type="text" name="KeywordName" id="KeywordName" value="<?php echo $this->_tpl_vars['a']; ?>
" />
						</div>
						<div style="margin-left: 10px;float: right;"><button type="submit" id="LoadRecordsButton" >查询</button></div>
						<div style="margin-left: 10px;float: right;"><button type="submit" id="SEMRecordsButton" style="height: 26px;">关键词数据导出</button></div>						
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
	/*var graph = new AmCharts.AmGraph();
	graph.bulletBorderColor = "#69A55C";
	graph.bulletBorderThickness = 2;
	graph.bulletSize = 7;
	graph.balloonText = "newUv : [[newUv]]";
	graph.title = "newUv";
	graph.valueField = "newUv";
	graph.lineThickness = 2;
	graph.lineColor = "#69A55C";
	chart.addGraph(graph);*/

	
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
			var KeywordName = $(this).html();
		    jQuery.getJSON('PersonActionsPagedSorted.php?action=keywordChart&KeywordName='+encodeURIComponent(KeywordName)+'&websiteId=<?php echo $this->_tpl_vars['websiteId']; ?>
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
			title: '关键词',
			paging: true,
			pageSize: 20,
			sorting: true,
			defaultSorting: 'uv DESC',
			actions: {
				listAction: 'PersonActionsPagedSorted.php?action=keyword&websiteId=<?php echo $this->_tpl_vars['websiteId']; ?>
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
				name: {
					title: '关键词',
					width: 'auto',
					display: function (data) {
				        var $link = $('<div style="width:295px;height:24px;cursor: pointer;line-height:24px; font-size:13px; color:#6699ff;overflow:hidden;text-overflow:ellipsis;" title="'+ decodeURIComponent(data.record.name) +'" class="Loadamchart" href="#">' + decodeURIComponent(data.record.name) + '</div>');
				        $link.click(function(){ /* do something on click */ });
				        return $link;
				    }
				},
				pv: {
					title: 'PV',
					width: '20%',
				},
				uv: {
					title: 'UV',
					width: '20%',
				},
				ip: {
					title: 'IP',
					width: '20%',
					display: function (data) {
						if( $('#total_payorder').length == 0 ) {
							$('.jtable-title-text').append('<span id="total_payorder" style="display: block; font-size: 12px; font-weight: normal;">关键词总数：' + data.record.keywordTotal +'</span>');
						} else {
							var purate = (data.record.PayorderTotal/data.record.uvTotal)*100;
							$('#total_payorder').html('关键词总数：'+data.record.keywordTotal+' | PV总数：' +data.record.pvTotal+ ' | UV总数：' +data.record.uvTotal+ ' | IP总数：' + data.record.ipTotal  );
						}
				        var $link = $('<div >' + data.record.ip + '</div>');
				        $link.click(function(){ /* do something on click */ });
				        return $link;
				    }
				},
							
			}
		});

		
        $('#LoadRecordsButton').click(function (e) {
            e.preventDefault();
            var search_type = $('#search_type').val();
            var KeywordName = $('#KeywordName').val();   
            
            $('#PeopleTableContainer').jtable('load', {
            	search_type: search_type,
            	KeywordName: KeywordName,
            });
			
			document.cookie="search_type="+search_type;
            document.cookie="KeywordName="+KeywordName;
         
            if (search_type) {
            	var search_type = '&search_type='+search_type;
            }
            
            if (KeywordName) {
    		    jQuery.getJSON('PersonActionsPagedSorted.php?action=keywordChart'+search_type+'&KeywordName='+encodeURIComponent(KeywordName)+'&websiteId=<?php echo $this->_tpl_vars['websiteId']; ?>
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
            var KeywordName = $('#KeywordName').val();
  
			if (KeywordName) {
				var KeywordName = '&KeywordName='+KeywordName;
            }
			document.location.href ='PersonActionsPagedSorted.php?action=keyword&is_export_csv=1'+KeywordName+'&websiteId=<?php echo $this->_tpl_vars['websiteId']; ?>
<?php if ($this->_tpl_vars['islang'] == 'all'): ?><?php else: ?>&lang=<?php echo $this->_tpl_vars['isLang']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['s_range']): ?>&s_range=<?php echo $this->_tpl_vars['s_range']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['e_range']): ?>&e_range=<?php echo $this->_tpl_vars['e_range']; ?>
<?php endif; ?>';
        });

        $('#LoadRecordsButton').click();
	});
})(jQuery);
</script>
	<!--jTable end-->
</body>

</html>
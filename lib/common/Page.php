<?php
namespace Lib\common;

class Page {
	public static function paginate($reload, $page, $tpages) {
		$adjacents = 3;
		$toplabel = "&lsaquo; 首页";
		$prevlabel = "&lsaquo; 上一页";
		$nextlabel = "下一页 &rsaquo; &nbsp;";
		$out = "";
		// previous
		if ($page == 1) {
			$out .= "<span>" . $prevlabel . "</span>\n";
		} elseif ($page == 2) {
			$out .= "<li><a  href=\"" . $reload . "\">" . $prevlabel . "</a>\n</li>";
		} else {
			$out .= "<li><a  href=\"" . $reload . "&amp;page=" . (1) . "\">" . $toplabel . "</a>\n</li><li><a  href=\"" . $reload . "&amp;page=" . ($page - 1) . "\">" . $prevlabel . "</a>\n</li>";
		}
		
		$pmin = ($page > $adjacents) ? ($page - $adjacents) : 1;
		$pmax = ($page < ($tpages - $adjacents)) ? ($page + $adjacents) : $tpages;
		for($i = $pmin; $i <= $pmax; $i ++) {
			if ($i == $page) {
				$out .= "<li  class=\"active\"><a href=''>" . $i . "</a></li>\n";
			} elseif ($i == 1) {
				$out .= "<li><a  href=\"" . $reload . "\">" . $i . "</a>\n</li>";
			} else {
				$out .= "<li><a  href=\"" . $reload . "&amp;page=" . $i . "\">" . $i . "</a>\n</li>";
			}
		}
		
		if ($page < ($tpages - $adjacents)) {
			$out .= "<a style='font-size:11px' href=\"" . $reload . "&amp;page=" . $tpages . "\">" . $tpages . "</a>\n";
		}
		// next
		if ($page < $tpages) {
			$out .= "<li><a  href=\"" . $reload . "&amp;page=" . ($page + 1) . "\">" . $nextlabel . "</a> &nbsp; <div style='margin-top: -14px;'><input class='jump' style='float: left;height: 30px;margin: 0px 10px 0px 10px;' type='text' id='jump' name='jump' autocomplete='off' size=1 ><a id='jumptag' href=\"" . $reload . "&amp;page=" . ($page + 1) . "\">跳到这页</a></div>\n</li>";
		} else {
			$out .= "<span style='font-size:11px'>" . $nextlabel . "</span><input class='jump' style='float: left;height: 30px;margin: 0px 10px 0px 10px;' type='text' id='jump' name='jump' autocomplete='off' size=1 ><a id='jumptag' href=\"" . $reload . "&amp;page=" . ($page + 1) . "\">跳到这页</a></div>\n";
		}
		$out .= "";
		return $out;
	}
}
?>
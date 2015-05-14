<?php
namespace Module\logout;

class Index extends \Lib\common\Application {
	public function __construct() {
		session_destroy();
		echo "<script language=JavaScript>parent.window.location.href = 'index.php?module=logout&action=sso'</script>";
	}
}
?>

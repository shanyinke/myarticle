<?php
if (!isset($_GET['file'])) {
	echo "No page URL specified.";
}else{
	$url=$_GET['file'];
	if(file_exists($url)){
		echo "<h4>Source of: /".htmlentities($url)."</h4><hr size=1>";
		$dir = dirname($url);
		// If this is a legal dir, then it is under the docroot, else use basename
		if ($dir) {
			$page_name = $p_root_dir.$url;
		} else {
			$page_name = basename($url);
		}
		
		if (strpos($page_name,'header.inc.php') || $page_name=='header.inc.php') {$page_name='header.inc.bak';}
		if (strpos($page_name,'conf_global.php') || $page_name=='conf_global.php') {$page_name='forum/conf_global.bak';}
		if (strpos($page_name,'config.inc.php') || $page_name=='config.inc.php') {$page_name='stat/config.inc.php.bak';}
		echo "<!-- ".htmlentities($page_name)." -->\n";
		echo '<code>';
		show_source($page_name);
		echo '</code><br><hr size=1><br><br>';
	}else{
		echo "文件不存在!File not exists!";
	}
}
?>

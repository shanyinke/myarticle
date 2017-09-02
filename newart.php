<?php


require('article_global.inc.php');
$articlenum = "10"; // 显示最新文章数 
$artpath=home_url; 

$lastarticles=$DB_site->query("SELECT $table_article.title,$table_article.articleid,$table_article.posttime FROM $table_article WHERE cateid=$cateid
ORDER BY posttime DESC LIMIT 0,$articlenum");


while ($lastarticle=$DB_site->fetch_array($lastarticles)){

	$time= getTime($lastarticle[posttime]);

	echo "document.write('<a href=$artpath/article.php?aid=$lastarticle[articleid] target=_blank title=$time>');\n";
	echo "document.write('$lastarticle[title]');\n"; 
	echo "document.write('</a><br>');\n"; 

}

?>
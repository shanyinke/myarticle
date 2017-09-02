<?php
/*******************************************************************
 * File: index.php
 * Version: 0.0.1
 * Date: 2002/07/25
 *
 * Copyright (C) 2002 Myluntan Team. All rights reserved.
 *
 * This software is the proprietary information of Myluntan Team Team.
 * Use is subject to license terms.
 */


/**
 * Load global library
 */

 /**
 * Load global library
 */
define('getCache',1);
require('article_global.inc.php');

if (!isset($aid)){
	showerror('error_articleid');
}


$t->preload('printarticle');

initialize();

$t->set_template("printArticle","printarticle"); 
$t->set_block("printArticle","page","pages"); 

$article=$DB_site->query_first("SELECT $table_article.* FROM $table_article 
WHERE $table_article.articleid=$aid");

//make cate nav
$articlenav="<a href=index.php>".hometitle."</a>(".home_url.")<br>";
$navcache=get_nav($article[cateid],0);
$articleNav=array_reverse($navcache, TRUE);
foreach ($articleNav AS $key=>$navs) {
	$articlenav.="<a href=category.php?cid=$navs[navid]>$navs[navtitle]</a>(".home_url."href=category.php?cid=$navs[navid])<br>";
}


$t->set_var("articlenav", $articlenav);
$t->set_var("articleid", $article[articleid]);
$t->set_var("articletitle", $article[title]);
$t->set_var("articletime", getTime($article[posttime]));
$t->set_var("articleauthor", $article[author]);

$pages=$DB_site->query("SELECT $table_page.* FROM $table_page 
WHERE $table_page.articleid=$aid");

while ($page=$DB_site->fetch_array($pages)){
	$t->set_var("articlesubtitle",$page[subtitle]);
	$t->set_var("articlecontent",$page[content]);
	$t->parse("pages","page", true);

}
$t->pparse("printArticle","printArticle"); 
?>
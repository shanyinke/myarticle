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
define('getCache',1);
require('article_global.inc.php');


$t->preload('articlehome');
$t->preload('cate_menu');

initialize();

$t->set_template("articleHome","articlehome"); 
$t->set_block("articleHome","article","articles"); 
$t->set_block("articleHome","hot10","hot10s"); 
$t->set_block("articleHome","toprate10","toprate10s"); 
$t->set_block("articleHome","list","lists"); 

$lastarticles=$DB_site->query("SELECT $table_article.*,$table_cate.cateid,$table_cate.title AS catetitle FROM $table_article 
LEFT JOIN $table_cate using (cateid)
ORDER BY posttime DESC LIMIT 0,10");

while ($lastarticle=$DB_site->fetch_array($lastarticles)){
	//$description=$lastarticle[description];
	
	
	$t->set_var("articleid", $lastarticle[articleid]);
	$t->set_var("articletitle", $lastarticle[title]);
	$t->set_var("time", getTime($lastarticle[posttime]));
	$t->set_var("author", $lastarticle[author]);
	$t->set_var("description",$lastarticle[description]);
	$t->set_var("undercatetitle",$lastarticle[catetitle]);
	$t->set_var("undercateid",$lastarticle[cateid]);
	if ($lastarticle[rating]==0){
		$t->set_var("articlerating", 'not rated yet');
	}else{
		if ($lastarticle[votes]< numofshowrate){
			$t->set_var("articlerating", 'less than '.numofshowrate.' votes');
		}else{
			$ratingavg=round($lastarticle[rating]/$lastarticle[votes],2);
			$ratingimg="";
			$intavg=ceil($ratingavg);
			$ratingimg.=str_repeat("<img src=images/on.gif border=0 alt=$ratingavg/$article[votes]>", $intavg);
				
			$ratingimg.=str_repeat("<img src=images/off.gif border=0 alt=$ratingavg/$article[votes]>", 10-$intavg);
			$t->set_var("articlerating", $ratingimg);


		}
	}
	$t->parse("articles","article", true);
	
}

if (isset($category_cache[0])){

	foreach ($category_cache[0] as $k => $cates){
		$tdnum++;
		$t->set_var("cateclistspc",(($tdnum % 3 == 0)? '</tr><tr vAlign=top>': ''));
		$catelist="<a href=category.php?cid=$cates[cateid]><FONT CLASS=normalfont><b>$cates[title]</b></font></a><br>";
		$catelist=show_category_list($cates[cateid]);
		$t->set_var("catelist", $catelist);
		
		$t->parse("lists","list", true);
	}
}

$hotests=$DB_site->query("SELECT articleid,title,clicktimes  FROM $table_article WHERE clicktimes>0 ORDER BY clicktimes DESC LIMIT 0,10");
while ($hotest=$DB_site->fetch_array($hotests)){
	$t->set_var("hottitle", $hotest[title]);
	$t->set_var("hotid", $hotest[articleid]);
	$t->set_var("hotclicktimes", $hotest[clicktimes]);
	$t->parse("hot10s","hot10", true);
}

$toprates=$DB_site->query("SELECT articleid,title,rating,votes FROM $table_article WHERE votes>=".numofshowrate." ORDER BY rating/votes DESC LIMIT 0,10");
while ($toprate=$DB_site->fetch_array($toprates)){
	$t->set_var("toprateid", $toprate[articleid]);
	$t->set_var("topratetitle", $toprate[title]);
	$ratingavg=round($toprate[rating]/$toprate[votes], 2);
	$t->set_var("topraterating", "$ratingavg/$toprate[votes]");
	$t->parse("toprate10s","toprate10", true);
}

require('catemenu.php');

$t->pparse("articleHome","articleHome"); 

function show_category_list($cid = 0, $depth = 1){
	global $category_cache,$catelist;

	if (!isset($category_cache[$cid])) {
		return $catelist;
	}
	foreach ($category_cache[$cid] as $key => $cats) {
		$catelist.="<font class=middlefont><a href=category.php?cid=$cats[cateid]>$cats[title]</a>&nbsp;&nbsp;&nbsp;</font>";
	}
	return $catelist;
	unset($category_cache[$cid]);
}

?>
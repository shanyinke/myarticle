<?php
/*******************************************************************
 * File: category.php
 * Version: 0.0.1
 * Date: 2002/07/25
 *
 * Copyright (C) 2002 Myluntan Team. All rights reserved.
 *
 * This software is the proprietary information of Myluntan Team .
 * Use is subject to license terms.
 */


/**
 * Load global library
 */

require('article_global.inc.php');

if (!isset($cid)){
	showerror('error_cateid');
}


$t->preload('cate_display');
$t->preload('cate_menu');

initialize();

$t->set_template("cateDisplay","cate_display"); 
$t->set_block("cateDisplay","cateList","cateLists");
$t->set_block("cateDisplay","undercate","undercate"); 
$t->set_block("cateDisplay","article","articles"); 

$articleCount = $DB_site->query_first("SELECT count(*) FROM $table_article WHERE cateid=$cid");
$totalPage = ceil($articleCount/$articlesPP);

if(!isset($page)) {
	$page = 1;
}
$beginRow = ($page-1)*$articlesPP;

$t->set_var("totalarticle", $articleCount);
$t->set_var("totalpage", $totalPage);
$t->set_var("beginaticle", $beginRow+1);
$t->set_var("endaticle", $beginRow+$articlesPP);

//make page nav
if($totalPage > 1) {
	$prenum=$page-1;
	$nextnum=$page+1;
	($page<=1) ? $prevPage="" : $prevPage="<a href=category.php?cid=$cid&page=$prenum><img src=images/pre.gif border=0>pre</a>";
	($page>=$totalPage) ? $nextPage="" : $nextPage="<a href=category.php?cid=$cid&page=$nextnum>next<img src=images/next.gif border=0></a>";
	$pageStatus=$prevPage.'&nbsp;'.$nextPage;
	$t->set_var("pageStatus", $pageStatus);
} else {
	$t->set_var("pageStatus", '');
}


//dispaly artiles in this category
$articles=$DB_site->query("SELECT $table_article.*,$table_cate.cateid,$table_cate.title AS catetitle FROM $table_article 
LEFT JOIN $table_cate using (cateid)
WHERE $table_article.cateid=$cid
ORDER BY posttime DESC LIMIT $beginRow,$articlesPP");

while ($article=$DB_site->fetch_array($articles)){
	//$content=parseString($article[content]);
	//$content=msubstr($content,0,300);
	
	$t->set_var("articleid", $article[articleid]);
	$t->set_var("articletitle", $article[title]);
	$t->set_var("time", getTime($article[posttime]));
	$t->set_var("author", $article[author]);
	$t->set_var("description",$article[description]);
	$t->set_var("undercatetitle",$article[catetitle]);
	$t->set_var("undercateid",$article[cateid]);
	/*
	 *display rating
	 */
	if ($article[rating]==0){
		$t->set_var("articlerating", 'not rated yet');
	}else{
		if ($article[votes]< numofshowrate){
			$t->set_var("articlerating", 'less than '.numofshowrate.' votes');
		}else{
			$ratingavg=round($article[rating]/$article[votes],2);
			$ratingimg="";
			$intavg=ceil($ratingavg);
			$ratingimg.=str_repeat("<img src=images/on.gif border=0 alt=$ratingavg/$article[votes]>", $intavg);
				
			$ratingimg.=str_repeat("<img src=images/off.gif border=0 alt=$ratingavg/$article[votes]>", 10-$intavg);

			$t->set_var("articlerating", $ratingimg);
		}
	}
	$t->parse("articles","article", true);
}

/*
 *make cate nav
 */
$catenav="<a href=index.php>".hometitle."</a><img src=images/next.gif border=0>";
$navcache=get_nav($cid,0);
$cateNav=array_reverse($navcache, TRUE);
foreach ($cateNav AS $key=>$navs) {
	$catenav.="<a href=category.php?cid=$navs[navid]>$navs[navtitle]</a>";
	$catenav.=(($navs[navid]==$cid)? "":"<img src=images/next.gif border=0>");
}
$catenav.="$article[title]";
$t->set_var("catenav", $catenav);

/*
 *make subcate nav
 */
//if the category has subcategory display it or there display nothing
if (isset($category_cache[$cid])){

	foreach ($category_cache[$cid] as $k => $cates){
		$tdnum++;
		$t->set_var("cateclistspc",(($tdnum % 3 == 0)? '</tr><tr>': ''));
		$cateclist="<a href=category.php?cid=$cates[cateid]><FONT CLASS=normalfont>$cates[title]</font></a><br>";
		$cateclist.=show_category_list($cates[cateid]);
		$t->set_var("cateclist", $cateclist);
		$t->parse("cateLists","cateList", true);
	}
}else{
	$t->set_var("undercate",'');
}


require('catemenu.php');


//make cate jump
$jumpmenu=get_category_dropdown($cid,1);
$t->set_var("jumpmenu",$jumpmenu);


//the function that make cate list
function show_category_list($cid = 0, $depth = 1){
	global $category_cache,$cateclist;

	if (!isset($category_cache[$cid])) {
		return false;
	}
	foreach ($category_cache[$cid] as $key => $cats) {
		$catelist.="<font class=middlefont><a href=category.php?cid=$cats[cateid]>$cats[title]</a>&nbsp;&nbsp;&nbsp;</font>";
	}
	return $catelist;
	//unset($category_cache[$cid]);
}
$t->pparse("cateDisplay","cateDisplay");
?>
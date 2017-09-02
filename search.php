<?php
/*******************************************************************
 * File: search.php
 * Version: 1.3.1
 * Date: 2002/04/15
 *
 * Copyright (C) 2002 Fastboard Team. All rights reserved.
 *
 * This software is the proprietary information of Fastboard Team.
 * Use is subject to license terms.
 */
define('getCache',1);
require('article_global.inc.php');

if(empty($keyword)) {

	$t->preload('search');
	$t->preload('cate_menu');
	initialize();
	$t->set_template("search","search"); 


	require('catemenu.php');

	$jumpmenu=get_category_dropdown(0);
	$t->set_var("forumselect",$jumpmenu);

	$t->pparse("search","search"); 

	
	exit();
}
if($keyword && strlen($keyword)<2) {
	showerror('keyword_too_short');
}

$page = (!($page && isint($page))) ? 1 : $page;

$t->preload('searchresult');
$t->preload('cate_menu');
initialize();


$t->set_template("searchResult","searchresult"); 
$t->set_block("searchResult","article","articles"); 

$t->set_var('page', $page);
$t->set_var('keyword', $keyword);
$t->set_var('type', $type);
$t->set_var('cateid', $cateid);




$key = addslashes($keyword);
$conditions = array();
if($keyword) {
	$keyword_conditions = array();
	if($searchTitle) {
		$keyword_conditions[0] = "$table_article.title like '%$key%'";
	}
	if($searchInSubtitle) {
		$keyword_conditions[1] = "$table_page.subtitle like '%$key%'";
		$searchPage = true;
	}
	if($searchInContent) {
		$keyword_conditions[2] = "$table_page.content like '%$key%'";
		$searchPage = true;
	}
	
	$conditions[0] = join(' OR ', $keyword_conditions);
	if($conditions[0]) $conditions[0] = '('.$conditions[0].')';
		else unset($conditions[0]);
}

if (isset($cateid)){
	$ids=get_all_cat($cateid);
	$ids[]=$cateid;
	
	$conditions[1] =  $table_article.'.cateid IN ('.join(',', $ids). ')';
	
}

$condition = 'WHERE '.join(' AND ', $conditions);

$t->set_var("condition", $condition);

switch($orderby) {
		case 'posttime': $orderby = "posttime $sortorder"; break;
		case 'clicktimes': $orderby = "clicktimes $sortorder"; break;
		case 'rating': $orderby = "rating $sortorder"; break;
		default:       $orderby = "posttime DESC"; break;
}
$joinMode = $searchPage ? "$table_page LEFT JOIN $table_article USING(articleid)" : "$table_article";

$distinctMode = $searchPage ? "DISTINCT" : "";

$articleCount = $DB_site->query_first("SELECT count(*) FROM $joinMode $condition");




if($articleCount<1) {
	showerror('search_noresult');
}
$totalPage = ceil($articleCount/$articlesPP);

//if(!isset($page)) {
//	$page = 1;
//}
$beginRow = ($page-1)*$articlesPP;

$t->set_var("totalarticle", $articleCount);
$t->set_var("totalpage", $totalPage);
$t->set_var("beginaticle", $beginRow+1);
$t->set_var("endaticle", $beginRow+$articlesPP);

//make page nav
if($totalPage > 1) {
	$prenum=$page-1;
	$nextnum=$page+1;
	($page<=1) ? $prevPage="" : $prevPage="<a href=search.php?page=$prenum&keyword=$keyword&searchTitle=$searchTitle&searchInSubtitle=$searchInSubtitle&searchInContent=$searchInContent&cat_id=$cat_id&orderby=$orderby><img src=images/pre.gif border=0>pre</a>";
	($page>=$totalPage) ? $nextPage="" : $nextPage="<a href=category.php?page=$nextnum&keyword=$keyword&searchTitle=$searchTitle&searchInSubtitle=$searchInSubtitle&searchInContent=$searchInContent&cat_id=$cat_id&orderby=$orderby>next<img src=images/next.gif border=0></a>";
	$pageStatus=$prevPage.'&nbsp;'.$nextPage;
	$t->set_var("pageStatus", $pageStatus);
} else {
	$t->set_var("pageStatus", '');
}



$articles = $DB_site->query(
		"SELECT $distinctMode $table_article.*,$table_cate.cateid,$table_cate.title AS catetitle FROM $joinMode
LEFT JOIN $table_cate using (cateid)
		$condition ORDER BY $orderby  limit $beginRow,$articlesPP");

//---- display ----//
while ($article=$DB_site->fetch_array($articles)){
	
	
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
			for ($i=0; $i<$intavg; $i++){
				$ratingimg.="<img src=images/on.gif border=0 alt=$ratingavg/$article[votes]>";
			}
			for ($i=0; $i<10-$intavg; $i++){
				$ratingimg.="<img src=images/off.gif border=0 alt=$ratingavg/$article[votes]>";
			}
			$t->set_var("articlerating", $ratingimg);
		}
	}
	$t->parse("articles","article", true);
}

//dispaly left menu
require('catemenu.php');

$t->pparse("searchResult","searchResult");


?>
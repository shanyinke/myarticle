<?php
/*******************************************************************
 * File: index.php
 * Version: 0.0.3
 * Date: 2002/11/25
 *
 * Copyright (C) 2002 Myluntan Team. All rights reserved.
 *
 * This software is the proprietary information of Myluntan Team Team.
 * Use is subject to license terms.
 */


/**
 * Load global library
 */

require('article_global.inc.php');

if (!isset($aid)){
	showerror('error_articleid');
}

if (!isset($page)){
	$page=1;
}

$t->preload('showarticle');
$t->preload('cate_menu');




$t->caching= using_cache;

if (!$t->is_cached("showArticle",$aid."|".$page)){

	initialize();

	$t->set_template("showArticle","showarticle"); 


	$cookieName="rate".$aid;
	$cookie_name=getCookieName($cookieName);


	if ($_COOKIE[$cookie_name]=='y'){
		$t->set_block("showArticle","ratingform","ratingforms"); 
	
	}

	



	$article=$DB_site->query_first("SELECT $table_article.*,$table_page.* FROM $table_article 
	LEFT JOIN $table_page USING (articleid)
	WHERE $table_article.articleid=$aid and $table_page.pagenum=$page");

	//make cate nav
	$articlenav="<a href=index.php>".hometitle."</a><img src=images/next.gif border=0>";
	$navcache=get_nav($article[cateid],0);
	$articleNav=array_reverse($navcache, TRUE);
	foreach ($articleNav AS $key=>$navs) {
		$articlenav.="<a href=category.php?cid=$navs[navid]>$navs[navtitle]</a><img src=images/next.gif border=0>";
		$articlepcateid.="/".$navs[navid];
	}
	$articlenav.="$article[title]";
	$t->set_var("articlenav", $articlenav);

	$content=parseString($article[content]);

	if ($article[rating]==0){
		$t->set_var("articlerating", 'not rated yet');
	}else{
		if ($article[votes]< numofshowrate){
			$t->set_var("articlerating", 'less than '.numofshowrate.' votes');
		}else{
			$ratingavg=round($article[rating]/$article[votes],2);
		
			$intavg=ceil($ratingavg);
			
			$ratingimg.=str_repeat("<img src=images/on.gif border=0 alt=$ratingavg/$article[votes]>", $intavg);
				
			$ratingimg.=str_repeat("<img src=images/off.gif border=0 alt=$ratingavg/$article[votes]>", 10-$intavg);

			$t->set_var("articlerating", $ratingimg);


		}
	}
	$t->set_var("articleid", $article[articleid]);
	$t->set_var("articletitle", $article[title]);
	$t->set_var("articletime", getTime($article[posttime]));
	$t->set_var("articleauthor", $article[author]);
	$t->set_var("articlesubtitle", $article[subtitle]);
	$t->set_var("articlecontent",$content);

	$t->set_var("commnetnum",$article[commentnum]);
	//$t->parse("articles","article", true);


	require('catemenu.php');

	$jumpmenu=get_category_dropdown($article[cateid],1);
	$t->set_var("jumpmenu",$jumpmenu);

	$allpages=$DB_site->query("SELECT pagenum,subtitle FROM $table_page WHERE articleid=$aid");
	$t->set_block("showArticle","pageoption","pageoptions"); 
	$t->set_block("showArticle","pagejump","pagejump"); 


	while ($everypage=$DB_site->fetch_array($allpages)){
		$t->set_var("pagenum", $everypage[pagenum]);
		$t->set_var("subtitle", $everypage[subtitle]);
		$t->parse ("pageoptions","pageoption",true);
		$totalPage++;
	}


	if ($totalPage > 1){
		$t->parse ("pagejump","pagejump");


	}else{
		$t->set_var("pagejump", '');
	}


	if($totalPage > 1) {
		$prenum=$page-1;
		$nextnum=$page+1;
		($page<=1) ? $prevPage="" : $prevPage="<a href=article.php?aid=$aid&page=$prenum><img src=images/pre.gif border=0>pre</a>";
		($page>=$totalPage) ? $nextPage="" : $nextPage="<a href=article.php?aid=$aid&page=$nextnum>next<img src=images/next.gif border=0></a>";
		$pageStatus=$prevPage.'&nbsp;'.$nextPage;
		$t->set_var("pageStatus", $pageStatus);
	} else {
		$t->set_var("pageStatus", '');
	}


}
//update article info
$DB_site->query("UPDATE $table_article SET clicktimes=clicktimes+1 WHERE articleid=$aid");

$t->pparse("showArticle","showArticle",false,$aid."|".$page); 



?>
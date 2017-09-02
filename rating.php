<?php
/*******************************************************************
 * File: rating.php
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

 require('article_global.inc.php');

if (!isset($aid)){
	showerror('error_articleid');
}


if (!isset($rating)){
	showerror('error_norating');
}

 

$cookieName="rate".$aid;

$cookie_name=getCookieName($cookieName);


if ($_COOKIE[$cookie_name]=='y'){
	showerror('error_multirate');
	
}else{
	cookie($cookieName,'y');
	
	$t->preload('votesuccess');

	initialize();

	$t->set_template("voteSuccess","votesuccess");
	
	$DB_site->query("UPDATE $table_article SET rating=rating+$rating,votes=votes+1 WHERE articleid=$aid");

	$pages=$DB_site->query("select pagenum FROM $table_page WHERE articleid=$aid");

	while ($onepage=$DB_site->fetch_array($pages)){
		
		$t->clear_cache("showArticle",$aid."|".$onepage[pagenum]);
	}
	$t->pparse("voteSuccess","voteSuccess");
	
	
}
?>
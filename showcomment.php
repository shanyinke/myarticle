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




if (!isset($_GET[aid])){
	showerror('error_articleid');
}else{
	$aid=$_GET[aid];
}


/**
if (!isset($page)){
	$page=1;
}
 */

//preload templates
$t->preload('showcomment');
$t->preload('cate_menu');

initialize();

$t->set_template("showComment","showcomment"); 

$t->set_block("showComment","comment","comments");

$t->set_var("articleid",$aid);


/**
 *display comment
 */

$comments=$DB_site->query("SELECT * FROM $table_comment  WHERE articleid=$aid");

if ($comments){
	while ($comment=$DB_site->fetch_array($comments)){

		$t->set_var("commentsubject", $comment[subject]);
		$t->set_var("commenttime", getTime($comment[dateline]));
		$t->set_var("commentauthor", $comment[name]);
		$t->set_var("commentcontent", $comment[content]);

		$t->parse ("comments","comment",true);

	}
}else{
	$t->set_var("comment", "there is no comment");
}

//display the cate menu
require('catemenu.php');


$t->pparse("showComment","showComment"); 

?>
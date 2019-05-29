<?php
/*******************************************************************
 * File: commnet.php
 * Version: 0.0.3
 * Date: 2002/11/01
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


//if ($_POST['submit']){

	if (!isset($_POST['articleid'])){
		showerror('error_articleid');
	}else{
		$articleid=$_GET['articleid'];
	}
	
	


	if ($_POST['subject'] && $_POST['content']){
		$articleid=$_POST[articleid];
		$name=un_badchars($_POST[name]);
		$content=un_badchars($_POST[content]);
		$subject=un_badchars($_POST[subject]);
		$email=un_badchars($_POST[email]);

		$DB_site->query("INSERT INTO $table_comment (commentid, articleid,  dateline, name, content, subject, email ) VALUES ('', '$articleid', '".time()."' ,'$name' , '$content' , '$subject', '$email')");

		$DB_site->query("UPDATE $table_article SET commentnum =commentnum +1 WHERE articleid=$articleid");
		$pages=$DB_site->query("select pagenum FROM $table_page WHERE articleid=$articleid");
	
		while ($onepage=$DB_site->fetch_array($pages)){
		
			$t->clear_cache("showArticle",$articleid."|".$onepage[pagenum]);
		}
		
		showsuccess("success_addcomment","article.php?aid=$articleid");
	}else{
		showerror('error_articleid');
	}
//}



?>
<?php
/*******************************************************************
 * File: cate.php
 * Version: 0.0.1
 * Date: 2002/07/25
 *
 * Copyright (C) 2002 Myluntan Team. All rights reserved.
 *
 * This software is the proprietary information of Myluntan Team Team.
 * Use is subject to license terms.
 */


//include('./article_global.inc.php');

$t->set_template('displayMenu','cate_menu');
$t->set_block ("displayMenu","menucate","menucates");

show_category_menu(0);
function show_category_menu($cid = 0, $depth = 1){
	global $category_cache,$t,$current_cid;

	if ($depth >= 30) $depth --;

	if (!isset($category_cache[$cid])) {
		return false;
	}

	foreach ($category_cache[$cid] as $key => $cats) {
	//$t->set_var('catebgcolor', (($cats['parentid']==0)? rootcolor: getColor()));
	$t->set_var('cateclass', (($cats['parentid']==0)? 'root_td': 'child_td'));
	if($cats['cateid']==$current_cid) {
		$t->set_var('cateclass', 'current_td');
	}
	$t->set_var('IDclass', (($cats['parentid']==0)? 'cattitle': 'td_root_2'));
	$t->set_var('pathimg', (($cats['parentid']==0)? '': '- '));
	$t->set_var('catetitle', htmlspecialchars($cats['title']));
	$t->set_var('cateid', $cats['cateid']);
	if ($depth > 1) {
		$t->set_var('depth', str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $depth-1));
    }
	$t->parse ("menucates","menucate",true);
	$t->set_var('depth', '');
	
	show_category_menu($cats['cateid'], $depth + 1);
	}
	unset($category_cache[$cid]);
}
/*
function getColor() {
  static $bgcolor;
  return ($bgcolor==firstaltcolor ? $bgcolor=secondaltcolor : $bgcolor=firstaltcolor);
}
*/
?>
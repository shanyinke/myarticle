<?php

/**
 * user -> add user
 */

$thisprog='user_editgroup.php';
//define('getCache',1);
include('./admin_global.inc.php');

if (!access("canadmin")){
	shownopermission();
}
init();
setTitle('Edit a group');
/*
if (!isset($usergoupid)){
	showerror();
}
*/
if ($_POST['submit']) {
	if ($_POST['groupTitle']) {
		$permissions=formTosql($_POST['permissions']);
		$DB_site->query("UPDATE $table_usergroup
		SET
		usergroupid ='',
		title      ='".slashesencode($_POST['groupTitle'])."',
		canadd     = '$permissions[canadd]',
		canedit    = '$permissions[canedit]',
		cancomment = '$permissions[cancomment]',
		canpublish = '$permissions[canpublish]',
		canadmin   = '$permissions[canadmin]',' ");
		showSuccess('You have added a new user successfully', 'cate_man_user.php');
	}else{

		$content.='<tr bgcolor=FFFFFF><td colspan=2><font color=red>Please fill up the content</font></td></tr>';
	}

}
	
$permissions=$DB_site->query_first("SELECT * FROM $table_usergroup WHERE usergroupid=$usergroupid");


$title       = $permissions['title'];
$permissions = sqlToform($permissions);

$content .= "<input type=hidden name=usergroupid value='$usergroupid'>";

$content.='<tr bgcolor="#ffffff"><td> grouptitle: </td><td><input type=text name=groupTitle value='.$title.'></td></tr>';
$content.='<tr bgcolor="#ffffff"><td colspan=2><input type=checkbox name=permissions[canadd] '.$permissions[canadd].'> user can post articles</td></tr>';
$content.='<tr bgcolor="#ffffff"><td colspan=2><input type=checkbox name=permissions[canedit] '.$permissions[canedit].'> user can edit articles</td></tr>';
$content.='<tr bgcolor="#ffffff"><td colspan=2><input type=checkbox name=permissions[cancomment] '.$permissions[cancomment].'> user can comment articles</td></tr>';
$content.='<tr bgcolor="#ffffff"><td colspan=2><input type=checkbox name=permissions[canpublish] '.$permissions[canpublish].'> user can publish articles</td></tr>';
$content.='<tr bgcolor="#ffffff"><td colspan=2><input type=checkbox name=permissions[canadmin] '.$permissions[canadmin].'> user can admin sys</td></tr>';

output();


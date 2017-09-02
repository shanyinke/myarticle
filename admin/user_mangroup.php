<?php

/**
 * user -> add user
 */

$thisprog='user_mangroup.php';
//define('getCache',1);
include('./admin_global.inc.php');

if (!access("canadmin")){
	shownopermission();
}
if (!isset($action)){
	$action="modify";
}
init();


if ($action=="removegroup" && $usergroupid){
	setTitle('Remove a new cat');
	$content.= "<tr bgcolor=\"#FFFFFF\">\n";
	$content.="<td ><b>Do you want to delete this usergroup?
		</b></td>\n</tr>\n";
	$content.= "<input type=hidden name=action value=del>";
	$content.= "<input type=hidden name=usergroup value=$usergroupid>";

}
if ($action=="del" && $usergroupid){
	$DB_site->query("DELETE FROM $table_usergroup WHERE usergroupid='$usergroupid'");
	$DB_site->query("DELETE FROM $table_permissions WHERE usergroupid='$usergroupid'");
	showSuccess('You have delete usergroup successfully', $thisprog);
}


if ($action=="modify"){
	setTitle('Manage groups');
	$content.="<tr bgcolor=#ffffff><td> Group title: </td></tr>";

	$groups=$DB_site->query("SELECT * FROM $table_usergroup");
	while ($row=$DB_site->fetch_array($groups)){
		$content.="<tr bgcolor=#ffffff><td> <b>$row[title]: </b>&nbsp;&nbsp;&nbsp;&nbsp;";
		$content.=show_text_link("edit", "user_editgroup.php?usergroupid=".$row[usergroupid]);
		$content.=show_text_link("delete", "user_mangroup.php?action=removegroup&usergroupid=".$row[usergroupid]);
		$content.=show_text_link("set permission", "user_setperm.php?usergroupid=".$row[usergroupid]);


		$content.="</td></tr>";
	}
}

output();


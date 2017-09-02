<?php
define('db_type','mysql');
define('db_user','root');
define('db_pass','admin@6');
define('db_host','localhost');
define('db_name','myarticle');
define('tablepre','my_');

define('language','gb2312');

define('hometitle','MyArticle');
define('home_url','http://our9713.techofficer.net/article/');
define('site_url','http://our9713.techofficer.net/article/');
define('UPLOAD_PATH','upload');

define('numofshowrate','3');
define('articlesPP','5');
define('timeformat','Y-m-d G:i:s');
define('gzip_level','3');
define('fix_unicode','1');
define('default_style','1');
define('using_cache','0');

define('cookie_path','/');
define('cookie_time',1999999999);
define('cookie_head','myarticle_');

define('DIR_SEP', "/");

define('showruntime','<font class=normalfont><center><b>Processed Time: %s s Querys: %s [ Gzip Level %s ]</b></center></font>');
define('script_version','0.03dev');


$tables = array('article', 'cate', 'comment', 'page', 'template', 'templateset', 'user', 'usergroup', 'permissions' );
foreach($tables as $name) {
	${"table_".$name} = tablepre.$name;
}

$user_table_fields = array(
  "user_id" => "userid",
  "user_groupid" => "usergroupid",
  "user_name" => "username",
  "user_password" => "password",
  "user_email" => "email",
  "user_joindate" => "joindate",
  "user_homepage" => "homepage",
  "user_icq" => "icq"
);
?>
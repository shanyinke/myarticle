<?php

if(showruntime) {
	function getMicroTime() {
		$tmp = explode(' ', microtime());
		return (real)$tmp[1]. substr($tmp[0], 1);
	}

	function benchmark() {
		global $starttime, $DB_site,$t;
		$endtime = getMicroTime();
		return sprintf(showruntime, substr($endtime-$starttime, 0, 6), $DB_site->querys ,$t->compress);
	}
	$starttime = getMicroTime();
}

/*
 * get std var
 */
//set_magic_quotes_runtime(0);
//$magic_quotes_gpc = get_magic_quotes_gpc();
$timestamp  = time()+time_zone_offset;
/*
function addslashes_array($array) {
  foreach ($array as $key => $val) {
    $array[$key] = (is_array($val)) ? addslashes_array($val) : addslashes($val);
  }
  return $array;
}

if ($magic_quotes_gpc == 0) {
  $_GET = addslashes_array($_GET);
  $_POST = addslashes_array($_POST);
  $_COOKIE = addslashes_array($_COOKIE);
}

 * start init db
 */
require("./include/db_mysql.inc.php");

$DB_site= new db_mysql(db_name, db_host, db_user, db_pass);


//$DB_site->database=db_name;
//$DB_site->server=db_host;
//$DB_site->user=db_user;
//$DB_site->password=db_pass;

//$DB_site->connect();

$style=default_style;
//template parsing inti

$t = new TEMPLATE($style,gzip_level,$table_template);
$t->preload(array('style_set', 'copyright', 'footer', 'header'));

//****************************************************************

$articlesPP=articlesPP;
$cat_cache = array();
$cat_parent_cache = array();
$category_cache = array();

function initialize() {
	global $t,$DB_site,$cat_cache,$cat_parent_cache,$category_cache;
	
	$t->preload(array('header','headinclude','footer'));
	$t->cachetemplates(); //cache the tempale
	//if(is_object($header)) return;
	$t->set_template(array(
		"header" => "header",
		"footer" =>"footer",
		"headinclude" =>"headinclude",
		"timezone" =>"timezone"));
	$t->set_var("hometitle",hometitle);
	$t->set_var("home_url",home_url);
	$t->set_var("site_url",site_url);
	$t->set_var("script_version",script_version);

	$t->parse("header","header");
	$t->parse("headinclude","headinclude");
	$t->parse("footer","footer");

	getCateCache($cat_cache,$cat_parent_cache,$category_cache);
	
	
}


function getTime($timestamp, $timezoneoffset = 0, $format = 'time') {
	$timestamp += $timezoneoffset;
	return ($format == 'time') ? date(timeformat, $timestamp) : date(dateformat, $timestamp);
}

function parseString($str, $html = true) {
	
	if(!$html) $str = htmlspecialchars($str);
	//return nl2br($str);
	return $str;
}

function msubstr($str, $start, $len) {
	$strlen = $start+$len;
	for($i=0;$i<$strlen;$i++) {
		if(ord(substr($str,$i,1))>160) {
			$tmpstr .= substr($str,$i,2);
			$i++;
		} else {
			$tmpstr .= substr($str,$i,1);
		}
	}
	return $tmpstr;
}

function cookie($cookie_name, $value = '', $time = -1) {
	if($time==-1) $time = cookie_time;
	setCookie(cookie_head.$cookie_name, $value, $time, cookie_path);
}
function getCookieName($cookie_name) {
	return cookie_head.$cookie_name;
}

function get_nav($cateid,$depth=0){
	global $cat_cache,$nav;
	$nav[$depth][navid]=$cat_cache[$cateid][cateid];
	$nav[$depth][navtitle]=$cat_cache[$cateid][title];
	if ($cat_cache[$cateid][parentid]!=0){
		get_nav($cat_cache[$cateid][parentid],$depth+1);
	}
	return $nav;
}

function get_category_dropdown_bits($cat_id, $cid = 0, $depth = 1) {
  global $drop_down_cat_cache, $cat_cache;

  if (!isset($drop_down_cat_cache[$cid])) {
    return "";
  }
  $category_list = "";
  foreach ($drop_down_cat_cache[$cid] as $key => $category_id) {
    //if (check_permission("auth_viewcat", $category_id)) {
      $category_list .= "<option value=\"".$category_id."\"";
      if ($cat_id == $category_id) {
        $category_list .= " selected=\"selected\"";
      }
      if ($cat_cache[$category_id]['parentid'] == -1) {
        $category_list .= " class=\"dropdownmarker\"";
      }

      if ($depth > 1) {
        $category_list .= ">".str_repeat("--", $depth - 1)." ".$cat_cache[$category_id]['title']."</option>\n";
      }
      else {
        $category_list .= ">".$cat_cache[$category_id]['title']."</option>\n";
      }
      $category_list .= get_category_dropdown_bits($cat_id, $category_id, $depth + 1);
    //}
  }
  unset($drop_down_cat_cache[$cid]);
  return $category_list;
}

function get_category_dropdown($cat_id, $jump = 0, $admin = 0, $i = 0) {
  global $drop_down_cat_cache, $cat_parent_cache;
  // $admin = 1  Main Cat (update/add cats)
  // $admin = 2  All Cats (find/validate images...)
  // $admin = 3  Select Cat (update/add image)
  // $admin = 4  No Cat (check new images)

  switch ($admin) {
  case 1:
    $category = "\n<select name=\"cat_parent_id\" class=\"categoryselect\">\n";
    $category .= "<option value=\"0\">main category</option>\n";
    $category .= "<option value=\"0\">--------------</option>\n";
    break;

  case 2:
    $category = "\n<select name=\"cat_id\" class=\"categoryselect\">\n";
    $category .= "<option value=\"0\">all categories</option>\n";
    $category .= "<option value=\"0\">-------------------------------</option>\n";
    break;

  case 3:
    $i = ($i) ? "_".$i : "";
    $category = "\n<select name=\"cat_id".$i."\" class=\"categoryselect\">\n";
    $category .= "<option value=\"0\">select category</option>\n";
    $category .= "<option value=\"0\">-------------------------------</option>\n";
    break;

  case 4:
    $category = "\n<select name=\"cat_id\" class=\"categoryselect\">\n";
    $category .= "<option value=\"0\">no category</option>\n";
    $category .= "<option value=\"0\">-------------------------------</option>\n";
    break;

  case 0:
  default:
    if ($jump) {
      $category = "\n<select name=\"cid\" onchange=\"if (this.options[this.selectedIndex].value != 0){ forms['jumpbox'].submit() }\" class=\"categoryselect\">\n";
    }
    else {
      $category = "\n<select name=\"cateid\" class=\"categoryselect\">\n";
    }
    $category .= "<option value=\"0\">select category</option>\n";
    $category .= "<option value=\"0\">-------------------------------</option>\n";
  } // end switch

  $drop_down_cat_cache = array();
  $drop_down_cat_cache = $cat_parent_cache;
  $category .= get_category_dropdown_bits($cat_id);
  $category .= "</select>\n";
  return $category;
}


function get_all_cat($cid = 0){
	global $category_cache,$allcatid;
	if (!isset($category_cache[$cid])) {
		return false;
	}
	foreach ($category_cache[$cid] as $key => $cats) {
		$allcatid[]=$cats['cateid'];
		get_all_cat($cats['cateid']);
	}
	//unset($category_cache[$cid]);
	return $allcatid;

}

function showerror($error_template) {
	global $t;
	
	$t->preload('error');
	$t->preload($error_template);
	initialize();
	$t->set_template(array(
		"error" => "error",
		"error_msg" =>$error_template
		));
	$jumpmenu=get_category_dropdown(0,1);
	$t->set_var("jumpmenu",$jumpmenu);
	$t->pparse('error','error');
	exit();
}
//****************************************************************
function getCateCache(&$cat_cache,&$cat_parent_cache,&$category_cache) {
	global $DB_site,$table_cate;
	$sql = "SELECT cateid, title, description, parentid, displayorder  
          FROM $table_cate 
          ORDER BY displayorder, cateid ASC";
	$result = $DB_site->query($sql);
	while ($row = $DB_site->fetch_array($result)) {
		$cat_cache[$row['cateid']] = $row;
		$cat_parent_cache[$row['parentid']][] = $row['cateid'];
		$category_cache[$row['parentid']][$row['cateid']] = $row;
	}
	$DB_site->free_result();
	
}

function un_badchars($chars) {
  global  $magic_quotes_gpc;
  $chars =(($magic_quotes_gpc)? $chars : addslashes($chars));
  $chars = preg_replace('/(javascript|about):/i', '\\1 :', $chars);
  $chars= htmlspecialchars($chars);
  return preg_replace('/(\\[:[a-z0-9_\\- ]+:\\])\\s*(\\1\s*){3,}/i', '\\1 \\1 \\1', $chars);
  
}

function showsuccess($success_template, $gotourl = 'index.php') {
	global $t;
	
	$t->preload('success');
	$t->preload($success_template);

	initialize();

	$t->set_template("showSuccess","success");
	$t->set_template("successTemplate",$success_template);
	$t->set_var("url",$gotourl);

	$t->parse("successTemplate","successTemplate",true);

	$t->pparse("showSuccess","showSuccess");
	exit();
}
?>
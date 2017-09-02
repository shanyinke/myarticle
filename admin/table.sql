# phpMyAdmin MySQL-Dump
# version 2.3.3-rc1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Nov 28, 2002 at 09:24 PM
# Server version: 3.23.41
# PHP Version: 4.0.6
# Database : `our9713_db`
# --------------------------------------------------------

#
# Table structure for table `my_article`
#

CREATE TABLE my_article (
  articleid int(10) unsigned NOT NULL auto_increment,
  cateid int(10) NOT NULL default '0',
  posttime int(10) unsigned NOT NULL default '0',
  author varchar(20) NOT NULL default '',
  title varchar(100) NOT NULL default '',
  description text,
  clicktimes int(10) unsigned NOT NULL default '0',
  rating int(2) NOT NULL default '0',
  votes smallint(6) NOT NULL default '0',
  ipaddress varchar(16) NOT NULL default '',
  commentnum smallint(8) NOT NULL default '0',
  PRIMARY KEY  (articleid),
  KEY postid (articleid)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `my_cate`
#

CREATE TABLE my_cate (
  cateid int(3) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  displayorder int(3) unsigned NOT NULL default '1',
  parentid int(3) unsigned NOT NULL default '0',
  articles int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (cateid)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `my_comment`
#

CREATE TABLE my_comment (
  commentid int(10) unsigned NOT NULL auto_increment,
  articleid int(10) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  name varchar(20) NOT NULL default '',
  subject varchar(100) NOT NULL default '',
  content text NOT NULL,
  email varchar(20) NOT NULL default '',
  PRIMARY KEY  (commentid)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `my_page`
#

CREATE TABLE my_page (
  pageid int(10) NOT NULL auto_increment,
  articleid smallint(6) NOT NULL default '0',
  pagenum smallint(6) NOT NULL default '0',
  subtitle varchar(100) NOT NULL default '',
  content text NOT NULL,
  dateline smallint(10) NOT NULL default '0',
  PRIMARY KEY  (pageid)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `my_permissions`
#

CREATE TABLE my_permissions (
  permissionid int(10) unsigned NOT NULL auto_increment,
  usergroupid int(10) unsigned NOT NULL default '0',
  cateid int(10) unsigned NOT NULL default '0',
  canadd enum('y','n') NOT NULL default 'n',
  canedit enum('y','n') NOT NULL default 'n',
  cancomment enum('y','n') NOT NULL default 'n',
  canpublish enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (permissionid)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `my_template`
#

CREATE TABLE my_template (
  templateid int(10) unsigned NOT NULL auto_increment,
  title varchar(30) NOT NULL default '',
  template text,
  templatesetid tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (templateid,title)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `my_templateset`
#

CREATE TABLE my_templateset (
  templatesetid smallint(5) unsigned NOT NULL auto_increment,
  title char(250) NOT NULL default '',
  PRIMARY KEY  (templatesetid)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `my_user`
#

CREATE TABLE my_user (
  userid int(10) unsigned NOT NULL auto_increment,
  username varchar(20) NOT NULL default '',
  password varchar(20) NOT NULL default '',
  usergroupid tinyint(6) NOT NULL default '0',
  email varchar(35) NOT NULL default '',
  joindate smallint(10) NOT NULL default '0',
  PRIMARY KEY  (userid),
  KEY userid (userid)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `my_usergroup`
#

CREATE TABLE my_usergroup (
  usergroupid int(10) unsigned NOT NULL auto_increment,
  title varchar(20) NOT NULL default '',
  canadd enum('y','n') NOT NULL default 'n',
  canedit enum('y','n') NOT NULL default 'n',
  cancomment enum('y','n') NOT NULL default 'n',
  canpublish enum('y','n') NOT NULL default 'n',
  canadmin enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (usergroupid)
) ENGINE=MyISAM;

#
# Dumping data for table `my_usergroup`
#

INSERT INTO my_usergroup VALUES (1, 'member', 'n', 'n', 'y', 'n', 'n');
INSERT INTO my_usergroup VALUES (2, 'author', 'y', 'y', 'y', 'n', 'n');
INSERT INTO my_usergroup VALUES (3, 'publisher', 'y', 'y', 'y', 'y', 'n');
INSERT INTO my_usergroup VALUES (4, 'admin', 'y', 'y', 'y', 'y', 'y');



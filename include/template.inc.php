<?php
class Template {
  var $classname = "Template";

  /* if set, echo assignments */
  var $debug     = false;
  /* $varkeys[key] = "key"; $varvals[key] = "value"; */
  var $varkeys = array();
  var $varvals = array();
	
  /* "remove"  => remove undefined variables
   * "comment" => replace undefined variables with comments
   * "keep"    => keep undefined variables
   */
  var $unknowns = "remove";

  /* "yes" => halt, "report" => report error, continue, "no" => ignore error quietly */
  var $halt_on_error  = "yes";

  /* last error message is retained here */
  var $last_error     = "";
  
  /* 
   *the table of template
   */
  var $table    = "template";

  /* $preloads[varname] = "templatename"; */
  var $preloads = array();

  /* $template[varname] = "template"; */
  var $template = array();

  /* the styleid of template */
  var $templatesetid  = -1;


  /* the template compress */
  var $compress;
  var $caching         =  0;     		// enable caching. can be one of 0/1/2.
										// 0 = no caching
										// 1 = use class cache_lifetime value
										// 2 = use cache_lifetime in cache file
										// default = 0.
  var $cache_dir       =  'cache';    // name of directory for template cache files
  var $cache_lifetime  =  -1;       // number of seconds cached content will persist.
										// 0 = always regenerate cache,
                                        // -1 = never expires. default is one hour (3600)
  //var $cache_handler_func   = null;   // function used for cached content. this is
                                        // an alternative to using the built-in file
                                        // based caching.
  //var $cache_modified_check = false;  // respect If-Modified-Since headers on cached content
  var $_cache_info           = array();    // info that makes up a cache file
  var $use_sub_dirs          = false;		// use sub dirs for cache and compiled files?
											// sub directories are more efficient, but
											// you can set this to false if your PHP environment
											// does not allow the creation of them.
  /***************************************************************************/
  /* public: Constructor.
   * templateset:     templateset.
   * unknowns: how to handle unknown variables.
   */
  function Template($templatesetid = 1, $compress = 3, $table = "template", $unknowns = "remove") {
	$this->set_templateset($templatesetid);
    $this->set_unknowns($unknowns);
	$this->table = $table;
	$this->compress = $compress;
	$this->compress_detect( $compress );
  }

  /***************************************************************************/
  /* public: compress_detect
   * 
   */
  function compress_detect($compress) {
		global $HTTP_ACCEPT_ENCODING;

		$gzip_pos = strpos($HTTP_ACCEPT_ENCODING, 'gzip');
		if($compress && $gzip_pos!==false && ($gzip_pos - strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') != 2)&& function_exists("crc32") and function_exists("gzcompress")) {
			ob_start();
			ob_implicit_flush(0);
			
		}
		else
		{
			$this->compress = 0;
		}
	}

  /***************************************************************************/
  /* public: set_unknowns(enum $unknowns)
   * unknowns: "remove", "comment", "keep"
   */
  function set_unknowns($unknowns = "remove") {
    $this->unknowns = $unknowns;
  }
  /***************************************************************************/
  /* public: set_templateset($templatesetid)
   * 
   */
  function set_templateset($templatesetid) {
		$this->templatesetid = $templatesetid;
	}
  /***************************************************************************/
  /* public: preLoad(array $templatelist)
   * templatelist: array of varname, templatename pairs.
   *
   * 
   * varname: varname for a templatename,
   * templatename: name of template
   * 
   */
   function preload($templateName = '') {
		if(is_array($templateName)) {

			foreach ( $templateName as $eachTemplateName )
				$this->preloads[$eachTemplateName] = $eachTemplateName;
			/* old
			$loads = count($templateName);
			for($i = 0; $i<$loads; $i++) {
				$this->preloads[$templateName[$i]] = $templateName[$i];
			}
			*/
		} else {
			$this->preloads[$templateName] = $templateName;
		}
	}
  /***************************************************************************/
  /* public: cachetemplates()(array $preloads)
   * 
   */
	function cachetemplates() {
		global $DB_site,$templatecache;
		if(!count($this->preloads)) return;
		$templatesetcondition = ($this->templatesetid) ? '(templatesetid=-1 OR templatesetid=%s)' : 'templatesetid=-1';
		$temps = $DB_site->query(sprintf(
			"SELECT template,title FROM %s
				where (title IN ('%s')) AND $templatesetcondition
			ORDER BY templatesetid",
			$this->table, join("','", $this->preloads), $this->templatesetid));

		while($temp=$DB_site->fetch_array($temps)) {
			$this->templatecache["$temp[title]"] = $temp['template'];
		}
	}
  /***************************************************************************/
  /* public: set_file(array $filelist)
   * filelist: array of varname, filename pairs.
   *
   * public: set_file(string $varname, string $filename)
   * varname: varname for a filename,
   * filename: name of template file
   */
  function set_template($varname, $templatename = "") {
    if (!is_array($varname)) {
      if ($templatename == "") {
        $this->halt("set_template: For varname $varname templatename is empty.");
        return false;
      }
      $this->template[$varname] = $this->templatecache[$templatename];
    } else {
      reset($varname);
	  foreach ( $varname as $v=> $f ){
        if ($f == "") {
          $this->halt("set_template: For varname $v templatename is empty.");
          return false;
        }
        $this->template[$v] = $this->templatecache[$f];
      }
    }
    return true;
  }


  /***************************************************************************/
  /* public: set_block(string $parent, string $varname, string $name = "")
   * extract the template $varname from $parent,
   * place variable {$name} instead.
   */
  function set_block($parent, $varname, $name = "") {
    if (!$this->loadfile($parent)) {
      $this->halt("set_block: unable to load $parent.");
      return false;
    }
    if ($name == "") {
      $name = $varname;
    }

    $str = $this->get_var($parent);
    $reg = "/<!--\s+BEGIN $varname\s+-->(.*)\s*<!--\s+END $varname\s+-->/sm";
    preg_match_all($reg, $str, $m);
    $str = preg_replace($reg, "{" . "$name}", $str);
    $this->set_var($varname, $m[1][0]);
    $this->set_var($parent, $str);
    return true;
  }


  /***************************************************************************/
  /* public: set_var(array $values)
   * values: array of variable name, value pairs.
   *
   * public: set_var(string $varname, string $value)
   * varname: name of a variable that is to be defined
   * value:   value of that variable
   */
  function set_var($varname, $value = "") {
    if (!is_array($varname)) {
      if (!empty($varname)) {
        if ($this->debug) print "scalar: set *$varname* to *$value*<br>\n";
        $value = preg_replace(array('/\$([0-9])/', '/\\\\([0-9])/'), array('&#36;\1', '&#92;\1'), $value);
        $this->varkeys[$varname] = "/".$this->varname($varname)."/";
        $this->varvals[$varname] = $value;
      }
    } else {
      reset($varname);
	  foreach ( $varname as $k => $v ) {
        if (!empty($k)) {
          if ($this->debug) print "array: set *$k* to *$v*<br>\n";
          $v = preg_replace(array('/\$([0-9])/', '/\\\\([0-9])/'), array('&#36;\1', '&#92;\1'), $v);
          $this->varkeys[$k] = "/".$this->varname($k)."/";
          $this->varvals[$k] = $v;
        }
      }
    }
  }


  /***************************************************************************/
  /* public: subst(string $varname)
   * varname: varname of template where variables are to be substituted.
   */
  function subst($varname) {
    if (!$this->loadfile($varname)) {
      $this->halt("subst: unable to load $varname.");
      return false;
    }

    $str = $this->get_var($varname);
	//$str = $this->template[$varname]
    $str = preg_replace($this->varkeys, $this->varvals, $str);
    return $str;
  }


  /***************************************************************************/
  /* public: psubst(string $varname)
   * varname: varname of template where variables are to be substituted.
   */
  function psubst($varname) {
    print $this->subst($varname);

    return false;
  }


  /***************************************************************************/
  /* public: parse(string $target, string $varname, boolean append)
   * public: parse(string $target, array  $varname, boolean append)
   * target: varname of variable to generate
   * varname: varname of template to substitute
   * append: append to target varname
   */

  function parse($target, $varname, $append = false, $_cache_id=null) {
	  /**/
	if ($this->caching and $_cache_id!=null) {
		if($this->_read_cache_file($target, $_cache_id, $str)){
			return $str;
                
		} else {
			$this->_cache_info = array();
			$this->_cache_info['template'][] = $target;
			

		}
	}
	
	
    if (!is_array($varname)) {
      $str = $this->subst($varname);
      if ($append) {
        $this->set_var($target, $this->get_var($target) . $str);
      } else {
        $this->set_var($target, $str);
      }
    } else {
      reset($varname);
      while(list($i, $v) = each($varname)) {
        $str = $this->subst($v);
        $this->set_var($target, $str);
      }
    }
	/**/
	if ($this->caching and $_cache_id!=null) {
		$this->_write_cache_file($target, $_cache_id, $str);
    }
    return $str;
  }


  /***************************************************************************/
  function pparse($target, $varname, $append = false, $_cache_id=null) {

	/*
	if ($this->caching) {
		if($this->_read_cache_file($target, $_cache_id, $_results)){
		
            $this->gzipOutPut($_results);
			return true;
                
		} else {
			$this->_cache_info = array();
			$this->_cache_info['template'][] = $target;
			

		}
	}*/
	$_results=$this->finish($this->parse($target, $varname, $append, $_cache_id));
	/*
	if ($this->caching) {
            $this->_write_cache_file($target, $_cache_id, $_results);
    }*/
	$this->gzipOutPut($_results);
	
			
    return false;
  }


  /***************************************************************************/
  /* public: get_vars()
   * return all variables as an array (mostly for debugging)
   */
  function get_vars() {
    reset($this->varkeys);
    while(list($k, $v) = each($this->varkeys)) {
      $result[$k] = $this->get_var($k);
    }
    return $result;
  }


  /***************************************************************************/
  /* public: get_var(string varname)
   * varname: name of variable.
   *
   * public: get_var(array varname)
   * varname: array of variable names*/
   
  function get_var($varname) {
    if (!is_array($varname)) {
      if (isset($this->varvals[$varname])) {
        $str = $this->varvals[$varname];
      } else {
        $str = "";
      }
      return $str;
    } else {
      reset($varname);
      while(list($k, $v) = each($varname)) {
        if (isset($this->varvals[$v])) {
          $str = $this->varvals[$v];
        } else {
          $str = "";
        }
        $result[$v] = $str;
      }
      return $result;
    }
  }
	

  /***************************************************************************/
  /* public: get_undefined($varname)
   * varname: varname of a template.
   */
  function get_undefined($varname) {
    if (!$this->loadfile($varname)) {
      $this->halt("get_undefined: unable to load $varname.");
      return false;
    }

    preg_match_all("/{([^ \t\r\n}]+)}/", $this->get_var($varname), $m);
    $m = $m[1];
    if (!is_array($m)) {
      return false;
    }

    reset($m);
    while(list($k, $v) = each($m)) {
      if (!isset($this->varkeys[$v])) {
        $result[$v] = $v;
      }
    }

    if (count($result)) {
      return $result;
    } else {
      return false;
    }
  }


  /***************************************************************************/
  /* public: finish(string $str)
   * str: string to finish.
   */
  function finish($str) {
    switch ($this->unknowns) {
      case "keep":
      break;

      case "remove":
        $str = preg_replace('/{[^ \t\r\n}]+}/', "", $str);
      break;

      case "comment":
        $str = preg_replace('/{([^ \t\r\n}]+)}/', "<!-- Template variable \\1 undefined -->", $str);
      break;
    }

    $str = preg_replace(array('/&#36;([0-9])/', '/&#92;([0-9])/'), array('$\1', '\\\1'), $str);
    return $str;
  }


  /***************************************************************************/
  function get($varname) {
    return $this->finish($this->get_var($varname));
  }


  /***************************************************************************/
  /* private: filename($filename)
   * filename: name to be completed.
   
  function filename($filename) {
    if (substr($filename, 0, 1) != "/") {
      $filename = $this->root."/".$filename;
    }

    if (!file_exists($filename)) {
      $this->halt("filename: file $filename does not exist.");
    }

    return $filename;
  }

  */
  /***************************************************************************/
  /* private: varname($varname)
   * varname: name of a replacement variable to be protected.
   */
  function varname($varname) {
    return preg_quote("{".$varname."}");
  }


  /***************************************************************************/
  /* private: loadfile(string $varname)
   * varname:  load file defined by varname, if it is not loaded yet.
   */
  function loadfile($varname) {
    if (!isset($this->template[$varname])) {
      // $varname does not reference a file so return
      return true;
    }

    if (isset($this->varvals[$varname])) {
      // will only be unset if varname was created with set_file and has never been loaded
      // $varname has already been loaded so return
      return true;
    }
    $file = $this->template[$varname];

    /* use @file here to avoid leaking filesystem information if there is an error 
    //$str = implode("", @file($filename));
    if (empty($filename)) {
      $this->halt("loadfile: While loading $varname, $filename does not exist or is empty.");
      return false;
    }*/

    $this->set_var($varname, $file);

    return true;
  }
	

  /***************************************************************************/
  /* public: halt(string $msg)
   * msg:    error message to show.
   */
  function halt($msg) {
    $this->last_error = $msg;

    if ($this->halt_on_error != "no") {
      $this->haltmsg($msg);
    }

    if ($this->halt_on_error == "yes") {
      die("<b>Halted.</b>");
    }

    return false;
  }


  /***************************************************************************/
  /* public, override: haltmsg($msg)
   * msg: error message to show.
   */
  function haltmsg($msg) {
    printf("<b>Template Error:</b> %s<br>\n", $msg);
  }
  /***************************************************************************/
  /* public, override: gzipOutPut($msg)
   * msg: out put the page.
   */
  function gzipOutPut($content) {
		if(showruntime) {
			$bench = benchmark();
		}
		$content.=$bench;
		if (!$this->compress) {
			print $content;
		}else{
		
			header('Content-Encoding: gzip');
			// zip
			
			print pack('cccccccc',0x1f,0x8b,0x08,0x00,0x00,0x00,0x00,0x00); 
			$Size = strlen($content); 
			$Crc = crc32($content); 
			$content = gzcompress($content, $this->compress); 
			$content = substr($content, 0, strlen($content) - 4); 
			print $content; 
			print pack('V',$Crc); 
			print pack('V',$Size); 
			exit;
		}
  }

  /*======================================================================*\
    Function:   _read_file()
    Purpose:    read in a file from line $start for $lines.
                read the entire file if $start and $lines are null.
\*======================================================================*/
    function _read_file($filename, $start=null, $lines=null)
    {
        if (!($fd = @fopen($filename, 'r'))) {
            return false;
        }
        flock($fd, LOCK_SH);
        if ($start == null && $lines == null) {
            // read the entire file
            $contents = fread($fd, filesize($filename));
        } else {
            if ( $start > 1 ) {
                // skip the first lines before $start
                for ($loop=1; $loop < $start; $loop++) {
                    fgets($fd, 65536);
                }
            }
            if ( $lines == null ) {
                // read the rest of the file
                while (!feof($fd)) {
                    $contents .= fgets($fd, 65536);
                }
            } else {
                // read up to $lines lines
                for ($loop=0; $loop < $lines; $loop++) {
                    $contents .= fgets($fd, 65536);
                    if (feof($fd)) {
                        break;
                    }
                }
            }
        }
        fclose($fd);
        return $contents;
    }

/*======================================================================*\
    Function:   _write_file()
    Purpose:    write out a file
\*======================================================================*/
    function _write_file($filename, $contents, $create_dirs = false)
    {
        if ($create_dirs)
            $this->_create_dir_structure(dirname($filename));

        if (!($fd = @fopen($filename, 'w'))) {
            $this->halt("problem writing '$filename.'");
            return false;
        }

        // flock doesn't seem to work on several windows platforms (98, NT4, NT5, ?),
        // so we'll not use it at all in windows.

        if ( strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || (flock($fd, LOCK_EX)) ) {
            fwrite( $fd, $contents );
            fclose($fd);
            chmod($filename, 0777);
        }

        return true;
    }

/*======================================================================*\
    Function: _get_auto_filename
    Purpose:  get a concrete filename for automagically created content
\*======================================================================*/
    function _get_auto_filename($auto_base, $auto_source = null, $auto_id = null)
    {
		
		static $_dir_sep = null;
		static $_dir_sep_enc = null;
		
		if(!isset($_dir_sep)) {
			$_dir_sep_enc = urlencode(DIR_SEP);
			if($this->use_sub_dirs) {
				$_dir_sep = DIR_SEP;
			} else {
				$_dir_sep = '^';		
			}
		}
		
		if(@is_dir($auto_base)) {
        	$res = $auto_base . DIR_SEP;
		} else {
			// auto_base not found, try include_path
			//$this->_get_include_path($auto_base,$_include_path);
			//$res = $_include_path . DIR_SEP;
			 $this->halt("\"$auto_base\" is not a directory ");
		}
		
		if(isset($auto_id)) {
			// make auto_id safe for directory names
			$auto_id = str_replace('%7C','|',(urlencode($auto_id)));
			// split into separate directories
			$auto_id = str_replace('|', $_dir_sep, $auto_id);
        	$res .= $auto_id . $_dir_sep;
		}
		
		if(isset($auto_source)) {
			// make source name safe for filename
			if($this->use_sub_dirs) {
				$_filename = urlencode(basename($auto_source));
				$_crc32 = crc32($auto_source) . $_dir_sep;
				// prepend %% to avoid name conflicts with
				// with $auto_id names
				$_crc32 = '%%' . substr($_crc32,0,3) . $_dir_sep . '%%' . $_crc32;
				$res .= $_crc32 . $_filename . '.php';
			} else {
        		$res .= str_replace($_dir_sep_enc,'^',urlencode($auto_source));
			}
		}
		//$res=$auto_base.'/'.$auto_id.'.php';
        return $res;
    }



/*======================================================================*\
    Function: _rm_auto
    Purpose: delete an automagically created file by name and id
\*======================================================================*/
    function _rm_auto($auto_base, $auto_source = null, $auto_id = null, $exp_time = null)
    {
        if (!is_dir($auto_base))
          return false;

		if(!isset($auto_id) && !isset($auto_source)) {
			$res = $this->_rmdir($auto_base, 0, $exp_time);			
		} else {		
        	$tname = $this->_get_auto_filename($auto_base, $auto_source, $auto_id);
			
			if(isset($auto_source)) {
				$res = @unlink($tname);
			} elseif ($this->use_sub_dirs) {
				$res = $this->_rmdir($tname, 1, $exp_time);
			} else {
				// remove matching file names
				$handle = opendir($auto_base);
        		while ($filename = readdir($handle)) {
					if($filename == '.' || $filename == '..') {
						continue;	
					} elseif (substr($auto_base . DIR_SEP . $filename,0,strlen($tname)) == $tname) {
						$this->_unlink($auto_base . DIR_SEP . $filename, $exp_time);
					}
				}
			}
		}

        return $res;
    }

/*======================================================================*\
    Function: _rmdir
    Purpose: delete a dir recursively (level=0 -> keep root)
    WARNING: no security whatsoever!!
\*======================================================================*/
    function _rmdir($dirname, $level = 1, $exp_time = null)
    {

       if($handle = @opendir($dirname)) {

        	while ($entry = readdir($handle)) {
            	if ($entry != '.' && $entry != '..') {
                	if (is_dir($dirname . DIR_SEP . $entry)) {
                    	$this->_rmdir($dirname . DIR_SEP . $entry, $level + 1, $exp_time);
                	}
                	else {
                    	$this->_unlink($dirname . DIR_SEP . $entry, $exp_time);
                	}
            	}
        	}

        	closedir($handle);

        	if ($level)
            	@rmdir($dirname);
        	
			return true;
		
		} else {
       	 	return false;
		}
    }

/*======================================================================*\
    Function: _unlink
    Purpose: unlink a file, possibly using expiration time
\*======================================================================*/
    function _unlink($resource, $exp_time = null)
    {
		if(isset($exp_time)) {
			if(time() - filemtime($resource) >= $exp_time) {
				unlink($resource);
			}
		} else {			
			unlink($resource);
		}
    }
	
/*======================================================================*\
    Function: _create_dir_structure
    Purpose:  create full directory structure
\*======================================================================*/
    function _create_dir_structure($dir)
    {
        if (!@file_exists($dir)) {
            $dir_parts = preg_split('!\\'.DIR_SEP.'+!', $dir, -1, PREG_SPLIT_NO_EMPTY);
            $new_dir = ($dir{0} == DIR_SEP) ? DIR_SEP : '';
            foreach ($dir_parts as $dir_part) {
                $new_dir .= $dir_part;
                if (!file_exists($new_dir) && !mkdir($new_dir, 0771)) {
                    $this->halt("problem creating directory \"$dir\"");
                    return false;
                }
                $new_dir .= DIR_SEP;
            }
        }
    }

/*======================================================================*\
    Function:   _write_cache_file
    Purpose:    Prepend the cache information to the cache file
                and write it
\*======================================================================*/
    function _write_cache_file($tpl_file, $cache_id, $results)
    {
        // put timestamp in cache header
        $this->_cache_info['timestamp'] = time();
        if ($this->cache_lifetime > -1){
            // expiration set
            $this->_cache_info['expires'] = $this->_cache_info['timestamp'] + $this->cache_lifetime;
        } else {
            // cache will never expire
            $this->_cache_info['expires'] = -1;
        }

        // prepend the cache header info into cache file
        $results = serialize($this->_cache_info)."\n".$results;

        
            // use local cache file
        if (isset($cache_id)){
            $auto_id =  $cache_id;
		}else{
            $auto_id = null;
		}

        $cache_file = $this->_get_auto_filename($this->cache_dir, $tpl_file, $auto_id);
        $this->_write_file($cache_file, $results, true);
        return true;
        
    }

/*======================================================================*\
    Function:   _read_cache_file
    Purpose:    read a cache file, determine if it needs to be
                regenerated or not
\*======================================================================*/
    function _read_cache_file($tpl_file, $cache_id, &$results)
    {
        static  $content_cache = array();


        if (isset($content_cache["$tpl_file,$cache_id,$compile_id"])) {
            list($results, $this->_cache_info) = $content_cache["$tpl_file,$cache_id,$compile_id"];
            return true;
        }

        
        if (isset($cache_id)){
            	$auto_id = $cache_id;
		}else{
                $auto_id = null;
		}

        $cache_file = $this->_get_auto_filename($this->cache_dir, $tpl_file, $auto_id);
        $results = $this->_read_file($cache_file);
       

        if (empty($results)) {
            // nothing to parse (error?), regenerate cache
            return false;
        }

        $cache_split = explode("\n", $results, 2);
        $cache_header = $cache_split[0];

        $this->_cache_info = unserialize($cache_header);

        if ($this->caching == 2 && isset ($this->_cache_info['expires'])){
            // caching by expiration time
            if ($this->_cache_info['expires'] > -1 && (time() > $this->_cache_info['expires'])) {
            // cache expired, regenerate
            return false;
            }
        } else {
            // caching by lifetime
            if ($this->cache_lifetime > -1 && (time() - $this->_cache_info['timestamp'] > $this->cache_lifetime)) {
            // cache expired, regenerate
            return false;
            }
        }
		/*
        if ($this->compile_check) {
            foreach ($this->_cache_info['template'] as $template_dep) {
                $this->_fetch_template_info($template_dep, $template_source, $template_timestamp, false);
                if ($this->_cache_info['timestamp'] < $template_timestamp) {
                    // template file has changed, regenerate cache
                    return false;
                }
            }

            if (isset($this->_cache_info['config'])) {
                foreach ($this->_cache_info['config'] as $config_dep) {
                    if ($this->_cache_info['timestamp'] < filemtime($this->config_dir.DIR_SEP.$config_dep)) {
                        // config file has changed, regenerate cache
                        return false;
                    }
                }
            }
        }*/

        $results = $cache_split[1];
        $content_cache["$tpl_file,$cache_id,$compile_id"] = array($results, $this->_cache_info);

        return true;
    }
/*======================================================================*\
    Function:   clear_cache()
    Purpose:    clear cached content for the given template and cache id
\*======================================================================*/
    function clear_cache($tpl_file = null, $cache_id = null, $exp_time = null)
    {
		
        if (isset($cache_id)){
            $auto_id = $cache_id;
		}else{
            $auto_id = null;
		}

        return $this->_rm_auto($this->cache_dir, $tpl_file, $auto_id, $exp_time);
        
		
    }


/*======================================================================*\
    Function:   clear_all_cache()
    Purpose:    clear the entire contents of cache (all templates)
\*======================================================================*/
    function clear_all_cache($exp_time = null)
    {
        
		return $this->_rm_auto($this->cache_dir,null,null,$exp_time);
        
    }


/*======================================================================*\
    Function:   is_cached()
    Purpose:    test to see if valid cache exists for this template
\*======================================================================*/
    function is_cached($tpl_file, $cache_id = null)
    {
        if (!$this->caching)
            return false;

        return $this->_read_cache_file($tpl_file, $cache_id, $results);
    }
}

?>

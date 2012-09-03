<?php
// plugin needs to work on Nucleus versions <=2.0 as well
if (!function_exists('sql_table')){
	function sql_table($name) {
		return 'nucleus_' . $name;
	}
}

/*                                       */
/* NP_ExtraSkin                          */
/* ------------------------------------  */
/* An easy way to add extra skins        */
/* and templates to Nucleus!             */
/*                                       */
/* code by RADEK HULAN                   */
/* http://hulan.info/blog/               */
/*                                       */
/* add this to your .htaccess            */
/*                                       */
/* <FilesMatch "^extra$">                */ 
/*  ForceType application/x-httpd-php    */
/* </FilesMatch>                         */


/*                                       */
/* NP_ExtraSkinJP                        */
/*  Based on NP_ExtraSkin                */
/* ------------------------------------  */
/* by Taka                               */
/* http://reverb.jp/vivian/              */
/*                                       */

class NP_ExtraSkinJP extends NucleusPlugin {

	function getName() 		{ return 'ExtraSkinJP'; }
	function getAuthor()  	{ return 'Radek HULAN + Taka + Nucleus(JP) team'; }
	function getURL()  		{ return 'http://japan.nucleuscms.org/wiki/plugins:extraskinjp'; }
	function getVersion() 	{ return '0.4.8.b1'; }
	function getDescription() { return ''._LANG_NP_EXTRASKINJP10.'';	}

	function supportsFeature($what) {
		switch($what)
		{ case 'SqlTablePrefix':
				return 1;
			default:
				return 0; }
	}

	function install() {
		$this->createOption('scols', _LANG_NP_EXTRASKINJP01, 'text', '80');
		$this->createOption('srows', _LANG_NP_EXTRASKINJP02, 'text', '20');
		$this->createOption('pcols', _LANG_NP_EXTRASKINJP03, 'text', '80');
		$this->createOption('prows', _LANG_NP_EXTRASKINJP04, 'text', '20');
		$this->createOption('bcols', _LANG_NP_EXTRASKINJP05, 'text', '50');
		$this->createOption('brows', _LANG_NP_EXTRASKINJP06, 'text', '10');
		$this->createOption('bclist', _LANG_NP_EXTRASKINJP07, 'yesno', 'yes');
		$this->createOption('quickmenu', _LANG_NP_EXTRASKINJP08, 'yesno', 'yes');
		$this->createOption('del_uninstall', _LANG_NP_EXTRASKINJP09, 'yesno', 'no');
		sql_query('CREATE TABLE IF NOT EXISTS '.sql_table('plug_extraskin_jp').' ( '.
              'tableid int(11) auto_increment, '.
              'title varchar(200) not null, '.
              'description varchar(200) not null, '.
              'url varchar(255)not null, '.
              'fieldtype varchar (10) not null, '.
              'contenttype varchar(40) not null, '.
              'includemode varchar(10) not null, '.
              'includeprefix varchar(50) not null, '.
              'skinvartype varchar (15) not null, '.
              'skintype varchar (15) not null, '.
              'filter varchar (50) not null, '.
              'PRIMARY KEY (tableid), '.
              'UNIQUE titleindx (title), '.
              'KEY urlindx (url) '.
              ') TYPE=MyISAM');
		sql_query('CREATE TABLE IF NOT EXISTS '.sql_table('plug_extraskin_jp_data').' (
			tableid int(11) not null,
			context varchar (10) not null,
			refid int (11) not null,
			body text not null,
			PRIMARY KEY (tableid,context,refid)
		)');
		
		$check_column = sql_query('SELECT * FROM '. sql_table('plug_extraskin_jp'));
		for ($i=0; $i<mysql_num_fields($check_column); $i++) {
			if ($meta = mysql_fetch_field($check_column)) {
				$names[] = $meta->name;
			}
		}
		if (in_array("skin",$names)) {
			while ($o = mysql_fetch_object($check_column)) {
				$context = ($o->pageflg) ? "skin" : "global";
				$query = "INSERT INTO ". sql_table('plug_extraskin_jp_data') . '(tableid, context, refid, body) VALUES ('.$o->tableid.', "'.$context.'", 0, "'.mysql_real_escape_string($o->skin).'")';
				sql_query($query);
			}
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' DROP skin');
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' CHANGE pageflg fieldtype varchar (10) not null');
			sql_query ('UPDATE '.sql_table('plug_extraskin_jp').' SET fieldtype="skin" WHERE fieldtype=1');
			sql_query ('UPDATE '.sql_table('plug_extraskin_jp').' SET fieldtype="global" WHERE fieldtype<>"skin"');
			
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' CHANGE title title varchar(200) not null');
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' CHANGE description  description varchar(200) not null');
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' CHANGE url url varchar(255) not null');
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' CHANGE contenttype contenttype varchar(40) not null');
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' CHANGE includemode includemode varchar(10) not null');
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' CHANGE includeprefix includeprefix varchar(50) not null');
		}
		if (!in_array("skinvartype",$names)) {
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' ADD skinvartype varchar (15) not null');
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' ADD skintype varchar (15) not null');
			sql_query ('ALTER TABLE '.sql_table('plug_extraskin_jp').' ADD filter varchar (50) not null');
			sql_query ('UPDATE '.sql_table('plug_extraskin_jp').' SET skinvartype="index",  skintype="pageparser" WHERE fieldtype="skin" and skinvartype=""');
		}
	}
	

	function unInstall() {
		if ($this->getOption('del_uninstall') == "yes") {
		  sql_query('DROP TABLE '.sql_table('plug_extraskin_jp'));
		  sql_query('DROP TABLE '.sql_table('plug_extraskin_jp_data'));
		}
	}


	function init()
	{
		// include language file for this plugin
		$language = str_replace( array('\\','/'), '', getLanguageName());
		$incFile  = (is_file($this->getDirectory().$language.'.php')) ? $language : 'english';
		include_once($this->getDirectory().$incFile.'.php');
		$this->language = $incFile;
	}



	function getTableList() {
		return array(sql_table('plug_extraskin_jp'), sql_table('plug_extraskin_jp_data'));
	}

	function getEventList() {
		return array('PostDeleteBlog', 'PostDeleteCategory', 'QuickMenu');
	}
	
	function hasAdminArea() {
		return 1;
	}
	
	function event_QuickMenu(&$data) {
		// only show when option enabled
		if ($this->getOption('quickmenu') != 'yes') return;
		global $member;
		// only show to admins
		if (!($member->isLoggedIn() && $member->isAdmin())) return;
		array_push(
			$data['options'], 
			array(
				'title' => 'ExtraSkinJP',
				'url' => $this->getAdminURL(),
				'tooltip' => 'Create extra skins.'
			)
		);
	}

	function event_PostDeleteBlog($data) {
		$res = mysql_query("DELETE FROM ". sql_table("plug_extraskin_jp_data") ." WHERE refid=".intval($data['blogid'])." and context='blog'");
		if(!$res) {
			ACTIONLOG::add(ERROR, 'NP_ExtraSkinJP : '.mysql_error());
		}
	}
	
	function event_PostDeleteCategory($data) {
		$res = mysql_query("DELETE FROM ". sql_table("plug_extraskin_jp_data") ." WHERE refid=".intval($data['catid'])." and context='category'");
		if(!$res) {
			ACTIONLOG::add(ERROR, 'NP_ExtraSkinJP : '.mysql_error());
		}
	}
	
	function doParse($content,$type='') {
		global $CONF;
		$sType = $type;
		if ($type == 'pageparser') {
			$type = 'index';
		}
		$handler = new ACTIONS($sType);
		$parser = new PARSER(SKIN::getAllowedActionsForType($type), $handler);
		$handler->parser =& $parser;
		$parser->parse($content);      
	}
		 
	function doSkinVar($skinType, $tablename='', $mode='', $firstpage=0, $sort='ASC', $opt='') {
		global $blog, $manager, $CONF, $catid, $itemid;

		if ($skinType == 'item') {
			$p = func_get_args();
			if (isset($p[3])) $sort = $p[3];
			if (isset($p[4])) $opt = $p[4];
			$firstpage = 0;
		}
		
		if (intval($firstpage) > 0) {
			if (intRequestVar('page') > 1) return;
		}

		if (!$tablename) return;
		
		if ($mode != 'include') {
			$res=sql_query("select * from ".sql_table('plug_extraskin_jp')." where title='".mysql_real_escape_string($tablename)."'");
			if (!$res || !mysql_num_rows($res)) return;
			$o = mysql_fetch_object($res);
			$fieldtype = $o->fieldtype;
		} else {
			$fieldtype = "global";
		}
		
		if (preg_match("{^(?:(blog|category|php|include)\:?)(.+)?}",$mode, $m)) {
			$mode = $m[1];
			if (isset($m[2])) $filters = explode("/",$m[2]);
		} elseif ($mode) {
			$filters = explode("/",$mode);
			$mode = "";
		}

		switch ($fieldtype) {
			case 'skin':
				return;
				break;
			case 'global':
				if ($mode != 'include') {
					$body = quickQuery("SELECT body as result FROM ".sql_table('plug_extraskin_jp_data'). " WHERE tableid=".$o->tableid." and refid=0 and context='global'");
					if (!$body) return;
				}
			  switch ($mode) {
			  	case 'php':
						ob_start();
						$this->parse_code($body,$skinType);
						$content = ob_get_contents();
						ob_end_clean();
						$this->doParse($content,$skinType); 
						break;
			  	case 'include':
						global $DIR_SKINS;
						ob_start();
						$filedir = PARSER::getProperty('IncludePrefix');
						if (PARSER::getProperty('IncludeMode') == 'skindir') {
							$filedir = $DIR_SKINS.$filedir;
						}
						$this->includefile($filedir.$tablename,$skinType);
						$content = ob_get_contents();
						ob_end_clean();
						$this->doParse($content,$skinType); 
						break;
					default:
						$this->doParse($body,$skinType); 
			  }
			  return;
			  break;
				
			default:
				if ($mode == "php" || $mode == "include") return;
			
				if ($blog) {
					$b =& $blog; 
				} else {
					$b =& $manager->getBlog($CONF['DefaultBlog']);
				}
				$blogid = $b->getID();
		
				if (strtoupper($sort) != 'DESC') $sort = 'ASC';
				
				if (!$mode) {
					switch ($skinType) {
						case 'index':
						case 'archive':
						case 'archivelist':
						case 'item':
							if ($catid && $o->fieldtype == "blogcat") {
								$mode = 'category';
							} else {
								$mode = 'blog';
							}
							break;
						case 'search':
							$mode = 'blog';
							break;
						default:
							return;
					}
				}
				$where = '';
				$all = 0;
				$refids = array();
				if (isset($filters)) {
					foreach ($filters as $v) {
						if ($v == '*') {
							$where = "";
							$all = 1;
							break;
						}
						if (!is_numeric($v)) {
							switch ($mode) {
								case 'blog':
									$v = getBlogIDFromName($v);
									break;
								case 'category':
									if ($o->fieldtype == "blogcat") {
										$v = quickQuery('SELECT catid as result FROM '.sql_table('category').' WHERE cname="'.mysql_real_escape_string($v).'"');
									}
									break;
							}
						}
						if (intval($v) > 0) $refids[] = intval($v);
					}
					if (count($refids) > 0) {
						$where .= 'refid in ('.implode(",",$refids).')';
					}
				}
				
				if (!$where && !$all) {
					switch ($mode) {
						case 'blog':
							$where .= 'refid='.intval($b->getID());
							break;
						case 'category':
							if (!$catid) {
								if ($skinType != 'item') return;
								$tempid = quickQuery('SELECT icat as result FROM '.sql_table('item').' WHERE inumber="'.intval($itemid).'"');
								$where .= 'refid='.intval($tempid);
							} else {
								$where .= 'refid='.intval($catid);
							}
							break;
						default:
							return;
					}
				}
				if (!$all) $where .= ' and ';
				$where .= 'tableid='.$o->tableid;
				
				$query = 'SELECT body FROM '.sql_table('plug_extraskin_jp_data').' WHERE '.$where.' and context="'.mysql_real_escape_string($mode).'"';
				if ($all) {
					$query .= ' ORDER BY refid '.$sort;
				} elseif (count($refids) > 1) {
					$query .= ' ORDER BY FIELD(refid,'.implode(",",$refids).')';
				}

				$res = sql_query($query);
		
				while ($body = mysql_fetch_row($res)) {
					$this->doParse($body[0],$skinType);
				}
		
		}
	}
	
  function doTemplateVar(&$item, $tablename='', $mode='', $sort='ASC') {
		global $blogid, $catid, $itemid, $archive, $archivelist, $query, $memberid, $imagepopup;

		if ($itemid) {
			$skinType = 'item';
		} elseif ($archive) {
			$skinType = 'archive';
		} elseif ($archivelist) {
			$skinType = 'archivelist';
		} elseif ($query && !preg_match("/^(\s|\xE3\x80\x80|\xA1\xA1)*$/",$query)) {
			$skinType = 'search';
		} elseif ($memberid) {
			$skinType  = 'member';
		} elseif ($imagepopup) {
			$skinType  = 'imagepopup';
		} else {
			if (function_exists("ParsePlugExtraSkin") && !$blogid) {
				$skinType = 'pageparser';
			} else {
			  $skinType  = 'index';
			}
		}

		preg_match("{^(?:(blog|category)\:?)(.+)?}",$mode, $m);
		if (isset($m[1]) && !isset($m[2])) {
			switch ($mode) {
				case 'blog':
					$mode = 'blog:'.getBlogIDFromItemID($item->itemid);
					break;
				case 'category':
					$mode = 'category:'.$item->catid;
					break;
			}
		}
		if ($skinType != 'item') {
			$this->doSkinVar($skinType, $tablename, $mode, 0, $sort);
		} else {
			$this->doSkinVar($skinType, $tablename, $mode, $sort);
		}
  }
  
  function includefile($file,$skinType) {
  	include($file);
  }
  
  function parse_code($code,$skinType) {
  	eval($code);
  }
  
  function extra_selector($requests) {
		global $itemid, $blogid, $memberid, $query, $amount, $archivelist;
		global $archive, $skinid, $imagepopup, $catid;
		global $manager, $CONF, $blog, $member, $memberinfo, $maxresults;
		
		$url = rawurldecode($requests[0]);
		$url = mysql_real_escape_string(stripslashes($url));
		$r = mysql_query('SELECT e.*, d.body as skin FROM '.sql_table('plug_extraskin_jp').' as e, '.sql_table('plug_extraskin_jp_data').' as d WHERE url="'.$url.'" and e.tableid=d.tableid and d.context="skin" and d.refid=0');
		if ($r) $o = mysql_fetch_object($r);
	 
		if (!$o) doError('No such page exists.');
		
		if ((headers_sent() && $CONF['alertOnHeadersSent'])) {
			selector();
			exit;
		}
		
		if (isset($requests[1]) && intval($requests[1]) && $manager->existsBlogID($requests[1])) {
			$blogid = $requests[1];
		}
		if (preg_match("/&archivelist(&|$)/", serverVar('QUERY_STRING')))
			$archivelist = $CONF['DefaultBlog'];
		
	// skin vars setting -------------------
	// item
		if ($itemid) {
			if ($manager->existsItem($itemid,0,0)) {
				global $itemidprev, $itemidnext, $catid, $itemtitlenext, $itemtitleprev;
			
				$q = 'SELECT itime, iblog FROM '.sql_table('item').' WHERE inumber=' . intval($itemid);
				$res = sql_query($q);
				$obj = mysql_fetch_object($res);
			
				$blogid = $obj->iblog;
				$timestamp = strtotime($obj->itime);
			
				$b =& $manager->getBlog($blogid);
				if ($b->isValidCategory($catid)) $catextra = ' and icat=' . $catid;

				// get previous itemid and title
				$q = 'SELECT inumber, ititle FROM '.sql_table('item').' WHERE itime<' . mysqldate($timestamp) . ' and idraft=0 and iblog=' . $blogid . $catextra . ' ORDER BY itime DESC LIMIT 1';
				$res = sql_query($q);

				$obj = mysql_fetch_object($res);
				if ($obj) {
					$itemidprev = $obj->inumber;
					$itemtitleprev = $obj->ititle;
				}

				// get next itemid and title
				$q = 'SELECT inumber, ititle FROM '.sql_table('item').' WHERE itime>' . mysqldate($timestamp) . ' and itime <= ' . mysqldate(time()) . ' and idraft=0 and iblog=' . $blogid . $catextra . ' ORDER BY itime ASC LIMIT 1';
				$res = sql_query($q);

				$obj = mysql_fetch_object($res);
				if ($obj) {
					$itemidnext = $obj->inumber;
					$itemtitlenext = $obj->ititle;
				}
			
			} elseif ($o->skinvartype == 'item') {
				doError(_ERROR_NOSUCHITEM);
			}
		
	// archive
		} elseif ($archive) {
			// get next and prev month links
			global $archivenext, $archiveprev, $archivetype;

			sscanf($archive,'%d-%d-%d',$y,$m,$d);
			if ($d != 0) {
				$archivetype = _ARCHIVETYPE_DAY;
				$t = mktime(0,0,0,$m,$d,$y);
				$archiveprev = strftime('%Y-%m-%d',$t - (24*60*60));
				$archivenext = strftime('%Y-%m-%d',$t + (24*60*60));

			} else {
				$archivetype = _ARCHIVETYPE_MONTH;
				$t = mktime(0,0,0,$m,1,$y);
				$archiveprev = strftime('%Y-%m',$t - (1*24*60*60));
				$archivenext = strftime('%Y-%m',$t + (32*24*60*60));
			}
		
	// archivelist
		} elseif ($archivelist) {
			if (intval($archivelist) != 0) {
				$blogid = $archivelist;
			} else {
				$temp_blogid = getBlogIDFromName($archivelist);
				if ($temp_blogid) $blogid = $temp_blogid;
			}
		
	// search
		} elseif ($query) {
		  global $startpos;
			$query = stripslashes($query);
			$order = (_CHARSET == 'EUC-JP') ? 'EUC-JP, UTF-8,' : 'UTF-8, EUC-JP,';
			$query = mb_convert_encoding($query, _CHARSET, $order.' JIS, SJIS, ASCII');
			if ($blogid && intval($blogid) == 0)
				$blogid = getBlogIDFromName($blogid);
		
	// member
		} elseif ($memberid) {
			if (MEMBER::existsID($memberid)) {
				$memberinfo = MEMBER::createFromID($memberid);
			} elseif ($o->skinvartype == 'member') {
				doError(_ERROR_NOSUCHMEMBER);
			}

		} elseif (!$imagepopup) {
			global $startpos;
		}
		

	// filter ------------------------------
		$req_blogid = $blogid;
		if (!$blogid) $blogid = $CONF['DefaultBlog'];
		if ($o->filter) {
			$filters = explode("/", trim($o->filter));
			if(preg_match("/<>(.*)/", $filters[0], $m)) {
				$deny = 1;
				$filters[0] = $m[1];
			}
			if (($deny && in_array($blogid, $filters)) || (!$deny && !in_array($blogid, $filters))) {
				doError('No such page exists.');
			}
		}
	
	// vars check ------------------------------
		switch (true) {
			case $o->skinvartype == 'item' && !$itemid:
			case $o->skinvartype == 'archive' && !$archive:
			case $o->skinvartype == 'member' && !$memberid:
			case $o->skinvartype == 'imagepopup' && !$imagepopup:
				doError('No such page exists.');
				break;
		}
		
		if (!isset($b)) $b =& $manager->getBlog($blogid);
		$blog = $b;	// references can't be placed in global variables?
		if (!$blog->isValid)
			doError(_ERROR_NOSUCHBLOG);

		if ($catid)
			$blog->setSelectedCategory($catid);
		
		switch ($o->includemode) {
			case 'auto':
				if ($req_blogid) {
					$skinid = $b->getDefaultSkin();
				} else {
					$skinid = $CONF['BaseSkin'];
				}
				if (SKIN::existsID($skinid)) {
					$skin =& new SKIN($skinid);
					// set IncludeMode properties of parser
					PARSER::setProperty('IncludeMode',$skin->getIncludeMode());
					PARSER::setProperty('IncludePrefix',$skin->getIncludePrefix());
				}
				break;
			case 'normal':
			case 'skindir':
				PARSER::setProperty('IncludeMode',$o->includemode);
				PARSER::setProperty('IncludePrefix',$o->includeprefix);
				break;
		}
		
		$contenttype = $o->contenttype;
		$charset = _CHARSET;
		if (
				($contenttype == 'application/xhtml+xml')
			&&	(($CONF['UsingAdminArea'] && !$CONF['debug']) || !stristr(serverVar('HTTP_ACCEPT'),'application/xhtml+xml'))
			)
		{
			$contenttype = 'text/html';
		}
		if (function_exists("sendContentType")) {
			$manager->notify(
				'PreSendContentType',
				array(
					'contentType' => &$contenttype,
					'charset' => &$charset,
					'pageType' => 'skin'
				)
			);
		}
		if (!headers_sent()) 
			header('Content-Type: ' . $contenttype . '; charset=' . $charset);

		if (!isset($o->skinvartype)) $o->skinvartype = 'index';
		if (!isset($o->skintype)) $o->skintype = 'pageparser';
		$skinType = ($o->skintype == 'same') ? $o->skinvartype : 'pageparser';
		
		if (!isset($skin)) $skin = new SKIN($CONF['BaseSkin']);
		$manager->notify('PreSkinParse',array('skin' => &$skin, 'type' => $skinType));

		$handler =& new ACTIONS($skinType);
		$parser =& new PARSER(SKIN::getAllowedActionsForType($o->skinvartype), $handler);
		$handler->setParser($parser);
		$parser->parse($o->skin); 

		$manager->notify('PostSkinParse',array('skin' => &$skin, 'type' => $skinType));
	}

}
?>

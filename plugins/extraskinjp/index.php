<?php

/**
  * NP_ExtraSkin Admin Page
  */

/* original code by RADEK HULAN       */
/* http://hulan.info/blog/            */

/* Japanese Edition by Taka         */
/* http://reverb.jp/vivian/         */

/* Fixed for sequrity by Nucleus(JP) team */

	// if your 'plugin' directory is not in the default location,
	// edit this variable to point to your site directory
	// (where config.php is)
$strRel = '../../../';

include($strRel . 'config.php');
if (!$member->isLoggedIn())
	doError('You\'re not logged in.');

include($DIR_LIBS . 'PLUGINADMIN.php');

// create the admin area page
$oPluginAdmin = new PluginAdmin('ExtraSkinJP');

/* Exit if not valid ticket */
if ((requestVar('action') && requestVar('action')!='overview')
		&& (!$manager->checkTicket())){
	$oPluginAdmin->start();
	echo '<p>' . _ERROR_BADTICKET . '</p>';
	$oPluginAdmin->end();
	exit;
}

$extraskin_langfile = $oPluginAdmin->plugin->getDirectory().$language.'.php';
if (strstr(strtolower($CONF['Language']),"japan") && file_exists($extraskin_langfile)) {
	include ($extraskin_langfile);
} else {
	include ($oPluginAdmin->plugin->getDirectory().'/english.php');
}
include($oPluginAdmin->plugin->getDirectory().'/class.php');


// ----------------------------------------------------------------------------
class ExtraSkin_ADMIN extends PLUG_ADMIN {

	function ExtraSkin_ADMIN() {
		global $oPluginAdmin, $oPlugin;
		
		$this->plug =& $oPluginAdmin->plugin;
		$this->plugname = $this->plug->getName();
		$this->url = $this->plug->getAdminURL();

		$this->template_table = sql_table('plug_extraskin_jp');
		$this->template_idname = 'tableid';
		$this->template_namepart = 'title';
		$this->tmanager = new PLUG_TEMPLATE_MANAGER(
				$this->template_table,
				$this->template_idname,
				$this->template_namepart);
		$this->template_parts = array(
				'tableid',
				'title',
				'description',
				'url',
				'fieldtype',
				'contenttype',
				'includemode',
				'includeprefix',
				'skinvartype',
				'skintype',
				'filter',
		);
		
		$this->data_table = sql_table('plug_extraskin_jp_data');

		// option
		$this->convert = array(
			'0'=>_NPCONVERT_NO,
			'1'=>_NPCONVERT_YESALL,
			'2'=>_NPCONVERT_YESTRIM,
			);
		$this->incmode = array(
			'auto'=>_NPINCLUDE_AUTO,
			'skindir'=>_NPINCLUDE_SKINDIR,
			'normal'=>_NPINCMODE_NORMAL,
			);
		$this->skinvartype = array(
			'index'=>_SKIN_PART_MAIN,
			'item'=>_SKIN_PART_ITEM,
			'archivelist'=>_SKIN_PART_ALIST,
			'archive'=>_SKIN_PART_ARCHIVE,
			'search'=>_SKIN_PART_SEARCH,
			'member'=>_SKIN_PART_MEMBER,
			'imagepopup'=>_SKIN_PART_POPUP
			);
		$this->skintype = array(
			'pageparser'=>'pageparser',
			'same'=>_NPSAME_AS_SKINVARTYPE
			);
		$this->ptype = array(
			'global'=>_NPTYPE_GLOBAL,
			'blog'=>_NPTYPE_BLOG,
			'blogcat'=>_NPTYPE_BLOGCAT,
			);
		$this->blogcat = array(
			'blog'=>_NPTYPE_BLOG,
			'blogcat'=>_NPTYPE_BLOGCAT,
			);
		
		// textarea size
		$this->srows = $this->plug->getOption('srows');
		$this->scols = $this->plug->getOption('scols');
		$this->prows = $this->plug->getOption('prows');
		$this->pcols = $this->plug->getOption('pcols');
		$this->brows = $this->plug->getOption('brows');
		$this->bcols = $this->plug->getOption('bcols');
		
		// alias
		$this->bclist = ($this->plug->getOption('bclist') == "yes");
	}

// -------

	function action_overview($msg='') {
		global $member, $oPluginAdmin, $includemode_array, $manager;
		
		$member->isAdmin() or $this->disallow();

		$oPluginAdmin->start();

		echo '<p><a href="index.php?action=pluginlist">('._PLUGS_BACK.')</a></p>';
		echo '<h2>' .$this->plugname. '</h2>'."\n";
		if ($msg) echo "<p>"._MESSAGE.": $msg</p>";
		echo '<p>[ <a href="index.php?action=pluginoptions&amp;plugid='.$this->plug->getID().'">'._NPPLUGIN_EDITOPTION.'</a> ]';
		//echo ' [<a href="http://reverb.jp/vivian/download.php?itemid=NP_ExtraSkinJP_howto">'._NPPLUGIN_HOWTOUSE.'</a>]</p>';

?>

<h3><?php echo _NPPAGESKIN_EDIT_TITLE?></h3>
	<table>
<?php
		echo '<caption style="text-align:left;">' . _SKIN_AVAILABLE_TITLE . '</caption>';
?>

	<thead>
		<tr>
			<th><?php echo _LISTS_NAME?></th>
			<th><?php echo _LISTS_DESC?></th>
			<th colspan='3'><?php echo _LISTS_ACTIONS?></th></tr>
	</thead>
	<tbody>
<?php
		$skins = $this->tmanager->getNameList('fieldtype="skin"');
		if ($skins) {
			foreach ($skins as $k => $v) {
				$sdata = $this->tmanager->read($v);
?>
		<tr onmouseover='focusRow(this);' onmouseout='blurRow(this);'>
			<td style="line-height: 1.2">
				<dl style="margin: 0px;">
					<dt><strong><?php echo $this->hsc($v) ?></strong></dt>
					<dd style="margin-left: 1.5em">URL: <?php echo $this->hsc($sdata['url']) ?><br />
					<?php 
						echo _LISTS_TYPE .': '. $this->hsc($sdata['contenttype']) ?><br /><br />
					<?php
						echo _LIST_SKINS_INCMODE .': '. $this->incmode[$sdata['includemode']] ?><br />
					<?php
						if ($sdata['includeprefix']) {
							echo _LIST_SKINS_INCPREFIX.': '. $this->hsc($sdata['includeprefix']) ?><br />
					<?php
						}
						echo _NPSKIN_VAR_TYPE.': '. $this->hsc($this->skinvartype[$sdata['skinvartype']]) ?><br />
					<?php
						echo _NPSKIN_TYPE.': '. $this->hsc($this->skintype[$sdata['skintype']]) ?><br />
					<?php
						if ($sdata['filter']) {
							echo _NPSKIN_FILTER.': '. $this->hsc($sdata['filter']);
						} ?>
					</dd>
				</dl>
			</td>
			<td><?php echo $this->hsc($sdata['description']) ?></td>
			<td><a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=skinedit&tableid='.$k)); ?>" tabindex="50"><?php echo _LISTS_EDIT?></a></td>
			<td><a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=skinclone&tableid='.$k)); ?>" tabindex="50"><?php echo _LISTS_CLONE?></a></td>
			<td><a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=skindelete&tableid='.$k)); ?>" tabindex="50"><?php echo _LISTS_DELETE?></a></td>
		</tr>
<?php
			}
		}
?>
	</tbody>
	</table>
	<form method="post" action="<?php echo $this->url ?>index.php"><div>
	
		<?php $manager->addTicketHidden(); ?>
		<input name="action" value="skinnew" type="hidden" />
		<table>
<?php
		echo '		<caption style="text-align:left;">' . _SKIN_NEW_TITLE . '</caption>';
?>

		<tr>
			<td><?php echo _SKIN_NAME?> <?php $this->help('name');?></td>
			<td><input name="<?php echo $this->template_namepart ?>" tabindex="60" size="20" maxlength="200" /></td>
		</tr><tr>
			<td><?php echo _SKIN_DESC?></td>
			<td><input name="description" tabindex="60" size="60" maxlength="200" /></td>
		</tr><tr>
			<td><?php echo _SKIN_CREATE?></td>
			<td><input type="submit" tabindex="60" value="<?php echo _SKIN_CREATE_BTN?>" onclick="return checkSubmit();" /></td>
		</tr></table>
		
	</div></form><br /><br />


<h3><?php echo _NPPART_EDIT_TITLE?></h3>
	<table>
<?php
		echo '<caption style="text-align:left;">' . _NPPART_AVAILABLE_TITLE . '</caption>';
?>

	<thead>
		<tr>
			<th><?php echo _LISTS_NAME?></th>
			<th><?php echo _LISTS_DESC?></th>
			<th colspan='3'><?php echo _LISTS_ACTIONS?></th></tr>
	</thead>
	<tbody>
<?php
		$parts = $this->tmanager->getNameList('fieldtype<>"skin"');
		if ($parts) {
			foreach ($parts as $k=>$v) {
				$pdata = $this->tmanager->read($v);
				
				if ($this->bclist && $pdata['fieldtype'] != "global") {
					$res = sql_query("SELECT refid, context FROM ".$this->data_table." WHERE tableid=".$pdata['tableid']." and context<>'skin' and context<>'global' ORDER BY FIELD(context,'blog','category'), refid");
					$b_array = array();
					$c_array = array();
					while ($o = mysql_fetch_object($res)) {
						if ($o->context == 'blog') {
							$b_array[] = $o->refid;
							$cres = sql_query('SELECT catid FROM '.sql_table('category').' WHERE cblog='.$o->refid);
							while ($cat = mysql_fetch_row($cres)) {
								$c_array[] = $cat[0];
							}
						} else {
							if (!in_array($o->refid,$c_array)) {
								$bid = quickQuery("SELECT cblog as result FROM ".sql_table('category')." WHERE catid=".$o->refid);
								if ($bid) {
									$b_array[] = $bid;
									$cres = sql_query('SELECT catid FROM '.sql_table('category').' WHERE cblog='.$o->refid);
									while ($cat = mysql_fetch_row($cres)) {
										$c_array[] = $cat[0];
									}
								}
							}
						}
					}
				}
?>
		<tr onmouseover='focusRow(this);' onmouseout='blurRow(this);'>
			<td>
				<strong><?php echo $v ?></strong><br /><br />
				<?php 
					echo _NPTYPE_TITLE .': '.$this->ptype[$pdata['fieldtype']] ?>
			</td>
			<td><?php echo $this->hsc($pdata['description']);?><br /><br />
<?php
	
				if ($this->bclist && $pdata['fieldtype'] != "global") {
					if (count($b_array) > 0) {
						asort($b_array);
						$res = sql_query("SELECT bnumber, bname FROM ".sql_table('blog')." WHERE bnumber in (".implode(",",$b_array).") ORDER BY bnumber");
						
						echo  _NPDIFINED_FIELD."\n";
						if ($pdata['fieldtype'] == 'blog') {
							echo ' [ <a href="'.$this->hsc($manager->addTicketToUrl($this->url.'index.php?action=editallbfield&tableid='.$pdata['tableid'])).'">'._NPEDIT_ALLFIELD.'</a> ]'."\n";
						}
						echo "				<ul style=\"margin-top: 0.5em\">\n";
						while ($o = mysql_fetch_object($res)) {
	?>
						<li><a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=editfield&refid='.$o->bnumber)); ?>&amp;tableid=<?php echo $pdata['tableid'] ?>"><?php echo $this->hsc($o->bname) ?></a></li>
	<?php
						}
						echo "				</ul>\n";
					} elseif  ($pdata['fieldtype'] == 'blog') {
						echo '[ <a href="'.$this->hsc($manager->addTicketToUrl($this->url.'index.php?action=editallbfield&tableid='.$pdata['tableid'])).'">'._NPEDIT_ALLFIELD.'</a> ]'."\n";
					} 
				}
?>
			</td>
			<td><a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=partedit&tableid='.$k)); ?>" tabindex="70"><?php echo _LISTS_EDIT?></a></td>
			<td>
<?php
				if ($pdata['fieldtype'] == "global") {
?>
				<a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=partclone&tableid='.$k)); ?>" tabindex="70"><?php echo _LISTS_CLONE?></a>
<?php
				}
?>
			</td>
			<td><a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=partdelete&tableid='.$k)); ?>" tabindex="70"><?php echo _LISTS_DELETE?></a></td>
		</tr>
<?php
			}
		}
?>
	</tbody>
	</table>

	<form method="post" action="<?php echo $this->url ?>index.php"><div>
	
		<?php $manager->addTicketHidden(); ?>
		<input name="action" value="partnew" type="hidden" />
		<table>
<?php
		echo '		<caption style="text-align:left;">' . _NPPART_NEW_TITLE . '</caption>';
?>

		<tr>
			<td><?php echo _NPPART_NAME?> <?php $this->help('name');?></td>
			<td><input name="<?php echo $this->template_namepart ?>" tabindex="80" size="20" maxlength="200" /></td>
		</tr><tr>
			<td><?php echo _NPPART_DESC?></td>
			<td><input name="description" tabindex="80" size="60" maxlength="200" /></td>
		</tr><tr>
			<td><?php echo _NPTYPE_TITLE?> <?php $this->help('fieldtype');?></td>
			<td><?php $this->showRadioButton('fieldtype',$this->ptype,'global',80)?></td>
		</tr><tr>
			<td><?php echo _NPPART_CREATE?></td>
			<td><input type="submit" tabindex="80" value="<?php echo _NPPART_CREATE_BTN?>" onclick="return checkSubmit();" /></td>
		</tr></table>
		
	</div></form>
<?php
		
		$oPluginAdmin->end();
	
	}
	
//-----

	function action_skinedit($msg = '') {
		global $member, $oPluginAdmin, $includemode_array, $manager;
		
		$member->isAdmin() or $this->disallow();
		
		$oPluginAdmin->start();

		$tableid = intRequestVar('tableid');
		$sname = $this->tmanager->getNameFromID($tableid);
		$sdata = $this->tmanager->read($sname);
		if ($sdata['fieldtype'] != "skin") {
			$this->error('wrong action.');
		}
		$body = quickQuery("SELECT body as result FROM ". $this->data_table. " WHERE context='skin' and refid=0 and tableid=$tableid");

?>
<p><a href="<?php echo $this->url ?>index.php?action=overview">(<?php echo _NPPLUGIN_GOBACK?>)</a></p>

<h2><?php 
		echo _NPPAGESKIN_EDIT_TITLE;
		echo  ": $sname</h2>\n";

		if ($msg) echo "<p>"._MESSAGE.": $msg</p>";
?>

<form method="post" action="<?php echo $this->url ?>index.php">
	<div>
	
	<?php $manager->addTicketHidden(); ?>
	<input type="hidden" name="action" value="updateskin" />
	<input type="hidden" name="tableid" value="<?php echo $tableid; ?>" />
	<input type="hidden" name="fieldtype" value="skin" />
	<input type="hidden" name="context" value="skin" />
	<input type="hidden" name="refid" value="0" />
<?php

		echo '<h3>'._OVERVIEW_GSETTINGS."</h3>\n";

?>

	<table><tr>
			<td><?php echo _SKIN_NAME?> <?php $this->help('name');?></td>
			<td><input name="<?php echo $this->template_namepart ?>" tabindex="50" size="20" maxlength="200" value="<?php echo $sname ?>" /></td>
		</tr><tr>
			<td><?php echo _SKIN_DESC?></td>
			<td><input name="description" tabindex="50" size="60" maxlength="200" value="<?php echo $this->hsc($sdata['description']) ?>" /></td>
		</tr><tr>
			<td>URL <?php $this->help('url');?></td>
			<td><input name="url" tabindex="50" size="60" maxlength="250" value="<?php echo $this->hsc($sdata['url']) ?>" /></td>
		</tr><tr>
			<td>ContentType</td>
			<td><input name="contenttype" tabindex="50" size="20" maxlength="40" value="<?php echo $this->hsc($sdata['contenttype']) ?>" /></td>
		</tr><tr>
			<td><?php echo _SKIN_INCLUDE_MODE?> <?php $this->help('parser-properties');?></td>
			<td>
<?php
		$this->showRadioButton('includemode',$this->incmode,$sdata['includemode'],50);
?>
			</td>
		</tr><tr>
			<td><?php echo _SKIN_INCLUDE_PREFIX?> <?php $this->help('parser-properties');?></td>
			<td><input name="includeprefix" tabindex="50" size="20" maxlength="50" value="<?php echo $this->hsc($sdata['includeprefix']) ?>" /></td>
		</tr><tr>
			<td><?php echo _NPSKIN_VAR_TYPE?> <?php $this->help('skinvartype');?></td>
			<td>
<?php
		$this->showSelectMenu('skinvartype', $this->skinvartype, $sdata['skinvartype'], 50);
?>
			</td>
		</tr><tr>
			<td><?php echo _NPSKIN_TYPE?> <?php $this->help('skintype');?></td>
			<td>
<?php
		$this->showRadioButton('skintype', $this->skintype, $sdata['skintype'], 50);
?>
			</td>
		</tr><tr>
			<td><?php echo _NPSKIN_FILTER?> <?php $this->help('filter');?></td>
			<td><input name="filter" tabindex="50" size="20" maxlength="50" value="<?php echo $this->hsc($sdata['filter']) ?>" /></td>
		</tr></table>

<?php
		echo '<h3>'._NPPAGESKIN_EDIT_TITLE."</h3>\n";
		
?>
	<textarea name="body" rows='<?php echo $this->srows ?>' cols="<?php echo $this->scols ?>" tabindex="50"><?php echo $this->hsc($body)?></textarea><br /><br />
	<input type="submit" tabindex="50" value="<?php echo _SKIN_UPDATE_BTN ?>" onclick="return checkSubmit();" />
	<input type="reset" tabindex="50" value="<?php echo _SKIN_RESET_BTN ?>" />
	</div>
</form>

<?php
	
		$oPluginAdmin->end();
	
	}
	
//-----

	function action_partedit($msg = '') {
		global $member, $oPluginAdmin, $includemode_array, $manager;
		
		$member->isAdmin() or $this->disallow();
		
		$oPluginAdmin->start();

		$tableid = intRequestVar('tableid');
		$pname = $this->tmanager->getNameFromID($tableid);
		$pdata = $this->tmanager->read($pname);
		$fieldtype = $pdata['fieldtype'];
		if ($fieldtype == "skin") {
			$this->error('wrong action.');
		}

?>
<p><a href="<?php echo $this->url ?>index.php?action=overview">(<?php echo _NPPLUGIN_GOBACK?>)</a></p>

<h2><?php 
		echo _NPPART_EDIT_TITLE;
		echo  ": $pname</h2>\n";

		if ($msg) echo "<p>"._MESSAGE.": $msg</p>";

		if ($fieldtype == "global") {
			$body = quickQuery("SELECT body as result FROM ". $this->data_table. " WHERE context='global' and refid=0 and tableid=$tableid");

?>

<form method="post" action="<?php echo $this->url ?>index.php">
	<div>
	
	<?php $manager->addTicketHidden(); ?>
	<input type="hidden" name="action" value="updateskin" />
	<input type="hidden" name="tableid" value="<?php echo $tableid; ?>" />
	<input type="hidden" name="fieldtype" value="global" />
	<input type="hidden" name="context" value="global" />
	<input type="hidden" name="refid" value="0" />
<?php

		echo '<h3>'._OVERVIEW_GSETTINGS."</h3>\n";

?>

	<table><tr>
			<td><?php echo _NPPART_NAME?> <?php $this->help('name');?></td>
			<td><input name="<?php echo $this->template_namepart ?>" tabindex="50" size="20" maxlength="200" value="<?php echo $pname ?>" /></td>
		</tr><tr>
			<td><?php echo _NPPART_DESC?></td>
			<td><input name="description" tabindex="50" size="60" maxlength="200" value="<?php echo $this->hsc($pdata['description']) ?>" /></td>
		</tr></table>

<?php
		echo '<h3>'._NPPART_EDIT_TITLE."</h3>\n";
		
?>
	<textarea name="body" rows='<?php echo $this->prows ?>' cols="<?php echo $this->pcols ?>" tabindex="50"><?php echo $this->hsc($body)?></textarea><br /><br />
	<input type="submit" tabindex="50" value="<?php echo _NPPART_UPDATE_BTN ?>" onclick="return checkSubmit();" />
	<input type="reset" tabindex="50" value="<?php echo _NPPART_RESET_BTN ?>" />
	</div>
</form>

<?php
	
		} else {
			
			echo '<h3>' . _NPPART_EDIT_TITLE . '</h3>'."\n\n";
?>
<table>
	<thead>
		<tr>
			<th>Blog</th>
			<th><?php echo _LISTS_ACTIONS?></th>
		</tr>
	</thead>
	<tbody>
<?php
	
			$query = 'SELECT bnumber, bname FROM '.sql_table('blog').' ORDER BY bnumber';
			$res = sql_query($query);
			while ($blogs = mysql_fetch_assoc($res)) {
?>
		<tr>
			<td><?php echo $this->hsc($blogs['bname']) ?></td>
			<td><a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=editfield&refid='.$blogs['bnumber'])); ?>&amp;tableid=<?php echo $pdata['tableid'] ?>"><?php echo _LISTS_EDIT?></a></td>
		</tr>
<?php
			}
?>
</tbody></table>

<form method="post" action="<?php echo $this->url ?>index.php">
	<div>
	
	<?php $manager->addTicketHidden(); ?>
	<input type="hidden" name="action" value="updatepartdata" />
	<input type="hidden" name="tableid" value="<?php echo $tableid; ?>" />

	<table><tr>
			<th colspan="2"><?php echo _OVERVIEW_GSETTINGS ?></th>
		</tr><tr>
			<td><?php echo _NPPART_NAME?> <?php $this->help('name');?></td>
			<td><input name="<?php echo $this->template_namepart ?>" tabindex="4" size="20" maxlength="20" value="<?php echo $this->hsc($pname) ?>" /></td>
		</tr><tr>
			<td><?php echo _NPPART_DESC?></td>
			<td><input name="description" tabindex="5" size="60" maxlength="200" value="<?php echo $this->hsc($pdata['description']) ?>" /></td>
		</tr><tr>
			<td><?php echo _NPTYPE_TITLE?> <?php $this->help('fieldtype');?></td>
			<td><?php $this->showRadioButton('fieldtype',$this->blogcat,$fieldtype,20) ?></td>
		</tr><tr>
			<th colspan="2"><?php echo _NPFIELD_UPDATE?></th>
		</tr><tr>
			<td><?php echo _NPFIELD_UPDATE ?></td>
			<td>
				<input type="submit" tabindex="40" value="<?php echo _NPPART_UPDATE_BTN?>" onclick="return checkSubmit();" />
				<input type="reset" tabindex="50" value="<?php echo _NPPART_RESET_BTN?>" />
			</td>
		</tr></table>
		
	</div>
</form>

<?php
		}
		
		$oPluginAdmin->end();
	
	}

//-----

	function action_editfield($msg = '') {
		global $member, $oPluginAdmin, $CONF, $manager;
		
		$member->isAdmin() or $this->disallow();
		
		$refid = intRequestVar('refid');
		$tableid = intRequestVar('tableid');

		$pname = $this->tmanager->getNameFromID($tableid);
		$pdata = $this->tmanager->read($pname);
		$hname = getBlogNameFromID($refid);
		$fieldtype = $pdata['fieldtype'];
		
		if ($fieldtype == "skin" || $fieldtype == "global") {
			$this->error('wrong action.');
		}
		
		$body = quickQuery("SELECT body as result FROM ". $this->data_table. " WHERE context='blog' and refid=$refid and tableid=$tableid");
		
		$extrahead = '<script type="text/javascript" src="'.$CONF['AdminURL'].'javascript/templateEdit.js"></script>';
		$extrahead .= '<script type="text/javascript">setTemplateEditText("'.addslashes(_EDITTEMPLATE_EMPTY).'");</script>'."\n";
		$oPluginAdmin->start($extrahead);

?>
<p><a href="<?php echo $this->url ?>index.php?action=overview"><?php echo _NPPLUGIN_GOBACK?></a> | <a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=partedit&tableid='.$tableid)); ?>"><?php echo $this->hsc($pname)._NPPLUGIN_GOBACK_PTOP ?></a></p>

<?php
		echo '<h2>'._NPPART_EDIT_TITLE.' : '.$this->hsc($pname).' &gt; '.$this->hsc($hname)."</h2>\n";
		if ($msg) echo "<p>"._MESSAGE.": $msg</p>\n\n";
		echo '<h3>'._NPBLOG_TITLE."</h3>\n";
?>

<form method="post" action="<?php echo $this->url ?>index.php">
	<div>
	<?php $manager->addTicketHidden(); ?>
	<input type="hidden" name="action" value="updatefield" />
	<input type="hidden" name="tableid" value="<?php echo $tableid ?>" />
	<input type="hidden" name="refid" value="<?php echo $refid ?>" />
	<input type="hidden" name="context" value="blog" />
<?php
		if ($fieldtype == 'blogcat') {
?>
	<table><tr><th colspan="2"><?php echo _NPBLOG_TITLE ?></th>
	</tr><tr>
		<td><?php echo $this->hsc($hname)?></td>
		<td id="td1">
			<textarea name="body" rows='<?php echo $this->brows ?>' cols="<?php echo $this->bcols ?>" tabindex="5" id="textarea1"><?php echo $this->hsc($body)?></textarea>
		</td>
	</tr><tr>
		<td><?php echo _NPFIELD_UPDATE ?></td>
		<td>
			<input type="submit" tabindex="6" value="<?php echo _NPFIELD_UPDATE_BTN?>" onclick="return checkSubmit();" />
			<input type="reset" tabindex="7" value="<?php echo _NPFIELD_RESET_BTN?>" />
		</td>
	</tr></table>
<?php
		} else {
?>
	<textarea name="body" rows='<?php echo $this->brows ?>' cols="<?php echo $this->bcols ?>" tabindex="5"><?php echo $this->hsc($body)?></textarea><br /><br />
	<input type="submit" tabindex="6" value="<?php echo _NPFIELD_UPDATE_BTN?>" onclick="return checkSubmit();" />
	<input type="reset" tabindex="7" value="<?php echo _NPFIELD_RESET_BTN?>" />
<?php
		}
?>
	</div>
</form>

<?php
		if ($fieldtype == 'blogcat') {
			echo "<br /><br />\n";
			echo '<h3>'._NPCAT_TITLE."</h3>\n";

?>

<form method="post" action="<?php echo $this->url ?>index.php">
	<div>
	<?php $manager->addTicketHidden(); ?>
	<input type="hidden" name="action" value="updatecatfield" />
	<input type="hidden" name="tableid" value="<?php echo $tableid ?>" />
	<input type="hidden" name="refid" value="<?php echo $refid ?>" />
	<input type="hidden" name="context" value="category" />
	<table><tr>
			<th colspan="2"><?php echo _NPFIELD_UPDATE?></th>
		</tr><tr>
			<td><?php echo _NPFIELD_UPDATE ?></td>
			<td>
				<input type="submit" tabindex="10" value="<?php echo _NPFIELD_UPDATE_BTN?>" onclick="return checkSubmit();" />
				<input type="reset" tabindex="20" value="<?php echo _NPFIELD_RESET_BTN?>" />
			</td>
		</tr><tr>
			<th colspan="2"><?php echo _NPCAT_TITLE?></th>
<?php
			$res = sql_query("SELECT catid, cname FROM ".sql_table('category')." WHERE cblog=$refid");
			while ($o = mysql_fetch_object($res)) {
				$catids[] = $o->catid;
				$cnames[$o->catid] = $o->cname;
			}
			if (isset($catids)) {
				$cres = sql_query("SELECT refid, body FROM ".$this->data_table." WHERE context='category' and tableid=$tableid and refid in (".implode(",",$catids).")");
				$cbodys = array();
				while ($c = mysql_fetch_object($cres)) {
					$cbodys[$c->refid] = $c->body;
				}
				$cnt = 2;
				foreach ($cnames as $k => $v) {
?>
			</tr><tr>
				<td><?php echo $this->hsc($v) ?></td>
				<td id="td<?php echo $cnt ?>"><textarea name="<?php echo 'cbody['.$k.']' ?>" rows="<?php echo $this->brows ?>" cols="<?php echo $this->bcols ?>" id="textarea<?php echo $cnt ?>" tabindex="30"><?php echo $this->hsc($cbodys[$k]) ?></textarea></td>
<?php
					$cnt ++;
				}
			}
?>
		</tr><tr>
			<th colspan="2"><?php echo _NPFIELD_UPDATE?></th>
		</tr><tr>
			<td><?php echo _NPFIELD_UPDATE ?></td>
			<td>
				<input type="submit" tabindex="40" value="<?php echo _NPFIELD_UPDATE_BTN?>" onclick="return checkSubmit();" />
				<input type="reset" tabindex="50" value="<?php echo _NPFIELD_RESET_BTN?>" />
			</td>
	</tr></table>
	</div>
</form>

<?php
		}
	
		$oPluginAdmin->end();
	
	}
	
//-----

	function action_editallbfield($msg = '') {
		global $member, $oPluginAdmin, $CONF, $manager;
		
		$member->isAdmin() or $this->disallow();
		
		$tableid = intRequestVar('tableid');

		$pname = $this->tmanager->getNameFromID($tableid);
		$pdata = $this->tmanager->read($pname);
		$fieldtype = $pdata['fieldtype'];
		
		if ($fieldtype != "blog") {
			$this->error('wrong action.');
		}

		$extrahead = '<script type="text/javascript" src="'.$CONF['AdminURL'].'javascript/templateEdit.js"></script>';
		$extrahead .= '<script type="text/javascript">setTemplateEditText("'.addslashes(_EDITTEMPLATE_EMPTY).'");</script>'."\n";
		$oPluginAdmin->start($extrahead);

?>
<p><a href="<?php echo $this->url ?>index.php?action=overview"><?php echo _NPPLUGIN_GOBACK?></a> | <a href="<?php echo $this->hsc($manager->addTicketToUrl($this->url.'index.php?action=partedit&tableid='.$tableid)); ?>"><?php echo $this->hsc($pname)._NPPLUGIN_GOBACK_PTOP ?></a></p>

<?php
		echo '<h2>'._NPPART_EDIT_TITLE.' : '.$this->hsc($pname)."</h2>\n";
		if ($msg) echo "<p>"._MESSAGE.": $msg</p>\n\n";
?>
<form method="post" action="<?php echo $this->url ?>index.php">
	<div>
	<?php $manager->addTicketHidden(); ?>
	<input type="hidden" name="action" value="updateallbfield" />
	<input type="hidden" name="tableid" value="<?php echo $tableid ?>" />
	<input type="hidden" name="context" value="blog" />
	<table><tr>
			<th colspan="2"><?php echo _NPFIELD_UPDATE?></th>
		</tr><tr>
			<td><?php echo _NPFIELD_UPDATE ?></td>
			<td>
				<input type="submit" tabindex="10" value="<?php echo _NPFIELD_UPDATE_BTN?>" onclick="return checkSubmit();" />
				<input type="reset" tabindex="20" value="<?php echo _NPFIELD_RESET_BTN?>" />
			</td>
		</tr><tr>
			<th colspan="2"><?php echo _NPBLOG_TITLE?></th>
<?php
			$res = sql_query("SELECT bnumber, bname FROM ".sql_table('blog'));
			while ($o = mysql_fetch_object($res)) {
				$bnames[$o->bnumber] = $o->bname;
			}
			if (isset($bnames)) {
				$bres = sql_query("SELECT refid, body FROM ".$this->data_table." WHERE context='blog' and tableid=$tableid");
				$bbodys = array();
				while ($b = mysql_fetch_object($bres)) {
					$bbodys[$b->refid] = $b->body;
				}
				$cnt = 1;
				foreach ($bnames as $k => $v) {
?>
			</tr><tr>
				<td><?php echo $this->hsc($v) ?></td>
				<td id="td<?php echo $cnt ?>"><textarea name="<?php echo 'bbody['.$k.']' ?>" rows="<?php echo $this->brows ?>" cols="<?php echo $this->bcols ?>" id="textarea<?php echo $cnt ?>" tabindex="30"><?php echo $this->hsc($bbodys[$k]) ?></textarea></td>
<?php
					$cnt ++;
				}
			}
?>
		</tr><tr>
			<th colspan="2"><?php echo _NPFIELD_UPDATE?></th>
		</tr><tr>
			<td><?php echo _NPFIELD_UPDATE ?></td>
			<td>
				<input type="submit" tabindex="40" value="<?php echo _NPFIELD_UPDATE_BTN?>" onclick="return checkSubmit();" />
				<input type="reset" tabindex="50" value="<?php echo _NPFIELD_RESET_BTN?>" />
			</td>
	</tr></table>
	</div>
</form>

<?php
	
		$oPluginAdmin->end();
	
	}

//-----

	function action_updatefield() {
		global $member;
		
		$member->isAdmin() or $this->disallow();
		
		$tableid = intPostVar('tableid');
		$refid = intPostVar('refid');
		$context = postVar('context');
		$body = postVar('body');

		if ($context != 'blog') {
			$this->error('Wrong field: '.$this->hsc($context));
		} elseif (!$refid) {
			$this->error('ID is missing ...');
		} elseif (!$tableid) {
			$this->error('parts is missing ...');
		}
		
		$this->doUpdateField($refid,$context,$tableid,$body);
		
		$this->action_editfield(_MSG_BLOGFIELD_UPDATED);
	}
	
//-----

	function action_updatecatfield() {
		global $member;
		
		$member->isAdmin() or $this->disallow();
		
		$tableid = intPostVar('tableid');
		$context = postVar('context');
		$cbodys = $this->requestEx('cbody');
		
		if ($context != 'category') {
			$this->error('Wrong field: '.$this->hsc($context));
		} elseif (!$tableid) {
			$this->error('parts is missing ...');
		}
		
		foreach ($cbodys as $refid => $body) {
			$this->doUpdateField($refid,$context,$tableid,$body);
		}
		$this->action_editfield(_MSG_CATFIELD_UPDATED);
	}
	
//-----

	function action_updateallbfield() {
		global $member;
		
		$member->isAdmin() or $this->disallow();
		
		$tableid = intPostVar('tableid');
		$context = postVar('context');
		$bbodys = $this->requestEx('bbody');
		
		if ($context != 'blog') {
			$this->error('Wrong field: '.$this->hsc($context));
		} elseif (!$tableid) {
			$this->error('parts is missing ...');
		}
		
		foreach ($bbodys as $refid => $body) {
			$this->doUpdateField($refid,$context,$tableid,$body);
		}
		$this->action_editallbfield(_MSG_BLOGFIELD_UPDATED);
	}
	
//-----

	function doUpdateField($refid, $context, $tableid, $body, $return=0) {

		if ($context == "skin" || $context == "global") $refid = 0;
		
		$checkrow = mysql_query("SELECT * FROM ". $this->data_table ." WHERE refid=$refid and context='$context' and tableid=$tableid");
	
		if (trim($body)) {
			if ($checkrow && mysql_num_rows($checkrow) > 0) {
				$res = mysql_query("UPDATE ". $this->data_table ." SET body='".mysql_real_escape_string($body)."' WHERE refid=$refid and context='$context' and tableid=$tableid");
			} else {
				$res = mysql_query("INSERT INTO ". $this->data_table ." SET refid=$refid, context='$context', body='".mysql_real_escape_string($body)."', tableid=$tableid");
			}
		} else {
			if ($checkrow && mysql_num_rows($checkrow) > 0) {
				$res = mysql_query("DELETE FROM ". $this->data_table ." WHERE refid=$refid and context='$context' and tableid=$tableid");
			}
		}
		
		if((isset($res) && !$res) || !$checkrow) {
			if ($return) {
				return 'MySQL Error.';
			} else {
				$this->error($this->hsc(sql_error()));
			}
		}
	}
	
//-----

	function action_updateskin() {
		global $member;
		
		$member->isAdmin() or $this->disallow();
		
		$tableid = intRequestVar('tableid');
		if (!$tableid) {
			$this->error('parts is missing ...');
		}
		$fieldtype = postVar('fieldtype');
		if ($fieldtype != "skin" && $fieldtype != "global") {
			$this->error('Wrong type: '.$this->hsc($fieldtype));
		}

		$name = postVar('title');
		if (!isValidSkinName($name))
			$this->error(_ERROR_BADSKINPARTNAME);
		if (($this->tmanager->getNameFromID($tableid) != $name) && $this->tmanager->exists($name))
			$this->error(_ERROR_DUPSKINPARTNAME);

		$this->addToSkin($tableid);
		
		if (postVar('context') == "skin") {
			$this->action_skinedit(_SKIN_UPDATED);
		} else {
			$this->action_partedit(_NPPART_UPDATED);
		}
	
	}

//-----

	function addToSkin($nowid, $basename='') {
		if ($basename) {
			$skin = $this->tmanager->read($basename);
			$oldid = $skin['tableid'];
			$newname = $this->tmanager->getNameFromID($nowid);
			$skin['tableid'] = $nowid;
			$skin['title'] = $newname;
			$res = sql_query("SELECT * FROM ".$this->data_table." WHERE tableid=".$oldid);
			$o = mysql_fetch_object($res);
			
			$this->tmanager->updateTemplate($nowid,$skin);
			$this->doUpdateField(0, $o->context, $nowid, $o->body);
			
		} else {
			$skin = array();
			foreach ($this->template_parts as $val) {
				$skin[$val] = postVar($val);
			}
			$context = postVar('context');
			$body = postVar('body');
			if ($context != 'skin' && $context != 'global') {
				$this->error('Wrong field: '.$this->hsc($context));
			}
			
			$this->tmanager->updateTemplate($nowid,$skin);
			$this->doUpdateField(0, $context, $nowid, $body);
		}
	}
	

//-----

	function action_updatepartdata() {
		global $member;
		
		$member->isAdmin() or $this->disallow();
		
		$tableid = intRequestVar('tableid');
		if (!$tableid) {
			$this->error('parts is missing ...');
		}
		$fieldtype = postVar('fieldtype');
		if ($fieldtype != "blog" && $fieldtype != "blogcat") {
			$this->error('Wrong type: '.$this->hsc($fieldtype));
		}
		
		$name = postVar('title');
		if (!isValidTemplateName($name))
			$this->error(_ERROR_BADSKINPARTNAME);
		
		if (($this->tmanager->getNameFromID($tableid) != $name) && $this->tmanager->exists($name))
			$this->error(_ERROR_DUPSKINPARTNAME);

		$part = array();
		foreach ($this->template_parts as $val) {
			$part[$val] = postVar($val);
		}
		
		$this->tmanager->updateTemplate($tableid, $part);
		
		// jump back to template edit
		$this->action_partedit(_NPPART_UPDATED);
	
	}	

//-----

	function action_skinnew() {
		global $member;
		
		$member->isAdmin() or $this->disallow();
		
		$name = postVar('title');
		if (!isValidSkinName($name))
			$this->error(_ERROR_BADSKINNAME);
		if ($this->tmanager->exists($name))
			$this->error(_ERROR_DUPSKINPARTNAME);

		$newid = $this->tmanager->createTemplate($name);
		$array = array(
			'description'=>postVar('description'),
			'fieldtype'=>'skin',
			'url'=>$name,
			'contenttype'=>'text/html',
			'includemode'=>'auto',
			'skinvartype'=>'index',
			'skintype'=>'pageparser',
			'filter'=>''
		);
		$this->tmanager->updateTemplate($newid,$array);

		$this->action_overview();
	}
	
	function action_partnew() {
		global $member;
		
		$member->isAdmin() or $this->disallow();
		
		$name = postVar('title');
		if (!isValidSkinName($name))
			$this->error(_ERROR_BADPARTNAME);
		if ($this->tmanager->exists($name))
			$this->error(_ERROR_DUPSKINPARTNAME);
		
		$fieldtype = postVar('fieldtype');
		if ($fieldtype != 'blog' && $fieldtype != 'blogcat') {
			$fieldtype = 'global';
		}
		
		$newid = $this->tmanager->createTemplate($name);
		$array = array(
			'description'=>postVar('description'),
			'fieldtype'=>$fieldtype
		);
		$this->tmanager->updateTemplate($newid,$array);

		$this->action_overview();
	}

	function action_skinclone() {
		global $member;
		
		$member->isAdmin() or $this->disallow();
		
		$tableid = intRequestVar('tableid');
		
		$fieldtype = $this->tmanager->getDataFromID("fieldtype",$tableid);
		if ($fieldtype != "skin" && $fieldtype != "global") {
			$this->error('Wrong type: '.$this->hsc($fieldtype));
		}
		
		$basename = $this->tmanager->getNameFromID($tableid);
		$newname = "cloned" . $basename;
		
		if ($this->tmanager->exists($newname)) {
			$i = 1;
			while ($this->tmanager->exists($newname . $i))
				$i++;
			$newname .= $i;
		}		
		
		$newid = $this->tmanager->createTemplate($newname);
		$this->addToSkin(intval($newid), $basename);

		$this->action_overview();
	}
	
	function action_partclone() {
		$this->action_skinclone();
	}

	function action_skindelete() {
		global $member, $oPluginAdmin, $manager;
		
		$member->isAdmin() or $this->disallow();
		
		$tableid = intRequestVar('tableid');
		
		$oPluginAdmin->start();
		
		$name = $this->tmanager->getNameFromId($tableid);
		$pageflg = ($this->tmanager->getDataFromId('fieldtype',$tableid) == "skin");
		
		?>
			<h2><?php echo _DELETE_CONFIRM?></h2>
			
			<p><?php echo ($pageflg) ? _CONFIRMTXT_SKIN : _CONFIRMTXT_NPPART?><b><?php echo $name ?></b></p>
			
			<form method="post" action="<?php echo $this->url ?>index.php"><div>
				<?php $manager->addTicketHidden(); ?>
				<input type="hidden" name="action" value="skindeleteconfirm" />
				<input type="hidden" name="tableid" value="<?php echo $tableid ?>" />
				<input type="submit" tabindex="10" value="<?php echo _DELETE_CONFIRM_BTN?>" />
			</div></form>
		<?php
		
		$oPluginAdmin->end();
	}
	
	function action_partdelete() {
		$this->action_skindelete();
	}
	
	function action_skindeleteconfirm() {
		global $member, $manager;
		
		$tableid = intRequestVar('tableid');
		
		$member->isAdmin() or $this->disallow();
		
		$this->tmanager->deleteTemplate($tableid);
		
		$checkrow = mysql_query("SELECT * FROM ". $this->data_table ." WHERE tableid=".$tableid);
		if ($checkrow && mysql_num_rows($checkrow) > 0) {
			sql_query ("DELETE FROM ".$this->data_table." WHERE tableid=".$tableid);
		}
		
		$this->action_overview();
	}
	
	function hsc($str) {
		return htmlspecialchars($str,ENT_QUOTES,_CHARSET);
	}
} // ExtraSkin_ADMIN end

// ----------------------------------------------------------------------------

$myAdmin = new ExtraSkin_ADMIN();
if (requestVar('action')) {
	$myAdmin->action(requestVar('action'));
} else {
	$myAdmin->action('overview');
}


?>

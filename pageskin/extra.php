<?php
include('./config.php');

ParsePlugExtraSkin();
  
function ParsePlugExtraSkin() {
	global $CONF, $manager, $member;

	// under v3.2 needs this
	if ($CONF['DisableSite'] && !$member->isAdmin()) {
		header('Location: ' . $CONF['DisableSiteURL']);
		exit;
	}

  $data = serverVar('QUERY_STRING');
	$temp = preg_split("/=|&/",$data);

	$plugin =& $manager->getPlugin('NP_ExtraSkinJP');
	$plugin->extra_selector($temp);

}
?>
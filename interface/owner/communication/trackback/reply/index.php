<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'url' => array('url', 'default'=> null)
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.trackback");

requireStrictRoute();

/// First, detect trackback URL from RDF information.
$info  = getRDFfromURL($_POST['url']);
if(empty($info)) {
	$blogInfo = getInfoFromURL($_POST['url']);
	if(!empty($blogInfo) && $blogInfo['service'] != null) {
		$info['trackbackURL'] = getTrackbackURLFromInfo($_POST['url'],$blogInfo['service']);
	} else {
		respond::ResultPage(false);
		exit;
	}
}
respond::ResultPage(!empty($_POST['url']) && sendTrackback($blogid, $suri['id'], trim($info['trackbackURL'])));
?>

<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'group' => array('int', 0, 'default' => 0),
		'starred' => array(array('0','1'), 'default' => '0'),
		'keyword' => array('string', 'default' => '')
	)
);
require ROOT . '/library/includeForReader.php';
$result = array('error' => '0');
ob_start();
printFeedGroups($blogid, $_POST['group'], $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
respond::PrintResult($result);
?>

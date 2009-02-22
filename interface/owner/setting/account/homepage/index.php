<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'homepage' => array('url','mandatory' => false),
		'type' => array('string'),
		'blogid' => array('id')
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireStrictRoute();
if (User::setHomepage($_POST['type'],$_POST['homepage'],$_POST['blogid'])) {
	$result = 0;
}
else {
	$result = -1;
}
//TODO : 현재 checkAjaxRequest가 동작하지 않으므로 관련부분 주석처리
//if ( checkAjaxRequest() ) {
	respond::ResultPage( $result );
/*}
else {
	if (!$result) {
		$message = '대표 주소를 변경하였습니다.';
	}
	else {
		$message = '대표 주소 변경에 실패 하였습니다.';
	}
	respond::NoticePage($message, $blogURL."/owner/setting/account");
}
*/
?>

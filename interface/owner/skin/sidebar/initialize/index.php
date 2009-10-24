<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$ajaxcall = isset($_REQUEST['ajaxcall']) ? true : false;

require ROOT . '/library/preprocessor.php';
requireStrictRoute();
requireLibrary('blog.skin');
$skin = new Skin($skinSetting['skin']);

if (!array_key_exists('viewMode', $_REQUEST)) $_REQUEST['viewMode'] = '';
else $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

Setting::removeBlogSettingGlobal('sidebarOrder');
$skin->purgeCache();
if($ajaxcall == false) header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
else Respond::ResultPage(0);
?>

<?php
/// Copyright (c) 2004-2018, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
importlib("model.blog.link");
requireStrictRoute();
Respond::ResultPage(deleteLink($blogid, $suri['id']));
?>

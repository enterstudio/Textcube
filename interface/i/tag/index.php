<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
$context = Model_Context::getInstance();
requireView('iphoneView');
printMobileHTMLHeader();
printMobileHTMLMenu();

if(strlen($suri['value'])) {
	if(!isset($suri['id'])) {
		$tag = getTagId($blogid, $suri['value']);
	} else {
		$tag = $suri['id'];
		$suri['value'] = getTagById($blogid, $suri['id']);
	}

	$blog['entriesOnList'] = 8;
	$listWithPaging = getEntryListWithPagingByTag($blogid, $tag, $suri['page'], $blog['entriesOnList']);
	if (!array_key_exists('total',$listWithPaging[1])) $listWithPaging[1]['total'] = 0;
	$list = array('title' => $suri['value'], 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	?>
	<ul data-role="listview" class="posts" id="tag_<?php echo $suri['page'];?>" title="<?php echo getTagById($blogid, $suri['id']);?>" selected="false">
	<?php
		$itemsView = '<li class="group ui-bar ui-bar-e">'.CRLF;
		$itemsView .= '	<span class="left">' . getTagById($blogid, $suri['id']) . ' ('.$list['count'].')</span>'.CRLF;
		$itemsView .= '	<span class="right">Page <span class="now_page">' . $paging['page'] . '</span> / '.$paging['pages'].'</span>'.CRLF;
		$itemsView .= '</li>'.CRLF;
		foreach ($list['items'] as $item) {	
			$author = User::getName($item['userid']);
			if($imageName = printMobileAttachmentExtract(printMobileEntryContent($blogid, $item['userid'], $item['id']))){
				$imageSrc = printMobileImageResizer($blogid, $imageName, 28);
			}else{
				$imageSrc = $service['path'] . '/resources/style/iphone/image/noPostThumb.png';
			}
			$itemsView .= '<li class="post_item">'.CRLF;
			$itemsView .= '	<span class="image"><img src="' . $imageSrc . '" width="28" height="28" /></span>'.CRLF;
			$itemsView .= '	<a href="' . $context->getProperty('uri.blog') . '/entry/' . $item['id'] . '" class="link">'.CRLF;
			$itemsView .= '		<div class="post">'.CRLF;
			$itemsView .= '			<span class="title">' . fireEvent('ViewListTitle', htmlspecialchars($item['title'])) . '</span>'.CRLF;
			$itemsView .= '			<span class="description">' . Timestamp::format5($item['published']) . '</span><span class="ui-li-count"> ' . _textf('댓글 %1개',($item['comments'] > 0 ? $item['comments'] : 0))  . '</span>'.CRLF;
			$itemsView .= '		</div>'.CRLF;
			$itemsView .= '	</a>'.CRLF;
			$itemsView .= '</li>'.CRLF;
		}

		$itemsView .= '</ul>'.CRLF;
		print $itemsView;
		print printMobileListNavigation($paging,'tag/' . $suri['id']);
}
printMobileHTMLFooter();
?>

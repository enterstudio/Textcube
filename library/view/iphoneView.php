<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function printIphoneEntryContentView($blogid, $entry, $keywords = array()) {
	global $blogURL;
	if (doesHaveOwnership() || ($entry['visibility'] >= 2) || (isset($_COOKIE['GUEST_PASSWORD']) && (trim($_COOKIE['GUEST_PASSWORD']) == trim($entry['password']))))
		print '<div class="entry_body">'.(getEntryContentView($blogid, $entry['id'], $entry['content'], $entry['contentFormatter'], $keywords)).'</div>';
	else
	{
	?>
	<p><b><?php echo _text('Protected post!');?></b></p>
	<form id="passwordForm" class="dialog" method="post" action="<?php echo $blogURL;?>/protected/<?php echo $entry['id'];?>">
		<fieldset>
			<label for="password"><?php echo _text('Password:');?></label>
			<input type="password" id="password" name="password" />
			<a href="#" class="whiteButton margin-top10" type="submit"><?php echo _text('View Post');?></a>
        </fieldset>
	</form>
	<?php
	}
}

function printIphoneEntryContent($blogid, $userid, $id) {
	global $database;
	$result = POD::queryCell("SELECT content 
		FROM {$database['prefix']}Entries
		WHERE 
			blogid = $blogid AND userid = $userid AND id = $id");
	return $result;
}

function printIphoneCategoriesView($totalPosts, $categories) {
	global $blogURL, $service, $blog;
	requireModel('blog.category');
	requireLibrary('blog.skin');
	$blogid = getBlogId();
	$categoryCount = 0;
	$categoryCountAll = 0;
	$parentCategoryCount = 0;
	$tree = array('id' => 0, 'label' => 'All Category', 'value' => $totalPosts, 'link' => "$blogURL/category/0", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		if(doesHaveOwnership() || getCategoryVisibility($blogid, $category1['id']) > 1) {
			foreach ($category1['children'] as $category2) {
				if( doesHaveOwnership() || getCategoryVisibility($blogid, $category2['id']) > 1) {
					array_push($children, 
						array('id' => $category2['id'], 
							'label' => $category2['name'], 
							'value' => (doesHaveOwnership() ? $category2['entriesInLogin'] : $category2['entries']), 
							'link' => "$blogURL/category/" . $category2['id'], 
							'children' => array()
						)
					);
					$categoryCount = $categoryCount + (doesHaveOwnership() ? $category2['entriesInLogin'] : $category2['entries']);
				}
				$categoryCountAll = $categoryCountAll + (doesHaveOwnership() ? $category2['entriesInLogin'] : $category2['entries']);
			}
			$parentCategoryCount = (doesHaveOwnership() ? $category1['entriesInLogin'] - $categoryCountAll : $category1['entries'] - $categoryCountAll);
			if($category1['id'] != 0) {
				array_push($tree['children'], 
					array('id' => $category1['id'], 
						'label' => $category1['name'], 
						'value' => $categoryCount + $parentCategoryCount, 
						'link' => "$blogURL/category/" . $category1['id'], 
						'children' => $children)
				);
			}
			$categoryCount = 0;
			$categoryCountAll = 0;
			$parentCategoryCount = 0;
		}
	}
	return printIphonePrintTreeView($tree, true);
}

function printIphonePrintTreeView($tree, $xhtml=true) {
	if ($xhtml) {
		$printCategory  = '<li class="category"><a href="' . htmlspecialchars($tree['link']) . '" class="link">' . htmlspecialchars($tree['label']);
		$printCategory .= ' <span class="c_cnt">' . $tree['value'] . '</span>';
		$printCategory .= '</a></li>';
		for ($i=0; $i<count($tree['children']); $i++) {
			$child = $tree['children'][$i];
			$printCategory .= '<li class="category"><a href="' . htmlspecialchars($child['link']) . '" class="link">' . htmlspecialchars($child['label']);
			$printCategory .= ' <span class="c_cnt">' . $child['value'] . '</span>';
			$printCategory .= '</a></li>';
			if (sizeof($child['children']) > 0) {
				for ($j=0; $j<count($child['children']); $j++) {
					$leaf = $child['children'][$j];
					$printCategory .= '<li class="category_sub"><a href="' . htmlspecialchars($leaf['link']) . '" class="link">&bull;&nbsp; ' . htmlspecialchars($leaf['label']);
					$printCategory .= ' <span class="c_cnt">' . $leaf['value'] . '</span>';
					$printCategory .= '</a></li>';
				}
			}
		}
		return $printCategory;
	}
}

function printIphoneArchives($blogid) {
	global $database;
	$archives = array();
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	$skinSetting = getSkinSetting($blogid);
	$result = POD::queryAllWithDBCache("SELECT EXTRACT(year_month FROM FROM_UNIXTIME(e.published)) period, COUNT(*) count 
		FROM {$database['prefix']}Entries e
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 
		GROUP BY period 
		ORDER BY period 
		DESC ");
	if ($result) {
		foreach($result as $archive)
			array_push($archives, $archive);
	}
	return $archives;
}

function printIphoneArchivesView($archives) {
	global $blogURL;
	$oldPeriod = '';
	$newPeriod = '';
	foreach ($archives as $archive) {
		$newPeriod = substr($archive['period'],0,4);
		if($newPeriod != $oldPeriod){
			$printArchive .= '<li class="group"><span class="left">' . $newPeriod . '</span><span class="right">&nbsp;</span></li>';
		}
		$dateName = date("F Y",(mktime(0,0,0,substr($archive['period'],4),1,substr($archive['period'],0,4))));
		$printArchive .= '<li class="archive"><a href="' . $blogURL . '/archive/' . $archive['period'] . '" class="link">' . $dateName;
		$printArchive .= ' <span class="c_cnt">' . $archive['count'] . '</span>';
		$printArchive .= '</a></li>';
		$oldPeriod = substr($archive['period'],0,4);
	}
	return $printArchive;
}

function printIphoneTags($blogid, $flag = 'random', $max = 10) {
	global $database, $skinSetting;
	$tags = array();
	$aux = "limit $max";
	if ($flag == 'count') { // order by count
			$tags = POD::queryAll("SELECT `name`, count(*) `cnt`, t.id FROM `{$database['prefix']}Tags` t,
				`{$database['prefix']}TagRelations` r, 
				`{$database['prefix']}Entries` e 
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid 
				GROUP BY r.tag 
				ORDER BY `cnt` DESC $aux");
	} else if ($flag == 'name') {  // order by name
			$tags = POD::queryAll("SELECT DISTINCT name, count(*) cnt, t.id FROM `{$database['prefix']}Tags` t, 
				`{$database['prefix']}TagRelations` r,
				`{$database['prefix']}Entries` e 
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid 
				GROUP BY r.tag 
				ORDER BY t.name $aux");
	} else { // random
			$tags = POD::queryAll("SELECT name, count(*) cnt, t.id FROM `{$database['prefix']}Tags` t,
				`{$database['prefix']}TagRelations` r,
				`{$database['prefix']}Entries` e
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid 
				GROUP BY r.tag 
				ORDER BY RAND() $aux");
	}
	return $tags;
}

function printIphoneTagsView($tags) {
	global $blogURL, $service;
	ob_start();
	list($maxTagFreq, $minTagFreq) = getTagFrequencyRange();
	foreach ($tags as $tag) {
		$printTag .= '<li class="tag"> <a href="' . $blogURL . '/tag/' . $tag['id'] . '" class="cloud' . getTagFrequency($tag, $maxTagFreq, $minTagFreq).'" >' . htmlspecialchars($tag['name']);
		$printTag .= '</a> </li>';
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $printTag;
}

function printIphoneLinksView($links) {
	global $blogURL, $skinSetting, $suri, $pathURL;
	if( rtrim( $suri['url'], '/' ) == $pathURL ) {
		$home = true;
	} else {
		$home = false;
	}
	foreach ($links as $link) {
		if((!doesHaveOwnership() && $link['visibility'] == 0) ||
			(!doesHaveMembership() && $link['visibility'] < 2)) {
			continue;
		}
		$linkView .= '<li><a href="' . htmlspecialchars($link['url']) . '" class="link" target="_blank">' . htmlspecialchars(UTF8::lessenAsEm($link['name'], $skinSetting['linkLength'])) . '</a></li>'.CRLF;
	}
	return $linkView;
}

function printIphoneHtmlHeader($title = '') {
	global $blogURL, $blog, $service, $blogid;
	$title = htmlspecialchars($blog['title']) . ' :: ' . $title;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<title><?php echo $title;?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
		<link rel="stylesheet" type="text/css" href="<?php echo $service['path'];?>/style/iphone/iphone.css" />
		<script type="application/x-javascript" src="<?php echo $service['path'];?>/script/iphone/iphone.js"></script>
	</head>
	<body>
<?php
}

function printIphoneAttachmentExtract($content){
	$result = null;
	if(preg_match_all('/\[##_(1R|1L|1C|2C|3C|iMazing|Gallery)\|[^|]*\.(gif|jpg|jpeg|png|bmp|GIF|JPG|JPEG|PNG|BMP)\|.*_##\]/si', $content, $matches)) {
		$split = explode("|", $matches[0][0]);
		$result = $split[1];
	}else if(preg_match_all('/<img[^>]+?src=("|\')?([^\'">]*?)("|\')/si', $content, $matches)) {
		if( !stristr('http://', $matches[2][0]) ){
			$result = basename($matches[2][0]);
		}
	}
	return $result;
}

function printIphoneHtmlFooter() {
?>
	</body>
</html>
<?php
}

function printIphoneNavigation($entry, $jumpToComment = true, $jumpToTrackback = true, $paging = null) {
	global $suri, $blogURL;
?>
	<ul class="content navigation">
		<?php
	if (isset($paging['prev'])) {
?>
		<li><a href="<?php echo $blogURL;?>/entry/<?php echo $paging['prev'];?>" accesskey="1"><?php echo _text('Show previous post');?></a></li>
		<?php
	}
	if (isset($paging['next'])) {
?>
		<li><a href="<?php echo $blogURL;?>/entry/<?php echo $paging['next'];?>" accesskey="2"><?php echo _text('Show next post');?></a></li>
		<?php
	}
	if (!isset($paging)) {
?>	
		<li><a href="<?php echo $blogURL;?>/entry/<?php echo $entry['id'];?>" accesskey="3"><?php echo _text('Show posts');?></a></li>
		<?php
	}
	if ($jumpToComment) {
?>
		<li><a href="<?php echo $blogURL;?>/comment/<?php echo $entry['id'];?>" accesskey="4"><?php echo _text('Show comment');?> (<?php echo $entry['comments'];?>)</a></li>
		<?php
	}
	if ($jumpToTrackback) {
?>
		<li><a href="<?php echo $blogURL;?>/trackback/<?php echo $entry['id'];?>" accesskey="5"><?php echo _text('Show trackbacks');?> (<?php echo $entry['trackbacks'];?>)</a></li>
		<?php
	}
	if ($suri['directive'] != '/iphone') {
?>
		<li class="last_no_line"><a href="#" onclick="self.location.reload();" accesskey="6"><?php echo _text('Show front page');?></a></li>
		<?php
	}
?>
	</ul>
<?php
}

function printIphoneTrackbackView($entryId) {
	$trackbacks = getTrackbacks($entryId);
	if (count($trackbacks) == 0) {
?>
		<p>&nbsp;<?php echo _text('No trackback');?></p>
		<?php
	} else {
		foreach (getTrackbacks($entryId) as $trackback) {
?>
		<ul id="trackback_<?php echo $commentItem['id'];?>" class="trackback">
			<li class="group">
				<span class="left">
					<?php echo htmlspecialchars($trackback['subject']);?>
				</span>
				<span class="right">&nbsp;</span>
			</li>
			<li class="body">
				<span class="date">DATE : <?php echo Timestamp::format5($trackback['written']);?></span>
				<?php echo htmlspecialchars($trackback['excerpt']);?>
			</li>
		</ul>
		<?php
		}
	}
}

function printIphoneCommentView($entryId) {
	global $blogURL;
	$comments = getComments($entryId);
	if (count($comments) == 0) {
?>
		<p>&nbsp;<?php echo _text('Comments does not exist');?></p>
		<?php
	} else {
		foreach ($comments as $commentItem) {
?>
		<ul id="comment_<?php echo $commentItem['id'];?>" class="comment">
			<li class="group">
				<span class="left">
					<?php if(!empty($commentItem['name'])) { ?><strong><?php echo htmlspecialchars($commentItem['name']);?></strong><?php } ?>
					(<?php echo Timestamp::format5($commentItem['written']);?>)
				</span>
				<span class="right">
					<a href="<?php echo $blogURL;?>/comment/comment/<?php echo $commentItem['id'];?>">RE</a> :
					<a href="<?php echo $blogURL;?>/comment/delete/<?php echo $commentItem['id'];?>">DEL</a>
				</span>
			</li>
			<li class="body">
				<?php echo ($commentItem['secret'] && doesHaveOwnership() ? '<div class="hiddenComment" style="font-weight: bold; color: #e11">'._t('Secret Comment').' &gt;&gt;</div>' : '').nl2br(addLinkSense(htmlspecialchars($commentItem['comment'])));?>
			</li>
			<?php
			foreach (getCommentComments($commentItem['id']) as $commentSubItem) {
?>
			<li class="groupSub">
				<span class="left">&nbsp;Re :
					<?php if(!empty($commentSubItem['name'])) { ?><strong><?php echo htmlspecialchars($commentSubItem['name']);?></strong><?php } ?>
					(<?php echo Timestamp::format5($commentSubItem['written']);?>)
				</span>
				<span class="right">
					<a href="<?php echo $blogURL;?>/comment/delete/<?php echo $commentSubItem['id'];?>">DEL</a><br />
				</span>
			</li>
			<li class="body">
				<?php echo ($commentSubItem['secret'] && doesHaveOwnership() ? '<div class="hiddenComment" style="font-weight: bold; color: #e11">'._t('Secret Comment').' &gt;&gt;</div>' : '').nl2br(addLinkSense(htmlspecialchars($commentSubItem['comment'])));?>
			</li>
			<?php
			}
?>
		</ul>
		<?php
		}
	}
	printIphoneCommentFormView($entryId, 'Write comment', 'comment');
}

function printIphoneCommentFormView($entryId, $title, $actionURL) {
	global $blogURL;
?>
	
	<form method="GET" action="<?php echo $blogURL;?>/<?php echo $actionURL;?>/add/<?php echo $entryId;?>" class="commentForm">
	<h2><?php echo $title;?></h2>
	<fieldset>
		<?php
	if (!doesHaveOwnership()) {
?>
		<input type="hidden" name="id" value="<?php echo $entryId;?>" />
		<input type="hidden" id="secret_<?php echo $entryId;?>" name="secret_<?php echo $entryId;?>" value="0" />
		<div class="row">
			<label>Private comment</label>
			<div class="toggle" onclick="secretToggleCheck(this, <?php echo $entryId;?>);"><span class="thumb"></span><span class="toggleOn">ON</span><span class="toggleOff">OFF</span></div>
		</div>
		<div class="row">
			<label for="name_<?php echo $entryId;?>"><?php echo _text('Name');?></label>
			<input type="text" id="name_<?php echo $entryId;?>" name="name_<?php echo $entryId;?>" value="<?php echo isset($_COOKIE['guestName']) ? htmlspecialchars($_COOKIE['guestName']) : '';?>" />
		</div>
		<div class="row">
			<label for="password_<?php echo $entryId;?>"><?php echo _text('Password');?></label>
			<input type="password" id="password_<?php echo $entryId;?>" name="password_<?php echo $entryId;?>" />
		</div>
		<div class="row">
			<label for="homepage_<?php echo $entryId;?>"><?php echo _text('Homepage');?></label>
			<input type="text" id="homepage_<?php echo $entryId;?>" name="homepage_<?php echo $entryId;?>"  value="<?php echo (isset($_COOKIE['guestHomepage']) && $_COOKIE['guestHomepage'] != 'http://') ? htmlspecialchars($_COOKIE['guestHomepage']) : 'http://';?>" />
		</div>
		<?php
	}
?>
		<div class="row">
			<textarea cols="40" rows="6" id="comment_<?php echo $entryId;?>" name="comment_<?php echo $entryId;?>"></textarea>
		</div>
		<a href="#" class="whiteButton margin-top10" type="submit"><?php echo _text('Submit');?></a>
	</fieldset>
	</form>
	
	<?php
}

function printIphoneErrorPage($messageTitle, $messageBody, $redirectURL) {
?>
	<div id="postError" title="Error" class="panel">
		<h2 class="title"><?php echo htmlspecialchars($messageTitle);?></h2>
		<div class="content">
			<?php echo htmlspecialchars($messageBody);?>
		</div>
		<a href="<?php echo $redirectURL;?>" class="whiteButton margin-top10"><?php echo _text('Go to previous page');?></a>
	</div>
<?php
}

function printIphoneSimpleMessage($message, $redirectMessage, $redirectURL, $title = '') {
?>
	<div id="postSuccess" title="Successfully" class="panel">
		<div class="content">
			<?php echo htmlspecialchars($message);?>
		</div>
		<a href="<?php echo $redirectURL;?>" class="whiteButton margin-top10"><?php echo htmlspecialchars($redirectMessage);?></a>
	</div>
<?php
}
?>

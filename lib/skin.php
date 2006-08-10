<?php

class Skin {
	var $outter;
	var $skin;
	var $list;
	var $listItem;
	var $commentList;
	var $commentListItem;
	var $rss;
	var $rss_date;
	var $rss_rep;
	var $keywordItem;
	var $keywordGroup;
	var $keyword;
	var $noticeItem;
	var $recentNotice;
	var $recentNoticeItem;
	var $locative;
	var $locativeSpot;
	var $locativeEntry;
	var $guest;
	var $guestGuest;
	var $guestMember;
	var $guestItem;
	var $guestSubItem;
	var $search;
	var $recentEntry;
	var $recentComments;
	var $recentTrackback;
	var $management;
	var $entryProtected;
	var $entry;
	var $paging;
	var $pagingItem;
	var $archive;
	var $s_ps;
	var $s_ps_rep;
	var $comment;
	var $commentCountNone;
	var $commentCountSingle;
	var $commentCountMultiple;
	var $commentForm;
	var $commentGuest;
	var $commentMember;
	var $commentItem;
	var $commentSubItem;
	var $trackbacks;
	var $trackback;
	var $trackbackCountNone;
	var $trackbackCountSingle;
	var $trackbackCountMultiple;
	var $tagLabel;
	var $siteTag;
	var $siteTagItem;
	var $randomTags;
	var $s_link_rep;
	var $pageTitle;
	var $pageError;
	var $aux;

	function Skin($name) {
		global $service, $blogURL;
		global $owner;
		
		if (strncmp($name, 'customize/', 10) == 0) {
			$name = "customize/$owner";
		} else {
			$name = Path::getBaseName($name);
		}
		
		if (($name == '.') || ($name == '..')) {
			respondErrorPage(_text('스킨 정보가 존재하지 않습니다.'), _text('로그인'), $blogURL."/owner");
		}
		
		$filename = ROOT . "/skin/$name/skin.html";
		
		if (!is_file($filename)) {
			respondErrorPage(_text('스킨 정보가 존재하지 않습니다.'), _text('로그인'), $blogURL."/owner");
		}
		
		if (!$sval = file_get_contents($filename))
			respondErrorPage(_text('스킨 정보가 존재하지 않습니다.'), _text('로그인'), $blogURL."/owner");

		$sval = replaceSkinTag($sval, 'html');
		$sval = replaceSkinTag($sval, 'head');
		$sval = replaceSkinTag($sval, 'body');
		handleTags($sval);

		list($sval, $this->sidebarTitles) = cutSkinTag($sval, 'sidebar_titles');
		list($sval, $this->sidebarItem) = cutSkinTag($sval, 'sidebar_rep');
		handleSidebars($this);
		dress('sidebar_rep', $this->sidebarItem, $sval);

		$sval = str_replace('./', "{$service['path']}/skin/$name/", $sval);
		list($sval, $this->listItem) = cutSkinTag($sval, 'list_rep');
		list($sval, $this->list) = cutSkinTag($sval, 'list');
		list($sval, $this->commentListItem) = cutSkinTag($sval, 'rplist_rep');
		list($sval, $this->commentList) = cutSkinTag($sval, 'rplist');
		list($sval, $this->rss_rep) = cutSkinTag($sval, 'rss_rep');
		list($sval, $this->rss_date) = cutSkinTag($sval, 'rss_date');
		list($sval, $this->rss) = cutSkinTag($sval, 'rss');
		list($sval, $this->keywordItem) = cutSkinTag($sval, 'keyword_rep');
		list($sval, $this->keywordGroup) = cutSkinTag($sval, 'keyword_date_rep');
		list($sval, $this->keyword) = cutSkinTag($sval, 'keyword');
		list($sval, $this->noticeItem) = cutSkinTag($sval, 'notice_rep');
		list($sval, $this->recentNoticeItem) = cutSkinTag($sval, 'rct_notice_rep');
		list($sval, $this->recentNotice) = cutSkinTag($sval, 'rct_notice');
		list($sval, $this->locativeEntry) = cutSkinTag($sval, 'local_info_rep');
		list($sval, $this->locativeSpot) = cutSkinTag($sval, 'local_spot_rep');
		list($sval, $this->locative) = cutSkinTag($sval, 'local');
		list($sval, $this->guestSubItem) = cutSkinTag($sval, 'guest_reply_rep');
		list($sval, $this->guestItem) = cutSkinTag($sval, 'guest_rep');
		list($sval, $this->guestGuest) = cutSkinTag($sval, 'guest_form');
		list($sval, $this->guestMember) = cutSkinTag($sval, 'guest_member');
		list($sval, $this->guest) = cutSkinTag($sval, 'guest');
		list($sval, $this->entryProtected) = cutSkinTag($sval, 'article_protected');
		list($sval, $this->management) = cutSkinTag($sval, 'ad_div');
		list($sval, $this->trackbackCountNone) = cutSkinTag($sval, 'tb_none');
		list($sval, $this->trackbackCountSingle) = cutSkinTag($sval, 'tb_single');
		list($sval, $this->trackbackCountMultiple) = cutSkinTag($sval, 'tb_multiple');
		list($sval, $this->commentCountNone) = cutSkinTag($sval, 'rp_none');
		list($sval, $this->commentCountSingle) = cutSkinTag($sval, 'rp_single');
		list($sval, $this->commentCountMultiple) = cutSkinTag($sval, 'rp_multiple');
		list($sval, $this->trackback) = cutSkinTag($sval, 'tb_rep');
		list($sval, $this->trackbacks) = cutSkinTag($sval, 'tb');
		list($sval, $this->tagLabel) = cutSkinTag($sval, 'tag_label');
		list($sval, $this->siteTagItem) = cutSkinTag($sval, 'tag_rep');
		list($sval, $this->siteTag) = cutSkinTag($sval, 'tag');
		list($sval, $this->randomTags) = cutSkinTag($sval, 'random_tags');
		list($sval, $this->commentSubItem) = cutSkinTag($sval, 'rp2_rep');
		list($sval, $this->commentItem) = cutSkinTag($sval, 'rp_rep');
		list($sval, $this->commentGuest) = cutSkinTag($sval, 'rp_guest');
		list($sval, $this->commentMember) = cutSkinTag($sval, 'rp_member');
		list($sval, $this->commentForm) = cutSkinTag($sval, 'rp_form');
		list($sval, $this->comment) = cutSkinTag($sval, 'rp');
		list($sval, $this->pageError) = cutSkinTag($sval, 'page_error');
		list($sval, $this->entry) = cutSkinTag($sval, 'article_rep');
		list($sval, $this->pagingItem) = cutSkinTag($sval, 'paging_rep');
		list($sval, $this->paging) = cutSkinTag($sval, 'paging');
		list($sval, $this->archive) = cutSkinTag($sval, 'archive_rep');
		list($sval, $this->search) = cutSkinTag($sval, 'search');
		list($sval, $this->recentEntry) = cutSkinTag($sval, 'rctps_rep');
		list($sval, $this->recentComments) = cutSkinTag($sval, 'rctrp_rep');
		list($sval, $this->recentTrackback) = cutSkinTag($sval, 'rcttb_rep');
		list($sval, $this->s_link_rep) = cutSkinTag($sval, 'link_rep');
		list($sval, $this->skin) = cutSkinTag($sval, 't3');
		list($sval, $this->pageTitle) = cutSkinTag($sval, 'page_title');
		$this->outter = $sval;
	}
}

class KeywordSkin {
	var $outter;
	var $skin;
	var $keylog;
	var $keylogItem;

	function KeywordSkin($name) {
		global $service;
		$filename = ROOT . "/skin/$name/skin_keyword.html";
		if (!$sval = file_get_contents($filename))
			respondErrorPage("KeywordSkin");
		$sval = str_replace('./', "{$service['path']}/skin/$name/", $sval);
		list($sval, $this->keylogItem) = cutSkinTag($sval, 'blog_rep');
		list($sval, $this->keylog) = cutSkinTag($sval, 'blog');
		list($sval, $this->skin) = cutSkinTag($sval, 't3');
		$this->outter = $sval;
	}
}

function cutSkinTag($contents, $tag) {
	$tagSize = strlen($tag) + 4;
	$begin = strpos($contents, "<s_$tag>");
	if ($begin === false)
		return array($contents, '');
	$end = strpos($contents, "</s_$tag>", $begin + 5);
	if ($end === false)
		return array($contents, '');
	$inner = substr($contents, $begin + $tagSize, $end - $begin - $tagSize);
	$outter = substr($contents, 0, $begin) . "[##_{$tag}_##]" . substr($contents, $end + $tagSize + 1);
	return array($outter, $inner);
}

function removeAllTags($contents) {
	 return preg_replace('@\\[#[#M].?.?([^#M][^#][^\\]])+[#M]#\\]@s', '', $contents);
}

function replaceSkinTag($contents, $tag) {
	$pattern[] = '/(<'.$tag.'.*>)\r?\n/Ui';
	$pattern[] = '/<\/'.$tag.'>/Ui';

	$replacement[] = '$1'.CRLF.'[##_SKIN_'.$tag.'_start_##]';
	$replacement[] = '[##_SKIN_'.$tag.'_end_##]$0';

	return preg_replace($pattern, $replacement, $contents);
}
?>

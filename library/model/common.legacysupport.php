<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* LEGACY FUNCTION SUPPORT
   Functions below will be deprecated after Textcube 1.8 or later.
*/

/***** blog.teamblog *****/
function addTeamUser($email, $name, $comment, $senderName, $senderEmail) {
	return Blog::addUser($email, $name, $comment, $senderName, $senderEmail);
}

function changeACLonBlog($blogid, $ACLtype, $userid, $switch) {  // Change user priviledge on the blog.
	return Blog::changeACLofUser($blogid, $userid, $ACLtype, $switch);
}

function deleteTeamblogUser($userid ,$blogid = null, $clean = true) {
	return Blog::deleteUser($blogid, $userid, $clean);
}

function changeBlogOwner($blogid,$userid) {
	return Blog::changeOwner($blogid, $userid);
}

/***** blog.statistics *****/
function getStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getStatistics($blogid);
}

function getDailyStatistics($period) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getDailyStatistics($period);
}

function getMonthlyStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getMonthlyStatistics($blogid);
}

function getRefererStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getRefererStatistics($blogid);
}

function getRefererLogsWithPage($page, $count) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getRefererLogsWithPage($page,$count);
}  

function getRefererLogs() {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getRefererLogs();
}

function updateVisitorStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::updateVisitorStatistics($blogid);
}

function setTotalStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::setTotalStatistics($blogid);
}

/***** common.paging *****/
function initPaging($url, $prefix = '?page=') {
	requireComponent('Textcube.Model.Paging');
	return Paging::initPaging($url,$prefix);
}

function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=', $countItem = null) {
	requireComponent('Textcube.Model.Paging');
	return Paging::fetchWithPaging($sql,$page,$count,$url,$prefix,$countItem);
}
?>

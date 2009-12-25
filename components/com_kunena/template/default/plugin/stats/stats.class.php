<?php
/**
* @version $Id$
* Kunena Component
* @package Kunena
*
* @Copyright (C) 2008 - 2009 Kunena Team All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.kunena.com
*
* Based on FireBoard Component
* @Copyright (C) 2006 - 2007 Best Of Joomla All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.bestofjoomla.com
*
* Based on Joomlaboard Component
* @copyright (C) 2000 - 2004 TSMF / Jan de Graaff / All Rights Reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author TSMF & Jan de Graaff
**/
// Dont allow direct linking
defined( '_JEXEC' ) or die('Restricted access');
$kunena_config =& CKunenaConfig::getInstance();

if ($kunena_config->showstats)
{

if ($kunena_config->showgenstats)
{
$kunena_db->setQuery("SELECT COUNT(*) FROM #__users");
$totalmembers = $kunena_db->loadResult();

$kunena_db->setQuery("SELECT SUM(numTopics) AS titles, SUM(numPosts) AS msgs FROM #__fb_categories WHERE parent='0'");
$totaltmp = $kunena_db->loadObject();
$totaltitles = !empty($totaltmp->titles)?$totaltmp->titles:0;
$totalmsgs = !empty($totaltmp->msgs)?$totaltmp->msgs + $totaltitles:$totaltitles;

$kunena_db->setQuery("SELECT SUM(parent='0') AS totalcats, SUM(parent>'0') AS totalsections FROM #__fb_categories");
$totaltmp = $kunena_db->loadObject();
$totalsections = !empty($totaltmp->totalsections)?$totaltmp->totalsections:0;
$totalcats = !empty($totaltmp->totalcats)?$totaltmp->totalcats:0;

$fb_queryName = $kunena_config->username ? "username" : "name";
$kunena_db->setQuery("SELECT id, {$fb_queryName} AS username FROM #__users WHERE block='0' AND activation='' ORDER BY id DESC", 0, 1);
$_lastestmember = $kunena_db->loadObject();
$lastestmember = $_lastestmember->username;
$lastestmemberid =$_lastestmember->id;

$todaystart = strtotime(date('Y-m-d'));
$yesterdaystart = $todaystart - (1 * 24 * 60 * 60);
$kunena_db->setQuery("SELECT SUM(time >= '{$todaystart}' AND parent='0') AS todayopen, "
                   ." SUM(time >= '{$yesterdaystart}' AND time < '{$todaystart}' AND parent='0') AS yesterdayopen, "
                   ." SUM(time >= '{$todaystart}' AND parent>'0') AS todayanswer, "
                   ." SUM(time >= '{$yesterdaystart}' AND time < '{$todaystart}' AND parent>'0') AS yesterdayanswer "
                   ." FROM #__fb_messages WHERE time >= '{$yesterdaystart}' AND hold='0'");

$totaltmp = $kunena_db->loadObject();
$todayopen = !empty($totaltmp->todayopen)?$totaltmp->todayopen:0;
$yesterdayopen = !empty($totaltmp->yesterdayopen)?$totaltmp->yesterdayopen:0;
$todayanswer = !empty($totaltmp->todayanswer)?$totaltmp->todayanswer:0;
$yesterdayanswer = !empty($totaltmp->yesterdayanswer)?$totaltmp->yesterdayanswer:0;
} // ENDIF: showgenstats

$PopUserCount = $kunena_config->popusercount;
if ($kunena_config->showpopuserstats)
{
	$kunena_db->setQuery("SELECT p.userid, p.posts, u.id, u.{$fb_queryName} AS username FROM #__fb_users AS p INNER JOIN #__users AS u ON u.id = p.userid WHERE p.posts > '0' AND u.block=0 ORDER BY p.posts DESC", 0, $PopUserCount);
	$topposters = $kunena_db->loadObjectList();

	$topmessage = !empty($topposters[0]->posts)?$topposters[0]->posts:0;

if ($kunena_config->fb_profile == "jomsocial") {
		$kunena_db->setQuery("SELECT u.id AS user_id, c.view AS hits, u.{$fb_queryName} AS user FROM #__community_users as c"
		. " LEFT JOIN #__users as u on u.id=c.userid "
		. " WHERE c.view>'0' ORDER BY c.view DESC", 0, $PopUserCount);
	}
	elseif ($kunena_config->fb_profile == "cb") {
		$kunena_db->setQuery("SELECT c.hits AS hits, u.id AS user_id, u.{$fb_queryName} AS user FROM #__comprofiler AS c"
		. " INNER JOIN #__users AS u ON u.id = c.user_id"
		. " WHERE c.hits>'0' ORDER BY c.hits DESC", 0, $PopUserCount);
	}
	elseif ($kunena_config->fb_profile == "aup") {
		$kunena_db->setQuery("SELECT a.profileviews AS hits, u.id AS user_id, u.{$fb_queryName} AS user FROM #__alpha_userpoints AS a"
		. " INNER JOIN #__users AS u ON u.id = a.userid"
		. " WHERE u.profileviews>'0' ORDER BY u.profileviews DESC", 0, $PopUserCount);
	}
	else {
		$kunena_db->setQuery("SELECT u.uhits AS hits, u.userid AS user_id, j.id, j.{$fb_queryName} AS user FROM #__fb_users AS u"
		. " INNER JOIN #__users AS j ON j.id = u.userid"
		. " WHERE u.uhits>'0' AND j.block=0 ORDER BY u.uhits DESC", 0, $PopUserCount);
	}
	$topprofiles = $kunena_db->loadObjectList();

	$topprofil = !empty($topprofiles[0]->hits)?$topprofiles[0]->hits:0;
} // ENDIF: showpopuserstats

$PopSubjectCount = $kunena_config->popsubjectcount;
if ($kunena_config->showpopsubjectstats)
{
	$kunena_session =& CKunenaSession::getInstance();
	$kunena_db->setQuery("SELECT * FROM #__fb_messages WHERE moved='0' AND hold='0' AND parent='0' AND catid IN ($kunena_session->allowed) ORDER BY hits DESC", 0, $PopSubjectCount);
	$toptitles = $kunena_db->loadObjectList();

	$toptitlehits = !empty($toptitles[0]->hits)?$toptitles[0]->hits:0;
} // ENDIF: showpopsubjectstats

} // ENDIF: showstats
?>

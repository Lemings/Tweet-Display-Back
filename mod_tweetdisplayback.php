<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die;

// Check if cURL is loaded; if not, proceed no further
if (!extension_loaded('curl')) {
	echo JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOCURL');
	return;
}
		
// Include the helper
require_once dirname(__FILE__).DS.'helper.php';

// Check the number of hits available if the cache is disabled or expired;
// If there are 0 hits remaining, then proceed no further
//TODO: Check if the cache is expired
if (($params->get('cache')) == 0) {
	$hits = modTweetDisplayBackHelper::getLimit($params);
	if ($hits == 0) {
		echo JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOHITS');
		return;
	}
}

//Initialize the cache
jimport('joomla.cache.cache');
$conf = JFactory::getConfig();
$options = array(
	'defaultgroup' => 'mod_tweetdisplayback',
	'cachebase' => $conf->get('config.cache_path'),
	'lifetime' => $params->get('cache_time') * 60, // minutes to seconds
	'language' => $conf->get('config.language'),
	'storage' => 'file' );
$cache = JCache::getInstance("callback", $options );
$cache->setCaching($params->get("cache"));

// Call the cache; if expired, pull new data
$twitter = $cache->call(array('modTweetDisplayBackHelper', 'getTweets'), $params);
if (!$twitter) {
	echo JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD');
	return;
}

$layout = $params->get("templateLayout", "default");
require(JModuleHelper::getLayoutPath('mod_tweetdisplayback', $layout));

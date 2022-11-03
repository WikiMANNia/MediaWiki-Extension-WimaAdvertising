<?php
/**
 * An extension for WimaAdvertising for
 * Mediawiki until version 1.24
 *
 * PHP Version 5
 *
 * @category  Extension
 * @package   WimaAdvertising
 * @author    WikiMANNia <chef@wikimannia.org>
 * @copyright WikiMANNia
 * @license   
 * @version   GIT: 1.0
 * @link      https://www.mediawiki.org/wiki/Extension:WimaAdvertising
 */

/**
 * The main file of the WimaAdvertising extension
 *
 * This file is part of the MediaWiki extension WimaAdvertising.
 * The WimaAdvertising extension is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The WimaAdvertising extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a License, this software is not free..
 *
 * @file
 */

###
# This is the path to your installation of Semantic Forms as
# seen on your local filesystem. Used against some PHP file path
# issues.
##
$dtgIP = dirname( __FILE__ );
$dir = __DIR__ . '/';
##

call_user_func(
	function () {
		if ( function_exists( 'wfLoadExtension' ) ) {
			wfLoadExtension( 'WimaAdvertising' );
			// Keep i18n globals so mergeMessageFileList.php doesn't break
			$wgMessagesDirs['WimaAdvertising'] = "$dtgIP/i18n";
			wfWarn(
			   'Deprecated PHP entry point used for WimaAdvertising extension. ' .
			   'Please use wfLoadExtension instead, ' .
			   'see https://www.mediawiki.org/wiki/Extension_registration ' .
			   'for more details.'
			);
			return;
		}
	}
);

$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'WimaAdvertising',
	'version'        => '2.2.0',
	'author'         => 'WikiMANNia',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:WimaAdvertising',
	'descriptionmsg' => 'wimaadvertising-desc',
);

$wgResourceModules['ext.wimaadvertising.common'] = array(
	'styles' => 'resources/css/Common.css',
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'WimaAdvertising',
	'dependencies' => array(
		'jquery.makeCollapsible',
	),
);
$wgResourceModules['ext.wimaadvertising.cologneblue'] = array(
	'styles' => 'resources/css/Cologneblue.css',
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'WimaAdvertising',
	'dependencies' => array(
		'jquery.makeCollapsible',
	),
);
$wgResourceModules['ext.wimaadvertising.mobile'] = array(
	'styles' => 'resources/css/Mobile.css',
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'WimaAdvertising',
	'dependencies' => array(
		'jquery.makeCollapsible',
	),
);
$wgResourceModules['ext.wimaadvertising.minerva'] = array(
	'styles' => 'resources/css/Minerva.css',
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'WimaAdvertising',
	'dependencies' => array(
		'jquery.makeCollapsible',
	),
);
$wgResourceModules['ext.wimaadvertising.modern'] = array(
	'styles' => 'resources/css/Modern.css',
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'WimaAdvertising',
	'dependencies' => array(
		'jquery.makeCollapsible',
	),
);
$wgResourceModules['ext.wimaadvertising.monobook'] = array(
	'styles' => 'resources/css/Monobook.css',
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'WimaAdvertising',
	'dependencies' => array(
		'jquery.makeCollapsible',
	),
);
$wgResourceModules['ext.wimaadvertising.timeless'] = array(
	'styles' => 'resources/css/Timeless.css',
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'WimaAdvertising',
	'dependencies' => array(
		'jquery.makeCollapsible',
	),
);
$wgResourceModules['ext.wimaadvertising.vector'] = array(
	'styles' => 'resources/css/Vector.css',
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'WimaAdvertising',
	'dependencies' => array(
		'jquery.makeCollapsible',
	),
);

// register all special pages and other classes
$wgMessagesDirs['WimaAdvertising'] = "$dtgIP/i18n";
$wgExtensionMessagesFiles['WimaAdvertisingAliases'] = $dir . 'WimaAdvertising.alias.php';
$wgAutoloadClasses['CustomAdvertisingSettings'] = "$dtgIP/includes/CustomAdvertisingSettings.php";
$wgAutoloadClasses['GoogleAdvertisingSettings'] = "$dtgIP/includes/GoogleAdvertisingSettings.php";
$wgAutoloadClasses['WimaAdvertisingHooks'] = "$dtgIP/includes/specials/HooksWimaAdvertising.php";
$wgHooks['BeforePageDisplay'][] = 'WimaAdvertisingHooks::onBeforePageDisplay';
# Since the "SidebarBeforeOutput" hook was provided with REL1_25,
# the hook "SkinBuildSidebar" is used as a workaround instead:
$wgHooks['SkinBuildSidebar'][] = 'WimaAdvertisingHooks::onSidebarBeforeOutput';
$wgHooks['SiteNoticeAfter'][] = 'WimaAdvertisingHooks::onSiteNoticeAfter';
$wgHooks['SkinAfterContent'][] = 'WimaAdvertisingHooks::onSkinAfterContent';

###
# This is the path to your installation of the Data Transfer extension as
# seen from the web. Change it if required ($wgScriptPath is the
# path to the base directory of your wiki). No final slash.
##
$dtgScriptPath = $wgScriptPath . '/extensions/WimaAdvertising';
##

// Global settings
$wgBannerBottomCode = "";
$wgBannerBottomStyle = "border:1px solid black; text-align:center;";
$wgBannerBottomType = "advertising";
$wgBannerTopCode = "";
$wgBannerTopStyle = "border:1px solid black; text-align:center;";
$wgBannerTopType = "advertising";
$wgSidebarAd1Code = "";
$wgSidebarAd1Type = "advertising";
$wgSidebarAd2Code = "";
$wgSidebarAd2Type = "advertising";
$wgWimaAdvertisingAnonOnly = false;
$wgWimaGoogleAdSenseClient = "none";
$wgWimaGoogleAdSenseSlot = "none";
$wgWimaGoogleAdSenseSrc = "//pagead2.googlesyndication.com/pagead/show_ads.js";
$wgWimaGoogleAdSenseID = "none";
$wgWimaGoogleAdSenseAnonOnly = false;
###


$wgExtensionFunctions[] = 'setupWimaAdvertisingExtension';

function setupWimaAdvertisingExtension() {
	global $wgDisableWimaAdvertising, $wgVersion;

	if ( version_compare( $wgVersion, '1.23', '<' ) ) {
		die( 'This extension requires MediaWiki 1.23+' );
	}
	elseif ( $wgDisableWimaAdvertising === false ) {
		global $wgAvailableRights, $wgGroupPermissions, $wgLogTypes, $wgLogActionsHandlers;
	}

	return true;
}

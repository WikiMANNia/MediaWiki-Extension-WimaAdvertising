<?php
/**
 * Hooks for WimaAdvertising extension
 *
 * @file
 * @ingroup Extensions
 */

use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\SiteNoticeAfterHook;
use MediaWiki\Hook\SkinAfterContentHook;
use MediaWiki\Skins\Hook\SkinAfterPortletHook;
use MediaWiki\Hook\SidebarBeforeOutputHook;

/**
 * @phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
 */
class WimaAdvertisingHooks implements
	BeforePageDisplayHook,
	SiteNoticeAfterHook,
	SkinAfterContentHook,
	SkinAfterPortletHook,
	SidebarBeforeOutputHook
{
	/**
	 * https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * 
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {

		if ( !self::isActive( $skin->getUser() ) )  return;

		$skinname = $skin->getSkinName();
		$out->addModuleStyles( 'ext.wimaadvertising.common' );
		$out->addModuleStyles( 'ext.wimaadvertising.mobile' );
		if ( CustomAdvertisingSettings::isSupportedSkin( $skinname ) ) {
			if ( $skinname === 'vector-2022' ) {
				$out->addModuleStyles( 'ext.wimaadvertising.vector' );
			} else {
				$out->addModuleStyles( 'ext.wimaadvertising.' . $skinname );
			}
		} else if ( $skinname !== 'fallback' ) {
			wfLogWarning( 'Skin ' . $skinname . ' not supported by WimaAdvertising.' . "\n" );
		}

		$script_code = GoogleAdvertisingSettings::getJavaCode();
		if ( !empty( $script_code ) ) {
			$out->addHeadItem( 'google_ads_javacode', $script_code );
		}
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SiteNoticeAfter
	 * 
	 * @param $siteNotice: String with HTML for sitenotice.
	 * @param $skin: Skin object MW 1.18+
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSiteNoticeAfter( &$siteNotice, $skin ) {

		$issetSitenoticeBox    = !empty( $siteNotice );
		$issetAdvertisementBox =  self::isPresentAd( $skin->getUser(), 'top' );

		if ( $issetSitenoticeBox && $issetAdvertisementBox ) {
			if ( rand(0, 1) ) {
#				$siteNotice = self::getSitenoticeBox();
			} else {
				$siteNotice = self::getAdvertisementBox( 'top',  $skin );
			}
		} elseif ( $issetSitenoticeBox ) {
#			$siteNotice = self::getSitenoticeBox();
		} elseif ( $issetAdvertisementBox ) {
			$siteNotice = self::getAdvertisementBox( 'top',  $skin );
		}
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SkinAfterContent
	 * Use this hook to add text after the page content and
	 * article metadata. This hook should work in all skins. Set the &$data variable to
	 * the text you're going to add.
	 *
	 * @param string &$data Text to be printed out directly (without parsing)
	 * @param Skin $skin
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSkinAfterContent( &$data, $skin ) {

		if ( self::isPresentAd( $skin->getUser(), 'bottom' ) ) {
			$data .= self::getAdvertisementBox( 'bottom', $skin );
		}
		return true;
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SkinAfterPortlet
	 *
	 * This hook is called when generating portlets.
	 * It allows injecting custom HTML after the portlet.
	 *
	 * @param Skin $skin
	 * @param string $portletName
	 * @param string &$html
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSkinAfterPortlet( $skin, $portletName, &$html ) {

		$user = $skin->getUser();
		$_html1 = self::getAdCode( $user, 'side1' );
		$_html2 = self::getAdCode( $user, 'side2' );
		$_key1 = 'wimaadvertising-' . self::getAdType( $user, 'side1' );
		$_key2 = 'wimaadvertising-' . self::getAdType( $user, 'side2' );
		// Dirty hack:
		$_key2 .= '2';

		/*
		 * Sonderbehandlung gemäß Skin.
		 */
		switch ( $skin->getSkinName() ) {
			case 'cologneblue' :
			case 'vector' :
				$_html1 = self::getAdBox( $_html1 );
				$_html2 = self::getAdBox( $_html2 );
			break;
		}

		$sidebar_elements[$_key1] = $_html1;
		$sidebar_elements[$_key2] = $_html2;

		if ( array_key_exists( $portletName, $sidebar_elements ) ) {
			$element = $sidebar_elements[$portletName];
			if ( !empty( $element ) ) {
				$html = $element;
				return true;
			}
		}
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SidebarBeforeOutput
	 *
	 * @param Skin $skin
	 * @param array &$sidebar Sidebar content. Modify $sidebar to add or modify sidebar portlets.
	 * @return void This hook must not abort; it must not return value.
	 */
	public function onSidebarBeforeOutput( $skin, &$sidebar ): void {

		$user = $skin->getUser();
		$_key1 = 'wimaadvertising-' . self::getAdType( $user, 'side1' );
		$_key2 = 'wimaadvertising-' . self::getAdType( $user, 'side2' );
		// Dirty hack:
		$_key2 .= '2';
		$empty_item = [
			'text'   => '',
			'id'     => 'n-advertising',
			'active' => true
		];

		if ( !self::isActive( $user ) ) {
			// Wenn die Erweiterung deaktiviert wurde, bleibt als Aufgabe, die
			// Marker 'AD1' und 'AD2' in der Sidebar zu deaktivieren:
			$sidebar['AD1'] = false;
			$sidebar['AD2'] = false;

			return;
		}

		// Keys 'AD1' und 'AD2' umbenennen
		$_modified = false;
		$newbar = [];
		foreach ( $sidebar as $key => $value ) {
			switch ( $key ) {
				case 'AD1' :
					if ( self::isPresentAd( $user, 'side1' ) ) {
						// Dirty hack for skin Timeless
						if ( $skin->getSkinName() === 'timeless' ) {
							$value = [ $empty_item ];
						}
						$newbar[$_key1] = $value;
					}
					$_modified = true;
				break;
				case 'AD2' :
					if ( self::isPresentAd( $user, 'side2' ) ) {
						// Dirty hack for skin Timeless
						if ( $skin->getSkinName() === 'timeless' ) {
							$value = [ $empty_item ];
						}
						$newbar[$_key2] = $value;
					}
					$_modified = true;
				break;
				default:
					$newbar[$key] = $value;
				break;
			}
		}
		if ( $_modified ) {
			$sidebar = $newbar;
		}
	}

	/**
	 * Load sidebar ad for Monaco skin.
	 *
	 * @return bool
	 */
	public static function onMonacoStaticboxEnd( $skin, &$html ) {

		$user = RequestContext::getMain()->getUser();
		$tag  = 'side1';
		$adCode = self::getAdCode( $user, $tag );
		if ( !empty( $adCode ) ) {
			$adKey = 'wimaadvertising-' . self::getAdType( $user, $tag );
			$adTitle = wfMessage( $adKey )->text();
			$html .= "<p>$adTitle</p>";
			$html .= self::getAdBox( $adCode );
		}

		return true;
	}

	/**
	 * Load sidebar ad for Monaco skin.
	 *
	 * @return bool
	 */
	public static function onMonacoSidebarEnd( $skin, &$html ) {

		$user = RequestContext::getMain()->getUser();
		$tag  = 'side2';
		$adCode = self::getAdCode( $user, $tag );
		if ( !empty( $adCode ) ) {
			$adKey = 'wimaadvertising-' . self::getAdType( $user, $tag );
			$adTitle = wfMessage( $adKey )->text();
			$html .= "<p>$adTitle</p>";
			$html .= self::getAdBox( $adCode );
		}

		return true;
	}

	private static function getAdBox( $html ) {
		return Html::rawElement( 'div', [ 'class' => 'wima-adbox' ], $html );
	}

	private static function getAdvertisementBox( $type, $skin ) {

		$user = $skin->getUser();

		if ( self::isPresentAd( $user, $type ) ) {
			$style1 = 'clear:both; margin-top:1em; text-align:left;';
			$style2 = self::getAdStyle( $type );
			$title  = self::getAdType( $user, $type );
			$title  = $skin->msg( 'wimaadvertising-' . $title )->text();
			$options['style'] = $style1;
			if ( $type === 'bottom' ) {
				$options['id'] = 'siteNotice';
			}
			$html_code = self::getAdCode( $user, $type );

			return Html::rawElement( 'div', $options,
						Html::rawElement( 'p', [], $title . ':' ).
						Html::rawElement( 'div', [ 'style' => $style2 ], $html_code )
					);
		}
		return '';
	}

	private static function isActive( $user ) {
		return ( CustomAdvertisingSettings::isActive( $user ) ||
			// If custom ad is not active, give google a chance
			GoogleAdvertisingSettings::isActive( $user ) );
	}

	private static function isPresentAd( $user, $type ) {

		$present_ad_found = false;

		if ( CustomAdvertisingSettings::isActive( $user ) ) {
			// Defined ad should be only used, if custom ad is activated
			$present_ad_found = CustomAdvertisingSettings::isPresentAd( $type );
		}
		if ( !$present_ad_found && GoogleAdvertisingSettings::isActive( $user ) ) {
			// If custom ad is not defined or not activated, give google a chance
			$present_ad_found = GoogleAdvertisingSettings::isPresentAd( $type );
		}
		return $present_ad_found;
	}

	private static function getAdCode( $user, $type ) {

		$return_value = '';
		$present_ad_found = false;

		if ( CustomAdvertisingSettings::isActive( $user ) ) {
			// Defined ad should be only used, if custom ad is activated
			$present_ad_found = CustomAdvertisingSettings::isPresentAd( $type );
			if ( $present_ad_found ) {
				$return_value = CustomAdvertisingSettings::getAdCode( $type );
			}
		}
		if ( !$present_ad_found && GoogleAdvertisingSettings::isActive( $user ) ) {
			// If custom ad is not defined or not activated, give google a chance
			$present_ad_found = GoogleAdvertisingSettings::isPresentAd( $type );
			if ( $present_ad_found ) {
				$return_value = GoogleAdvertisingSettings::getAdCode( $type );
			}
		}
		return $return_value;
	}

	private static function getAdStyle( $type ) {
		return CustomAdvertisingSettings::getAdStyle( $type );
	}

	private static function getAdType( $user, $type ) {

		$return_value = '';
		$present_ad_found = false;

		if ( CustomAdvertisingSettings::isActive( $user ) ) {
			$present_ad_found = CustomAdvertisingSettings::isPresentAd( $type );
			if ( $present_ad_found ) {
				$return_value = CustomAdvertisingSettings::getAdType( $type );
			}
		}
		if ( !$present_ad_found && GoogleAdvertisingSettings::isActive( $user ) ) {
			// If custom ad is not defined or not activated, give google a chance
			$present_ad_found = GoogleAdvertisingSettings::isPresentAd( $type );
			if ( $present_ad_found ) {
				$return_value = GoogleAdvertisingSettings::getAdType( $type );
			}
		}
		return $return_value;
	}
}

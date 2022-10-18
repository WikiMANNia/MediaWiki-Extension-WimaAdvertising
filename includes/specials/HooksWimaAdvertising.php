<?php

class WimaAdvertisingHooks extends Hooks {

	/**
	 * Hook: BeforePageDisplay
	 * @param OutputPage $out
	 * @param Skin $skin
	 * https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {

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
	}

	/**
	 * Hook: SiteNoticeAfter
	 * @param $siteNotice: String with HTML for sitenotice.
	 * @param $skin: Skin object MW 1.18+
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SiteNoticeAfter
	 */
	public static function onSiteNoticeAfter( &$html, $skin ) {

		$issetSitenoticeBox    = !empty( $html );
		$issetAdvertisementBox =  self::isPresentAd( $skin->getUser(), 'top' );

		if ( $issetSitenoticeBox && $issetAdvertisementBox ) {
			if ( rand(0, 1) ) {
#				$html = self::getSitenoticeBox();
			} else {
				$html = self::getAdvertisementBox( 'top',  $skin );
			}
		} elseif ( $issetSitenoticeBox ) {
#			$html = self::getSitenoticeBox();
		} elseif ( $issetAdvertisementBox ) {
			$html = self::getAdvertisementBox( 'top',  $skin );
		}
	}

	/**
	 * Use this hook to add text after the page content and
	 * article metadata. This hook should work in all skins. Set the &$data variable to
	 * the text you're going to add.
	 *
	 * @param string &$data Text to be printed out directly (without parsing)
	 * @param Skin $skin
	 * @return bool|void True or no return value to continue or false to abort
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SkinAfterContent
	 */
	public static function onSkinAfterContent( &$data, $skin ) {

		if ( self::isPresentAd( $skin->getUser(), 'bottom' ) ) {
			$data .= self::getAdvertisementBox( 'bottom', $skin );
		}
		return true;
	}

	/**
	 * Hook: SidebarBeforeOutput
	 * @since 1.25
	 * @param Skin $skin: Skin object
	 * @param array &$bar: Sidebar contents
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SidebarBeforeOutput
	 */
	public static function onSidebarBeforeOutput(
		Skin $skin,
		array &$bar
	) {

		$user = $skin->getUser();

		if ( !self::isActive( $user ) ) {
			// Wenn die Erweiterung deaktiviert wurde, bleibt als Aufgabe, die
			// Marker 'AD1' und 'AD2' in der Sidebar zu deaktivieren:
			$bar['AD1'] = false;
			$bar['AD2'] = false;

			return;
		}

		$_key1 = 'wimaadvertising-' . self::getAdType( $user, 'side1' );
		$_key2 = 'wimaadvertising-' . self::getAdType( $user, 'side2' );
		// Dirty hack:
		$_key2 .= '2';

		/*
		 * Sonderbehandlung gemäß Skin.
		 */
		$_html1 = self::getAdCode( $user, 'side1' );
		$_html2 = self::getAdCode( $user, 'side2' );

		switch ( $skin->getSkinName() ) {
			case 'cologneblue' :
			case 'vector' :
				$_html1 = self::getAdBox( $_html1 );
				$_html2 = self::getAdBox( $_html2 );
			break;
			case 'modern' :
			case 'monobook' :
			break;
		}

		// Keys 'AD1' und 'AD2' umbenennen
		$_modified = false;
		$newbar = [];
		foreach ( $bar as $key => $value ) {
			switch ( $key ) {
				case 'AD1' :
					if ( self::isPresentAd( $user, 'side1' ) ) {
						$newbar[$_key1] = $_html1;
					}
					$_modified = true;
				break;
				case 'AD2' :
					if ( self::isPresentAd( $user, 'side2' ) ) {
						$newbar[$_key2] = $_html2;
					}
					$_modified = true;
				break;
				default:
					$newbar[$key] = $value;
				break;
			}
		}
		if ( $_modified ) {
			$bar = $newbar;
		}
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

			return Html::rawElement( 'div', $options,
						Html::rawElement( 'p', [], $title . ':' ).
						Html::rawElement( 'div', [ 'style' => $style2 ], self::getAdCode( $user, $type )
					)
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

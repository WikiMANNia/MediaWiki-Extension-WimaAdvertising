<?php

/**
 * Settings is a singleton - used as a store of values for a particular site.
 *
 * This is a generic container, that can be used by other extensions to
 * store values other than ones that came from the database and/or were set
 * by the user - for instance, to store the amount of file space taken by
 * files uploaded for this wiki.
 */

class CustomAdvertisingSettings {

	private static $instance;

 	private $mActive;
 	private $mAnonOnly;

 	private $mDefaultType;
 	private $mStyleArray;
 	private $mTypeArray;

 	private $mCodeArray;

	private function __construct() {

		/**
		 * Global variables are set in 'extension.json' and
		 * also can be set in the 'LocalSettings.php'.
		 */

		// 1. Steuerung
		global $wgWimaAdvertising;
		global $wgWimaAdvertisingAnonOnly;

		$this->mActive   = empty( $wgWimaAdvertising         ) ? false : ( ( $wgWimaAdvertising         === true ) || ( $wgWimaAdvertising         === 'true' ) );
		$this->mAnonOnly = empty( $wgWimaAdvertisingAnonOnly ) ? false : ( ( $wgWimaAdvertisingAnonOnly === true ) || ( $wgWimaAdvertisingAnonOnly === 'true' ) );


		// 2. Spezifische Variablen für jeden Werbeblock
		global $wgBannerBottomStyle, $wgBannerBottomType;
		global $wgBannerTopStyle,    $wgBannerTopType;
		global $wgSidebarAd1Style,   $wgSidebarAd1Type;
		global $wgSidebarAd2Style,   $wgSidebarAd2Type;

		$this->mDefaultType = 'advertising';
		$this->mStyleArray['bottom'] = empty( $wgBannerBottomStyle ) ? '' : $wgBannerBottomStyle;
		$this->mStyleArray['top']    = empty( $wgBannerTopStyle    ) ? '' : $wgBannerTopStyle;
		$this->mStyleArray['side1']  = empty( $wgSidebarAd1Style   ) ? '' : $wgSidebarAd1Style;
		$this->mStyleArray['side2']  = empty( $wgSidebarAd2Style   ) ? '' : $wgSidebarAd2Style;
		$this->mTypeArray['bottom'] = empty( $wgBannerBottomType ) ? $this->mDefaultType : $wgBannerBottomType;
		$this->mTypeArray['top']    = empty( $wgBannerTopType    ) ? $this->mDefaultType : $wgBannerTopType;
		$this->mTypeArray['side1']  = empty( $wgSidebarAd1Type   ) ? $this->mDefaultType : $wgSidebarAd1Type;
		$this->mTypeArray['side2']  = empty( $wgSidebarAd2Type   ) ? $this->mDefaultType : $wgSidebarAd2Type;


		// HTML-Snippet für jeden Werbeblock, falls jedoch ungültige Parameter auf false setzen
		global $wgSidebarAd1Code,   $wgBannerBottomCode;
		global $wgSidebarAd2Code,   $wgBannerTopCode;

		$this->mCodeArray['bottom'] = empty( $wgBannerBottomCode ) ? false : $wgBannerBottomCode;
		$this->mCodeArray['top']    = empty( $wgBannerTopCode    ) ? false : $wgBannerTopCode;
		$this->mCodeArray['side1']  = empty( $wgSidebarAd1Code   ) ? false : $wgSidebarAd1Code;
		$this->mCodeArray['side2']  = empty( $wgSidebarAd2Code   ) ? false : $wgSidebarAd2Code;
	}

	private function __clone() { }

	/**
	 * @return self
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			// Erstelle eine neue Instanz, falls noch keine vorhanden ist.
			self::$instance = new self();
		}

		// Liefere immer die selbe Instanz.
		return self::$instance;
	}

	/**
	 * $type: string
	 * return: string
	 */
	public static function getAdCode( $key ) {

		$_array = self::getInstance()->mCodeArray;
		$_return_value = '';

		if ( array_key_exists( $key, $_array ) ) {
			$_return_value = $_array[ $key ];
		} else {
			wfLogWarning( 'Custom::getAdCode was called for an unsupported key: "' . $key . '"' . "\n" );
		}
		return $_return_value;
	}

	/**
	 * $type: string
	 * return: bool
	 */
	public static function isPresentAd( $key ) {

		$_array = self::getInstance()->mCodeArray;
		$_return_value = false;

		if ( array_key_exists( $key, $_array ) ) {
			$_return_value = ( $_array[ $key ] !== false );
		} else {
			wfLogWarning( 'Custom::isPresentAd was called for an unsupported key: "' . $key . '"' . "\n" );
		}
		return $_return_value;
	}

	/**
	 * bool $user_LoggedIn
	 * return: bool
	 */
	public static function isActive( $user ) {

 		if ( self::getInstance()->mActive ) {
			return ( $user->isAnon() || !self::getInstance()->mAnonOnly );
 		}
		return false;
	}

	/**
	 * $type: string
	 * return: string
	 */
	public static function getAdStyle( $key ) {

		$_array = self::getInstance()->mStyleArray;
		$_return_value = '';

		if ( array_key_exists( $key, $_array ) ) {
			$_return_value = $_array[ $key ];
		} else {
			wfLogWarning( 'Custom::getAdStyle was called for an unsupported key: "' . $key . '"' . "\n" );
		}

		return $_return_value;
	}

	/**
	 * $type: string
	 * return: string
	 */
	public static function getAdType( $key ) {

		$_array        = self::getInstance()->mTypeArray;
		$_return_value = self::getInstance()->mDefaultType;

		if ( array_key_exists( $key, $_array ) ) {
			$_return_value = $_array[ $key ];
		} else {
			wfLogWarning( 'Custom::getAdType was called for an unsupported key: "' . $key . '"' . "\n" );
		}

		return $_return_value;
	}

	/**
	 * $skinname: string
	 * return: bool
	 */
	public static function isSupportedSkin( $key ) {
		return in_array( $key, [ 'cologneblue', 'minerva', 'modern', 'monobook', 'timeless', 'vector', 'vector-2022' ] );
	}
}
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
		global $wmWimaAdvertising;
		global $wmWimaAdvertisingAnonOnly;

		$this->mActive   = empty( $wmWimaAdvertising         ) ? false : ( ( $wmWimaAdvertising         === true ) || ( $wmWimaAdvertising         === 'true' ) );
		$this->mAnonOnly = empty( $wmWimaAdvertisingAnonOnly ) ? false : ( ( $wmWimaAdvertisingAnonOnly === true ) || ( $wmWimaAdvertisingAnonOnly === 'true' ) );


		// 2. Spezifische Variablen für jeden Werbeblock
		global $wmBannerBottomStyle, $wmBannerBottomType;
		global $wmBannerTopStyle,    $wmBannerTopType;
		global $wmSidebarAd1Style,   $wmSidebarAd1Type;
		global $wmSidebarAd2Style,   $wmSidebarAd2Type;

		$this->mDefaultType = 'advertising';
		$this->mStyleArray['bottom'] = empty( $wmBannerBottomStyle ) ? '' : $wmBannerBottomStyle;
		$this->mStyleArray['top']    = empty( $wmBannerTopStyle    ) ? '' : $wmBannerTopStyle;
		$this->mStyleArray['side1']  = empty( $wmSidebarAd1Style   ) ? '' : $wmSidebarAd1Style;
		$this->mStyleArray['side2']  = empty( $wmSidebarAd2Style   ) ? '' : $wmSidebarAd2Style;
		$this->mTypeArray['bottom'] = empty( $wmBannerBottomType ) ? $this->mDefaultType : $wmBannerBottomType;
		$this->mTypeArray['top']    = empty( $wmBannerTopType    ) ? $this->mDefaultType : $wmBannerTopType;
		$this->mTypeArray['side1']  = empty( $wmSidebarAd1Type   ) ? $this->mDefaultType : $wmSidebarAd1Type;
		$this->mTypeArray['side2']  = empty( $wmSidebarAd2Type   ) ? $this->mDefaultType : $wmSidebarAd2Type;


		// HTML-Snippet für jeden Werbeblock, falls jedoch ungültige Parameter auf false setzen
		global $wmSidebarAd1Code, $wmBannerBottomCode;
		global $wmSidebarAd2Code, $wmBannerTopCode;

		$this->mCodeArray['bottom'] = empty( $wmBannerBottomCode ) ? false : $wmBannerBottomCode;
		$this->mCodeArray['top']    = empty( $wmBannerTopCode    ) ? false : $wmBannerTopCode;
		$this->mCodeArray['side1']  = empty( $wmSidebarAd1Code   ) ? false : $wmSidebarAd1Code;
		$this->mCodeArray['side2']  = empty( $wmSidebarAd2Code   ) ? false : $wmSidebarAd2Code;
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
	 * @param string $key
	 * @return string
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
	 * @param bool $user_LoggedIn
	 * @return bool
	 */
	public static function isActive( $user ) {

 		if ( self::getInstance()->mActive ) {
			return ( $user->isAnon() || !self::getInstance()->mAnonOnly );
 		}
		return false;
	}

	/**
	 * @param string $key
	 * @return bool
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
	 * @param string $key
	 * @return string
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
	 * @param string $key
	 * @return string
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
	 * $param string $key (skinname)
	 * @return bool
	 */
	public static function isSupportedSkin( $key ) {
		return in_array( $key, [ 'cologneblue', 'minerva', 'modern', 'monobook', 'timeless', 'vector', 'vector-2022' ] );
	}
}
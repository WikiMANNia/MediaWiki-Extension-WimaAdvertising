<?php
/**
 * Settings is a singleton - used as a store of values for a particular site.
 *
 * This is a generic container, that can be used by other extensions to
 * store values other than ones that came from the database and/or were set
 * by the user - for instance, to store the amount of file space taken by
 * files uploaded for this wiki.
 */

use MediaWiki\MediaWikiServices;

class CustomAdvertisingSettings {

	private static $instance;

 	private bool $mActive;
 	private bool $mAnonOnly;

 	private string $mDefaultType;
 	private array $mStyleArray = [];
 	private array $mTypeArray = [];

 	private $mCodeArray;

	private function __construct() {

		/**
		 * Global variables are set in 'extension.json' and
		 * also can be set in the 'LocalSettings.php'.
		 */

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'wimaadvertising' );

		// 1. Steuerung
		$_WimaAdvertising         = $config->get( 'WimaAdvertising' );
		$_WimaAdvertisingAnonOnly = $config->get( 'WimaAdvertisingAnonOnly' );

		$this->mActive   = empty( $_WimaAdvertising         ) ? false : ( $_WimaAdvertising         === true );
		$this->mAnonOnly = empty( $_WimaAdvertisingAnonOnly ) ? false : ( $_WimaAdvertisingAnonOnly === true );


		// 2. Spezifische Variablen für jeden Werbeblock
		$_BannerBottomStyle = $config->get( 'BannerBottomStyle' );
		$_BannerTopStyle    = $config->get( 'BannerTopStyle' );
		$_SidebarAd1Style   = $config->get( 'SidebarAd1Style' );
		$_SidebarAd2Style   = $config->get( 'SidebarAd2Style' );
		$_BannerBottomType  = $config->get( 'BannerBottomType' );
		$_BannerTopType     = $config->get( 'BannerTopType' );
		$_SidebarAd1Type    = $config->get( 'SidebarAd1Type' );
		$_SidebarAd2Type    = $config->get( 'SidebarAd2Type' );

		$this->mStyleArray['bottom'] = empty( $_BannerBottomStyle ) ? '' : $_BannerBottomStyle;
		$this->mStyleArray['top']    = empty( $_BannerTopStyle    ) ? '' : $_BannerTopStyle;
		$this->mStyleArray['side1']  = empty( $_SidebarAd1Style   ) ? '' : $_SidebarAd1Style;
		$this->mStyleArray['side2']  = empty( $_SidebarAd2Style   ) ? '' : $_SidebarAd2Style;
		$this->mDefaultType = 'advertising';
		$validTypes = [ 'blank', 'eventnote', 'hint', $this->mDefaultType ];
		$this->mTypeArray['bottom'] = in_array( $_BannerBottomType, $validTypes ) ? $_BannerBottomType : $this->mDefaultType;
		$this->mTypeArray['top']    = in_array( $_BannerTopType,    $validTypes ) ? $_BannerTopType    : $this->mDefaultType;
		$this->mTypeArray['side1']  = in_array( $_SidebarAd1Type,   $validTypes ) ? $_SidebarAd1Type   : $this->mDefaultType;
		$this->mTypeArray['side2']  = in_array( $_SidebarAd2Type,   $validTypes ) ? $_SidebarAd2Type   : $this->mDefaultType;


		// HTML-Snippet für jeden Werbeblock, falls jedoch ungültige Parameter auf false setzen
		$_BannerBottomCode = $config->get( 'BannerBottomCode' );
		$_BannerTopCode    = $config->get( 'BannerTopCode' );
		$_SidebarAd1Code   = $config->get( 'SidebarAd1Code' );
		$_SidebarAd2Code   = $config->get( 'SidebarAd2Code' );

		$this->mCodeArray['bottom'] = empty( $_BannerBottomCode ) ? false : $_BannerBottomCode;
		$this->mCodeArray['top']    = empty( $_BannerTopCode    ) ? false : $_BannerTopCode;
		$this->mCodeArray['side1']  = empty( $_SidebarAd1Code   ) ? false : $_SidebarAd1Code;
		$this->mCodeArray['side2']  = empty( $_SidebarAd2Code   ) ? false : $_SidebarAd2Code;
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

	public static function getAdCode( string $key ): string {

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
	 */
	public static function isActive( User $user ): bool {

 		if ( self::getInstance()->mActive ) {
			return ( $user->isAnon() || !self::getInstance()->mAnonOnly );
 		}
		return false;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public static function isPresentAd( string $key ): bool  {

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
		return in_array( $key, [ 'citizen', 'cologneblue', 'minerva', 'modern', 'monaco', 'monobook', 'timeless', 'vector', 'vector-2022' ] );
	}
}
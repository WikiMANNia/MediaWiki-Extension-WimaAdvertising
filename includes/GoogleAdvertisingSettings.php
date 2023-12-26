<?php

/**
 * Settings is a singleton - used as a store of values for a particular site.
 *
 * This is a generic container, that can be used by other extensions to
 * store values other than ones that came from the database and/or were set
 * by the user - for instance, to store the amount of file space taken by
 * files uploaded for this wiki.
 */

class GoogleAdvertisingSettings {

	private static $instance;

 	private $mActive;
 	private $mAnonOnly;

 	private $mDefaultType;
 	private $mConfigArray;

 	private $mCodeArray;

	private function __construct() {

		/**
		 * Global variables are set in 'extension.json' and
		 * also can be set in the 'LocalSettings.php'.
		 */
		global $wgLanguageCode;

		// 1. Steuerung
		global $wmGoogleAdSense;
		global $wmGoogleAdSenseAnonOnly;

		$this->mActive   = empty( $wmGoogleAdSense         ) ? false : ( ( $wmGoogleAdSense         === true ) || ( $wmGoogleAdSense         === 'true' ) );
		$this->mAnonOnly = empty( $wmGoogleAdSenseAnonOnly ) ? false : ( ( $wmGoogleAdSenseAnonOnly === true ) || ( $wmGoogleAdSenseAnonOnly === 'true' ) );


		// 2. Spezifische Variablen für jeden Werbeblock
		global $wmGoogleAdSense_Bottom;
		global $wmGoogleAdSense_Top;
		global $wmGoogleAdSense_AD1;
		global $wmGoogleAdSense_AD2;

		$this->mDefaultType = 'advertising';
		$Ad_Bottom = self::getAdConfigArray( $wmGoogleAdSense_Bottom );
		$Ad_Top    = self::getAdConfigArray( $wmGoogleAdSense_Top );
		$Ad_AD1    = self::getAdConfigArray( $wmGoogleAdSense_AD1 );
		$Ad_AD2    = self::getAdConfigArray( $wmGoogleAdSense_AD2 );


		// 3. Allgemeine Variablen für alle Werbeblöcke
		global $wmGoogleAdSenseClient;
		global $wmGoogleAdSenseSrc;
		global $wmGoogleAdSenseID;
		global $wmGoogleAdSenseEncoding;
		global $wmGoogleAdSenseLanguage;

		$this->mConfigArray['ad_client']   = ( empty( $wmGoogleAdSenseClient ) || ( $wmGoogleAdSenseClient === 'none' ) ) ? false : $wmGoogleAdSenseClient;
		$this->mConfigArray['ad_src']      = !empty( $wmGoogleAdSenseSrc      ) ? $wmGoogleAdSenseSrc      : false;
		$this->mConfigArray['ad_clientId'] = !empty( $wmGoogleAdSenseID       ) ? $wmGoogleAdSenseID       : 'ID 007';
		$this->mConfigArray['ad_encoding'] = !empty( $wmGoogleAdSenseEncoding ) ? $wmGoogleAdSenseEncoding : 'utf8';
		$this->mConfigArray['ad_language'] = !empty( $wmGoogleAdSenseLanguage ) ? $wmGoogleAdSenseLanguage : $wgLanguageCode;


		// HTML-Snippet für jeden Werbeblock, falls ungültige Parameter auftreten sollten, auf false setzen
		$this->mCodeArray['bottom'] = false;
		$this->mCodeArray['top']    = false;
		$this->mCodeArray['side1']  = false;
		$this->mCodeArray['side2']  = false;

		// HTML-Snippet für jeden Werbeblock erstellen, sofern keine ungültigen Parameter vorhanden
		if ( ( $this->mConfigArray['ad_src'] !== false ) &&
			 ( $this->mConfigArray['ad_client'] !== false ) ) {

			if ( $Ad_Bottom !== false ) {
				$this->mCodeArray['bottom'] = self::getAdCodePrivate( $this->mConfigArray, $Ad_Bottom );
			}
			if ( $Ad_Top !== false ) {
				$this->mCodeArray['top']    = self::getAdCodePrivate( $this->mConfigArray, $Ad_Top );
			}
			if ( $Ad_AD1 !== false ) {
				$this->mCodeArray['side1']  = self::getAdCodePrivate( $this->mConfigArray, $Ad_AD1 );
			}
			if ( $Ad_AD2 !== false ) {
				$this->mCodeArray['side2']  = self::getAdCodePrivate( $this->mConfigArray, $Ad_AD2 );
			}
		}
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
			wfLogWarning( 'Google::getAdCode was called for an unsupported key: "' . $key . '"' . "\n" );
		}

		return $_return_value;
	}

	/**
	 * return: string|false
	 */
	public static function getJavaCode() {

		$general_data = self::getInstance()->mConfigArray;
		$javacode_date = $general_data['ad_src'];
		$script_code = false;

		if ( self::getInstance()->mActive && !empty( $javacode_date ) ) {
			$script_pattern = '<script type="text/javascript" src="%1$s">
</script>';
			$script_code = sprintf( $script_pattern, $javacode_date );
		}

		return $script_code;
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
			wfLogWarning( 'Google::isPresentAd was called for an unsupported key: "' . $key . '"' . "\n" );
		}

		return $_return_value;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	private static function getAdStyle( $type ) {
		return '';
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public static function getAdType( $type ) {

		return self::getInstance()->mDefaultType;
	}

	/**
	 * @param int|string $value
	 * @return int|'auto'|false
	 */
	private static function getSizeValue( $value ) {

		if ( empty( $value ) )  return false;

		if ( is_int( $value ) ) {
			return intval( $value );
		}

		if ( $value === 'auto' ) {
			return 'auto';
		}

		return false;
	}

	/**
	 * @param array $array
	 * @return false|array
	 */
	private static function getAdConfigArray( $array ) {

		if ( is_array( $array ) ) {
			if ( count( $array ) !== 3 ) {
				wfLogWarning( 'Google::getAdConfigArray expected an array with three values, but got this: "' . implode( ', ', $array ) . '"' . "\n" );
				return false;
			}
		} else {
			// Because this is obviously not an array, the variable is very probably not set. This is NOT an error!
			return false;
		}

		// Slot must be defined, not empty or 'none'
		$slot = empty( $array[0] ) ? 'none' : $array[0];
		if ( ( $slot === 'none' ) || ( $slot === 'slot' ) ) {
			wfLogWarning( 'Google::getAdConfigArray did not detect a (valid) slot: "' . $slot . '"' . "\n" );
			return false;
		}

		// width and height must be 'auto' or int
		$width  = self::getSizeValue( $array[1] );
		if ( $width === false ) {
			wfLogWarning( 'Google::getAdConfigArray not recognize a valid width value: "' . $array[1] . '"' . "\n" );
			return false;
		}
		$height = self::getSizeValue( $array[2] );
		if ( $height === false ) {
			wfLogWarning( 'Google::getAdConfigArray not recognize a valid height value: "' . $array[2] . '"' . "\n" );
			return false;
		}

		return [ 'slot' => $slot, 'width' => $width, 'height' => $height ];
	}

	/**
	 * @param array $general_data
	 * @param array $ad_data
	 * @return string
	 */
	private static function getAdCodePrivate( $general_data, $ad_data ) {
		$script_pattern = '<script type="text/javascript"><!--
google_ad_client = "%1$s";
/* %2$s */
google_ad_slot = "%3$s";
google_ad_width = %4$d;
google_ad_height = %5$d;
google_language = "%6$s";
google_encoding = "%7$s";
// -->
</script>';
		$script_code = sprintf( $script_pattern,
				$general_data['ad_client'],
				$general_data['ad_clientId'],
				$ad_data['slot'],
				$ad_data['width'],
				$ad_data['height'],
				$general_data['ad_language'],
				$general_data['ad_encoding']
			);

		return $script_code;
	}
}
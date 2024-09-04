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

		$this->mActive   = empty( $wmGoogleAdSense         ) ? false : ( $wmGoogleAdSense         === true );
		$this->mAnonOnly = empty( $wmGoogleAdSenseAnonOnly ) ? false : ( $wmGoogleAdSenseAnonOnly === true );


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
		global $wmGoogleAdSenseHost;
		global $wmGoogleAdSenseMode;
		global $wmGoogleAdSenseSrc;

		$this->mConfigArray['ad_client'] = ( empty( $wmGoogleAdSenseClient ) || ( $wmGoogleAdSenseClient === 'none' ) ) ? false : $wmGoogleAdSenseClient;
		$this->mConfigArray['ad_host']   = ( empty( $wmGoogleAdSenseHost )   || ( $wmGoogleAdSenseHost === 'none' )   ) ? false : $wmGoogleAdSenseHost;
		$this->mConfigArray['ad_mode']   = ( $wmGoogleAdSenseMode === 'responsive' ) ? 'responsive' : 'normal';
		$this->mConfigArray['ad_src']    = !empty( $wmGoogleAdSenseSrc ) ? $wmGoogleAdSenseSrc : false;


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
			$script_pattern = '<script type="text/javascript" async src="%1$s" crossorigin="anonymous">
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
			if ( ( count( $array ) !== 3 ) && ( count( $array ) !== 4 ) ) {
				wfLogWarning( 'Google::getAdConfigArray expected an array with three or four values, but got this: "' . implode( ', ', $array ) . '"' . "\n" );
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

		// Slot can be empty or 'auto', 'horizontal', 'vertical', 'rectangle'
		$format = empty( $array[3] ) ? '' : $array[3];

		return [ 'slot' => $slot, 'width' => $width, 'height' => $height, 'format' => $format ];
	}

	/**
	 * @param array $general_data
	 * @param array $ad_data
	 * @return string
	 */
	private static function getAdCodePrivate( $general_data, $ad_data ) {

		$script_code = '';
		$scriptsnippet_client_host_slot =
			empty( $general_data['ad_host'] )
			? '
    data-ad-client="ca-pub-%1$s"
    data-ad-slot="%3$s"'
			: '
    data-ad-client="ca-pub-%1$s"
    data-ad-host="ca-host-pub-%2$s"
    data-ad-slot="%3$s"';
		$scriptsnippet_format =
			empty( $ad_data['format'] )
			? ''
			: '
    data-ad-format="%6$s"';

		if ( $general_data['ad_mode'] === 'responsive' ) {
			$script_pattern = '<ins class="adsbygoogle"
    style="display:block;"' . $scriptsnippet_client_host_slot . '
    data-full-width-responsive="true"
    data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>';
			$script_code = sprintf( $script_pattern,
					$general_data['ad_client'],
					$general_data['ad_host'],
					$ad_data['slot']
				);
		} else {
			$script_pattern = '<ins class="adsbygoogle"
    style="display:inline-block;width:%4$dpx;height:%5$dpx"' .
    	$scriptsnippet_client_host_slot .
    	$scriptsnippet_format . '></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>';
			$script_code = sprintf( $script_pattern,
					$general_data['ad_client'],
					$general_data['ad_host'],
					$ad_data['slot'],
					$ad_data['width'],
					$ad_data['height'],
					$ad_data['format']
				);
		}

		return $script_code;
	}
}
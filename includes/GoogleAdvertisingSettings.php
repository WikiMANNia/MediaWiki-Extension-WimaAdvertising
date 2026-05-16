<?php
/**
 * Settings is a singleton - used as a store of values for a particular site.
 *
 * This is a generic container, that can be used by other extensions to
 * store values other than ones that came from the database and/or were set
 * by the user - for instance, to store the amount of file space taken by
 * files uploaded for this wiki.
 */

namespace MediaWiki\Extension\WimaAdvertising;

use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

class GoogleAdvertisingSettings {

	private static $instance;

 	private bool $mActive;
 	private bool $mAnonOnly;

 	private string $mDefaultType = 'advertising';
 	private string $mScriptPattern = '';
 	private array $mConfigArray = [];

 	private array $mCodeArray = [];

	private function __construct() {

		/**
		 * Global variables are set in 'extension.json' and
		 * also can be set in the 'LocalSettings.php'.
		 */
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'wimaadvertising' );

		// 1. Steuerung
		$this->mActive   = $config->get( 'GoogleAdSense' );
		$this->mAnonOnly = $config->get( 'GoogleAdSenseAnonOnly' );


		// 2. Spezifische Variablen für jeden Werbeblock
		$Ad_Bottom = self::getAdConfigArray( $config->get( 'GoogleAdSense_Bottom' ) );
		$Ad_Top    = self::getAdConfigArray( $config->get( 'GoogleAdSense_Top' ) );
		$Ad_AD1    = self::getAdConfigArray( $config->get( 'GoogleAdSense_AD1' ) );
		$Ad_AD2    = self::getAdConfigArray( $config->get( 'GoogleAdSense_AD2' ) );


		// 3. Allgemeine Variablen für alle Werbeblöcke
		$_GoogleAdSenseClient = $config->get( 'GoogleAdSenseClient' );
		$_GoogleAdSenseHost   = $config->get( 'GoogleAdSenseHost' );
		$_GoogleAdSenseMode   = $config->get( 'GoogleAdSenseMode' );
		$_GoogleAdSenseSrc    = $config->get( 'GoogleAdSenseSrc' );

		$this->mConfigArray['ad_client'] = ( empty( $_GoogleAdSenseClient ) || ( $_GoogleAdSenseClient === 'none' ) ) ? false : $_GoogleAdSenseClient;
		$this->mConfigArray['ad_host']   = ( empty( $_GoogleAdSenseHost )   || ( $_GoogleAdSenseHost === 'none' )   ) ? false : $_GoogleAdSenseHost;
		$this->mConfigArray['ad_mode']   = ( $_GoogleAdSenseMode === 'responsive' ) ? 'responsive' : 'normal';
		$this->mConfigArray['ad_src']    = !empty( $_GoogleAdSenseSrc ) ? $_GoogleAdSenseSrc : false;


		// HTML-Snippet für jeden Werbeblock, falls ungültige Parameter auftreten sollten, auf false setzen
		$this->mCodeArray['bottom'] = false;
		$this->mCodeArray['top']    = false;
		$this->mCodeArray['side1']  = false;
		$this->mCodeArray['side2']  = false;

		// HTML-Snippet für jeden Werbeblock erstellen, sofern keine ungültigen Parameter vorhanden
		if ( ( $this->mConfigArray['ad_src'] !== false ) &&
			 ( $this->mConfigArray['ad_client'] !== false ) ) {

			// 4. Script Pattern
			$this->mScriptPattern = '<script type="text/javascript" async src="%1$s?client=ca-pub-%2$s';
			if ( !empty( $general_data['ad_host'] ) ) {
				$this->mScriptPattern .= '&host=ca-host-pub-%3$s';
			}
			$this->mScriptPattern .= '" crossorigin="anonymous">
</script>';

			// 5. HTML-Snippets
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
	public static function getAdCode( string $key ): string {

		$_array = self::getInstance()->mCodeArray;
		$_return_value = '';

		if ( array_key_exists( $key, $_array ) ) {
			$_return_value = $_array[ $key ];
		} else {
			wfLogWarning( "Google::getAdCode was called for an unsupported key: $key \n" );
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
			$script_code = sprintf(
					self::getInstance()->mScriptPattern,
					$javacode_date,
					$general_data['ad_client'],
					$general_data['ad_host']
				);
		}

		return $script_code;
	}

	public static function isActive( User $user ): bool {

 		if ( self::getInstance()->mActive ) {
			return ( $user->isAnon() || !self::getInstance()->mAnonOnly );
 		}
		return false;
	}

	public static function isPresentAd( string $key ): bool {

		$_array = self::getInstance()->mCodeArray;
		$_return_value = false;

		if ( array_key_exists( $key, $_array ) ) {
			$_return_value = ( $_array[ $key ] !== false );
		} else {
			wfLogWarning( "Google::isPresentAd was called for an unsupported key: $key \n" );
		}

		return $_return_value;
	}

	private static function getAdStyle( string $type ): string {
		return '';
	}

	public static function getAdType( string $type ): string {

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
	 * @return false|array
	 */
	private static function getAdConfigArray( array $array ) {

		if ( is_array( $array ) ) {
			switch ( count( $array ) ) {
				case 0:
					// The array is empty.  This is NOT an error!
					return false;
					break;
				case 3:
				case 4: // the element #4 is optional
					break;
				default:
					wfLogWarning( 'Google::getAdConfigArray expected an array with three or four values, but got this: "' . implode( ', ', $array ) . '"' . "\n" );
					return false;
					break;
			}
		} else {
			// Because this is obviously not an array, the variable is very probably not set. This is NOT an error!
			return false;
		}

		// Slot must be defined, not empty or 'none'
		$slot = empty( $array[0] ) ? 'none' : $array[0];
		if ( ( $slot === 'none' ) || ( $slot === 'slot' ) ) {
			wfLogWarning( "Google::getAdConfigArray did not detect a (valid) slot: $slot \n" );
			return false;
		}

		// width and height must be 'auto' or int
		$width  = self::getSizeValue( $array[1] );
		if ( $width === false ) {
			wfLogWarning( "Google::getAdConfigArray not recognize a valid width value: $array[1] \n" );
			return false;
		}
		$height = self::getSizeValue( $array[2] );
		if ( $height === false ) {
			wfLogWarning( "Google::getAdConfigArray not recognize a valid height value: $array[2] \n" );
			return false;
		}

		// Slot can be empty or 'auto', 'horizontal', 'vertical', 'rectangle'
		$format = empty( $array[3] ) ? '' : $array[3];

		return [ 'slot' => $slot, 'width' => $width, 'height' => $height, 'format' => $format ];
	}

	private static function getAdCodePrivate( array $general_data, array $ad_data ): string {

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
<?php

namespace MediaWiki\Extension\WimaAdvertising;

class Compat {

    public static function init(): void {
        self::aliasCoreClasses();
    }

    private static function aliasCoreClasses(): void {

		// Class aliases for multi-version compatibility.
		// These need to be in global scope so phan can pick up on them,
		// and before any use statements that make use of the namespaced names.

		if ( class_exists( \Html::class ) && /* < 1.40 */
			!class_exists( \MediaWiki\Html\Html::class, false ) ) {
			class_alias(
				\Html::class,
				\MediaWiki\Html\Html::class );
		}
		if ( class_exists( \User::class ) && /* < 1.41 */
			!class_exists( \MediaWiki\User\User::class, false ) ) {
			class_alias(
				\User::class,
				\MediaWiki\User\User::class );
		}
		if ( class_exists( \SkinTemplate::class ) && /* < 1.44 */
			!class_exists( \MediaWiki\Skin\SkinTemplate::class, false ) ) {
			class_alias(
				\SkinTemplate::class,
				\MediaWiki\Skin\SkinTemplate::class );
		}
    }
}
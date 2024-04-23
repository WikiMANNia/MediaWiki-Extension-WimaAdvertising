# MediaWiki Wima-Advertising

Die Pflege der MediaWiki-Erweiterung [WimaAdvertising](https://www.mediawiki.org/wiki/Extension:WimaAdvertising/de) wird von WikiMANNia verwaltet.

The maintenance of the MediaWiki extension [WimaAdvertising](https://www.mediawiki.org/wiki/Extension:WimaAdvertising) is managed by WikiMANNia.

El mantenimiento de la extensión de MediaWiki [WimaAdvertising](https://www.mediawiki.org/wiki/Extension:WimaAdvertising/es) está gestionado por WikiMANNia.

## Description

Adds four possible advertising spaces for the Skins [Cologne Blue](https://www.mediawiki.org/wiki/Skin:Cologne_Blue), [Modern](https://www.mediawiki.org/wiki/Skin:Modern), [MonoBook](https://www.mediawiki.org/wiki/Skin:MonoBook), [Minerva](https://www.mediawiki.org/wiki/Skin:Minerva), [Timeless](https://www.mediawiki.org/wiki/Skin:Timeless), and [Vector/Vector-2022](https://www.mediawiki.org/wiki/Skin:Vector) that are filled by [LocalSettings.php](https://www.mediawiki.org/wiki/Manual:LocalSettings.php).

The advertising space 1 alternates randomly with Sitenotice. Advertising space 2 is located at the bottom of the article content. The advertising spaces 3 and 4 are located in the Sidebar. The exact positioning is determined with the entries `* AD1` and `* AD2` in the [Sidebar](https://www.mediawiki.org/wiki/MediaWiki:Sidebar).

## Custom Advertising

Enable advertising. Default is `false`.
* `$wmWimaAdvertising = true;`

Disable advertising for logged-in users. Default is `false`.
* `$wmWimaAdvertisingAnonOnly = true;`

The slots can be labelled as Advert, Eventnote, Hint or let be blank. These variables have to be set:

* `$wmBannerTopType = 'advertising';`
* `$wmBannerBottomType = 'blank';`
* `$wmSidebarAd1Type = 'eventnote';`
* `$wmSidebarAd2Type = 'hint';`

The default value is `advertising`. These variables can therefore be omitted for advertising insertions.

HTML code must be assigned to these variables:

* `$wmBannerTopCode = '';`
* `$wmBannerBottomCode = '';`
* `$wmSidebarAd1Code = '';`
* `$wmSidebarAd2Code = '';`

If a variable is not set or contains its string of zero length, the corresponding ad space remains unoccupied.

These variables must be assigned CSS style specifications, for example:

* `$wmBannerTopStyle = 'text-align:center;border:1px solid blue;';`
* `$wmBannerBottomStyle = 'text-align:center;border:1px dotted red;';`

The advertising space 1 alternates randomly with Sitenotice. Advertising space 2 is located at the bottom of the article content. The advertising spaces 3 and 4 are located in the sitenotice. The exact positioning is determined with the entries `* AD1` and `* AD2` in the [Sidebar](https://www.mediawiki.org/wiki/MediaWiki:Sidebar).

Enable advertising. Default is `false`.

* `$WimaAdvertising = true;`

Disable advertising for logged-in users. Default is `false`.

* `$wmWimaAdvertisingAnonOnly = true;`

The extension is localized for the languages "de", "en", "es", "fr", "it", "nl", "pt", and "ru".

Currently, this extension supports the skins Cologne Blue, Modern, MonoBook and Vector.
Further skins may require additional adjustments, which would have to be made in `resources/css/myskin.css`.

## Google AdSense

Enable advertising. Default is `false`.
* `$wmGoogleAdSense = true;`

Disable advertising for logged-in users. Default is `false`.
* `$wmGoogleAdSenseAnonOnly = true;`

### Mandatory parameters
Replace this with your own publisher ID (google_ad_client / data-ad-client)
* `$wmGoogleAdSenseClient = 'none';` // Client ID for your AdSense script (example: ca-pub-1234546403419693)
(You can get your publisher ID and ad unit ID from the "Get code" page: Get and copy the ad code.)

### Ad units
Define up to four ad units:
* `$wmGoogleAdSense_AD1= [ 'slotid 1', 145, 260 ];`
* `$wmGoogleAdSense_AD2= [ 'slotid 2', 145, 260 ];`
* `$wmGoogleAdSense_Top= [ 'slotid 3', 145, 260 ];`
* `$wmGoogleAdSense_Bottom = [ 'slotid 4', 145, 260 ];`
Replace the first value with your AdSense ad unit ID (google_ad_slot / data-ad-slot) for each ad unit. The Slot ID for your AdSense script is for example `1234580893`.

Also specify the width and the height of the AdSense unit, specified in your AdSense account (google_ad_width / data-ad-width, google_ad_height / data-ad-height). Values such as 'auto', '100%', '60%' etc. are accepted.

### Optional parameters
Add any of the optional settings below – if your settings deviate from the defaults:

This can be anything you like. Default is `none`.
* `$wmGoogleAdSenseID = 'none';`

Source URL of the AdSense script. No need to change – it can't deviate from the defaults.
* `$wmGoogleAdSenseSrc = '//pagead2.googlesyndication.com/pagead/show_ads.js';`

Text coding. Default is `utf8`.
* `$wmGoogleAdSenseEncoding = 'utf8';`

Advertising language. Default is `$wgLanguageCode`.
* `$wmGoogleAdSenseLanguage = 'en';`

## Compatibility

This extension works from REL1_35 and has been tested up to MediaWiki version `1.35.14`, `1.39.7` and `1.41.1`.

## Version history

1.0.0

- First public release

2.0.0

- GoogleAdSense functionality added.

2.0.1

- Support added for REL1_37 and for 'mobile'.
- Avoid warning: Use of `$skin->getUser()->isLoggedIn()` instead of `!$skin->getUser()->isAnon()`.

2.0.2

- Support for skin 'minerva' added.

2.0.3

- Support for REL1_23 added.

2.1.0

- Support for skin 'vector-2022' added.

2.2.0

- Support for skin 'timeless' and 'fallback' added.

2.3.0

- Support for REL1_35+ added.

2.4.0

- Changed "configuration schema", replaced manifest version 1 with version 2 and changed the prefix of the configuration variables from default to `wm`.
- Replaced class “WimaAdvertisingHooks” extending class “Hooks” with class implementing interfaces.

2.4.1

- Dirty hack for skin [Timeless](https://www.mediawiki.org/wiki/Skin:Timeless).

2.5.0

- Place JavaScript for GoogleAdSense in `<head>` section.

2.6.0

- The label of Wima slots can be set as `blank`.

2.7.0

- Support added for Skin [Monaco](https://www.mediawiki.org/wiki/Skin:Monaco).

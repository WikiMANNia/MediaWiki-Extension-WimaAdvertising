# MediaWiki Wima-Advertising

Die Pflege der MediaWiki-Erweiterung [WimaAdvertising](https://www.mediawiki.org/wiki/Extension:WimaAdvertising) wird von WikiMANNia verwaltet.

The maintenance of the MediaWiki extension [WimaAdvertising](https://www.mediawiki.org/wiki/Extension:WimaAdvertising) is managed by WikiMANNia.

El mantenimiento de la extensión de MediaWiki [WimaAdvertising](https://www.mediawiki.org/wiki/Extension:WimaAdvertising) está gestionado por WikiMANNia.

## Description

Adds four possible advertising spaces for the Skins [Cologne Blue](https://www.mediawiki.org/wiki/Skin:Cologne_Blue), [Modern](https://www.mediawiki.org/wiki/Skin:Modern), [MonoBook](https://www.mediawiki.org/wiki/Skin:MonoBook), and [Vector](https://www.mediawiki.org/wiki/Skin:Vector) that are filled by LocalSettings.php.

The advertising space 1 alternates randomly with Sitenotice. Advertising space 2 is located at the bottom of the article content. The advertising spaces 3 and 4 are located in the Sidebar. The exact positioning is determined with the entries “*AD1” and “*AD2” in the “MediaWiki:Sidebar”.

## Installation

Enable advertising. Default is false.
* $wgWimaAdvertising = true;

Disable advertising for logged-in users. Default is false.
* $wgWimaAdvertisingAnonOnly = true;

Two advertising spaces can also be used as event information. These variables have to be set:

* $wgBannerTopType = 'advertising';
* $wgBannerBottomType = 'advertising';
* $wgSidebarAd1Type = 'eventnote';
* $wgSidebarAd2Type = 'hint';

The default value is “advertising”. These variables can therefore be omitted for advertising insertions.

HTML code must be assigned to these variables:

* $wgBannerTopCode = '';
* $wgBannerBottomCode = '';
* $wgSidebarAd1Code = '';
* $wgSidebarAd2Code = '';

If a variable is not set or contains its string of zero length, the corresponding ad space remains unoccupied.

These variables must be assigned CSS style specifications, for example:

* $wgBannerTopStyle = 'text-align:center;border:1px solid blue;';
* $wgBannerBottomStyle = 'text-align:center;border:1px dotted red;';

The advertising space 1 alternates randomly with Sitenotice. Advertising space 2 is located at the bottom of the article content. The advertising spaces 3 and 4 are located in the sitenotice. The exact positioning is determined with the entries “*AD1” and “*AD2” in the “MediaWiki:Sidebar”.

Enable advertising. Default is false.

* $WimaAdvertising = true;

Disable advertising for logged-in users. Default is false.

* $wgWimaAdvertisingAnonOnly = true;

The extension is localized for the languages "de", "en", "es", "fr", "it", "nl", "pt" and "ru".

Currently, this extension supports the skins Cologne Blue, Modern, MonoBook and Vector.
Further skins may require additional adjustments, which would have to be made in "resources/css/myskin.css".

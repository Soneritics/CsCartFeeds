# CsCartFeeds
Data feeds for Cs Cart.
This data feed plugin is not as flexible as many others, but it is way more easy to use.
This plugin lets you add predefined feeds in predefined formats.
Adding an extra feed requires coding.

The following feeds have been implemented:
* [Google Shopping (RSS 2.0)](https://support.google.com/merchants/answer/160589) 
* Daisycon
* Beslist


## Shipment cost per product
Most feeds require shipment cost. The default shipment cost cannot be used.
To set the feed shipment cost, add the following product features:
* Shipment cost {COUNTRY_CODE}

_COUNTRY_CODE_ is the 2 character code of the country. RU, EN, NL, DE, etc.
Use _Other_ > _Text_ as the feature type.


## Using the Google Data Feed
The Google data feed requires some extra information.
You must add these as product features.

To use the Google feed, add the following features (as text features)
and use the description exactly listed below (case insensitive):
* gtin
* mpn
* google product category
* google product type

Either the _gtin_ or _mpn_ field must be filled to have valid product feed data.

Optional, you can add the feature _condition_.
If you choose not to use this, all products will be given the _new_ condition.
If you also want to include _used_ items,this feature is mandatory.

## Using the Beslist Data Feed
The Beslist data feed requires some extra information.
You must add these as product features.

To use the Beslist feed, add the following features (as text features)
and use the description exactly listed below (case insensitive):
* ean
* beslist category

## Screenshots
Data feeds overview
![Data feed overview](.README/screenshot-01.png "Data feeds overview")

Data feed settings
![Data feed settings](.README/screenshot-02.png "Data feeds settings")

Data feed integration in products pages
![Data feeds product page integration](.README/screenshot-03.png "Data feeds product page integration")


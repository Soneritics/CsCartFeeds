# CsCartFeeds
Data feeds for Cs Cart

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
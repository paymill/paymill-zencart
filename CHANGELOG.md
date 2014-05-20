#Release Notes
## 1.5.0
* add payment logos
* merge elv / sepa form
* add pre notification to email and invoice

##1.4.0
* Added Language Support for german, english, french, italian, spanish and portuguese
* Added improved early pan detection
* Added iban validation

##1.3.0
* Added SEPA Payment Form for german Direct Debit
* Added WebHooks. WebHooks will automatically synch your shops order states on refund or chargeback events
* Updated Fast Checkout
* Removed Paymill Label
* Added version number to payment configuration
* Fixed Log view
* Added improved feedback on errors for both bridge and api errors

##1.2.2
- update fast checkout

##1.2.1
- Added Improved Error Feedback
- Added Changelog
- Added prefixes to the modules tables. This will only affect reinstalled modules
- Fixed a bug causing crashes on reinstalling of the plugin in some cases

##1.2.0
- Changed the german display name of the payment mean direct debit to 'ELV' from 'Elektronisches Lastschriftverfahren'
- Added special handling for maestro credit cards without a CVC
- Fixed a bug causing the shop version not to be communicated correctly
- Added unset for the session identifier to ensure it's null after a payment has been processed or an error has occurred
- Updated readme file

##1.1.0
- Redesigned log

##1.0.0
- Initial release
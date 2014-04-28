<?php
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_PUBLIC_TITLE", "D&eacute;bit direct");
define("MODULE_PAYMENT_PAYMILL_ELV_STATUS_TITLE", "Activer");
define("MODULE_PAYMENT_PAYMILL_ELV_DESCRIPTION", "Journal PAYMILL");
define("MODULE_PAYMENT_PAYMILL_ELV_TRANSACTION_ORDER_STATUS_ID_TITLE", "Etat de l'ordre d'op&eacute;ration");
define("MODULE_PAYMENT_PAYMILL_ELV_TRANSACTION_ORDER_STATUS_ID_DESC", "Inclure les informations de l'op&eacute;ration &agrave; ce niveau du statut de l'ordre.");
define("MODULE_PAYMENT_PAYMILL_ELV_FASTCHECKOUT_TITLE", "Activer le paiement rapide.");
define("MODULE_PAYMENT_PAYMILL_ELV_FASTCHECKOUT_DESC", "Si ce mode est activ&eacute;, les donn&eacute;es de vos clients seront conserv&eacute;es par PAYMILL et remises &agrave; disposition pour de futurs achats. Le client n'aura &agrave; saisir ses donn&eacute;es qu'une seule fois. Cette solution est compatible PCI.");
define("MODULE_PAYMENT_PAYMILL_ELV_WEBHOOKS_TITLE", "Activer les Webhooks");
define("MODULE_PAYMENT_PAYMILL_ELV_WEBHOOKS_DESC", "Synchroniser automatiquement mes Remboursements &agrave; partir du Cockpit PAYMILL et mon magasin");
define("MODULE_PAYMENT_PAYMILL_ELV_WEBHOOKS_LINK", "Cr&eacute;er des Webhooks");
define("MODULE_PAYMENT_PAYMILL_ELV_WEBHOOKS_LINK_CREATE", "Cr&eacute;er des Webhooks");
define("MODULE_PAYMENT_PAYMILL_ELV_WEBHOOKS_LINK_REMOVE", "Supprimer les Webhooks");
define("MODULE_PAYMENT_PAYMILL_ELV_SORT_ORDER_TITLE", "S&eacute;quence");
define("MODULE_PAYMENT_PAYMILL_ELV_SORT_ORDER_DESC", "Position de l'affichage lors du paiement.");
define("MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY_TITLE", "Cl&eacute; priv&eacute;e");
define("MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY_DESC", "Vous trouverez votre cl&eacute; priv&eacute;e dans le cockpit PAYMILL.");
define("MODULE_PAYMENT_PAYMILL_ELV_PUBLICKEY_TITLE", "Cl&eacute; publique");
define("MODULE_PAYMENT_PAYMILL_ELV_PUBLICKEY_DESC", "Vous trouverez votre cl&eacute; publique dans le cockpit PAYMILL.");
define("MODULE_PAYMENT_PAYMILL_ELV_LOGGING_TITLE", "Activer la journalisation.");
define("MODULE_PAYMENT_PAYMILL_ELV_LOGGING_DESC", "Si ce mode est activ&eacute;, les informations concernant l'avancement du traitement de la commande seront &eacute;crites dans le journal.");
define("MODULE_PAYMENT_PAYMILL_ELV_ORDER_STATUS_ID_TITLE", "Etat de l'ordre d'op&eacute;ration");
define("MODULE_PAYMENT_PAYMILL_ELV_ORDER_STATUS_ID_DESC", "Inclure les informations de l'op&eacute;ration &agrave; ce niveau du statut de l'ordre.");
define("MODULE_PAYMENT_PAYMILL_ELV_ZONE_TITLE", "Zones autoris&eacute;es");
define("MODULE_PAYMENT_PAYMILL_ELV_ZONE_DESC", "Veuillez entrer individuellement les zones qui doivent &ecirc;tre autoris&eacute;es &agrave; utiliser ce module (par exemple, USA, GB (laisser un blanc pour autoriser toutes les zones))");
define("MODULE_PAYMENT_PAYMILL_ELV_ALLOWED_TITLE", "Pays accept&eacute;s");
define("MODULE_PAYMENT_PAYMILL_ELV_ALLOWED_DESC", "Si rien n'a &eacute;t&eacute; s&eacute;lectionn&eacute;, tous les pays seront accept&eacute;s");
define("MODULE_PAYMENT_PAYMILL_ELV_TRANS_ORDER_STATUS_ID_TITLE", "Etat de l'ordre d'op&eacute;ration");
define("MODULE_PAYMENT_PAYMILL_ELV_TRANS_ORDER_STATUS_ID_DESC", "Inclure les informations de l'op&eacute;ration &agrave; ce niveau du statut de l'ordre.");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_ACCOUNT", "Num&eacute;ro de compte");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_BANKCODE", "Code bancaire");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_ACCOUNT_HOLDER", "Titulaire du compte");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_ACCOUNT_HOLDER_INVALID", "Veuillez saisir le nom du titulaire du compte de d&eacute;bit direct");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_ACCOUNT_INVALID", "Veuillez saisir un num&eacute;ro de compte de d&eacute;bit direct valide");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_BANKCODE_INVALID", "Veuillez saisir un code bancaire de d&eacute;bit direct valide.");
define("MODULE_PAYMENT_PAYMILL_ELV_SEPA_TITLE", "Montrer le formulaire SEPA");
define("MODULE_PAYMENT_PAYMILL_ELV_SEPA_DESC", "Actuellement, seules les donn&eacute;es bancaires d'Allemagne sont trait&eacute;es");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_BIC", "BIC");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_IBAN", "IBAN");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_IBAN_INVALID", "Veuillez entrer un IBAN valide");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_BIC_INVALID", "Veuillez entrer un BIC valide.");
define("PAYMILL_10001", "General undefined response.");
define("PAYMILL_10002", "Still waiting on something.");
define("PAYMILL_20000", "General success response.");
define("PAYMILL_40000", "General problem with data.");
define("PAYMILL_40001", "General problem with payment data.");
define("PAYMILL_40100", "Problem with credit card data.");
define("PAYMILL_40101", "Problem with cvv.");
define("PAYMILL_40102", "Card expired or not yet valid.");
define("PAYMILL_40103", "Limit exceeded.");
define("PAYMILL_40104", "Card invalid.");
define("PAYMILL_40105", "Expiry date not valid.");
define("PAYMILL_40106", "Credit card brand required.");
define("PAYMILL_40200", "Problem with bank account data.");
define("PAYMILL_40201", "Bank account data combination mismatch.");
define("PAYMILL_40202", "User authentication failed.");
define("PAYMILL_40300", "Problem with 3d secure data.");
define("PAYMILL_40301", "Currency / amount mismatch");
define("PAYMILL_40400", "Problem with input data.");
define("PAYMILL_40401", "Amount too low or zero.");
define("PAYMILL_40402", "Usage field too long.");
define("PAYMILL_40403", "Currency not allowed.");
define("PAYMILL_50000", "General problem with backend.");
define("PAYMILL_50001", "Country blacklisted.");
define("PAYMILL_50100", "Technical error with credit card.");
define("PAYMILL_50101", "Error limit exceeded.");
define("PAYMILL_50102", "Card declined by authorization system.");
define("PAYMILL_50103", "Manipulation or stolen card.");
define("PAYMILL_50104", "Card restricted");
define("PAYMILL_50105", "Invalid card configuration data.");
define("PAYMILL_50200", "Technical error with bank account.");
define("PAYMILL_50201", "Card blacklisted.");
define("PAYMILL_50300", "Technical error with 3D secure.");
define("PAYMILL_50400", "Decline because of risk issues.");
define("PAYMILL_50500", "General timeout.");
define("PAYMILL_50501", "Timeout on side of the acquirer.");
define("PAYMILL_50502", "Risk management transaction timeout");
define("PAYMILL_50600", "Duplicate transaction.");
define("PAYMILL_INTERNAL_SERVER_ERROR", "The communication with the psp failed.");
define("PAYMILL_INVALID_PUBLIC_KEY", "The public key is invalid.");
define("PAYMILL_INVALID_PAYMENT_DATA", "Paymentmethod, card type currency or country not authorized");
define("PAYMILL_UNKNOWN_ERROR", "Unknown Error");
define("PAYMILL_FIELD_INVALID_AMOUNT_INT", "Missing amount for 3-D Secure");
define("PAYMILL_FIELD_INVALID_AMOUNT", "Missing amount for 3-D Secure");
define("PAYMILL_FIELD_INVALID_CURRENCY", "Invalid currency for 3-D Secure");
define("PAYMILL_FIELD_INVALID_ACCOUNT_NUMBER", "Invalid Account Number");
define("PAYMILL_FIELD_INVALID_ACCOUNT_HOLDER", "Invalid Account Holder");
define("PAYMILL_FIELD_INVALID_BANK_CODE", "Invalid bank code");
define("PAYMILL_FIELD_INVALID_IBAN", "Invalid IBAN");
define("PAYMILL_FIELD_INVALID_BIC", "Invalid BIC");
define("PAYMILL_FIELD_INVALID_COUNTRY", "Invalid country for sepa transactions");
define("PAYMILL_FIELD_INVALID_BANK_DATA", "Invalid bank data");
define("PAYMILL_0", "Une erreur s'est produit pendant le traiement de votre paiement.");
define("MODULE_PAYMENT_PAYMILL_ELV_TEXT_TITLE", "PAYMILL D&eacute;bit direct");
define("TEXT_INFO_API_VERSION", "API Version");
define("MODULE_PAYMENT_PAYMILL_ELV_STATUS_DESC", "");
define("SEPA_DRAWN_TEXT", "The direct debit is drawn to the following date: ");
?>
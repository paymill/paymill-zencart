<?php
global $db;
require_once ('includes/application_top.php');

$sql = "SELECT * FROM `". DB_PREFIX . "pi_paymill_logging` WHERE id = '" . zen_db_input($_GET['id']) . "'";
$logs = $db->Execute($sql);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
        <title><?php echo TITLE; ?></title>
        <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
        <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
        <script language="javascript" src="includes/menu.js"></script>
        <script language="javascript" src="includes/general.js"></script>
    </head>
    <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="init()">
        <div id="spiffycalendar" class="text"></div>
        <!-- header //-->
        <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
        <table border="0" width="100%" cellspacing="2" cellpadding="2">
            <tr>
                <td width="100%" valign="top">
                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr>
                            <td>
                                <table border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
                                    <tr>
                                        <td class="pageHeading">PAYMILL Log Entry</td>
                                    </tr>
                                    <tr>
                                        <td><img width="100%" height="1" border="0" alt="" src="images/pixel_black.gif"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <pre><?php echo $logs->fields['message']; ?><hr/><?php echo $logs->fields['debug']; ?></pre>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
        <br />
    </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
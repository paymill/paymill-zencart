<?php
global $db;
require_once('includes/application_top.php');
$logs = $db->Execute("SELECT * FROM `pi_paymill_logging`");
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
                            <td width="100%">
                                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="pageHeading">PAYMILL Log</td>
                                        <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr class="dataTableHeadingRow">
                                        <th class="dataTableHeadingContent">ID</th>
                                        <th class="dataTableHeadingContent">Debug</th>
                                        <th class="dataTableHeadingContent">Message</th>
                                        <th class="dataTableHeadingContent">Date</th>
                                    </tr>
                                    <?php while (!$logs->EOF): ?>
                                        <tr class="dataTableRow">
                                            <td class="dataTableContent"><?php echo $logs->fields['id']; ?></td>
                                            <td class="dataTableContent"><?php echo $logs->fields['debug']; ?></td>
                                            <td class="dataTableContent"><?php echo $logs->fields['message']; ?></td>
                                            <td class="dataTableContent"><?php echo $logs->fields['date']; ?></td>
                                        </tr>
                                        <?php $logs->MoveNext(); ?>
                                    <?php endwhile; ?>
                                </table>
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

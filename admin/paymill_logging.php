<?php
global $db;
require_once('includes/application_top.php');

$recordLimit = 10;
$page = $_GET['seite'];
if (!isset($_GET['seite'])) {
    $page = 1;
}

$start = $page * $recordLimit - $recordLimit;

$sql = "SELECT * FROM `pi_paymill_logging` LIMIT $start, $recordLimit";
if (isset($_POST['submit'])) {
    $sql = "SELECT * FROM `pi_paymill_logging` WHERE debug like '%" . zen_db_input($_POST['search_key']) . "%' LIMIT $start, $recordLimit";
}

$data = $db->Execute($sql);

$logs = array();

while (!$data->EOF) {
    array_push($logs, $data->fields);
    $data->MoveNext();
}

print_r(json_decode($logs[0]['debug'], true));exit;

$recordCount = count($logs);
$pageCount = $recordCount / $recordLimit;
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
                                <div>
                                    <b>Page: </b>
                                    <?php for ($a = 0; $a <= $pageCount; $a++) : ?>
                                        <?php $b = $a + 1; ?>
                                        <?php if ($page == $b) : ?>
                                            <b><?php echo $b; ?></b>
                                        <?php else : ?>
                                            <a href="<?php echo zen_href_link('paymill_logging.php'); ?>?seite=<?php echo $b; ?>"><?php echo $b; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <form action="<?php echo zen_href_link('paymill_logging.php'); ?>" method="POST">
                                    <input value="" name="search_key"/><input type="submit" value="Search..." name="submit"/>
                                </form>
                                <table>
                                    <tr class="dataTableHeadingRow">
                                        <th class="dataTableHeadingContent">ID</th>
                                        <th class="dataTableHeadingContent">Debug</th>
                                        <th class="dataTableHeadingContent">Date</th>
                                    </tr>
                                    
                                    <?php foreach ($logs as $log): ?>
                                    
                                        <tr class="dataTableRow">
                                            <td class="dataTableContent"><?php echo $log['id']; ?></td>
                                            <td class="dataTableContent">
                                                <table>
                                                    <tr class="dataTableHeadingRow">
                                                        <?php foreach (json_decode($log['debug'], true) as $key => $value): ?>
                                                            <th class="dataTableHeadingContent"><?php echo strtoupper(str_replace('_', ' ', $key)); ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                    <tr class="dataTableRow">
                                                        <?php foreach (json_decode($log['debug'], true) as $key => $value): ?>
                                                            <td class="dataTableContent">
                                                                <?php if (strlen($value['debug']) > 300): ?>
                                                                    <center><a href="<?php echo zen_href_link('paymill_log.php', 'id=' . $log['id'] . '&key=' . $key, 'SSL', true, false); ?>">See more</a></center>
                                                                <?php else: ?>
                                                                    <pre><?php echo $value['message']; ?><hr/><?php echo $value['debug']; ?></pre>
                                                                <?php endif; ?>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td class="dataTableContent"><?php echo $log['date']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                                <form action="<?php echo zen_href_link('paymill_logging.php'); ?>" method="POST">
                                    <input value="" name="search_key"/><input type="submit" value="Search..." name="submit"/>
                                </form>
                                <div>
                                    <b>Page: </b>
                                    <?php for ($a = 0; $a <= $pageCount; $a++) : ?>
                                        <?php $b = $a + 1; ?>
                                        <?php if ($page == $b) : ?>
                                            <b><?php echo $b; ?></b>
                                        <?php else : ?>
                                            <a href="<?php echo zen_href_link('paymill_logging.php'); ?>?seite=<?php echo $b; ?>"><?php echo $b; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
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

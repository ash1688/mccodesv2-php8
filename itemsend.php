<?php
declare(strict_types=1);
/**
 * MCCodes v2 by Dabomstew & ColdBlooded
 *
 * Repository: https://github.com/davemacaulay/mccodesv2
 * License: MIT License
 */

global $db, $ir, $userid, $h;
require_once('globals.php');
$_GET['ID']    =
    (isset($_GET['ID']) && is_numeric($_GET['ID']))
        ? abs(intval($_GET['ID'])) : '';
$_POST['user'] =
    (isset($_POST['user']) && is_numeric($_POST['user']))
        ? abs(intval($_POST['user'])) : '';
$_POST['qty']  =
    (isset($_POST['qty']) && is_numeric($_POST['qty']))
        ? abs(intval($_POST['qty'])) : '';
if (!empty($_POST['qty']) && !empty($_POST['user'])) {
    $r = $db->row(
        'SELECT inv_qty, inv_itemid, itmname, itmid
        FROM inventory AS iv
        INNER JOIN items AS it ON iv.inv_itemid = it.itmid
        WHERE iv.inv_id = ? AND iv.inv_userid = ?
        LIMIT 1',
        $_GET['ID'],
        $userid,
    );
    if (empty($r)) {
        echo 'Invalid item ID';
    } else {
        $rm = $db->row(
            'SELECT lastip, username FROM users WHERE userid = ? LIMIT 1',
            $_POST['user'],
        );
        if (!isset($_POST['verf'])
            || !verify_csrf_code("senditem_{$_GET['ID']}",
                stripslashes($_POST['verf']))) {
            echo '<h3>Error</h3><hr />
   			This transaction has been blocked for your security.<br />
    		Please send items quickly after you open the form - do not leave it open in tabs.<br />
    		&gt; <a href="itemsend.php?ID=' . $_GET['ID'] . '">Try Again</a>';
            $h->endpage();
            exit;
        } elseif ($_POST['qty'] > $r['inv_qty']) {
            echo 'You are trying to send more than you have!';
        } elseif (empty($rm)) {
            echo 'You are trying to send to an invalid user!';
        } else {
            $save = function () use ($db, $userid, $ir, $r, $rm) {
                item_remove($userid, $r['inv_itemid'], $_POST['qty']);
                item_add($_POST['user'], $r['inv_itemid'], $_POST['qty']);
                event_add($_POST['user'],
                    "You received {$_POST['qty']} {$r['itmname']}(s) from <a href='viewuser.php?u=$userid'>{$ir['username']}</a>");
                $db->insert(
                    'itemxferlogs',
                    [
                        'ixFROM' => $userid,
                        'ixTO' => $_POST['user'],
                        'ixITEM' => $r['itmid'],
                        'ixQTY' => $_POST['qty'],
                        'ixTIME' => time(),
                        'ixFROMIP' => $ir['lastip'],
                        'ixTOIP' => $rm['lastip'],
                    ],
                );
            };
            $db->tryFlatTransaction($save);
            echo 'You sent ' . $_POST['qty'] . ' ' . $r['itmname'] . '(s) to ' . $rm['username'];
        }
    }
} elseif (!empty($_GET['ID'])) {
    $r = $db->row(
        'SELECT inv_qty, itmname
        FROM inventory iv
        INNER JOIN items AS it ON iv.inv_itemid = it.itmid
        WHERE iv.inv_id = ? AND iv.inv_userid = ?
        LIMIT 1',
        $_GET['ID'],
        $userid,
    );
    if (empty($r)) {
        echo 'Invalid item ID';
    } else {
        $code = request_csrf_code("senditem_{$_GET['ID']}");
        echo "
		<b>Enter who you want to send {$r['itmname']} to and how many you want to send.
			You have {$r['inv_qty']} to send.</b>
		<br />
		<form action='itemsend.php?ID={$_GET['ID']}' method='post'>
			User ID: <input type='text' name='user' value='' />
			<br />
			Quantity: <input type='text' name='qty' value='' />
			<br />
			<input type='hidden' name='verf' value='{$code}' />
			<input type='submit' value='Send Items (no prompt so be sure!' />
		</form>
   		";
    }
} else {
    echo 'Invalid use of file.';
}
$h->endpage();

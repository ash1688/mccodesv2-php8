<?php
declare(strict_types=1);
/**
 * MCCodes v2 by Dabomstew & ColdBlooded
 *
 * Repository: https://github.com/davemacaulay/mccodesv2
 * License: MIT License
 */

global $h;
require_once('globals.php');
echo '
<h3>My Contacts</h3>
<table width="85%" class="table" cellspacing="1">
		<tr>
	<td><a href="mailbox.php?action=inbox">Inbox</a></td>
	<td><a href="mailbox.php?action=outbox">Sent Messages</a></td>
	<td><a href="mailbox.php?action=compose">Compose Message</a></td>
	<td><a href="mailbox.php?action=delall">Delete All Messages</a></td>
	<td><a href="mailbox.php?action=archive">Archive Messages</a></td>
	<td><a href="contactlist.php">My Contacts</a></td>
		</tr>
</table>
<br />
   ';

switch ($_GET['action']) {
    case 'add':
        add_contact();
        break;
    case 'remove':
        remove_contact();
        break;
    default:
        contacts_list();
        break;
}

/**
 * @return void
 */
function contacts_list(): void
{
    global $db, $userid;
    echo "
<a href='contactlist.php?action=add'>&gt; Add a Contact</a><br />
These are the people on your contact list.
<br />
<table width='90%' class='table' cellspacing='1'>
		<tr style='background:gray'>
	<th>ID</th>
	<th>Name</th>
	<th>Mail</th>
	<th>Remove</th>
		</tr>
   ";
    $q = $db->run(
        'SELECT cl.cl_ID, u.donatordays, username, userid
        FROM contactlist AS cl
        LEFT JOIN users AS u ON cl.cl_ADDED = u.userid
        WHERE cl.cl_ADDER = ?
        ORDER BY u.username',
        $userid,
    );
    foreach ($q as $r) {
        $d = '';
        if ($r['donatordays']) {
            $r['username'] = "<font color=red>{$r['username']}</font>";
            $d             =
                "<img src='app/view/assets/images/donator.gif' alt='Donator: {$r['donatordays']} Days Left' title='Donator: {$r['donatordays']} Days Left' />";
        }
        echo '
		<tr>
	<td>' . $r['userid'] . '</td>
	<td><a href="viewuser.php?u=' . $r['userid'] . '">' . $r['username']
            . '</a> ' . $d
            . '</td>
	<td><a href="mailbox.php?action=compose&ID=' . $r['userid']
            . '">Mail</a></td>
	<td><a href="contactlist.php?action=remove&contact=' . $r['cl_ID']
            . '">Remove</a></td>
		</tr>
   ';
    }
    echo '</table>';
}

/**
 * @return void
 */
function add_contact(): void
{
    global $db, $userid;
    $_POST['ID'] =
        (isset($_POST['ID']) && is_numeric($_POST['ID']))
            ? abs(intval($_POST['ID'])) : '';
    if ($_POST['ID']) {
        $dupe_count = $db->cell(
            'SELECT COUNT(cl_ADDER) FROM contactlist WHERE cl_ADDER = ? AND cl_ADDED = ?',
            $userid,
            $_POST['ID'],
        );
        $r          = $db->row(
            'SELECT userid, username FROM users WHERE userid = ? LIMIT 1',
            $_POST['ID'],
        );
        if ($dupe_count > 0) {
            echo 'You cannot add the same person twice.';
        } elseif ($userid == $_POST['ID']) {
            echo 'There is no point in adding yourself to your own list.';
        } elseif (empty($r)) {
            echo "Oh no, you're trying to add a ghost.";
        } else {
            $db->insert(
                'contactlist',
                [
                    'cl_ADDER' => $userid,
                    'cl_ADDED' => $r['userid'],
                ],
            );
            echo "{$r['username']} was added to your contact list.<br />
<a href='contactlist.php'>&gt; Back</a>";
        }
    } else {
        echo "
Adding a contact!
<form action='contactlist.php?action=add' method='post'>
	Contact's ID: <input type='text' name='ID' value='{$_GET['ID']}' />
	<br />
	<input type='submit' value='Add Contact' />
</form>
   ";
    }
}

/**
 * @return void
 */
function remove_contact(): void
{
    global $db, $userid, $h;
    $_GET['contact'] =
        (isset($_GET['contact']) && is_numeric($_GET['contact']))
            ? abs(intval($_GET['contact'])) : '';
    if (empty($_GET['contact'])) {
        echo '
You didn\'t select a real contact.<br />
&gt; <a href="contactlist.php">Back</a>
    ';
        $h->endpage();
        exit;
    }
    $exist_count = $db->cell(
        'SELECT COUNT(cl_ADDER) FROM contactlist WHERE cl_ADDER = ? AND cl_ID = ?',
        $userid,
        $_GET['contact'],
    );
    if ($exist_count == 0) {
        echo 'Listing doesn\'t exist.<br />&gt; <a href="contactlist.php">Go Back</a>';
        $h->endpage();
        exit;
    }
    $db->delete(
        'contactlist',
        [
            'cl_ID' => $_GET['contact'],
            'cl_ADDER' => $userid,
        ],
    );
    echo "
Contact removed from your list.<br />
&gt; <a href='contactlist.php'>Go Back</a>
   ";
}

$h->endpage();

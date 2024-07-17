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
$ac = $ir['new_announcements'];
$q = $db->run(
    'SELECT a_text, a_time FROM announcements ORDER BY a_time DESC',
);
echo '
<table width="80%" cellspacing="1" cellpadding="1" class="table">
		<tr>
	<th width="30%">Time</th>
	<th width="70%">Announcement</th>
		</tr>
   ';
foreach ($q as $r)
{
    if ($ac > 0)
    {
        $ac--;
        $new = '<br /><b>New!</b>';
    }
    else
    {
        $new = '';
    }
    $r['a_text'] = nl2br($r['a_text']);
    echo '
		<tr>
	<td valign=top>' . date('F j Y, g:i:s a', (int)$r['a_time']) . $new
            . '</td>
	<td valign=top>' . $r['a_text'] . '</td>
		</tr>
   ';
}
echo '</table>';
if ($ir['new_announcements'] > 0)
{
    $db->update(
        'users',
        ['new_announcements' => 0],
        ['userid' => $userid],
    );
}
$h->endpage();

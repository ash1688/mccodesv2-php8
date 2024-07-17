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
if (!isset($_GET['type'])
    || !in_array($_GET['type'],
        ['equip_primary', 'equip_secondary', 'equip_armor'],
        true)) {
    echo 'This slot ID is not valid.';
    $h->endpage();
    exit;
}
if ($ir[$_GET['type']] == 0) {
    echo 'You do not have anything equipped in this slot.';
    $h->endpage();
    exit;
}
$save = function () use ($db, $ir, $userid) {
    item_add($userid, $ir[$_GET['type']], 1);
    $db->update(
        'users',
        [$_GET['type'] => 0],
        ['userid' => $ir['userid']],
    );
};
$db->tryFlatTransaction($save);
$names =
    ['equip_primary' => 'Primary Weapon',
        'equip_secondary' => 'Secondary Weapon',
        'equip_armor' => 'Armor'];
echo 'The item in your ' . $names[$_GET['type']]
    . ' slot was successfully unequipped.';
$h->endpage();

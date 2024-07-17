<?php
declare(strict_types=1);
/**
 * MCCodes v2 by Dabomstew & ColdBlooded
 *
 * Repository: https://github.com/davemacaulay/mccodesv2
 * License: MIT License
 */

$macropage = 'criminal.php';
global $db, $ir, $h;
require_once('globals.php');
if ($ir['jail'] || $ir['hospital']) {
    die('This page cannot be accessed while in jail or hospital.');
}
$crimes = [];
$q2     = $db->run(
    'SELECT crimeGROUP, crimeNAME, crimeBRAVE, crimeID FROM crimes ORDER BY crimeBRAVE'
);
foreach ($q2 as $r2) {
    $crimes[] = $r2;
}
$q = $db->run(
    'SELECT cgID, cgNAME FROM crimegroups ORDER BY cgORDER'
);
echo "<h3>Criminal Centre</h3><br />
<table width='75%' cellspacing='1' class='table'><tr><th>Crime</th><th>Cost</th><th>Do</th></tr>";
foreach ($q as $r) {
    echo "<tr><td colspan='3' class='h'>{$r['cgNAME']}</td></tr>";
    foreach ($crimes as $v) {
        if ($v['crimeGROUP'] == $r['cgID']) {
            echo "<tr><td>{$v['crimeNAME']}</td><td>{$v['crimeBRAVE']} Brave</td><td><a href='docrime.php?c={$v['crimeID']}'>Do</a></td></tr>";
        }
    }
}
echo '</table>';
$h->endpage();

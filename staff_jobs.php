<?php
declare(strict_types=1);
/**
 * MCCodes v2 by Dabomstew & ColdBlooded
 *
 * Repository: https://github.com/davemacaulay/mccodesv2
 * License: MIT License
 */

global $ir, $h;
require_once('sglobals.php');
check_access('manage_jobs');
if (!isset($_GET['action'])) {
    $_GET['action'] = '';
}
switch ($_GET['action']) {
    case 'newjob':
        newjob();
        break;
    case 'jobedit':
        jobedit();
        break;
    case 'newjobrank':
        newjobrank();
        break;
    case 'jobrankedit':
        jobrankedit();
        break;
    case 'jobdele':
        jobdele();
        break;
    case 'jobrankdele':
        jobrankdele();
        break;
    default:
        echo 'Error: This script requires an action.';
        break;
}

function process_jobs_post_data(): void
{
    global $db;
    $_POST['jNAME']     =
        (isset($_POST['jNAME'])
            && preg_match(
                "/^[a-z0-9_]+([\\s]{1}[a-z0-9_]|[a-z0-9_])+$/i",
                $_POST['jNAME']))
            ? strip_tags(stripslashes($_POST['jNAME']))
            : '';
    $_POST['jDESC']     =
        (isset($_POST['jDESC']))
            ? strip_tags(stripslashes($_POST['jDESC']))
            : '';
    $_POST['jOWNER']    =
        (isset($_POST['jOWNER'])
            && preg_match(
                "/^[a-z0-9_]+([\\s]{1}[a-z0-9_]|[a-z0-9_])+$/i",
                $_POST['jOWNER']))
            ? strip_tags(stripslashes($_POST['jOWNER']))
            : '';
    $_POST['jrNAME']    =
        (isset($_POST['jrNAME'])
            && preg_match(
                "/^[a-z0-9_]+([\\s]{1}[a-z0-9_]|[a-z0-9_])+$/i",
                $_POST['jrNAME']))
            ? strip_tags(stripslashes($_POST['jrNAME']))
            : '';
    $_POST['jrPAY']     =
        (isset($_POST['jrPAY']) && is_numeric($_POST['jrPAY']))
            ? abs(intval($_POST['jrPAY'])) : '';
    $_POST['jrSTRG']    =
        (isset($_POST['jrSTRG']) && is_numeric($_POST['jrSTRG']))
            ? abs(intval($_POST['jrSTRG'])) : 0;
    $_POST['jrLABOURG'] =
        (isset($_POST['jrLABOURG']) && is_numeric($_POST['jrLABOURG']))
            ? abs(intval($_POST['jrLABOURG'])) : 0;
    $_POST['jrIQG']     =
        (isset($_POST['jrIQG']) && is_numeric($_POST['jrIQG']))
            ? abs(intval($_POST['jrIQG'])) : 0;
    $_POST['jrSTRN']    =
        (isset($_POST['jrSTRN']) && is_numeric($_POST['jrSTRN']))
            ? abs(intval($_POST['jrSTRN'])) : 0;
    $_POST['jrLABOURN'] =
        (isset($_POST['jrLABOURN']) && is_numeric($_POST['jrLABOURN']))
            ? abs(intval($_POST['jrLABOURN'])) : 0;
    $_POST['jrIQN']     =
        (isset($_POST['jrIQN']) && is_numeric($_POST['jrIQN']))
            ? abs(intval($_POST['jrIQN'])) : 0;
}

/**
 * @return void
 */
function newjob(): void
{
    global $db, $h;
    process_jobs_post_data();
    if (!empty($_POST['jNAME']) && !empty($_POST['jDESC'])
        && !empty($_POST['jOWNER']) && !empty($_POST['jrNAME'])
        && !empty($_POST['jrPAY']) && !empty($_POST['jrSTRN'])
        && !empty($_POST['jrLABOURN']) && !empty($_POST['jrIQN'])) {
        staff_csrf_stdverify('staff_newjob', 'staff_jobs.php?action=newjob');
        $save = function () use ($db) {
            $i = $db->insert(
                'jobs',
                [
                    'jNAME' => $_POST['jNAME'],
                    'jDESC' => $_POST['jDESC'],
                    'jOWNER' => $_POST['jOWNER'],
                ],
            );
            $j = $db->insert(
                'jobranks',
                [
                    'jrNAME' => $_POST['jrNAME'],
                    'jrPAY' => $_POST['jrPAY'],
                    'jrSTRN' => $_POST['jrSTRN'],
                    'jrLABOURN' => $_POST['jrLABOURN'],
                    'jrIQN' => $_POST['jrIQN'],
                    'jrSTRG' => $_POST['jrSTRG'],
                    'jrLABOURG' => $_POST['jrLABOURG'],
                    'jrIQG' => $_POST['jrIQG'],
                    'jrJOB' => $i,
                ],
            );
            $db->update(
                'jobs',
                ['jFIRST' => $j],
                ['jID' => $i],
            );
            stafflog_add('Added job ' . $_POST['jNAME']);
        };
        $db->tryFlatTransaction($save);
        echo 'Job created!<br />
        &gt; <a href="staff.php">Go Home</a>';
        $h->endpage();
        exit;
    } else {
        $csrf = request_csrf_html('staff_newjob');
        echo "
        <form action='staff_jobs.php?action=newjob' method='post'>
        	<b>Job Name:</b> <input type='text' name='jNAME' />
        	<br />
        	<b>Job Description:</b> <input type='text' name='jDESC' />
        	<br />
        	<b>Job Owner:</b> <input type='text' name='jOWNER' />
        	<br />
        	<hr />
        	<b>First Job Rank:</b>
        	<br />
        	<b>Rank Name:</b> <input type='text' name='jrNAME' />
        	<br />
        	<b>Pays:</b> <input type='text' name='jrPAY' value='10' />
        	<br />
        	<b>Gains:</b>
        		Str: <input type='text' name='jrSTRG' size='3' maxlength='3' value='0' />
        		Lab: <input type='text' name='jrLABOURG' size='3' maxlength='3' value='0' />
        		IQ: <input type='text' name='jrIQG' size='3' maxlength='3' value='0' />
        	<br />
        	<b>Reqs:</b>
        		Str: <input type='text' name='jrSTRN' size='5' maxlength='5' value='1' />
        		Lab: <input type='text' name='jrLABOURN' size='5' maxlength='5' value='1' />
        		IQ: <input type='text' name='jrIQN' size='5' maxlength='5' value='1' />
        	<br />
        	{$csrf}
        	<input type='submit' value='Create Job' />
        </form>
           ";
    }
}

/**
 * @return void
 */
function jobedit(): void
{
    global $db, $h;
    $_POST['jNAME']  =
        (isset($_POST['jNAME'])
            && preg_match(
                "/^[a-z0-9_]+([\\s]{1}[a-z0-9_]|[a-z0-9_])+$/i",
                $_POST['jNAME']))
            ? strip_tags(stripslashes($_POST['jNAME']))
            : '';
    $_POST['jDESC']  =
        (isset($_POST['jDESC']))
            ? strip_tags(stripslashes($_POST['jDESC']))
            : '';
    $_POST['jOWNER'] =
        (isset($_POST['jOWNER'])
            && preg_match(
                "/^[a-z0-9_]+([\\s]{1}[a-z0-9_]|[a-z0-9_])+$/i",
                $_POST['jOWNER']))
            ? strip_tags(stripslashes($_POST['jOWNER']))
            : '';
    $_POST['jFIRST'] =
        (isset($_POST['jFIRST']) && is_numeric($_POST['jFIRST']))
            ? abs(intval($_POST['jFIRST'])) : '';
    $_POST['jID']    =
        (isset($_POST['jID']) && is_numeric($_POST['jID']))
            ? abs(intval($_POST['jID'])) : '';
    if (!empty($_POST['jID']) && !empty($_POST['jFIRST'])
        && !empty($_POST['jOWNER']) && !empty($_POST['jDESC'])
        && !empty($_POST['jNAME'])) {
        staff_csrf_stdverify('staff_editjob2', 'staff_jobs.php?action=jobedit');
        $job_exists = $db->exists(
            'SELECT COUNT(jID) FROM jobs WHERE jID = ?',
            $_POST['jID'],
        );
        if (!$job_exists) {
            echo 'Invalid job.<br />
            &gt; <a href="staff_jobs.php?action=jobedit">Go Back</a>';
            $h->endpage();
            exit;
        }
        $rank_exists = $db->exists(
            'SELECT COUNT(jrID) FROM jobranks WHERE jrID = ?',
            $_POST['jFIRST'],
        );
        if (!$rank_exists) {
            echo 'Invalid rank.<br />
            &gt; <a href="staff_jobs.php?action=jobedit">Go Back</a>';
            $h->endpage();
            exit;
        }
        $save = function () use ($db) {
            $db->update(
                'jobs',
                [
                    'jNAME' => $_POST['jNAME'],
                    'jDESC' => $_POST['jDESC'],
                    'jOWNER' => $_POST['jOWNER'],
                    'jFIRST' => $_POST['jFIRST'],
                ],
                ['jID' => $_POST['jID']],
            );
            stafflog_add('Edited job ' . $_POST['jNAME']);
        };
        $db->tryFlatTransaction($save);
        echo 'Job updated!<br />
        &gt; <a href="staff.php">Go Home</a>';
        $h->endpage();
        exit;
    } elseif (!empty($_POST['jID'])) {
        staff_csrf_stdverify('staff_editjob1', 'staff_jobs.php?action=jobedit');
        $r = $db->row(
            "SELECT jFIRST, jOWNER, jDESC, jNAME FROM jobs WHERE jID = ?",
            $_POST['jID'],
        );
        if (empty($r)) {
            echo 'Invalid job.<br />
            &gt; <a href="staff_jobs.php?action=jobedit">Go Back</a>';
            $h->endpage();
            exit;
        }
        $csrf     = request_csrf_html('staff_editjob2');
        $jobname  = addslashes($r['jNAME']);
        $jobdesc  = addslashes($r['jDESC']);
        $jobowner = addslashes($r['jOWNER']);
        echo "
        <form action='staff_jobs.php?action=jobedit' method='post'>
        	<input type='hidden' name='jID' value='{$_POST['jID']}'>
        	<b>Job Name:</b> <input type='text' name='jNAME' value='{$jobname}'>
        	<br />
        	<b>Job Description:</b> <input type='text' name='jDESC' value='{$jobdesc}'>
        	<br />
        	<b>Job Owner:</b> <input type='text' name='jOWNER' value='{$jobowner}'>
        	<br />
        	<b>First Job Rank:</b> "
            . jobrank_dropdown('jFIRST', $r['jFIRST'])
            . "
        	<br />
        	{$csrf}
        	<input type='submit' value='Edit' />
        </form>
           ";
    } else {
        $csrf = request_csrf_html('staff_editjob1');
        echo "
        <form action='staff_jobs.php?action=jobedit' method='post'>
        Select a job to edit.
        <br />
        	" . job_dropdown('jID', -1)
            . "
        <br />
        	{$csrf}
        	<input type='submit' value='Edit Job' />
        </form>
           ";
    }
}

/**
 * @return void
 */
function newjobrank(): void
{
    global $db, $h;
    process_jobs_post_data();
    if (!empty($_POST['jrNAME']) && !empty($_POST['jrJOB'])
        && !empty($_POST['jrPAY']) && !empty($_POST['jrSTRN'])
        && !empty($_POST['jrLABOURN']) && !empty($_POST['jrIQN'])) {
        staff_csrf_stdverify('staff_newjobrank',
            'staff_jobs.php?action=newjobrank');
        $row = $db->row(
            'SELECT jNAME FROM jobs WHERE jID = ?',
            $_POST['jrJOB'],
        );
        if (empty($row)) {
            echo 'Invalid job.<br />
            &gt; <a href="staff_jobs.php?action=newjobrank">Go Back</a>';
            $h->endpage();
            exit;
        }
        $save = function () use ($db, $row) {
            $db->insert(
                'jobranks',
                [
                    'jrNAME' => $_POST['jrNAME'],
                    'jrJOB' => $_POST['jrJOB'],
                    'jrPAY' => $_POST['jrPAY'],
                    'jrSTRN' => $_POST['jrSTRN'],
                    'jrLABOURN' => $_POST['jrLABOURN'],
                    'jrIQN' => $_POST['jrIQN'],
                    'jrSTRG' => $_POST['jrSTRG'],
                    'jrLABOURG' => $_POST['jrLABOURG'],
                    'jrIQG' => $_POST['jrIQG'],
                ],
            );
            stafflog_add('Added job rank ' . $_POST['jrNAME'] . ' to job ' . $row['jNAME']);
        };
        $db->tryFlatTransaction($save);
        echo 'Job rank created!<br />
        &gt; <a href="staff_jobs.php?action=newjobrank">Go Back</a>';
        $h->endpage();
        exit;
    } else {
        $csrf = request_csrf_html('staff_newjobrank');
        echo "
        <form action='staff_jobs.php?action=newjobrank' method='post'>
        	<b>Rank Name:</b> <input type='text' name='jrNAME' />
        	<br />
        	<b>Pays:</b> <input type='text' name='jrPAY' value='10' />
        	<br />
        	<b>Job:</b> " . job_dropdown('jrJOB', -1)
            . "
        	<br />
        	<b>Gains:</b>
        		Str: <input type='text' name='jrSTRG' size='3' maxlength='3' value='0' />
        		Lab: <input type='text' name='jrLABOURG' size='3' maxlength='3' value='0' />
        		IQ: <input type='text' name='jrIQG' size='3' maxlength='3' value='0' />
        	<br />
        	<b>Reqs:</b>
        		Str: <input type='text' name='jrSTRN' size='5' maxlength='5' value='1' />
        		Lab: <input type='text' name='jrLABOURN' size='5' maxlength='5'  value='1' />
        		IQ: <input type='text' name='jrIQN' size='5' maxlength='5'  value='1' />
        	<br />
        	{$csrf}
        	<input type='submit' value='Create Job Rank' />
        </form>
           ";
    }
}

/**
 * @return void
 */
function jobrankedit(): void
{
    global $db, $h;
    $_POST['jrID'] =
        (isset($_POST['jrID']) && is_numeric($_POST['jrID']))
            ? abs(intval($_POST['jrID'])) : '';
    process_jobs_post_data();
    if (!empty($_POST['jrID']) && !empty($_POST['jrNAME'])
        && !empty($_POST['jrJOB']) && !empty($_POST['jrPAY'])
        && !empty($_POST['jrSTRN']) && !empty($_POST['jrLABOURN'])
        && !empty($_POST['jrIQN'])) {
        staff_csrf_stdverify('staff_editjobrank2',
            'staff_jobs.php?action=jobrankedit');
        $rank_exists = $db->exists(
            'SELECT COUNT(jrID) FROM jobranks WHERE jrID = ?',
            $_POST['jrID'],
        );
        if (!$rank_exists) {
            echo 'Invalid rank.<br />
            &gt; <a href="staff_jobs.php?action=jobrankedit">Go Back</a>';
            $h->endpage();
            exit;
        }
        $job_exists = $db->exists(
            "SELECT COUNT(jID) FROM jobs WHERE jID = ?",
            $_POST['jrJOB'],
        );
        if (!$job_exists) {
            echo 'Invalid job.<br />
            &gt; <a href="staff_jobs.php?action=jobrankedit">Go Back</a>';
            $h->endpage();
            exit;
        }
        $save = function () use ($db) {
            $db->update(
                'jobranks',
                [
                    'jrNAME' => $_POST['jrNAME'],
                    'jrJOB' => $_POST['jrJOB'],
                    'jrPAY' => $_POST['jrPAY'],
                    'jrSTRN' => $_POST['jrSTRN'],
                    'jrLABOURN' => $_POST['jrLABOURN'],
                    'jrIQN' => $_POST['jrIQN'],
                    'jrSTRG' => $_POST['jrSTRG'],
                    'jrLABOURG' => $_POST['jrLABOURG'],
                    'jrIQG' => $_POST['jrIQG'],
                ],
                ['jrID' => $_POST['jrID']],
            );
            stafflog_add('Edited job rank ' . $_POST['jrNAME']);
        };
        $db->tryFlatTransaction($save);
        echo 'Job rank updated!<br />
        &gt; <a href="staff.php">Go Home</a>';
    } elseif (!empty($_POST['jrID'])) {
        staff_csrf_stdverify('staff_editjobrank1',
            'staff_jobs.php?action=jobrankedit');
        $r = $db->row(
            "SELECT * FROM jobranks WHERE jrID = ?",
            $_POST['jrID'],
        );
        if (empty($r)) {
            echo 'Invalid rank.<br />
            &gt; <a href="staff_jobs.php?action=jobrankedit">Go Back</a>';
            $h->endpage();
            exit;
        }
        $csrf = request_csrf_html('staff_editjobrank2');
        echo "
        <form action='staff_jobs.php?action=jobrankedit' method='post'>
        	<input type='hidden' name='jrID' value='{$_POST['jrID']}' />
        	<b>Job Rank Name:</b> <input type='text' name='jrNAME' value='{$r['jrNAME']}'><br />
        	<b>Job:</b> " . job_dropdown('jrJOB', $r['jrJOB'])
            . "
        	<br />
        	<b>Pays:</b> <input type='text' name='jrPAY' value='{$r['jrPAY']}' /><br />
        	<b>Gains:</b>
        		Str: <input type='text' name='jrSTRG' size='3' maxlength='3' value='{$r['jrSTRG']}' />
        		Lab: <input type='text' name='jrLABOURG' size='3' maxlength='3' value='{$r['jrLABOURG']}' />
        		IQ: <input type='text' name='jrIQG' size='3' maxlength='3' value='{$r['jrIQG']}' />
        	<br />
        	<b>Reqs:</b>
        		Str: <input type='text' name='jrSTRN' size='5' maxlength='5' value='{$r['jrSTRN']}' />
        		Lab: <input type='text' name='jrLABOURN' size='5' maxlength='5' value='{$r['jrLABOURN']}' />
        		IQ: <input type='text' name='jrIQN' size='5' maxlength='5' value='{$r['jrIQN']}' />
        	<br />
        	{$csrf}
        	<input type='submit' value='Edit' />
        </form>
           ";
    } else {
        $csrf = request_csrf_html('staff_editjobrank1');
        echo "
        <form action='staff_jobs.php?action=jobrankedit' method='post'>
        	Select a job rank to edit.
        	<br />
        	" . jobrank_dropdown('jrID', -1)
            . "
        	<br />
        	{$csrf}
        	<input type='submit' value='Edit Job Rank' />
        </form>
           ";
    }
}

/**
 * @return void
 */
function jobrankdele(): void
{
    global $db, $h;
    $_POST['jrID'] =
        (isset($_POST['jrID']) && is_numeric($_POST['jrID']))
            ? abs(intval($_POST['jrID'])) : '';
    if (!empty($_POST['jrID'])) {
        staff_csrf_stdverify('staff_deljobrank',
            'staff_jobs.php?action=jobrankdele');
        $aff_job = $db->cell(
            "SELECT jrJOB FROM jobranks WHERE jrID = ?",
            $_POST['jrID'],
        );
        if (empty($aff_job)) {
            echo 'Invalid rank.<br />
            &gt; <a href="staff_jobs.php?action=jobrankdele">Go Back</a>';
            $h->endpage();
            exit;
        }
        $save = function () use ($db, $aff_job) {
            $names = $db->row(
                'SELECT * FROM (
                    (SELECT jNAME FROM jobs WHERE jFIRST = ?) AS a,
                    (SELECT jrNAME FROM jobranks WHERE jrID = ?) AS b
                    )',
                $_POST['jrID'],
                $_POST['jrID'],
            );
            $db->delete(
                'jobranks',
                ['jrID' => $_POST['jrID']],
            );
            if (!empty($names)) {
                echo "<br />
                <b>Warning!</b> The Job {$names['jNAME']} now has no first rank!
                Please go edit it and include a first rank.<br />
                Users who were in the rank you deleted will have to
                reapply for their job.";
                $db->update(
                    'users',
                    [
                        'job' => 0,
                        'jobrank' => 0,
                    ],
                    ['jobrank' => $_POST['jrID']],
                );
            } else {
                $db->safeQuery(
                    'UPDATE users AS u
                    INNER JOIN jobs AS j ON u.job = j.jID
                    SET u.jobrank = j.jFIRST
                    WHERE u.job = ? AND u.jobrank = ?',
                    [$aff_job, $_POST['jrID']],
                );
            }
            stafflog_add('Deleted job rank ' . $names['jrNAME'] . ' from job ' . $names['jNAME']);
        };
        $db->tryFlatTransaction($save);
        echo 'Job rank successfully deleted!<br />&gt; <a href="staff.php">Go Home</a>';
    } else {
        $csrf = request_csrf_html('staff_deljobrank');
        echo "
        <form action='staff_jobs.php?action=jobrankdele' method='post'>
        Select a job rank to delete.<br />
        	" . jobrank_dropdown('jrID', -1)
            . "
        	<br />
        	{$csrf}
        	<input type='submit' value='Delete Job Rank' />
        </form>
           ";
    }
}

/**
 * @return void
 */
function jobdele(): void
{
    global $db, $h;
    $_POST['jID'] =
        (isset($_POST['jID']) && is_numeric($_POST['jID']))
            ? abs(intval($_POST['jID'])) : '';
    if (!empty($_POST['jID'])) {
        staff_csrf_stdverify('staff_deljob', 'staff_jobs.php?action=jobdele');
        $name = $db->cell(
            'SELECT jNAME FROM jobs WHERE jID = ?',
            $_POST['jID'],
        );
        if (empty($name)) {
            echo 'Invalid job.<br />
            &gt; <a href="staff_jobs.php?action=jobdele">Go Back</a>';
            $h->endpage();
            exit;
        }
        $deleted = 0;
        $save    = function () use ($db, &$deleted, $name) {
            $db->delete(
                'jobs',
                ['jID' => $_POST['jID']],
            );
            $deleted = $db->delete(
                'jobranks',
                ['jrJOB' => $_POST['jID']],
            );
            $db->update(
                'users',
                [
                    'job' => 0,
                    'jobrank' => 0,
                ],
                ['job' => $_POST['jID']],
            );
            stafflog_add('Deleted job ' . $name . ' and all associated job ranks');
        };
        $db->tryFlatTransaction($save);
        echo 'Job successfully deleted!<br />' . $deleted . ' job ranks deleted.<br />&gt; <a href="staff.php">Go Home</a>';
    } else {
        $csrf = request_csrf_html('staff_deljob');
        echo "
        <form action='staff_jobs.php?action=jobdele' method='post'>
        Select a job to delete.<br />
        	" . job_dropdown('jID', -1)
            . "
        	<br />
        	{$csrf}
        	<input type='submit' value='Delete Job' />
        </form>
           ";
    }
}

$h->endpage();

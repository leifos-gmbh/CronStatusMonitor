<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Class ilCronStatusMonitorCronJob
 * @author Thomas Famula <famula@leifos.de>
 */
class ilCronStatusMonitorCronJob extends ilCronJob
{
    protected $plugin;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    public function getId() : string
    {
        return "cronstatusmonitor";
    }

    public function getTitle() : string
    {
        return ilCronStatusMonitorPlugin::PNAME;
    }

    public function getDescription() : string
    {
        return ilCronStatusMonitorPlugin::getInstance()->txt("cron_description");
    }

    public function hasAutoActivation() : bool
    {
        return false;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_HOURS;
    }

    public function getDefaultScheduleValue() : int
    {
        return 1;
    }

    public function run() : ilCronJobResult
    {
        try {
            $crashed_jobs = $this->checkCrashedCronJobs();
            if (is_array($crashed_jobs)) {
                $this->composeAndSendMail($crashed_jobs);
                $result = new ilCronJobResult();
                $result->setStatus(ilCronJobResult::STATUS_OK);
            } else {
                $result = new ilCronJobResult();
                $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
            }
        } catch (Exception $e) {
            $result = new ilCronJobResult();
            $result->setStatus(ilCronJobResult::STATUS_CRASHED);
            $result->setMessage($e->getMessage());
            ilLoggerFactory::getLogger('cron')->error("Cron-job 'CronStatusMonitor' crashed");
        }

        return $result;
    }

    /**
     * @return array|void
     *
     * Check cron-jobs, which crashed or failed since the last run (or first run) of this cron-job
     */
    public function checkCrashedCronJobs()
    {
        global $DIC;
        $ilDB = $DIC->database();
        $old_crashed_jobs = array();
        $new_crashed_jobs = array();

        // Get cron-jobs, which had the status "crashed" or "failed" on the last run of this cron-job (empty result on first run)
        $result = $ilDB->query("SELECT job_id, ts FROM crn_sts_mtr");
        if ($ilDB->numRows($result) > 0) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $old_crashed_jobs[$row["job_id"]] = $row["ts"];
            }
        }

        // Get cron-jobs, which have the status "crashed" or "failed" on the current run of this cron-job
        $result = $ilDB->queryF(
            "SELECT job_id, job_result_status, job_result_ts FROM cron_job WHERE job_result_status IN (%s, %s)",
            array("integer", "integer"),
            array(ilCronJobResult::STATUS_CRASHED, ilCronJobResult::STATUS_FAILED)
        );
        if ($ilDB->numRows($result) > 0) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $new_crashed_jobs[$row["job_id"]] = $row["job_result_ts"];
            }
        } else {
            // No further processing if no cron-jobs have the status "crashed" or "failed"
            $ilDB->manipulate("DELETE FROM crn_sts_mtr");
            return;
        }

        $ilDB->manipulate("DELETE FROM crn_sts_mtr");

        // Convert the "new" crashed cron-jobs into "old" crashed cron-jobs for the next run of this cron-job
        foreach ($new_crashed_jobs as $key => $job) {
            $ilDB->manipulateF(
                "INSERT INTO crn_sts_mtr (job_id, ts) VALUES ".
                " (%s,%s)",
                array("text", "integer"),
                array($key, $job)
            );
        }

        /*
        Compare the actual crashed cron-jobs with the crashed cron-jobs from the last run to check which new cron-jobs crashed since the last run.
        Check also if crashed cron-jobs, which have been reset since the last run,
        crashed again in the period between the last run and the actual run of this cron-job.
        */
        $crashed_jobs = array_diff_assoc($new_crashed_jobs, $old_crashed_jobs);


        // No further processing if no new cron-jobs crashed
        if (empty($crashed_jobs)) {
            return;
        }

        return $crashed_jobs;
    }

    /**
     * Compose a message with information about the currently crashed cron-jobs and
     * send this message to the entered account logins, which are defined in the plugin configuration
     */
    public function composeAndSendMail(array $crashed_jobs) : void
    {
        $sender = ilObjUser::_lookupId("anonymous");
        $subject = $this->plugin->txt("email_subject");
        $crashed_jobs_string = implode(", ", array_keys($crashed_jobs));
        $message = $this->plugin->txt("email_message") . ": " . $crashed_jobs_string;

        include_once("./Customizing/global/plugins/Services/Cron/CronHook/CronStatusMonitor/classes/class.ilCronStatusMonitorSettings.php");
        $setting = new ilCronStatusMonitorSettings();
        $users = $setting->get("email_recipient");

        /**
         * In case of using ilSystemNotification to get recipients
         *
        $users_parts = explode(',', $users);
        $users_ids = array();
        foreach ($users_parts as $p)
        {
        $users_ids[] = ilObjUser::_lookupId($p);
        }
         */

        include_once "./Services/Mail/classes/class.ilMail.php";
        $ntf = new ilMail($sender);
        $ntf->enqueue(
            $users,
            "",
            "",
            $subject,
            $message,
            []
        );
    }
}

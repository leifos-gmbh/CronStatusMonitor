<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Class ilCronStatusMonitorCronJob
 * @author Thomas Famula <famula@leifos.de>
 */
class ilCronStatusMonitorCronJob extends ilCronJob
{
    protected $plugin;

    function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    public function getId()
    {
        return "cronstatusmonitor";
    }

    public function getTitle()
    {
        return ilCronStatusMonitorPlugin::PNAME;
    }

    public function getDescription()
    {
        return ilCronStatusMonitorPlugin::getInstance()->txt("cron_description");
    }

    public function hasAutoActivation()
    {
        return false;
    }

    public function hasFlexibleSchedule()
    {
        return true;
    }

    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_IN_HOURS;
    }

    public function getDefaultScheduleValue()
    {
        return 1;
    }

    public function run()
    {
        try
        {
            $crashed_jobs = $this->checkCrashedCronJobs();
            if (is_array($crashed_jobs)) {
                $this->composeAndSendMail($crashed_jobs);
                $result = new ilCronJobResult();
                $result->setStatus(ilCronJobResult::STATUS_OK);
            }
            else {
                $result = new ilCronJobResult();
                $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
            }

        }
        catch(Exception $e)
        {
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
     * Check cron-jobs, which crashed since the last run (or first run) of this cron-job
     */
    public function checkCrashedCronJobs()
    {
        global $DIC;
        $ilDB = $DIC->database();
        $old_crashed_jobs = array();
        $new_crashed_jobs = array();

        // Get cron-jobs, which had the status "crashed" on the last run of this cron-job (empty result on first run)
        $result = $ilDB->query("SELECT job_id, ts FROM crn_sts_mtr");
        if ($ilDB->numRows($result) > 0) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $old_crashed_jobs[$row["job_id"]] = $row["ts"];
            }

        }

        // Get cron-jobs, which have the status "crashed" on the current run of this cron-job
        $result = $ilDB->queryF("SELECT job_id, job_result_status, job_result_ts FROM cron_job WHERE job_result_status = %s",
            array("integer"),
            array(4));
        if ($ilDB->numRows($result) > 0) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $new_crashed_jobs[$row["job_id"]] = $row["job_result_ts"];
            }

        } else {
            // No further processing if no cron-jobs have the status "crashed"
            $ilDB->manipulate("DELETE FROM crn_sts_mtr");
            return;
        }

        $ilDB->manipulate("DELETE FROM crn_sts_mtr");

        // Convert the "new" crashed cron-jobs into "old" crashed cron-jobs for the next run of this cron-job
        foreach ($new_crashed_jobs as $key => $job) {
            $ilDB->manipulateF("INSERT INTO crn_sts_mtr (job_id, ts) VALUES ".
                " (%s,%s)",
                array("text", "integer"),
                array($key, $job));
        }

        /*
        Compare the actual crashed cron-jobs with the crashed cron-jobs from the last run to check which new cron-jobs crashed since the last run.
        Check also if crashed cron-jobs, which have been reset since the last run,
        crashed again in the period between the last run and the actual run of this cron-job.
        */
        $crashed_jobs = array_diff_assoc($new_crashed_jobs,$old_crashed_jobs);


        // No further processing if no new cron-jobs crashed
        if (empty($crashed_jobs)) {
            return;
        }

        return $crashed_jobs;
    }

    /**
     * @param array $crashed_jobs
     *
     * Compose a message with information about the currently crashed cron-jobs and
     * send this message to the entered account logins, which are defined in the plugin configuration
     */
    public function composeAndSendMail(array $crashed_jobs)
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

        include_once "./Services/Notification/classes/class.ilMail.php";
        $ntf = new ilMail($sender);
        $ntf->sendMail(
            $users,
            "",
            "",
            $subject,
            $message,
            false,
            array("system")
        );

    }

}

?>

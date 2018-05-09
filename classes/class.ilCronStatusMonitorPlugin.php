<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");

/**
 * Class ilCronStatusMonitorPlugin
 * @author Thomas Famula <famula@leifos.de>
 */
class ilCronStatusMonitorPlugin extends ilCronHookPlugin
{
    private static $instance = null;

    const CTYPE = "Services";
    const CNAME = "Cron";
    const SLOT_ID = "crnhk";
    const PNAME = "CronStatusMonitor";

    /**
     * Get singelton instance
     * @return ilCronStatusMonitorPlugin
     */
    public static function getInstance()
    {
        global $ilPluginAdmin;
        if(self::$instance)
        {
            return self::$instance;
        }
        include_once "./Services/Component/classes/class.ilPluginAdmin.php";
        return self::$instance = ilPluginAdmin::getPluginObject(
            self::CTYPE,
            self::CNAME,
            self::SLOT_ID,
            self::PNAME
        );
    }

    function getPluginName()
    {
        return self::PNAME;
    }

    function getCronJobInstances()
    {
        include_once "class.ilCronStatusMonitorCronJob.php";
        $job = new ilCronStatusMonitorCronJob();
        return array($job);
    }

    function getCronJobInstance($a_job_id)
    {
        include_once "class.ilCronStatusMonitorCronJob.php";
        return new ilCronStatusMonitorCronJob();
    }

    /**
     * Delete the database tables, which were created for the plugin, when the plugin became uninstalled
     */
    function afterUninstall()
    {
        global $ilDB;

        if ($ilDB->tableExists('crn_sts_mtr')) {
            $ilDB->dropTable("crn_sts_mtr");
        }

        if ($ilDB->tableExists('crn_sts_mtr_settings')) {
            $ilDB->dropTable("crn_sts_mtr_settings");
        }
    }
}

?>
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
     * Get singleton instance
     */
    public static function getInstance() : ilPlugin
    {
        global $ilPluginAdmin;
        if (self::$instance) {
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

    public function getPluginName() : string
    {
        return self::PNAME;
    }

    public function getCronJobInstances() : array
    {
        include_once "class.ilCronStatusMonitorCronJob.php";
        $job = new ilCronStatusMonitorCronJob($this);
        return array($job);
    }

    public function getCronJobInstance($a_job_id) : ilCronStatusMonitorCronJob
    {
        include_once "class.ilCronStatusMonitorCronJob.php";
        return new ilCronStatusMonitorCronJob($this);
    }

    /**
     * Delete the database tables, which were created for the plugin, when the plugin became uninstalled
     */
    protected function afterUninstall() : void
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

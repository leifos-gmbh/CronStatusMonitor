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

declare(strict_types=1);

use Leifos\CronStatusMonitor\Job\CronJob;
use Leifos\CronStatusMonitor\Job\PreviousResults\Repository as PreviousResultsRepository;
use Leifos\CronStatusMonitor\Settings\Settings;
use Leifos\CronStatusMonitor\Job\Notification\Handler as NotificationHandler;
use Leifos\CronStatusMonitor\Job\Notification\Writer as NotificationWriter;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilCronStatusMonitorPlugin extends ilCronHookPlugin
{
    protected const PNAME = "CronStatusMonitor";
    protected const PLUGIN_ID = "cronstatusmonitor";

    public function getPluginName(): string
    {
        return self::PNAME;
    }

    public function getCronJobInstances(): array
    {
        return [$this->getCronJob()];
    }

    public function getCronJobInstance(string $jobId): CronJob
    {
        return $this->getCronJob();
    }

    protected function getCronJob(): CronJob
    {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule('cron');

        return new CronJob(
            $DIC->cron()->repository(),
            $this,
            new PreviousResultsRepository($DIC->database()),
            new Settings($DIC->database()),
            new NotificationHandler(new NotificationWriter($this, $lng))
        );
    }

    protected function afterUninstall(): void
    {
        if ($this->db->tableExists('crn_sts_mtr')) {
            $this->db->dropTable("crn_sts_mtr");
        }

        if ($this->db->tableExists('crn_sts_mtr_settings')) {
            $this->db->dropTable("crn_sts_mtr_settings");
        }
    }
}

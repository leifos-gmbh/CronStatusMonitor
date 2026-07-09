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

namespace Leifos\CronStatusMonitor\Job\Notification;

use ilCronStatusMonitorPlugin;
use ilLanguage;
use ilCronJobEntity;
use ilCronJobResult;
use DateTimeImmutable;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class Writer
{
    public function __construct(
        protected ilCronStatusMonitorPlugin $plugin,
        protected ilLanguage $lng
    ) {
    }

    public function subject(): string
    {
        return $this->plugin->txt("email_subject");
    }

    public function message(ilCronJobEntity ...$faulty_jobs): string
    {
        $first_line = $this->plugin->txt("email_message") . PHP_EOL;

        $job_report_lines = [];
        foreach ($faulty_jobs as $job) {
            $presentable_status = $this->makeStatusPresentable($job->getJobResultStatus());
            if (!$presentable_status) {
                continue;
            }
            $title = $job->getEffectiveTitle();
            $time = new DateTimeImmutable('@' . $job->getJobResultTimestamp());

            $presentable_time = $time
                ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
                ->format('d M Y H:i:s T');

            $job_report_lines[$title] = "  * " . sprintf(
                $this->plugin->txt("email_job_status"),
                $title,
                $presentable_status,
                $presentable_time
            );
        }
        ksort($job_report_lines);

        return implode(PHP_EOL, array_merge([$first_line], $job_report_lines));
    }

    protected function makeStatusPresentable(int $result_status): string
    {
        return match ($result_status) {
            ilCronJobResult::STATUS_INVALID_CONFIGURATION =>
                $this->lng->txt('cron_result_status_invalid_configuration'),
            ilCronJobResult::STATUS_CRASHED =>
                $this->lng->txt('cron_result_status_crashed'),
            ilCronJobResult::STATUS_FAIL =>
                $this->lng->txt('cron_result_status_fail'),
            default => ''
        };
    }
}

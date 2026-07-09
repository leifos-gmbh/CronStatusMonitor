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

namespace Leifos\CronStatusMonitor\Job;

use ilCronJob;
use ilCronJobResult;
use ilCronJobRepository;
use Exception;
use ilCronStatusMonitorPlugin;
use ILIAS\Cron\Schedule\CronJobScheduleType;
use Leifos\CronStatusMonitor\Settings\Settings;
use Leifos\CronStatusMonitor\Job\Notification\Handler as NotificationHandler;
use Leifos\CronStatusMonitor\Job\PreviousResults\Repository as PreviousResultsRepository;
use ilCronJobEntity;
use Leifos\CronStatusMonitor\Validation\MailAdressHelper;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class CronJob extends ilCronJob
{
    use MailAdressHelper;

    protected const NOTEWORTHY_STATUSES = [
        ilCronJobResult::STATUS_CRASHED,
        ilCronJobResult::STATUS_FAIL,
        ilCronJobResult::STATUS_INVALID_CONFIGURATION
    ];

    public function __construct(
        protected ilCronJobRepository $cron_job_repository,
        protected ilCronStatusMonitorPlugin $plugin,
        protected PreviousResultsRepository $previous_results_repo,
        protected Settings $settings,
        protected NotificationHandler $notification_handler
    ) {
    }

    public function getId(): string
    {
        return "cronstatusmonitor";
    }

    public function getTitle(): string
    {
        return $this->plugin->txt("cron_title");
    }

    public function getDescription(): string
    {
        return $this->plugin->txt("cron_description");
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): CronJobScheduleType
    {
        return CronJobScheduleType::SCHEDULE_TYPE_IN_HOURS;
    }

    public function getDefaultScheduleValue(): int
    {
        return 1;
    }

    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();

        try {
            $faulty_jobs = $this->findNewFaultyCronJobs();
            $recipients = $this->settings->getRecipients();
            if ($recipients === []) {
                $result->setStatus(ilCronJobResult::STATUS_INVALID_CONFIGURATION);
                $result->setMessage('No recipients configured');
            } elseif ($faulty_jobs === []) {
                $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
            } else {
                $this->sendMails($recipients, $faulty_jobs);
                $result->setStatus(ilCronJobResult::STATUS_OK);
            }
        } catch (Exception $e) {
            $result->setStatus(ilCronJobResult::STATUS_CRASHED);
            $result->setMessage($e->getMessage());
        }

        return $result;
    }

    /**
     * @return ilCronJobEntity[]
     */
    public function findNewFaultyCronJobs(): array
    {
        $previous_result = $this->previous_results_repo->readAll();
        $new_faulty_jobs = [];
        $still_faulty_jobs = [];
        foreach ($this->cron_job_repository->findAll()->toArray() as $job) {
            if (!in_array($job->getJobResultStatus(), self::NOTEWORTHY_STATUSES, true)) {
                continue;
            }
            if (($previous_result[$job->getJobId()] ?? null) === $job->getJobResultTimestamp()) {
                $still_faulty_jobs[] = $job;
                continue;
            }
            $new_faulty_jobs[] = $job;
        }

        $this->previous_results_repo->deleteAll();
        foreach (array_merge($new_faulty_jobs, $still_faulty_jobs) as $job) {
            $this->previous_results_repo->addResult($job->getJobId(), $job->getJobResultTimestamp());
        }

        return $new_faulty_jobs;
    }

    /**
     * @param string[] $recipients
     * @param ilCronJobEntity[] $faulty_cron_jobs
     */
    public function sendMails(
        array $recipients,
        array $faulty_jobs
    ): void {
        $recipients = $this->filterMailAdresses(...$recipients);
        $this->notification_handler->sendNotification(
            $recipients,
            $faulty_jobs
        );
    }
}

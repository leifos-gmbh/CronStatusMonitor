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

namespace Leifos\CronStatusMonitor\Job\PreviousResults;

use ilDBInterface;
use ilDBConstants;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class Repository
{
    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function deleteAll(): void
    {
        $this->db->manipulate("DELETE FROM crn_sts_mtr");
    }

    public function addResult(string $job_id, int $timestamp): void
    {
        $this->db->insert('crn_sts_mtr', [
            'job_id' => [ilDBConstants::T_TEXT, $job_id],
            'ts' => [ilDBConstants::T_INTEGER, $timestamp]
        ]);
    }

    /**
     * @return array<string, int> timestamp by job ID
     */
    public function readAll(): array
    {
        $query = "SELECT job_id, ts FROM crn_sts_mtr";
        $result = $this->db->query($query);

        $last_results = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $last_results[(string) $row["job_id"]] = (int) $row["ts"];
        }
        return $last_results;
    }
}

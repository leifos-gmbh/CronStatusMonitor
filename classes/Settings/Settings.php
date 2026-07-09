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

namespace Leifos\CronStatusMonitor\Settings;

use ilDBInterface;
use ilDBConstants;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class Settings
{
    /**
     * @var string[]
     */
    protected array $recipients;

    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    /**
     * @return string[]
     */
    public function getRecipients(): array
    {
        return $this->recipients ??= $this->readRecipients();
    }

    public function saveRecipients(string ...$recipients): void
    {
        $recipients = array_filter($recipients, fn($recipient) => $recipient !== "");
        $this->db->replace(
            "crn_sts_mtr_settings",
            ["keyword" => [ilDBConstants::T_TEXT, "email_recipient"]],
            ["value" => ["clob", implode(",", $recipients)]]
        );
    }

    protected function readRecipients(): array
    {
        $query = "SELECT value FROM crn_sts_mtr_settings WHERE keyword = 'email_recipient'";
        $result = $this->db->query($query);

        $recipients = "";
        if ($row = $this->db->fetchAssoc($result)) {
            $recipients = (string) $row["value"];
        }
        return $recipients === "" ? [] : explode(",", $recipients);
    }
}

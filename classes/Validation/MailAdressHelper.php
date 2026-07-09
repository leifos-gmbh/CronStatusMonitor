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

namespace Leifos\CronStatusMonitor\Validation;

use ilMailRfc822AddressParserFactory;
use ilMail;
use Exception;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
trait MailAdressHelper
{
    protected function areMailAdresses(string ...$values): bool
    {
        $filtered = $this->filterMailAdresses(...$values);
        return count($filtered) === count($values);
    }

    /**
     * @return string[]
     */
    protected function filterMailAdresses(string ...$values): array
    {
        $parser_factory = new ilMailRfc822AddressParserFactory();
        $filtered = [];
        foreach ($values as $value) {
            try {
                $parser = $parser_factory->getParser($value);
                $addresses = $parser->parse();
                if (count($addresses) === 1 && $addresses[0]->getHost() !== ilMail::ILIAS_HOST) {
                    $filtered[] = $value;
                }
            } catch (Exception) {
            }
        }
        return $filtered;
    }
}

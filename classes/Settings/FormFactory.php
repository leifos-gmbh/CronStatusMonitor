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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Form\Standard as UIForm;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ilCronStatusMonitorPlugin;
use Leifos\CronStatusMonitor\Validation\MailAdressHelper;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class FormFactory
{
    use MailAdressHelper;

    public function __construct(
        protected UIFactory $factory,
        protected Refinery $refinery,
        protected ilCronStatusMonitorPlugin $plugin,
        protected Settings $settings
    ) {
    }

    /**
     * If valid, form saves values to settings automatically.
     */
    public function get(string $action): UIForm
    {
        $recipients = $this->factory->input()->field()->text(
            $this->plugin->txt("email_recipient"),
            $this->plugin->txt("email_recipient_info")
        )->withValue(implode(", ", $this->settings->getRecipients()))
         ->withAdditionalTransformation($this->listFromStringTransformation())
         ->withAdditionalTransformation($this->listOfMailsConstraint())
         ->withAdditionalTransformation($this->saveListOfMailsTransformation());
        $section = $this->factory->input()->field()->section(
            [$recipients],
            $this->plugin->txt("gui_title")
        );
        return $this->factory->input()->container()->form()->standard(
            $action,
            [$section]
        );
    }

    protected function listFromStringTransformation(): Transformation
    {
        $callable = function (string $input) {
            $res = [];
            foreach (explode(",", $input) as $value) {
                if (trim($value) !== "") {
                    $res[] = trim($value);
                }
            }
            return $res;
        };
        return $this->refinery->custom()->transformation($callable);
    }

    protected function saveListOfMailsTransformation(): Transformation
    {
        $callable = function (array $input) {
            $this->settings->saveRecipients(...$input);
        };
        return $this->refinery->custom()->transformation($callable);
    }

    protected function listOfMailsConstraint(): Transformation
    {
        $callable = function (array $input) {
            return $input === [] || $this->areMailAdresses(...$input);
        };
        return $this->refinery->custom()->constraint(
            $callable,
            $this->plugin->txt("email_recipient_error")
        );
    }
}

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

use Leifos\CronStatusMonitor\Settings\FormFactory;
use Leifos\CronStatusMonitor\Settings\Settings;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as UIForm;
use ILIAS\Http\Services as Http;

/**
 * @ilCtrl_isCalledBy ilCronStatusMonitorConfigGUI: ilObjComponentSettingsGUI
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilCronStatusMonitorConfigGUI extends ilPluginConfigGUI
{
    protected ilCtrl $ctrl;
    protected Http $http;
    protected ilGlobalTemplateInterface $tpl;
    protected UIRenderer $ui_renderer;
    protected ilLanguage $lng;
    protected FormFactory $form_factory;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->lng = $DIC->language();
    }

    // Needs lazy init, plugin isn't set in the constructor.
    protected function getFormFactory(): FormFactory
    {
        global $DIC;

        return $this->form_factory ??= new FormFactory(
            $DIC->ui()->factory(),
            $DIC->refinery(),
            $this->plugin_object,
            new Settings($DIC->database())
        );
    }

    public function performCommand(string $cmd): void
    {
        switch ($cmd) {
            case 'save':
                $this->save();
                break;

            case 'configure':
            default:
                $this->configure();
                break;
        }
    }

    protected function buildForm(): UIForm
    {
        $action = $this->ctrl->getLinkTarget($this, "save");
        return $this->getFormFactory()->get($action);
    }

    public function configure(): void
    {
        $form = $this->buildForm();
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    public function save(): void
    {
        $form = $this->buildForm()->withRequest($this->http->request());
        if ($form->getData()) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt("settings_saved"),
                true
            );
            $this->ctrl->redirect($this, "configure");
        }
        $this->tpl->setContent($this->ui_renderer->render($form));
    }
}

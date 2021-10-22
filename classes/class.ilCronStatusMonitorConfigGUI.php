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

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * Class ilCronStatusMonitorConfigGUI
 * @author Thomas Famula <famula@leifos.de>
 */
class ilCronStatusMonitorConfigGUI extends ilPluginConfigGUI
{
    /**
     * @param string $cmd
     *
     * Handles all commands, default is "configure"
     */
    public function performCommand($cmd) : void
    {
        switch ($cmd) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * Show settings screen
     */
    public function configure(?ilPropertyFormGUI $form = null) : void
    {
        global $tpl;
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initConfigurationForm();
        }
        $tpl->setContent($form->getHTML());
    }

    public function initConfigurationForm() : ilPropertyFormGUI
    {
        global $ilCtrl, $lng;

        //create the form
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->getPluginObject()->txt("gui_title"));

        //add button
        $form->addCommandButton("save", $lng->txt("save"));

        //text input
        include_once("./Customizing/global/plugins/Services/Cron/CronHook/CronStatusMonitor/classes/class.ilCronStatusMonitorSettings.php");
        $setting = new ilCronStatusMonitorSettings();
        $text = new ilTextInputGUI($this->getPluginObject()->txt("email_recipient"), "email_recipient");
        $text->setValue($setting->get("email_recipient"));
        $text->setInfo($this->getPluginObject()->txt("email_recipient_info"));
        $text->setRequired(true);
        $form->addItem($text);

        return $form;
    }

    public function save() : void
    {
        global $lng, $ilCtrl;
        $form = $this->initConfigurationForm();

        if ($form->checkInput()) {
            include_once("./Customizing/global/plugins/Services/Cron/CronHook/CronStatusMonitor/classes/class.ilCronStatusMonitorSettings.php");
            $setting = new ilCronStatusMonitorSettings();
            $setting->setList($form->getInput("email_recipient"));
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "configure");
        }

        //$form->setValuesByPost();
        $this->configure($form);
    }
}

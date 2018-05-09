<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * Class ilCronStatusMonitorConfigGUI
 * @author Thomas Famula <famula@leifos.de>
 */
class ilCronStatusMonitorConfigGUI extends ilPluginConfigGUI
{
    /**
     * @param $cmd
     *
     * Handles all commmands, default is "configure"
     */
    function performCommand($cmd)
    {
        switch ($cmd)
        {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * @param ilPropertyFormGUI|null $form
     *
     * Show settings screen
     */
    function configure(ilPropertyFormGUI $form = null)
    {
        global $tpl;
        if(!$form instanceof ilPropertyFormGUI)
        {
            $form = $this->initConfigurationForm();
        }
        $tpl->setContent($form->getHTML());
    }

    /**
     * @return ilPropertyFormGUI
     *
     * Init configuration form
     */
    function initConfigurationForm()
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
        $text = new ilTextInputGUI($this->getPluginObject()->txt("email_recipient"),"email_recipient");
        $text->setValue($setting->get("email_recipient"));
        $text->setInfo($this->getPluginObject()->txt("email_recipient_info"));
        $text->setRequired(true);
        $form->addItem($text);

        return $form;
    }

    /**
     * Save settings
     */
    function save()
    {
        global $lng, $ilCtrl;
        $form = $this->initConfigurationForm();

        if ($form->checkInput())
        {
            include_once("./Customizing/global/plugins/Services/Cron/CronHook/CronStatusMonitor/classes/class.ilCronStatusMonitorSettings.php");
            $setting = new ilCronStatusMonitorSettings();
            $setting->setList($form->getInput("email_recipient"));
            ilUtil::sendSuccess($lng->txt("settings_saved"),true);
            $ilCtrl->redirect($this,"configure");
        }

        //$form->setValuesByPost();
        $this->configure($form);
    }

}
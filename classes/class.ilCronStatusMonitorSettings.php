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

/**
 * Class ilCronStatusMonitorSettings
 * @author Thomas Famula <famula@leifos.de>
 */
class ilCronStatusMonitorSettings
{
    protected $db;
    public $setting = array();

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();

        $ilDB = $DIC->database();

        // check whether ini file object exists
        if (!is_object($ilDB)) {
            die("Fatal Error: ilSettings object instantiated without DB initialisation.");
        }

        $this->read();
    }

    /**
     * Read all settings, which were made in the plugin configuration, from the database and put them in an array
     */
    public function read() : void
    {
        $ilDB = $this->db;

        $query = "SELECT * FROM crn_sts_mtr_settings";
        $result = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($result)) {
            $this->setting[$row["keyword"]] = $row["value"];
        }
    }

    /**
     * @return bool|array
     * Check if settings were made in the plugin configuration and return them
     */
    public function get(string $a_keyword, bool $a_default_value = false)
    {
        return $this->setting[$a_keyword] ?? $a_default_value;
    }

    /**
     * Put made settings from the plugin configuration into the database
     */
    public function set(string $a_key, string $a_val) : void
    {
        $ilDB = $this->db;

        $ilDB->replace(
            "crn_sts_mtr_settings",
            array(
            "keyword" => array("text", $a_key)),
            array(
            "value" => array("clob", $a_val))
        );

        $this->setting[$a_key] = $a_val;
    }

    /**
     * Check if the entered account logins in the plugin configuration are valid and accept only the valid ones
     */
    public function setList(string $a_list) : void
    {
        $list = explode(",", $a_list);
        $accounts = array();
        foreach ($list as $l) {
            if (ilObjUser::_lookupId(trim($l)) > 0) {
                $accounts[] = trim($l);
            }
        }

        $this->set("email_recipient", implode(",", $accounts));
    }
}

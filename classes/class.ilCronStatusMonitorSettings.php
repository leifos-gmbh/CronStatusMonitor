<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronStatusMonitorSettings
 * @author Thomas Famula <famula@leifos.de>
 */
class ilCronStatusMonitorSettings
{
    protected $db;
    public $setting = array();

    /**
     * ilCronStatusMonitorSettings constructor.
     */
    function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();

        $ilDB = $DIC->database();

        // check whether ini file object exists
        if (!is_object($ilDB))
        {
            die ("Fatal Error: ilSettings object instantiated without DB initialisation.");
        }

        $this->read();
    }

    /**
     * Read all settings, which were made in the plugin configuration, from the database and put them in an array
     */
    function read()
    {
        $ilDB = $this->db;

        $query = "SELECT * FROM crn_sts_mtr_settings";
        $result = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($result))
        {
            $this->setting[$row["keyword"]] = $row["value"];
        }

    }

    /**
     * @param $a_keyword
     * @param bool $a_default_value
     * @return bool|array
     *
     * Check if settings were made in the plugin configuration and return them
     */
    function get($a_keyword, $a_default_value = false)
    {
        if (isset($this->setting[$a_keyword]))
        {
            return $this->setting[$a_keyword];
        }
        else
        {
            return $a_default_value;
        }
    }

    /**
     * @param $a_key
     * @param $a_val
     *
     * Put made settings from the plugin configuration into the database
     */
    function set($a_key, $a_val)
    {
        $ilDB = $this->db;

        $ilDB->replace("crn_sts_mtr_settings",
            array(
            "keyword" => array("text", $a_key)),
            array(
            "value" => array("clob", $a_val))
            );

        $this->setting[$a_key] = $a_val;

    }

    /**
     * @param $a_list
     *
     * Check if the entered account logins in the plugin configuration are valid and accept only the valid ones
     */
    function setList($a_list)
    {
        $list = explode(",", $a_list);
        $accounts = array();
        foreach ($list as $l)
        {
            if (ilObjUser::_lookupId(trim($l)) > 0)
            {
                $accounts[] = trim($l);
            }
        }

        return $this->set("email_recipient", implode(",", $accounts));
    }
}
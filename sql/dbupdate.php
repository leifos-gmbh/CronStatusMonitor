<#1>
<?php

if (!$ilDB->tableExists('crn_sts_mtr'))
{
    $ilDB->createTable('crn_sts_mtr', array(
        'job_id' => array(
            'type' => 'text',
            'length' => 50,
            'notnull' => true
        ),
        'job_result_status' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
    ));
    $ilDB->addPrimaryKey('crn_sts_mtr',array('job_id'));
}

?>

<#2>
<?php

if (!$ilDB->tableExists('crn_sts_mtr_settings'))
{
    $ilDB->createTable('crn_sts_mtr_settings', array(
        'keyword' => array(
            'type' => 'text',
            'length' => 50,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'clob',
            'notnull' => false
        ),
    ));
    $ilDB->addPrimaryKey('crn_sts_mtr_settings',array('keyword'));
}

?>

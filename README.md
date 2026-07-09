# CronStatusMonitor

CronStatusMonitor is a cron job plugin that sends out
mail notifications when cron jobs in ILIAS crash, or
otherwise report an error status.

**Minimum ILIAS Version:** 10.0

**Maximum ILIAS Version:** 10.999

**Responsible Developer:** Thomas Famula - famula AT leifos.com

**Supported Languages:** German, English

### Change Notes

- With the upgrade to ILIAS 10, CronStatusMonitor will
  send notifications as external mails only. Because of
  this, ILIAS logins are not accepted as recipients
  anymore. Existing configuration is reset, recipients
  have to be re-added.
- Notifications will now also be sent for cron jobs
  with status 'Failed' and 'Invalid Configuration', not
  only 'Crashed'.


### Quick Installation Guide

1. Copy the content of this folder in <ILIAS_directory>/public/Customizing/global/plugins/Services/Cron/CronHook/CronStatusMonitor

2. Access to ILIAS and go to *Administration > Extending ILIAS > Plugins* in the Mainbar.

3. Look for the CronStatusMonitor plugin in the list and select "Install" in the "Actions" dropdown.

4. When ILIAS has installed the plugin, select "Install" in the "Actions" dropdown.

5. "Refresh Languages" in the "Actions" dropdown to update the language files.

6. Select "Configure" in the "Actions" dropdown to set the users which will receive messages about crashed cron-jobs. Hit the "Save" button.

7. Go to *Administration > System Settings and Maintenance > General Settings* in the Mainbar.

8. Go to the "Cron Jobs" tab.

9. Look for the CronStatusMonitor cron-job and select "Activate".

10. Look again for the CronStatusMonitor cron-job and select "Edit".

11. Schedule how often the cron-job should run (default is hourly). Hit the "Save" button.

12. Now, ILIAS will check if a cron-job crashed and sends a mail to the selected users with this information.

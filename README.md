# CronStatusMonitor

CronStatusMonitor is a cron-job plugin to identify if active cron-jobs crashed and informs selected users about them by mail.

**Minimum ILIAS Version:** 5.2.0

**Maximum ILIAS Version:** 7.999

**Responsible Developer:** Thomas Famula - famula AT leifos.com

**Supported Languages:** German, English


### Quick Installation Guide

1. Copy the content of this folder in <ILIAS_directory>/Customizing/global/plugins/Services/Cron/CronHook/CronStatusMonitor

2. Access to ILIAS and go to *Administration > Extending ILIAS > Plugins* in the Mainbar.

3. Look for the CronStatusMonitor plugin in the table and hit the "Actions" dropdown and select "Install".

4. When ILIAS has installed the plugin, hit the "Actions" dropdown again and select "Activate".

5. Hit the "Actions" dropdown and select "Refresh Languages" to update the language files.

6. Hit the "Actions" dropdown and select "Configure" to set the users which will receive messages about crashed cron-jobs. Hit the "Save" button.

7. Go to *Administration > System Settings and Maintenance > General Settings* in the Mainbar.

8. Hit the "Cron Jobs" tab.

9. Look for the CronStatusMonitor cron-job and select "Activate".

10. Look again for the CronStatusMonitor cron-job and select "Edit".

11. Schedule how often the cron-job should run (default is hourly). Hit the "Save" button.

12. Now, ILIAS will check if a cron-job crashed and sends a mail to the selected users with this information.

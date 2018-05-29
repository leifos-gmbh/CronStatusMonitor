# CronStatusMonitor

CronStatusMonitor is a cron-job plugin to identify if active cron-jobs crashed and informs selected users about them by mail.

**Minimum ILIAS Version:** 5.2.0

**Maximum ILIAS Version:** 5.3.999

**Responsible Developer:** Thomas Famula - famula AT leifos.com

**Supported Languages:** German, English


### Quick Installation Guide

1. Copy the content of this folder in <ILIAS_directory>/Customizing/global/plugins/Services/Cron/Cronhook/CronStatusMonitor

2. Access to ILIAS and go to the administration page.

3. Select "Plugins" in the menu.

4. Look for the CronStatusMonitor plugin in the table and hit the "Action" button and select "Update".

5. When ILIAS updates the plugin, hit the "Action" button and select "Activate" that will appear instead of the "Update" link.

6. Hit the "Action" button and select "Refresh Languages" to update the language files.

7. Hit the "Action" button and select "Configure" to set the users which will recieve messages about crashed cron-jobs. Hit the "Save" button.

8. Go back to the administration page.

9. Select "General Settings" in the menu.

10. Hit the "Cron Jobs" tab.

11. Look for the CronStatusMonitor cron-job and select "Activate".

12. Look again for the CronStatusMonitor cron-job and select "Edit".

13. Schedule how often the cron-job should run (default is hourly). Hit the "Save" button.

14. Now, ILIAS will check if a cron job crashed and sends a mail to the selected users with this information.

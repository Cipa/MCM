#MCM

###A simple module for Clipper CMS and MODx Evolution CMS to send mailchimp campaigns from the CMS.

###Only works with campaigns generated from pages inside the CMS

##INSTALL
- Create a new mcm folder inside the modules folder and copy all the files
- Create a new module named "MCM" or "MailChimp" and paste this code 
```
require_once($modx->config['base_path'] . "assets/modules/mcm/mailchimp.php");
```
- Configure the module with 
```
&moduleFolder=Module Folder;text;mcm &apiKey=API Key;text;YOUR API KEY &templateIds=Allowed Templates;text;0
```

##Notes:
- this is not a full MailChimp integration module and might not work with all MailChimp setups
- send at least one campaign from MailChimp before you use this module
- you can only send documets that have a template id present in the "Allowed Templates" section
- make sure you have a valid mailchimp list before you try the module
- not all mailchim groups are supported. Further testing needed.
- campaings sent from the module will be sent from the site administrator email(Tools/Configuration/Email Address)

###Roadmap:
- better documentation
- better design

# Gravity Forms Lead Enrichment Add-On
Integrates Gravity Forms with the Ninthlink Enrichment System ("nes") allowing form submissions to be automatically sent for further enrichment from TowerData and beyond.

Note: this is the add-on to route a particular Gravity Forms form's submissions in to nes, not to actually make the enrichment happen inside nes. That would be some place else.

## Installation

This plugin is an add-on for Gravity Forms, so before installing this plugin, make sure you have Gravity Forms installed and activated on the site.

After that, follow these steps:

1. Download this WordPress plugin, and upload to `/wp-content/plugins/gforms-lead-enrichment/`
2. Activate the plugin
3. Go to the plugin Gravity Forms Settings at `/wp-admin/admin.php?page=gf_settings&subview=gforms-lead-enrichment` and enter the following:
    * API Endpoint Base URL: `http://dev-nes.pantheonsite.io/`
    * Site ID Key: This is a specific Site ID Key tied to the particular site you are trying to activate this on. If you did not receive this information, please contact wpadmin@ninthlink.com
4. Click the `Update Settings` button to save those settings.
5. Go to the particular Gravity Forms form that you want to have Lead Enrichment
6. In the Settings for that particular form, click on "Lead Enrichment"
7. Click the `Add New` or `create one!` link to create a new Feed.
8. Fill out the information there, and map fields for Lead Enrichment. At a minimum, you should provide an Email address.

### Running Enrichment

The `Lead Enrichment` Settings give you the option to specify "Default Enrichment" which is used for all Lead Enrichment form processing unless the form itself overrides with a different setting.

The options are:

1. Now = Process the Lead through the Enrichment systems immediately
2. Later = Save the Lead, but do not run Enrichment at this time
3. Never = Save the Lead, but do not run Enrichment. Ever?
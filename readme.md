Remarkable CakePHP Plugin (work in progress, use at your own risk)
==================================================================

**Easily enable remarking on any Model Object.**

Notes:
------

* To associate User model with user_id, need to add 'hasMany' association manually to model.
* Remark model needs to be adjusted for User model association (move into a config somehow?).
* The sample view elements will use jQuery for some AJAX stuff.
* SoftDeletable must be included separately and then the config must be set.

TODO Before Releasing:
----------------------

1. Better sample view files.
1. Move methods in `app_controller.php` to an abstracted controller in the plugin.
1. Move some of the settings to a config (global stuff, like softDeletable integration).
1. Finish installation and usage instructions.
1. Write tests for behavior and such.

Installation (WIP):
-------------------

1. There are a few options to add the plugin: 

	a. Clone code from Github (https://github.com/DailyPath/Remarkable-Plugin);

		$ git clone git@github.com:DailyPath/Remarkable-Plugin.git /path/to/desired/plugin/directory

	b. Add as a submodule:
		
		$ git submodule add git@github.com:DailyPath/Remarkable-Plugin.git /path/to/desired/plugin/directory
		$ git submodule init

	c. Download and place it in your desired directory:

		https://github.com/DailyPath/Remarkable-Plugin/zipball/master

1. Setup Remark table

	`$ cake schema create Remarkable.remark`

Usage Examples:
---------------
# SMF Tools repository

This repository contains a few useful scripts for SMF, such as install/upgrade or repair scripts, database cleaning up, etc.

All scripts in this repository are under BSD 3-clause license, unless specified otherwise.
File|Description
---|---
**databasecleanup**|Analyses a database and compares it to a fresh install.  Displays added settings and columns with options to remove.
**install_script**|A template that can be used to create manual installation scripts for mods. At the moment the hook part is fully working, the database part is still WIP.
**ban_script.php**|A script that allows perform multiple user banning at once. You can provide a list of usernames that you want to ban or you can ask the script to scan a board you have collected all the users you want to ban in (the name must be the subject of the topic).
**fix_packages.php**|After a large upgrade (to cleanup forum) the mods are still marked as installed, with this script you can invert that state.
**Populate.php**|a script that can be used to populate a forum with dummy users (usually useful for testing), originaly written by [SlammedDime](http://code.mattzuba.com/populator)
**repair_settings.php**|A script that can detect the correct value for a number of fields and settings on your forum.  Useful to fix broken installs.
**smfinfo.php**|A script that will provide detailed information to help with support issues. Output includes details of the system, PHP, file versions, database, error log, and mods installed.  Can provide password access to output for trusted users.
**status.php**|A script that can be used to analyse your database performance and provide suggestions on how to improve settings (experimental)

### Tools download

Apart from cloning the repo, you can find the files more useful to end-users at the [download page](https://github.com/emanuele45/tools/downloads)

### How to contribute:
* fork the repository. If you are not used to Github, please check out [fork a repository](http://help.github.com/fork-a-repo).
* branch your repository, to commit the desired changes.
* sign-off your commits, to acknowledge your submission under the license of the project.
  * an easy way to do so, is to define an alias for the git commit command, which includes `-s` switch (reference: [How to create Git aliases](http://githacks.com/post/1168909216/how-to-create-git-aliases))
* send a pull request to us.

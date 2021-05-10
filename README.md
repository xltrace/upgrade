# Hades Upgrade

This script is meant to be a single file upgrader. Able to reconstruct the relevant configuration of the application.

```
$ php -f upgrade.php upgrade.json
```

## upgrade.json

Each file postfixed with `upgrade.json` can be accepted by the script to run.

```json
{
	".": "https://path.to/somewhere/raw/",
	"upgrade.php": true
}
```

`.` can refer to a raw collection like one github provides, or any other repository. You can include files and directories.

| short | alias of instructions | description |
| ---- | ---- | ---- |
| `true` | `{"create":true,"clear":true}` | file or map will be completely replaced |
| `null` | `{"create":true,"clear":false}` | file or map will be created if not yet exists |
| `false` | `{"delete":true}` | file or map (and all its contents) will be removed |
| *target* | `{"move":"*target*"}` | file or map will be renamed to *target* |

Within the instructions you could include other functional flags: `mtime`, `chmod`, `user`, `group`. The file `composer.phar` can be combined with the flags: `upgrade` (for self-upgrade), `update` (for (re-)install).

NOTE: the above *target*, `user`, `group` is not yet implemented!

## \XLtrace\Hades\upgrade($file)

This method allows to execute a particular upgrade-request.

## \XLtrace\Hades\composer($action)

This method enables a wrapper-function to **composer.phar**. The actions *install* and *self-update* have been tested. Other actions are experimental!

## \XLtrace\Hades\backup($file=NULL, $mode=TRUE)

This method creates an zip-archive to save (a selection of) the application-directory. When no $file is given, it will return the raw data of the zip-archive.

| $file | $mode | description | result |
| ----- | ---- | ---- | ---- |
| *filename* | - / `TRUE` | sets the filename, and (default) `$mode=TRUE` | `{"file":"`*filename*`","all":true}` |
| `{...}` | - || assumes the input is a welformed configuration array, to set as $mode |
|| `TRUE` | selects all files: `{"all":true}` | `{"all":true,"select":[`...`]}` |
|| `NULL` | selects all upgradable files: `{"upgradable":true}` | `{"upgradable":true,"select":[`...`]}` |
|| `FALSE` | selects all non-upgradable files: `{"upgradable":false}` | `{"upgradable":false,"select":[`...`]}` |

With `$mode = \XLtrace\Hades\backup_conf()` &mdash; called by backup &mdash; you improve the configuration of the backup-method, into an **array**. 

| flag | type | description |
| ---- | ---- | ---- |
| file | uri | path to the zip-file |
| all | `TRUE` | shortcut to select all files |
| upgradable | bool | shortcut to select the (non-)upgradable files, based upon the `*upgrade.json`-files |
| by | uri | path to the `*upgrade.json` file, used to populate the select-array |
| select | string | fixes the forgotten setting of *by* |
| select | array | a list of files to include in the archive |
| debug | `TRUE` | adds flags to debug the shortcut *upgradable*: `upgrade.json` + `upgrade-files` |
| ignore | array | a list of files/maps to ignore the search. By default `.git/` and `vendor/` is ignored |

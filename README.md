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

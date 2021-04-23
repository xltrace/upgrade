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

## \XLtrace\Hades\upgrade($file)

This method allows to execute a particular upgrade-request.

## \XLtrace\Hades\composer($action)

This method enables a wrapper-function to **composer.phar**. The actions *install* and *self-update* have been tested. 

# Hades Upgrade

This script is meant to be a single file upgrader. Able to reconstruct the relevant configuration of the application.

```
$ php -f upgrade.php upgrade.json
```

## upgrade.json

Each file postfixed with `upgrade.json` can be accepted by the script to run.

```json
{
	".":"https://path.to/somewhere/raw/"
}
```

`.` can refer to a raw collection like one github provides

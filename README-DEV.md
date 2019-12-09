## First use

* `cp .env.dist .env`
* Linux case
  * comment line in .env `PHP_TAG=7.2-dev-macos-4.12.8` and comment out `PHP_TAG=7.2-dev-4.12.8`
  * comment out docker-compose.yml `#PHP_XDEBUG_REMOTE_HOST: 172.17.0.1 # Linux`
* `docker-compose up -d`
* `./composer.sh install`
* `cp web/sites/example.settings.local.php web/sites/default/settings.local.php`
* Add `$databases` array to `web/sites/default/settings.local.php`
```php
$databases['default']['default'] = [
  'database' => getenv('DB_NAME'),
  'driver' => 'mysql',
  'host' => getenv('DB_HOST'),
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'password' => getenv('DB_PASSWORD'),
  'prefix' => '',
  'username' => getenv('DB_USER'),
];

$config_directories = array(
  CONFIG_SYNC_DIRECTORY => '../config/sync',
);
```
* Uncomment these lines:
```php
# if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
#   include $app_root . '/' . $site_path . '/settings.local.php';
# }
```
* Run `./drush.sh si swiss_army_knife`

## Before development

* `./composer.sh install`

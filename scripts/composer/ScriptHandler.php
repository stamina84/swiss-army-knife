<?php

/**
 * @file
 * Contains \DrupalProject\composer\ScriptHandler.
 */

namespace DrupalProject\composer;

use Alchemy\Zippy\Exception\IOException;
use Composer\Script\Event;
use Composer\Semver\Comparator;
use Drupal\Core\Site\SettingsEditor;
use DrupalFinder\DrupalFinder;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;
use Composer\Util\Platform;
use Composer\Util\Filesystem as ComposerFilesystem;

class ScriptHandler {

  public static function createRequiredFiles(Event $event) {
    $fs = new Filesystem();
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(getcwd());
    $drupalRoot = $drupalFinder->getDrupalRoot();

    $dirs = [
      'modules',
      'profiles',
      'themes',
    ];

    // Required for unit testing
    foreach ($dirs as $dir) {
      if (!$fs->exists($drupalRoot . '/'. $dir)) {
        $fs->mkdir($drupalRoot . '/'. $dir);
        $fs->touch($drupalRoot . '/'. $dir . '/.gitkeep');
      }
    }

    // Prepare the settings file for installation
    if (!$fs->exists($drupalRoot . '/sites/default/settings.php') and $fs->exists($drupalRoot . '/sites/default/default.settings.php')) {
      $fs->copy($drupalRoot . '/sites/default/default.settings.php', $drupalRoot . '/sites/default/settings.php');
      require_once $drupalRoot . '/core/includes/bootstrap.inc';
      require_once $drupalRoot . '/core/includes/install.inc';
      $settings['config_sync_directory'] = Path::makeRelative($drupalFinder->getComposerRoot() . '/config/sync', $drupalRoot);
      SettingsEditor::rewrite($drupalRoot . '/sites/default/settings.php', $settings);
      $fs->chmod($drupalRoot . '/sites/default/settings.php', 0666);
      $event->getIO()->write("Create a sites/default/settings.php file with chmod 0666");
    }

    // Create the files directory with chmod 0777
    if (!$fs->exists($drupalRoot . '/sites/default/files')) {
      $oldmask = umask(0);
      $fs->mkdir($drupalRoot . '/sites/default/files', 0777);
      umask($oldmask);
      $event->getIO()->write("Create a sites/default/files directory with chmod 0777");
    }
  }

  /**
   * Checks if the installed version of Composer is compatible.
   *
   * Composer 1.0.0 and higher consider a `composer install` without having a
   * lock file present as equal to `composer update`. We do not ship with a lock
   * file to avoid merge conflicts downstream, meaning that if a project is
   * installed with an older version of Composer the scaffolding of Drupal will
   * not be triggered. We check this here instead of in drupal-scaffold to be
   * able to give immediate feedback to the end user, rather than failing the
   * installation after going through the lengthy process of compiling and
   * downloading the Composer dependencies.
   *
   * @see https://github.com/composer/composer/pull/5035
   */
  public static function checkComposerVersion(Event $event) {
    $composer = $event->getComposer();
    $io = $event->getIO();

    $version = $composer::VERSION;

    // The dev-channel of composer uses the git revision as version number,
    // try to the branch alias instead.
    if (preg_match('/^[0-9a-f]{40}$/i', $version)) {
      $version = $composer::BRANCH_ALIAS_VERSION;
    }

    // If Composer is installed through git we have no easy way to determine if
    // it is new enough, just display a warning.
    if ($version === '@package_version@' || $version === '@package_branch_alias_version@') {
      $io->writeError('<warning>You are running a development version of Composer. If you experience problems, please update Composer to the latest stable version.</warning>');
    }
    elseif (Comparator::lessThan($version, '1.0.0')) {
      $io->writeError('<error>Drupal-project requires Composer version 1.0.0 or higher. Please update your Composer before continuing</error>.');
      exit(1);
    }
  }


  /**
   * Places openscholar profile in correct location.
   */
  public static function placeProfile(Event $event) {
    $fs = new ComposerFilesystem();
    $io = $event->getIO();
    $fileList = [
      'swiss_army_knife.info.yml' => 'swiss_army_knife.info.yml',
      'swiss_army_knife.profile' => 'swiss_army_knife.profile',
      'config' => 'config',
      'profile' . DIRECTORY_SEPARATOR . 'modules' => 'modules',
      'profile' . DIRECTORY_SEPARATOR . 'themes' => 'themes',
    ];
    $root = realpath($event->getComposer()->getPackage()->getDistUrl());
    $path = $root . '/web/profiles/contrib/swiss_army_knife';

    try {
      foreach ($fileList as $orig_file => $file) {
        $orig = $root . DIRECTORY_SEPARATOR . $orig_file;
        $link = $path . DIRECTORY_SEPARATOR . $file;
        if (Platform::isWindows() && is_dir($orig)) {
          if (file_exists($link)) {
            if ($fs->isJunction($link)) {
              $io->writeError(sprintf("Removing junction from %s\n", $file));
              $fs->removeJunction($link);
            }
            elseif (is_dir($link)) {
              $fs->removeDirectory($link);
            }
            else {
              $fs->unlink($link);
            }
          }

          $io->writeError(sprintf("Junctioning from %s\n", $file), FALSE);
          $fs->junction($orig, $link);
        }
        else {
          $path = rtrim($path, DIRECTORY_SEPARATOR);
          $io->writeError(sprintf("Symlinking from %s\n", $file), FALSE);
          $fs->ensureDirectoryExists(dirname($link));
          $fs->relativeSymlink($orig, $link);
        }
      }
    }
    catch (IOException $e) {
      throw new \RuntimeException(sprintf('Symlink from "%s" to "%s" failed!', $root, $path));
    }
  }

}

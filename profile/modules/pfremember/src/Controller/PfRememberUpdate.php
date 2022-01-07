<?php

namespace Drupal\pfremember\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class PfRememberUpdate.
 *
 * @package Drupal\pfremember\Controller
 */
class PfRememberUpdate extends ControllerBase {

  /**
   * Process given hash and update remember item.
   *
   * @param string $hash
   *   Secret hash generated from remember.
   *
   * @return mixed
   *   Build array.
   */
  public function processHash(string $hash) {
    $build['main'] = [
      '#type' => 'markup',
      '#markup' => $hash,
    ];

    return $build;
  }

}

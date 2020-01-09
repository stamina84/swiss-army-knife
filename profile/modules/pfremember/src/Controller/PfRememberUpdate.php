<?php

namespace Drupal\pfremember\Controller;

use Drupal\Core\Controller\ControllerBase;

class PfRememberUpdate extends ControllerBase {

  public function processHash($hash) {
    $build['main'] = [
      '#type' => 'markup',
      '#markup' => $hash,
    ];

    return $build;
  }

}

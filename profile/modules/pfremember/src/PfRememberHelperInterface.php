<?php

namespace Drupal\pfremember;

use Drupal\node\NodeInterface;

interface PfRememberHelperInterface {

  /**
   * Search remember node by hash.
   *
   * @param string $hash
   *   Target hash string.
   *
   * @return NodeInterface|NULL
   *   Found remember entity.
   */
  public function getRemember(string $hash): ?NodeInterface;

}

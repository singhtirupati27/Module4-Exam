<?php

namespace Drupal\product_module\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns response for product module routing.
 */
class ProductModuleController extends ControllerBase {

  /**
   * Function to show message to users.
   */
  public function build() {
    return [
      '#type' => '#markup',
      '#markup' => $this->t('Thank you for buying.'),
    ];
  }

}

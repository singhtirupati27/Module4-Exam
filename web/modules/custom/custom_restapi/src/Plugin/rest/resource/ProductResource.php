<?php

namespace Drupal\custom_restapi\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a SampleGetRestResource.
 *
 * @RestResource(
 *   id = "product_resource",
 *   label = @Translation("Product Resource"),
 *   uri_paths = {
 *     "canonical" = "/custom_restapi/product_resource"
 *   }
 * )
 */
class ProductResource extends ResourceBase {

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    $storage = \Drupal::service('entity_type.manager')->getStorage('node');
    $node_id = $storage->getQuery()
      ->condition('type', 'product')
      ->condition('status', 1)
      ->accessCheck('FALSE')
      ->execute();
    $nodes = $storage->loadMultiple($node_id);
    $response = ['product_data' => $nodes];
    return new ResourceResponse($response);
  }

}

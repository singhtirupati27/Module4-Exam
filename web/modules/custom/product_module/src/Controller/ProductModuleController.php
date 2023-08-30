<?php

namespace Drupal\product_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

/**
 * Returns response for product module routing.
 */
class ProductModuleController extends ControllerBase {

  /**
   * Function to show message to users.
   */
  public function build() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Thank you for buying.'),
    ];
  }

  /**
   * Function to show ordered product details and thank you message to user.
   */
  public function orderProduct($id) {
    $username = \Drupal::currentUser()->getDisplayName();
    // $node = \Drupal::service('entity_type.manager')->getStorage('node');
    // $node_data = $node->load($id);
    // $title = $node_data->title->value;
    // $price = $node_data->field_price->value;
    // $description = $node_data->field_description->value;
    // $image = $node_data->field_product_image->target_id;
    $node = Node::load($id);
    $title = $node->getTitle();
    $description = $node->get('field_description')->getValue()[0]['value'];
    $price = $node->get('field_price')->getValue()[0]['value'];
    $image = $node->get('field_product_image')->getValue()[0]['target_id'];
    $image = File::load($image);
    $image_path = $image->get('uri')->getValue()[0]['value'];
    // $image_path = $image->get('filename')->getValue()[0]['value'];
    // $image_path = $image->getFileUri();
    $image_path = str_replace('public:/', '', $image_path);
    $image_url = '/sites/default/files/styles/product_image_/public' . $image_path;
    $image_tag = '<img src="' . $image_url . '" alt="Product images">';
    $order = '<div class="order-data"><p>Title: ' . $title . '<br>Description: ' . $description . '<br>Price: Rs ' . $price . '/-<br>Images: ' . $image_tag . '</p></div>';

    return [
      '#markup' => t('Thank you ' . $username .' for your order!'),
      'order_info' => [
        '#markup' => t($order),
      ],
    ];
  }

}

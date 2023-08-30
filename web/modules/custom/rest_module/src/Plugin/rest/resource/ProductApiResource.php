<?php

namespace Drupal\rest_module\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Represents Product Api records as resources.
 *
 * @RestResource (
 *   id = "rest_module_product_api",
 *   label = @Translation("Product Api"),
 *   uri_paths = {
 *     "canonical" = "/api/rest-module-product-api",
 *     "create" = "/api/rest-module-product-api"
 *   }
 * )
 *
 * @DCG
 * The plugin exposes key-value records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. An
 * example of such configuration can be located in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively you can enable it through admin interface provider by REST UI
 * module.
 * @see https://www.drupal.org/project/restui
 *
 * @DCG
 * Notice that this plugin does not provide any validation for the data.
 * Consider creating custom normalizer to validate and normalize the incoming
 * data. It can be enabled in the plugin definition as follows.
 * @code
 *   serialization_class = "Drupal\foo\MyDataStructure",
 * @endcode
 *
 * @DCG
 * For entities, it is recommended to use REST resource plugin provided by
 * Drupal core.
 * @see \Drupal\rest\Plugin\rest\resource\EntityResource
 */
class ProductApiResource extends ResourceBase {

  /**
   * The key-value storage.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $storage;

  /**
   * Entity storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    KeyValueFactoryInterface $keyValueFactory,
    EntityTypeManager $entity,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $keyValueFactory);
    $this->storage = $keyValueFactory->get('rest_module_product_api');
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('keyvalue'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Responds to POST requests and saves the new record.
   *
   * @param array $data
   *   Data to write into the database.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function post(array $data) {
    $data['id'] = $this->getNextId();
    $this->storage->set($data['id'], $data);
    $this->logger->notice('Create new product api record @id.');
    // Return the newly created record in the response body.
    return new ModifiedResourceResponse($data, 201);
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the record.
   */
  public function get() {
    $nodes = $this->entity->getStorage('node')->loadByProperties(['type' => 'product', 'status' => 1]);
    $resource = [];
    foreach ($nodes as $key => $value) {
      $resource[$key]['product_title'] = $value->title;
      $resource[$key]['product_description'] = $value->field_description;
      $resource[$key]['product_price'] = $value->field_price;
      $resource[$key]['product_images'] = $value->field_product_image;
    }
    $response = new ResourceResponse($resource);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => ['node_list:product'],
      ],
    ]));
    return $response;
  }

  /**
   * Responds to PATCH requests.
   *
   * @param int $id
   *   The ID of the record.
   * @param array $data
   *   Data to write into the storage.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function patch($id, array $data) {
    if (!$this->storage->has($id)) {
      throw new NotFoundHttpException();
    }
    $stored_data = $this->storage->get($id);
    $data += $stored_data;
    $this->storage->set($id, $data);
    $this->logger->notice('The product api record @id has been updated.');
    return new ModifiedResourceResponse($data, 200);
  }

  /**
   * Responds to DELETE requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function delete($id) {
    if (!$this->storage->has($id)) {
      throw new NotFoundHttpException();
    }
    $this->storage->delete($id);
    $this->logger->notice('The product api record @id has been deleted.', ['@id' => $id]);
    // Deleted responses have an empty body.
    return new ModifiedResourceResponse(NULL, 204);
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method) {
    $route = parent::getBaseRoute($canonical_path, $method);
    // Set ID validation pattern.
    if ($method != 'POST') {
      $route->setRequirement('id', '\d+');
    }
    return $route;
  }

  /**
   * Returns next available ID.
   */
  private function getNextId() {
    $ids = \array_keys($this->storage->getAll());
    return count($ids) > 0 ? max($ids) + 1 : 1;
  }

}

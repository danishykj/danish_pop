<?php

namespace Drupal\danish_pop\Controller;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\danish_pop\SpotifyHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Initialize class.
 */
class ArtistController extends ControllerBase {
  /**
   * Settings key.
   */
  const SETTINGS = 'danish_pop.settings';

  /**
   * Configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $settings;

  /**
   * The Request Stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected Request $request;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('danish_pop.spotify_helper'),
          $container->get('config.factory'),
          $container->get('request_stack')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function __construct(SpotifyHelper $spotify, ConfigFactoryInterface $config_factory, RequestStack $request_stack) {
    $this->spotify = $spotify;
    $this->settings = $config_factory->getEditable(static::SETTINGS);
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * Data sync function to create batch process.
   */
  public function artistsync() {

    $auth = $this->spotify->authorizeToken();
    $count = 0;
    if ($auth['success']) {

      $artist_ids = '6M2wZ9GZgrQXHCFfjv46we,2CIMQHirSU0MQqyYHq0eOx,57dN52uHvrHOxijzpIgu3E,1vCWHaC5f2uS3yhpwWbIA6,77IW5ZK1smDQYYKDCQugXh,1wRPtKGflJrBx9BmLsSwlU,5GnnSrwNCGyfAU4zuIytiS,3fMbdgg4jU18AjLCKBhRSm,7dGJo4pcD2V6oG8kP0tJRR,26dSoYclwsYLMAKD3tpOr4,6vWDO969PvNqNYHIOW5v0m,3bVFCD8huf39y8tZ0wbtRb,66CXWjxzNUsdJxJ2JdwvnR';
      $artist_object = $this->spotify->getArtists($artist_ids);

      foreach ($artist_object['artists'] as $artist) {

        $entity_id = $this->spotify->createNode($artist);
        if (!empty($entity_id)) {
          $count = $count + 1;
        }

      }

      $output = "Total " . $count . " Nodes imported successfully.";
      \Drupal::messenger()->addMessage($output);
      return $this->redirect('system.admin_content');
    }
    else {
      $output = "Total " . $count . " Nodes imported as API is not working correctly.";
      \Drupal::messenger()->addMessage($output);
      return $this->redirect('system.admin_content');
    }

  }

}

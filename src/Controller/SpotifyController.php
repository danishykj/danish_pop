<?php

namespace Drupal\danish_pop\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\danish_pop\SpotifyHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Processing and output for Spotify API data.
 */
class SpotifyController extends ControllerBase {

  /**
   * Settings key.
   */
  const SETTINGS = 'danish_pop.settings';

  /**
   * Spotify Helper service.
   *
   * @var \Drupal\danish_pop\SpotifyHelper
   */
  protected $spotify;

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
   * JSON Endpoint for Spotify Now Playing widgets.
   */
  public function content(): CacheableJsonResponse {

    $auth = $this->spotify->authorizeToken();

    if ($auth['success']) {
      $string = "The api is working Correctly";
    }
    else {
      $string = "The api is not working Correctly";
    }
    $data['now'] = $string;

    $data['#cache'] = [
      'max-age'  => (1 * 5),
      'tags'     => [
        'danish_pop',
      ],
    ];

    $response = new CacheableJsonResponse($data);

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($data));

    return $response;
  }

}

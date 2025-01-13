<?php

namespace Drupal\danish_pop;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service to fetch API helper functions from Spotify.
 */
class SpotifyHelper {

  /**
   * Http Client variable.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('http_client')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client) {
    $this->settings = $config_factory->getEditable('danish_pop.settings');
    $this->httpClient = $http_client;
  }

  /**
   * Function to authorize the Token for Spotify.
   */
  public function authorizeToken() {

    $auth_url = $this->settings->get('auth_url');
    $client_id = $this->settings->get('client_id');
    $client_secret = $this->settings->get('client_secret');

    if (!empty($client_id) && !empty($client_secret) && !empty($auth_url)) {
      try {
        $response = $this->httpClient->request('POST', $auth_url . '/api/token', [
          'verify' => TRUE,
          'form_params' => [
            'grant_type' => 'client_credentials',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
          ],
          'headers' => [
            'Content-type' => 'application/x-www-form-urlencoded',
          ],
        ])->getBody()->getContents();
        $response = json_decode($response, TRUE);
        $response['success'] = TRUE;

      }
      catch (GuzzleException $error) {
        $response = $error->getResponse();
        $response_info = $response->getBody()->getContents();
        $response_info = json_decode($response_info, TRUE);

        $response = $response_info;
        $response['success'] = FALSE;
      }
      return $response;
    }

  }

  /**
   * Function to get the Artist.
   */
  public function getArtist(string $artist_id) {

    $token = \Drupal::service('danish_pop.spotify_helper')->authorizeToken();
    $api_urls = 'https://api.spotify.com/v1/artists/' . $artist_id;
    $response_final = $this->httpClient->request('GET', $api_urls, [
      'headers' => [
    // Replace with your authentication method.
        'Authorization' => 'Bearer ' . $token['access_token'],
      ],
    ]);

    $response = $response_final->getBody()->getContents();
    $response = json_decode($response, TRUE);
    $response['success'] = TRUE;

    return $response;

  }

  /**
   * Function to get the list of Artists.
   */
  public function getArtists($artist_ids) {

    $token = \Drupal::service('danish_pop.spotify_helper')->authorizeToken();

    $api_urls = 'https://api.spotify.com/v1/artists?ids=' . $artist_ids;
    $response_final = $this->httpClient->request('GET', $api_urls, [
      'headers' => [
        'Authorization' => 'Bearer ' . $token['access_token'],
      ],
    ]);

    $check = $response_final->getBody()->getContents();
    $check = json_decode($check, TRUE);
    return $check;
  }

  /**
   * Function to check if the article exists.
   */
  public function artistExists($artist_unique_id) {
    $query = \Drupal::database()->select('node__field_unique_id', 'uid');
    $query->fields('uid', ['entity_id']);
    $query->condition('field_unique_id_value', $artist_unique_id);
    $result = $query->execute();

    foreach ($result as $row) {
      $entity_id = $row->entity_id;
    }
    if (!empty($entity_id)) {
      return $entity_id;
    }
    else {
      return NULL;
    }

  }

  /**
   * To Create Terms if it is not available.
   */
  public function createNode($artist) {
    $artist_unique_id = $artist['id'];
    $artist_exists = \Drupal::service('danish_pop.spotify_helper')->artistExists($artist_unique_id);
    if ($artist_exists == NULL) {
      $terms = \Drupal::service('danish_pop.spotify_helper')->getTermReferences('genres', $artist['genres']);
      if (!empty($terms)) {
        $nodeArray['field_genre'] = $terms;
      }

      $nodeArray['title'] = $artist['name'];
      $nodeArray['body'] = $artist_name;
      $nodeArray['field_external_url'] = $artist['external_urls']['spotify'];
      $nodeArray['field_followers'] = $artist['followers']['total'];
      $nodeArray['field_image_url'] = $artist['images'][0]['url'];
      $nodeArray['field_popularity'] = $artist['popularity'];
      $nodeArray['field_unique_id'] = $artist_unique_id;

      $nodeArray['type'] = strtolower('artist');
      $nodeArray['promote'] = 0;
      $nodeArray['sticky'] = 0;
      $nodeArray['langcode'] = 'en';

      $node = Node::create($nodeArray);

      $node->save();
      return $node->id();
    }
    else {
      return NULL;
    }
  }

  /**
   * To Create Terms if it is not available.
   */
  public function createTerms($voc, $term, $vid) {
    Term::create(
            [
              'parent' => [$voc],
              'name' => $term,
              'vid' => $vid,
            ]
        )->save();
    $termId = \Drupal::service('danish_pop.spotify_helper')->getTermIds($term, $vid);
    return $termId;
  }

  /**
   * To get Termid available.
   */
  public function getTermIds($term, $vid) {
    $query = \Drupal::database()->select('taxonomy_term_field_data', 't');
    $query->fields('t', ['tid']);
    $query->condition('t.vid', $vid);
    $query->condition('t.name', $term);
    $termRes = $query->execute()->fetchAll();
    foreach ($termRes as $val) {
      $term_id = $val->tid;
    }
    return $term_id;
  }

  /**
   * To get Reference field ids.
   */
  public function getTermReferences($voc, $terms) {
    $vocName = strtolower($voc);
    $vid = preg_replace('@[^a-z0-9_]+@', '_', $vocName);
    $vocabularies = Vocabulary::loadMultiple();
    // $termArray = array_map('trim', explode(',', $terms));
    $termIds = [];
    foreach ($terms as $term) {

      $term_id = \Drupal::service('danish_pop.spotify_helper')->getTermIds($term, $vid);
      if (empty($term_id)) {
        $term_id = \Drupal::service('danish_pop.spotify_helper')->createTerms($voc, $term, $vid);
      }
      $termIds[]['target_id'] = $term_id;
    }
    return $termIds;
  }

}

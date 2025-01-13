<?php

namespace Drupal\danish_pop\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\danish_pop\SpotifyHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration Form for the Spotify Now Playing module.
 */
class ArtistAddForm extends ConfigFormBase {


  /**
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritDoc}
   */
  public function __construct(Renderer $renderer, SpotifyHelper $spotify) {
    $this->renderer = $renderer;
    $this->spotify = $spotify;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('danish_pop.spotify_helper')
    );
  }

  const SETTINGS = 'danish_pop.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'artist_add_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $auth = $this->spotify->authorizeToken();
    if ($auth['success']) {

      $config = $this->config(static::SETTINGS);

      $form['artist_id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Artist Spotify ID'),
        '#required' => TRUE,
        '#default_value' => '',
        '#description' => $this->t('Enter the unique Spotify Artist ID.e.g: 23zg3TcAtWQy7J6upgbUnj. You can get the unique ID from https://open.spotify.com/'),
        '#maxlength' => '22',
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => t('Submit'),
      ];
    }
    else {
      $form['endpoint_test'] = [
        '#type'  => 'html_tag',
        '#tag'   => 'p',
        '#value' => '<strong>The API is not configured correctly.</strong>',
      ];
    }
    $view = views_embed_view('spotify_artists', 'block_1');

    $form['form_output'] = [
      '#prefix' => '<p>',
      '#suffix' => '</p>',
      '#markup' => $this->renderer->render($view),
    ];
    return $form;
  }

  /**
   * Function to validate the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // The field with the key "fullname" must have at least 5 characters.
    $minimum_length = 5;
    if (strlen($form_state->getValue('artist_id')) < $minimum_length) {
      $form_state->setErrorByName('artist_id', $this->t('The name is too short; minimum length is 22 characters.'));
    }

    $ids = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('type', 'artist')
      ->execute();
    $total_node = count($ids);
    if ($total_node >= 20) {
      $form_state->setErrorByName('artist_id', $this->t('Maximum 20 artist content can be added.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $artist_id = $form_state->getValue('artist_id');

    $artist_exists = $this->spotify->artistExists($artist_id);

    if ($artist_exists == NULL) {
      $artist_object = $this->spotify->getArtist($artist_id);

      if (!empty($artist_object)) {

        $entity_id = $this->spotify->createNode($artist_object);
      }
      else {
        \Drupal::messenger()->addMessage($this->t('Please make sure to enter the correct artist id'));
      }

    }

    $url = Url::fromRoute('danish_pop.artistadd');
    $form_state->setRedirectUrl($url);

    parent::submitForm($form, $form_state);
  }

}

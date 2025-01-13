<?php

namespace Drupal\danish_pop\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configuration Form for the Spotify Now Playing module.
 */
class SpotifyConfigForm extends ConfigFormBase {

  const SETTINGS = 'danish_pop.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'danish_pop_config_form';
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

    $config = $this->config(static::SETTINGS);

    $form['auth_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify Auth URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('auth_url'),
      '#description' => $this->t('Enter the Auth URL of the Spotify Playing API client. e.g: https://accounts.spotify.com'),
    ];

    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify API URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('api_url'),
      '#description' => $this->t('Enter the API URL of the Spotify Playing API client.e.g: https://api.spotify.com'),
    ];

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#required' => TRUE,
      '#default_value' => $config->get('client_id'),
      '#description' => $this->t('Enter the Client ID of the Spotify Playing API client.'),
    ];

    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#required' => TRUE,
      '#default_value' => $config->get('client_secret'),
      '#description' => $this->t('Enter the Client Secret of the Spotify Playing API client.'),
    ];

    if ($config->get('client_id') && $config->get('client_secret')) {

      $endpoint_test = Url::fromRoute('danish_pop.endpoint', [], [
        'absolute' => TRUE,
      ])->toString();

      $form['endpoint_test'] = [
        '#type'  => 'html_tag',
        '#tag'   => 'p',
        '#value' => '<strong>Test the Endpoint:</strong> <a href="' . $endpoint_test . '">' . $endpoint_test . '</a> <br/>',
      ];

    }

    return parent::buildForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $config->set('client_id', $form_state->getValue('client_id'));
    $config->set('client_secret', $form_state->getValue('client_secret'));
    $config->set('api_url', $form_state->getValue('api_url'));
    $config->set('auth_url', $form_state->getValue('auth_url'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

}

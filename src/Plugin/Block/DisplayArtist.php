<?php

namespace Drupal\danish_pop\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Renderer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Now Playing block.
 *
 * @Block(
 *  id = "display_artist_block",
 *     admin_label = @Translation("Display Artist"),
 *     category = @Translation("Custom")
 * )
 */
class DisplayArtist extends BlockBase {


  /**
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  public function __construct(Renderer $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $view = views_embed_view('artist_list', 'block_1');
    return [
      '#markup' => $this->renderer->render($view),
    ];
  }

}

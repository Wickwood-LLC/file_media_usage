<?php

namespace Drupal\file_media_usage\Plugin\views\field;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\field\Standard;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

/**
 * Default implementation of the base field plugin.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("file_display")
 */
class FileDisplay extends Standard {
  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->additional_fields['filemime'] = 'filemime';
    $this->additional_fields['fid'] = 'fid';
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['image_style'] = ['default' => 'thumbnail'];
    $options['video_width'] = ['default' => 600];
    $options['video_height'] = ['default' => 400];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Image style for image'),
      '#description' => $this->t('Image style to use for images.'),
      '#default_value' => $this->options['image_style'],
      '#options' => image_style_options(FALSE),
    ];

    $form['video_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Video width'),
      '#default_value' => $this->options['video_width'],
    ];

    $form['video_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Video width'),
      '#default_value' => $this->options['video_height'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $this->addAdditionalFields();
    //$this->field_alias = $this->table . '_' . $this->field;
  }

  public function render(ResultRow $values) {
    $filemime = $this->getValue($values, 'filemime');
    $file = $values->_entity;
    $uri = $file->uri->get(0)->get('value')->getValue();

    if (preg_match('~^image/.+~', $filemime)) {
      return [
        '#theme' => 'image_style',
        // '#width' => $variables['width'],
        // '#height' => $variables['height'],
        '#style_name' => $this->options['image_style'],
        '#uri' => $uri,
      ];
    }
    else if (preg_match('~^video/.+~', $filemime)) {
      $video_attributes = (new Attribute())
        ->setAttribute('controls', 'controls')
        ->setAttribute('width', $this->options['video_width'])
        ->setAttribute('height', $this->options['video_height']);
      return [
        '#theme' => 'file_video',
        '#attributes' => $video_attributes,
        '#files' => [
          ['source_attributes' => (new Attribute())->setAttribute('src', file_create_url($uri))->setAttribute('type', $file->getMimeType()),],
        ],
      ];
    }
    else if (preg_match('~^audio/.+~', $filemime)) {
      return [
        '#theme' => 'file_audio',
        '#attributes' => (new Attribute())->setAttribute('controls', 'controls'),
        '#files' => [
          ['source_attributes' => (new Attribute())->setAttribute('src', file_create_url($uri))->setAttribute('type', $file->getMimeType()),],
        ],
      ];
    }
    else  {
      return array(
        '#type' => 'link',
        '#title' => $file->get('filename')->value,
        '#url' => Url::fromUri(file_create_url($uri)),
      );
    }
  }
}

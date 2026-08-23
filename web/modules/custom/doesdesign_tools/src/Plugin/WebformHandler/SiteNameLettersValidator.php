<?php

declare(strict_types=1);

namespace Drupal\doesdesign_tools\Plugin\WebformHandler;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

// AI generated (bead doesdesign-tk7f).
/**
 * Validates a field against N letters from a reference string.
 *
 * @WebformHandler(
 *   id = "sitename_letters_validator",
 *   label = @Translation("Site-name letters spam check"),
 *   category = @Translation("Validation"),
 *   description = @Translation("Requires N letters from a reference string in a target field (bot filter)."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_IGNORED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
final class SiteNameLettersValidator extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   *
   * @return array<string, mixed>
   *   Default configuration.
   */
  public function defaultConfiguration(): array {
    return [
      'field_name' => 'tools',
      'reference_string' => 'doesdesign.nl',
      'required_letters' => 3,
      'error_message' => 'Het antwoord is niet juist. Om te controleren of u geen spamrobot bent, vraag ik om 3 letters uit de naam van de site in te voeren.',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @return array<string, mixed>
   *   Summary render array.
   */
  public function getSummary(): array {
    $settings = $this->getConfiguration()['settings'];
    return [
      '#markup' => $this->t(
        'Requires @n letter(s) of "@ref" in field <code>@field</code>.',
        [
          '@n' => (int) $settings['required_letters'],
          '@ref' => $settings['reference_string'],
          '@field' => $settings['field_name'],
        ]
      ),
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array<string, mixed>
   *   Configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $settings = $this->getConfiguration()['settings'];
    $form['field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Target field key'),
      '#description' => $this->t('Webform element key whose value gets validated.'),
      '#default_value' => $settings['field_name'],
      '#required' => TRUE,
    ];
    $form['reference_string'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reference string'),
      '#description' => $this->t('Submitted value must contain N distinct letters from this string.'),
      '#default_value' => $settings['reference_string'],
      '#required' => TRUE,
    ];
    $form['required_letters'] = [
      '#type' => 'number',
      '#title' => $this->t('Required matching letters'),
      '#min' => 1,
      '#max' => 20,
      '#default_value' => $settings['required_letters'],
      '#required' => TRUE,
    ];
    $form['error_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Error message'),
      '#description' => $this->t('Shown when the check fails.'),
      '#default_value' => $settings['error_message'],
      '#required' => TRUE,
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<string, mixed> $form
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    foreach (array_keys($this->defaultConfiguration()) as $key) {
      $this->configuration[$key] = $form_state->getValue($key);
    }
    $this->configuration['required_letters'] = (int) $this->configuration['required_letters'];
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<string, mixed> $form
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission): void {
    $settings = $this->getConfiguration()['settings'];
    $field = $settings['field_name'];
    $answer = mb_strtolower((string) $form_state->getValue($field));
    $reference = mb_strtolower((string) $settings['reference_string']);
    $required = (int) $settings['required_letters'];
    $matches = count(array_intersect(str_split($answer), str_split($reference)));
    if ($matches < $required) {
      $form_state->setErrorByName($field, Xss::filter($settings['error_message']));
    }
  }

}

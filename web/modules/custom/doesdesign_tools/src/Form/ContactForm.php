<?php

declare(strict_types=1);

namespace Drupal\doesdesign_tools\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a contact form.
 */
class ContactForm extends FormBase {

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Constructs a new ContactForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  final public function __construct(
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory,
    MailManagerInterface $mail_manager,
    LanguageManagerInterface $language_manager,
  ) {
    $this->configFactory = $config_factory;
    $this->loggerFactory = $logger_factory;
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('plugin.manager.mail'),
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'contact_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array<string, mixed>
   *   The build form array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['contact'] = [
      '#markup' => $this->t('Gebruik het onderstaande contactformulier voor vragen of opmerkingen'),
      '#prefix' => '<div class="fields"><div class="field">',
      '#suffix' => '</div>',
    ];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Naam'),
      '#maxlength' => 64,
      '#size' => 64,
      '#weight' => '0',
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mailadres'),
      '#weight' => '0',
      '#required' => TRUE,
    ];
    $form['telephone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Telefoonnummer'),
      '#weight' => '0',
      '#required' => TRUE,
    ];
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Onderwerp'),
      '#weight' => '0',
      '#required' => TRUE,
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Vraag of opmerking'),
      '#weight' => '0',
      '#suffix' => '</div>',
      '#required' => TRUE,
    ];
    $form['tools'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Noem drie letters uit de naam van de site'),
      '#size' => 24,
      '#description' => $this->t('Ik stel deze vraag om te controleren dat dit formulier niet door een robot wordt ingevuld'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Versturen'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $tools_string = strtolower($form_state->getValue('tools'));
    $letters = str_split($tools_string);
    $doesdesign_letters = str_split('doesdesign.nl');
    $result = array_intersect($letters, $doesdesign_letters);
    $result = count($result);
    if ($result < 3) {
      $form_state->setError($form, $this->t('Het antwoord is niet juist. Om te controleren of u geen spamrobot bent, vraag ik om 3 letters uit de naam van de site in te voeren. Als het niet lukt, stuur dan een email naar birgit@doesdesign.nl'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $telephone = $form_state->getValue('telephone');
    $name = $form_state->getValue('name');
    $message = $form_state->getValue('message');
    $subject = $form_state->getValue('subject');
    $module = 'doesdesign_tools';
    $key = 'doesdesign_tools_mail';
    $to = $this->configFactory->get('system.site')->get('mail') ?: 'coxdoes@gmail.com';
    $params = [];
    $params['message'] = "Onderwerp: $subject \n\nNaam: $name \n\nTelefoon: $telephone\n\nBericht: $message";
    $params['subject'] = $subject;
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $send = TRUE;
    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      $this->messenger()->addError($this->t('There was a problem sending your message and it was not sent.'));
    }
    else {
      $this->messenger()->addStatus($this->t('Your message has been sent.'));
    }
  }

}

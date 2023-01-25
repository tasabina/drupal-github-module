<?php

namespace Drupal\github_fetcher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

class GitHubFetcherSettings extends FormBase
{
    protected const GITHUB_API_VALUES = 'github_fetcher:values';

    public function getFormId(): string
    {
        return 'github_fetcher_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        $values = \Drupal::state()->get(self::GITHUB_API_VALUES) ??
                    [
                        'repo_url' => '',
                        'github_token' => '',
                    ];
        $form['github_details'] = [
            '#type' => 'details',
            '#title' => $this->t('GitHub url & access token'),
        ];

        $form['repo_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('GitHub account URL'),
            '#group' => 'github_details',
            '#description' => $this->t('Description'),
            '#required' => TRUE,
            '#default_value' => $values['repo_url'],
        ];

        $form['github_token'] = [
            '#type' => 'textfield',
            '#title' => $this->t('GitHub token'),
            '#group' => 'github_details',
            '#description' => $this->t('Description'),
            '#required' => TRUE,
            '#default_value' => $values['github_token'],
        ];
              
          $form['actions'] = [
            '#type' => 'actions',
            'submit' => [
                '#type' => 'submit',
                '#value' => $this->t('Save'),
                '#button_type' => 'primary',
            ],
            'cancel' => [
                '#type' => 'button',
                '#value' => $this->t('Cancel'),
            ],
        ];
      
        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (strlen($form_state->getValue('repo_url')) < 1) {
            $form_state->setErrorByName('repo_url', $this->t('GitHub account URL is not set.'));
        }
        if (strlen($form_state->getValue('github_token')) < 1) {
            $form_state->setErrorByName('github_token', $this->t('Github token is not set.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $submitted_value = $form_state->cleanValues()->getValues();
        \Drupal::state()->set(self::GITHUB_API_VALUES, $submitted_value);

        $this->messenger()->addStatus(
            $this->t('GitHub account URL is @url', ['@url' => $form_state->getValue('repo_url')])
        );
    }
}
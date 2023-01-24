<?php

namespace Drupal\github_fetcher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class GitHubFetcherSettings extends FormBase
{
    public function getFormId(): string
    {
        return 'github_fetcher_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state): array
    {
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
        ];

        $form['github_token'] = [
            '#type' => 'textfield',
            '#title' => $this->t('GitHub token'),
            '#group' => 'github_details',
            '#description' => $this->t('Description'),
            '#required' => TRUE,
        ];
              
          $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
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
        $this->messenger()->addStatus(
            $this->t('GitHub account URL is @url', ['@url' => $form_state->getValue('repo_url')])
        );
    }
}
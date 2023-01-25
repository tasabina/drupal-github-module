<?php

namespace Drupal\github_fetcher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\github_fetcher\Connector\GitHubAPIConnector;


class GitHubFetcherController extends ControllerBase {

 public function getAPI()
 {
    $this->getGitHubRepo();
    $content = [];
    $content['placeholder'] = 'Placeholder for content';
    
    return [
        '#theme' => 'github_fetcher',
        '#content' => $content
    ];
 }

 public function getGitHubRepo()
 {
    /** @var GitHubAPIConnector  $api_connector */
    $api_connector = \Drupal::service('github_fetcher.github_api_connector');
    $repo_info = $api_connector->run();
    var_dump($repo_info);
 }
}
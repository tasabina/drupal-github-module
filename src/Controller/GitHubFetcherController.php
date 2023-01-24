<?php

namespace Drupal\github_fetcher\Controller;

use Drupal\Core\Controller\ControllerBase;

class GitHubFetcherController extends ControllerBase {

 public function getAPI()
 {
    $content = [];
    $content['placeholder'] = 'Placeholder for content';
    
    return [
        '#theme' => 'github_fetcher',
        '#content' => $content
    ];
 }
}
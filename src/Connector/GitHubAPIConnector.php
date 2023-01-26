<?php

namespace Drupal\github_fetcher\Connector;

use Drupal\github_fetcher\Form\GitHubFetcherSettings;
use Exception;
use Symfony\Component\HttpKernel\Log\Logger;

class GitHubAPIConnector 
{
    private $repo_url;
    private $github_user;
    private $github_token;

    public function __construct()
    {
        $api_config = \Drupal::state()->get(GitHubFetcherSettings::GITHUB_API_VALUES);

        $this->repo_url = $api_config['repo_url'] ?? '';
        $this->github_user = $api_config['github_user'] ?? '';
        $this->github_token = $api_config['github_token'] ?? '';
    
    }

    private function fetch($url) {
        $curl_request = curl_init();
        curl_setopt($curl_request, CURLOPT_URL, $url);
        curl_setopt($curl_request, CURLOPT_USERPWD, $this->get_credentials());
        curl_setopt($curl_request, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
        ]);
    
        curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, false);
        sleep(1); // don't exceed rate limit
        $result = curl_exec($curl_request);
        curl_close($curl_request);
        return $result;
    }

    private function get_credentials() {
        return implode(':', [$this->github_user, $this->github_token]);
    }

    private function fetch_json($url) {
        $result = $this->fetch($url);
        return json_decode($result);
    }
    
    private function dump_json($json) {
        $string = json_encode($json, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
        file_put_contents('modules.txt', $string, FILE_APPEND);

        return $string;
    }

    private function getComposerRequire($composer) {
        $arr = [
            'require' => $composer->require,
            'require-dev' => $composer->{'require-dev'},
        ];
        return $arr;
    }
    
    private function outputFormat($version, $composer) {
        return [
            'module' => $this->repo_url,
            'last_branch' => $version,
            'composer' => $this->getComposerRequire($composer),
        ];
    }
    
    private function identifyLastBranch($array) {
        $currentLast = 0;
        foreach($array as $key => $val) {
            foreach($val as $k => $v) {
                if ($k != 'name') {
                    continue;
                }
                $version = (float) $v;
                if ($version > 0) {
                    $currentLast = $version < $currentLast ? $currentLast : $version;
                }
            }
        }
    
        return $currentLast;
    }

    public function run()
    {
        try {
            $json = $this->fetch_json("https://api.github.com/repos/$this->repo_url/branches");
            $lastVersion = $this->identifyLastBranch($json);
            
            $json_responce = $this->fetch_json("https://raw.githubusercontent.com/$this->repo_url/$lastVersion/composer.json");
            // $j = $this->fetch_json("https://raw.githubusercontent.com/$githubCC/pulls/$lastVersion/upgrade-cms5/composer.json");
            
            return $this->dump_json($this->outputFormat($lastVersion, $json_responce));

        } catch (Exception $exception) {
            var_dump($exception);
        }
    }
}
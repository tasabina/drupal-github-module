<?php

namespace Drupal\github_fetcher\Connector;

use Drupal\github_fetcher\Form\GitHubFetcherSettings;

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

    private function fetch() {
        $curl_request = curl_init();
        curl_setopt($curl_request, CURLOPT_URL, $this->repo_url);
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

    function fetch_json($url) {
        $result = fetch($url);
        return json_decode($result);
    }
    
    function dump_json($json) {
        $string = json_encode($json, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
        file_put_contents('modules.txt', $string, FILE_APPEND);
    }
    
    function packagist_to_github($packagist) {
        list($account, $repo) = explode('/', $packagist);
        if ($account == 'silverstripe') {
            if (strpos($repo, 'recipe') !== 0 && $repo != 'comment-notifications' && $repo != 'vendor-plugin' && $repo != 'eslint-config') {
                $repo = 'silverstripe-' . $repo;
            }
        }
        if ($account == 'colymba') {
            $repo = 'GridfieldBulkEditingTools';
        }
        if ($account == 'cwp') {
            $account = 'silverstripe';
            if (strpos($repo, 'cwp') !== 0) {
                $repo = 'cwp-' . $repo;
            }
            if ($repo == 'cwp-agency-extensions') {
                $repo = 'cwp-agencyextensions';
            }
        }
        if ($account == 'tractorcow' && $repo == 'silverstripe-fluent') {
            $account = 'tractorcow-farm';
        }
        $arr = [
            "cc" => "creative-commoners/$repo",
            "ss" => "$account/$repo"
        ];
        return $arr;
        // return "$account/$repo";
    }
    
    function getComposerRequire($composer) {
        $arr = [
            'require' => $composer->require,
            'require-dev' => $composer->{'require-dev'},
        ];
        return $arr;
    }
    
    function outputFormat($name, $version, $composer) {
        return [
            'module' => $name,
            'last_branch' => $version,
            'composer' => getComposerRequire($composer),
        ];
    }
    
    function identifyLastBranch($array) {
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
        foreach ($modules as $packagist) {
            $githubSS = packagist_to_github($packagist)["ss"];
            $githubCC = packagist_to_github($packagist)["cc"];
        
            // fetch data from github API
            $json = fetch_json("https://api.github.com/repos/$githubSS/branches");
            $lastVersion = identifyLastBranch($json);
        
            //fetch raw file from github
            // $j = fetch_json("https://raw.githubusercontent.com/$github/$lastVersion/composer.json");
            $j = fetch_json("https://raw.githubusercontent.com/$githubCC/pulls/$lastVersion/upgrade-cms5/composer.json");
            
            dump_json(outputFormat($githubSS, $lastVersion, $j));
        }
    }
}
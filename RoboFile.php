<?php

require_once "vendor/autoload.php";

use Eaudeweb\RedmineAPI;
use Redmine\Client\NativeCurlClient;
use Robo\Tasks;

/**
 * @noinspection PhpUnused
 */
class RoboFile extends Tasks
{

  /**
   * @throws \Exception
   */
  private function createClient($redmineUrl): NativeCurlClient {
    $apikey = getenv('REDMINE_APIKEY') ?: trim(file_get_contents('redmine.key'));
    if(empty($redmineUrl) || empty($apikey))
      throw new \Exception('Invalid client configuration. Missing Redmine URL or API key');
    $this->say('Using Redmine: ' . $redmineUrl);
    return new NativeCurlClient($redmineUrl, $apikey);
  }

  /**
   * redmine:create-test-issue
   *
   * @throws \Exception
   * @noinspection PhpUnused
   */
  public function redmineCreateTestIssue($redmineProjectId): void {
    $redmine = new RedmineAPI($this->createClient());
    try {
      $out = $redmine->createIssueFromYaml('templates/test.yml', $this->output);
    } catch (\Redmine\Exception|\Throwable $e) {
      $this->yell($e->getMessage());
    }
    $this->say('Created issue with ID: ' . $out->id);
  }


  /**
   * Create hierarchy issues related to web design phase.
   *
   * @throws \Exception
   * @noinspection PhpUnused
   */
  public function redmineCreateNewProject($redmineUrl, $redmineProjectId, $parentContractId = NULL): void {
    $client = $this->createClient($redmineUrl);
    $redmine = new RedmineAPI($client);
    try {
      $redmine->createProject($redmineProjectId, $this->output, $parentContractId);
    } catch (\Redmine\Exception|\Throwable $e) {
      $this->yell($e->getMessage());
    }
    if($parentContractId) {
      $url = $redmineUrl . '/issues/' . $parentContractId;
    } else {
      $url = $redmineUrl . '/projects/' . $redmineProjectId;
    }
    $this->say('Done creating project structure. Visit it at: ' . $url);
  }
}

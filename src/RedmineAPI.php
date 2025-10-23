<?php

namespace Eaudeweb;

use Redmine\Client\NativeCurlClient;
use Symfony\Component\Yaml\Yaml;

class RedmineAPI {
  protected NativeCurlClient $client;

  /**
   * @throws \Exception
   */
  public function __construct(NativeCurlClient $client) {
    $this->client = $client;
  }

  public function createIssueFromYaml($yaml, $logger, $parentIdOverride = NULL) {
    $data = Yaml::parseFile($yaml);
    if($parentIdOverride) {
      $data['parent_issue_id'] = $parentIdOverride;
    }
    if (empty($data['parent_issue_id'])) {
      unset($data['parent_issue_id']);
    }
    $logger->writeln('Creating new issue: ' . $data['subject']);
    //var_dump(empty(0));

    $issues = $this->client->getApi('issue');
    return $issues->create($data);
  }
}
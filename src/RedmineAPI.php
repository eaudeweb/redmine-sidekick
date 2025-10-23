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

  public function createIssueFromYaml($redmineProjectId, $yaml, $logger, $parentIdOverride = NULL) {
    static $mappings = [];
    $data = Yaml::parseFile($yaml);
    $data['project_id'] = $redmineProjectId;

    /** Parent handling */
    // parent_issue refers to a previously created issue during this session.
    // It points to a filename and ID is computed from $mappings
    if(isset($data['parent_issue'])) {
      $basename = basename($data['parent_issue']);
      if(isset($mappings[$basename])) {
        $data['parent_issue_id'] = $mappings[$basename];
      } else {
        $logger->writeln('Cannot attach to parent - parent not in mappings: ' . $basename);
      }
      unset($data['parent_issue']);
    }
    if(empty($data['parent_issue_id']) && $parentIdOverride) {
      $data['parent_issue_id'] = $parentIdOverride;
    }
    // Unset if empty to avoid API call errors
    if(empty($data['parent_issue_id'])) {
      unset($data['parent_issue_id']);
    }
    /** END parent handling */

    $logger->writeln('Creating new issue: ' . $data['subject']);
    $issues = $this->client->getApi('issue');
    $ret = $issues->create($data);
    if(!isset($ret->error)) {
      $basename = basename($yaml);
      $mappings[$basename] = $ret->id;
    }
    return $ret;
  }

  public function createProject($redmineProjectId, $logger, $parentContractId = NULL): void {
    $this->createIssuesFromDirectory('project', $redmineProjectId, $logger, $parentContractId);
  }

  public function createIssuesFromDirectory($dirname, $redmineProjectId, $logger, $parentContractId = NULL): void {
    $templatesPath = realpath(dirname(__DIR__ . '/../../') . '/templates');
    $sourceDir = $templatesPath . '/' . $dirname;
    $files = Utilities::readYamlFiles($sourceDir);
    foreach($files as $file) {
      $path = 'templates/' . $dirname . '/' . $file;
      $this->createIssueFromYaml($redmineProjectId, $path, $logger, $parentContractId);
    }
  }
}
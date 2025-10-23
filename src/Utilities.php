<?php

namespace Eaudeweb;

class Utilities {

  public static function readYamlFiles($directory): array {
    $files = [];
    try {
      $dir = opendir($directory);
      while (FALSE != ($file = readdir($dir))) {
        if (($file != ".") && ($file != "..") && str_ends_with($file, '.yml')) {
          $files[] = $file;
        }
      }
      sort($files);
      return $files;
    }
    catch(\Exception $e) {
      // TODO:
    }
    return $files;
  }
}
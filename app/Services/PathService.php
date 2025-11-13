<?php

namespace App\Services;
use Illuminate\Support\Facades\Storage;


class PathService {

  const BB_BASE_CUSTOM = "civicrm/custom";
  const BB_BASE_CORE = "civicrm/core";

  public static array $custom_dirs = [
    self::BB_BASE_CUSTOM ."/php",
    self::BB_BASE_CUSTOM,
  ];

  public static array $custom_to_core_map = [
    self::BB_BASE_CUSTOM ."/php/CRM" => self::BB_BASE_CORE ."/CRM",
    self::BB_BASE_CUSTOM ."/php/api" => self::BB_BASE_CORE ."/api",
    self::BB_BASE_CUSTOM ."/templates" => self::BB_BASE_CORE ."/templates"
  ];

  /**
   * Maps Bluebird civicrm core path to civi-core path
   * eg... $Bluebird_base/civicrm/core/CRM maps to $civicrm_base/CRM
  */
  private static array $core_path_map = [
    self::BB_BASE_CORE ."/CRM" => "CRM",
    self::BB_BASE_CORE ."/ang" => "ang",
    self::BB_BASE_CORE ."/api" => "api",
    self::BB_BASE_CORE ."/Civi" => "Civi",
    self::BB_BASE_CORE ."/ext" => "ext",
    self::BB_BASE_CORE ."/js" => "js",
    self::BB_BASE_CORE ."/templates" => "templates",
  ];

  public static function normalizeDirectoryPath($path) {
    return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
  }

  public function collect(string $base_path, $disk) {
    //echo "collecting files for: " .  $base_path;
    return $disk->allFiles($base_path);
  }

  public static function isCustomPath($path) : bool{
    foreach (self::$custom_dirs as $prefix) {
      if (str_starts_with($path, $prefix)) {
        return true;
      }
    }
    return false;
  }

  public static function mapBBCustomToCore($path) : ?string {
    foreach (array_keys(self::$custom_to_core_map) as $prefix) {
      if (str_starts_with($path, $prefix)) {
        return str_replace($prefix,self::$custom_to_core_map[$prefix],$path);
        //return self::$override_to_core_map[$prefix];
      }
    }
    return NULL;
  }

  /**
   * Get a path that is relative to the root of civicrm-core repository
   */
  public static function getCoreRelativePath($path) : string|null {
    foreach (array_keys(self::$core_path_map) as $prefix) {
      if (str_starts_with($path, $prefix)) {
        $core_path =  str_replace($prefix,self::$core_path_map[$prefix],$path);
        return $core_path;
      }
    }
    return null;
  }

  public static function getCustomBaseDir($path) : ?string {
    foreach (self::$custom_dirs as $prefix) {
      if (str_starts_with($path, $prefix)) {
        return $prefix;
      }
    }
    return null;
  }
}
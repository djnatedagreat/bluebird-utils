<?php
$home = getenv('HOME') ?: getenv('USERPROFILE');
$root = rtrim($home, '/\\') . DIRECTORY_SEPARATOR . '.bluebird-utils';
if (!is_dir($root)) {
  mkdir($root, 0755, true);
}

return [
  'default' => 'local',
  'disks' => [
    'local' => [
      'driver' => 'local',
      'root' => $root,
    ],
  ],
];
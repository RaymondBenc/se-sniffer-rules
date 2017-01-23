<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;

/**
 * @inheritdoc
 */
class Config extends Command
{
  public $name = 'config';

  public $description = 'Configure your Social Engine work environment.';

  public $arguments = [
    'action',
    'key',
    'value'
  ];

  /**
   * Array of valid config keys
   *
   * @var array
   */
  private $allowed = [
    'path'
  ];

  public function process()
  {
    $action = $this->get('action');
    $key = $this->get('key');
    $value = $this->get('value');

    if (!in_array($key, $this->allowed)) {
      throw new \Exception('Not a valid config key: ' . $key);
    }

    switch ($action) {
      case 'set':
        $config = $this->getConfigPath();
        if (!file_exists($config)) {
          touch($config);
        }
        $data = json_decode(file_get_contents($config));
        $data->{$key} = $value;
        file_put_contents($config, json_encode($data, JSON_PRETTY_PRINT));
        break;
      case 'get':
        $this->write($this->getConfigValue($key));
        break;
    }
  }
}

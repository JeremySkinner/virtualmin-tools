<?php

class Settings {

  private $mode;
  private $parent;
  private $user;
  private $webroot;
  private $url;

  public function __construct(array $options) {
    $this->url = $options['url'];

    if (isset($options['root'])) {
      $this->mode = 'root';
    }
    elseif (isset($options['childof'])) {
      $this->mode = 'child';
      $this->parent = $options['childof'];
    }
    elseif (isset($options['aliasof'])) {
      $this->mode = 'alias';
      $this->parent = $options['aliasof'];
    }

    if (isset($options['user'])) {
      $this->user = $options['user'];
    }

    if (isset($options['webroot'])) {
      $this->webroot = $options['webroot'];
    }
  }


}

function build_settings_from_command_line_args() {
  $option_definitions = [
    'root',
    'url:',
    'user:',
    'child',
    'childof:',
    'alias',
    'aliasof:',
    'webroot:',
    'database:',
    'help',
  ];

  $options = getopt('h', $option_definitions);

  if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
  };

  // Must supply root|alias|child|aliasof|childof. If not specified, ask the user.
  if(!isset($options['root']) && !isset($options['alias']) && !isset($options['child']) && !isset($options['aliasof']) && !isset($options['childof'])) {
    do {
      $line = strtolower(readline("Please specify the site type (root, alias, child): "));
    } while($line != 'root' && $line != 'alias' && $line != 'child');

    $options[$line] = true;
  }


  // If child is specified, we haven't indicated what this is a child of, prompt for it.
  if(isset($options['child']) && (!isset($options['childof']))) {
    do {
      // @todo we should list sites here.
      $options['childof'] = readline("Of which site should this be a child? ");
    } while(!$options['childof']);
  }
  // If alias is specified, we haven't indicated what this is an alias of, prompt for it.
  elseif(isset($options['alias']) && (!isset($options['aliasof']))) {
    do {
      // @todo we should list sites here.
      $options['aliasof'] = readline("Of which site should this be an alias? ");
    } while(!$options['aliasof']);
  }

  // If root/aliasof/childof are specified, but no URL is specified, prompt for it.
  if(isset($options['childof']) || isset($options['aliasof']) || isset($options['root'])) {
    if(!isset($options['url'])) {
      do {
        $options['url'] = readline("Please enter a URL for the site: ");
      } while(!$options['url']);
    }
  }

  // Root site must have a username specified
  if (isset($options['root']) && !isset($options['user'])) {
    do {
      $options['user'] = readline("Enter a username for the site to run under: ");
    } while(!$options['user']);
  }

  if (!isset($options['database'])) {
    do {
      $options['database'] = readline("Enter a database name: ");
    } while(!$options['database']);
  }

  return new Settings($options);
}
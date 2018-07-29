<?php

class Settings {

  public $mode;
  public $parent;
  public $user;
  public $webroot;
  public $url;
  public $database;
  public $password;
  public $phpversion;

  public function __construct(array $options) {
    $this->url = str_replace('https://', '', $options['url']);
    $this->url = str_replace('http://', '', $this->url);

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

    if(isset($options['database'])) {
      $this->database = $options['database'];
    }

    if(isset($options['password'])) {
      $this->password = $options['password'];
    }

    if(isset($options['phpversion'])) {
      $this->phpversion = $options['phpversion'];
    }

    $this->setDefaults();
  }

  private function setDefaults() {

    if (file_exists(__DIR__ . "/defaults.json")) {
      $defaults = json_decode(file_get_contents(__DIR__ . "/defaults.json"), true);
      
      if (is_array($defaults)) {
        foreach($defaults as $key => $value) {
          if (empty($this->$key)) {
            $this->$key = $value;
          }
        }
      }
    }

    // @todo: load from config
    //if(empty($this->phpversion)) {
    //  $this->phpversion = '7.0';
    //}

    //if (empty($this->webroot)) {
    //  $this->webroot = 'app/web';
    //}
  }

}

function buildSettings() {
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
    'password:',
    'phpversion:',
    'help',
  ];

  $options = getopt('h', $option_definitions);

  if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
  };

  $prompt_for_password = false;

  // Must supply root|alias|child|aliasof|childof. If not specified, ask the user.
  if(!isset($options['root']) && !isset($options['alias']) && !isset($options['child']) && !isset($options['aliasof']) && !isset($options['childof'])) {
    do {
      $line = strtolower(readline("Please specify the site type (root, alias, child): "));
    } while($line != 'root' && $line != 'alias' && $line != 'child');

    if ($line == 'root' && !isset($options['password'])) {
      $prompt_for_password = true;
    }

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

  if($prompt_for_password) {
    $password = trim(readline("Enter a password. Leave blank for it to be auto-generated."));
    $options['password'] = $password;

     // If no password specified, generate one.
    if(empty($options['password'])) {
      $options['password'] = generatePassword();
      print "--------------------------------\n";
      print "Generated password: \n";
      print $options['password'];
      print "Make a note of this!";
      print "--------------------------------\n";
    }
  }


  return new Settings($options);
}


// https://gist.github.com/tylerhall/521810
function generatePassword($length = 9, $add_dashes = false, $available_sets = 'lud') {
	$sets = array();
	if(strpos($available_sets, 'l') !== false)
		$sets[] = 'abcdefghjkmnpqrstuvwxyz';
	if(strpos($available_sets, 'u') !== false)
		$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
	if(strpos($available_sets, 'd') !== false)
		$sets[] = '23456789';
	if(strpos($available_sets, 's') !== false)
		$sets[] = '!@#$%&*?';
	$all = '';
	$password = '';
	foreach($sets as $set)
	{
		$password .= $set[array_rand(str_split($set))];
		$all .= $set;
	}
	$all = str_split($all);
	for($i = 0; $i < $length - count($sets); $i++)
		$password .= $all[array_rand($all)];
	$password = str_shuffle($password);
	if(!$add_dashes)
		return $password;
	$dash_len = floor(sqrt($length));
	$dash_str = '';
	while(strlen($password) > $dash_len)
	{
		$dash_str .= substr($password, 0, $dash_len) . '-';
		$password = substr($password, $dash_len);
	}
	$dash_str .= $password;
	return $dash_str;
}
<?php

class Virtualmin {
  private $args = '';
  private $settings;

  public function __construct(Settings $settings) {
    $this->settings = $settings;
  }


  public function execute() {
    switch($this->settings->mode) {
      case 'root':
        $this->buildRootSite();
        break;
      case 'alias':
        $this->buildAlias();
        break;
      case 'child':
        $this->buildChild();
        break;
    }
  }

  public function command($cmd) {
    $this->args .= "$cmd ";
    return $this;
  }

  public function buildAlias() {
    $s = $this->settings;
    $domain = $s->url;
    $domain = str_replace('http://', '', $domain);
    $parent = $s->parent;

    vexec("create-domain --domain $domain --alias $parent");
  }

  public function buildChild() {
    $s = $this->settings;

    $domain = $s->url;
    $database = $s->database;
    $phpversion = $s->phpversion;
    $parent = $s->parent;
    $webroot = $s->webroot;

    vexec("create-domain --domain $domain --parent $parent --mysql --db $database --web");

    if ($webroot) {
      vexec("modify-web --domain $domain --document-dir $webroot");
    }
    //vexec("set-php-directory --domain $domain --dir . --version $phpversion");
  }

  public function buildRootSite() {
    $s = $this->settings;

    $domain = str_replace('https://', '', $s->url);
    $domain = str_replace('http://', '', $domain);
    $user = $s->user;
    $password = $s->password;
    $database = $s->database;
    $phpversion = $s->phpversion;
    $webroot = $s->webroot;

    vexec("create-domain --domain $domain --user $user --unix --dir --pass $password --mysql --db $database --web");

    if ($webroot) {
      vexec("modify-web --domain $domain --document-dir $webroot");
    }
    //vexec("set-php-directory --domain $domain --dir . --version $phpversion");
  }
}

function vexec($args) {
  $cmd = 'virtualmin ' . $args;

  print "Executing $cmd";
  return;

  $descriptorspec = [
    0 => ["pipe", "r"],   // stdin is a pipe that the child will read from
    1 => ["pipe", "w"],   // stdout is a pipe that the child will write to
    2 => ["pipe", "w"]    // stderr is a pipe that the child will write to
  ];

  ob_implicit_flush(true);
  //ob_end_flush();

 flush();

 $process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());

  if (is_resource($process)) {
    while ($s = fgets($pipes[1])) {
      print $s;
      flush();
    }
    proc_close($process);
  }
}

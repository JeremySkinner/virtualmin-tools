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
    }
  }

  public function command($cmd) {
    $this->args .= "$cmd ";
    return $this;
  }

  public function domain() {

  }

  public function buildRootSite() {
    $s = $this->settings;

    $domain = str_replace('https://', '', $s->url);
    $domain = str_replace('http://', '', $s->url);
    $user = $s->user;
    $password = $s->password;
    $database = $s->database;
    $phpversion = $s->phpversion;

    vexec("create-domain --domain $domain --user $user --unix --dir --pass $password --mysql --db $database --web");
    vexec("set-php-directory --domain $domain --dir . --version $phpversion");
  }
}

function vexec($args) {
  $cmd = 'virtualmin ' . $args;

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

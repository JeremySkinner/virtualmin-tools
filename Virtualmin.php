<?php

class Virtualmin {
  private $args = '';

  public function command($cmd) {
    $this->args .= "$cmd ";
    return $this;
  }

  public function domain() {

  }

  public function buildRootSite($domain, $user, $pass, $database) {
    vexec("create-domain --domain $domain --user $user --unix --mysql --db $database --pass $pass --web")
  }
}
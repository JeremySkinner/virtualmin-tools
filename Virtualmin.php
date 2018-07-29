<?php

class Virtualmin {
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

  public function buildAlias() {
    $s = $this->settings;
    $domain = $s->url;
    $domain = str_replace('http://', '', $domain);
    $parent = $s->parent;

    vexec(["create-domain --domain $domain --alias $parent"]);
  }

  public function buildChild() {
    $s = $this->settings;

    $domain = $s->url;
    $database = $s->database;
    $phpversion = $s->phpversion;
    $parent = $s->parent;
    $webroot = $s->webroot;
    $user = $s->user;

    $commands = [];
    $commands[] = "create-domain --domain $domain --parent $parent --dir --mysql --db $database --web";

    if ($webroot) {
      $commands[] = "modify-web --domain $domain --document-dir $webroot";
    }

    if ($phpversion) {
      $commands[] = "set-php-directory --domain $domain --dir $webroot --version $phpversion";
    }

    vexec($commands);
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

    $commands = [];

    $commands[] = "create-domain --domain $domain --user $user --unix --dir --pass $password --mysql --db $database --web";

    if ($webroot) {
      $commands[] = "modify-web --domain $domain --document-dir $webroot";
    }

    if ($phpversion) {
      $commands[] = "set-php-directory --domain $domain --dir $webroot --version $phpversion";
    }

    vexec($commands);
  }
}
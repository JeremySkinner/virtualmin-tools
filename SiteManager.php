<?php

require 'Help.php';
require 'Settings.php';
require 'Virtualmin.php';

$settings = buildSettings();
$v = new Virtualmin($settings);
$v->execute();
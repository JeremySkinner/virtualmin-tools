<?php

require 'Help.php';
require 'Settings.php';

$settings = build_settings_from_command_line_args();

print_r($settings);
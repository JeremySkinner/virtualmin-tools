<?php

function vexec(array $commands) {
  print "About to execute the following commands:\n";

  foreach($commands as $command) {
    print "virtualmin $command\n";
  }

  do {
    $continue = strtolower(readline("Continue? [y/n]: "));
  } while($continue != 'y' && $continue != 'n');

  if ($continue != 'y') {
    return;
  }

  foreach($commands as $command) {
    exec_command("virtualmin $command");
  }
}

function exec_command($cmd) {
  $descriptorspec = [
    0 => ["pipe", "r"],   // stdin is a pipe that the child will read from
    1 => ["pipe", "w"],   // stdout is a pipe that the child will write to
    2 => ["pipe", "w"]    // stderr is a pipe that the child will write to
  ];

  ob_implicit_flush(true);

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
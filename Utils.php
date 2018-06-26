<?php

function vexec($args, &$rtn, $as_array=false) {
  $command = 'virtualmin ' . $args;

  $process = proc_open($command, [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
  ],
    $pipes
  );

  if (!is_resource($process)) {
    throw new \RuntimeException('Could not create a valid process');
  }

  // This will prevent to program from continuing until the processes is complete
  // Note: exitcode is created on the final loop here
  $status = proc_get_status($process);
  while($status['running']) {
    $status = proc_get_status($process);
  }

  $stdOutput = stream_get_contents($pipes[1]);
  $stdError  = stream_get_contents($pipes[2]);

  proc_close($process);

  $rtn = $status['exitcode'];

  if ($as_array) {
    return array_filter(explode("\n", $stdOutput));
  }

  return trim($stdOutput);
}
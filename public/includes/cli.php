<?php
// includes/cli.php

$cmd = $argv[1] ?? 'help';

switch ($cmd) {
	case 'track:drain':
        require_once ROOT_PATH . '/track/cli-drain-impressions.php';
        break;

	case 'help':
	default:
        echo "Available CLI commands:\n";
        echo "  track:drain   Drain Redis tracking list to update tracking database\n\n";
		break;
}

exit;
?>
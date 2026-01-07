<?php
$output = shell_exec('yt-dlp --version');
echo $output ? "yt-dlp version: " . trim($output) : "yt-dlp not found";
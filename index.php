<?php
require_once './src/errorLogParser.php';

$info = new errorLogParser('error.log', 'test', 'localhost', 'mysql', 'mysql');

$info->setEmailsFlag('emails', 'email', 'flag');

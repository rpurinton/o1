#!/usr/bin/env php
<?php

namespace RPurinton\O1;

require_once(__DIR__ . '/vendor/autoload.php');
$o1 = new O1(getenv('OPENAI_API_KEY'));
$o1->run();

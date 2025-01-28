#!/usr/bin/env php
<?php

require_once(__DIR__ . '/vendor/autoload.php');
$o1 = \OpenAI::client(getenv('OPENAI_API_KEY'));


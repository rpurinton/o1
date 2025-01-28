#!/usr/bin/env php
<?php

require_once(__DIR__ . '/vendor/autoload.php');
$o1 = \OpenAI::client(getenv('OPENAI_API_KEY'));

$request = json_decode(file_get_contents(__DIR__ . '/request.json'), true);
$lines = file(__DIR__ . '/history.jsonl', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) $request['messages'][] = json_decode($line, true);

$prompt = "o1> ";
$thinking = "🧠 Thinking...";

while (true) {
    echo ($prompt);
    $input = trim(fgets(STDIN));
    if ($input === 'exit') exit(0);
    echo ($thinking);
    $request['messages'][] = ['role' => 'user', 'content' => ['type' => 'text', 'text' => $input]];
    $response = $o1->chat()->create($request);
    $response = $response->choices[0]->message->content;
    $request['messages'][] = ['role' => 'assistant', 'content' => ['type' => 'text', 'text' => $response]];
    echo ("\r$response\n");
}

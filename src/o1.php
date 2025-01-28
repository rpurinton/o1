<?php

namespace RPurinton\o1;

use OpenAI\Client;

class O1
{
    const prompt = "o1> ";
    const thinking = "🧠 Thinking...";
    private Client $o1;
    private array $request = [];

    public function __construct($openai_api_key)
    {
        $this->o1 = \OpenAI::client($openai_api_key);
        $this->request = $this->loadRequest();
    }

    private function loadRequest()
    {
        $request = json_decode(file_get_contents(__DIR__ . '/request.json'), true);
        $request['messages'] = array_merge($request['messages'], $this->loadHistory());
    }

    private function loadHistory()
    {
        $lines = file(__DIR__ . '/history.jsonl', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $history = [];
        foreach ($lines as $line) $history[] = json_decode($line, true);
        return $history;
    }

    private function saveHistory($message)
    {
        $this->request['messages'][] = $message;
        file_put_contents(__DIR__ . '/history.jsonl', json_encode($message) . PHP_EOL, FILE_APPEND);
    }

    private function clearHistory()
    {
        file_put_contents(__DIR__ . '/history.jsonl', '');
        $this->request = $this->loadRequest();
    }

    public function run()
    {
        while (true) {
            echo (self::prompt);
            $input = trim(fgets(STDIN));
            if ($input === 'exit') exit(0);
            if ($input === 'clear') {
                $this->clearHistory();
                continue;
            }
            echo (self::thinking);
            $input = ['role' => 'user', 'content' => ['type' => 'text', 'text' => $input]];
            $this->saveHistory($input);
            $response = $this->o1->chat()->create($this->request);
            $response = $response->choices[0]->message->content;
            $response = ['role' => 'assistant', 'content' => ['type' => 'text', 'text' => $response]];
            $this->saveHistory($response);
            echo ("\r{$response['content']['text']}\n");
        }
    }
}

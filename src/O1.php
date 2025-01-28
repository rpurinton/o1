<?php

namespace RPurinton\O1;

use OpenAI\Client;

class O1
{
    const prompt = "o1> ";
    const thinking = "🧠 Thinking...";
    const request_file = __DIR__ . '/../request.json';
    const history_file = __DIR__ . '/../history.jsonl';
    private Client $o1;
    private array $request = [];

    public function __construct($openai_api_key)
    {
        $this->o1 = \OpenAI::client($openai_api_key);
        $this->request = $this->loadRequest();
    }

    private function loadRequest()
    {
        $request = json_decode(self::request_file, true);
        print_r($request);
        $request['messages'] = array_merge($request['messages'], $this->loadHistory());
    }

    private function loadHistory()
    {
        $lines = file(self::history_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $history = [];
        foreach ($lines as $line) $history[] = json_decode($line, true);
        return $history;
    }

    private function saveHistory($message)
    {
        $this->request['messages'][] = $message;
        file_put_contents(self::history_file, json_encode($message) . PHP_EOL, FILE_APPEND);
    }

    private function clearHistory()
    {
        file_put_contents(self::history_file, '');
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

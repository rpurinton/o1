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
        $requestContent = file_get_contents(self::request_file);
        $request = json_decode($requestContent, true) ?? [];
        $history = $this->loadHistory();
        $request['messages'] = array_merge($request['messages'] ?? [], $history);
        return $request;
    }

    private function loadHistory()
    {
        if (!file_exists(self::history_file)) {
            return [];
        }
        $lines = file(self::history_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $history = [];
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if ($decoded) {
                $history[] = $decoded;
            }
        }
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
            echo self::prompt;
            $input = trim(fgets(STDIN));
            if ($input === 'exit') {
                exit(0);
            }
            if ($input === 'clear') {
                $this->clearHistory();
                continue;
            }
            echo self::thinking;
            $inputMessage = ['role' => 'user', 'content' => [['type' => 'text', 'text' => $input]]];
            $this->saveHistory($inputMessage);
            $response = $this->o1->chat()->create($this->request);
            $content = $response->choices[0]->message->content;
            $responseMessage = ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => $content]]];
            $this->saveHistory($responseMessage);
            $screenWidth = (int)exec('tput cols');
            $wrapped = wordwrap($content, max($screenWidth - 1, 40), "\n", true);
            echo "\r\033[K" . $wrapped . PHP_EOL;
        }
    }
}

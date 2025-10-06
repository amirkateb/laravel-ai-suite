<?php

namespace AmirKateb\AiSuite\Support\History;

use AmirKateb\AiSuite\Contracts\HistoryStoreInterface;

class InMemoryHistoryStore implements HistoryStoreInterface
{
    protected static array $data = [];

    public function put(string $conversationId, string $role, mixed $content): void
    {
        if (!isset(self::$data[$conversationId])) {
            self::$data[$conversationId] = [];
        }
        self::$data[$conversationId][] = ['role' => $role, 'content' => $content, 'ts' => microtime(true)];
    }

    public function get(string $conversationId, int $limit = 50): array
    {
        $all = self::$data[$conversationId] ?? [];
        if ($limit <= 0) {
            return $all;
        }
        return array_slice($all, -$limit);
    }

    public function clear(string $conversationId): void
    {
        unset(self::$data[$conversationId]);
    }
}
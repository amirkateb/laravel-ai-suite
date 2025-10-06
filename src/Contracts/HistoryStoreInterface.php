<?php

namespace AmirKateb\AiSuite\Contracts;

interface HistoryStoreInterface
{
    public function put(string $conversationId, string $role, mixed $content): void;
    public function get(string $conversationId, int $limit = 50): array;
    public function clear(string $conversationId): void;
}
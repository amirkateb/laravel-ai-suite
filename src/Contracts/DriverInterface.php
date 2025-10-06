<?php

namespace AmirKateb\AiSuite\Contracts;

interface DriverInterface
{
    public function listModels(): array;
    public function chat(array $messages, array $options = []): array;
    public function embeddings(string $text, array $options = []): array;
    public function ocr(string $imagePath, array $options = []): array;
    public function image(array $options): array;
    public function audioToText(string $filePath, array $options = []): array;
    public function textToAudio(string $text, array $options = []): array;
    public function fineTune(array $options): array;
    public function calculateCost(array $usage): float;
}
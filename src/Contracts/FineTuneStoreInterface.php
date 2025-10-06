<?php

namespace AmirKateb\AiSuite\Contracts;

interface FineTuneStoreInterface
{
    public function createDataset(string $name, array $files, array $meta = []): array;
    public function listDatasets(): array;
    public function getDataset(string $id): ?array;
    public function deleteDataset(string $id): bool;
    public function createJob(string $provider, array $options): array;
    public function listJobs(): array;
    public function getJob(string $id): ?array;
}
<?php

namespace AmirKateb\AiSuite\Support\FineTune;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use AmirKateb\AiSuite\Contracts\FineTuneStoreInterface;

class FileFineTuneStore implements FineTuneStoreInterface
{
    protected string $disk = 'local';
    protected string $datasetsPath = 'ai_finetune/datasets';
    protected string $jobsFile = 'ai_finetune/jobs.json';

    public function createDataset(string $name, array $files, array $meta = []): array
    {
        $id = (string) Str::uuid();
        $base = $this->datasetsPath.'/'.$id;
        Storage::disk($this->disk)->makeDirectory($base);
        $stored = [];
        foreach ($files as $f) {
            $fname = is_string($f) ? basename($f) : $f->getClientOriginalName();
            $path = $base.'/'.$fname;
            if (is_string($f) && file_exists($f)) {
                Storage::disk($this->disk)->put($path, file_get_contents($f));
            } else {
                Storage::disk($this->disk)->put($path, $f->get());
            }
            $stored[] = Storage::disk($this->disk)->path($path);
        }
        $dataset = ['id' => $id, 'name' => $name, 'files' => $stored, 'meta' => $meta, 'created_at' => now()->toISOString()];
        Storage::disk($this->disk)->put($this->datasetsPath.'/'.$id.'.json', json_encode($dataset, JSON_UNESCAPED_UNICODE));
        return $dataset;
    }

    public function listDatasets(): array
    {
        $files = Storage::disk($this->disk)->files($this->datasetsPath);
        $out = [];
        foreach ($files as $f) {
            if (str_ends_with($f, '.json')) {
                $j = json_decode(Storage::disk($this->disk)->get($f), true);
                if (is_array($j)) $out[] = $j;
            }
        }
        return $out;
    }

    public function getDataset(string $id): ?array
    {
        $file = $this->datasetsPath.'/'.$id.'.json';
        if (!Storage::disk($this->disk)->exists($file)) return null;
        $j = json_decode(Storage::disk($this->disk)->get($file), true);
        return is_array($j) ? $j : null;
    }

    public function deleteDataset(string $id): bool
    {
        $dir = $this->datasetsPath.'/'.$id;
        $meta = $dir.'.json';
        if (Storage::disk($this->disk)->exists($meta)) Storage::disk($this->disk)->delete($meta);
        if (Storage::disk($this->disk)->exists($dir)) Storage::disk($this->disk)->deleteDirectory($dir);
        return true;
    }

    public function createJob(string $provider, array $options): array
    {
        $jobs = $this->readJobs();
        $id = (string) Str::uuid();
        $job = ['id' => $id, 'provider' => $provider, 'options' => $options, 'status' => 'queued', 'created_at' => now()->toISOString()];
        $jobs[] = $job;
        $this->writeJobs($jobs);
        return $job;
    }

    public function listJobs(): array
    {
        return $this->readJobs();
    }

    public function getJob(string $id): ?array
    {
        foreach ($this->readJobs() as $j) {
            if (($j['id'] ?? '') === $id) return $j;
        }
        return null;
    }

    protected function readJobs(): array
    {
        if (!Storage::disk($this->disk)->exists($this->jobsFile)) return [];
        $j = json_decode(Storage::disk($this->disk)->get($this->jobsFile), true);
        return is_array($j) ? $j : [];
    }

    protected function writeJobs(array $jobs): void
    {
        Storage::disk($this->disk)->put($this->jobsFile, json_encode($jobs, JSON_UNESCAPED_UNICODE));
    }
}
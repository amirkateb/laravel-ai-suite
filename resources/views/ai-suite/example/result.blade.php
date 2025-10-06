@extends('layouts.app')

@section('content')
<div style="max-width:640px;margin:40px auto;padding:24px;border:1px solid #ccc;border-radius:8px;font-family:Inter, sans-serif;">
    <h2>💬 AI Response</h2>
    <p><strong>Prompt:</strong> {{ $prompt }}</p>
    <p><strong>Driver:</strong> {{ $driver ?: '(default)' }}</p>
    <p><strong>Model:</strong> {{ $model ?: '(auto)' }}</p>

    <hr style="margin:20px 0;">

    <pre style="white-space:pre-wrap;font-family:JetBrains Mono, monospace;background:#f7f7f7;padding:16px;border-radius:8px;">{{ json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

    <a href="{{ route('ai-suite.example.form') }}" style="display:inline-block;margin-top:20px;background:#2d84da;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;">🔁 New Chat</a>
</div>
@endsection
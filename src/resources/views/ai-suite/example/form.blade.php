@extends('layouts.app')

@section('content')
<div style="max-width:640px;margin:40px auto;padding:24px;border:1px solid #ccc;border-radius:8px;font-family:Inter, sans-serif;">
    <h2>🧠 AI Suite Example Chat</h2>
    <form method="POST" action="{{ route('ai-suite.example.chat') }}">
        @csrf
        <div style="margin-top:16px;">
            <label>Prompt:</label>
            <textarea name="prompt" required rows="4" style="width:100%;padding:8px;border-radius:4px;border:1px solid #aaa;"></textarea>
        </div>
        <div style="margin-top:16px;">
            <label>Driver:</label>
            <select name="driver" style="width:100%;padding:8px;border-radius:4px;border:1px solid #aaa;">
                <option value="">(default)</option>
                <option value="openai">OpenAI</option>
                <option value="google_gemini">Gemini</option>
                <option value="deepseek">DeepSeek</option>
                <option value="xai_grok">xAI Grok</option>
                <option value="anthropic">Anthropic Claude</option>
            </select>
        </div>
        <div style="margin-top:16px;">
            <label>Model (optional):</label>
            <input type="text" name="model" placeholder="مثلاً gpt-4o-mini" style="width:100%;padding:8px;border-radius:4px;border:1px solid #aaa;">
        </div>
        <button type="submit" style="margin-top:24px;background:#24d3a7;color:#fff;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;">Send</button>
    </form>
</div>
@endsection
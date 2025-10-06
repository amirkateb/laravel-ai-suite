<?php

return [
    'default' => env('AI_DEFAULT', 'openai'),
    'fallback' => [
        'enabled' => env('AI_FALLBACK_ENABLED', false),
        'order' => explode(',', env('AI_FALLBACK_ORDER', 'openai,google_gemini,deepseek,xai_grok,ollama,anthropic,azure_openai,aws_bedrock'))
    ],
    'features' => [
        'chat' => true,
        'vision' => true,
        'ocr' => true,
        'embeddings' => true,
        'image' => true,
        'audio_tts' => true,
        'audio_stt' => true,
        'tools' => true,
        'fine_tuning' => true
    ],
    'history' => [
        'max_messages' => env('AI_HISTORY_MAX_MESSAGES', 50),
        'max_tokens' => env('AI_HISTORY_MAX_TOKENS', 32000)
    ],
    'costing' => [
        'currency' => env('AI_COST_CURRENCY', 'USD'),
        'round' => 6,
        'enabled' => env('AI_COST_ENABLED', true)
    ],
    'timeouts' => [
        'connect' => env('AI_TIMEOUT_CONNECT', 10),
        'read' => env('AI_TIMEOUT_READ', 120)
    ],
    'retries' => [
        'enabled' => env('AI_RETRY_ENABLED', true),
        'times' => env('AI_RETRY_TIMES', 2),
        'sleep_ms' => env('AI_RETRY_SLEEP_MS', 250)
    ],
    'drivers' => [
        'map' => [
            'openai' => 'AmirKateb\\AiSuite\\Drivers\\OpenAI\\OpenAIDriver',
            'google_gemini' => 'AmirKateb\\AiSuite\\Drivers\\Gemini\\GeminiDriver',
            'deepseek' => 'AmirKateb\\AiSuite\\Drivers\\DeepSeek\\DeepSeekDriver',
            'xai_grok' => 'AmirKateb\\AiSuite\\Drivers\\XaiGrok\\XaiGrokDriver',
            'ollama' => 'AmirKateb\\AiSuite\\Drivers\\Ollama\\OllamaDriver',
            'anthropic' => 'AmirKateb\\AiSuite\\Drivers\\Anthropic\\AnthropicDriver',
            'azure_openai' => 'AmirKateb\\AiSuite\\Drivers\\AzureOpenAI\\AzureOpenAIDriver',
            'aws_bedrock' => 'AmirKateb\\AiSuite\\Drivers\\Bedrock\\BedrockDriver'
        ]
    ],
    'providers' => [
        'openai' => [
            'enabled' => env('OPENAI_ENABLED', true),
            'api_key' => env('OPENAI_API_KEY', ''),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'organization' => env('OPENAI_ORG', null),
            'project' => env('OPENAI_PROJECT', null),
            'models_cache_ttl' => env('OPENAI_MODELS_TTL', 3600),
            'pricing' => [
                'gpt-4o' => ['input_per_1k' => 5, 'output_per_1k' => 15, 'token_unit' => 1000],
                'gpt-4o-mini' => ['input_per_1k' => 0.15, 'output_per_1k' => 0.6, 'token_unit' => 1000]
            ],
            'capabilities' => [
                'chat' => true,
                'vision' => true,
                'ocr' => true,
                'embeddings' => true,
                'image' => true,
                'audio_tts' => true,
                'audio_stt' => true,
                'tools' => true,
                'fine_tuning' => true
            ]
        ],
        'google_gemini' => [
            'enabled' => env('GEMINI_ENABLED', true),
            'api_key' => env('GEMINI_API_KEY', ''),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
            'models_cache_ttl' => env('GEMINI_MODELS_TTL', 3600),
            'pricing' => [],
            'capabilities' => [
                'chat' => true,
                'vision' => true,
                'ocr' => true,
                'embeddings' => true,
                'image' => true,
                'audio_tts' => true,
                'audio_stt' => true,
                'tools' => true,
                'fine_tuning' => true
            ]
        ],
        'deepseek' => [
            'enabled' => env('DEEPSEEK_ENABLED', true),
            'api_key' => env('DEEPSEEK_API_KEY', ''),
            'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
            'models_cache_ttl' => env('DEEPSEEK_MODELS_TTL', 3600),
            'pricing' => [],
            'capabilities' => [
                'chat' => true,
                'vision' => false,
                'ocr' => false,
                'embeddings' => true,
                'image' => false,
                'audio_tts' => false,
                'audio_stt' => false,
                'tools' => true,
                'fine_tuning' => true
            ]
        ],
        'xai_grok' => [
            'enabled' => env('XAI_ENABLED', true),
            'api_key' => env('XAI_API_KEY', ''),
            'base_url' => env('XAI_BASE_URL', 'https://api.x.ai'),
            'models_cache_ttl' => env('XAI_MODELS_TTL', 3600),
            'pricing' => [],
            'capabilities' => [
                'chat' => true,
                'vision' => true,
                'ocr' => false,
                'embeddings' => true,
                'image' => false,
                'audio_tts' => false,
                'audio_stt' => false,
                'tools' => true,
                'fine_tuning' => false
            ]
        ],
        'ollama' => [
            'enabled' => env('OLLAMA_ENABLED', true),
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'models_cache_ttl' => env('OLLAMA_MODELS_TTL', 60),
            'pricing' => [],
            'capabilities' => [
                'chat' => true,
                'vision' => false,
                'ocr' => false,
                'embeddings' => true,
                'image' => false,
                'audio_tts' => false,
                'audio_stt' => false,
                'tools' => true,
                'fine_tuning' => false
            ]
        ],
        'anthropic' => [
            'enabled' => env('ANTHROPIC_ENABLED', true),
            'api_key' => env('ANTHROPIC_API_KEY', ''),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
            'models_cache_ttl' => env('ANTHROPIC_MODELS_TTL', 3600),
            'pricing' => [],
            'capabilities' => [
                'chat' => true,
                'vision' => true,
                'ocr' => false,
                'embeddings' => false,
                'image' => false,
                'audio_tts' => false,
                'audio_stt' => false,
                'tools' => true,
                'fine_tuning' => false
            ]
        ],
        'azure_openai' => [
            'enabled' => env('AZURE_OPENAI_ENABLED', false),
            'api_key' => env('AZURE_OPENAI_API_KEY', ''),
            'endpoint' => env('AZURE_OPENAI_ENDPOINT', ''),
            'deployment' => env('AZURE_OPENAI_DEPLOYMENT', ''),
            'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-06-01'),
            'models_cache_ttl' => env('AZURE_OPENAI_MODELS_TTL', 3600),
            'pricing' => [],
            'capabilities' => [
                'chat' => true,
                'vision' => true,
                'ocr' => true,
                'embeddings' => true,
                'image' => true,
                'audio_tts' => true,
                'audio_stt' => true,
                'tools' => true,
                'fine_tuning' => false
            ]
        ],
        'aws_bedrock' => [
            'enabled' => env('BEDROCK_ENABLED', false),
            'access_key' => env('AWS_ACCESS_KEY_ID', ''),
            'secret_key' => env('AWS_SECRET_ACCESS_KEY', ''),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'models_cache_ttl' => env('BEDROCK_MODELS_TTL', 3600),
            'pricing' => [],
            'capabilities' => [
                'chat' => true,
                'vision' => true,
                'ocr' => false,
                'embeddings' => true,
                'image' => true,
                'audio_tts' => false,
                'audio_stt' => false,
                'tools' => true,
                'fine_tuning' => false
            ]
        ]
    ]
];
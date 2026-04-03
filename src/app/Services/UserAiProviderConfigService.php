<?php

namespace App\Services;

use App\Models\ModelAccess;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\AiManager;

class UserAiProviderConfigService
{
    private const TOKEN_CONFIG_MAP = [
        'gpt-5' => [
            'target' => 'ai.providers.openai.key',
            'fallback' => 'services.openai.key',
            'provider' => 'openai',
        ],
        'gemini-3-pro' => [
            'target' => 'ai.providers.gemini.key',
            'fallback' => 'services.gemini.key',
            'provider' => 'gemini',
        ],
    ];

    public function applyForUser(int|string|null $userId): void
    {
        $this->resetToFallbacks();

        if (blank($userId)) {
            $this->forgetResolvedProviders();

            return;
        }

        $modelAccesses = ModelAccess::query()
            ->where('user_id', $userId)
            ->whereIn('model_key', array_keys(self::TOKEN_CONFIG_MAP))
            ->get(['model_key', 'token']);

        foreach ($modelAccesses as $modelAccess) {
            $config = self::TOKEN_CONFIG_MAP[$modelAccess->model_key] ?? null;

            if (! $config || blank($modelAccess->token)) {
                continue;
            }

            try {
                $token = Crypt::decryptString($modelAccess->token);
            } catch (\Throwable $exception) {
                Log::warning('Unable to decrypt model access token.', [
                    'user_id' => $userId,
                    'model_key' => $modelAccess->model_key,
                    'message' => $exception->getMessage(),
                ]);

                continue;
            }

            if (blank($token)) {
                continue;
            }

            config([$config['target'] => $token]);
        }

        $this->forgetResolvedProviders();
    }

    private function resetToFallbacks(): void
    {
        foreach (self::TOKEN_CONFIG_MAP as $config) {
            config([$config['target'] => config($config['fallback'])]);
        }
    }

    private function forgetResolvedProviders(): void
    {
        app(AiManager::class)->forgetInstance(array_values(array_unique(array_column(self::TOKEN_CONFIG_MAP, 'provider'))));
    }
}
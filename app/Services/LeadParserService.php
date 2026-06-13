<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class LeadParserService
{
    public const FIELDS = [
        'case_name',
        'work_content',
        'required_skills',
        'preferred_skills',
        'location',
        'period',
        'unit_price',
        'settlement',
        'interview_count',
        'flow_limit',
        'contract_type',
        'age_limit',
        'foreigner_ok',
        'freelance_ok',
        'memo',
    ];

    public function parse(string $rawText): array
    {
        $config = config('lead_parser');
        $apiKey = $config['api_key'] ?? null;
        if (empty($apiKey) || $apiKey === '__SET_YOUR_KEY_HERE__') {
            Log::warning('lead_parser: ANTHROPIC_API_KEY が未設定です');
            throw new RuntimeException('AI解析の API キーが設定されていません。管理者に連絡してください。');
        }

        $payload = [
            'model' => $config['model'],
            'max_tokens' => $config['max_tokens'],
            'system' => $config['system_prompt'],
            'messages' => [[
                'role' => 'user',
                'content' => sprintf($config['user_prompt_template'], $rawText),
            ]],
        ];

        $attempts = max(1, ((int) $config['retry_times']) + 1);
        $lastError = null;

        for ($i = 0; $i < $attempts; $i++) {
            try {
                $response = Http::withHeaders([
                        'x-api-key' => $apiKey,
                        'anthropic-version' => '2023-06-01',
                        'content-type' => 'application/json',
                    ])
                    ->timeout((int) $config['timeout'])
                    ->post(rtrim($config['api_base'], '/') . '/v1/messages', $payload);

                if ($response->serverError() || $response->status() === 429) {
                    $lastError = 'API応答エラー: HTTP ' . $response->status();
                    Log::warning('lead_parser: retryable error', ['status' => $response->status(), 'body' => $response->body()]);
                    usleep(((int) $config['retry_sleep_ms']) * 1000);
                    continue;
                }
                if ($response->failed()) {
                    Log::error('lead_parser: client error', ['status' => $response->status(), 'body' => $response->body()]);
                    $body = $response->json();
                    $apiMsg = is_array($body)
                        ? ($body['error']['message'] ?? $response->body())
                        : $response->body();
                    $apiType = is_array($body) ? ($body['error']['type'] ?? '') : '';
                    throw new RuntimeException(
                        'AI解析に失敗しました (HTTP ' . $response->status() . ')'
                        . ($apiType ? ' [' . $apiType . ']' : '')
                        . ': ' . mb_substr((string) $apiMsg, 0, 500)
                    );
                }

                $text = $this->extractText($response->json());
                $json = $this->extractJson($text);
                return $this->normalize($json);
            } catch (ConnectionException|RequestException $e) {
                $lastError = $e->getMessage();
                Log::warning('lead_parser: transport error', ['error' => $lastError]);
                usleep(((int) $config['retry_sleep_ms']) * 1000);
            }
        }

        throw new RuntimeException('AI解析がタイムアウトしました: ' . ($lastError ?? '原因不明'));
    }

    private function extractText(array $response): string
    {
        $blocks = $response['content'] ?? [];
        $out = '';
        foreach ($blocks as $b) {
            if (($b['type'] ?? null) === 'text') {
                $out .= (string) ($b['text'] ?? '');
            }
        }
        return $out;
    }

    private function extractJson(string $text): array
    {
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            throw new RuntimeException('AI 応答から JSON を抽出できませんでした');
        }
        $slice = substr($text, $start, $end - $start + 1);
        $decoded = json_decode($slice, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('AI 応答の JSON パースに失敗しました');
        }
        return $decoded;
    }

    private function normalize(array $data): array
    {
        $out = [];
        foreach (self::FIELDS as $f) {
            $out[$f] = $data[$f] ?? ($f === 'interview_count' ? null : (in_array($f, ['required_skills', 'preferred_skills'], true) ? [] : ''));
        }
        $out['required_skills'] = $this->ensureStringArray($out['required_skills']);
        $out['preferred_skills'] = $this->ensureStringArray($out['preferred_skills']);
        if ($out['interview_count'] !== null && !is_int($out['interview_count'])) {
            $out['interview_count'] = is_numeric($out['interview_count']) ? (int) $out['interview_count'] : null;
        }
        return $out;
    }

    private function ensureStringArray(mixed $v): array
    {
        if (!is_array($v)) {
            return [];
        }
        return array_values(array_filter(array_map(fn ($s) => is_string($s) ? trim($s) : '', $v), fn ($s) => $s !== ''));
    }
}

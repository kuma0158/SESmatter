<?php

return [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'api_base' => env('ANTHROPIC_API_BASE', 'https://api.anthropic.com'),
    'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
    'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 4096),
    'timeout' => (int) env('ANTHROPIC_TIMEOUT', 60),
    'retry_times' => 2,
    'retry_sleep_ms' => 800,

    'system_prompt' => <<<'PROMPT'
あなたは日本のSES営業向けに、取引先から届く案件募集テキストを構造化するアシスタントです。
入力テキストから以下のフィールドを抽出し、必ず JSON オブジェクト 1 つだけを返してください。
- マークダウンのコードフェンス（```）や説明文・前置きは禁止。出力は JSON のみ。
- 値が読み取れない場合は文字列なら "" 、配列なら [] 、数値なら null を返す。
- required_skills / preferred_skills は文字列配列。
- interview_count は数値（不明なら null）。
- 単価・場所等の表記はリードの表記をそのまま残す（例: 「～75万円」「基本テレワーク」）。

スキーマ:
{
  "case_name": string,
  "work_content": string,
  "required_skills": string[],
  "preferred_skills": string[],
  "location": string,
  "period": string,
  "unit_price": string,
  "settlement": string,
  "interview_count": number|null,
  "flow_limit": string,
  "contract_type": string,
  "age_limit": string,
  "foreigner_ok": string,
  "freelance_ok": string,
  "memo": string
}
PROMPT,

    'user_prompt_template' => <<<'PROMPT'
以下の案件募集テキストを上記スキーマに従って JSON 化してください。
JSON 以外は一切出力しないでください。

---案件本文ここから---
%s
---案件本文ここまで---
PROMPT,
];

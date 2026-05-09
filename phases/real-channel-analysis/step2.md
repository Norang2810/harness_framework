# Step 2: openai-analysis

Implement Laravel OpenAI Responses API analysis.

Read:
- `backend/app/Services/ChannelAnalysisService.php`
- `backend/config/services.php`

Requirements:
- Use Laravel `Http` client.
- Read API key only from `config('services.openai.api_key')`.
- Use `POST https://api.openai.com/v1/responses`.
- Default model comes from `config('services.openai.model')`.
- Prompt must request Korean JSON with `summary`, `observations`, and `next_actions`.
- Parse `output_text` or `output[].content[].text`.
- Return validation-style errors for missing key, provider failure, and malformed JSON.

Acceptance:
- `npm run test`

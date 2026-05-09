# Step 3: analysis-api

Wire the real analysis API.

Read:
- `backend/routes/api.php`
- `backend/app/Services/ChannelAnalysisService.php`
- `backend/tests/Feature/AnalysisPreviewTest.php`

Requirements:
- Keep route `POST /api/analysis`.
- Request shape stays `{ "channel_url": string }`.
- Response keeps `status`, `language`, and `storage`.
- Response includes parsed channel reference, real YouTube channel metadata, provider source metadata, Korean summary, observations, and next actions.
- Frontend must not call YouTube or OpenAI directly.

Acceptance:
- `npm run lint`
- `npm run test`
- `npm run build`

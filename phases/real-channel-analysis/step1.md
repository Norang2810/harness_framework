# Step 1: youtube-provider

Implement Laravel YouTube Data API lookup.

Read:
- `backend/app/Support/YoutubeChannelUrlParser.php`
- `backend/config/services.php`
- `backend/tests/Unit/YoutubeChannelUrlParserTest.php`

Requirements:
- Use Laravel `Http` client.
- Read API key only from `config('services.youtube.data_api_key')`.
- Use `channels.list` with `part=snippet,statistics`.
- Lookup by `id` for `/channel/{id}`, `forHandle` for `@handle` and `/c/{name}`, and `forUsername` for `/user/{name}`.
- Normalize title, description, published date, channel id, canonical channel URL, thumbnails, and statistics.
- Return validation-style errors for missing key, provider failure, and channel not found.
- Do not expose API keys in JSON responses.

Acceptance:
- `npm run test`

# Step 0: docs-and-contract

Update project docs before implementation.

Read:
- `AGENTS.md`
- `docs/PRD.md`
- `docs/ARCHITECTURE.md`
- `docs/ADR.md`
- `docs/UI_GUIDE.md`

Requirements:
- Document that MVP now calls backend-only YouTube Data API and OpenAI Responses API.
- Document env names: `YOUTUBE_DATA_API_KEY`, `OPENAI_API_KEY`, optional `OPENAI_MODEL`.
- Keep out of scope: YouTube OAuth, transcript/text API, competitive comparison, scheduled reports, email export, DB persistence, user auth.
- API remains `POST /api/analysis`.

Acceptance:
- `npm run test`

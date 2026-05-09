# YouTube Channel Insight

Split Laravel API and Vue SPA workspace for YouTube channel analysis.

## Structure

- `backend/`: Laravel API application.
- `frontend/`: Vue 3 SPA built with Vite and Single-File Components.
- `agents/skills/`: Harness skills for planning and review workflows.
- `scripts/`: Harness phase execution utilities.
- `.githooks/`: Git hooks used by this repository.

## Setup

1. Install backend dependencies from `backend/` with `composer install`.
2. Install frontend dependencies from `frontend/` with `npm install`.
3. Copy environment files as needed and set `YOUTUBE_API_KEY` and `OPENAI_API_KEY`.
4. Run the Laravel API with `npm run dev:backend`.
5. Run the Vue SPA with `npm run dev:frontend`.

## Root Scripts

- `npm run lint`
- `npm run test`
- `npm run build`

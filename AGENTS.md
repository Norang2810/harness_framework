# 프로젝트: YouTube Channel Insight

## 기술 스택
- Backend: PHP 8.3+, Laravel 13
- Frontend: Vue 3, TypeScript, Vite
- Testing: Laravel feature tests and Vitest
- Styling: Vue SFC scoped CSS first; introduce a design system only when needed

## 아키텍처 규칙
- CRITICAL: Backend API code must live under `backend/`; do not add API routes under the repository root or frontend.
- CRITICAL: Frontend code must live under `frontend/`; Vue components must use Single-File Components and Composition API.
- CRITICAL: The Vue app must call Laravel API endpoints, not external provider APIs directly.
- Harness skills live under `agents/skills/`; do not recreate legacy command prompts under `.codex/commands/`.

## 개발 프로세스
- CRITICAL: 새 기능 구현 시 반드시 테스트를 먼저 작성하고, 테스트가 통과하는 구현을 작성할 것 (TDD)
- 커밋 메시지는 conventional commits 형식을 따를 것 (feat:, fix:, docs:, refactor:)

## 명령어
npm run dev:backend   # Laravel 개발 서버
npm run dev:frontend  # Vue 개발 서버
npm run lint          # Frontend lint + backend Pint check
npm run test          # Frontend Vitest + backend Laravel tests
npm run build         # Frontend build + backend config check

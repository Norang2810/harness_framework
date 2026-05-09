# YouTube Channel Insight

Laravel API와 Vue SPA로 만든 단일 YouTube 채널 분석 MVP입니다. 이 저장소는 Codex Harness Engineering 흐름을 적용해 문서, 스킬, phase, hook, 검증 명령을 한곳에서 관리합니다.

## 핵심 목표

- 사용자가 YouTube 채널 URL을 입력한다.
- Vue SPA는 Laravel API만 호출한다.
- Laravel backend가 YouTube Data API와 OpenAI Responses API를 호출한다.
- 분석 결과는 한국어로 생성하고 현재 화면 세션에만 유지한다.
- 서버 DB 저장, OAuth, transcript API, 경쟁 채널 비교, 예약 리포트, 메일 내보내기는 MVP 범위에서 제외한다.

## 기술 스택

- Backend: PHP 8.3+, Laravel 13
- Frontend: Vue 3, TypeScript, Vite
- Testing: Laravel unit/feature tests, Vitest
- Styling: Vue SFC scoped CSS 우선

## 디렉터리 구조

```text
backend/                 Laravel API, provider 연동, backend tests
frontend/                Vue 3 SPA, Vite, Vitest
docs/                    제품/아키텍처/UI/ADR 문서
agents/skills/           Codex Harness skill, review skill
phases/                  Harness phase metadata와 step 문서
scripts/                 Harness phase executor와 테스트
.githooks/               Git pre-commit 품질 게이트
```

## Harness Engineering 적용 내용

### Skills

`agents/skills/harness`는 작업을 phase와 step으로 쪼개기 위한 스킬입니다.

- 문서 먼저 읽기: `AGENTS.md`, `docs/PRD.md`, `docs/ARCHITECTURE.md`, `docs/ADR.md`, `docs/UI_GUIDE.md`
- step 설계: backend, frontend, workflow 변경을 작게 나눔
- phase 파일 작성: `phases/{task-name}/index.json`, `stepN.md`
- 실행 명령: `python scripts/execute.py {task-name}`

`agents/skills/review`는 변경사항을 리뷰하는 스킬입니다.

- 아키텍처 경계 확인
- Laravel/Vue 스택 준수 확인
- 테스트와 검증 명령 확인
- `AGENTS.md`의 CRITICAL 규칙 위반 여부 확인

### Phases

현재 적용된 phase는 `phases/real-channel-analysis`입니다.

```text
step0 docs-and-contract   문서와 API 계약 정리
step1 youtube-provider    YouTube Data API provider 구현
step2 openai-analysis     OpenAI Responses API provider 구현
step3 analysis-api        POST /api/analysis 연결
step4 vue-ui-polish       Vue 결과 UI 정리
```

각 step은 `pending`, `completed`, `error`, `blocked` 상태를 가질 수 있고, 완료된 step은 다음 step의 맥락이 되는 `summary`를 남깁니다.

### Hooks

`.githooks/pre-commit`은 커밋 전에 아래 명령을 자동 실행합니다.

```bash
npm run lint
npm run test
npm run build
```

하나라도 실패하면 커밋이 중단됩니다. 이 hook은 “스킬 실행”이 아니라 Git 커밋 전 품질 게이트입니다.

## 문서 구성

`docs/` 문서는 역할별로 나뉩니다.

### PRD

`docs/PRD.md`

- 목표
- MVP 범위
- MVP 제외 범위
- 사용자 흐름
- 수용 기준
- 구현 순서

### Architecture

`docs/ARCHITECTURE.md`

- 기술 스택
- 디렉터리 구조
- MVP 컴포넌트
- 데이터 흐름
- 상태 관리
- 경계 규칙

### ADR

`docs/ADR.md`

- Laravel API와 Vue SPA 분리 결정
- 분석 결과를 화면 세션에만 유지하는 결정
- provider 연동을 backend가 소유하는 결정
- YouTube Data API와 OpenAI Responses API 사용 결정

### UI Guide

`docs/UI_GUIDE.md`

- 디자인 원칙
- 화면 구성
- 컴포넌트 기준
- 스타일 기준

## 데이터 흐름

```text
User input
  -> Vue HomeView session state
  -> POST /api/analysis
  -> Laravel validation
  -> YoutubeChannelUrlParser
  -> YouTube Data API channels.list
  -> OpenAI Responses API
  -> ChannelAnalysisService
  -> JSON response
  -> Vue result panel
```

중요한 규칙은 frontend가 YouTube나 OpenAI를 직접 호출하지 않는 것입니다. API 키와 provider 오류 처리는 모두 `backend/` 안에서 처리합니다.

## 환경 변수

Laravel은 루트 `.env`가 아니라 `backend/.env`를 읽습니다.

```env
YOUTUBE_DATA_API_KEY=
OPENAI_API_KEY=
OPENAI_MODEL=gpt-5.2
```

로컬 Windows PHP에서 SSL CA 문제가 있으면 개발 중에만 아래 값을 사용할 수 있습니다.

```env
HTTP_CLIENT_VERIFY_SSL=false
```

## 실행

Backend:

```bash
npm run dev:backend
```

Frontend:

```bash
npm run dev:frontend
```

기본 주소:

```text
Backend  http://127.0.0.1:8000
Frontend http://127.0.0.1:5173
```

## 검증

```bash
npm run lint
npm run test
npm run build
```

검증 범위:

- Frontend: Oxlint, ESLint, Vitest, Vue type-check, Vite build
- Backend: Pint check, Laravel tests, config clear/build check

## 필수 규칙

- API route와 backend domain code는 `backend/` 아래에 둔다.
- Vue code는 `frontend/` 아래에 둔다.
- Vue 컴포넌트는 Single-File Component와 Composition API를 사용한다.
- 새 기능은 테스트를 먼저 작성하고 구현한다.
- Frontend는 외부 provider API를 직접 호출하지 않는다.
- Harness skill은 `agents/skills/` 아래에 둔다.
- legacy command prompt를 `.codex/commands/` 아래에 다시 만들지 않는다.
- 커밋 메시지는 conventional commits 형식을 따른다.

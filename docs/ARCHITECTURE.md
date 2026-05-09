# 아키텍처

## 기술 스택
- Backend: PHP 8.3+, Laravel 13
- Frontend: Vue 3, TypeScript, Vite
- Testing: Laravel feature/unit tests, Vitest
- Styling: Vue SFC scoped CSS first

## 디렉터리 구조
```text
backend/
  app/               # Laravel application and domain code
  routes/api.php     # JSON API endpoints
  tests/             # Laravel unit and feature tests

frontend/
  src/               # Vue SPA source
  src/views          # Page-level Vue SFCs
  src/components     # Reusable Vue SFCs

agents/skills/
  harness            # Harness planning and phase workflow
  review             # Repository review workflow
```

## MVP 컴포넌트
- `ChannelUrlParser`: YouTube 채널 URL을 검증하고 `{type, value, canonical_url}` 형태로 정규화한다.
- `YoutubeChannelDataProvider`: YouTube Data API `channels.list`를 backend에서 호출해 공개 채널 메타데이터를 정규화한다.
- `OpenAiChannelInsightProvider`: OpenAI Responses API를 backend에서 호출해 한국어 분석 JSON을 만든다.
- `ChannelAnalysisService`: URL 파서, YouTube provider, OpenAI provider를 조합해 API 응답을 만든다.
- `POST /api/analysis`: Vue SPA가 호출하는 단일 분석 엔드포인트다.
- `HomeView.vue`: URL 입력, 요청 상태, 오류, 현재 세션 분석 결과를 관리한다.

## 데이터 흐름
```text
User input
  -> Vue HomeView session state
  -> POST /api/analysis
  -> Laravel validation
  -> ChannelUrlParser
  -> YouTube Data API channels.list
  -> OpenAI Responses API
  -> ChannelAnalysisService
  -> JSON response
  -> Vue result panel
```

## 상태 관리
- MVP 분석 결과는 Vue 컴포넌트 상태에만 둔다.
- 서버는 요청마다 계산한 JSON을 반환하고 DB에 저장하지 않는다.
- 외부 API 키와 provider 호출은 Laravel backend에만 둔다.
- 환경 변수는 `YOUTUBE_DATA_API_KEY`, `OPENAI_API_KEY`, 선택값 `OPENAI_MODEL`을 사용한다.

## 경계 규칙
- API route와 backend domain code는 `backend/` 아래에만 둔다.
- Vue 컴포넌트는 `frontend/` 아래의 SFC와 Composition API로 작성한다.
- Frontend는 외부 provider API를 직접 호출하지 않는다.

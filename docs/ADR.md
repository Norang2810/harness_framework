# Architecture Decision Records

## ADR-001: Split Laravel API and Vue SPA
**결정**: Laravel API는 `backend/`, Vue SPA는 `frontend/`에 분리한다.

**이유**: API 책임과 UI 책임을 명확히 나누고, 요청된 Laravel/Vue 스택을 그대로 따른다.

**트레이드오프**: 개발 서버와 의존성 트리가 둘로 나뉜다.

## ADR-002: MVP keeps analysis ephemeral
**결정**: MVP 분석 결과는 DB에 저장하지 않고 현재 Vue 화면 세션 상태에만 유지한다.

**이유**: 첫 MVP는 단일 채널 URL 파싱과 분석 UI 검증이 목적이며, 저장/계정/히스토리는 제품 판단 이후에 붙인다.

**트레이드오프**: 새로고침이나 탭 종료 시 결과가 사라진다.

## ADR-003: Backend owns provider integration
**결정**: Vue 앱은 Laravel API만 호출하고 YouTube, transcript, AI provider API를 직접 호출하지 않는다.

**이유**: 인증키, provider 정책, rate limit, 오류 처리를 backend 경계 안에 둔다.

**트레이드오프**: provider 연동 전에도 backend 응답 계약을 먼저 설계해야 한다.

## ADR-004: No external YouTube integrations in MVP
**결정**: YouTube OAuth와 YouTube transcript/text API는 MVP에서 제외하되, 공개 채널 메타데이터 조회를 위한 YouTube Data API `channels.list`는 backend에서 사용한다.

**이유**: 단일 채널 분석 품질을 검증하려면 최소한의 실제 채널 제목/설명/통계가 필요하지만, OAuth나 transcript 권한은 아직 필요하지 않다.

**트레이드오프**: API key와 quota 관리가 필요하다.

## ADR-005: OpenAI Responses API for Korean analysis
**결정**: 한국어 분석 생성은 Laravel에서 OpenAI Responses API `POST /v1/responses`로 수행한다.

**이유**: frontend에 provider key를 노출하지 않고, 최신 OpenAI text generation 권장 인터페이스를 사용한다.

**트레이드오프**: provider 장애나 malformed JSON을 validation-style 오류로 사용자에게 설명해야 한다.

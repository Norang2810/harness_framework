# PRD: YouTube Channel Insight

## 목표
YouTube 채널 URL을 입력하면 Laravel API가 채널 식별자를 정규화하고, YouTube Data API와 OpenAI Responses API를 backend에서 호출해 Vue SPA에 한국어 단일 채널 분석을 보여준다.

## MVP 범위
- 단일 YouTube 채널 분석만 지원한다.
- 분석 언어는 한국어로 고정한다.
- 분석 결과는 현재 화면 세션의 Vue 상태에만 유지한다.
- 저장소 DB, 사용자 계정, 분석 히스토리는 만들지 않는다.
- Vue 앱은 Laravel API만 호출하며 YouTube나 AI provider API를 직접 호출하지 않는다.
- API 키는 `YOUTUBE_DATA_API_KEY`, `OPENAI_API_KEY`, 선택값 `OPENAI_MODEL`로 backend 환경에만 둔다.

## MVP 제외 범위
- YouTube OAuth
- YouTube transcript/text API 연동
- 경쟁 채널 비교
- 예약 리포트
- 피드백 메일 내보내기
- 서버 DB 저장과 장기 보관

## 사용자 흐름
1. 사용자가 Vue 화면에서 YouTube 채널 URL을 입력한다.
2. Vue 앱이 Laravel의 분석 API로 URL을 전송한다.
3. Laravel이 URL을 검증하고 채널 식별자를 정규화한다.
4. Laravel이 YouTube Data API `channels.list`로 공개 채널 메타데이터를 조회한다.
5. Laravel이 OpenAI Responses API로 한국어 분석 JSON을 생성한다.
6. Vue 화면이 결과를 현재 세션 상태로 표시한다.

## 수용 기준
- 지원 URL: `youtube.com/@handle`, `youtube.com/channel/{id}`, `youtube.com/c/{name}`, `youtube.com/user/{name}`.
- 비 YouTube URL 또는 채널을 특정할 수 없는 URL은 검증 오류로 처리한다.
- 결과 카드에는 실제 채널 제목, 채널 ID, 통계, 분석 언어, 핵심 관찰, 다음 액션이 표시된다.
- 새로고침하면 분석 결과가 사라져도 된다.

## 구현 순서
문서 업데이트를 먼저 완료한 뒤 TDD로 진행한다.

1. URL 파서 테스트 작성 후 구현
2. Laravel 서비스 API 라우트 테스트 작성 후 구현
3. Vue UI 테스트 작성 후 구현

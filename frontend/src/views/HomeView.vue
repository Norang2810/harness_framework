<script setup lang="ts">
import { computed, ref } from 'vue'

interface ChannelStatistics {
  subscriber_count: number | null
  video_count: number | null
  view_count: number | null
}

interface ChannelAnalysis {
  status: string
  language: 'ko'
  storage: 'session_only'
  channel: {
    type: string
    value: string
    canonical_url: string
    id: string
    title: string
    description: string
    published_at?: string | null
    statistics: ChannelStatistics
  }
  source: {
    youtube: {
      provider: string
      endpoint: string
      lookup: string
    }
    openai: {
      provider: string
      model: string
    }
  }
  summary: string
  observations: string[]
  next_actions: string[]
}

const channelUrl = ref('')
const analysis = ref<ChannelAnalysis | null>(null)
const errorMessage = ref('')
const isSubmitting = ref(false)

const statusLabel = computed(() => {
  if (isSubmitting.value) return '분석 요청 중'
  if (analysis.value) return '분석 완료'
  if (errorMessage.value) return '확인 필요'
  return '대기 중'
})

function formatCount(value: number | null | undefined): string {
  return typeof value === 'number' ? value.toLocaleString('ko-KR') : '비공개'
}

async function submitAnalysis() {
  errorMessage.value = ''
  isSubmitting.value = true

  try {
    const response = await fetch('/api/analysis', {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ channel_url: channelUrl.value }),
    })
    const payload = await response.json()

    if (!response.ok) {
      errorMessage.value =
        payload.errors?.channel_url?.[0] ?? payload.message ?? '채널 URL을 다시 확인해 주세요.'
      return
    }

    analysis.value = payload.data
  } catch {
    errorMessage.value = 'Laravel API에 연결할 수 없습니다.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="workspace">
    <section class="intro" aria-labelledby="page-title">
      <div>
        <p class="eyebrow">Live MVP</p>
        <h1 id="page-title">YouTube Channel Insight</h1>
        <p class="lede">
          YouTube 공개 채널 데이터를 가져와 한국어 분석 초안을 생성합니다. 결과는 현재 화면 세션에만 유지됩니다.
        </p>
      </div>
      <div class="scope-list" aria-label="MVP 범위">
        <span>단일 채널</span>
        <span>한국어 분석</span>
        <span>DB 저장 없음</span>
      </div>
    </section>

    <section class="analysis-grid">
      <form class="input-panel" @submit.prevent="submitAnalysis">
        <div>
          <label for="channel-url">채널 URL</label>
          <input
            id="channel-url"
            v-model="channelUrl"
            name="channelUrl"
            type="url"
            placeholder="https://www.youtube.com/@example"
            autocomplete="off"
            required
          />
        </div>

        <button type="submit" :disabled="isSubmitting">
          {{ isSubmitting ? '분석 중' : '분석하기' }}
        </button>

        <p class="status" role="status">{{ statusLabel }}</p>
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      </form>

      <section class="result-panel" aria-label="분석 결과">
        <div v-if="analysis" class="result-content">
          <div class="result-header">
            <div>
              <p class="eyebrow">분석 채널</p>
              <h2>{{ analysis.channel.title || analysis.channel.value }}</h2>
              <p class="channel-id">{{ analysis.channel.id }}</p>
            </div>
            <span>{{ analysis.language === 'ko' ? '한국어' : analysis.language }}</span>
          </div>

          <a class="canonical-link" :href="analysis.channel.canonical_url" target="_blank" rel="noreferrer">
            {{ analysis.channel.canonical_url }}
          </a>

          <dl class="metrics">
            <div>
              <dt>구독자</dt>
              <dd>{{ formatCount(analysis.channel.statistics.subscriber_count) }}</dd>
            </div>
            <div>
              <dt>영상</dt>
              <dd>{{ formatCount(analysis.channel.statistics.video_count) }}</dd>
            </div>
            <div>
              <dt>조회수</dt>
              <dd>{{ formatCount(analysis.channel.statistics.view_count) }}</dd>
            </div>
          </dl>

          <p v-if="analysis.channel.description" class="description">
            {{ analysis.channel.description }}
          </p>

          <p class="summary">{{ analysis.summary }}</p>

          <div class="result-columns">
            <div>
              <h3>핵심 관찰</h3>
              <ul>
                <li v-for="item in analysis.observations" :key="item">{{ item }}</li>
              </ul>
            </div>
            <div>
              <h3>다음 액션</h3>
              <ul>
                <li v-for="item in analysis.next_actions" :key="item">{{ item }}</li>
              </ul>
            </div>
          </div>

          <p class="source-note">
            YouTube {{ analysis.source.youtube.endpoint }} · OpenAI {{ analysis.source.openai.model }} · 현재 화면 세션
          </p>
        </div>

        <div v-else class="empty-state">
          <p class="eyebrow">결과 대기</p>
          <h2>채널 URL을 입력하면 실제 공개 데이터 기반 분석이 여기에 표시됩니다.</h2>
          <p>YouTube OAuth, transcript API, 경쟁 채널 비교, 예약 리포트, 메일 내보내기는 MVP 이후 범위입니다.</p>
        </div>
      </section>
    </section>
  </main>
</template>

<style scoped>
.workspace {
  width: min(1120px, 100%);
  margin: 0 auto;
  padding: 48px 24px;
}

.intro {
  display: flex;
  gap: 24px;
  align-items: end;
  justify-content: space-between;
  margin-bottom: 28px;
}

.eyebrow {
  margin-bottom: 8px;
  color: #5c6470;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0;
  text-transform: uppercase;
}

h1,
h2,
h3,
p {
  margin: 0;
}

h1 {
  color: #18202a;
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 800;
  line-height: 1;
}

.lede {
  max-width: 680px;
  margin-top: 16px;
  color: #4d5662;
  font-size: 1.05rem;
}

.scope-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-end;
}

.scope-list span,
.result-header span {
  border: 1px solid #ccd3dc;
  border-radius: 999px;
  padding: 6px 10px;
  color: #2f3947;
  font-size: 0.82rem;
  font-weight: 700;
  white-space: nowrap;
}

.analysis-grid {
  display: grid;
  grid-template-columns: minmax(280px, 360px) 1fr;
  gap: 20px;
  align-items: start;
}

.input-panel,
.result-panel {
  border: 1px solid #d7dde5;
  border-radius: 8px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(24, 32, 42, 0.08);
}

.input-panel {
  display: grid;
  gap: 16px;
  padding: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: #2f3947;
  font-weight: 800;
}

input {
  width: 100%;
  border: 1px solid #bbc4cf;
  border-radius: 6px;
  padding: 12px;
  color: #18202a;
  font: inherit;
}

button {
  border: 0;
  border-radius: 6px;
  padding: 12px 16px;
  background: #166534;
  color: #ffffff;
  cursor: pointer;
  font: inherit;
  font-weight: 800;
}

button:disabled {
  cursor: progress;
  opacity: 0.7;
}

.status {
  color: #4d5662;
  font-weight: 700;
}

.error {
  color: #b42318;
  font-weight: 700;
}

.result-panel {
  min-height: 420px;
  padding: 24px;
}

.result-content {
  display: grid;
  gap: 18px;
}

.result-header {
  display: flex;
  gap: 16px;
  align-items: start;
  justify-content: space-between;
}

h2 {
  color: #18202a;
  font-size: 1.55rem;
  font-weight: 800;
  line-height: 1.2;
}

.channel-id,
.source-note {
  color: #687382;
  font-size: 0.86rem;
}

.canonical-link {
  color: #0f5f9d;
  overflow-wrap: anywhere;
}

.metrics {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
  margin: 0;
}

.metrics div {
  border: 1px solid #e1e6ee;
  border-radius: 8px;
  padding: 12px;
}

dt {
  color: #687382;
  font-size: 0.78rem;
  font-weight: 700;
}

dd {
  margin: 4px 0 0;
  color: #18202a;
  font-size: 1.2rem;
  font-weight: 800;
}

.description {
  color: #4d5662;
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
}

.summary {
  border-left: 4px solid #166534;
  padding-left: 14px;
  color: #2f3947;
  font-size: 1rem;
}

.result-columns {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
}

h3 {
  margin-bottom: 10px;
  color: #2f3947;
  font-size: 1rem;
  font-weight: 800;
}

ul {
  display: grid;
  gap: 10px;
  margin: 0;
  padding-left: 20px;
  color: #4d5662;
}

.empty-state {
  display: grid;
  min-height: 360px;
  align-content: center;
  gap: 12px;
  color: #4d5662;
}

.empty-state h2 {
  max-width: 520px;
}

@media (max-width: 760px) {
  .workspace {
    padding: 28px 16px;
  }

  .intro,
  .result-header {
    align-items: stretch;
    flex-direction: column;
  }

  .scope-list {
    justify-content: flex-start;
  }

  .analysis-grid,
  .result-columns,
  .metrics {
    grid-template-columns: 1fr;
  }
}
</style>

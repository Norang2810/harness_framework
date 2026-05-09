import { mount } from '@vue/test-utils'
import { afterEach, describe, expect, it, vi } from 'vitest'
import HomeView from './HomeView.vue'

const analysisResponse = {
  data: {
    status: 'ready',
    language: 'ko',
    storage: 'session_only',
    channel: {
      type: 'handle',
      value: '@example',
      canonical_url: 'https://www.youtube.com/channel/UC123',
      id: 'UC123',
      title: 'Example Channel',
      description: 'A channel about examples.',
      statistics: {
        subscriber_count: 1000,
        video_count: 12,
        view_count: 34000,
      },
    },
    source: {
      youtube: {
        provider: 'youtube_data_api',
        endpoint: 'channels.list',
        lookup: 'forHandle',
      },
      openai: {
        provider: 'openai_responses',
        model: 'gpt-5.2',
      },
    },
    summary: '한국어 분석 초안입니다.',
    observations: ['포지셔닝을 빠르게 점검하세요.', '업로드 리듬을 확인하세요.', '대표 콘텐츠를 분류하세요.'],
    next_actions: ['채널 소개를 정리하세요.', '상위 영상 주제를 묶으세요.', '다음 실험을 고르세요.'],
  },
}

describe('HomeView', () => {
  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('submits a channel url to the Laravel analysis API and renders real Korean results', async () => {
    const jsonMock = vi.fn<() => Promise<typeof analysisResponse>>().mockResolvedValue(analysisResponse)
    const fetchMock = vi.fn<typeof fetch>().mockResolvedValue({
      ok: true,
      json: jsonMock,
    } as unknown as Response)
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(HomeView)

    await wrapper.get('input[name="channelUrl"]').setValue('https://www.youtube.com/@example')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledOnce())

    expect(fetchMock).toHaveBeenCalledWith('/api/analysis', {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ channel_url: 'https://www.youtube.com/@example' }),
    })
    expect(wrapper.text()).toContain('Example Channel')
    expect(wrapper.text()).toContain('UC123')
    expect(wrapper.text()).toContain('구독자')
    expect(wrapper.text()).toContain('1,000')
    expect(wrapper.text()).toContain('한국어 분석 초안입니다.')
    expect(wrapper.text()).toContain('현재 화면 세션')
  })

  it('renders validation-style provider errors', async () => {
    const errorResponse = {
      message: 'YouTube Data API 요청에 실패했습니다.',
      errors: { channel_url: ['YouTube Data API 요청에 실패했습니다.'] },
    }
    const jsonMock = vi.fn<() => Promise<typeof errorResponse>>().mockResolvedValue(errorResponse)
    const fetchMock = vi.fn<typeof fetch>().mockResolvedValue({
      ok: false,
      json: jsonMock,
    } as unknown as Response)
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(HomeView)

    await wrapper.get('input[name="channelUrl"]').setValue('https://www.youtube.com/@missing')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.text()).toContain('YouTube Data API 요청에 실패했습니다.'))
  })
})

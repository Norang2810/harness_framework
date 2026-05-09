# Step 4: vue-ui-polish

Update Vue UI for real analysis output.

Read:
- `frontend/src/views/HomeView.vue`
- `frontend/src/views/HomeView.spec.ts`
- `docs/UI_GUIDE.md`

Requirements:
- Render channel title, ID, canonical URL, subscriber/video/view counts where present.
- Render Korean summary, observations, and next actions.
- Keep result in current screen session only.
- Show loading and provider validation errors clearly.
- Do not introduce direct YouTube/OpenAI calls or API key usage.

Acceptance:
- `npm run lint`
- `npm run test`
- `npm run build`

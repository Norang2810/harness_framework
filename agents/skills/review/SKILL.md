---
name: review
description: "Use when the user asks to review repository changes against AGENTS.md, docs/ARCHITECTURE.md, docs/ADR.md, and the split Laravel API plus Vue SPA architecture."
---

# Review

Use this skill to review changes against the repository rules and the Laravel/Vue split architecture.

## Read First

- `/AGENTS.md`
- `/docs/ARCHITECTURE.md`
- `/docs/ADR.md`
- `/docs/PRD.md`

If UI changed, also read:

- `/docs/UI_GUIDE.md`

## Checklist

1. Architecture compliance
Backend API code must stay in `backend/`; frontend SPA code must stay in `frontend/`; Harness skills must stay in `agents/skills/`.

2. Stack compliance
Laravel handles API routing, validation, and provider integration. Vue 3 with Vite handles SPA UI. Do not reintroduce Next.js/React files.

3. Test coverage
Backend changes need Laravel tests. Frontend behavior changes need Vitest coverage where practical.

4. Critical rules
Check AGENTS.md CRITICAL rules, especially TDD and "frontend calls Laravel API, not external APIs directly."

5. Verification
Prefer these root commands:

```bash
npm run lint
npm run test
npm run build
```

## Output

Use findings-first review style for code review requests. For a checklist-only review, use:

| Item | Result | Notes |
|------|--------|-------|
| Architecture compliance | PASS/FAIL | Evidence |
| Stack compliance | PASS/FAIL | Evidence |
| Tests | PASS/FAIL | Evidence |
| Critical rules | PASS/FAIL | Evidence |
| Build/test readiness | PASS/FAIL | Evidence |

When something fails, include a concrete fix path.

## Guardrails

- Do not claim compliance before reading the relevant files.
- Do not treat missing tests as acceptable for risky behavior changes.
- Do not review generated dependency folders such as `vendor/`, `node_modules/`, or build outputs unless the issue is specifically there.

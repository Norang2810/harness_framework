---
name: harness
description: "Use when the user wants to work in this repository's Harness workflow for the split Laravel API and Vue SPA setup: read docs, discuss implementation decisions, design phase steps, create phases metadata, or run scripts/execute.py."
---

# Harness

This skill guides phased work for the split Laravel API and Vue SPA repository.

## When To Use

- The user asks for an implementation plan split into executable steps.
- Work needs `phases/` metadata or `stepN.md` files.
- A task should be executed through `scripts/execute.py`.
- The change touches the Laravel backend, Vue frontend, or shared Harness workflow.

## Discovery

Read these first:

- `/AGENTS.md`
- `/docs/PRD.md`
- `/docs/ARCHITECTURE.md`
- `/docs/ADR.md`
- `/docs/UI_GUIDE.md` when frontend UI is involved

Then inspect relevant implementation paths:

- `backend/` for Laravel API, validation, resources, tests, and provider integration.
- `frontend/` for Vue 3 SPA, Vite, TypeScript, components, router, and client state.
- `scripts/execute.py` for Harness phase execution behavior.

## Step Design Rules

1. Keep scope small.
One step should handle one layer or module. Split backend API, frontend UI, and shared workflow changes into separate steps unless coupling is unavoidable.

2. Make each step self-contained.
Do not refer to previous conversation context. Include required docs, files, decisions, and constraints in the step file.

3. Force context reading.
List relevant files from `backend/`, `frontend/`, docs, and previous step outputs.

4. Give interface-level direction.
Specify route names, request/response shapes, component contracts, or command behavior. Leave implementation details to the executing agent unless correctness depends on them.

5. Use executable acceptance criteria.
Prefer root commands:

```bash
npm run lint
npm run test
npm run build
```

6. State concrete prohibitions.
Use `Do not do X. Reason: Y`.

7. Use kebab-case names.
Step names should be short slugs such as `api-boundary`, `vue-shell`, or `analysis-preview`.

## Phase Files

Create or update:

- `phases/index.json`
- `phases/{task-name}/index.json`
- `phases/{task-name}/step{N}.md`

`phases/{task-name}/index.json` should use:

```json
{
  "project": "YouTube Channel Insight",
  "phase": "<task-name>",
  "steps": [
    { "step": 0, "name": "api-boundary", "status": "pending" }
  ]
}
```

Status values:

- `pending`
- `completed`
- `error`
- `blocked`

On completion, add a one-line `summary` with files changed and key decisions. `execute.py` uses this as context for later steps.

## Execution

```bash
python scripts/execute.py {task-name}
python scripts/execute.py {task-name} --push
```

Execution handles branch creation, guardrail injection, step context accumulation, retry feedback, separated code/metadata commits, and timestamps.

## Recovery

- For `error`, reset the failed step status to `pending`, remove `error_message`, and rerun.
- For `blocked`, resolve the external blocker, reset status to `pending`, remove `blocked_reason`, and rerun.

## Repository Rules

- Backend API work belongs in `backend/`.
- Frontend SPA work belongs in `frontend/`.
- Vue must not call external provider APIs directly; use Laravel endpoints.
- Do not recreate `.codex/commands` prompts. Harness skills live under `agents/skills`.

# AI Tooling & Development Process

This document records the AI tools used to build the secure quote-draft demo
and the process followed, so reviewers can see that the briefs were completed
**before** any implementation began.

## AI tools used

| Tool | Role |
|---|---|
| **OpenCode** | Agent runtime / IDE where the work happened (file edits, terminal, sub-agent delegation). |
| **Gentle AI** | Orchestration layer: the ODD (Organic Driven Development) workflow, brief templates, delegation rules, and the Judgment Day adversarial review (blind judges + bounded fix actor). |
| **Engram** | Persistent memory (MCP): saved decisions, discoveries, and the final architecture across sessions. |
| **Archify** | Generated the workflow diagram (actors + approval gate) from a typed JSON specification. |

> **Orca** (CLI / worktree management) is available in this environment but was
> not used for this task.

## Process: briefs first (ODD)

Before writing any code, two briefs were completed following Gentle AI's
templates:

- **`brief_fix_quote_ia_EN.md`** — change brief (**FIX**) on the existing
  quote-draft system, following the `sdd-change-template`.
- **`demo_brief_quote_ia_EN.md`** — product brief for the demo, following the
  `sdd-new-template`.

These briefs define the problem, scope, constraints, success criteria, and
out-of-scope boundaries before implementation.

## Development flow

1. **Briefs completed** (see above) — the change brief and the demo brief.
2. **TDD** — tests written first (RED), then implementation (GREEN).
3. **Hexagonal architecture** (ports & adapters) with dependency injection.
4. **Coverage extended** to all scenarios — 29 tests green.
5. **Judgment Day** — two blind adversarial judges; 0 CRITICAL, 3 fixes
   applied, final verdict `APPROVED`.

## Deliverables

- **`demo-quote-fix/`** — the Laravel 13 + SQLite demo (see its `README.md` for
  setup and how to reproduce the challenge).
- **`brief_fix_quote_ia_EN.md`** — the change brief (FIX).
- **`demo_brief_quote_ia_EN.md`** — the demo product brief.

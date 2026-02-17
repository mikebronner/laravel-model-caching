# Workflow

## CRITICAL: File Operation Approval Required

**THIS RULE IS ABSOLUTE. NO EXCEPTIONS.**

### Before ANY File Operation (create, edit, delete, write):

1. **STOP** — Do not proceed automatically
2. **CHECK** — Has user explicitly approved in last 3 messages?
3. **IF NO** — Ask first and wait for confirmation

### Approval Signals

Proceed only if user said: "yes", "proceed", "go ahead", "do it", "option 1/2/3", or explicit instruction.

### Remember

- Long sessions don't exempt you
- Proactive file creation is NOT allowed
- When in doubt, ASK FIRST

**VIOLATING THIS RULE DAMAGES USER TRUST.**

---

## Sub-Agent Orchestration

**THIS RULE IS MANDATORY FOR NON-TRIVIAL TASKS.**

### Pre-Task Checklist

**STOP. Before starting ANY task, verify:**

- [ ] Does this require domain expertise? → **MUST spawn specialist**
- [ ] Does this involve research/analysis/evaluation? → **MUST spawn `research-analyst`**
- [ ] Are there multiple independent sub-tasks? → **MUST spawn agents in parallel**
- [ ] Does user mention: security, test, review, refactor, debug, optimize? → **MUST spawn matching skill**

### If ANY Box is Checked

You MUST spawn sub-agents BEFORE attempting the task yourself.

### Core Principle

**Research in parallel, act with approval.** Sub-agents investigate and recommend. File operations still require user approval per the rules above.

### Quick Trigger Reference

| If User Says... | Spawn |
|-----------------|-------|
| review, PR, check this | `code-reviewer` |
| test, coverage, edge case | `pest-testing` |
| security, vulnerabilities | `security-auditor` |
| refactor, clean up, simplify | `refactoring-specialist` |
| bug, error, not working | `debugger` |
| migration, schema, database | `database-administrator` |
| evaluate, compare, analyze, research | `research-analyst` |
| find, locate, search | `search-specialist` |
| architecture, design, scalability | `architect-reviewer` |
| payments, transactions, financial | `fintech-engineer` |

### Do NOT Spawn When

- Task is trivial (single-line fix, typo correction)
- Information is already in context
- User explicitly wants inline handling
- Sub-agent would just re-read files already in conversation

### Transparency

Always inform the user when spawning sub-agents and summarize findings when complete.

---

## MCP Tool Usage

### Documentation First

Use `search-docs` before making code changes to ensure the correct approach for the installed package versions.

### Debugging

- Use `last-error` and `browser-logs` to diagnose issues before guessing at fixes.
- Use `tinker` to execute PHP for debugging or querying Eloquent models directly.

### Database

- Use `database-query` for read-only database access instead of raw SQL or tinker.
- Use `database-schema` to understand table structure before writing migrations or queries.

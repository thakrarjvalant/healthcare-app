# GitHub Project: Healthcare App

This repository contains the Healthcare Management System. This file documents a recommended GitHub Project (kanban) setup and step-by-step commands to create it using the GitHub CLI or the web UI.

Suggested columns:
- Backlog (triaged features & ideas)
- To do (ready for work)
- In progress
- Review / QA
- Done

Suggested labels:
- bug
- enhancement
- documentation
- help wanted
- priority:high
- priority:low

Creating the project (recommended — requires GitHub CLI `gh`):
1. Install and authenticate the GitHub CLI: `gh auth login`.
2. From the repository root run (replace placeholders):

```powershell
# create a remote repo if you haven't yet (interactive)
gh repo create <OWNER>/healthcare-app --public --source="." --remote=origin --push

# create a project (projects v2 or v1 depending on your account); example for a repository-scoped project:
gh project create "Healthcare App" --repo <OWNER>/healthcare-app --body "Project board for the healthcare-app repository"

# create columns (if using classic Projects v1)
gh project column create "Backlog" --project "Healthcare App" --repo <OWNER>/healthcare-app
gh project column create "To do" --project "Healthcare App" --repo <OWNER>/healthcare-app
gh project column create "In progress" --project "Healthcare App" --repo <OWNER>/healthcare-app
gh project column create "Review / QA" --project "Healthcare App" --repo <OWNER>/healthcare-app
gh project column create "Done" --project "Healthcare App" --repo <OWNER>/healthcare-app
```

If you prefer the web UI: open your repository on GitHub -> Projects -> New project -> choose "Board" and create the same columns and labels.

Automation ideas (later):
- Use GitHub Actions to automatically create issues/cards from PRs or labels.
- Add the `labels.yml` and `.github/issue_template` files (already added) to keep issues consistent.

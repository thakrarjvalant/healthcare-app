# Healthcare Management System (Repository)

A comprehensive healthcare management system built with a microservices architecture.

This repository contains the full project and a small set of repository-support files to help you push this workspace to GitHub and create a Project board.

## Quick repository setup (Windows PowerShell)

Prerequisites: Git, GitHub CLI (`gh`), Docker, Docker Compose, Node.js, PHP, Composer

From the repo root (example path on this machine):

```powershell
cd d:\customprojects\healthcare-app
# initialize local git
git init
git add .
git commit -m "Initial import"

# create remote repo and push (interactive)
gh auth login
gh repo create <OWNER>/healthcare-app --public --source="." --remote=origin --push

# create a repository-scoped project (classic Projects v1 example)
gh project create "Healthcare App" --repo <OWNER>/healthcare-app --body "Project board for healthcare-app"

# Optionally create columns (classic projects)
gh project column create "Backlog" --project "Healthcare App" --repo <OWNER>/healthcare-app
gh project column create "To do" --project "Healthcare App" --repo <OWNER>/healthcare-app
gh project column create "In progress" --project "Healthcare App" --repo <OWNER>/healthcare-app
gh project column create "Review / QA" --project "Healthcare App" --repo <OWNER>/healthcare-app
gh project column create "Done" --project "Healthcare App" --repo <OWNER>/healthcare-app
```

Notes:
- Run `gh auth login` first to authenticate the GitHub CLI.
- GitHub Projects v2 has a different API and `gh` support varies by account; you can create Projects v2 from the web UI if needed.

## What I added for you

- `.gitignore` — ignores node_modules, vendor, env files, build artifacts, and common editor files.
- `.github/workflows/ci.yml` — a basic CI workflow that checks frontend deps and runs quick checks on PHP files.
- `.github/issue_templates/*` — templates for bugs and feature requests.
- `.github/labels.yml` — a starter label set.
- `.github/PROJECT_BOARD.md` — guidance for creating a project board.

## Local dev quickstart

1. Build and start services with Docker Compose:

```powershell
cd d:\customprojects\healthcare-app
docker-compose up --build -d
```

2. Frontend

```powershell
cd frontend
npm install
npm start
```

3. Backend (example `user-service`)

```powershell
cd backend\user-service
composer install
php -S localhost:8000
```

## CI and Issues

- See `.github/workflows/ci.yml` for the CI job.
- Use `.github/issue_templates` when creating issues to keep reports consistent.

If you want, I can (A) provide the exact PowerShell commands you can run locally to create the remote repo and project, or (B) attempt to run them for you here — option (B) requires that you supply a GitHub token with repo and project permissions (not recommended to paste tokens in chat). Tell me which you prefer.
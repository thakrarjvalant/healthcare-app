# API Gateway (PHP) — documentation

This document describes the lightweight PHP API Gateway (gateway.php) and the example nginx configuration (nginx.conf) used to proxy frontend requests to backend microservices.

Overview
- The gateway is a single PHP script (gateway.php) that proxies requests under `/api/*` to backend services running on different ports.
- nginx is configured to forward `/api/` requests to `gateway.php` via PHP-FPM.
- Frontend should use the gateway base URL, e.g. `REACT_APP_API_BASE_URL=http://localhost:8000/api`.

Route mapping
- Default route-to-target mapping used by the gateway:
  - `/api/users`         → http://127.0.0.1:8001
  - `/api/appointments`  → http://127.0.0.1:8002
  - `/api/clinical`      → http://127.0.0.1:8003
  - `/api/notifications` → http://127.0.0.1:8004
  - `/api/billing`       → http://127.0.0.1:8005
  - `/api/storage`       → http://127.0.0.1:8006
  - `/api/admin`         → http://127.0.0.1:8007

nginx integration
- The provided `backend/api-gateway/nginx.conf` listens on port 8000 and forwards `/api/*` to `gateway.php` through PHP-FPM.
- Update these values in the nginx config:
  - `fastcgi_param SCRIPT_FILENAME` — point to the real filesystem path of `gateway.php`.
  - `fastcgi_pass` — set to your PHP-FPM socket or host:port (e.g. `unix:/run/php/php8.0-fpm.sock` or `127.0.0.1:9000`).
  - `root` — optional, set to your webroot if needed.
- Example health endpoint: `/health` proxies to the gateway and returns JSON status.

Deployment & local testing
1. Ensure backend services are running on their ports (8001..8007).
2. Start PHP-FPM and configure nginx with the provided config fragment (adjust paths).
3. Reload nginx: `sudo nginx -t && sudo systemctl reload nginx`
4. Verify:
   - Health: `curl http://localhost:8000/health`
   - Proxy: `curl http://localhost:8000/api/users/health` (or an endpoint served by the user service)

Security and headers
- The PHP gateway forwards Authorization header and other non hop-by-hop headers. Ensure sensitive headers are handled appropriately.
- In production:
  - Enable SSL between clients and nginx (TLS at nginx).
  - Consider mutual TLS or authenticated internal network between nginx/gateway and backend services.
  - Disable `CURLOPT_SSL_VERIFYPEER=false` if used — enable proper CA verification.
  - Rate-limit and add request logging/monitoring as needed.

Timeouts and retries
- The gateway uses a configurable cURL timeout; tune it to suit backend SLAs.
- For improved resilience consider adding basic retries, circuit-breaker logic, or switching to a dedicated gateway (e.g., HAProxy/Traefik) if you need advanced features.

Notes
- If you prefer to serve the gateway as a standalone PHP server for development: `php -S 0.0.0.0:8000 gateway.php` (not recommended for production).
- You can add this gateway to docker-compose and expose port 8000; ensure networking between containers is configured so nginx/PHP-FPM can reach backend services.

If you want, I can:
- Patch gateway.php or nginx.conf to match your exact filesystem and PHP-FPM socket paths.
- Add a docker-compose service for the gateway (PHP-FPM + nginx) and show the compose fragment.

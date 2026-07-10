## Logistics Service Deployment Guide

### Prerequisites

- PHP and extensions required by the main application
- Database is reachable for the main application
- Web server routes `/logistics` to the same Laravel app (path-based deployment)

### Deploy Steps

1. Deploy application code
2. Run migrations
   - Ensure the new tables exist:
     - `service_sessions`
     - `logistics_profiles`
3. Clear caches
   - Config cache, route cache, view cache
4. Verify routing
   - `/logistics` loads the public logistics landing
   - `/explore/logistics` returns a permanent redirect to `/logistics`
5. Verify auth flows
   - New user registration on `/logistics/register` creates a Fuwa.ng user and a logistics profile, then redirects to `/logistics/dashboard`
   - Existing user can use `/logistics/login` and is redirected to `/logistics/dashboard`
   - Existing logged-in user can use “Login with Fuwa.ng Account” and is redirected to `/logistics/dashboard`

### Rollback Procedure

1. Revert application code to the previous version
2. Roll back migrations (only the new tables)
   - Roll back `create_logistics_profiles_table`
   - Roll back `create_service_sessions_table`
3. Clear caches
4. Validate that legacy `/explore/logistics` still functions (or keep the redirect if desired)

### Optional Subdomain Routing

If you want `logistics.fuwa.ng` in addition to `fuwa.ng/logistics`, configure your reverse proxy to route `logistics.fuwa.ng/*` to the same Laravel app and rewrite the path prefix to `/logistics` (or add a dedicated domain route group).

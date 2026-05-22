# EditPro Local + Deployment Fixes Checklist

## What was fixed
- [x] Confirmed PHP dev server runs on `http://127.0.0.1:8000` with `router.php`
- [x] Fixed router root route (`/`) to show `about.html` (was 404)
- [x] Restored missing `backend/editor_login.php` from `EditPro_site.zip`
- [x] Fixed fatal error in `backend/editor_dashboard.php` by adding missing timer columns to MySQL:
  - [x] Added `timer_started_at`, `timer_duration`, `timer_status`, `timer_completed_at`, `download_count`, `early_submit_enabled`
- [x] Restored missing `backend/team_members.php` from `EditPro_site.zip`

## Remaining (for LinkedIn full working)
- [ ] Ensure you deploy to an environment with PHP + MySQL enabled
- [ ] Ensure MySQL connection vars are set in that environment (or setup uses local defaults)
- [ ] Verify these URLs after deploy:
  - [ ] `/` loads properly
  - [ ] `/request.html` loads and submits
  - [ ] `/backend/admin_login.php` works
  - [ ] `/backend/editor_login.php` works
  - [ ] `/backend/team_members.php` works
  - [ ] `/backend/editor_dashboard.php` works after editor login
  - [ ] order save + dashboard rendering works end-to-end


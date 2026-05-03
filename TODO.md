# Editor Timer Implementation TODO

## Task Requirements
1. Timer starts exactly when video is downloaded (not on page load)
2. Prevent screen lock while timer is active
3. Early submit option - allow submission if editing completes 40 min before timer ends
4. Submit button available for early completion

## Implementation Steps

### Step 1: Database Schema Update
- [x] Add timer columns to `orders` table (timer_started_at, timer_duration, timer_status, download_count)
- [x] Create migration script `backend/setup_timer_columns.php`

### Step 2: Backend API Endpoints
- [x] Create `backend/start_timer.php` - records timer start on first download
- [x] Create `backend/get_timer_status.php` - returns current timer state
- [x] Create `backend/update_timer_status.php` - handles early submit / status updates

### Step 3: Editor Dashboard Updates
- [ ] Replace direct file links with controlled download buttons
- [ ] Add Timer UI component (countdown display)
- [ ] Implement Wake Lock API to prevent screen lock
- [ ] Add conditional submit buttons (Early Submit vs Regular Submit)
- [ ] Auto-refresh timer via polling

### Step 4: Testing & Validation
- [ ] Test timer starts on first download only
- [ ] Test wake lock works in supported browsers
- [ ] Test early submit (40 min threshold)
- [ ] Test timer expiry handling

---

# FIX: Join Team Download Issue (DONE)

## Problem
Clicking on "Join Team", "Editor", or "Admin" links triggered file download instead of page redirect.

## Root Cause
1. `start-server.sh` was using Python HTTP server which can't execute PHP files
2. `.htaccess` rules weren't properly configured for allowed public pages

## Solution
1. Updated `start-server.sh` to use PHP built-in server instead of Python
2. Fixed `backend/.htaccess` to allow public-facing pages:
   - join_team.php
   - editor_login.php
   - admin_login.php
   - contact.php
   - content_team.php
   - login_content.php
   - team_members.php

## Testing
- Verified join_team.php returns 200 with HTML content
- Verified editor_login.php returns 200 with HTML content
- Verified admin_login.php returns 200 with HTML content


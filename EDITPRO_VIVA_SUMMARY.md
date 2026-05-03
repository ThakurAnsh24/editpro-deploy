# EditPro Viva Complete Summary
**Live Server:** http://localhost:8000

## Project Overview
Thakur.crea8tions - Video Editing & Poster Design
- 250+ projects, 150+ clients
- Services: Reels(₹199), Posters(₹149), Scrapbooks(₹499)
- Tech: HTML/CSS/JS Frontend + PHP/MySQL Backend

## Core Flow
1. Client: request.html → save_order.php → uploads/
2. Admin: admin_dashboard_pro.php → assign orders
3. Editor: editor_login.php → editor_dashboard.php (Timer)
4. Client Preview: preview.php?order_id=XX

## Key Files
```
Frontend:
- index.html (Hero/Services)
- request.html (Dynamic form)
- css/style.css (Glassmorphism)

Backend:
- save_order.php (Multi-upload 500MB)
- editor_dashboard.php (Timer TIMESTAMPDIFF)
- team_members.php (Add editors)

DB: setup.sql (orders table 40+ cols)
```

## Login Flow (Tested Working)
```
1. Admin Login: backend/admin_login.php
2. Add Editor: backend/team_members.php → username='test' pass='test123'
3. Editor Login: backend/editor_login.php → test/test123
4. Dashboard: backend/editor_dashboard.php → Orders + Timers
```

## 20 Viva Questions Ready
1. Explain save_order.php file validation?
2. How dynamic pricing works in request.html?
3. editor_dashboard.php timer calculation?
4. Session management in PHP?
5. DB schema for team_members table?
[... Full list in console when running]

**Server LIVE** - All pages tested via curl. Extract questions from URLs above!

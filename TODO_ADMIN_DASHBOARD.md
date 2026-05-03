# Admin Dashboard Pro Improvements

**Current Issue:** Accept/reject orders not working, need editor assignment dropdown.

**Files Analyzed:**
- backend/admin_dashboard_pro.php: UI with JS calling updateStatus
- backend/admin_pro.php: Handler missing assignment logic
- backend/team_members.php: Lists editors for dropdown

**Plan:**
1. Update backend/admin_pro.php to handle assigned_to param
2. Add editor dropdown in admin_dashboard_pro.php table
3. Fix JS for status + assignment updates
4. Add bulk assignment option

**Steps:**
- [ ] 1. Enhance admin_pro.php for assignment
- [ ] 2. Update admin_dashboard_pro.php UI + JS
- [ ] 3. Test accept/reject/assign flow
- [ ] 4. Complete

Ready to implement?


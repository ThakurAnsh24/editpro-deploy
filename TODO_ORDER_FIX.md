# TODO: Fix Order Submission Not Saving to Backend

## Issue: Orders are not being saved to database
- request.html stores only in localStorage
- No actual submission to backend/save_order.php

## Steps to Fix:

### Step 1: Fix request.html
- [ ] Fix JavaScript form submission to actually call backend/save_order.php
- [ ] Map form field names to PHP expected names
- [ ] Map service values (video-editing → edit, poster-design → poster, etc.)
- [ ] Add proper error handling and success response

### Step 2: Test the fix
- [ ] Submit test order from request.html
- [ ] Verify data saved in database
- [ ] Check admin dashboard displays new order

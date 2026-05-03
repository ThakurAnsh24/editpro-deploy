# FIX_ORDER_SUBMISSION_ACTION_PLAN.md

## Information Gathered

### Current Implementation Analysis:
1. **request.html**: The form has correct name attributes:
   - `name="service_type"` on #mainService (not "mainService")
   - `name="sub_service"` on #subService (not "subService")
   - Form is properly structured with enctype="multipart/form-data"

2. **Current Submit Handler Problem**:
   - The submit event handler does NOT send data to backend
   - It only stores order data in localStorage and shows a modal
   - NO AJAX call is made to backend/save_order.php
   - NO actual database save occurs

3. **backend/save_order.php**:
   - Expects POST with FormData
   - Validates required fields (name, phone, service_type, sub_service, delivery_date, price, payment)
   - Handles file uploads
   - Returns JSON response with success/error

### Task Requirements:
1. Create FormData with all form fields
2. Send AJAX POST to backend/save_order.php
3. Handle JSON response
4. Show success/error message

---

## Plan

### File to Edit:
- `request.html` - fix the form submit handler

### Changes Required:

Replace the current submit handler (around line 230-280) and add AJAX functionality:

1. **Modify form submit handler**:
   - Create FormData from the form using `new FormData(form)`
   - Send AJAX POST to `backend/save_order.php`
   - Use fetch API with async/await

2. **Handle JSON response**:
   - Check `response.success`
   - Show appropriate toast message
   - On success: Show thank you modal with order details
   - On error: Show error toast

3. **Keep existing functionality**:
   - Service selection (updateSubServices)
   - Price updating (updatePrice)
   - File display (fileInput change handler)

---

## Implementation Steps

1. Replace the entire submit event listener to:
   - Prevent default form submission
   - Show loading state
   - Create FormData
   - Send fetch POST to backend/save_order.php
   - Handle JSON response
   - Show success/error messages

2. The form already has correct field names:
   - name, phone, email (Step 1)
   - service_type (mainService), sub_service (subService), price, delivery_date (Step 2)
   - work_file[] (fileInput), description (Step 3)
   - payment (Step 4)

---

## Dependent Files:
- No other files need modification
- The backend/save_order.php is already properly set up

---

## Followup Steps:
1. Test the form submission
2. Verify order is saved in database
3. Check file uploads work correctly

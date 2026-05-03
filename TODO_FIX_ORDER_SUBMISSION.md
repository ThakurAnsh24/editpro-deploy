# TODO - Fix Order Submission to Database

## Task
When user fills the order form and submits, the order should appear in the admin dashboard.

## Root Cause
- request.html only stores order in localStorage
- save_order.php is never called
- No AJAX call to backend

## Steps to Complete

- [ ] 1. Add service type mapping (frontend → backend format)
- [ ] 2. Create AJAX handler to call save_order.php
- [ ] 3. Map all form fields to backend expected format
- [ ] 4. Handle success/error responses
- [ ] 5. Test the fix

## Field Mapping Needed
| Frontend Field | Backend Field | Example |
|----------------|----------------|---------|
| service (video-editing) | service_type (edit) | video-editing → edit |
| subService | sub_service | fitness_reels → fitness_edit |
| priceInput | price | 199 |
| deliveryDate | delivery_date | 2025-01-20 |
| payment | payment_method | UPI |
| description | description | ... |
| fileInput | work_file[] | ... |

## Backend Expected Fields (from save_order.php)
- name, phone
- service_type (edit/poster/scrapbook/invitation)
- sub_service
- delivery_date
- price
- payment
- description
- work_file[] (files)
- payment_screenshot (optional for non-COD)

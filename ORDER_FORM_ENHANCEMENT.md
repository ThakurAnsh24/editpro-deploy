# Order Form Enhancement Plan

## Current State
- Order form already has editing preferences (music, transitions, effects, duration, aspect ratio)
- Payment methods include GPay, PhonePe, Paytm, Pay After Delivery

## Proposed Enhancements

### 1. Additional Editing Preferences (for Video Editing)
- **Color Grading Style**: Cinematic, Vibrant, Muted, Vintage, Natural
- **Output Format**: MP4, MOV, AVI, No Preference
- **Audio Mix Preference**: Balanced, Voice Louder, Music Louder
- **Include Thumbnail**: Yes, No
- **Reference Video Link**: Optional text field
- **Timeline/Storyboard Notes**: Optional textarea

### 2. Additional Payment Methods
- Bank Transfer (with bank details display)
- UPI (with UPI ID display)
- Cash on Delivery
- Credit/Debit Card

### 3. Backend Updates
- Update `save_order.php` to handle new fields
- Update `setup.sql` for new database columns
- Update `admin_orders.php` to display new fields

## Files to Edit
1. `order.html` - Add new form fields
2. `backend/save_order.php` - Handle new fields
3. `backend/setup.sql` - Add new columns
4. `backend/admin_orders.php` - Display new fields

## Implementation Steps
1. Add new preference fields to order form
2. Add new payment methods with conditional displays
3. Update JavaScript to handle new fields
4. Update backend to save and display new data


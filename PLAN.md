# EditPro - Form Enhancement Plan

## Task Summary
1. Add option for users to specify HOW they want their edit (editing preferences/style options)
2. Improve multiple selection of videos/photos in form

## Information Gathered

### Current State Analysis:
1. **order.html**: 
   - Has a basic textarea for "How do you want your edit?" (Description field)
   - Already supports multiple file uploads with drag & drop
   - No pre-defined editing style options

2. **save_order.php**: 
   - Handles multiple file uploads (comma-separated storage)
   - Has `description` field but no structured editing preferences
   - Validates description as plain text

3. **admin_orders.php**: 
   - Displays description field
   - Shows multiple files properly with clickable links
   - No structured editing preferences display

### What Needs to be Added:
1. Pre-defined editing style options (checkboxes/multi-select)
2. Specific editing preference fields (music, transitions, color grading, etc.)
3. Video-specific options (duration, aspect ratio, etc.)
4. Updated backend to store structured preferences
5. Updated admin panel to display preferences clearly

---

## Plan: Detailed Code Updates

### File 1: `order.html`
**Changes:**
1. Add "Editing Preferences" section with checkboxes for:
   - Music Style (Trending, Cinematic, Epic, Chill, Traditional)
   - Transition Style (Smooth, Quick Cuts, Zoom, Glitch)
   - Effects (Color Grading, Slow Motion, Text Overlays, Logo)
   - Duration Preference (Short <30s, Medium 30s-1min, Long >1min)
   - Aspect Ratio (9:16, 16:9, 1:1, 4:5)

2. Enhance the description textarea to have helper text for editing instructions

3. Add a "Special Instructions" field for any additional requirements

4. Update JavaScript to:
   - Show/hide relevant options based on work type (video vs poster)
   - Collect all preferences and send to backend

### File 2: `backend/save_order.php`
**Changes:**
1. Add new fields for structured preferences:
   - `editing_style` - comma-separated style preferences
   - `aspect_ratio` - selected aspect ratio
   - `duration_preference` - preferred duration
   - `special_instructions` - additional instructions

2. Update validation to include new fields

3. Update SQL INSERT to include new columns

### File 3: `backend/admin_orders.php`
**Changes:**
1. Add new columns to the orders table query for preferences

2. Update the table header to include new columns

3. Display preferences with badges/tags for easy reading

4. Style the preference tags with colors

### File 4: `backend/setup.sql`
**Changes:**
1. Add new columns to the orders table:
   - `editing_style` VARCHAR(255)
   - `aspect_ratio` VARCHAR(50)
   - `duration_preference` VARCHAR(50)
   - `special_instructions` TEXT

---

## Dependent Files to be Edited:
1. `order.html` - Frontend form
2. `backend/save_order.php` - Backend handler
3. `backend/admin_orders.php` - Admin panel
4. `backend/setup.sql` - Database schema

---

## Followup Steps:
1. Run `backend/setup.php` to update database schema
2. Test the new form with various options
3. Verify admin panel displays new preferences correctly
4. Test multiple file uploads again

---

## Preview of Changes:

### New Form Section Preview:
```
📹 EDITING PREFERENCES

Music Style: [✓] Trending [ ] Cinematic [ ] Epic [ ] Chill [ ] Traditional
Transitions: [✓] Smooth [ ] Quick Cuts [ ] Zoom [ ] Glitch
Effects: [✓] Color Grading [✓] Text Overlays [ ] Slow Motion [ ] Logo
Duration: ( ) Short (<30s) (✓) Medium (30s-1min) ( ) Long (>1min)
Aspect Ratio: (✓) 9:16 (Vertical) ( ) 16:9 (Horizontal) ( ) 1:1 (Square)

Special Instructions:
[Text area for any additional requirements...]
```

### Admin Panel Preview:
```
| Preferences                                    |
| Music: 🔥 Trending, Epic                      |
| Effects: 🎨 Color Grading, 📝 Text Overlays   |
| Duration: Medium | Ratio: 9:16                |
| Special: Add my intro clip at start           |
```


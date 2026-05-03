# Fix Plan for ArgumentCountError in save_order.php

## Problem Identified
The PHP error "Fatal error: Uncaught ArgumentCountError: The number of elements in the type definiti" occurs when submitting the order form because:

1. **Database Missing Columns**: The `orders` table is missing referral-related columns:
   - `referral_code`
   - `referred_by`
   - `referral_discount`
   - `referral_used`

2. **bind_param Type Mismatch**: The SQL has 22 placeholders but the type string doesn't match properly.

## Fix Steps

### Step 1: Update Database Schema
Add missing referral columns to the orders table.

### Step 2: Fix save_order.php
- Update the SQL INSERT to include all columns correctly
- Fix the bind_param type string to match all 22 parameters

### Step 3: Test the fix
Verify the order submission works correctly.


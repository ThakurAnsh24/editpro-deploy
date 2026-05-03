# EditPro Admin Panel Enhancement Plan

## Features to Implement

### 1. Order Actions
- Edit order details (name, phone, price, delivery date, service type)
- Add internal notes visible only to admin
- Manual urgent flag on orders
- Resend confirmation to customer

### 2. Customer Management
- Customer order history (show all past orders by phone)
- Quick WhatsApp/call buttons
- Customer notes (special requirements, preferences)
- Customer blacklist option

### 3. Editor Features
- Assign orders to specific editors
- Track editor workload (order count)
- Editor performance stats (completed on time, ratings)

### 4. Financial Features
- Payment status tracking (Pending, Paid, Refunded)
- Revenue analytics (daily, weekly, monthly)
- Pending payments list
- Revenue by service type chart

### 6. Export/Reports
- Export orders to CSV
- Monthly revenue report
- Order summary PDF download

### 7. Notifications
- Email alerts for new orders
- Browser notification support

---

## Database Changes Needed:
- `internal_notes` - TEXT (admin notes)
- `is_urgent` - TINYINT (manual urgent flag)
- `customer_notes` - TEXT (customer preferences)
- `payment_status` - VARCHAR(20) DEFAULT 'Pending'
- `paid_amount` - DECIMAL(10,2)
- `assigned_to` - VARCHAR(100) (editor name)
- `customer_blacklist` - TINYINT

---

## Files to Update:
1. `backend/setup.sql` - Add new columns
2. `backend/setup.php` - Update table creation
3. `backend/save_order.php` - Handle new fields
4. `backend/admin_orders.php` - Complete rewrite with all features


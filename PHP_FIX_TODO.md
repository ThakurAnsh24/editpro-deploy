# PHP Fix Plan - Get EditPro Running

## Problems Identified:
1. MySQL is installed but NOT running
2. PHP config uses wrong XAMPP socket path (doesn't exist on this system)
3. Need to update config files for Homebrew MySQL

## Steps to Fix:
- [ ] 1. Start MySQL service
- [ ] 2. Update backend/config.php for Homebrew MySQL
- [ ] 3. Update backend/test.php for Homebrew MySQL
- [ ] 4. Create the database and tables
- [ ] 5. Test PHP server

## Commands to run:
```bash
brew services start mysql
mysql -u root -e "CREATE DATABASE IF NOT EXISTS editpro"
php -S localhost:8000
```


# Project Setup Documentation

## Cloning the Repository
```bash
git clone https://github.com/Chief-Strategist-J/todo-app.git
```
Clones the repository to the local machine.

## Verifying Clone
```bash
ls
```
Lists the directory contents to confirm the repository has been cloned.

## Navigating to the Project Directory
```bash
cd todo-app/
```
Changes the working directory to the cloned project.

## Clearing the Terminal (Optional)
```bash
clear
```
Clears the terminal for better visibility.

## Installing Dependencies
```bash
composer install
```
Installs project dependencies using Composer.

## Installing Composer (If Not Installed)
```bash
sudo apt install composer
```
Installs Composer on the system if it is missing.

## Updating and Installing Required PHP Extensions
```bash
sudo apt update && sudo apt install -y php8.3-cli php8.3-common php8.3-xml \
php8.3-mbstring php8.3-curl php8.3-zip php8.3-grpc php8.3-dom && \
composer update --ignore-platform-req=ext-grpc --ignore-platform-req=ext-dom \
--ignore-platform-req=ext-xml && composer install
```
Updates system packages and installs PHP 8.3 along with required extensions.

## Installing PHP Extensions via PECL (If Needed)
```bash
sudo apt update && sudo apt install -y php8.3-cli php8.3-common php8.3-xml \
php8.3-mbstring php8.3-curl php8.3-zip php8.3-dev php-pear && \
sudo pecl install grpc && \
echo "extension=grpc.so" | sudo tee -a /etc/php/8.3/cli/php.ini && \
composer update --ignore-platform-req=ext-grpc --ignore-platform-req=ext-dom \
--ignore-platform-req=ext-xml && composer install
```
Installs additional PHP extensions including `grpc` via PECL and updates Composer dependencies.
---------------
# MySQL Database and User Setup for Laravel

## 1️⃣ SQL Script to Create Database and User

Below is the SQL script to set up a MySQL database and user with the given credentials:

```sql
-- Create the database
CREATE DATABASE laravel;

-- Create the user and set the password
CREATE USER 'root'@'127.0.0.1' IDENTIFIED BY 'Scaibu@123';

-- Grant all privileges to the user on the database
GRANT ALL PRIVILEGES ON laravel.* TO 'root'@'127.0.0.1';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;
```

## 2️⃣ Steps to Execute the SQL Script

### **Step 1: Log into MySQL**
Open a terminal and run:
```bash
sudo mysql -u root
```

### **Step 2: Execute the SQL Script**
Copy and paste the SQL script above into the MySQL prompt and press **Enter**.

### **Step 3: Exit MySQL**
After executing the script, type the following command to exit MySQL:
```sql
EXIT;
```

## 3️⃣ Laravel Database Configuration
Ensure your Laravel `.env` file is configured as follows:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=Scaibu@123
```

## 4️⃣ Verify the Connection
Run the following command to test the database connection from Laravel:
```bash
php artisan migrate
```
If everything is set up correctly, the migrations should run successfully.

Now your Laravel application is connected to MySQL and ready to use! 🚀


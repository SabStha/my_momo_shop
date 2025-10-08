-- FULL CLEANUP: Truncate All Business Data Tables
-- Created: 2025-10-07
-- Backup: database.sqlite.backup-20251007-154710
-- EXECUTE AT YOUR OWN RISK

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- BUSINESS TRANSACTION DATA
-- ============================================

TRUNCATE TABLE orders;
TRUNCATE TABLE order_items;
TRUNCATE TABLE payments;
TRUNCATE TABLE product_ratings;

-- ============================================
-- FINANCIAL & WALLET DATA
-- ============================================

TRUNCATE TABLE wallets;
TRUNCATE TABLE wallet_transactions;
TRUNCATE TABLE ama_credits;
TRUNCATE TABLE ama_credit_transactions;
TRUNCATE TABLE cashouts;
TRUNCATE TABLE payouts;
TRUNCATE TABLE payout_requests;

-- ============================================
-- USER PROGRESS & GAMIFICATION
-- ============================================

TRUNCATE TABLE user_badges;
TRUNCATE TABLE badge_progress;
TRUNCATE TABLE user_task_completions;
TRUNCATE TABLE user_reward_redemptions;

-- ============================================
-- MARKETING & CAMPAIGNS
-- ============================================

TRUNCATE TABLE campaigns;
TRUNCATE TABLE campaign_triggers;
TRUNCATE TABLE coupons;
TRUNCATE TABLE coupon_usages;
TRUNCATE TABLE user_coupons;
TRUNCATE TABLE offers;
TRUNCATE TABLE offer_claims;
TRUNCATE TABLE referrals;
TRUNCATE TABLE customer_segments;

-- ============================================
-- CREATOR PROGRAM
-- ============================================

TRUNCATE TABLE rewards;
TRUNCATE TABLE creator_earnings;
TRUNCATE TABLE creator_rewards;

-- ============================================
-- INVENTORY & SUPPLY CHAIN
-- ============================================

TRUNCATE TABLE inventories;
TRUNCATE TABLE branch_inventory;
TRUNCATE TABLE inventory_transactions;
TRUNCATE TABLE stock_items;
TRUNCATE TABLE supply_orders;
TRUNCATE TABLE supply_order_items;
TRUNCATE TABLE weekly_stock_checks;
TRUNCATE TABLE monthly_stock_checks;
-- TRUNCATE TABLE daily_stock_checks;  -- May not exist

-- ============================================
-- POS OPERATIONS
-- ============================================

TRUNCATE TABLE cash_drawers;
TRUNCATE TABLE cash_drawer_sessions;
TRUNCATE TABLE cash_drawer_logs;
TRUNCATE TABLE cash_drawer_adjustments;
-- TRUNCATE TABLE cash_drawer_alerts;  -- Keep alert templates

-- ============================================
-- EMPLOYEE MANAGEMENT
-- ============================================

TRUNCATE TABLE employees;
TRUNCATE TABLE employee_schedules;
TRUNCATE TABLE time_logs;
TRUNCATE TABLE time_entries;

-- ============================================
-- ANALYTICS & LOGS
-- ============================================

TRUNCATE TABLE activity_log;
TRUNCATE TABLE pos_access_logs;
TRUNCATE TABLE churn_predictions;
TRUNCATE TABLE forecast_feedback;
TRUNCATE TABLE customer_feedback;
TRUNCATE TABLE investment_page_visits;

-- ============================================
-- INVESTOR DATA
-- ============================================

TRUNCATE TABLE investors;
TRUNCATE TABLE investor_investments;
TRUNCATE TABLE investor_payouts;
TRUNCATE TABLE investor_reports;

-- ============================================
-- PRODUCT DATA (Will Re-Seed)
-- ============================================

TRUNCATE TABLE products;
-- TRUNCATE TABLE categories;  -- KEEP - may have manual configuration

-- ============================================
-- OTHER BUSINESS DATA
-- ============================================

TRUNCATE TABLE merchandises;
TRUNCATE TABLE bulk_packages;
-- TRUNCATE TABLE combos;  -- Legacy table, may not be used
-- TRUNCATE TABLE drinks;  -- Legacy table, may not be used
TRUNCATE TABLE user_themes;
-- TRUNCATE TABLE customers;  -- May duplicate users table

-- ============================================
-- CACHE & SESSIONS (Always Safe)
-- ============================================

TRUNCATE TABLE cache;
-- TRUNCATE TABLE cache_locks;  -- May not exist
TRUNCATE TABLE sessions;
-- TRUNCATE TABLE web_sessions;  -- May not exist
TRUNCATE TABLE jobs;
TRUNCATE TABLE failed_jobs;
-- TRUNCATE TABLE job_batches;  -- May not exist

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check remaining data
SELECT table_name, table_rows 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
  AND table_rows > 0 
ORDER BY table_rows DESC 
LIMIT 30;

-- Verify critical tables still have structure
SHOW TABLES LIKE 'users';
SHOW TABLES LIKE 'roles';
SHOW TABLES LIKE 'permissions';

-- Check user count
SELECT COUNT(*) as user_count FROM users;

-- COMMIT ONLY IF VERIFICATION LOOKS GOOD
-- COMMIT;

-- OR ROLLBACK IF SOMETHING IS WRONG
-- ROLLBACK;


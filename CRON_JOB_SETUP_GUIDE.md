# 🕐 Cron Job Setup Guide for Laravel Scheduler

This guide will help you set up automated tasks for your Amako Momo Shop application, including AI-powered notifications, campaign triggers, and more.

---

## 📋 **What Automated Tasks Are Running?**

Your application has the following scheduled tasks:

1. **🤖 AI Daily Offers** - Daily at 10:00 AM
   - Generates personalized offers using OpenAI
   - Sends push notifications to mobile users
   
2. **📢 Campaign Triggers** - Every 5 minutes
   - Processes automated marketing campaigns
   - Sends targeted notifications based on user behavior

3. **⚠️ Churn Risk Detection** - Daily at 9:00 AM
   - Identifies users at risk of churning
   - Sends re-engagement notifications

4. **🧹 Cleanup Declined Orders** - Monthly on the 1st at 2:00 AM
   - Removes old declined orders from database

5. **📰 Branch Updates** - Daily at 10:00 PM
   - Auto-generates daily branch update content

6. **📊 Impact Stats** - Monthly on the 1st at 1:00 AM
   - Calculates monthly impact statistics

---

## 🚀 **Setup Instructions**

### **Step 1: Add Cron Job to Server**

You need to add **ONE** cron job that will run Laravel's scheduler every minute. Laravel will then decide which tasks to run based on their schedule.

#### **For Ubuntu/Linux (Production Server):**

1. **Open crontab editor:**
   ```bash
   crontab -e
   ```

2. **Add this single line at the end:**
   ```bash
   * * * * * cd /var/www/amako-momo\(p\)/my_momo_shop && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Save and exit** (Press `Ctrl+X`, then `Y`, then `Enter` in nano)

4. **Verify cron job is added:**
   ```bash
   crontab -l
   ```

---

### **Step 2: Test the Scheduler**

Before waiting for the cron to run automatically, test each command manually:

#### **Test AI Offers (Most Important):**
```bash
php artisan offers:send-daily-ai
```

**Expected Output:**
```
🤖 Starting Daily AI Offer Generation...
📊 Analyzing business data for branch 1...
✅ Successfully generated 3 AI offers
📱 Sending notifications to 25 mobile users...
✅ Sent 25 notifications successfully
🎉 Daily AI Offer Generation Complete!
```

#### **Test Campaign Triggers:**
```bash
php artisan campaigns:process-triggers
```

#### **Test Churn Detection:**
```bash
php artisan churn:check
```

#### **Test All Scheduled Tasks:**
```bash
php artisan schedule:list
```

This shows all scheduled tasks and when they'll run next.

---

### **Step 3: Monitor the Scheduler**

#### **Check if scheduler is running:**
```bash
php artisan schedule:work
```

This will run the scheduler in the foreground so you can see what happens.

#### **Check scheduler logs:**
```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
```
[2025-10-18 10:00:00] production.INFO: Running scheduled command: Artisan::call('offers:send-daily-ai')
```

---

## 🔍 **Troubleshooting**

### **Problem: Cron job not running**

1. **Check if cron service is running:**
   ```bash
   sudo systemctl status cron
   ```

2. **If stopped, start it:**
   ```bash
   sudo systemctl start cron
   sudo systemctl enable cron
   ```

3. **Check cron logs:**
   ```bash
   grep CRON /var/log/syslog
   ```

### **Problem: Commands run but fail**

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Run command manually with verbose output:**
   ```bash
   php artisan offers:send-daily-ai -v
   ```

3. **Check if `.env` has correct OpenAI API key:**
   ```bash
   grep OPENAI_API_KEY .env
   ```

### **Problem: No notifications sent**

1. **Check if OpenAI API key is set:**
   ```bash
   php artisan tinker
   >>> config('services.openai.api_key')
   ```

2. **Check if users have push tokens:**
   ```bash
   php artisan tinker
   >>> \App\Models\User::whereNotNull('expo_push_token')->count()
   ```

3. **Test notification service:**
   ```bash
   php artisan offers:send-daily-ai --branch=1
   ```

---

## ⏰ **Schedule Summary**

| Time | Task | Description |
|------|------|-------------|
| **01:00 AM** | Impact Stats | Calculate monthly impact (1st of month) |
| **02:00 AM** | Cleanup | Remove old declined orders (1st of month) |
| **09:00 AM** | Churn Check | Identify at-risk users |
| **10:00 AM** | **AI Offers** | **Send personalized offers (MAIN FEATURE)** |
| **10:00 PM** | Branch Updates | Generate daily content |
| **Every 5 min** | Campaign Triggers | Process automated campaigns |

---

## 🧪 **Testing Commands**

### **Test AI Offer Generation:**
```bash
# Generate offers for branch 1 (main branch)
php artisan offers:send-daily-ai --branch=1

# Generate AI offers without sending notifications (testing)
php artisan offers:generate-ai --branch=1
```

### **Test Campaign System:**
```bash
# Process all pending campaign triggers
php artisan campaigns:process-triggers

# Test specific campaign
php artisan campaigns:test --campaign=1
```

### **Manual Badge Processing:**
```bash
# Process badges for specific user
php artisan badges:process 1

# Process badges for all users
php artisan badges:process
```

---

## 📱 **Mobile Push Notifications Setup**

For AI offers and campaigns to send push notifications, users must:

1. **Allow notifications** when prompted in the mobile app
2. **Have a valid Expo push token** stored in database

### **Check push token count:**
```bash
php artisan tinker
>>> \App\Models\User::whereNotNull('expo_push_token')->count()
```

### **Test push notification:**
```bash
php artisan tinker
>>> $user = \App\Models\User::first();
>>> $service = app(\App\Services\MobileNotificationService::class);
>>> $service->sendToUser($user, 'Test', 'This is a test notification', ['type' => 'test']);
```

---

## ✅ **Verification Checklist**

After setup, verify everything works:

- [ ] Cron job added to crontab (`crontab -l`)
- [ ] Cron service is running (`systemctl status cron`)
- [ ] Scheduler lists all tasks (`php artisan schedule:list`)
- [ ] AI offers command works manually (`php artisan offers:send-daily-ai`)
- [ ] OpenAI API key is configured in `.env`
- [ ] Users have push tokens in database
- [ ] Notifications appear in mobile app
- [ ] Laravel logs show scheduled commands running (`tail -f storage/logs/laravel.log`)

---

## 🎯 **Quick Setup (Copy-Paste)**

Run these commands on your production server to set up everything:

```bash
# 1. Add cron job
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/amako-momo\(p\)/my_momo_shop && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# 2. Verify cron is added
crontab -l

# 3. Test AI offers immediately
php artisan offers:send-daily-ai

# 4. Check what's scheduled
php artisan schedule:list

# 5. Monitor logs in real-time
tail -f storage/logs/laravel.log
```

---

## 📞 **Support**

If you encounter issues:

1. Check the logs: `storage/logs/laravel.log`
2. Run commands manually with `-v` flag for verbose output
3. Verify cron is running: `systemctl status cron`
4. Check OpenAI API key: `grep OPENAI_API_KEY .env`

---

## 🎉 **Success!**

Once set up, your application will automatically:
- ✅ Send personalized AI-powered offers daily at 10 AM
- ✅ Process campaign triggers every 5 minutes
- ✅ Detect and re-engage churning users daily at 9 AM
- ✅ Keep your database clean with monthly cleanup
- ✅ Generate fresh content daily

**No manual intervention needed!** 🚀


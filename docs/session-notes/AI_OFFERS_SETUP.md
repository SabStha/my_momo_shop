# 🤖 AI Offers Not Showing - Here's Why and How to Fix

## **Problem:**
Your AI offer system is fully built but **not running automatically**. It's scheduled to run daily at 10:00 AM but requires Laravel scheduler to be active on your server.

---

## **Quick Fix - Generate AI Offers Right Now:**

### **SSH into your server and run:**
```bash
cd /var/www/amako-momo\(p\)/my_momo_shop
php artisan offers:send-daily-ai
```

This will:
- 🤖 Use OpenAI to analyze your sales data
- 🎁 Create personalized offers
- 📱 Send notifications to ALL mobile users
- ✅ Show in the mobile app notifications

---

## **Permanent Fix - Auto-run Daily at 10 AM:**

### **1. Check if Laravel scheduler is running:**
```bash
crontab -l
```

### **2. If you DON'T see `schedule:run`, add it:**
```bash
crontab -e
```

Add this line:
```cron
* * * * * cd /var/www/amako-momo\(p\)/my_momo_shop && php artisan schedule:run >> /dev/null 2>&1
```

Save and exit (`:wq` in vim or `Ctrl+X` then `Y` in nano)

### **3. Verify it's scheduled:**
```bash
php artisan schedule:list
```

You should see:
```
0 10 * * *   offers:send-daily-ai ........... Next Due: Tomorrow at 10:00 AM
```

---

## **What AI Offers Do:**

The system uses **OpenAI GPT-4** to:
- 📊 Analyze your order history
- 🍜 Identify popular products
- 👥 Segment customers (new, VIP, inactive)
- 💡 Generate creative offer titles & descriptions
- 🎯 Create personalized discount codes
- 📱 Push notifications to mobile users

**Example Offers Generated:**
- "🔥 VIP Exclusive: 30% OFF on Chicken Momo!"
- "🎁 Welcome Back! 20% OFF Your Favorite Dish"
- "⚡ Flash Sale: Buy 2 Combos, Get 1 FREE!"

---

## **Manually Trigger from Admin Panel (Alternative):**

The API endpoint exists but needs admin authentication:
```bash
POST https://amakomomo.com/api/admin/mobile-notifications/generate-ai-offers
Authorization: Bearer YOUR_ADMIN_TOKEN
```

---

## **Verify OpenAI API Key:**

Check your `.env` file:
```bash
grep OPENAI_API_KEY /var/www/amako-momo\(p\)/my_momo_shop/.env
```

Should show:
```
OPENAI_API_KEY=YOUR_OPENAI_API_KEY_HERE
```

If missing, add your OpenAI key and run:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## **Test Now:**

Run this single command to generate offers immediately:
```bash
cd /var/www/amako-momo\(p\)/my_momo_shop && php artisan offers:send-daily-ai
```

Then check your mobile app notifications! 🎉


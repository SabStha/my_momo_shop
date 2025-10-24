# Switch to New Expo Account

**Old Account:** sabstha98@gmail.com (out of free builds)  
**New Account:** evanhuc404@gmail.com  
**Password:** sabin12345

---

## Step 1: Logout from Current Account

```bash
cd amako-shop
eas logout
```

**Expected output:**
```
✓ Logged out
```

---

## Step 2: Login to New Account

```bash
eas login
```

**When prompted:**
- **Email:** `evanhuc404@gmail.com`
- **Password:** `sabin12345`

**Expected output:**
```
✓ Logged in as evanhuc404
```

---

## Step 3: Verify Login

```bash
eas whoami
```

**Expected output:**
```
evanhuc404
```

---

## Step 4: Update Project Owner (Important!)

You need to link the project to your new account:

```bash
eas project:init
```

**When prompted:**
- Select "Create a new project"
- It will create a new project ID for evanhuc404

This will update `app.json` with the new project ID.

---

## Step 5: Build APK

Now build with your new account:

```bash
eas build --platform android --profile preview
```

**Monitor at:** https://expo.dev/accounts/evanhuc404/projects/

---

## Alternative: Quick Commands

Run these one by one:

```bash
cd amako-shop
eas logout
eas login
eas build --platform android --profile preview
```

When login prompts appear:
- Email: `evanhuc404@gmail.com`
- Password: `sabin12345`

---

## Important Notes

1. **Free Builds:** New account has 30 free Android builds/month
2. **Project ID:** Will be different from old account
3. **Build Time:** Still 10-20 minutes
4. **APK Link:** Will be at https://expo.dev/accounts/evanhuc404/

---

## Troubleshooting

### If "Project not found":
```bash
eas project:init
```

### If "Not logged in":
```bash
eas login --username evanhuc404@gmail.com
```

### If build fails with "Invalid credentials":
Re-login:
```bash
eas logout
eas login
```

---

**Ready to build!** 🚀



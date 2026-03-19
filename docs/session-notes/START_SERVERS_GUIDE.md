# How to Start Your Amako Shop Servers

## Quick Start (Easiest Way)

### Start Both Servers at Once
Just double-click:
```
start-both-servers.bat
```

This will open **two terminal windows**:
1. **Laravel API Server** on `http://192.168.2.145:8000`
2. **Expo App** (scan QR code to open on your phone)

---

## Manual Start (Individual Servers)

### Option 1: Start Laravel API Server Only

**Using Batch File:**
```bash
# Double-click or run:
start-laravel-server.bat
```

**Using PowerShell:**
```powershell
.\start-laravel-server.ps1
```

**Using Command Line:**
```bash
php -S 0.0.0.0:8000 -t public
```

### Option 2: Start Expo App Only

```bash
cd amako-shop
npm run start:tunnel
```

---

## Understanding the Servers

### Laravel API Server (Backend)
- **Port**: 8000
- **Purpose**: Handles authentication, database, API endpoints
- **Access**: `http://192.168.2.145:8000`
- **Must be running** for login, data loading, etc.

### Expo Development Server (Frontend)
- **Port**: 8081 (Metro bundler)
- **Purpose**: Serves the React Native app to your phone
- **Access**: Via QR code or tunnel URL
- **Connects to**: Laravel API server for data

---

## Troubleshooting

### "Network Error" when logging in
**Problem**: Laravel server is not running

**Solution**: 
1. Check if Laravel is running by visiting: `http://192.168.2.145:8000`
2. If not, start it: `php -S 0.0.0.0:8000 -t public`

### "Port 8000 already in use"
**Problem**: Another Laravel server is already running

**Solution**:
1. Find the running process: `netstat -ano | findstr :8000`
2. Kill it: `taskkill /PID [process_id] /F`
3. Or just use that existing server!

### Can't access from phone
**Problem**: Firewall blocking connections

**Solution**:
1. Allow PHP in Windows Firewall
2. Make sure phone and computer are on same WiFi network
3. Check IP address is correct: `ipconfig`

---

## Production Deployment

For production, DO NOT use `php -S`. Instead:
- Use Apache/Nginx with PHP-FPM
- See `PRODUCTION_DEPLOYMENT_GUIDE.md` for details

---

## Daily Development Workflow

### Morning Setup:
1. Open terminal in project root
2. Run: `start-both-servers.bat`
3. Wait for both servers to start
4. Scan QR code on your phone
5. Start coding! 🚀

### When Done:
1. Press `Ctrl+C` in both terminal windows
2. Or just close the terminal windows

---

## Pro Tips

### Keep Terminals Open
- Laravel and Expo servers need to keep running while you develop
- Don't close the terminal windows
- Minimize them if they're in the way

### Use Separate Terminals
- One for Laravel (backend)
- One for Expo (frontend)
- One for commands (git, composer, npm)

### Monitor Logs
- Laravel terminal shows API requests and errors
- Expo terminal shows app bundling and React errors
- Both are useful for debugging!

### Restart When Needed
Restart Laravel when:
- ❌ You change `.env` configuration
- ❌ You add new routes
- ❌ You modify middleware
- ❌ Strange errors appear

Restart Expo when:
- ❌ You install new npm packages
- ❌ You change `app.json` or `metro.config.js`
- ❌ Metro bundler gets stuck
- ✅ Usually auto-reloads on code changes

---

## Network Detection

Your app **automatically detects** your network IP! 🎉

When you switch between:
- 🏠 Home network (e.g., 192.168.2.145)
- 🏢 Office network (e.g., 192.168.1.100)

The app finds the correct IP automatically. Just make sure Laravel is running on that machine!

---

## Summary

**Both servers need to be running for the app to work:**

| Server | Command | Purpose |
|--------|---------|---------|
| Laravel API | `php -S 0.0.0.0:8000 -t public` | Backend (login, data, database) |
| Expo | `cd amako-shop && npm run start:tunnel` | Frontend (React Native app) |

**Easiest way**: Just double-click `start-both-servers.bat`! 🚀


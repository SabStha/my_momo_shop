# 🔧 Network Switching Guide

This guide helps you easily switch between different networks (home, school, etc.) without manually changing IP addresses everywhere.

## 🚀 Quick Switch Methods

### Method 1: Using Batch Files (Easiest)
Double-click these files in your project folder:
- `switch-to-home.bat` - Switch to home network
- `switch-to-school.bat` - Switch to school network

### Method 2: Using Command Line
```bash
# Switch to home network
node switch-network.js home

# Switch to school network  
node switch-network.js school
```

### Method 3: Manual Configuration
1. Open `src/config/network.ts`
2. Change `NETWORK_MODE` to:
   - `'tunnel'` for Expo tunnel (recommended for development)
   - `'home'` for home network
   - `'school'` for school network
3. Update the IP address in `NETWORK_CONFIGS` if needed
4. Save and reload your app

## 📍 Current Network Configuration

The app automatically uses the correct IP based on your `NETWORK_MODE` setting.

### Expo Tunnel (Recommended for Development)
- **IP**: `1rt7vr4-sabstha98-8081.exp.direct`
- **Use when**: Development with Expo tunnel

### Home Network
- **IP**: `192.168.56.1`
- **Use when**: Working at home

### School Network  
- **IP**: `192.168.0.19` (update this to your school's actual IP)
- **Use when**: Working at school

## 🔄 How It Works

1. **API Configuration**: Automatically uses the correct IP for API calls
2. **Image URLs**: All banner and product images use the correct IP
3. **One-Click Switching**: Change network mode and everything updates

## 🛠️ Adding New Networks

To add a new network (e.g., office):

1. Open `src/config/network.ts`
2. Add your network to `NETWORK_CONFIGS`:
```typescript
office: {
  ip: '10.0.0.1', // Your office IP
  name: 'Office Network',
  description: 'Your office WiFi network'
}
```
3. Add 'office' to the `NetworkMode` type
4. Update `switch-network.js` to include your new network

## 🐛 Troubleshooting

### App Still Using Old IP?
1. Make sure you saved the network config file
2. Reload your app completely (close and reopen)
3. Check that the batch file ran successfully

### Network Not Working?
1. Verify your Laravel server is running on the correct IP
2. Check that your device is on the same network
3. Test the IP in your browser: `http://YOUR_IP:8000/api/health`

### Still Having Issues?
1. Check the console logs for network errors
2. Verify the IP address in `src/config/network.ts`
3. Make sure your Laravel server is accessible from your device

## 📱 Testing

After switching networks:
1. Reload your app
2. Try logging in
3. Check if images load properly
4. Test API calls

## 🎯 Benefits

- ✅ No more manual IP changes
- ✅ Easy switching between networks
- ✅ All URLs update automatically
- ✅ No more network connection errors
- ✅ Works on any network with one click

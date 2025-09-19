# AmaKo Shop

A React Native + Expo food delivery application.

## 🚀 Getting Started

### Prerequisites
- Node.js 18+
- Expo CLI
- Android Studio (for Android development)
- Xcode (for iOS development, macOS only)

### Installation
```bash
npm install
```

## 📱 How to Run on Device

### Option 1: Tunnel Mode (Recommended)
**Best for Expo Go and works on any network:**
```bash
npm run start:tunnel
```
- Opens a QR code that works immediately in Expo Go
- Bypasses network configuration issues
- Works over any Wi-Fi or mobile data connection

### Option 2: LAN Mode (Advanced)
**Only if you need faster refresh rates:**
```bash
# First, find your Wi-Fi IP address
npm run find-ip  # Automated IP detection
# OR manually:
ipconfig  # Windows
ifconfig  # Mac/Linux

# Update the IP in package.json scripts, then:
npm run start:lan
```
- Replace `<REPLACE_WITH_WIFI_IP>` in package.json with your actual Wi-Fi IP
- Never use 192.168.56.x (VirtualBox Host-Only network)
- Disable VirtualBox Host-Only adapter if it interferes

### Option 3: USB Mode (Fallback)
**When Wi-Fi connection fails:**
```bash
npm run start:usb
```
- Requires USB debugging enabled
- Automatically sets up port forwarding
- Works without network configuration

### Troubleshooting
- **App keeps loading**: Use tunnel mode instead of LAN
- **VirtualBox issues**: Disable VirtualBox Host-Only adapter
- **Connection problems**: Check debug screen at `/debug` route
- **Version warnings**: Run `npm run doctor` to check dependencies

## 📱 Features

- User authentication
- Product browsing and search
- Shopping cart management
- Order placement and tracking
- Push notifications
- Loyalty program

## 🏗️ Architecture

- **Frontend**: React Native + Expo
- **Backend**: Laravel API
- **State Management**: Zustand
- **Navigation**: Expo Router
- **Styling**: Custom design system with tokens

## 📁 Project Structure

```
amako-shop/
├── app/                    # Expo Router screens
├── src/
│   ├── api/               # API client and hooks
│   ├── components/        # Reusable components
│   ├── config/            # Configuration files
│   ├── hooks/             # Custom hooks
│   ├── notifications/     # Push notification setup
│   ├── session/           # Authentication context
│   ├── state/             # Zustand stores
│   ├── types/             # TypeScript types
│   └── ui/                # Design system components
├── assets/                 # Static assets
└── scripts/                # Build and utility scripts
```

## 🔧 Configuration

### Environment Variables
Create a `.env` file in the root directory:
```env
EXPO_PUBLIC_API_URL=http://localhost:8000/api
```

### API Configuration
The app automatically detects the platform and uses appropriate API endpoints:
- **Android Emulator**: `http://10.0.2.2:8000/api`
- **iOS Simulator**: `http://localhost:8000/api`
- **Physical Device**: `http://[YOUR_LAN_IP]:8000/api`

## 📦 Build

### Development Build
```bash
npx eas build --platform android --profile development
```

### Production Build
```bash
npx eas build --platform android --profile production
```

## 🧪 Testing

### QA Testing Guide
See `QA_TESTING_GUIDE.md` for comprehensive testing procedures.

### Push Notifications
- **Expo Go**: Limited functionality (SDK 53+)
- **Development Build**: Full functionality
- **Test Button**: Available in Profile screen

## 📋 Play Store Preparation

### Required Assets
- App icon: 1024×1024
- Feature graphic: 1024×500
- Screenshots: 1080×1920

### Compliance Documents
- Privacy Policy: `PRIVACY_POLICY.md`
- Data Safety Form: `DATA_SAFETY_FORM.md`

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is proprietary software.

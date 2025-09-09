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

### Development
```bash
npx expo start
```

### Dev server host tips
PowerShell:
```powershell
$env:EXPO_DEV_SERVER_HOST="192.168.2.130"  # replace with your LAN IP
npx expo start -c --host lan
# or safer:
npx expo start -c --host tunnel
```

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

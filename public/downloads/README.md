# Downloads Folder

This folder hosts APK files for beta testing.

## How to Add Your Beta APK

1. **Build your APK** using one of these methods:
   - Run `amako-shop/build-beta.bat`
   - Or manually: `eas build --platform android --profile preview`

2. **Download the APK** from the EAS build link

3. **Copy it here:**
   ```bash
   # Name it: amako-shop-beta.apk
   # Full path: public/downloads/amako-shop-beta.apk
   ```

4. **Verify it's accessible:**
   ```
   http://localhost:8000/downloads/amako-shop-beta.apk
   ```

## Current Files

- Place your `amako-shop-beta.apk` file here
- File will be ~30-60 MB
- Format: Android Package (APK)

## Security

- **Do not** commit APK files to Git (too large)
- Add to `.gitignore` if needed
- Only share download link with trusted testers
- Use access codes on beta page for protection

## Testing

The beta page is available at:
- Local: `http://localhost:8000/beta`
- Production: `http://your-domain.com/beta`

## Access Codes

Default codes (change in `resources/views/beta-testing.blade.php`):
- AMAKO2025
- BETA2025
- MOMOTEST
- TESTAMAKO
- BETAUSER





#!/usr/bin/env node

import { writeFileSync, mkdirSync } from 'fs';
import { join } from 'path';

// Create playstore-assets directory if it doesn't exist
const assetsDir = join(process.cwd(), 'playstore-assets');
try {
  mkdirSync(assetsDir, { recursive: true });
} catch (error) {
  // Directory already exists
}

// Generate SVG content for app icon (1024x1024)
const appIconSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg width="1024" height="1024" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#4F46E5;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#7C3AED;stop-opacity:1" />
    </linearGradient>
  </defs>
  <rect width="1024" height="1024" fill="url(#bg)" rx="200"/>
  <text x="512" y="600" font-family="Arial, sans-serif" font-size="200" font-weight="bold" text-anchor="middle" fill="white">AK</text>
  <circle cx="512" cy="400" r="80" fill="white" opacity="0.2"/>
</svg>`;

// Generate SVG content for feature graphic (1024x500)
const featureGraphicSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg width="1024" height="500" viewBox="0 0 1024 500" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#4F46E5;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#7C3AED;stop-opacity:1" />
    </linearGradient>
  </defs>
  <rect width="1024" height="500" fill="url(#bg)"/>
  <text x="512" y="200" font-family="Arial, sans-serif" font-size="80" font-weight="bold" text-anchor="middle" fill="white">AmaKo Shop</text>
  <text x="512" y="280" font-family="Arial, sans-serif" font-size="40" text-anchor="middle" fill="white" opacity="0.9">Your Favorite Food & More</text>
  <circle cx="200" cy="150" r="60" fill="white" opacity="0.2"/>
  <circle cx="824" cy="350" r="40" fill="white" opacity="0.2"/>
</svg>`;

// Generate SVG content for phone screenshot (1080x1920)
const phoneScreenshotSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg width="1080" height="1920" viewBox="0 0 1080 1920" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#F8FAFC;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#E2E8F0;stop-opacity:1" />
    </linearGradient>
  </defs>
  <rect width="1080" height="1920" fill="url(#bg)"/>
  
  <!-- Header -->
  <rect x="0" y="0" width="1080" height="200" fill="#4F46E5"/>
  <text x="540" y="120" font-family="Arial, sans-serif" font-size="48" font-weight="bold" text-anchor="middle" fill="white">AmaKo Shop</text>
  
  <!-- Search Bar -->
  <rect x="40" y="240" width="1000" height="80" fill="white" rx="20" stroke="#E2E8F0" stroke-width="2"/>
  <text x="80" y="290" font-family="Arial, sans-serif" font-size="32" fill="#64748B">Search for food...</text>
  
  <!-- Category Cards -->
  <rect x="40" y="360" width="240" height="160" fill="white" rx="20" stroke="#E2E8F0" stroke-width="1"/>
  <text x="160" y="420" font-family="Arial, sans-serif" font-size="24" font-weight="bold" text-anchor="middle" fill="#1E293B">Food</text>
  
  <rect x="300" y="360" width="240" height="160" fill="white" rx="20" stroke="#E2E8F0" stroke-width="1"/>
  <text x="420" y="420" font-family="Arial, sans-serif" font-size="24" font-weight="bold" text-anchor="middle" fill="#1E293B">Drinks</text>
  
  <rect x="560" y="360" width="240" height="160" fill="white" rx="20" stroke="#E2E8F0" stroke-width="1"/>
  <text x="680" y="420" font-family="Arial, sans-serif" font-size="24" font-weight="bold" text-anchor="middle" fill="#1E293B">Desserts</text>
  
  <rect x="800" y="360" width="240" height="160" fill="white" rx="20" stroke="#E2E8F0" stroke-width="1"/>
  <text x="920" y="420" font-family="Arial, sans-serif" font-size="24" font-weight="bold" text-anchor="middle" fill="#1E293B">More</text>
  
  <!-- Product Grid -->
  <rect x="40" y="560" width="320" height="400" fill="white" rx="20" stroke="#E2E8F0" stroke-width="1"/>
  <rect x="60" y="580" width="280" height="200" fill="#F1F5F9" rx="10"/>
  <text x="200" y="820" font-family="Arial, sans-serif" font-size="20" font-weight="bold" text-anchor="middle" fill="#1E293B">Burger Combo</text>
  <text x="200" y="850" font-family="Arial, sans-serif" font-size="18" text-anchor="middle" fill="#64748B">Rs. 450</text>
  
  <rect x="380" y="560" width="320" height="400" fill="white" rx="20" stroke="#E2E8F0" stroke-width="1"/>
  <rect x="400" y="580" width="280" height="200" fill="#F1F5F9" rx="10"/>
  <text x="540" y="820" font-family="Arial, sans-serif" font-size="20" font-weight="bold" text-anchor="middle" fill="#1E293B">Pizza</text>
  <text x="540" y="850" font-family="Arial, sans-serif" font-size="18" text-anchor="middle" fill="#64748B">Rs. 650</text>
  
  <rect x="720" y="560" width="320" height="400" fill="white" rx="20" stroke="#E2E8F0" stroke-width="1"/>
  <rect x="740" y="580" width="280" height="200" fill="#F1F5F9" rx="10"/>
  <text x="880" y="820" font-family="Arial, sans-serif" font-size="20" font-weight="bold" text-anchor="middle" fill="#1E293B">Coffee</text>
  <text x="880" y="850" font-family="Arial, sans-serif" font-size="18" text-anchor="middle" fill="#64748B">Rs. 180</text>
  
  <!-- Bottom Navigation -->
  <rect x="0" y="1800" width="1080" height="120" fill="white" stroke="#E2E8F0" stroke-width="1"/>
  <text x="216" y="1860" font-family="Arial, sans-serif" font-size="20" text-anchor="middle" fill="#4F46E5">Home</text>
  <text x="432" y="1860" font-family="Arial, sans-serif" font-size="20" text-anchor="middle" fill="#64748B">Menu</text>
  <text x="648" y="1860" font-family="Arial, sans-serif" font-size="20" text-anchor="middle" fill="#64748B">Cart</text>
  <text x="864" y="1860" font-family="Arial, sans-serif" font-size="20" text-anchor="middle" fill="#64748B">Profile</text>
</svg>`;

// Write SVG files
writeFileSync(join(assetsDir, 'app-icon-1024x1024.svg'), appIconSvg);
writeFileSync(join(assetsDir, 'feature-graphic-1024x500.svg'), featureGraphicSvg);
writeFileSync(join(assetsDir, 'phone-screenshot-1080x1920.svg'), phoneScreenshotSvg);

// Create README with instructions
const readmeContent = `# Play Store Assets

This directory contains the required assets for publishing AmaKo Shop to the Google Play Store.

## Required Assets

### 1. App Icon (1024×1024)
- File: app-icon-1024x1024.svg
- Format: PNG (convert from SVG)
- Size: 1024×1024 pixels
- Background: Must be opaque

### 2. Feature Graphic (1024×500)
- File: feature-graphic-1024x500.svg
- Format: PNG (convert from SVG)
- Size: 1024×500 pixels
- No text overlay (Google adds app name automatically)

### 3. Screenshots (1080×1920)
- File: phone-screenshot-1080x1920.svg (template)
- Format: PNG (convert from SVG or use real screenshots)
- Size: 1080×1920 pixels (minimum)
- Show key app features

## Conversion Instructions

1. Use online SVG to PNG converters or design tools
2. Ensure all text is readable
3. Test on different screen densities
4. Keep file sizes under 8MB

## Play Store Requirements

- App icon: 1024×1024 PNG
- Feature graphic: 1024×500 PNG  
- Screenshots: 1080×1920 PNG (minimum)
- All assets must be high quality
- No placeholder text or images
- Follow Material Design guidelines

## Notes

- These SVG files are templates - customize with your actual app design
- Replace placeholder content with real app screenshots
- Ensure branding consistency across all assets
`;

writeFileSync(join(assetsDir, 'README.md'), readmeContent);

console.log('✅ Play Store assets generated successfully!');
console.log('📁 Check the "playstore-assets" directory');
console.log('🔄 Convert SVG files to PNG for Play Store submission');
console.log('📱 Customize the designs to match your app branding');

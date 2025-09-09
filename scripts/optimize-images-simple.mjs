import fs from 'fs';
import path from 'path';

const SRC_DIRS = [
    'public/images',
    'public/storage/products',
    'public/storage/banners',
    'public/storage/logo'
];

const exts = ['.jpg', '.jpeg', '.png'];

function findImages(dirPath, images = []) {
    if (!fs.existsSync(dirPath)) {
        return images;
    }

    const items = fs.readdirSync(dirPath);
    
    for (const item of items) {
        const fullPath = path.join(dirPath, item);
        const stat = fs.statSync(fullPath);
        
        if (stat.isDirectory()) {
            findImages(fullPath, images);
        } else if (stat.isFile()) {
            const ext = path.extname(fullPath).toLowerCase();
            if (exts.includes(ext)) {
                images.push(fullPath);
            }
        }
    }
    
    return images;
}

async function run() {
    console.log('🚀 Image Optimization Analysis...');
    console.log('📋 This script analyzes images for optimization opportunities.\n');
    
    let totalImages = 0;
    let totalSize = 0;
    let largeImages = [];
    
    for (const srcDir of SRC_DIRS) {
        console.log(`📁 Scanning directory: ${srcDir}`);
        const images = findImages(srcDir);
        
        for (const imagePath of images) {
            const stats = fs.statSync(imagePath);
            const sizeKB = Math.round(stats.size / 1024);
            totalImages++;
            totalSize += sizeKB;
            
            if (sizeKB > 100) { // Images larger than 100KB
                largeImages.push({ path: imagePath, size: sizeKB });
            }
        }
    }
    
    console.log(`\n📊 Analysis Results:`);
    console.log(`   Total images found: ${totalImages}`);
    console.log(`   Total size: ${Math.round(totalSize / 1024)}MB`);
    console.log(`   Large images (>100KB): ${largeImages.length}`);
    
    if (largeImages.length > 0) {
        console.log(`\n⚠️  Large images that need optimization:`);
        largeImages.forEach(img => {
            console.log(`   ${img.path} (${img.size}KB)`);
        });
    }
    
    console.log(`\n💡 Optimization Recommendations:`);
    console.log(`   1. Install sharp: npm install sharp --save-dev`);
    console.log(`   2. Run: node scripts/optimize-images.mjs`);
    console.log(`   3. Use WebP/AVIF formats for better compression`);
    console.log(`   4. Resize images to max 1600px width`);
    console.log(`   5. Use the <x-optimized-image> component in Blade templates`);
    
    console.log(`\n✅ Analysis complete!`);
}

run().catch(console.error); 
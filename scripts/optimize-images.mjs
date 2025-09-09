import fs from 'fs';
import path from 'path';
import sharp from 'sharp';

const SRC_DIRS = [
    'public/images',
    'public/storage/products',
    'public/storage/banners',
    'public/storage/logo'
];

const exts = ['.jpg', '.jpeg', '.png'];

async function optimizeImage(filePath) {
    try {
        const ext = path.extname(filePath).toLowerCase();
        if (!exts.includes(ext)) return;

        const dir = path.dirname(filePath);
        const baseName = path.parse(filePath).name;
        const basePath = path.join(dir, baseName);

        console.log(`Processing: ${filePath}`);

        // Create WebP version
        await sharp(filePath)
            .resize({ width: 1600, withoutEnlargement: true })
            .webp({ quality: 78 })
            .toFile(basePath + '.webp');

        // Create AVIF version
        await sharp(filePath)
            .resize({ width: 1600, withoutEnlargement: true })
            .avif({ quality: 45 })
            .toFile(basePath + '.avif');

        console.log(`✓ Created WebP and AVIF for: ${baseName}`);
    } catch (error) {
        console.error(`✗ Error processing ${filePath}:`, error.message);
    }
}

async function processDirectory(dirPath) {
    if (!fs.existsSync(dirPath)) {
        console.log(`Directory not found: ${dirPath}`);
        return;
    }

    const items = fs.readdirSync(dirPath);
    
    for (const item of items) {
        const fullPath = path.join(dirPath, item);
        const stat = fs.statSync(fullPath);
        
        if (stat.isDirectory()) {
            await processDirectory(fullPath);
        } else if (stat.isFile()) {
            await optimizeImage(fullPath);
        }
    }
}

async function run() {
    console.log('🚀 Starting image optimization...');
    
    for (const srcDir of SRC_DIRS) {
        console.log(`\n📁 Processing directory: ${srcDir}`);
        await processDirectory(srcDir);
    }
    
    console.log('\n✅ Image optimization complete!');
}

run().catch(console.error); 
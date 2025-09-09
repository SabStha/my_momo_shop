import fs from 'fs';
import path from 'path';

const sourcePath = 'public/build/.vite/manifest.json';
const targetPath = 'public/build/manifest.json';

try {
    if (fs.existsSync(sourcePath)) {
        fs.copyFileSync(sourcePath, targetPath);
        console.log('✅ Manifest copied successfully');
    } else {
        console.log('⚠️  Source manifest not found');
    }
} catch (error) {
    console.error('❌ Error copying manifest:', error.message);
} 
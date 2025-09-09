#!/usr/bin/env node

import { readFileSync, writeFileSync } from 'fs';
import { join } from 'path';

console.log('🔧 Fixing Circular Import Dependencies...\n');

// Components that need to be updated
const componentsToFix = [
  'app/order/[id].tsx',
  'app/item/[id].tsx',
  'src/notifications/toast.tsx',
  'app/checkout.tsx',
  'src/notifications/NotificationExample.tsx',
  'app/(tabs)/orders.tsx',
  'app/(tabs)/index.tsx',
  'src/components/CartItem.tsx',
  'src/components/CategoryFilter.tsx',
  'src/components/ItemCard.tsx',
  'src/components/ErrorState.tsx',
  'src/components/OffersBanner.tsx',
  'src/components/SkeletonCard.tsx',
  'src/components/SearchInput.tsx',
  'src/components/Screen.tsx'
];

let fixedCount = 0;

componentsToFix.forEach(componentPath => {
  try {
    const fullPath = join(process.cwd(), componentPath);
    const content = readFileSync(fullPath, 'utf8');
    
    // Check if this component has the circular import issue
    if (content.includes('from \'../ui/tokens\'') || content.includes('from \'../../src/ui/tokens\'')) {
      console.log(`🔧 Fixing: ${componentPath}`);
      
      // Replace the problematic import
      let newContent = content;
      
      // Fix relative path imports
      newContent = newContent.replace(
        /from ['"]\.\.\/ui\/tokens['"]/g,
        'from \'../ui\''
      );
      
      // Fix absolute path imports
      newContent = newContent.replace(
        /from ['"]\.\.\/\.\.\/src\/ui\/tokens['"]/g,
        'from \'../../src/ui\''
      );
      
      // Fix other path variations
      newContent = newContent.replace(
        /from ['"]\.\.\/\.\.\/\.\.\/src\/ui\/tokens['"]/g,
        'from \'../../../src/ui\''
      );
      
      // Write the updated content
      writeFileSync(fullPath, newContent);
      console.log(`✅ Fixed: ${componentPath}`);
      fixedCount++;
    } else {
      console.log(`✅ Already correct: ${componentPath}`);
    }
  } catch (error) {
    console.log(`⚠️  Could not process ${componentPath}: ${error.message}`);
  }
});

console.log(`\n🎯 Circular Import Fix Complete!`);
console.log(`Fixed ${fixedCount} components`);
console.log(`\n📱 Next Steps:`);
console.log(`1. Restart your development server:`);
console.log(`   npx expo start --clear`);
console.log(`\n2. Test if the radius errors are resolved`);
console.log(`\n3. If issues persist, we may need to investigate further`);

console.log(`\n🔍 What was fixed:`);
console.log(`- Removed direct imports from '../ui/tokens'`);
console.log(`- Updated components to import from '../ui' (which re-exports tokens)`);
console.log(`- This should resolve the circular dependency causing the radius error`);

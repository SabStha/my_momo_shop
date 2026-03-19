# 🖼️ Product Images Setup Guide

This guide explains how to set up product images in your momo shop application.

## 🚀 Quick Start

### 1. Run the Setup Script

First, run the setup script to create products and assign images:

```bash
cd /c/Users/user/my_momo_shop
php scripts/setup-product-images.php
```

This script will:
- ✅ Create sample momo products if none exist
- ✅ Assign appropriate images from your project's image collection
- ✅ Update the database with image paths

### 2. What the Script Does

The script automatically:
- **Creates 8 sample momo products** with realistic names and prices
- **Assigns images** from `public/storage/products/foods/` folder
- **Maps products to images** based on keywords (chicken, veg, paneer, etc.)
- **Sets up a default branch** if none exists

### 3. Sample Products Created

| Product Name | Category | Tag | Image Assigned |
|--------------|----------|-----|----------------|
| Steamed Chicken Momos | Chicken | chicken | steamed-chicken-momos.jpg |
| Spicy Chicken Momos | Chicken | chicken | spicy-chicken-momos.jpg |
| Vegetable Momos | Vegetarian | veg | veg-momos.jpg |
| Paneer Momos | Vegetarian | paneer | Paneer-momos.jpg |
| Cheese Corn Momos | Vegetarian | veg | cheese-corn-momos.jpg |
| Fried Chicken Momos | Chicken | chicken | fried-chicken-momos.jpg |
| Tandoori Chicken Momos | Chicken | chicken | tandoori-momos.jpg |
| Chilli Garlic Momos | Spicy | chilli | Chilli-garlic-momos.jpg |

## 🔧 How It Works

### Database Structure
- **Products table** has an `image` field storing relative paths
- **Image paths** are stored as `products/foods/filename.jpg`
- **Full URLs** are generated dynamically via API

### API Endpoints
- `GET /api/product-images` - Get all products with images
- `GET /api/product-images/{id}` - Get specific product image
- `GET /api/product-images/category/{category}` - Get images by category

### React Native Integration
- **`useProductImages` hook** manages image fetching and caching
- **Automatic fallbacks** to emoji placeholders if images fail to load
- **Real-time updates** when new images are added to database

## 🎨 Adding New Images

### Option 1: Add to Existing Products
1. Place new images in `public/storage/products/foods/`
2. Update product records in database:
```sql
UPDATE products SET image = 'products/foods/new-image.jpg' WHERE name LIKE '%keyword%';
```

### Option 2: Create New Products
1. Add images to `public/storage/products/foods/`
2. Create new product records via admin panel
3. Images will be automatically assigned based on product names/tags

### Option 3: Manual Assignment
1. Run the setup script again (it skips products with existing images)
2. Or manually update specific products via admin interface

## 🐛 Troubleshooting

### Common Issues

**"No images found"**
- Check if `public/storage/products/foods/` folder exists
- Verify image files are readable
- Check database connection

**"Images not loading in React Native"**
- Ensure API endpoints are accessible
- Check network connectivity
- Verify image URLs are correct

**"Script fails to run"**
- Check PHP version (requires 8.0+)
- Verify Laravel is properly bootstrapped
- Check database permissions

### Debug Commands

```bash
# Check if products exist
php artisan tinker
>>> App\Models\Product::count()

# Check product images
php artisan tinker
>>> App\Models\Product::whereNotNull('image')->get(['name', 'image'])

# Test API endpoint
curl http://localhost:8000/api/product-images
```

## 📱 React Native Usage

### Basic Usage
```typescript
import { useProductImages } from '../src/hooks/useProductImages';

function MyComponent() {
  const { productImages, getImageUrl, isLoading } = useProductImages();
  
  // Get image for specific product
  const imageUrl = await getImageUrl('Steamed Chicken Momos');
  
  return (
    <Image source={{ uri: imageUrl }} style={styles.image} />
  );
}
```

### Advanced Usage
```typescript
// Get images by category
const chickenImages = productImages.filter(p => p.tag === 'chicken');

// Cache management
const refreshImages = async () => {
  await refreshImages();
};
```

## 🔄 Updating Images

### Batch Update
```bash
# Run setup script again (will skip existing images)
php scripts/setup-product-images.php
```

### Individual Update
```bash
# Update specific product
php artisan tinker
>>> $product = App\Models\Product::where('name', 'Steamed Chicken Momos')->first();
>>> $product->update(['image' => 'products/foods/new-steamed-momo.jpg']);
```

## 🎯 Next Steps

1. **Run the setup script** to populate your database
2. **Test the API endpoints** to ensure images are accessible
3. **Update your React Native app** to use the new image system
4. **Add more products and images** as needed
5. **Customize the image mapping** logic in the setup script

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Verify database connectivity
3. Check file permissions for image folders
4. Review Laravel logs for errors

---

**Happy coding! 🚀 Your momo shop now has beautiful product images!**

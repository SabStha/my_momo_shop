import { Image } from 'react-native';

interface PreloadConfig {
  productImages: string[];
  bulkImages: string[];
  staticAssets: string[];
}

class ImagePreloader {
  private preloadedImages = new Set<string>();
  private preloadPromises: Promise<void>[] = [];

  /**
   * Preload all images when app starts
   */
  async preloadAllImages(): Promise<void> {
    console.log('🖼️ [PRELOADER] Starting comprehensive image preloading...');
    
    const startTime = Date.now();
    
    try {
      // Get all images that need preloading
      const imagesToPreload = await this.getAllImagesToPreload();
      
      console.log(`🖼️ [PRELOADER] Found ${imagesToPreload.length} images to preload`);
      
      // Preload in batches to avoid overwhelming the system
      const batchSize = 5;
      for (let i = 0; i < imagesToPreload.length; i += batchSize) {
        const batch = imagesToPreload.slice(i, i + batchSize);
        const batchPromises = batch.map(imageUrl => this.preloadImage(imageUrl));
        
        await Promise.allSettled(batchPromises);
        
        const progress = Math.round(((i + batchSize) / imagesToPreload.length) * 100);
        console.log(`🖼️ [PRELOADER] Progress: ${Math.min(progress, 100)}% (${i + batchSize}/${imagesToPreload.length})`);
      }
      
      const endTime = Date.now();
      const duration = endTime - startTime;
      
      console.log(`🖼️ [PRELOADER] ✅ Preloading complete! ${this.preloadedImages.size} images cached in ${duration}ms`);
      
    } catch (error) {
      console.error('🖼️ [PRELOADER] ❌ Error during preloading:', error);
    }
  }

  /**
   * Get all images that need to be preloaded
   */
  private async getAllImagesToPreload(): Promise<string[]> {
    const images: string[] = [];

    try {
      // 1. Product images from menu
      const menuImages = await this.getProductImages();
      images.push(...menuImages);
      console.log(`🖼️ [PRELOADER] Found ${menuImages.length} product images`);

      // 2. Bulk package images
      const bulkImages = await this.getBulkImages();
      images.push(...bulkImages);
      console.log(`🖼️ [PRELOADER] Found ${bulkImages.length} bulk images`);

      // 3. Static assets (GIFs, icons, etc.)
      const staticAssets = this.getStaticAssets();
      images.push(...staticAssets);
      console.log(`🖼️ [PRELOADER] Found ${staticAssets.length} static assets`);

      // 4. Finds/merchandise images
      const findsImages = await this.getFindsImages();
      images.push(...findsImages);
      console.log(`🖼️ [PRELOADER] Found ${findsImages.length} finds images`);

    } catch (error) {
      console.error('🖼️ [PRELOADER] Error getting images to preload:', error);
    }

    // Remove duplicates
    const uniqueImages = [...new Set(images)];
    console.log(`🖼️ [PRELOADER] Total unique images: ${uniqueImages.length}`);
    
    return uniqueImages;
  }

  /**
   * Get product images from menu API
   */
  private async getProductImages(): Promise<string[]> {
    try {
      const response = await fetch('https://amakomomo.com/api/menu');
      const data = await response.json();
      
      const images: string[] = [];
      
      if (data.items) {
        Object.values(data.items).forEach((category: any) => {
          if (Array.isArray(category)) {
            category.forEach((item: any) => {
              if (item.image && !item.image.includes('default.jpg')) {
                const fullUrl = item.image.startsWith('http') 
                  ? item.image 
                  : `https://amakomomo.com/storage/${item.image}`;
                images.push(fullUrl);
              }
            });
          }
        });
      }
      
      return images;
    } catch (error) {
      console.error('🖼️ [PRELOADER] Error fetching product images:', error);
      return [];
    }
  }

  /**
   * Get bulk package images
   */
  private async getBulkImages(): Promise<string[]> {
    try {
      const response = await fetch('https://amakomomo.com/api/bulk');
      const data = await response.json();
      
      const images: string[] = [];
      
      if (data.packages) {
        Object.values(data.packages).forEach((type: any) => {
          if (typeof type === 'object') {
            Object.values(type).forEach((pkg: any) => {
              if (pkg.image) {
                const fullUrl = pkg.image.startsWith('http') 
                  ? pkg.image 
                  : `https://amakomomo.com/storage/${pkg.image}`;
                images.push(fullUrl);
              }
            });
          }
        });
      }
      
      return images;
    } catch (error) {
      console.error('🖼️ [PRELOADER] Error fetching bulk images:', error);
      return [];
    }
  }

  /**
   * Get finds/merchandise images
   */
  private async getFindsImages(): Promise<string[]> {
    try {
      const response = await fetch('https://amakomomo.com/api/finds');
      const data = await response.json();
      
      const images: string[] = [];
      
      if (data.merchandise) {
        Object.values(data.merchandise).forEach((category: any) => {
          if (Array.isArray(category)) {
            category.forEach((item: any) => {
              if (item.image_url || item.image) {
                const imageUrl = item.image_url || item.image;
                const fullUrl = imageUrl.startsWith('http') 
                  ? imageUrl 
                  : `https://amakomomo.com/storage/${imageUrl}`;
                images.push(fullUrl);
              }
            });
          }
        });
      }
      
      return images;
    } catch (error) {
      console.error('🖼️ [PRELOADER] Error fetching finds images:', error);
      return [];
    }
  }

  /**
   * Get static assets (GIFs, icons, etc.)
   */
  private getStaticAssets(): string[] {
    return [
      // These are bundled assets, so we don't need to preload them
      // They're already in the APK
    ];
  }

  /**
   * Preload a single image
   */
  private async preloadImage(imageUrl: string): Promise<void> {
    if (this.preloadedImages.has(imageUrl)) {
      return; // Already preloaded
    }

    try {
      await Image.prefetch(imageUrl);
      this.preloadedImages.add(imageUrl);
      console.log(`🖼️ [PRELOADER] ✅ Cached: ${imageUrl.split('/').pop()}`);
    } catch (error) {
      console.warn(`🖼️ [PRELOADER] ⚠️ Failed to cache: ${imageUrl.split('/').pop()}`);
    }
  }

  /**
   * Check if an image is already preloaded
   */
  isImagePreloaded(imageUrl: string): boolean {
    return this.preloadedImages.has(imageUrl);
  }

  /**
   * Get preload statistics
   */
  getStats() {
    return {
      totalPreloaded: this.preloadedImages.size,
      preloadedImages: Array.from(this.preloadedImages)
    };
  }
}

// Export singleton instance
export const imagePreloader = new ImagePreloader();
export default imagePreloader;

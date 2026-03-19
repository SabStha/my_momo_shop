import { useState, useEffect } from 'react';
import { imagePreloader } from '../services/ImagePreloader';

interface PreloaderStats {
  totalPreloaded: number;
  isPreloading: boolean;
  preloadProgress: number;
}

export function useImagePreloader(): PreloaderStats {
  const [stats, setStats] = useState<PreloaderStats>({
    totalPreloaded: 0,
    isPreloading: true,
    preloadProgress: 0,
  });

  useEffect(() => {
    // Check preloader status periodically
    const interval = setInterval(() => {
      const preloaderStats = imagePreloader.getStats();
      setStats(prev => ({
        ...prev,
        totalPreloaded: preloaderStats.totalPreloaded,
        isPreloading: preloaderStats.totalPreloaded === 0,
        preloadProgress: Math.min(100, (preloaderStats.totalPreloaded / 50) * 100), // Estimate based on typical image count
      }));
    }, 1000);

    return () => clearInterval(interval);
  }, []);

  return stats;
}

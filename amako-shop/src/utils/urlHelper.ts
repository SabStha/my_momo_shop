import { getCurrentNetworkIP } from '../config/network';

// Function to get the base URL for images and assets
export const getBaseUrl = (): string => {
  const ip = getCurrentNetworkIP();
  return `http://${ip}:8000`;
};

// Function to get image URL
export const getImageUrl = (path: string): string => {
  const baseUrl = getBaseUrl();
  return `${baseUrl}/storage/${path}`;
};

// Function to get banner image URL
export const getBannerUrl = (bannerNumber: number): string => {
  return getImageUrl(`banners/banner${bannerNumber}.jpg`);
};

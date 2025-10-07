import { useState, useEffect } from 'react';
import { API_BASE_URL } from '../config/api';

export interface SiteContent {
  key: string;
  title: string;
  content: string;
  type: string;
  component?: string;
  metadata?: any;
}

export interface SiteContentResponse {
  success: boolean;
  data: SiteContent[] | Record<string, any>;
  message?: string;
}

export interface AppConfig {
  app_name: string;
  app_tagline: string;
  hero_default_cta: string;
  empty_hero_message: string;
  product_default_subtitle: string;
}

/**
 * Hook to fetch site content by section
 */
export function useSiteContent(section: string, platform: 'web' | 'mobile' = 'mobile') {
  const [content, setContent] = useState<SiteContent[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchContent = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const response = await fetch(`${API_BASE_URL}/content/section/${section}?platform=${platform}`);
        const data: SiteContentResponse = await response.json();
        
        if (data.success) {
          setContent(Array.isArray(data.data) ? data.data : []);
        } else {
          setError(data.message || 'Failed to fetch content');
        }
      } catch (err) {
        setError('Network error while fetching content');
        console.error('Error fetching site content:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchContent();
  }, [section, platform]);

  return { content, loading, error };
}

/**
 * Hook to fetch content by key
 */
export function useContentByKey(key: string, platform: 'web' | 'mobile' = 'mobile') {
  const [content, setContent] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchContent = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const response = await fetch(`${API_BASE_URL}/content/key/${key}?platform=${platform}`);
        const data: SiteContentResponse = await response.json();
        
        if (data.success) {
          setContent(data.data.content);
        } else {
          setError(data.message || 'Failed to fetch content');
        }
      } catch (err) {
        setError('Network error while fetching content');
        console.error('Error fetching content by key:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchContent();
  }, [key, platform]);

  return { content, loading, error };
}

/**
 * Hook to fetch section content as key-value pairs
 */
export function useSectionContentArray(section: string, platform: 'web' | 'mobile' = 'mobile') {
  const [content, setContent] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchContent = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const response = await fetch(`${API_BASE_URL}/content/section/${section}/array?platform=${platform}`);
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('Response is not JSON');
        }
        
        const data: SiteContentResponse = await response.json();
        
        if (data.success) {
          setContent(data.data as Record<string, string>);
        } else {
          setError(data.message || 'Failed to fetch content');
        }
      } catch (err) {
        setError('Network error while fetching content');
        console.error('Error fetching section content array:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchContent();
  }, [section, platform]);

  return { content, loading, error };
}

/**
 * Hook to fetch app configuration
 */
export function useAppConfig(platform: 'web' | 'mobile' = 'mobile') {
  const [config, setConfig] = useState<AppConfig>({
    app_name: 'Amako Shop',
    app_tagline: 'From our kitchen to your heart',
    hero_default_cta: 'Add to Cart',
    empty_hero_message: 'No featured items available',
    product_default_subtitle: 'Delicious and authentic',
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchConfig = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const response = await fetch(`${API_BASE_URL}/content/app-config?platform=${platform}`);
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('Response is not JSON');
        }
        
        const data: SiteContentResponse = await response.json();
        
        if (data.success) {
          setConfig(data.data as AppConfig);
        } else {
          setError(data.message || 'Failed to fetch app config');
        }
      } catch (err) {
        setError('Network error while fetching app config');
        console.error('Error fetching app config:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchConfig();
  }, [platform]);

  return { config, loading, error };
}

/**
 * Hook to fetch multiple sections at once
 */
export function useMultipleSections(sections: string[], platform: 'web' | 'mobile' = 'mobile') {
  const [content, setContent] = useState<Record<string, Record<string, string>>>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchContent = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const response = await fetch(`${API_BASE_URL}/content/multiple-sections`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ sections, platform }),
        });
        
        const data: SiteContentResponse = await response.json();
        
        if (data.success) {
          setContent(data.data as Record<string, Record<string, string>>);
        } else {
          setError(data.message || 'Failed to fetch content');
        }
      } catch (err) {
        setError('Network error while fetching content');
        console.error('Error fetching multiple sections:', err);
      } finally {
        setLoading(false);
      }
    };

    if (sections.length > 0) {
      fetchContent();
    }
  }, [sections.join(','), platform]);

  return { content, loading, error };
}

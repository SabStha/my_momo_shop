const VERSION = 'v2';
const STATIC_CACHE = [
    '/',
    '/build/assets/app.css',
    '/build/assets/app.js',
    '/css/theme.css',
    '/js/cart.js',
    '/js/home.js',
    '/js/interactive-tour.js',
    '/images/icons/icon-72x72.png',
    '/images/icons/icon-96x96.png',
    '/images/icons/icon-128x128.png',
    '/images/icons/icon-144x144.png',
    '/images/icons/icon-152x152.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-384x384.png',
    '/images/icons/icon-512x512.png',
    '/images/icons/maskable-512.png'
];

const DYNAMIC_CACHE = [
    'https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap',
    'https://fonts.gstatic.com/s/cinzel/v26/8vIU387JBE6lRZvB7X7m6yeu5NWmwW7rPVZP_fmRcJg.woff2',
    'https://fonts.gstatic.com/s/inter/v12/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hiA.woff2'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('Service Worker installing...');
    event.waitUntil(
        Promise.all([
            caches.open(VERSION + '-static').then(cache => cache.addAll(STATIC_CACHE)),
            caches.open(VERSION + '-dynamic').then(cache => cache.addAll(DYNAMIC_CACHE))
        ]).then(() => {
            console.log('Service Worker installed successfully');
            return self.skipWaiting();
        })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker activating...');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== VERSION + '-static' && cacheName !== VERSION + '-dynamic') {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            console.log('Service Worker activated');
            return self.clients.claim();
        })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
    const request = event.request;
    
    // Skip non-GET requests
    if (request.method !== 'GET') return;
    
    // Skip chrome-extension and other non-http requests
    if (!request.url.startsWith('http')) return;
    
    event.respondWith(
        caches.match(request).then(response => {
            // Return cached version if available
            if (response) {
                return response;
            }
            
            // Clone the request for potential caching
            const fetchRequest = request.clone();
            
            return fetch(fetchRequest).then(response => {
                // Check if we received a valid response
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                
                // Clone the response for caching
                const responseToCache = response.clone();
                
                // Determine which cache to use based on request type
                let cacheName = VERSION + '-dynamic';
                if (STATIC_CACHE.includes(request.url) || request.url.includes('/build/')) {
                    cacheName = VERSION + '-static';
                }
                
                // Cache the response
                caches.open(cacheName).then(cache => {
                    cache.put(request, responseToCache);
                });
                
                return response;
            }).catch(() => {
                // Network failed, try to serve fallback
                if (request.destination === 'image') {
                    return caches.match('/images/icons/icon-192x192.png');
                }
                return new Response('Network error occurred', {
                    status: 408,
                    headers: { 'Content-Type': 'text/plain' }
                });
            });
        })
    );
});

// Background sync for offline functionality
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    try {
        // Handle any pending offline actions
        console.log('Background sync triggered');
    } catch (error) {
        console.error('Background sync failed:', error);
    }
} 
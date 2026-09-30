const CACHE_NAME = '{PWA_CACHE_NAME}';

const SCOPE = self.registration.scope;
const SCOPE_PATH = new URL(SCOPE).pathname;

const STATIC_PREFIXES = [
	SCOPE_PATH + 'static/',
	SCOPE_PATH + 'template/',
];

const PRECACHE_URLS = [
	SCOPE,
	new URL('index.php', SCOPE).href
];

self.addEventListener('install', event => {
	event.waitUntil(
	    caches.open(CACHE_NAME)
		.then(cache => cache.addAll(PRECACHE_URLS))
		.catch(err => {
			console.warn('[SW] precache failed:', err);
		})
		.then(() => self.skipWaiting())
	);
});

self.addEventListener('activate', event => {
	event.waitUntil(
	    caches.keys().then(keys =>
		Promise.all(
		    keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
		)
	    ).then(() => self.clients.claim())
	);
});

self.addEventListener('fetch', event => {
	const {request} = event;
	const url = new URL(request.url);

	if (request.method !== 'GET') return;
	if (url.origin !== self.location.origin) return;

	if (request.mode === 'navigate') {
		event.respondWith(
		    fetch(request)
			.then(response => {
				if (response.ok) {
					const copy = response.clone();
					caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
				}
				return response;
			})
			.catch(() => caches.match(request) || caches.match(SCOPE))
		);
		return;
	}

	event.respondWith(
	    caches.match(request).then(cached => {
		    if (cached) return cached;
		    return fetch(request).then(response => {
			    const contentType = response.headers.get('content-type') || '';
			    const isStatic =
				STATIC_PREFIXES.some(p => url.pathname.startsWith(p)) ||
				contentType.includes('image/') ||
				contentType.includes('text/css') ||
				contentType.includes('application/javascript');
			    if (response.ok && isStatic) {
				    const copy = response.clone();
				    caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
			    }
			    return response;
		    });
	    })
	);
});

/**
 * Resolves a path under public/ (e.g. "images/logo.png") against the app's actual base path.
 * A hardcoded "/images/logo.png" resolves against the domain root, which is wrong locally where
 * the app is deployed under /stmarkswebsite/ - the browser would request /images/logo.png (404)
 * instead of /stmarkswebsite/images/logo.png where the file actually is. Matches production too,
 * where BASE_URL is '/' and this is a no-op.
 */
export function staticAsset(path: string): string {
  return import.meta.env.BASE_URL.replace(/\/?$/, '/') + path.replace(/^\/+/, '')
}

import { onMounted } from 'vue'

export function usePageView(pageType = null, pageId = null) {
  function trackPageView() {
    const pageUrl = window.location.href

    console.log('Tracking page view:', { pageUrl, pageType, pageId })

    const params = new URLSearchParams({
      page_url: pageUrl,
      page_type: pageType || '',
      page_id: pageId || '',
    })

    fetch(`/api/page-view?${params}`, {
      method: 'GET',
    })
    .then(response => response.json())
    .then(data => {
      console.log('Page view tracked successfully:', data)
    })
    .catch((error) => {
      console.error('Failed to track page view:', error)
    })
  }

  onMounted(() => {
    trackPageView()
  })

  return {
    trackPageView,
  }
}

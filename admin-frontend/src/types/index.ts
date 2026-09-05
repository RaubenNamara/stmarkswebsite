export interface User {
  id: number
  name: string
  email: string
}

export interface Slide {
  id: number
  title: string | null
  caption: string | null
  type: 'image' | 'video'
  image_url: string | null
  video_url: string | null
}

export interface NewsItem {
  id: number
  title: string
  slug: string
  excerpt: string | null
  content: string
  image_path: string | null
  image_url: string | null
  is_published: number
  published_at: string | null
  created_at: string
  updated_at: string
}

export interface ImageVideoItem {
  id: number
  title: string
  [key: string]: unknown
  image_url?: string | null
  video_url?: string | null
}

export interface SinglePhotoItem {
  id: number
  [key: string]: unknown
  photo_url?: string | null
}

export interface PdfItem {
  id: number
  title: string
  file_url: string | null
  created_at: string
}

export interface Post {
  id: number
  title: string
  slug: string
  content: string
  is_published: number
  created_at: string
}

export interface GalleryEvent {
  id: number
  title: string
  created_at: string
  images: { id: number; image_url: string | null }[]
}

export interface Club {
  id: number
  title: string
  slug: string
  content: string
  created_at: string
  images: { id: number; image_url: string | null }[]
}

export interface CampusVoiceItem {
  id: number
  student_name: string
  title: string
  slug: string
  summary: string | null
  content: string
  featured_image_url: string | null
  category: string | null
  author: string | null
  featured: number
  status: 'draft' | 'published'
  views: number
  reading_time: number
  created_at: string
}

export interface MediaItem {
  id: number
  title: string | null
  type: 'image' | 'video' | 'link'
  file_url: string | null
  video_url: string | null
  created_at: string
}

export interface JobApplicationItem {
  id: number
  full_name: string
  contact: string
  email: string
  address: string
  position: string
  file_url: string | null
  created_at: string
}

export interface ContactItem {
  id: number
  name: string
  telephone: string | null
  email: string
  message: string
  is_read: number
  created_at: string
}

export interface ApiResponse<T = unknown> {
  success: boolean
  message: string
  data?: T
  errors?: Record<string, string>
}

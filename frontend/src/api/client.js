/**
 * REST client for PHP API — uses session cookies (credentials: include).
 */
function apiBase() {
  if (import.meta.env.VITE_API_BASE) {
    return import.meta.env.VITE_API_BASE.replace(/\/$/, '')
  }
  if (typeof window !== 'undefined') {
    return `${window.location.origin}/mero-kam/backend/api`
  }
  return '/mero-kam/backend/api'
}

export async function apiGet(path, params = {}) {
  const u = new URL(path.replace(/^\//, ''), apiBase() + '/')
  Object.entries(params).forEach(([k, v]) => {
    if (v !== undefined && v !== null && v !== '') u.searchParams.set(k, v)
  })
  const res = await fetch(u.toString(), { credentials: 'include' })
  const text = (await res.text()).replace(/^\uFEFF/, '').trim()
  let data
  try {
    data = JSON.parse(text)
  } catch {
    const preview = text.slice(0, 120).replace(/\s+/g, ' ')
    throw new Error(
      preview.startsWith('<')
        ? 'Server returned HTML (PHP error?). Check XAMPP PHP error log.'
        : `Invalid JSON from server: ${preview || '(empty)'}`
    )
  }
  if (!res.ok) {
    const msg = data.detail ? `${data.error || 'Error'}: ${data.detail}` : data.error || res.statusText
    throw new Error(msg)
  }
  return data
}

export async function apiSend(method, path, body, isForm = false) {
  const opts = {
    method,
    credentials: 'include',
  }
  if (isForm) {
    opts.body = body
  } else if (body !== undefined) {
    opts.headers = { 'Content-Type': 'application/json' }
    opts.body = JSON.stringify(body)
  }
  // Let browser set multipart boundary for FormData
  const res = await fetch(`${apiBase()}/${path.replace(/^\//, '')}`, opts)
  const text = (await res.text()).replace(/^\uFEFF/, '').trim()
  let data
  try {
    data = JSON.parse(text)
  } catch {
    const preview = text.slice(0, 120).replace(/\s+/g, ' ')
    throw new Error(
      preview.startsWith('<')
        ? 'Server returned HTML (PHP error?). Check XAMPP PHP error log.'
        : `Invalid JSON from server: ${preview || '(empty)'}`
    )
  }
  if (!res.ok) {
    const msg = data.detail ? `${data.error || 'Error'}: ${data.detail}` : data.error || res.statusText
    throw new Error(msg)
  }
  return data
}

export { apiBase }

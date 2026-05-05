import { useEffect, useState } from 'react'
import { Helmet } from 'react-helmet-async'
import { apiGet, apiSend } from '../api/client'

export function CompanySettings() {
  const [c, setC] = useState(null)
  const [form, setForm] = useState({ company_name: '', description: '', website: '', location: '' })
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')

  useEffect(() => {
    setLoading(true)
    apiGet('company_profile.php')
      .then((r) => {
        setC(r.company)
        if (r.company) {
          setForm({
            company_name: r.company.company_name || '',
            description: r.company.description || '',
            website: r.company.website || '',
            location: r.company.location || '',
          })
        }
      })
      .catch(() => setErr('Could not load company profile.'))
      .finally(() => setLoading(false))
  }, [])

  const save = async (e) => {
    e.preventDefault()
    setErr('')
    setMsg('')
    setSaving(true)
    try {
      // POST is reliable on XAMPP; some setups drop PUT bodies.
      const res = await apiSend('POST', 'company_profile.php', form)
      setC(res.company)
      setMsg('Saved successfully.')
    } catch (ex) {
      setErr(ex.message || 'Save failed.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <>
      <Helmet>
        <title>Company profile — Mero Kam</title>
      </Helmet>
      <div className="max-w-2xl mx-auto px-4 py-10">
        <h1 className="text-2xl font-bold mb-6">Company profile</h1>
        {loading && <p className="text-slate-500 mb-4">Loading…</p>}
        {err && !loading && <p className="text-red-600 text-sm mb-4">{err}</p>}
        {msg && <p className="text-emerald-600 text-sm mb-4">{msg}</p>}
        <form onSubmit={save} className="space-y-4">
          <input
            value={form.company_name}
            onChange={(e) => setForm({ ...form, company_name: e.target.value })}
            placeholder="Company name"
            required
            className="w-full rounded-xl border px-4 py-3"
          />
          <textarea
            value={form.description}
            onChange={(e) => setForm({ ...form, description: e.target.value })}
            rows={5}
            placeholder="About the company"
            className="w-full rounded-xl border px-4 py-3"
          />
          <input
            value={form.website}
            onChange={(e) => setForm({ ...form, website: e.target.value })}
            placeholder="Website"
            className="w-full rounded-xl border px-4 py-3"
          />
          <input
            value={form.location}
            onChange={(e) => setForm({ ...form, location: e.target.value })}
            placeholder="Location"
            className="w-full rounded-xl border px-4 py-3"
          />
          <button type="submit" disabled={saving || loading} className="px-6 py-3 rounded-xl bg-brand-600 text-white disabled:opacity-50">
            {saving ? 'Saving…' : 'Save'}
          </button>
        </form>
        {c?.logo && (
          <img
            src={`${typeof window !== 'undefined' ? window.location.origin : ''}/mero-kam/backend/${c.logo}`}
            alt="Logo"
            className="mt-6 h-16 object-contain"
          />
        )}
      </div>
    </>
  )
}

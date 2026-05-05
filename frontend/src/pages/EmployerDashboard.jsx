import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { apiGet } from '../api/client'

export function EmployerDashboard() {
  const [d, setD] = useState(null)
  const [err, setErr] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let alive = true
    setLoading(true)
    setErr(null)
    apiGet('dashboard_employer.php')
      .then((r) => {
        if (alive) {
          setD(r)
          setErr(null)
        }
      })
      .catch((e) => {
        if (alive) {
          setErr(e.message || 'Could not load dashboard (check login / API URL).')
          setD({
            ok: true,
            company: null,
            jobs: [],
            credits: { job_credits: 0, featured_credits: 0 },
            messages_preview: [],
          })
        }
      })
      .finally(() => {
        if (alive) setLoading(false)
      })
    return () => {
      alive = false
    }
  }, [])

  if (loading || !d) {
    return <div className="p-20 text-center">Loading…</div>
  }

  return (
    <>
      <Helmet>
        <title>Employer dashboard — Mero Kam</title>
      </Helmet>
      <div className="max-w-7xl mx-auto px-4 py-10">
        {err && (
          <div className="mb-6 rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-sm text-amber-900 dark:text-amber-100">
            {err}
          </div>
        )}
        <div className="flex flex-wrap justify-between gap-4 mb-8">
          <h1 className="text-3xl font-bold">Employer dashboard</h1>
          <div className="flex gap-2">
            <Link to="/post-job" className="px-4 py-2 rounded-xl bg-brand-600 text-white font-medium">
              Post job
            </Link>
            <Link to="/company" className="px-4 py-2 rounded-xl border border-slate-300">
              Company profile
            </Link>
            <Link to="/messages" className="px-4 py-2 rounded-xl border border-slate-300">
              Messages
            </Link>
            <Link to="/pricing" className="px-4 py-2 rounded-xl border border-slate-300">
              Plans
            </Link>
          </div>
        </div>

        <div className="grid sm:grid-cols-3 gap-4 mb-10">
          <div className="rounded-2xl border p-6">
            <p className="text-sm text-slate-500">Job credits</p>
            <p className="text-3xl font-bold text-brand-600">{d.credits?.job_credits ?? 0}</p>
          </div>
          <div className="rounded-2xl border p-6">
            <p className="text-sm text-slate-500">Featured credits</p>
            <p className="text-3xl font-bold text-orange-600">{d.credits?.featured_credits ?? 0}</p>
          </div>
          <div className="rounded-2xl border p-6">
            <p className="text-sm text-slate-500">Company</p>
            <p className="font-semibold">{d.company?.company_name || 'Not set'}</p>
          </div>
        </div>

        <h2 className="text-xl font-semibold mb-4">Your listings</h2>
        <div className="space-y-3">
          {d.jobs?.length === 0 && <p className="text-slate-500">No jobs posted yet.</p>}
          {d.jobs?.map((j) => (
            <div key={j.id} className="rounded-xl border border-slate-200 dark:border-slate-800 p-4 flex flex-wrap justify-between gap-4">
              <div>
                <p className="font-semibold">{j.title}</p>
                <p className="text-sm text-slate-500">
                  {j.status} · {j.location}
                </p>
              </div>
              <Link to={`/jobs/${j.id}`} className="text-brand-600 text-sm">
                View
              </Link>
              <Link to={`/employer/applicants?job_id=${j.id}`} className="text-sm text-orange-600 font-medium">
                View applicants
              </Link>
            </div>
          ))}
        </div>
      </div>
    </>
  )
}

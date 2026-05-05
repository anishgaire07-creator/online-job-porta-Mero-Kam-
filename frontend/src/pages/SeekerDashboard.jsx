import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { apiGet, apiSend } from '../api/client'
import { JobCard } from '../components/JobCard'

export function SeekerDashboard() {
  const [d, setD] = useState(null)
  const [err, setErr] = useState(null)
  const [loading, setLoading] = useState(true)
  const [alertKw, setAlertKw] = useState('')
  const [tab, setTab] = useState('apps')

  const load = () =>
    apiGet('dashboard_seeker.php')
      .then((r) => {
        setD(r)
        setErr(null)
      })
      .catch((e) => {
        setErr(e.message || 'Could not load dashboard.')
        setD({
          applications: [],
          saved_jobs: [],
          job_alerts: [],
          recent_jobs: [],
          recommendations: [],
        })
      })
      .finally(() => setLoading(false))

  useEffect(() => {
    load()
  }, [])

  const addAlert = async (e) => {
    e.preventDefault()
    await apiSend('POST', 'job_alerts.php', { keywords: alertKw })
    setAlertKw('')
    load()
  }

  if (loading || !d) {
    return <div className="p-20 text-center">Loading…</div>
  }

  return (
    <>
      <Helmet>
        <title>Seeker dashboard — Mero Kam</title>
      </Helmet>
      <div className="max-w-7xl mx-auto px-4 py-10">
        {err && (
          <div className="mb-6 rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-sm text-amber-900 dark:text-amber-100">
            {err}
          </div>
        )}
        <h1 className="text-3xl font-bold mb-8">Job seeker dashboard</h1>
        <div className="flex flex-wrap gap-2 mb-8">
          {['apps', 'saved', 'alerts', 'recent', 'forYou'].map((t) => (
            <button
              key={t}
              type="button"
              onClick={() => setTab(t)}
              className={`px-4 py-2 rounded-xl text-sm font-medium ${
                tab === t ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800'
              }`}
            >
              {t === 'apps' && 'Applications'}
              {t === 'saved' && 'Saved'}
              {t === 'alerts' && 'Job alerts'}
              {t === 'recent' && 'Recently viewed'}
              {t === 'forYou' && 'For you'}
            </button>
          ))}
          <Link to="/resume" className="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-sm">
            Resume builder
          </Link>
          <Link to="/messages" className="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-sm">
            Messages
          </Link>
        </div>

        {tab === 'apps' && (
          <div className="space-y-3">
            {d.applications?.length === 0 && <p className="text-slate-500">No applications yet.</p>}
            {d.applications?.map((a) => (
              <div key={a.id} className="rounded-xl border border-slate-200 dark:border-slate-800 p-4 flex justify-between">
                <div>
                  <p className="font-semibold">{a.job_title}</p>
                  <p className="text-sm text-slate-500">{a.company_name}</p>
                  <p className="text-xs mt-1">Status: {a.status}</p>
                </div>
                <Link to={`/jobs/${a.job_id}`} className="text-brand-600 text-sm">
                  View
                </Link>
              </div>
            ))}
          </div>
        )}

        {tab === 'saved' && (
          <div className="grid sm:grid-cols-2 gap-4">
            {d.saved_jobs?.map((s) => (
              <JobCard key={s.job_id} job={{ ...s, id: s.job_id, title: s.title }} />
            ))}
          </div>
        )}

        {tab === 'alerts' && (
          <div>
            <form onSubmit={addAlert} className="flex gap-2 mb-6">
              <input
                value={alertKw}
                onChange={(e) => setAlertKw(e.target.value)}
                placeholder="Keywords e.g. React, PHP"
                className="flex-1 rounded-xl border px-4 py-2"
              />
              <button type="submit" className="px-4 py-2 rounded-xl bg-brand-600 text-white">
                Add alert
              </button>
            </form>
            <ul className="space-y-2">
              {d.job_alerts?.map((a) => (
                <li key={a.id} className="flex justify-between rounded-lg border px-4 py-2">
                  <span>{a.keywords}</span>
                  <button
                    type="button"
                    className="text-red-600 text-sm"
                    onClick={async () => {
                      await apiSend('DELETE', 'job_alerts.php', { id: a.id })
                      load()
                    }}
                  >
                    Remove
                  </button>
                </li>
              ))}
            </ul>
          </div>
        )}

        {tab === 'recent' && (
          <div className="grid sm:grid-cols-2 gap-4">
            {d.recent_jobs?.map((j) => (
              <JobCard key={j.id} job={j} />
            ))}
          </div>
        )}

        {tab === 'forYou' && (
          <div className="grid sm:grid-cols-2 gap-4">
            {d.recommendations?.map((j) => (
              <JobCard key={j.id} job={j} />
            ))}
          </div>
        )}
      </div>
    </>
  )
}

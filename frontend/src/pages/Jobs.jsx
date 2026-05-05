import { useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { apiGet } from '../api/client'
import { JobCard } from '../components/JobCard'
import { useI18n } from '../contexts/I18nContext'

export function Jobs() {
  const { t } = useI18n()
  const [params, setParams] = useSearchParams()
  const q = params.get('q') || ''
  const location = params.get('location') || ''
  const [type, setType] = useState(params.get('type') || '')
  const [exp, setExp] = useState(params.get('experience_level') || '')
  const [salMin, setSalMin] = useState(Number(params.get('salary_min')) || 0)
  const [salMax, setSalMax] = useState(Number(params.get('salary_max')) || 200000)
  const [page, setPage] = useState(1)
  const [data, setData] = useState({ jobs: [], total: 0 })
  const [loading, setLoading] = useState(true)

  const filters = useMemo(
    () => ({
      q,
      location,
      type,
      experience_level: exp,
      salary_min: salMin > 0 ? salMin : undefined,
      salary_max: salMax < 200000 ? salMax : undefined,
      page,
      per: 12,
    }),
    [q, location, type, exp, salMin, salMax, page]
  )

  useEffect(() => {
    setLoading(true)
    apiGet('get_jobs.php', filters)
      .then(setData)
      .catch(() => setData({ jobs: [], total: 0 }))
      .finally(() => setLoading(false))
  }, [filters])

  return (
    <>
      <Helmet>
        <title>Search Jobs — Mero Kam</title>
      </Helmet>
      <div className="max-w-7xl mx-auto px-4 py-10">
        <h1 className="text-3xl font-bold mb-4">Browse jobs</h1>
        <p className="text-sm text-slate-600 dark:text-slate-400 mb-8 rounded-xl border border-blue-100 dark:border-blue-900 bg-blue-50/80 dark:bg-blue-950/40 px-4 py-3">
          Only <strong>approved</strong> jobs appear here. New posts stay <strong>pending</strong> until an admin approves them in the admin panel — then they show up in search and filters.
        </p>
        <div className="grid lg:grid-cols-4 gap-8">
          <aside className="lg:col-span-1 space-y-6 animate-fade-in">
            <div className="rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-card sticky top-24">
              <h2 className="font-semibold mb-4">{t('jobs.filters')}</h2>
              <label className="block text-sm mb-2">{t('jobs.type')}</label>
              <select
                value={type}
                onChange={(e) => {
                  setType(e.target.value)
                  setPage(1)
                }}
                className="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
              >
                <option value="">Any</option>
                <option value="full-time">Full-time</option>
                <option value="part-time">Part-time</option>
                <option value="contract">Contract</option>
                <option value="internship">Internship</option>
              </select>
              <label className="block text-sm mt-4 mb-2">{t('jobs.experience')}</label>
              <select
                value={exp}
                onChange={(e) => {
                  setExp(e.target.value)
                  setPage(1)
                }}
                className="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
              >
                <option value="">Any</option>
                <option value="entry">Entry</option>
                <option value="mid">Mid</option>
                <option value="senior">Senior</option>
                <option value="lead">Lead</option>
              </select>
              <label className="block text-sm mt-4 mb-2">{t('jobs.salary')}</label>
              <p className="text-xs text-slate-500 mb-2">
                NPR {salMin.toLocaleString()} — {salMax.toLocaleString()}
              </p>
              <input
                type="range"
                min={0}
                max={200000}
                step={5000}
                value={salMin}
                onChange={(e) => {
                  const v = Number(e.target.value)
                  setSalMin(Math.min(v, salMax - 5000))
                  setPage(1)
                }}
                className="w-full accent-brand-600"
              />
              <input
                type="range"
                min={0}
                max={200000}
                step={5000}
                value={salMax}
                onChange={(e) => {
                  const v = Number(e.target.value)
                  setSalMax(Math.max(v, salMin + 5000))
                  setPage(1)
                }}
                className="w-full mt-2 accent-brand-600"
              />
              <button
                type="button"
                onClick={() => {
                  setSalMin(0)
                  setSalMax(200000)
                  setPage(1)
                }}
                className="mt-3 text-sm text-brand-600"
              >
                Reset salary
              </button>
            </div>
          </aside>
          <div className="lg:col-span-3">
            <div className="flex flex-wrap gap-2 mb-4">
              <input
                defaultValue={q}
                onBlur={(e) => {
                  const v = e.target.value
                  const p = new URLSearchParams(params)
                  if (v) p.set('q', v)
                  else p.delete('q')
                  setParams(p)
                  setPage(1)
                }}
                placeholder="Keyword"
                className="flex-1 min-w-[200px] rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2"
              />
              <input
                defaultValue={location}
                onBlur={(e) => {
                  const v = e.target.value
                  const p = new URLSearchParams(params)
                  if (v) p.set('location', v)
                  else p.delete('location')
                  setParams(p)
                  setPage(1)
                }}
                placeholder="Location"
                className="flex-1 min-w-[160px] rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2"
              />
            </div>
            {loading ? (
              <p className="text-slate-500">Loading…</p>
            ) : (
              <>
                <p className="text-sm text-slate-500 mb-4">{data.total} jobs found</p>
                <div className="grid sm:grid-cols-2 gap-4">
                  {data.jobs?.map((j) => (
                    <JobCard key={j.id} job={j} />
                  ))}
                </div>
                <div className="flex justify-center gap-4 mt-8">
                  <button
                    type="button"
                    disabled={page <= 1}
                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                    className="px-4 py-2 rounded-lg border disabled:opacity-40"
                  >
                    Previous
                  </button>
                  <button
                    type="button"
                    disabled={page * 12 >= data.total}
                    onClick={() => setPage((p) => p + 1)}
                    className="px-4 py-2 rounded-lg border disabled:opacity-40"
                  >
                    Next
                  </button>
                </div>
              </>
            )}
          </div>
        </div>
      </div>
    </>
  )
}

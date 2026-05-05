import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { apiGet, apiSend } from '../api/client'
import { useAuth } from '../contexts/AuthContext'

export function JobDetail() {
  const { id } = useParams()
  const { user } = useAuth()
  const [job, setJob] = useState(null)
  const [extra, setExtra] = useState({})
  const [cover, setCover] = useState('')
  const [msg, setMsg] = useState('')

  useEffect(() => {
    apiGet(`get_job.php`, { id })
      .then((d) => {
        setJob(d.job)
        setExtra({ applied: d.applied, saved: d.saved })
      })
      .catch(() => setJob(null))
  }, [id])

  const apply = async (e) => {
    e.preventDefault()
    setMsg('')
    const fd = new FormData()
    fd.append('job_id', id)
    fd.append('cover_letter', cover)
    const cv = e.target.cv.files[0]
    if (cv) fd.append('cv', cv)
    try {
      await apiSend('POST', 'apply_job.php', fd, true)
      setMsg('Application submitted!')
      setExtra((x) => ({ ...x, applied: true }))
    } catch (err) {
      setMsg(err.message)
    }
  }

  const toggleSave = async () => {
    if (!user || user.role !== 'seeker') return
    await apiSend('POST', 'save_job.php', {
      job_id: Number(id),
      action: extra.saved ? 'unsave' : 'save',
    })
    setExtra((x) => ({ ...x, saved: !x.saved }))
  }

  if (!job) {
    return <div className="max-w-3xl mx-auto px-4 py-20 text-center">Job not found.</div>
  }

  const sal =
    job.salary_min && job.salary_max
      ? `NPR ${Number(job.salary_min).toLocaleString()} – ${Number(job.salary_max).toLocaleString()}`
      : 'Negotiable'

  return (
    <>
      <Helmet>
        <title>{job.title} — {job.company_name} | Mero Kam</title>
        <meta name="description" content={job.description?.slice(0, 160)} />
      </Helmet>
      <article className="max-w-4xl mx-auto px-4 py-10">
        <div className="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80 p-8 shadow-card-lg">
          <h1 className="text-3xl font-bold">{job.title}</h1>
          <p className="text-xl text-brand-600 dark:text-brand-400 mt-1">{job.company_name}</p>
          <div className="flex flex-wrap gap-2 mt-4 text-sm">
            <span className="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">{job.location}</span>
            <span className="px-3 py-1 rounded-full bg-brand-50 dark:bg-brand-950">{job.type}</span>
            <span className="px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950">{job.experience_level}</span>
            <span className="px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950 text-emerald-800">{sal}</span>
          </div>
          <div className="prose dark:prose-invert max-w-none mt-8 whitespace-pre-wrap">{job.description}</div>

          {user?.role === 'seeker' && (
            <div className="mt-10 border-t border-slate-200 dark:border-slate-700 pt-8">
              <div className="flex gap-4 mb-6">
                <button
                  type="button"
                  onClick={toggleSave}
                  className="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600"
                >
                  {extra.saved ? 'Saved' : 'Save job'}
                </button>
                {job.employer_user_id && (
                  <Link
                    to={`/messages?to=${job.employer_user_id}&job=${job.id}`}
                    className="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800"
                  >
                    Message employer
                  </Link>
                )}
              </div>
              {!extra.applied ? (
                <form onSubmit={apply} className="space-y-4">
                  <label className="block text-sm font-medium">Cover letter</label>
                  <textarea
                    value={cover}
                    onChange={(e) => setCover(e.target.value)}
                    rows={4}
                    className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3"
                    placeholder="Why are you a great fit?"
                  />
                  <label className="block text-sm font-medium">CV (PDF/DOCX)</label>
                  <input type="file" name="cv" accept=".pdf,.doc,.docx" className="block w-full text-sm" />
                  <button type="submit" className="px-6 py-3 rounded-xl bg-brand-600 text-white font-semibold">
                    Apply now
                  </button>
                  {msg && <p className="text-sm text-emerald-600">{msg}</p>}
                </form>
              ) : (
                <p className="text-emerald-600 font-medium">You have applied to this job.</p>
              )}
            </div>
          )}
        </div>
      </article>
    </>
  )
}

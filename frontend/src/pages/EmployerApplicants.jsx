import { useEffect, useState } from 'react'
import { useSearchParams, Link } from 'react-router-dom'
import { apiGet, apiSend } from '../api/client'

export function EmployerApplicants() {
  const [params] = useSearchParams()
  const jobId = params.get('job_id')
  const [data, setData] = useState(null)
  const [status, setStatus] = useState('')

  useEffect(() => {
    if (!jobId) return
    apiGet('applicants.php', { job_id: jobId, status: status || undefined }).then(setData)
  }, [jobId, status])

  const updateStatus = async (applicationId, newStatus) => {
    await apiSend('POST', 'application_status.php', { application_id: applicationId, status: newStatus })
    if (jobId) apiGet('applicants.php', { job_id: jobId }).then(setData)
  }

  if (!jobId) {
    return (
      <div className="max-w-3xl mx-auto px-4 py-16">
        <p>Select a job from the dashboard with ?job_id=</p>
        <Link to="/employer" className="text-brand-600">
          Back
        </Link>
      </div>
    )
  }

  const base = typeof window !== 'undefined' ? window.location.origin + '/mero-kam/backend/' : ''

  return (
    <div className="max-w-5xl mx-auto px-4 py-10">
      <Link to="/employer" className="text-sm text-brand-600 mb-4 inline-block">
        ← Dashboard
      </Link>
      <h1 className="text-2xl font-bold mb-4">Applicants</h1>
      <select
        value={status}
        onChange={(e) => setStatus(e.target.value)}
        className="mb-6 rounded-lg border px-3 py-2"
      >
        <option value="">All statuses</option>
        <option value="pending">Pending</option>
        <option value="shortlisted">Shortlisted</option>
        <option value="rejected">Rejected</option>
        <option value="hired">Hired</option>
      </select>
      <div className="space-y-4">
        {data?.applicants?.map((a) => (
          <div key={a.id} className="rounded-xl border p-4 flex flex-wrap justify-between gap-4">
            <div>
              <p className="font-semibold">{a.applicant_name}</p>
              <p className="text-sm text-slate-500">{a.email}</p>
              <p className="text-sm">{a.phone}</p>
              <p className="text-xs mt-2">Status: {a.status}</p>
              {a.cv_path && (
                <a href={`${base}${a.cv_path}`} target="_blank" rel="noreferrer" className="text-brand-600 text-sm">
                  Download CV
                </a>
              )}
            </div>
            <div className="flex flex-wrap gap-2">
              {['shortlisted', 'rejected', 'hired'].map((s) => (
                <button
                  key={s}
                  type="button"
                  onClick={() => updateStatus(a.id, s)}
                  className="px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-sm capitalize"
                >
                  {s}
                </button>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

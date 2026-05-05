import { Link } from 'react-router-dom'

export function JobCard({ job }) {
  const sal =
    job.salary_min && job.salary_max
      ? `NPR ${Number(job.salary_min).toLocaleString()} – ${Number(job.salary_max).toLocaleString()}`
      : job.salary_min
        ? `From NPR ${Number(job.salary_min).toLocaleString()}`
        : 'Salary negotiable'

  return (
    <Link
      to={`/jobs/${job.id}`}
      className="group block rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-900/80 p-5 shadow-card hover:shadow-card-lg hover:border-brand-300 dark:hover:border-brand-700 transition-all duration-300 animate-fade-in"
    >
      <div className="flex gap-4">
        <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center font-bold text-lg shrink-0">
          {(job.company_name || 'C').slice(0, 1)}
        </div>
        <div className="min-w-0 flex-1">
          <h3 className="font-semibold text-lg text-slate-900 dark:text-white group-hover:text-brand-600 truncate">{job.title}</h3>
          <p className="text-slate-600 dark:text-slate-400 text-sm">{job.company_name}</p>
          <div className="mt-2 flex flex-wrap gap-2 text-xs">
            <span className="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800">{job.location || 'Remote'}</span>
            <span className="px-2 py-0.5 rounded-full bg-brand-50 dark:bg-brand-950 text-brand-800 dark:text-brand-200">{job.type}</span>
            <span className="px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950 text-amber-800">{job.experience_level}</span>
            {job.is_featured == 1 && (
              <span className="px-2 py-0.5 rounded-full bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-200">Featured</span>
            )}
          </div>
          <p className="mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">{sal}</p>
        </div>
      </div>
    </Link>
  )
}

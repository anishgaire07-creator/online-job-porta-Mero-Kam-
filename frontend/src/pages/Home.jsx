import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { apiGet } from '../api/client'
import { JobCard } from '../components/JobCard'
import { useI18n } from '../contexts/I18nContext'

const categories = [
  { name: 'IT & Software', icon: '💻' },
  { name: 'Marketing', icon: '📣' },
  { name: 'Banking', icon: '🏦' },
  { name: 'Education', icon: '📚' },
  { name: 'Health', icon: '🏥' },
  { name: 'Sales', icon: '📈' },
]

export function Home() {
  const { t } = useI18n()
  const [data, setData] = useState(null)
  const nav = useNavigate()

  useEffect(() => {
    apiGet('home.php').then(setData).catch(() => setData(null))
  }, [])

  const stats = data?.stats || { jobs: 0, companies: 0, users: 0 }

  return (
    <>
      <Helmet>
        <title>Mero Kam — Jobs in Nepal</title>
        <meta name="description" content="Find jobs, post vacancies, and grow your career with Mero Kam." />
      </Helmet>
      <section className="relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-700 to-indigo-900 text-white">
        <div className="absolute inset-0 opacity-30 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.08\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]" />
        <div className="max-w-7xl mx-auto px-4 py-20 md:py-28 relative">
          <div className="max-w-3xl animate-fade-in">
            <h1 className="text-4xl md:text-5xl font-bold leading-tight">{t('hero.title')}</h1>
            <p className="mt-4 text-lg text-blue-100">{t('hero.subtitle')}</p>
            <form
              className="mt-10 flex flex-col sm:flex-row gap-3"
              onSubmit={(e) => {
                e.preventDefault()
                const fd = new FormData(e.target)
                const q = fd.get('q') || ''
                const loc = fd.get('loc') || ''
                nav(`/jobs?q=${encodeURIComponent(q)}&location=${encodeURIComponent(loc)}`)
              }}
            >
              <input
                name="q"
                placeholder={t('hero.search')}
                className="flex-1 rounded-xl px-4 py-3 text-slate-900 shadow-lg"
              />
              <input name="loc" placeholder={t('hero.location')} className="sm:w-48 rounded-xl px-4 py-3 text-slate-900 shadow-lg" />
              <button type="submit" className="rounded-xl bg-white text-brand-700 font-semibold px-8 py-3 shadow-lg hover:bg-blue-50">
                {t('hero.search')}
              </button>
            </form>
            <div className="mt-8 flex flex-wrap gap-4">
              <Link to="/jobs" className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20">
                Find Job →
              </Link>
              <Link to="/register" className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 font-semibold">
                {t('hero.ctaHire')}
              </Link>
            </div>
          </div>
        </div>
      </section>

      <section className="max-w-7xl mx-auto px-4 py-14">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {[
            ['Active jobs', stats.jobs],
            ['Companies', stats.companies],
            ['Members', stats.users],
            ['Cities', '20+'],
          ].map(([label, val]) => (
            <div key={label} className="rounded-2xl border border-slate-200 dark:border-slate-800 p-6 text-center shadow-card">
              <p className="text-3xl font-bold text-brand-600 dark:text-brand-400">{val}</p>
              <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">{label}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="max-w-7xl mx-auto px-4 py-8">
        <h2 className="text-2xl font-bold mb-6">Featured jobs</h2>
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          {(data?.featured_jobs || []).map((j) => (
            <JobCard key={j.id} job={j} />
          ))}
        </div>
      </section>

      <section className="max-w-7xl mx-auto px-4 py-8">
        <h2 className="text-2xl font-bold mb-6">Latest jobs</h2>
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
          {(data?.latest_jobs || []).map((j) => (
            <JobCard key={j.id} job={j} />
          ))}
        </div>
      </section>

      <section className="max-w-7xl mx-auto px-4 py-12">
        <h2 className="text-2xl font-bold mb-6">Job categories</h2>
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
          {categories.map((c) => (
            <Link
              key={c.name}
              to={`/jobs?q=${encodeURIComponent(c.name)}`}
              className="rounded-xl border border-slate-200 dark:border-slate-800 p-4 text-center hover:border-brand-400 hover:shadow-card transition-all"
            >
              <span className="text-2xl">{c.icon}</span>
              <p className="text-sm font-medium mt-2">{c.name}</p>
            </Link>
          ))}
        </div>
      </section>

      <section className="max-w-7xl mx-auto px-4 py-12">
        <h2 className="text-2xl font-bold mb-6">Top companies</h2>
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {(data?.top_companies || []).map((c) => (
            <div key={c.id} className="rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-card">
              <div className="font-semibold text-lg">{c.company_name}</div>
              <p className="text-sm text-slate-500 mt-1">{c.location}</p>
              <p className="text-sm text-brand-600 mt-2">{c.job_count} open roles</p>
            </div>
          ))}
        </div>
      </section>
    </>
  )
}

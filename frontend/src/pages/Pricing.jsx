import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { apiGet, apiSend } from '../api/client'
import { useAuth } from '../contexts/AuthContext'

export function Pricing() {
  const { user } = useAuth()
  const navigate = useNavigate()
  const [plans, setPlans] = useState([])

  useEffect(() => {
    apiGet('payments.php').then((r) => setPlans(r.plans || []))
  }, [])

  const buy = async (slug) => {
    if (!user || user.role !== 'employer') {
      navigate('/register')
      return
    }
    await apiSend('POST', 'payments.php', { plan_slug: slug })
    alert('Plan activated (demo — credits added).')
  }

  return (
    <>
      <Helmet>
        <title>Pricing — Mero Kam</title>
      </Helmet>
      <div className="max-w-5xl mx-auto px-4 py-16">
        <h1 className="text-3xl font-bold text-center mb-4">Plans & pricing</h1>
        <p className="text-center text-slate-600 dark:text-slate-400 mb-12">Basic, Premium, and pay-per-job posting.</p>
        <div className="grid md:grid-cols-3 gap-6">
          {plans.map((p) => (
            <div
              key={p.id}
              className={`rounded-2xl border p-8 shadow-card flex flex-col ${p.slug === 'premium' ? 'border-brand-500 ring-2 ring-brand-200' : ''}`}
            >
              <h2 className="text-xl font-bold">{p.name}</h2>
              <p className="text-3xl font-bold mt-4">
                {p.currency} {Number(p.price).toLocaleString()}
              </p>
              <p className="text-sm text-slate-500 mt-2">{p.features}</p>
              <ul className="mt-4 text-sm space-y-1 text-slate-600">
                <li>{p.job_credits} job credits</li>
                <li>{p.featured_jobs} featured slots</li>
                <li>{p.duration_days} days validity</li>
              </ul>
              <button
                type="button"
                onClick={() => buy(p.slug)}
                className="mt-6 w-full py-3 rounded-xl bg-brand-600 text-white font-semibold"
              >
                Choose plan
              </button>
            </div>
          ))}
        </div>
      </div>
    </>
  )
}

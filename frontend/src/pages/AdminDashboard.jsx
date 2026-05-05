import { useCallback, useEffect, useState } from 'react'
import { Helmet } from 'react-helmet-async'
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
  CartesianGrid,
} from 'recharts'
import { apiGet, apiSend } from '../api/client'

export function AdminDashboard() {
  const [analytics, setAnalytics] = useState(null)
  const [users, setUsers] = useState([])
  const [jobs, setJobs] = useState([])
  const [payments, setPayments] = useState([])
  const [tab, setTab] = useState('overview')
  const [jobFilter, setJobFilter] = useState('all')
  const [loadError, setLoadError] = useState(null)
  const [refreshing, setRefreshing] = useState(false)

  const load = useCallback(async () => {
    setLoadError(null)
    setRefreshing(true)
    try {
      const jobParams = { filter: jobFilter === 'pending' ? 'pending' : 'all' }
      const [a, u, j, p] = await Promise.all([
        apiGet('admin_analytics.php'),
        apiGet('admin_users.php'),
        apiGet('admin_jobs.php', jobParams),
        apiGet('admin_payments.php'),
      ])
      setAnalytics(a.analytics ?? null)
      setUsers(u.users || [])
      setJobs(j.jobs || [])
      setPayments(p.payments || [])
    } catch (e) {
      setLoadError(e.message || 'Could not load admin data. Are you logged in as admin?')
    } finally {
      setRefreshing(false)
    }
  }, [jobFilter])

  useEffect(() => {
    load()
  }, [load])

  useEffect(() => {
    const onVis = () => {
      if (document.visibilityState === 'visible') load()
    }
    document.addEventListener('visibilitychange', onVis)
    return () => document.removeEventListener('visibilitychange', onVis)
  }, [load])

  const moderate = async (id, status) => {
    await apiSend('POST', 'admin_jobs.php', { id, status })
    await load()
  }

  const chartData = (analytics?.jobs_by_month || []).map((row) => ({
    month: row.m,
    jobs: Number(row.c),
  }))

  return (
    <>
      <Helmet>
        <title>Admin — Mero Kam</title>
      </Helmet>
      <div className="max-w-7xl mx-auto px-4 py-10">
        <div className="flex flex-wrap items-center justify-between gap-4 mb-8">
          <h1 className="text-3xl font-bold">Admin panel</h1>
          <button
            type="button"
            onClick={() => load()}
            disabled={refreshing}
            className="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 text-sm font-medium disabled:opacity-50"
          >
            {refreshing ? 'Refreshing…' : 'Refresh data'}
          </button>
        </div>

        {loadError && (
          <div className="mb-6 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/40 dark:border-red-800 px-4 py-3 text-sm text-red-800 dark:text-red-200">
            {loadError}
          </div>
        )}

        <div className="flex flex-wrap gap-2 mb-8">
          {['overview', 'users', 'jobs', 'payments'].map((t) => (
            <button
              key={t}
              type="button"
              onClick={() => setTab(t)}
              className={`px-4 py-2 rounded-xl capitalize ${tab === t ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800'}`}
            >
              {t}
            </button>
          ))}
        </div>

        {tab === 'overview' && analytics && (
          <div className="space-y-8">
            <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
              {[
                ['Users', analytics.users],
                ['Approved jobs', analytics.jobs],
                ['Pending jobs', analytics.pending_jobs],
                ['Applications', analytics.applications],
                ['Companies', analytics.companies],
                ['Revenue (NPR)', analytics.revenue],
              ].map(([label, val]) => (
                <div key={label} className="rounded-2xl border p-6">
                  <p className="text-sm text-slate-500">{label}</p>
                  <p className="text-2xl font-bold">{val}</p>
                </div>
              ))}
            </div>
            <div className="h-80 rounded-2xl border p-4">
              <h2 className="font-semibold mb-4">Jobs by month</h2>
              <ResponsiveContainer width="100%" height="85%">
                <BarChart data={chartData}>
                  <CartesianGrid strokeDasharray="3 3" opacity={0.3} />
                  <XAxis dataKey="month" tick={{ fontSize: 12 }} />
                  <YAxis tick={{ fontSize: 12 }} />
                  <Tooltip />
                  <Bar dataKey="jobs" fill="#2563eb" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </div>
        )}

        {tab === 'users' && (
          <div className="overflow-x-auto rounded-xl border">
            <table className="w-full text-sm">
              <thead className="bg-slate-100 dark:bg-slate-800">
                <tr>
                  <th className="p-3 text-left">ID</th>
                  <th className="p-3 text-left">Name</th>
                  <th className="p-3 text-left">Email</th>
                  <th className="p-3 text-left">Role</th>
                </tr>
              </thead>
              <tbody>
                {users.map((u) => (
                  <tr key={u.id} className="border-t border-slate-200 dark:border-slate-700">
                    <td className="p-3">{u.id}</td>
                    <td className="p-3">{u.name}</td>
                    <td className="p-3">{u.email}</td>
                    <td className="p-3">{u.role}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {tab === 'jobs' && (
          <div className="space-y-4">
            <div className="flex flex-wrap gap-2">
              <button
                type="button"
                onClick={() => setJobFilter('all')}
                className={`px-3 py-1.5 rounded-lg text-sm ${jobFilter === 'all' ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800'}`}
              >
                All jobs ({jobs.length})
              </button>
              <button
                type="button"
                onClick={() => setJobFilter('pending')}
                className={`px-3 py-1.5 rounded-lg text-sm ${jobFilter === 'pending' ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800'}`}
              >
                Pending approval
              </button>
            </div>
            <p className="text-sm text-slate-500">
              New employer posts start as <strong>pending</strong> until you approve them. Use “Refresh data” after posting a job.
            </p>
            {jobs.length === 0 && <p className="text-slate-500">No jobs in this view.</p>}
            <div className="space-y-3">
              {jobs.map((j) => (
                <div key={j.id} className="rounded-xl border p-4 flex flex-wrap justify-between gap-4">
                  <div>
                    <p className="font-semibold">{j.title}</p>
                    <p className="text-sm text-slate-500">{j.company_name}</p>
                    <p className="text-xs mt-1">
                      Status: <span className="font-medium capitalize">{j.status}</span> · ID {j.id}
                    </p>
                  </div>
                  {j.status === 'pending' && (
                    <div className="flex gap-2">
                      <button type="button" className="px-3 py-1 rounded-lg bg-emerald-600 text-white" onClick={() => moderate(j.id, 'approved')}>
                        Approve
                      </button>
                      <button type="button" className="px-3 py-1 rounded-lg bg-red-600 text-white" onClick={() => moderate(j.id, 'rejected')}>
                        Reject
                      </button>
                    </div>
                  )}
                  {j.status === 'approved' && (
                    <button type="button" className="px-3 py-1 rounded-lg border text-sm" onClick={() => moderate(j.id, 'pending')}>
                      Mark pending
                    </button>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {tab === 'payments' && (
          <div className="overflow-x-auto rounded-xl border">
            <table className="w-full text-sm">
              <thead className="bg-slate-100 dark:bg-slate-800">
                <tr>
                  <th className="p-3 text-left">ID</th>
                  <th className="p-3 text-left">User</th>
                  <th className="p-3 text-left">Amount</th>
                  <th className="p-3 text-left">Status</th>
                </tr>
              </thead>
              <tbody>
                {payments.map((p) => (
                  <tr key={p.id} className="border-t border-slate-200 dark:border-slate-700">
                    <td className="p-3">{p.id}</td>
                    <td className="p-3">{p.user_name}</td>
                    <td className="p-3">{p.amount}</td>
                    <td className="p-3">{p.status}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </>
  )
}

import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { useAuth } from '../contexts/AuthContext'

export function Login() {
  const { login } = useAuth()
  const nav = useNavigate()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [err, setErr] = useState('')

  const submit = async (e) => {
    e.preventDefault()
    setErr('')
    try {
      const u = await login(email, password)
      if (u.role === 'admin') nav('/admin')
      else if (u.role === 'employer') nav('/employer')
      else nav('/seeker')
    } catch (ex) {
      setErr(ex.message)
    }
  }

  return (
    <>
      <Helmet>
        <title>Login — Mero Kam</title>
      </Helmet>
      <div className="max-w-md mx-auto px-4 py-16">
        <h1 className="text-2xl font-bold mb-6">Welcome back</h1>
        <form onSubmit={submit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-1">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900"
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3"
            />
          </div>
          {err && <p className="text-sm text-red-600">{err}</p>}
          <button type="submit" className="w-full py-3 rounded-xl bg-brand-600 text-white font-semibold">
            Login
          </button>
        </form>
        <p className="mt-6 text-sm text-slate-600">
          No account? <Link to="/register" className="text-brand-600 font-medium">Register</Link>
        </p>
      </div>
    </>
  )
}

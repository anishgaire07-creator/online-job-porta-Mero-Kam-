import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { useAuth } from '../contexts/AuthContext'

export function Register() {
  const { register } = useAuth()
  const nav = useNavigate()
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [role, setRole] = useState('seeker')
  const [err, setErr] = useState('')

  const submit = async (e) => {
    e.preventDefault()
    setErr('')
    try {
      const u = await register({ name, email, password, role })
      if (u.role === 'employer') nav('/employer')
      else nav('/seeker')
    } catch (ex) {
      setErr(ex.message)
    }
  }

  return (
    <>
      <Helmet>
        <title>Register — Mero Kam</title>
      </Helmet>
      <div className="max-w-md mx-auto px-4 py-16">
        <h1 className="text-2xl font-bold mb-6">Create account</h1>
        <form onSubmit={submit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-1">Full name</label>
            <input
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
              className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3"
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3"
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Password (min 8)</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              minLength={8}
              required
              className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3"
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">I am a</label>
            <select
              value={role}
              onChange={(e) => setRole(e.target.value)}
              className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3"
            >
              <option value="seeker">Job seeker</option>
              <option value="employer">Employer</option>
            </select>
          </div>
          {err && <p className="text-sm text-red-600">{err}</p>}
          <button type="submit" className="w-full py-3 rounded-xl bg-brand-600 text-white font-semibold">
            Register
          </button>
        </form>
        <p className="mt-6 text-sm text-slate-600">
          Already have an account? <Link to="/login" className="text-brand-600 font-medium">Login</Link>
        </p>
      </div>
    </>
  )
}

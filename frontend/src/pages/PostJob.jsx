import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { apiSend } from '../api/client'

export function PostJob() {
  const nav = useNavigate()
  const [err, setErr] = useState('')
  const submit = async (e) => {
    e.preventDefault()
    setErr('')
    const fd = new FormData(e.target)
    const payload = {
      title: fd.get('title'),
      description: fd.get('description'),
      location: fd.get('location'),
      type: fd.get('type'),
      experience_level: fd.get('experience_level'),
      salary_min: fd.get('salary_min') ? Number(fd.get('salary_min')) : null,
      salary_max: fd.get('salary_max') ? Number(fd.get('salary_max')) : null,
      is_featured: fd.get('featured') === 'on',
    }
    try {
      await apiSend('POST', 'post_job.php', payload)
      nav('/employer')
    } catch (ex) {
      setErr(ex.message)
    }
  }

  return (
    <>
      <Helmet>
        <title>Post job — Mero Kam</title>
      </Helmet>
      <div className="max-w-2xl mx-auto px-4 py-10">
        <h1 className="text-2xl font-bold mb-6">Post a new job</h1>
        <form onSubmit={submit} className="space-y-4">
          <input name="title" required placeholder="Job title" className="w-full rounded-xl border px-4 py-3" />
          <textarea name="description" required rows={8} placeholder="Description" className="w-full rounded-xl border px-4 py-3" />
          <input name="location" placeholder="Location" className="w-full rounded-xl border px-4 py-3" />
          <div className="grid sm:grid-cols-2 gap-4">
            <select name="type" className="rounded-xl border px-4 py-3">
              <option value="full-time">Full-time</option>
              <option value="part-time">Part-time</option>
              <option value="contract">Contract</option>
              <option value="internship">Internship</option>
            </select>
            <select name="experience_level" className="rounded-xl border px-4 py-3">
              <option value="entry">Entry</option>
              <option value="mid">Mid</option>
              <option value="senior">Senior</option>
              <option value="lead">Lead</option>
            </select>
          </div>
          <div className="grid sm:grid-cols-2 gap-4">
            <input name="salary_min" type="number" placeholder="Min salary (NPR)" className="rounded-xl border px-4 py-3" />
            <input name="salary_max" type="number" placeholder="Max salary (NPR)" className="rounded-xl border px-4 py-3" />
          </div>
          <label className="flex items-center gap-2 text-sm">
            <input type="checkbox" name="featured" /> Featured listing (uses featured credit)
          </label>
          {err && <p className="text-red-600 text-sm">{err}</p>}
          <button type="submit" className="w-full py-3 rounded-xl bg-brand-600 text-white font-semibold">
            Submit for review
          </button>
        </form>
      </div>
    </>
  )
}

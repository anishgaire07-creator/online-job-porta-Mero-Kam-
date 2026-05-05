import { useEffect, useState } from 'react'
import { Helmet } from 'react-helmet-async'
import { apiGet, apiSend } from '../api/client'

export function Resume() {
  const [data, setData] = useState(null)
  const [form, setForm] = useState({
    summary: '',
    experience: '',
    education: '',
    skills: '',
  })
  const [skillsList, setSkillsList] = useState('')

  useEffect(() => {
    apiGet('resume.php').then((r) => {
      setData(r)
      if (r.resume) {
        setForm({
          summary: r.resume.summary || '',
          experience: r.resume.experience || '',
          education: r.resume.education || '',
          skills: r.resume.skills || '',
        })
      }
      if (r.skills?.length) {
        setSkillsList(r.skills.join(', '))
      }
    })
  }, [])

  const save = async (e) => {
    e.preventDefault()
    const skillsArr = skillsList.split(',').map((s) => s.trim()).filter(Boolean)
    const res = await apiSend('POST', 'resume.php', { ...form, skills_list: skillsArr })
    setData(res)
  }

  return (
    <>
      <Helmet>
        <title>Resume builder — Mero Kam</title>
      </Helmet>
      <div className="max-w-2xl mx-auto px-4 py-10">
        <h1 className="text-2xl font-bold mb-6">Resume builder</h1>
        <form onSubmit={save} className="space-y-4">
          <textarea
            value={form.summary}
            onChange={(e) => setForm({ ...form, summary: e.target.value })}
            rows={4}
            placeholder="Professional summary"
            className="w-full rounded-xl border px-4 py-3"
          />
          <textarea
            value={form.experience}
            onChange={(e) => setForm({ ...form, experience: e.target.value })}
            rows={6}
            placeholder="Work experience"
            className="w-full rounded-xl border px-4 py-3"
          />
          <textarea
            value={form.education}
            onChange={(e) => setForm({ ...form, education: e.target.value })}
            rows={4}
            placeholder="Education"
            className="w-full rounded-xl border px-4 py-3"
          />
          <textarea
            value={form.skills}
            onChange={(e) => setForm({ ...form, skills: e.target.value })}
            rows={3}
            placeholder="Skills (free text)"
            className="w-full rounded-xl border px-4 py-3"
          />
          <input
            value={skillsList}
            onChange={(e) => setSkillsList(e.target.value)}
            placeholder="Structured skills: PHP, React, MySQL (comma-separated)"
            className="w-full rounded-xl border px-4 py-3"
          />
          <button type="submit" className="px-6 py-3 rounded-xl bg-brand-600 text-white">
            Save resume
          </button>
        </form>
        {data?.skills?.length > 0 && (
          <p className="mt-4 text-sm text-slate-600">Saved skills: {data.skills.join(', ')}</p>
        )}
      </div>
    </>
  )
}

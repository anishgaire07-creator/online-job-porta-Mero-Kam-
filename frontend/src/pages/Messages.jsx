import { useEffect, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { apiGet, apiSend } from '../api/client'
import { useAuth } from '../contexts/AuthContext'

export function Messages() {
  const { user } = useAuth()
  const [params] = useSearchParams()
  const toUser = params.get('to')
  const jobId = params.get('job')
  const [inbox, setInbox] = useState(null)
  const [thread, setThread] = useState(null)
  const [other, setOther] = useState(toUser ? Number(toUser) : null)
  const [body, setBody] = useState('')

  const loadInbox = () => apiGet('messages.php').then(setInbox)

  useEffect(() => {
    loadInbox()
  }, [])

  useEffect(() => {
    if (toUser) {
      setOther(Number(toUser))
    }
  }, [toUser])

  useEffect(() => {
    if (other) {
      apiGet('messages.php', { with: other }).then(setThread)
    }
  }, [other])

  const send = async (e) => {
    e.preventDefault()
    await apiSend('POST', 'messages.php', {
      to_user_id: other,
      body,
      job_id: jobId ? Number(jobId) : undefined,
    })
    setBody('')
    apiGet('messages.php', { with: other }).then(setThread)
    loadInbox()
  }

  return (
    <>
      <Helmet>
        <title>Messages — Mero Kam</title>
      </Helmet>
      <div className="max-w-6xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-6">
        <div className="rounded-2xl border p-4 max-h-[70vh] overflow-y-auto">
          <h2 className="font-semibold mb-4">Inbox</h2>
          <p className="text-xs text-slate-500 mb-2">Unread: {inbox?.unread ?? 0}</p>
          <ul className="space-y-2 text-sm">
            {inbox?.inbox?.slice(0, 50).map((m) => {
              const otherId = user && m.from_user_id === user.id ? m.to_user_id : m.from_user_id
              return (
                <li key={m.id}>
                  <button
                    type="button"
                    className="text-left w-full hover:bg-slate-100 dark:hover:bg-slate-800 rounded p-2"
                    onClick={() => setOther(otherId)}
                  >
                    <span className="text-xs text-slate-500">{m.other_name}</span>
                    <br />
                    {m.body.slice(0, 80)}
                    {m.body.length > 80 ? '…' : ''}
                  </button>
                </li>
              )
            })}
          </ul>
        </div>
        <div className="md:col-span-2 rounded-2xl border p-4 flex flex-col min-h-[400px]">
          {other ? (
            <>
              <div className="flex-1 overflow-y-auto space-y-2 mb-4">
                {(thread?.messages || []).map((m) => (
                  <div key={m.id} className={`p-2 rounded-lg ${user && m.from_user_id === user.id ? 'bg-brand-50 ml-8' : 'bg-slate-100 mr-8'}`}>
                    <p className="text-xs text-slate-500">{m.from_name}</p>
                    <p>{m.body}</p>
                  </div>
                ))}
              </div>
              <form onSubmit={send} className="flex gap-2">
                <input value={body} onChange={(e) => setBody(e.target.value)} className="flex-1 rounded-xl border px-4 py-2" placeholder="Type a message..." />
                <button type="submit" className="px-4 py-2 rounded-xl bg-brand-600 text-white">
                  Send
                </button>
              </form>
            </>
          ) : (
            <p className="text-slate-500">Select a thread or open from a job page.</p>
          )}
        </div>
      </div>
    </>
  )
}

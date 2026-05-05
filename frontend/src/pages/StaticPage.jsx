import { Helmet } from 'react-helmet-async'

export function StaticPage({ title }) {
  return (
    <>
      <Helmet>
        <title>{title} — Mero Kam</title>
      </Helmet>
      <div className="max-w-3xl mx-auto px-4 py-16 space-y-4 text-slate-700 dark:text-slate-300">
        <h1>{title}</h1>
        <p>
        Mero Kam is a modern job portal designed to connect job seekers with employers in a simple, fast, and efficient way. The platform aims to reduce unemployment by providing a centralized place where users can find jobs, post vacancies, and manage applications easily.
        </p>
        <p>For support, contact MK@merokam.com</p>
      </div>
    </>
  )
}

import { Link, NavLink, useNavigate } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import { useTheme } from '../contexts/ThemeContext'
import { useI18n } from '../contexts/I18nContext'

export function Layout({ children }) {
  const { user, loading, logout } = useAuth()
  const { dark, toggle } = useTheme()
  const { t, lang, setLanguage } = useI18n()
  const nav = useNavigate()

  return (
    <div className="min-h-screen flex flex-col">
      <header className="sticky top-0 z-50 glass border-b border-slate-200/80 dark:border-slate-800">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
          <Link to="/" className="flex items-center gap-2 font-bold text-xl text-brand-700 dark:text-brand-400">
            <span className="w-9 h-9 rounded-lg bg-brand-600 text-white flex items-center justify-center text-sm font-black">MK</span>
            Mero Kam
          </Link>
          <nav className="flex items-center gap-3 md:gap-6 text-xs md:text-sm font-medium">
            <NavLink to="/" className={({ isActive }) => (isActive ? 'text-brand-600' : 'text-slate-600 dark:text-slate-300 hover:text-brand-600')}>
              {t('nav.home')}
            </NavLink>
            <NavLink to="/jobs" className={({ isActive }) => (isActive ? 'text-brand-600' : 'text-slate-600 dark:text-slate-300 hover:text-brand-600')}>
              {t('nav.jobs')}
            </NavLink>
            <NavLink to="/pricing" className={({ isActive }) => (isActive ? 'text-brand-600' : 'text-slate-600 dark:text-slate-300 hover:text-brand-600')}>
              {t('nav.pricing')}
            </NavLink>
            <span className="hidden sm:inline text-slate-300">|</span>
          </nav>
          <div className="flex items-center gap-2">
            <select
              value={lang}
              onChange={(e) => setLanguage(e.target.value)}
              className="text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1"
            >
              <option value="en">English</option>
              <option value="ne">नेपाली</option>
            </select>
            <button
              type="button"
              onClick={toggle}
              className="p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800"
              aria-label="Toggle dark mode"
            >
              {dark ? '☀️' : '🌙'}
            </button>
            {!loading && user === null && (
              <>
                <Link to="/login" className="hidden sm:inline px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                  {t('nav.login')}
                </Link>
                <Link to="/register" className="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold shadow hover:bg-brand-700">
                  {t('nav.register')}
                </Link>
              </>
            )}
            {loading && <span className="text-xs text-slate-400 px-2">…</span>}
            {user && (
              <>
                {user.role === 'admin' && (
                  <Link to="/admin" className="text-sm text-amber-600 font-semibold">
                    {t('nav.admin')}
                  </Link>
                )}
                <Link
                  to={user.role === 'seeker' ? '/seeker' : user.role === 'employer' ? '/employer' : '/admin'}
                  className="text-sm font-medium text-brand-700 dark:text-brand-400"
                >
                  {t('nav.dashboard')}
                </Link>
                <button
                  type="button"
                  onClick={async () => {
                    await logout()
                    nav('/')
                  }}
                  className="text-sm text-slate-500"
                >
                  {t('nav.logout')}
                </button>
              </>
            )}
          </div>
        </div>
      </header>
      <main className="flex-1">{children}</main>
      <footer className="border-t border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50">
        <div className="max-w-7xl mx-auto px-4 py-10 grid sm:grid-cols-3 gap-8 text-sm">
          <div>
            <p className="font-bold text-brand-700 dark:text-brand-400 mb-2">Mero Kam</p>
            <p className="text-slate-600 dark:text-slate-400">Nepal&apos;s job platform — built for seekers and employers.</p>
          </div>
          <div className="flex flex-col gap-2">
            <Link to="/about" className="hover:text-brand-600">
              {t('footer.about')}
            </Link>
            <Link to="/faq" className="hover:text-brand-600">
              {t('footer.faq')}
            </Link>
            <Link to="/privacy" className="hover:text-brand-600">
              {t('footer.privacy')}
            </Link>
          </div>
          <div>
            <p className="text-slate-500">{t('footer.contact')}: hello@merokam.local</p>
            <p className="mt-4 text-slate-400">© {new Date().getFullYear()} Mero Kam. {t('footer.rights')}</p>
          </div>
        </div>
      </footer>
    </div>
  )
}

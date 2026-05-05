import { createContext, useContext, useMemo, useState, useCallback } from 'react'
import en from '../locales/en.json'
import ne from '../locales/ne.json'

const dict = { en, ne }

const I18nContext = createContext(null)

export function I18nProvider({ children }) {
  const [lang, setLang] = useState(() => localStorage.getItem('mero-kam-lang') || 'en')

  const t = useCallback(
    (key) => {
      const k = key.split('.')
      let o = dict[lang] || en
      for (const part of k) {
        o = o?.[part]
      }
      if (typeof o === 'string') return o
      let fallback = en
      for (const part of k) {
        fallback = fallback?.[part]
      }
      return typeof fallback === 'string' ? fallback : key
    },
    [lang]
  )

  const setLanguage = useCallback((l) => {
    setLang(l)
    localStorage.setItem('mero-kam-lang', l)
    document.documentElement.lang = l
  }, [])

  const value = useMemo(() => ({ lang, t, setLanguage }), [lang, t, setLanguage])

  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>
}

export function useI18n() {
  const ctx = useContext(I18nContext)
  if (!ctx) throw new Error('useI18n outside provider')
  return ctx
}

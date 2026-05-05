import { createContext, useContext, useEffect, useState, useCallback, useRef } from 'react'
import { apiGet, apiSend } from '../api/client'

const AuthContext = createContext(null)

/**
 * Ignore stale /me responses so a slow initial session check cannot overwrite
 * a user who just logged in (fixes infinite spinner / lost employer session).
 */
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)
  const sessionEpoch = useRef(0)

  const refresh = useCallback(async () => {
    const id = ++sessionEpoch.current
    try {
      const data = await apiGet('me.php')
      if (id !== sessionEpoch.current) return
      setUser(data.user || null)
    } catch {
      if (id !== sessionEpoch.current) return
      setUser(null)
    } finally {
      // Always unblock UI; login/register may have bumped sessionEpoch while this was in flight.
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    refresh()
  }, [refresh])

  const login = async (email, password) => {
    sessionEpoch.current += 1
    const cred = await apiSend('POST', 'login.php', { email, password })
    const id = ++sessionEpoch.current
    setLoading(true)
    let nextUser = cred.user
    try {
      const me = await apiGet('me.php')
      if (id !== sessionEpoch.current) return cred.user
      nextUser = me.user || cred.user
      setUser(nextUser)
    } catch {
      if (id === sessionEpoch.current) setUser(cred.user)
    } finally {
      if (id === sessionEpoch.current) setLoading(false)
    }
    return nextUser
  }

  const register = async (payload) => {
    sessionEpoch.current += 1
    const cred = await apiSend('POST', 'register.php', payload)
    const id = ++sessionEpoch.current
    setLoading(true)
    let nextUser = cred.user
    try {
      const me = await apiGet('me.php')
      if (id !== sessionEpoch.current) return cred.user
      nextUser = me.user || cred.user
      setUser(nextUser)
    } catch {
      if (id === sessionEpoch.current) setUser(cred.user)
    } finally {
      if (id === sessionEpoch.current) setLoading(false)
    }
    return nextUser
  }

  const logout = async () => {
    sessionEpoch.current += 1
    await apiSend('POST', 'logout.php')
    setUser(null)
    setLoading(false)
  }

  return (
    <AuthContext.Provider value={{ user, loading, refresh, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth outside provider')
  return ctx
}

import { Routes, Route } from 'react-router-dom'
import { Layout } from './components/Layout'
import { ProtectedRoute } from './components/ProtectedRoute'
import { Home } from './pages/Home'
import { Jobs } from './pages/Jobs'
import { JobDetail } from './pages/JobDetail'
import { Login } from './pages/Login'
import { Register } from './pages/Register'
import { SeekerDashboard } from './pages/SeekerDashboard'
import { EmployerDashboard } from './pages/EmployerDashboard'
import { AdminDashboard } from './pages/AdminDashboard'
import { PostJob } from './pages/PostJob'
import { CompanySettings } from './pages/CompanySettings'
import { Messages } from './pages/Messages'
import { Resume } from './pages/Resume'
import { Pricing } from './pages/Pricing'
import { StaticPage } from './pages/StaticPage'
import { EmployerApplicants } from './pages/EmployerApplicants'

export default function App() {
  return (
    <Layout>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/jobs" element={<Jobs />} />
        <Route path="/jobs/:id" element={<JobDetail />} />
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/pricing" element={<Pricing />} />
        <Route path="/about" element={<StaticPage title="About Mero Kam" />} />
        <Route path="/faq" element={<StaticPage title="FAQ" />} />
        <Route path="/privacy" element={<StaticPage title="Privacy Policy" />} />
        <Route
          path="/seeker"
          element={
            <ProtectedRoute roles={['seeker']}>
              <SeekerDashboard />
            </ProtectedRoute>
          }
        />
        <Route
          path="/employer"
          element={
            <ProtectedRoute roles={['employer']}>
              <EmployerDashboard />
            </ProtectedRoute>
          }
        />
        <Route
          path="/admin"
          element={
            <ProtectedRoute roles={['admin']}>
              <AdminDashboard />
            </ProtectedRoute>
          }
        />
        <Route
          path="/post-job"
          element={
            <ProtectedRoute roles={['employer']}>
              <PostJob />
            </ProtectedRoute>
          }
        />
        <Route
          path="/company"
          element={
            <ProtectedRoute roles={['employer']}>
              <CompanySettings />
            </ProtectedRoute>
          }
        />
        <Route
          path="/employer/applicants"
          element={
            <ProtectedRoute roles={['employer']}>
              <EmployerApplicants />
            </ProtectedRoute>
          }
        />
        <Route
          path="/messages"
          element={
            <ProtectedRoute roles={['seeker', 'employer']}>
              <Messages />
            </ProtectedRoute>
          }
        />
        <Route
          path="/resume"
          element={
            <ProtectedRoute roles={['seeker']}>
              <Resume />
            </ProtectedRoute>
          }
        />
      </Routes>
    </Layout>
  )
}

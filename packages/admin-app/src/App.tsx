import { Routes, Route, Navigate } from 'react-router-dom'
import { SignedIn, SignedOut } from '@clerk/clerk-react'
import Home from './pages/Home'
import Login from './pages/Login'
import ClerkLogin from './pages/ClerkLogin'
import MembersPage from './pages/Members'
import MessagesPage from './pages/Messages'
import WhitelistGuard from './components/WhitelistGuard'
import Forbidden from './pages/Forbidden'
import ArchivePage from './pages/Archive'

export default function App() {
    return (
        <Routes>
            {/* Root */}
            <Route
                path="/"
                element={
                    <>
                        <SignedOut>
                            <Navigate to="/login" replace />
                        </SignedOut>
                        <SignedIn>
                            <WhitelistGuard />
                        </SignedIn>
                    </>
                }
            >
                <Route index element={<Home />} />
                <Route path="members" element={<MembersPage />} />
                <Route path="messages" element={<MessagesPage />} />
                <Route path="archive" element={<ArchivePage />} />
            </Route>

            {/* Public auth routes */}
            <Route
                path="/login"
                element={
                    <>
                        <SignedOut>
                            <Login />
                        </SignedOut>
                        <SignedIn>
                            <Navigate to="/" replace />
                        </SignedIn>
                    </>
                }
            />

            <Route
                path="/login/clerk"
                element={
                    <>
                        <SignedOut>
                            <ClerkLogin />
                        </SignedOut>
                        <SignedIn>
                            <Navigate to="/" replace />
                        </SignedIn>
                    </>
                }
            />

            {/* Error */}
            <Route path="/403" element={<Forbidden />} />

            {/* Catch-all */}
            <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
    )
}

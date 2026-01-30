import { Routes, Route, Navigate } from 'react-router-dom'
import { SignedIn, SignedOut } from '@clerk/clerk-react'
import Home from './pages/Home'
import Login from './pages/Login'
import ClerkLogin from './pages/ClerkLogin'
import MembersPage from './pages/Members'
import MessagesPage from './pages/Messages'
import WhitelistGuard from './components/WhitelistGuard'
import Forbidden from './pages/Forbidden'

export default function App() {
    return (
        <Routes>
            {/* Public routes */}
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

            {/* Protected routes */}
            <Route
                element={
                    <SignedIn>
                        <WhitelistGuard />
                    </SignedIn>
                }
            >
                <Route path="/" element={<Home />} />
                <Route path="/members" element={<MembersPage />} />
                <Route path="/messages" element={<MessagesPage />} />
            </Route>

            {/* Error routes */}
            <Route path="/403" element={<Forbidden />} />
        </Routes>
    )
}

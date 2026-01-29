import { Routes, Route, Navigate } from 'react-router-dom'
import { SignedIn, SignedOut } from '@clerk/clerk-react'
import Home from './pages/Home'
import Login from './pages/Login'
import ClerkLogin from './pages/ClerkLogin'
import MembersPage from './pages/Members'
import MessagesPage from './pages/Messages'

export default function App() {
    return (
        <Routes>
            <Route
                path="/"
                element={
                    <>
                        <SignedIn>
                            <Home />
                        </SignedIn>
                        <SignedOut>
                            <Navigate to="/login" replace />
                        </SignedOut>
                    </>
                }
            />

            {/* Landing */}
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

            {/* Actual Clerk sign-in */}
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

            <Route path="/members" element={<MembersPage />} />
            <Route path="/messages" element={<MessagesPage />} />
        </Routes>
    )
}

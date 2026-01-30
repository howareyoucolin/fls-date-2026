import { useAuth, useUser } from '@clerk/clerk-react'
import { useEffect, useState } from 'react'
import { Navigate, Outlet } from 'react-router-dom'

export default function WhitelistGuard() {
    const { getToken } = useAuth()
    const { user, isLoaded } = useUser()
    const [allowed, setAllowed] = useState<boolean | null>(null)

    useEffect(() => {
        if (!isLoaded) return

        // If not signed in, treat as not allowed (or redirect to login if you want)
        if (!user) {
            setAllowed(false)
            return
        }

        let cancelled = false
        const controller = new AbortController()

        const run = async () => {
            try {
                setAllowed(null)

                const token = await getToken()
                if (!token) throw new Error('No token returned (not signed in?)')

                const email = user.primaryEmailAddress?.emailAddress || ''
                if (!email) throw new Error('No primary email on user')

                const res = await fetch('/api/whitelist', {
                    method: 'GET',
                    headers: {
                        'x-user-email': email,
                        Authorization: `Bearer ${token}`,
                    },
                    signal: controller.signal,
                })

                if (res.status === 403) {
                    if (!cancelled) setAllowed(false)
                    return
                }

                // If your API uses api_ok/api_error, it should still be JSON,
                // but keep this safe:
                const data = await res.json().catch(() => null)

                if (!cancelled) {
                    setAllowed(Boolean(data?.data?.allowed))
                }
            } catch {
                if (!cancelled) setAllowed(false)
            }
        }

        run()

        return () => {
            cancelled = true
            controller.abort()
        }
    }, [isLoaded, user, getToken])

    if (!isLoaded || allowed === null) {
        return <div>Checking access...</div>
    }

    if (!allowed) {
        return <Navigate to="/403" replace />
    }

    return <Outlet />
}

import { useAuth, useUser } from '@clerk/clerk-react'
import { useEffect, useState } from 'react'
import { Navigate, Outlet } from 'react-router-dom'

export default function WhitelistGuard() {
    const { getToken } = useAuth()
    const { user, isLoaded } = useUser()
    const [allowed, setAllowed] = useState<boolean | null>(null)

    useEffect(() => {
        if (!isLoaded) return

        // Not signed in → not allowed
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

                const res = await fetch('/api/whitelist', {
                    method: 'GET',
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                    signal: controller.signal,
                })

                if (res.status === 403) {
                    if (!cancelled) setAllowed(false)
                    return
                }

                const data = await res.json().catch(() => null)

                if (!cancelled) {
                    // api_ok wraps payload under data
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

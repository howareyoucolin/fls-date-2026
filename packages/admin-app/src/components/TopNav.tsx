import { SignOutButton, useUser } from '@clerk/clerk-react'
import { useEffect, useMemo, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'

const DESKTOP_BREAKPOINT = 860
const DESKTOP_SIDEBAR_W = 280
const CONTENT_MAX_W = 680

function useIsMobile(breakpointPx = DESKTOP_BREAKPOINT) {
    const [isMobile, setIsMobile] = useState(() => window.innerWidth < breakpointPx)

    useEffect(() => {
        const onResize = () => setIsMobile(window.innerWidth < breakpointPx)
        window.addEventListener('resize', onResize)
        return () => window.removeEventListener('resize', onResize)
    }, [breakpointPx])

    return isMobile
}

export default function TopNav() {
    const { user } = useUser()
    const location = useLocation()
    const isMobile = useIsMobile(DESKTOP_BREAKPOINT)

    // Desktop open by default, mobile closed
    const [open, setOpen] = useState(false)

    useEffect(() => {
        setTimeout(() => {
            setOpen(!isMobile)
        }, 100)
    }, [isMobile])

    const email = useMemo(
        () => user?.primaryEmailAddress?.emailAddress || user?.emailAddresses?.[0]?.emailAddress || 'Unknown user',
        [user]
    )

    // ESC to close (mobile only)
    useEffect(() => {
        if (!open || !isMobile) return
        const onKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Escape') setOpen(false)
        }
        window.addEventListener('keydown', onKeyDown)
        return () => window.removeEventListener('keydown', onKeyDown)
    }, [open, isMobile])

    // Lock scroll on mobile drawer
    useEffect(() => {
        if (!open || !isMobile) return
        const prev = document.body.style.overflow
        document.body.style.overflow = 'hidden'
        return () => {
            document.body.style.overflow = prev
        }
    }, [open, isMobile])

    const isActive = (path: string) => location.pathname === path

    const navItemStyle = (active: boolean): React.CSSProperties => ({
        display: 'flex',
        alignItems: 'center',
        gap: 10,
        padding: '10px 12px',
        borderRadius: 12,
        textDecoration: 'none',
        color: active ? '#fff' : 'rgba(255,255,255,0.86)',
        background: active ? 'rgba(124,58,237,0.20)' : 'rgba(255,255,255,0.04)',
        border: '1px solid rgba(255,255,255,0.08)',
        fontWeight: 800,
        fontSize: 14,
    })

    const Hamburger = (
        <span style={{ display: 'grid', gap: 4 }}>
            <span style={{ width: 16, height: 2, background: 'rgba(255,255,255,0.92)', borderRadius: 2 }} />
            <span style={{ width: 16, height: 2, background: 'rgba(255,255,255,0.92)', borderRadius: 2 }} />
            <span style={{ width: 16, height: 2, background: 'rgba(255,255,255,0.92)', borderRadius: 2 }} />
        </span>
    )

    const SidebarContent = (
        <>
            {/* Header */}
            <div
                style={{
                    padding: 14,
                    borderBottom: '1px solid rgba(255,255,255,0.08)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    gap: 10,
                }}
            >
                <div style={{ color: '#fff', fontWeight: 900, letterSpacing: '-0.02em' }}>Menu</div>

                {isMobile && (
                    <button
                        onClick={() => setOpen(false)}
                        style={{
                            height: 34,
                            padding: '0 12px',
                            borderRadius: 10,
                            border: '1px solid rgba(255,255,255,0.12)',
                            background: 'rgba(255,255,255,0.04)',
                            color: 'rgba(255,255,255,0.92)',
                            fontWeight: 900,
                            cursor: 'pointer',
                        }}
                    >
                        ← Back
                    </button>
                )}
            </div>

            {/* Content */}
            <div style={{ padding: 14, display: 'flex', flexDirection: 'column', gap: 12 }}>
                <div
                    style={{
                        color: 'rgba(255,255,255,0.80)',
                        fontSize: 13,
                        padding: '10px 12px',
                        borderRadius: 12,
                        border: '1px solid rgba(255,255,255,0.08)',
                        background: 'rgba(255,255,255,0.04)',
                    }}
                >
                    You&apos;re logged in <span style={{ color: '#fff', fontWeight: 900 }}>{email}</span>
                </div>

                <Link to="/" onClick={() => isMobile && setOpen(false)} style={navItemStyle(isActive('/'))}>
                    Home
                </Link>

                <Link to="/messages" onClick={() => isMobile && setOpen(false)} style={navItemStyle(isActive('/messages'))}>
                    Messages
                </Link>

                <Link to="/members" onClick={() => isMobile && setOpen(false)} style={navItemStyle(isActive('/members'))}>
                    Members
                </Link>

                <div style={{ height: 6 }} />

                <SignOutButton>
                    <button
                        onClick={() => isMobile && setOpen(false)}
                        style={{
                            height: 40,
                            padding: '0 14px',
                            borderRadius: 12,
                            border: '1px solid rgba(255,255,255,0.12)',
                            background: 'linear-gradient(135deg, #ef4444 0%, #b91c1c 100%)',
                            color: '#fff',
                            fontSize: 14,
                            fontWeight: 900,
                            cursor: 'pointer',
                            textAlign: 'left',
                        }}
                    >
                        Logout
                    </button>
                </SignOutButton>
            </div>

            <div style={{ flex: 1 }} />

            <div
                style={{
                    padding: 14,
                    borderTop: '1px solid rgba(255,255,255,0.08)',
                    color: 'rgba(255,255,255,0.55)',
                    fontSize: 12,
                }}
            >
                Flushing Dating Admin
            </div>
        </>
    )

    return (
        <>
            {/* Top Nav */}
            <div
                style={{
                    position: 'sticky',
                    top: 0,
                    zIndex: 10,
                    backdropFilter: 'blur(12px)',
                    background: 'rgba(11, 11, 16, 0.78)',
                    borderBottom: '1px solid rgba(255,255,255,0.08)',
                }}
            >
                {/* Desktop offset wrapper */}
                <div style={{ paddingLeft: isMobile ? 0 : DESKTOP_SIDEBAR_W }}>
                    <div
                        style={{
                            maxWidth: CONTENT_MAX_W,
                            margin: '0 auto',
                            padding: '12px var(--app-pad)',
                        }}
                    >
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12 }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10, minWidth: 0 }}>
                                {isMobile && (
                                    <button
                                        onClick={() => setOpen(true)}
                                        aria-label="Open menu"
                                        style={{
                                            width: 34,
                                            height: 34,
                                            borderRadius: 10,
                                            border: '1px solid rgba(255,255,255,0.12)',
                                            background: 'rgba(255,255,255,0.04)',
                                            cursor: 'pointer',
                                            display: 'grid',
                                            placeItems: 'center',
                                            flex: '0 0 auto',
                                        }}
                                    >
                                        {Hamburger}
                                    </button>
                                )}

                                <div
                                    style={{
                                        fontWeight: 900,
                                        letterSpacing: '-0.02em',
                                        color: '#fff',
                                        fontSize: 16,
                                        lineHeight: 1.1,
                                        whiteSpace: 'nowrap',
                                        overflow: 'hidden',
                                        textOverflow: 'ellipsis',
                                    }}
                                >
                                    Flushing Dating Admin
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Desktop Sidebar */}
            {!isMobile && (
                <div
                    style={{
                        position: 'fixed',
                        top: 0,
                        left: 0,
                        height: '100vh',
                        width: DESKTOP_SIDEBAR_W,
                        background: 'rgba(11, 11, 16, 0.95)',
                        borderRight: '1px solid rgba(255,255,255,0.10)',
                        backdropFilter: 'blur(14px)',
                        zIndex: 5,
                    }}
                >
                    {SidebarContent}
                </div>
            )}

            {/* Mobile Drawer */}
            {isMobile && (
                <div
                    aria-hidden={!open}
                    style={{
                        position: 'fixed',
                        inset: 0,
                        zIndex: 50,
                        pointerEvents: open ? 'auto' : 'none',
                    }}
                >
                    <div
                        onClick={() => setOpen(false)}
                        style={{
                            position: 'absolute',
                            inset: 0,
                            background: 'rgba(0,0,0,0.55)',
                            opacity: open ? 1 : 0,
                            transition: 'opacity 200ms ease',
                        }}
                    />

                    <aside
                        style={{
                            position: 'absolute',
                            top: 0,
                            left: 0,
                            height: '100%',
                            width: 'min(320px, 86vw)',
                            background: 'rgba(11, 11, 16, 0.95)',
                            borderRight: '1px solid rgba(255,255,255,0.10)',
                            backdropFilter: 'blur(14px)',
                            transform: open ? 'translateX(0)' : 'translateX(-102%)',
                            transition: 'transform 220ms ease',
                            display: 'flex',
                            flexDirection: 'column',
                        }}
                    >
                        {SidebarContent}
                    </aside>
                </div>
            )}
        </>
    )
}

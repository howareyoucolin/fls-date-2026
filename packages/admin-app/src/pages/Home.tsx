import { useAuth } from '@clerk/clerk-react'
import { useEffect, useMemo, useState } from 'react'
import TopNav from '../components/TopNav'

type MessageCountsResp =
    | { success: true; message: string; data: { unread: number; total: number } }
    | { success: false; error: { code: string; message: string; details?: unknown } }

type MemberCountsResp =
    | { success: true; message: string; data: { total: number; active: number; inactive: number } }
    | { success: false; error: { code: string; message: string; details?: unknown } }

function useMedia(query: string) {
    const [matches, setMatches] = useState<boolean>(() =>
        typeof window !== 'undefined' ? window.matchMedia(query).matches : false
    )

    useEffect(() => {
        if (typeof window === 'undefined') return
        const mql = window.matchMedia(query)
        const onChange = (e: MediaQueryListEvent) => setMatches(e.matches)
        mql.addEventListener('change', onChange)
        return () => mql.removeEventListener('change', onChange)
    }, [query])

    return matches
}

const DESKTOP_BREAKPOINT = 860
const DESKTOP_SIDEBAR_W = 280
const CONTENT_MAX_W = 680

export default function Home() {
    const { getToken } = useAuth()

    const [unreadMessages, setUnreadMessages] = useState<number | null>(null)
    const [totalMessages, setTotalMessages] = useState<number | null>(null)

    const [totalMembers, setTotalMembers] = useState<number | null>(null)
    const [activeMembers, setActiveMembers] = useState<number | null>(null)
    const [inactiveMembers, setInactiveMembers] = useState<number | null>(null)

    const [loading, setLoading] = useState(true)

    const isDesktop = useMedia(`(min-width: ${DESKTOP_BREAKPOINT}px)`)
    const isNarrow = useMedia('(max-width: 640px)')
    const isTiny = useMedia('(max-width: 380px)')

    useEffect(() => {
        let cancelled = false

        ;(async () => {
            try {
                setLoading(true)

                const token = await getToken()
                if (!token) throw new Error('No token returned (not signed in?)')

                const headers: HeadersInit = { Authorization: `Bearer ${token}` }

                const [msgRes, memRes] = await Promise.all([
                    fetch('/api/message_counts', { method: 'GET', headers }),
                    fetch('/api/member_counts', { method: 'GET', headers }),
                ])

                const [msgJson, memJson] = await Promise.all([
                    msgRes.json() as Promise<MessageCountsResp>,
                    memRes.json() as Promise<MemberCountsResp>,
                ])

                if (cancelled) return

                // Messages
                if (!msgRes.ok || msgJson.success === false) {
                    setUnreadMessages(null)
                    setTotalMessages(null)
                } else {
                    setUnreadMessages(msgJson.data.unread)
                    setTotalMessages(msgJson.data.total)
                }

                // Members
                if (!memRes.ok || memJson.success === false) {
                    setTotalMembers(null)
                    setActiveMembers(null)
                    setInactiveMembers(null)
                } else {
                    setTotalMembers(memJson.data.total)
                    setActiveMembers(memJson.data.active)
                    setInactiveMembers(memJson.data.inactive)
                }
            } catch {
                if (!cancelled) {
                    setUnreadMessages(null)
                    setTotalMessages(null)
                    setTotalMembers(null)
                    setActiveMembers(null)
                    setInactiveMembers(null)
                }
            } finally {
                if (!cancelled) setLoading(false)
            }
        })()

        return () => {
            cancelled = true
        }
    }, [getToken])

    const styles = useMemo(
        () => ({
            page: {
                minHeight: '100vh',
                background:
                    'radial-gradient(900px circle at 25% 15%, rgba(124,58,237,0.18), transparent 55%),' +
                    'radial-gradient(800px circle at 80% 75%, rgba(79,70,229,0.14), transparent 60%),' +
                    'linear-gradient(180deg, #0b0b10 0%, #0f172a 100%)',
            } as React.CSSProperties,

            contentWrap: {
                paddingLeft: isDesktop ? DESKTOP_SIDEBAR_W : 0,
            } as React.CSSProperties,

            main: {
                minHeight: 'calc(100vh - 56px)',
                padding: 'clamp(14px, 3vw, 24px)',
            } as React.CSSProperties,

            container: {
                maxWidth: CONTENT_MAX_W,
                margin: '0 auto',
            } as React.CSSProperties,

            card: {
                width: '100%',
                padding: 'clamp(18px, 3.5vw, 26px)',
                borderRadius: 18,
                background: 'rgba(255,255,255,0.06)',
                border: '1px solid rgba(255,255,255,0.12)',
                boxShadow: '0 22px 70px rgba(0,0,0,0.55)',
                backdropFilter: 'blur(10px)',
                textAlign: 'left',
                color: '#fff',
            } as React.CSSProperties,

            headerRow: {
                display: 'flex',
                alignItems: 'baseline',
                justifyContent: 'space-between',
                gap: 12,
            } as React.CSSProperties,

            h1: {
                fontSize: 'clamp(22px, 4vw, 28px)',
                margin: 0,
                letterSpacing: 0.2,
            } as React.CSSProperties,

            sub: {
                fontSize: 13,
                color: 'rgba(255,255,255,0.55)',
                whiteSpace: 'nowrap',
            } as React.CSSProperties,

            divider: {
                height: 1,
                background: 'rgba(255,255,255,0.10)',
                margin: '16px 0',
            } as React.CSSProperties,

            statsGrid: {
                display: 'grid',
                gridTemplateColumns: isTiny ? '1fr' : isNarrow ? 'repeat(2, minmax(0, 1fr))' : 'repeat(3, minmax(0, 1fr))',
                gap: 12,
                marginTop: 14,
            } as React.CSSProperties,

            statCard: {
                padding: 'clamp(10px, 2vw, 14px) clamp(14px, 3vw, 18px)',
                borderRadius: 12,
                background: 'rgba(255,255,255,0.04)',
                border: '1px solid rgba(255,255,255,0.08)',
                minWidth: 0,
            } as React.CSSProperties,

            statLabel: {
                fontSize: 12,
                color: 'rgba(255,255,255,0.65)',
                letterSpacing: 0.6,
                textTransform: 'uppercase',
                display: 'block',
                marginBottom: 6,
            } as React.CSSProperties,

            statValue: {
                fontSize: 22,
                fontWeight: 650,
                color: '#fff',
                overflowWrap: 'anywhere',
            } as React.CSSProperties,

            sectionTitle: {
                fontSize: 16,
                color: 'rgba(255,255,255,0.75)',
                margin: '0 0 10px 0',
                letterSpacing: 0.3,
            } as React.CSSProperties,

            links: {
                display: 'grid',
                gap: 10,
            } as React.CSSProperties,

            link: {
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                gap: 12,
                padding: '12px 14px',
                borderRadius: 12,
                textDecoration: 'none',
                color: '#fff',
                background: 'rgba(255,255,255,0.04)',
                border: '1px solid rgba(255,255,255,0.08)',
            } as React.CSSProperties,

            linkHint: {
                fontSize: 12,
                color: 'rgba(255,255,255,0.55)',
            } as React.CSSProperties,
        }),
        [isDesktop, isNarrow, isTiny]
    )

    const unreadText = loading ? 'Loading…' : unreadMessages === null ? '—' : String(unreadMessages)
    const totalMsgText = loading ? 'Loading…' : totalMessages === null ? '—' : String(totalMessages)

    const totalMemberText = loading ? 'Loading…' : totalMembers === null ? '—' : String(totalMembers)
    const activeMemberText = loading ? 'Loading…' : activeMembers === null ? '—' : String(activeMembers)
    const inactiveMemberText = loading ? 'Loading…' : inactiveMembers === null ? '—' : String(inactiveMembers)

    return (
        <div style={styles.page}>
            <TopNav />

            <div style={styles.contentWrap}>
                <div style={styles.main}>
                    <div style={styles.container}>
                        <div style={styles.card}>
                            <div style={styles.headerRow}>
                                <h1 style={styles.h1}>Dashboard</h1>
                                <span style={styles.sub}>Admin overview</span>
                            </div>

                            <div style={styles.statsGrid}>
                                <Stat label="Unread messages" value={`${unreadText} / ${totalMsgText}`} styles={styles} />
                                <Stat label="Total members" value={totalMemberText} styles={styles} />
                                <Stat
                                    label="Active / Inactive"
                                    value={`${activeMemberText} / ${inactiveMemberText}`}
                                    styles={styles}
                                />
                            </div>

                            <div style={styles.divider} />

                            <h2 style={styles.sectionTitle}>Pages</h2>
                            <div style={styles.links}>
                                <a style={styles.link} href="/admin/messages">
                                    <span>💬 Messages</span>
                                    <span style={styles.linkHint}>/admin/messages →</span>
                                </a>

                                <a style={styles.link} href="/admin/members">
                                    <span>👥 Members</span>
                                    <span style={styles.linkHint}>/admin/members →</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

function Stat({ label, value, styles }: { label: string; value: string; styles: Record<string, React.CSSProperties> }) {
    return (
        <div style={styles.statCard}>
            <span style={styles.statLabel}>{label}</span>
            <span style={styles.statValue}>{value}</span>
        </div>
    )
}

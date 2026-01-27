import { useAuth } from '@clerk/clerk-react'
import { useEffect, useMemo, useState } from 'react'
import TopNav from '../components/TopNav'

type ApiResp =
    | { success: true; message: string; data: { unread: number } }
    | { success: false; error: { code: string; message: string; details?: unknown } }

export default function Home() {
    const { getToken } = useAuth()
    const [unread, setUnread] = useState<number | null>(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        let cancelled = false

        ;(async () => {
            try {
                setLoading(true)

                const token = await getToken()
                if (!token) throw new Error('No token returned (not signed in?)')

                const res = await fetch('/api/contacts/unread-count', {
                    method: 'GET',
                    headers: { Authorization: `Bearer ${token}` },
                })

                const json = (await res.json()) as ApiResp
                if (cancelled) return

                if (!res.ok || json.success === false) {
                    setUnread(null)
                    return
                }

                setUnread(json.data.unread)
            } catch {
                if (!cancelled) setUnread(null)
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

            main: {
                minHeight: 'calc(100vh - 56px)',
                display: 'grid',
                placeItems: 'center',
                padding: 'clamp(14px, 3vw, 24px)',
            } as React.CSSProperties,

            card: {
                width: 'min(560px, 100%)',
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
                gridTemplateColumns: 'repeat(3, minmax(0, 1fr))',
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

            // Mobile tweaks without external CSS:
            mobileStats: {
                gridTemplateColumns: 'repeat(2, minmax(0, 1fr))',
            } as React.CSSProperties,
            tinyStats: {
                gridTemplateColumns: '1fr',
            } as React.CSSProperties,
        }),
        []
    )

    const unreadText = loading ? 'Loading…' : unread === null ? '—' : String(unread)

    return (
        <div style={styles.page}>
            <TopNav />

            <div style={styles.main}>
                <div style={styles.card}>
                    <div style={styles.headerRow}>
                        <h1 style={styles.h1}>Dashboard</h1>
                        <span style={styles.sub}>Admin overview</span>
                    </div>

                    {/* Stats */}
                    <div
                        style={{
                            ...styles.statsGrid,
                            ...(window.innerWidth <= 380
                                ? styles.tinyStats
                                : window.innerWidth <= 640
                                  ? styles.mobileStats
                                  : null),
                        }}
                    >
                        <Stat label="Unread messages" value={unreadText} styles={styles} />
                        <Stat label="Total members" value="200" styles={styles} />
                        <Stat label="Active / Inactive" value="170 / 30" styles={styles} />
                    </div>

                    <div style={styles.divider} />

                    {/* Pages */}
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

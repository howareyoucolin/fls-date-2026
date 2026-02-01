import { useAuth } from '@clerk/clerk-react'
import React, { useEffect, useMemo, useState } from 'react'
import TopNav from '../components/TopNav'

type Member = {
    id: number
    title: string
    gender: 'm' | 'f' | null
    birthday: string | null

    description: string | null
    profile_image: string | null
    profile_thumbnail: string | null

    wechat: string | null
    phone: string | null
    email: string | null

    is_approved: number // 0/1
    created_at: string
    updated_at: string
}

type MembersListResp =
    | {
          success: true
          message: string
          data: { page: number; pageSize: number; total: number; totalPages: number; status: string; items: Member[] }
      }
    | { success: false; error: { code: string; message: string; details?: unknown } }

type SetApprovedResp =
    | { success: true; message: string; data: { id: number; is_approved: number } }
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

function formatAge(birthday: string | null) {
    if (!birthday) return null
    const d = new Date(birthday + 'T00:00:00')
    if (Number.isNaN(d.getTime())) return null
    const now = new Date()
    let age = now.getFullYear() - d.getFullYear()
    const m = now.getMonth() - d.getMonth()
    if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--
    if (age < 0 || age > 120) return null
    return age
}

function getPageWindow(current: number, total: number, windowSize = 5) {
    const pages: (number | '…')[] = []

    if (total <= windowSize + 4) {
        for (let i = 1; i <= total; i++) pages.push(i)
        return pages
    }

    const start = Math.max(2, current - Math.floor(windowSize / 2))
    const end = Math.min(total - 1, current + Math.floor(windowSize / 2))

    pages.push(1)

    if (start > 2) pages.push('…')

    for (let i = start; i <= end; i++) pages.push(i)

    if (end < total - 1) pages.push('…')

    pages.push(total)

    return pages
}

const DESKTOP_BREAKPOINT = 860
const DESKTOP_SIDEBAR_W = 280
const CONTENT_MAX_W = 680
const PAGE_SIZE = 10

type StatusFilter = 'all' | 'active' | 'inactive'

export default function Members() {
    const { getToken } = useAuth()

    const isDesktop = useMedia(`(min-width: ${DESKTOP_BREAKPOINT}px)`)
    const isNarrow = useMedia('(max-width: 520px)')
    const isTiny = useMedia('(max-width: 420px)')

    const [status, setStatus] = useState<StatusFilter>('all')
    const [page, setPage] = useState(1)

    const [total, setTotal] = useState(0)
    const [totalPages, setTotalPages] = useState(1)

    const [items, setItems] = useState<Member[]>([])
    const [loading, setLoading] = useState(true)
    const [savingIds, setSavingIds] = useState<Record<number, boolean>>({})

    useEffect(() => {
        setPage(1)
    }, [status])

    useEffect(() => {
        let cancelled = false

        ;(async () => {
            try {
                setLoading(true)

                const token = await getToken()
                if (!token) throw new Error('No token returned (not signed in?)')

                const qs = new URLSearchParams({
                    page: String(page),
                    pageSize: String(PAGE_SIZE),
                    status,
                })

                const res = await fetch(`/api/members_list?${qs.toString()}`, {
                    method: 'GET',
                    headers: { Authorization: `Bearer ${token}` },
                })

                const json = (await res.json()) as MembersListResp
                if (cancelled) return

                if (!res.ok || json.success === false) {
                    setItems([])
                    setTotal(0)
                    setTotalPages(1)
                    return
                }

                setItems(json.data.items)
                setTotal(json.data.total)
                setTotalPages(Math.max(1, json.data.totalPages))
            } catch {
                if (!cancelled) {
                    setItems([])
                    setTotal(0)
                    setTotalPages(1)
                }
            } finally {
                if (!cancelled) setLoading(false)
            }
        })()

        return () => {
            cancelled = true
        }
    }, [getToken, page, status])

    async function toggleApproved(id: number, currentApproved: number) {
        const nextApproved = currentApproved === 1 ? 0 : 1

        try {
            setSavingIds((m) => ({ ...m, [id]: true }))

            const token = await getToken()
            if (!token) throw new Error('No token')

            const res = await fetch('/api/member_set_approved', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id, is_approved: nextApproved }),
            })

            const json = (await res.json()) as SetApprovedResp
            if (!res.ok || json.success === false) return

            setItems((prev) => prev.map((x) => (x.id === id ? { ...x, is_approved: nextApproved } : x)))

            if (status === 'active' && nextApproved === 0) setItems((prev) => prev.filter((x) => x.id !== id))
            if (status === 'inactive' && nextApproved === 1) setItems((prev) => prev.filter((x) => x.id !== id))
        } finally {
            setSavingIds((m) => {
                const copy = { ...m }
                delete copy[id]
                return copy
            })
        }
    }

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
                alignItems: isNarrow ? 'flex-start' : 'flex-end',
                justifyContent: 'space-between',
                gap: 12,
                marginBottom: 14,
                flexDirection: isNarrow ? 'column' : 'row',
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

            toolbar: {
                display: 'flex',
                alignItems: 'stretch',
                justifyContent: 'space-between',
                gap: 12,
                marginBottom: 14,
                flexWrap: isNarrow ? 'wrap' : 'nowrap',
            } as React.CSSProperties,

            pager: {
                display: 'flex',
                flexDirection: isNarrow ? 'column' : 'row',
                alignItems: isNarrow ? 'stretch' : 'center',
                justifyContent: 'space-between',
                gap: 10,
                padding: '10px 12px',
                borderRadius: 12,
                background: 'rgba(255,255,255,0.04)',
                border: '1px solid rgba(255,255,255,0.08)',
                flex: 1,
                minWidth: isNarrow ? '100%' : 0,
            } as React.CSSProperties,

            pagerLabel: {
                fontSize: 13,
                color: 'rgba(255,255,255,0.55)',
                whiteSpace: 'nowrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
            } as React.CSSProperties,

            btnRow: {
                display: 'flex',
                gap: 8,
                alignItems: 'center',
                justifyContent: isNarrow ? 'flex-start' : 'flex-end',
                flexWrap: 'nowrap',
                overflowX: 'auto',
                WebkitOverflowScrolling: 'touch',
                paddingBottom: 2,
            } as React.CSSProperties,

            btn: {
                padding: '8px 12px',
                borderRadius: 10,
                border: '1px solid rgba(255,255,255,0.12)',
                background: 'rgba(255,255,255,0.06)',
                color: '#fff',
                cursor: 'pointer',
                flex: '0 0 auto',
            } as React.CSSProperties,

            btnDisabled: { opacity: 0.5, cursor: 'not-allowed' } as React.CSSProperties,

            pageBtn: {
                padding: '8px 10px',
                borderRadius: 10,
                border: '1px solid rgba(255,255,255,0.12)',
                background: 'rgba(255,255,255,0.04)',
                color: '#fff',
                cursor: 'pointer',
                minWidth: 36,
                textAlign: 'center',
                flex: '0 0 auto',
            } as React.CSSProperties,

            pageBtnActive: {
                background: 'rgba(124,58,237,0.35)',
                border: '1px solid rgba(124,58,237,0.6)',
            } as React.CSSProperties,

            seg: {
                display: 'flex',
                borderRadius: 12,
                border: '1px solid rgba(255,255,255,0.10)',
                overflow: 'hidden',
                background: 'rgba(255,255,255,0.03)',
                width: isNarrow ? '100%' : 'auto',
            } as React.CSSProperties,

            segBtn: {
                padding: isNarrow ? '10px 10px' : '9px 12px',
                fontSize: 12,
                letterSpacing: 0.6,
                textTransform: 'uppercase',
                border: 'none',
                background: 'transparent',
                color: 'rgba(255,255,255,0.70)',
                cursor: 'pointer',
                flex: 1,
            } as React.CSSProperties,

            segActive: {
                background: 'rgba(255,255,255,0.08)',
                color: '#fff',
            } as React.CSSProperties,

            list: {
                display: 'grid',
                gap: 12,
            } as React.CSSProperties,

            memberCard: {
                display: 'grid',
                gridTemplateColumns: isTiny ? '1fr' : '104px 1fr',
                gap: 14,
                padding: 14,
                borderRadius: 14,
                background: 'rgba(255,255,255,0.04)',
                border: '1px solid rgba(255,255,255,0.08)',
                alignItems: 'stretch',
            } as React.CSSProperties,

            imgWrap: {
                width: isTiny ? '100%' : 104,
                height: isTiny ? 180 : 104,
                borderRadius: 12,
                overflow: 'hidden',
                background: 'rgba(255,255,255,0.05)',
                border: '1px solid rgba(255,255,255,0.10)',
            } as React.CSSProperties,

            img: {
                width: '100%',
                height: '100%',
                objectFit: 'cover',
                display: 'block',
            } as React.CSSProperties,

            topLine: {
                display: 'flex',
                alignItems: isTiny ? 'stretch' : 'flex-start',
                justifyContent: 'space-between',
                gap: 10,
                flexDirection: isTiny ? 'column' : 'row',
            } as React.CSSProperties,

            name: {
                fontSize: 18,
                fontWeight: 700,
                margin: 0,
                lineHeight: 1.15,
            } as React.CSSProperties,

            meta: {
                marginTop: 6,
                fontSize: 13,
                color: 'rgba(255,255,255,0.70)',
                display: 'flex',
                gap: 10,
                flexWrap: 'wrap',
            } as React.CSSProperties,

            desc: {
                marginTop: 10,
                fontSize: 13,
                lineHeight: 1.45,
                color: 'rgba(255,255,255,0.78)',
                display: '-webkit-box',
                WebkitLineClamp: 3,
                WebkitBoxOrient: 'vertical',
                overflow: 'hidden',
            } as React.CSSProperties,

            contacts: {
                marginTop: 10,
                display: 'grid',
                gridTemplateColumns: isTiny ? '1fr' : 'repeat(3, minmax(0, 1fr))',
                gap: 8,
                fontSize: 12,
                color: 'rgba(255,255,255,0.75)',
            } as React.CSSProperties,

            contactItem: {
                padding: '6px 8px',
                borderRadius: 8,
                background: 'rgba(255,255,255,0.04)',
                border: '1px solid rgba(255,255,255,0.08)',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap',
            } as React.CSSProperties,

            contactLabel: {
                display: 'block',
                fontSize: 10,
                letterSpacing: 0.6,
                textTransform: 'uppercase',
                color: 'rgba(255,255,255,0.45)',
                marginBottom: 2,
            } as React.CSSProperties,

            contactNA: {
                color: 'rgba(255,255,255,0.35)',
            } as React.CSSProperties,

            trashBtn: {
                background: 'transparent',
                border: 'none',
                color: '#ef4444', // red
                cursor: 'pointer',
                padding: 6,
                borderRadius: 8,
                display: 'grid',
                placeItems: 'center',
            } as React.CSSProperties,

            approveBtn: {
                padding: '9px 12px',
                borderRadius: 999,
                border: '1px solid rgba(255,255,255,0.12)',
                background: 'rgba(255,255,255,0.06)',
                color: '#fff',
                cursor: 'pointer',
                fontSize: 12,
                whiteSpace: 'nowrap',
                width: isTiny ? '100%' : 'auto',
                textAlign: 'center',
            } as React.CSSProperties,

            approveOn: {
                background: 'rgba(34,197,94,0.16)',
                border: '1px solid rgba(34,197,94,0.28)',
            } as React.CSSProperties,

            approveOff: {
                background: 'rgba(250,204,21,0.12)',
                border: '1px solid rgba(250,204,21,0.22)',
            } as React.CSSProperties,

            muted: { color: 'rgba(255,255,255,0.55)' } as React.CSSProperties,

            empty: {
                padding: 14,
                borderRadius: 12,
                border: '1px dashed rgba(255,255,255,0.18)',
                color: 'rgba(255,255,255,0.70)',
                background: 'rgba(0,0,0,0.08)',
            } as React.CSSProperties,
        }),
        [isDesktop, isNarrow, isTiny]
    )

    const pageLabel = loading ? 'Loading…' : `Page ${page} / ${totalPages} · ${total} total`
    const pageWindowSize = isNarrow ? 3 : 5

    return (
        <div style={styles.page}>
            <TopNav />

            <div style={styles.contentWrap}>
                <div style={styles.main}>
                    <div style={styles.container}>
                        <div style={styles.card}>
                            <div style={styles.headerRow}>
                                <div>
                                    <h1 style={styles.h1}>Members</h1>
                                    <div style={styles.sub}>Moderate members · 10 per page</div>
                                </div>

                                <div style={styles.seg} aria-label="Status filter">
                                    <button
                                        type="button"
                                        style={{ ...styles.segBtn, ...(status === 'all' ? styles.segActive : {}) }}
                                        onClick={() => setStatus('all')}
                                    >
                                        All
                                    </button>
                                    <button
                                        type="button"
                                        style={{ ...styles.segBtn, ...(status === 'active' ? styles.segActive : {}) }}
                                        onClick={() => setStatus('active')}
                                    >
                                        Active
                                    </button>
                                    <button
                                        type="button"
                                        style={{ ...styles.segBtn, ...(status === 'inactive' ? styles.segActive : {}) }}
                                        onClick={() => setStatus('inactive')}
                                    >
                                        Inactive
                                    </button>
                                </div>
                            </div>

                            <div style={styles.toolbar}>
                                <div style={styles.pager}>
                                    <div style={styles.pagerLabel}>{pageLabel}</div>

                                    <div style={styles.btnRow}>
                                        <button
                                            style={{ ...styles.btn, ...(page <= 1 || loading ? styles.btnDisabled : {}) }}
                                            disabled={page <= 1 || loading}
                                            onClick={() => setPage((p) => Math.max(1, p - 1))}
                                        >
                                            ←
                                        </button>

                                        {getPageWindow(page, totalPages, pageWindowSize).map((p, i) =>
                                            p === '…' ? (
                                                <span
                                                    key={`dots-${i}`}
                                                    style={{
                                                        color: 'rgba(255,255,255,0.45)',
                                                        padding: '0 6px',
                                                        flex: '0 0 auto',
                                                    }}
                                                >
                                                    …
                                                </span>
                                            ) : (
                                                <button
                                                    key={p}
                                                    style={{
                                                        ...styles.pageBtn,
                                                        ...(p === page ? styles.pageBtnActive : {}),
                                                        ...(loading ? styles.btnDisabled : {}),
                                                    }}
                                                    disabled={loading}
                                                    onClick={() => setPage(p)}
                                                >
                                                    {p}
                                                </button>
                                            )
                                        )}

                                        <button
                                            style={{
                                                ...styles.btn,
                                                ...(page >= totalPages || loading ? styles.btnDisabled : {}),
                                            }}
                                            disabled={page >= totalPages || loading}
                                            onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                                        >
                                            →
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div style={styles.list}>
                                {!loading && items.length === 0 ? (
                                    <div style={styles.empty}>No members found for this filter.</div>
                                ) : null}

                                {loading ? <div style={styles.empty}>Loading…</div> : null}

                                {items.map((m) => {
                                    const approved = m.is_approved === 1
                                    const saving = !!savingIds[m.id]
                                    const age = formatAge(m.birthday)
                                    const imgSrc = m.profile_thumbnail || m.profile_image || ''

                                    return (
                                        <div key={m.id} style={styles.memberCard}>
                                            <div style={styles.imgWrap}>
                                                {imgSrc ? (
                                                    <img style={styles.img} src={imgSrc} alt={m.title || `Member ${m.id}`} />
                                                ) : (
                                                    <div
                                                        style={{
                                                            width: '100%',
                                                            height: '100%',
                                                            display: 'grid',
                                                            placeItems: 'center',
                                                            color: 'rgba(255,255,255,0.45)',
                                                            fontSize: 12,
                                                        }}
                                                    >
                                                        No photo
                                                    </div>
                                                )}
                                            </div>

                                            <div style={{ minWidth: 0 }}>
                                                <div style={styles.topLine}>
                                                    <div style={{ minWidth: 0 }}>
                                                        <h3 style={styles.name}>
                                                            {m.title || <span style={styles.muted}>Untitled</span>}
                                                        </h3>
                                                        <div style={styles.meta}>
                                                            <span>#{m.id}</span>
                                                            <span>
                                                                {m.gender === 'm' ? 'Male' : m.gender === 'f' ? 'Female' : '—'}
                                                            </span>
                                                            <span>{age !== null ? `${age} yrs` : 'Age —'}</span>
                                                            <span style={styles.muted}>{approved ? 'Active' : 'Inactive'}</span>
                                                        </div>
                                                    </div>

                                                    <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                                                        <button
                                                            type="button"
                                                            title="Delete member"
                                                            onClick={() => alert('hello world')}
                                                            style={styles.trashBtn}
                                                        >
                                                            <svg
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                strokeWidth="2"
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                            >
                                                                <polyline points="3 6 5 6 21 6" />
                                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                                <path d="M10 11v6" />
                                                                <path d="M14 11v6" />
                                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                                            </svg>
                                                        </button>

                                                        <button
                                                            type="button"
                                                            title="Toggle approval"
                                                            disabled={saving}
                                                            onClick={() => toggleApproved(m.id, m.is_approved)}
                                                            style={{
                                                                ...styles.approveBtn,
                                                                ...(approved ? styles.approveOn : styles.approveOff),
                                                                opacity: saving ? 0.6 : 1,
                                                                cursor: saving ? 'not-allowed' : 'pointer',
                                                            }}
                                                        >
                                                            {approved ? '✅ Approved' : '⏳ Pending'}
                                                            {saving ? '…' : ''}
                                                        </button>
                                                    </div>
                                                </div>

                                                {m.description ? (
                                                    <div style={styles.desc}>{m.description}</div>
                                                ) : (
                                                    <div style={{ ...styles.desc, ...styles.muted }}>No description</div>
                                                )}

                                                <div style={styles.contacts}>
                                                    <div style={styles.contactItem} title={m.wechat || 'N/A'}>
                                                        <span style={styles.contactLabel}>WeChat</span>
                                                        <span style={m.wechat ? undefined : styles.contactNA}>
                                                            {m.wechat || 'N/A'}
                                                        </span>
                                                    </div>

                                                    <div style={styles.contactItem} title={m.email || 'N/A'}>
                                                        <span style={styles.contactLabel}>Email</span>
                                                        <span style={m.email ? undefined : styles.contactNA}>
                                                            {m.email || 'N/A'}
                                                        </span>
                                                    </div>

                                                    <div style={styles.contactItem} title={m.phone || 'N/A'}>
                                                        <span style={styles.contactLabel}>Phone</span>
                                                        <span style={m.phone ? undefined : styles.contactNA}>
                                                            {m.phone || 'N/A'}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    )
                                })}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

import { useAuth } from '@clerk/clerk-react'
import React, { useEffect, useMemo, useState } from 'react'
import TopNav from '../components/TopNav'

type MessageItem = {
    id: number
    name: string
    wechat: string | null
    email: string | null
    message: string
    message_preview: string
    is_read: number // 0/1
    created_at: string
}

type MessagesListResp =
    | {
          success: true
          message: string
          data: {
              page: number
              pageSize: number
              total: number
              totalPages: number
              status: string
              items: MessageItem[]
          }
      }
    | { success: false; error: { code: string; message: string; details?: unknown } }

type MarkReadResp =
    | { success: true; message: string; data: { id: number; is_read: 1 } }
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
const PAGE_SIZE = 20

type StatusFilter = 'all' | 'read' | 'unread'

export default function Messages() {
    const { getToken } = useAuth()

    const isDesktop = useMedia(`(min-width: ${DESKTOP_BREAKPOINT}px)`)
    const isNarrow = useMedia('(max-width: 520px)')
    const isTiny = useMedia('(max-width: 420px)')

    const [status, setStatus] = useState<StatusFilter>('all')
    const [page, setPage] = useState(1)

    const [items, setItems] = useState<MessageItem[]>([])
    const [total, setTotal] = useState(0)
    const [totalPages, setTotalPages] = useState(1)
    const [loading, setLoading] = useState(true)

    const [selected, setSelected] = useState<MessageItem | null>(null)
    const [markingIds, setMarkingIds] = useState<Record<number, boolean>>({})

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

                const res = await fetch(`/api/messages_list?${qs.toString()}`, {
                    method: 'GET',
                    headers: { Authorization: `Bearer ${token}` },
                })

                const json = (await res.json()) as MessagesListResp
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

    async function markRead(id: number) {
        if (markingIds[id]) return

        try {
            setMarkingIds((m) => ({ ...m, [id]: true }))

            const token = await getToken()
            if (!token) throw new Error('No token')

            const res = await fetch('/api/message_mark_read', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id }),
            })

            const json = (await res.json()) as MarkReadResp
            if (!res.ok || json.success === false) return

            setItems((prev) => prev.map((x) => (x.id === id ? { ...x, is_read: 1 } : x)))

            // If user is filtering unread, remove it from list after marking read
            if (status === 'unread') {
                setItems((prev) => prev.filter((x) => x.id !== id))
            }
        } finally {
            setMarkingIds((m) => {
                const copy = { ...m }
                delete copy[id]
                return copy
            })
        }
    }

    function openMessage(m: MessageItem) {
        setSelected(m)
        if (m.is_read === 0) {
            void markRead(m.id)
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

            contentWrap: { paddingLeft: isDesktop ? DESKTOP_SIDEBAR_W : 0 } as React.CSSProperties,

            main: {
                minHeight: 'calc(100vh - 56px)',
                padding: 'clamp(14px, 3vw, 24px)',
            } as React.CSSProperties,

            container: { maxWidth: CONTENT_MAX_W, margin: '0 auto' } as React.CSSProperties,

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

            sub: { fontSize: 13, color: 'rgba(255,255,255,0.55)' } as React.CSSProperties,

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

            segActive: { background: 'rgba(255,255,255,0.08)', color: '#fff' } as React.CSSProperties,

            toolbar: { display: 'flex', gap: 12, marginBottom: 14 } as React.CSSProperties,

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
                width: '100%',
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

            list: { display: 'grid', gap: 10 } as React.CSSProperties,

            row: {
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                gap: 12,
                padding: '12px 14px',
                borderRadius: 12,
                background: 'rgba(255,255,255,0.04)',
                border: '1px solid rgba(255,255,255,0.08)',
                cursor: 'pointer',
            } as React.CSSProperties,

            left: { minWidth: 0 } as React.CSSProperties,

            titleLine: {
                display: 'flex',
                alignItems: 'baseline',
                gap: 10,
                flexWrap: 'wrap',
            } as React.CSSProperties,

            name: { margin: 0, fontSize: 15, fontWeight: 700 } as React.CSSProperties,

            time: { fontSize: 12, color: 'rgba(255,255,255,0.55)' } as React.CSSProperties,

            preview: {
                marginTop: 6,
                fontSize: 13,
                color: 'rgba(255,255,255,0.78)',
                whiteSpace: 'nowrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                maxWidth: isTiny ? 240 : 520,
            } as React.CSSProperties,

            badge: {
                flex: '0 0 auto',
                padding: '6px 10px',
                borderRadius: 999,
                fontSize: 12,
                border: '1px solid rgba(255,255,255,0.12)',
                background: 'rgba(255,255,255,0.05)',
            } as React.CSSProperties,

            badgeUnread: {
                background: 'rgba(244,63,94,0.18)',
                border: '1px solid rgba(244,63,94,0.45)',
                color: '#fff',
                boxShadow: '0 0 0 3px rgba(244,63,94,0.10)',
            } as React.CSSProperties,

            badgeRead: { color: 'rgba(255,255,255,0.55)' } as React.CSSProperties,

            empty: {
                padding: 14,
                borderRadius: 12,
                border: '1px dashed rgba(255,255,255,0.18)',
                color: 'rgba(255,255,255,0.70)',
                background: 'rgba(0,0,0,0.08)',
            } as React.CSSProperties,

            // Modal
            modalOverlay: {
                position: 'fixed',
                inset: 0,
                background: 'rgba(0,0,0,0.55)',
                display: 'grid',
                placeItems: 'center',
                padding: 16,
                zIndex: 50,
            } as React.CSSProperties,

            modal: {
                width: 'min(680px, 100%)',
                borderRadius: 18,
                background: '#ffffff', // ✅ white modal
                border: '1px solid rgba(0,0,0,0.10)',
                boxShadow: '0 30px 90px rgba(0,0,0,0.55)',
                overflow: 'hidden',
            } as React.CSSProperties,

            modalHeader: {
                padding: '14px 16px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                gap: 12,
                borderBottom: '1px solid rgba(0,0,0,0.08)',
            } as React.CSSProperties,

            modalTitle: { fontSize: 16, fontWeight: 800, margin: 0, color: '#0b0b10' } as React.CSSProperties,

            modalSub: { fontSize: 12, color: 'rgba(0,0,0,0.55)' } as React.CSSProperties,

            closeBtn: {
                padding: '8px 12px',
                borderRadius: 12,
                border: '1px solid rgba(0,0,0,0.10)',
                background: 'rgba(0,0,0,0.04)',
                color: '#0b0b10',
                cursor: 'pointer',
            } as React.CSSProperties,

            modalBody: { padding: 16, color: '#0b0b10' } as React.CSSProperties,

            contacts: {
                display: 'grid',
                gridTemplateColumns: isTiny ? '1fr' : 'repeat(3, minmax(0, 1fr))',
                gap: 8,
                marginTop: 10,
                marginBottom: 12,
            } as React.CSSProperties,

            contactItem: {
                padding: '6px 8px',
                borderRadius: 8,
                background: 'rgba(0,0,0,0.03)',
                border: '1px solid rgba(0,0,0,0.08)',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap',
                fontSize: 12,
                color: '#0b0b10',
            } as React.CSSProperties,

            contactLabel: {
                display: 'block',
                fontSize: 10,
                letterSpacing: 0.6,
                textTransform: 'uppercase',
                color: 'rgba(0,0,0,0.45)',
                marginBottom: 2,
            } as React.CSSProperties,

            contactNA: { color: 'rgba(0,0,0,0.35)' } as React.CSSProperties,

            messageText: {
                fontSize: 13,
                lineHeight: 1.55,
                color: 'rgba(0,0,0,0.85)',
                whiteSpace: 'pre-wrap',
                wordBreak: 'break-word',
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
                                    <h1 style={styles.h1}>Messages</h1>
                                    <div style={styles.sub}>Inbox · 20 per page</div>
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
                                        style={{ ...styles.segBtn, ...(status === 'unread' ? styles.segActive : {}) }}
                                        onClick={() => setStatus('unread')}
                                    >
                                        Unread
                                    </button>
                                    <button
                                        type="button"
                                        style={{ ...styles.segBtn, ...(status === 'read' ? styles.segActive : {}) }}
                                        onClick={() => setStatus('read')}
                                    >
                                        Read
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
                                {!loading && items.length === 0 ? <div style={styles.empty}>No messages found.</div> : null}
                                {loading ? <div style={styles.empty}>Loading…</div> : null}

                                {items.map((m) => {
                                    const unread = m.is_read === 0
                                    return (
                                        <div
                                            key={m.id}
                                            style={styles.row}
                                            role="button"
                                            tabIndex={0}
                                            onClick={() => openMessage(m)}
                                            onKeyDown={(e) => {
                                                if (e.key === 'Enter' || e.key === ' ') openMessage(m)
                                            }}
                                        >
                                            <div style={styles.left}>
                                                <div style={styles.titleLine}>
                                                    <h3 style={styles.name}>{m.name || '—'}</h3>
                                                    <span style={styles.time}>{m.created_at}</span>
                                                </div>
                                                <div style={styles.preview}>{m.message_preview || '—'}</div>
                                            </div>

                                            <div style={{ ...styles.badge, ...(unread ? styles.badgeUnread : styles.badgeRead) }}>
                                                {unread ? 'Unread' : 'Read'}
                                            </div>
                                        </div>
                                    )
                                })}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {selected ? (
                <div
                    style={styles.modalOverlay}
                    onMouseDown={(e) => {
                        if (e.target === e.currentTarget) setSelected(null)
                    }}
                >
                    <div style={styles.modal}>
                        <div style={styles.modalHeader}>
                            <div style={{ minWidth: 0 }}>
                                <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap', alignItems: 'baseline' }}>
                                    <h3 style={styles.modalTitle}>{selected.name || '—'}</h3>
                                    <span style={styles.modalSub}>{selected.created_at}</span>
                                </div>
                            </div>

                            <button style={styles.closeBtn} onClick={() => setSelected(null)}>
                                Close
                            </button>
                        </div>

                        <div style={styles.modalBody}>
                            <div style={styles.contacts}>
                                <div style={styles.contactItem} title={selected.wechat || 'N/A'}>
                                    <span style={styles.contactLabel}>WeChat</span>
                                    <span style={selected.wechat ? undefined : styles.contactNA}>{selected.wechat || 'N/A'}</span>
                                </div>

                                <div style={styles.contactItem} title={selected.email || 'N/A'}>
                                    <span style={styles.contactLabel}>Email</span>
                                    <span style={selected.email ? undefined : styles.contactNA}>{selected.email || 'N/A'}</span>
                                </div>
                            </div>

                            <div style={styles.messageText}>{selected.message || '—'}</div>
                        </div>
                    </div>
                </div>
            ) : null}
        </div>
    )
}

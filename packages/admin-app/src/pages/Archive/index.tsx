// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-nocheck
import { useAuth } from '@clerk/clerk-react'
import { useEffect, useMemo, useState } from 'react'
import TopNav from '../../components/TopNav'
import ConfirmModal from '../Members/ConfirmModal'
import Pagination from '../Members/Pagination'
import ArchiveMemberCard from './ArchiveMemberCard'
import '../Members/members.css'

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
    is_approved: number
    is_archived?: number
}

type MembersListResp =
    | {
          success: true
          message: string
          data: { page: number; pageSize: number; total: number; totalPages: number; status: string; items: Member[] }
      }
    | { success: false; error: { code: string; message: string; details?: unknown } }

type UnarchiveResp =
    | { success: true; message: string; data?: { id: number; is_archived: number } }
    | { success: false; error: { code: string; message: string; details?: unknown } }

const PAGE_SIZE = 10

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

export default function Archive() {
    const { getToken } = useAuth()

    const isDesktop = useMedia('(min-width: 860px)')
    const isNarrow = useMedia('(max-width: 520px)')
    const isTiny = useMedia('(max-width: 420px)')

    // Archive page always shows archived members
    const status = 'archived'

    const [page, setPage] = useState(1)
    const [total, setTotal] = useState(0)
    const [totalPages, setTotalPages] = useState(1)

    const [items, setItems] = useState<Member[]>([])
    const [loading, setLoading] = useState(true)

    const [restoreId, setRestoreId] = useState<number | null>(null)
    const [restoring, setRestoring] = useState(false)

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
                    status, // expects backend to support status=archived
                })

                const res = await fetch(`/api/archived_members_list?${qs.toString()}`, {
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

                setItems(json.data.items || [])
                setTotal(json.data.total || 0)
                setTotalPages(Math.max(1, json.data.totalPages || 1))
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
    }, [getToken, page])

    async function unarchiveMember(id: number) {
        setRestoring(true)
        try {
            const token = await getToken()
            if (!token) throw new Error('No token')

            // Try a dedicated endpoint first (recommended)
            const res = await fetch('/api/member_unarchive', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id }),
            })

            const json = (await res.json()) as UnarchiveResp
            if (!res.ok || json.success === false) return

            // Remove from the archive list UI
            setItems((prev) => prev.filter((x) => x.id !== id))

            // Update totals + pages
            setTotal((t) => {
                const next = Math.max(0, t - 1)
                const nextPages = Math.max(1, Math.ceil(next / PAGE_SIZE))
                setTotalPages(nextPages)
                setPage((p) => (p > nextPages ? nextPages : p))
                return next
            })
        } finally {
            setRestoring(false)
            setRestoreId(null)
        }
    }

    const pageLabel = loading ? 'Loading…' : `Page ${page} / ${totalPages} · ${total} total`

    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const cx = useMemo(
        () => ({
            navBtn: (disabled: boolean) => `btn ${disabled ? 'is-disabled' : ''}`,
        }),
        []
    )

    return (
        <div className="members-page">
            <TopNav />

            <div className="members-content-wrap" style={{ paddingLeft: isDesktop ? 280 : 0 }}>
                <div className="members-main">
                    <div className="members-container">
                        <div className="members-card">
                            <div className={`header-row ${isNarrow ? 'is-narrow' : ''}`}>
                                <div>
                                    <h1 className="members-h1">Archive</h1>
                                    <div className="members-sub">Archived members · 10 per page</div>
                                </div>

                                {/* keep layout same as Members page (right-side area), but no filter */}
                                <div className={`seg ${isNarrow ? 'is-full' : ''}`} aria-label="Status filter">
                                    <button type="button" className="seg-btn is-active" disabled>
                                        Archived
                                    </button>
                                </div>
                            </div>

                            <Pagination
                                pageLabel={pageLabel}
                                page={page}
                                totalPages={totalPages}
                                loading={loading}
                                isNarrow={isNarrow}
                                onPrev={() => setPage((p) => Math.max(1, p - 1))}
                                onNext={() => setPage((p) => Math.min(totalPages, p + 1))}
                                onSetPage={setPage}
                            />

                            <div className="members-list">
                                {!loading && items.length === 0 && <div className="empty">No archived members.</div>}
                                {loading && <div className="empty">Loading…</div>}

                                {items.map((m) => (
                                    <ArchiveMemberCard key={m.id} member={m} isTiny={isTiny} onRestore={setRestoreId} />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ConfirmModal
                open={restoreId !== null}
                title="Restore member"
                description={restoreId !== null ? `Restore member #${restoreId}?` : undefined}
                confirmText={restoring ? 'Restoring…' : 'Restore'}
                cancelText="Cancel"
                danger={false}
                disabled={restoring}
                onCancel={() => (restoring ? null : setRestoreId(null))}
                onConfirm={() => {
                    if (restoreId !== null && !restoring) unarchiveMember(restoreId)
                }}
            />
        </div>
    )
}

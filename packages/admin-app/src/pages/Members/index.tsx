// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-nocheck
import { useAuth } from '@clerk/clerk-react'
import { useEffect, useMemo, useState } from 'react'
import TopNav from '../../components/TopNav'
import ConfirmModal from './ConfirmModal'
import Pagination from './Pagination'
import MemberCard from './MemberCard'
import './members.css'

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

type StatusFilter = 'all' | 'active' | 'inactive'

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

export default function Members() {
    const { getToken } = useAuth()

    const isDesktop = useMedia('(min-width: 860px)')
    const isNarrow = useMedia('(max-width: 520px)')
    const isTiny = useMedia('(max-width: 420px)')

    const [status, setStatus] = useState<StatusFilter>('all')
    const [page, setPage] = useState(1)

    const [total, setTotal] = useState(0)
    const [totalPages, setTotalPages] = useState(1)

    const [items, setItems] = useState<unknown[]>([])
    const [loading, setLoading] = useState(true)
    const [savingIds, setSavingIds] = useState<Record<number, boolean>>({})

    const [deleteId, setDeleteId] = useState<number | null>(null)

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

            if (status === 'active' && nextApproved === 0) {
                setItems((prev) => prev.filter((x) => x.id !== id))
            }
            if (status === 'inactive' && nextApproved === 1) {
                setItems((prev) => prev.filter((x) => x.id !== id))
            }
        } finally {
            setSavingIds((m) => {
                const copy = { ...m }
                delete copy[id]
                return copy
            })
        }
    }

    const pageLabel = loading ? 'Loading…' : `Page ${page} / ${totalPages} · ${total} total`

    const cx = useMemo(
        () => ({
            segBtn: (active: boolean) => `seg-btn ${active ? 'is-active' : ''}`,
            navBtn: (disabled: boolean) => `btn ${disabled ? 'is-disabled' : ''}`,
            pageBtn: (active: boolean, disabled: boolean) =>
                `page-btn ${active ? 'is-active' : ''} ${disabled ? 'is-disabled' : ''}`,
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
                                    <h1 className="members-h1">Members</h1>
                                    <div className="members-sub">Moderate members · 10 per page</div>
                                </div>

                                <div className={`seg ${isNarrow ? 'is-full' : ''}`} aria-label="Status filter">
                                    <button
                                        type="button"
                                        className={cx.segBtn(status === 'all')}
                                        onClick={() => setStatus('all')}
                                    >
                                        All
                                    </button>
                                    <button
                                        type="button"
                                        className={cx.segBtn(status === 'active')}
                                        onClick={() => setStatus('active')}
                                    >
                                        Active
                                    </button>
                                    <button
                                        type="button"
                                        className={cx.segBtn(status === 'inactive')}
                                        onClick={() => setStatus('inactive')}
                                    >
                                        Inactive
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
                                {!loading && items.length === 0 && <div className="empty">No members found for this filter.</div>}

                                {loading && <div className="empty">Loading…</div>}

                                {items.map((m) => (
                                    <MemberCard
                                        key={m.id}
                                        member={m}
                                        isTiny={isTiny}
                                        saving={!!savingIds[m.id]}
                                        onToggleApproved={toggleApproved}
                                        onDelete={setDeleteId}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ConfirmModal
                open={deleteId !== null}
                title="确认操作"
                description={deleteId !== null ? `确定要删除成员 #${deleteId} 吗？此操作不可恢复。` : undefined}
                confirmText="确认删除"
                cancelText="手滑了"
                danger
                onCancel={() => setDeleteId(null)}
                onConfirm={() => {
                    alert('hello world')
                    setDeleteId(null)
                }}
            />
        </div>
    )
}

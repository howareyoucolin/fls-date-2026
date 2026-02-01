type Props = {
    pageLabel: string
    page: number
    totalPages: number
    loading: boolean
    isNarrow: boolean
    onPrev: () => void
    onNext: () => void
    onSetPage: (p: number) => void
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

export default function Pagination({ pageLabel, page, totalPages, loading, isNarrow, onPrev, onNext, onSetPage }: Props) {
    const pageWindowSize = isNarrow ? 3 : 5
    const win = getPageWindow(page, totalPages, pageWindowSize)

    return (
        <div className="toolbar">
            <div className={`pager ${isNarrow ? 'is-narrow' : ''}`}>
                <div className="pager-label">{pageLabel}</div>

                <div className="btn-row">
                    <button
                        className={`btn ${page <= 1 || loading ? 'is-disabled' : ''}`}
                        disabled={page <= 1 || loading}
                        onClick={onPrev}
                    >
                        ←
                    </button>

                    {win.map((p, i) =>
                        p === '…' ? (
                            <span key={`dots-${i}`} className="dots">
                                …
                            </span>
                        ) : (
                            <button
                                key={p}
                                className={`page-btn ${p === page ? 'is-active' : ''} ${loading ? 'is-disabled' : ''}`}
                                disabled={loading}
                                onClick={() => onSetPage(p)}
                            >
                                {p}
                            </button>
                        )
                    )}

                    <button
                        className={`btn ${page >= totalPages || loading ? 'is-disabled' : ''}`}
                        disabled={page >= totalPages || loading}
                        onClick={onNext}
                    >
                        →
                    </button>
                </div>
            </div>
        </div>
    )
}

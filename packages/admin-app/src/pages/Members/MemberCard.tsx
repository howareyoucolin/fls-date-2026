export type Member = {
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
}

type Props = {
    member: Member
    isTiny: boolean
    saving: boolean
    onToggleApproved: (id: number, current: number) => void
    onDelete: (id: number) => void
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

export default function MemberCard({
    member: m,
    isTiny,
    saving,
    onToggleApproved,
    onDelete,
}: Props) {
    const approved = m.is_approved === 1
    const age = formatAge(m.birthday)
    const imgSrc = m.profile_thumbnail || m.profile_image || ''

    const cx = {
        approveBtn: `approve-btn ${approved ? 'is-on' : 'is-off'} ${saving ? 'is-saving' : ''}`,
    }

    return (
        <div className={`member-card ${isTiny ? 'is-tiny' : ''}`}>
            <div className={`img-wrap ${isTiny ? 'is-tiny' : ''}`}>
                {imgSrc ? (
                    <img className="img" src={imgSrc} alt={m.title || `Member ${m.id}`} />
                ) : (
                    <div className="no-photo">No photo</div>
                )}
            </div>

            <div className="member-right">
                <div className={`top-line ${isTiny ? 'is-tiny' : ''}`}>
                    <div className="member-left">
                        <h3 className="name">
                            {m.title || <span className="muted">Untitled</span>}
                        </h3>
                        <div className="meta">
                            <span>#{m.id}</span>
                            <span>{m.gender === 'm' ? 'Male' : m.gender === 'f' ? 'Female' : '—'}</span>
                            <span>{age !== null ? `${age} yrs` : 'Age —'}</span>
                            <span className="muted">{approved ? 'Active' : 'Inactive'}</span>
                        </div>
                    </div>

                    <div className="actions">
                        <button
                            type="button"
                            title="Delete member"
                            onClick={() => onDelete(m.id)}
                            className="trash-btn"
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
                            onClick={() => onToggleApproved(m.id, m.is_approved)}
                            className={cx.approveBtn}
                        >
                            {approved ? '✅ Approved' : '⏳ Pending'}
                            {saving ? '…' : ''}
                        </button>
                    </div>
                </div>

                {m.description ? (
                    <div className="desc">{m.description}</div>
                ) : (
                    <div className="desc muted">No description</div>
                )}

                <div className={`contacts ${isTiny ? 'is-tiny' : ''}`}>
                    <div className="contact-item" title={m.wechat || 'N/A'}>
                        <span className="contact-label">WeChat</span>
                        <span className={!m.wechat ? 'na' : ''}>{m.wechat || 'N/A'}</span>
                    </div>

                    <div className="contact-item" title={m.email || 'N/A'}>
                        <span className="contact-label">Email</span>
                        <span className={!m.email ? 'na' : ''}>{m.email || 'N/A'}</span>
                    </div>

                    <div className="contact-item" title={m.phone || 'N/A'}>
                        <span className="contact-label">Phone</span>
                        <span className={!m.phone ? 'na' : ''}>{m.phone || 'N/A'}</span>
                    </div>
                </div>
            </div>
        </div>
    )
}

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

type Props = {
    member: Member
    isTiny: boolean
    onRestore: (id: number) => void
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

export default function ArchiveMemberCard({ member: m, isTiny, onRestore }: Props) {
    const age = formatAge(m.birthday)
    const imgSrc = m.profile_thumbnail || m.profile_image || ''

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
                        <h3 className="name">{m.title || <span className="muted">Untitled</span>}</h3>
                        <div className="meta">
                            <span>#{m.id}</span>
                            <span>{m.gender === 'm' ? 'Male' : m.gender === 'f' ? 'Female' : '—'}</span>
                            <span>{age !== null ? `${age} yrs` : 'Age —'}</span>
                            <span className="muted">Archived</span>
                        </div>
                    </div>

                    <div className="actions">
                        <button type="button" title="Restore member" onClick={() => onRestore(m.id)} className="trash-btn">
                            {/* simple “undo” style icon */}
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
                                <path d="M9 14l-4-4 4-4" />
                                <path d="M5 10h9a5 5 0 1 1 0 10h-3" />
                            </svg>
                        </button>

                        {/* Keep a second button so the card layout looks identical */}
                        <button
                            type="button"
                            className="approve-btn is-off"
                            disabled
                            title="Archived"
                            style={{ cursor: 'default' }}
                        >
                            🗄️ Archived
                        </button>
                    </div>
                </div>

                {m.description ? <div className="desc">{m.description}</div> : <div className="desc muted">No description</div>}

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

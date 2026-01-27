import { SignOutButton, useUser } from '@clerk/clerk-react'

export default function TopNav() {
    const { user } = useUser()

    const email = user?.primaryEmailAddress?.emailAddress || user?.emailAddresses?.[0]?.emailAddress || 'Unknown user'

    return (
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
            <div
                style={{
                    maxWidth: 1100,
                    margin: '0 auto',
                    padding: '12px var(--app-pad)',
                }}
            >
                {/* Row container: wraps on mobile */}
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        gap: 12,
                        flexWrap: 'wrap',
                    }}
                >
                    {/* Brand */}
                    <div
                        style={{
                            fontWeight: 900,
                            letterSpacing: '-0.02em',
                            color: '#fff',
                            fontSize: 16,
                            lineHeight: 1.1,
                            whiteSpace: 'nowrap',
                            flex: '1 1 160px',
                            minWidth: 160,
                        }}
                    >
                        Flushing Dating Admin
                    </div>

                    {/* Right side: email + sign out */}
                    <div
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 10,
                            flex: '1 1 260px',
                            minWidth: 260,
                            justifyContent: 'flex-end',
                        }}
                    >
                        {/* Email pill (truncate on mobile) */}
                        <div
                            style={{
                                fontSize: 13,
                                color: 'rgba(255,255,255,0.75)',
                                padding: '6px 10px',
                                border: '1px solid rgba(255,255,255,0.12)',
                                borderRadius: 999,

                                /* critical for mobile */
                                flex: '1 1 auto',
                                minWidth: 0, // allows ellipsis in flex
                                overflow: 'hidden',
                                textOverflow: 'ellipsis',
                                whiteSpace: 'nowrap',
                            }}
                            title={email}
                        >
                            {email}
                        </div>

                        <SignOutButton>
                            <button
                                style={{
                                    height: 34,
                                    padding: '0 14px',
                                    borderRadius: 999,
                                    border: '1px solid rgba(255,255,255,0.12)',
                                    background: 'linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%)',
                                    color: '#fff',
                                    fontSize: 13,
                                    fontWeight: 800,
                                    cursor: 'pointer',

                                    /* don't let it squish and wrap */
                                    flex: '0 0 auto',
                                    whiteSpace: 'nowrap',
                                }}
                            >
                                Sign out
                            </button>
                        </SignOutButton>
                    </div>
                </div>
            </div>
        </div>
    )
}

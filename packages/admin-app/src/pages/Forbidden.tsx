import { useClerk, useUser } from '@clerk/clerk-react'

export default function Forbidden() {
    const { signOut } = useClerk()
    const { user, isLoaded } = useUser()

    const email = user?.primaryEmailAddress?.emailAddress || '(unknown)'

    return (
        <div
            style={{
                height: '100dvh',
                overflow: 'hidden',
                display: 'grid',
                placeItems: 'center',
                padding: 'clamp(14px, 3vw, 28px)',
                position: 'relative',
                background:
                    'radial-gradient(1000px circle at 30% 20%, rgba(124,58,237,0.22), transparent 58%),' +
                    'radial-gradient(900px circle at 70% 70%, rgba(79,70,229,0.18), transparent 62%),' +
                    'linear-gradient(180deg, #0b0b10 0%, #0f172a 100%)',
            }}
        >
            <style>{`
        @keyframes floatUp {
          0% { transform: translateY(0) rotate(45deg) scale(0.85); opacity: 0; }
          15% { opacity: 0.9; }
          100% { transform: translateY(-130vh) rotate(45deg) scale(1.25); opacity: 0; }
        }
        @keyframes glowPulse {
          0%,100% { opacity: 0.55; transform: scale(1); }
          50% { opacity: 0.85; transform: scale(1.03); }
        }
      `}</style>

            {/* Soft glow layer */}
            <div
                style={{
                    position: 'absolute',
                    inset: -200,
                    background:
                        'radial-gradient(500px circle at 22% 18%, rgba(124,58,237,0.18), transparent 62%),' +
                        'radial-gradient(520px circle at 78% 78%, rgba(79,70,229,0.16), transparent 65%)',
                    filter: 'blur(10px)',
                    animation: 'glowPulse 7s ease-in-out infinite',
                    pointerEvents: 'none',
                }}
            />

            {/* Floating hearts */}
            <div
                style={{
                    position: 'absolute',
                    inset: 0,
                    pointerEvents: 'none',
                    overflow: 'hidden',
                    opacity: 0.55,
                }}
            >
                {[...Array(10)].map((_, i) => {
                    const size = 12 + i * 1.8
                    const left = 6 + i * 9.2
                    const duration = 6.5 + i * 0.75
                    const delay = i * 0.9
                    const color = `rgba(255, ${110 + i * 8}, 190, 0.55)`

                    return (
                        <div
                            key={i}
                            style={{
                                position: 'absolute',
                                left: `${left}%`,
                                bottom: '-60px',
                                width: size,
                                height: size,
                                background: color,
                                transform: 'rotate(45deg)',
                                animation: `floatUp ${duration}s ease-in-out ${delay}s infinite`,
                                filter: 'blur(0.2px)',
                            }}
                        >
                            <span
                                style={{
                                    position: 'absolute',
                                    width: size,
                                    height: size,
                                    background: color,
                                    borderRadius: '50%',
                                    top: -size / 2,
                                    left: 0,
                                }}
                            />
                            <span
                                style={{
                                    position: 'absolute',
                                    width: size,
                                    height: size,
                                    background: color,
                                    borderRadius: '50%',
                                    left: -size / 2,
                                    top: 0,
                                }}
                            />
                        </div>
                    )
                })}
            </div>

            {/* Card */}
            <div
                style={{
                    width: 'min(720px, 100%)',
                    padding: 'clamp(22px, 3.2vw, 34px)',
                    borderRadius: 20,
                    background: 'rgba(255,255,255,0.07)',
                    border: '1px solid rgba(255,255,255,0.12)',
                    boxShadow: '0 30px 90px rgba(0,0,0,0.62)',
                    backdropFilter: 'blur(12px)',
                    zIndex: 1,
                }}
            >
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12 }}>
                    <div style={{ minWidth: 0 }}>
                        <div
                            style={{
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: 8,
                                padding: '6px 10px',
                                borderRadius: 999,
                                border: '1px solid rgba(255,255,255,0.12)',
                                background: 'rgba(255,255,255,0.04)',
                                color: 'rgba(255,255,255,0.78)',
                                fontSize: 12,
                                fontWeight: 800,
                                letterSpacing: 0.2,
                                marginBottom: 12,
                            }}
                        >
                            ⛔ Access Denied
                        </div>

                        <h1
                            style={{
                                fontSize: 'clamp(28px, 4.6vw, 38px)',
                                color: '#fff',
                                margin: 0,
                                letterSpacing: '-0.02em',
                                lineHeight: 1.08,
                            }}
                        >
                            403
                            <span style={{ color: 'rgba(255,255,255,0.82)' }}> Forbidden</span>
                        </h1>

                        <p
                            style={{
                                marginTop: 10,
                                marginBottom: 0,
                                color: 'rgba(255,255,255,0.72)',
                                fontSize: 'clamp(14px, 2.6vw, 16px)',
                                lineHeight: 1.5,
                            }}
                        >
                            You’re signed in, but your account isn’t on the whitelist for this admin portal.
                        </p>

                        <p
                            style={{
                                marginTop: 10,
                                marginBottom: 0,
                                color: 'rgba(255,255,255,0.60)',
                                fontSize: 13,
                                lineHeight: 1.5,
                            }}
                        >
                            Signed in as: <strong style={{ color: 'rgba(255,255,255,0.85)' }}>{isLoaded ? email : '...'}</strong>
                        </p>
                    </div>

                    {/* icon badge */}
                    <div
                        style={{
                            width: 56,
                            height: 56,
                            borderRadius: 16,
                            background: 'rgba(255,255,255,0.06)',
                            border: '1px solid rgba(255,255,255,0.14)',
                            display: 'grid',
                            placeItems: 'center',
                            boxShadow: '0 12px 40px rgba(124,58,237,0.25)',
                            flex: '0 0 auto',
                        }}
                    >
                        <span
                            style={{
                                fontSize: 28,
                                lineHeight: 1,
                                filter: 'drop-shadow(0 6px 16px rgba(124,58,237,0.5))',
                            }}
                        >
                            🚫
                        </span>
                    </div>
                </div>

                <div
                    style={{
                        height: 1,
                        background: 'rgba(255,255,255,0.10)',
                        margin: '18px 0',
                    }}
                />

                <button
                    onClick={() => signOut({ redirectUrl: '/login' })}
                    style={{
                        width: '100%',
                        height: 54,
                        borderRadius: 14,
                        border: '1px solid rgba(255,255,255,0.14)',
                        background: 'linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%)',
                        color: 'white',
                        fontSize: 16,
                        fontWeight: 900,
                        cursor: 'pointer',
                        boxShadow: '0 18px 60px rgba(79,70,229,0.22)',
                    }}
                >
                    Sign out & try another account
                </button>

                <div
                    style={{
                        marginTop: 12,
                        color: 'rgba(255,255,255,0.55)',
                        fontSize: 12,
                        textAlign: 'center',
                    }}
                >
                    If you believe this is a mistake, ask an admin to add your email to <strong>whi</strong>.
                </div>
            </div>
        </div>
    )
}

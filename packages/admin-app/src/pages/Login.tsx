import { useNavigate } from 'react-router-dom'

export default function Login() {
    const navigate = useNavigate()

    return (
        <div
            style={{
                minHeight: '100vh',
                display: 'grid',
                placeItems: 'center',
                padding: 'var(--app-pad)',
                position: 'relative',
                overflow: 'hidden',
                background:
                    'radial-gradient(800px circle at 30% 20%, rgba(124,58,237,0.20), transparent 55%),' +
                    'radial-gradient(700px circle at 70% 70%, rgba(79,70,229,0.18), transparent 60%),' +
                    'linear-gradient(180deg, #0b0b10 0%, #0f172a 100%)',
            }}
        >
            {/* Component-scoped CSS */}
            <style>{`
        @keyframes floatUp {
          0% {
            transform: translateY(0) rotate(45deg) scale(0.8);
            opacity: 0;
          }
          20% {
            opacity: 1;
          }
          100% {
            transform: translateY(-120vh) rotate(45deg) scale(1.2);
            opacity: 0;
          }
        }
      `}</style>

            {/* Floating hearts */}
            <div
                style={{
                    position: 'absolute',
                    inset: 0,
                    pointerEvents: 'none',
                    overflow: 'hidden',
                }}
            >
                {[...Array(8)].map((_, i) => {
                    const size = 14 + i * 1.5
                    const left = 10 + i * 10
                    const duration = 6 + i * 0.8
                    const delay = i * 1.2

                    const color = `rgba(255, ${100 + i * 10}, 180, 0.8)`

                    return (
                        <div
                            key={i}
                            style={{
                                position: 'absolute',
                                left: `${left}%`,
                                bottom: '-40px',
                                width: size,
                                height: size,
                                background: color,
                                transform: 'rotate(45deg)',
                                animation: `floatUp ${duration}s ease-in-out ${delay}s infinite`,
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

            {/* Login card */}
            <div
                style={{
                    width: 'min(520px, 100%)',
                    padding: 24,
                    borderRadius: 18,
                    background: 'rgba(255,255,255,0.06)',
                    border: '1px solid rgba(255,255,255,0.10)',
                    boxShadow: '0 22px 70px rgba(0,0,0,0.55)',
                    backdropFilter: 'blur(10px)',
                    zIndex: 1,
                }}
            >
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 10,
                    }}
                >
                    <h1
                        style={{
                            fontSize: 'clamp(24px, 4.6vw, 30px)',
                            color: '#fff',
                            margin: 0,
                        }}
                    >
                        Flushing Dating Admin Portal
                    </h1>
                </div>

                <p
                    style={{
                        marginTop: 10,
                        marginBottom: 18,
                        color: 'rgba(255,255,255,0.72)',
                        fontSize: 'clamp(14px, 3.4vw, 16px)',
                    }}
                >
                    Please sign in to continue.
                </p>

                <button
                    onClick={() => navigate('/login/clerk')}
                    style={{
                        width: '100%',
                        height: 48,
                        borderRadius: 12,
                        border: '1px solid rgba(255,255,255,0.12)',
                        background: 'linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%)',
                        color: 'white',
                        fontSize: 16,
                        fontWeight: 800,
                        cursor: 'pointer',
                    }}
                >
                    Log In
                </button>
            </div>
        </div>
    )
}

import TopNav from '../components/TopNav'

export default function Home() {
    return (
        <div
            style={{
                minHeight: '100vh',
                background:
                    'radial-gradient(900px circle at 25% 15%, rgba(124,58,237,0.18), transparent 55%),' +
                    'radial-gradient(800px circle at 80% 75%, rgba(79,70,229,0.14), transparent 60%),' +
                    'linear-gradient(180deg, #0b0b10 0%, #0f172a 100%)',
            }}
        >
            <TopNav />

            <div
                style={{
                    minHeight: 'calc(100vh - 56px)',
                    display: 'grid',
                    placeItems: 'center',
                    padding: 'var(--app-pad)',
                }}
            >
                <div
                    style={{
                        width: 'min(520px, 100%)',
                        padding: 24,
                        borderRadius: 18,
                        background: 'rgba(255,255,255,0.06)',
                        border: '1px solid rgba(255,255,255,0.10)',
                        boxShadow: '0 22px 70px rgba(0,0,0,0.55)',
                        backdropFilter: 'blur(10px)',
                        textAlign: 'center',
                    }}
                >
                    <h1
                        style={{
                            fontSize: 'clamp(22px, 4vw, 28px)',
                            color: '#fff',
                            margin: 0,
                        }}
                    >
                        Hello world for now 👋
                    </h1>

                    <p
                        style={{
                            marginTop: 12,
                            color: 'rgba(255,255,255,0.70)',
                            fontSize: 14,
                        }}
                    >
                        Member approval dashboard coming next.
                    </p>
                </div>
            </div>
        </div>
    )
}

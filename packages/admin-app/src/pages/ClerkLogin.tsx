import { SignIn } from '@clerk/clerk-react'

export default function ClerkLogin() {
    return (
        <div style={{ minHeight: '100vh', display: 'grid', placeItems: 'center', padding: 'var(--app-pad)' }}>
            <SignIn
                routing="hash"
                appearance={{
                    elements: {
                        footerAction: { display: 'none' }, // hides "Don't have an account? Sign up"
                    },
                }}
            />
        </div>
    )
}

type ShimmerProps = {
    width?: string
    height?: number
    radius?: number
}

export default function Shimmer({ width = '100%', height = 22, radius = 6 }: ShimmerProps) {
    return (
        <span
            style={{
                display: 'inline-block',
                width,
                height,
                borderRadius: radius,
                background:
                    'linear-gradient(90deg, rgba(255,255,255,0.08) 25%, rgba(255,255,255,0.18) 37%, rgba(255,255,255,0.08) 63%)',
                backgroundSize: '400% 100%',
                animation: 'shimmer 1.4s ease infinite',
                verticalAlign: 'middle',
            }}
        >
            <style>{`
                @keyframes shimmer {
                    0% { background-position: 100% 0; }
                    100% { background-position: -100% 0; }
                }
            `}</style>
        </span>
    )
}

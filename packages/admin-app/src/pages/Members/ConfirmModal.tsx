import { useEffect } from 'react'
import './confirm-modal.css'

type ConfirmModalProps = {
    open: boolean
    title?: string
    description?: string
    confirmText: string
    cancelText: string
    danger?: boolean
    onConfirm: () => void
    onCancel: () => void
    disabled?: boolean
}

export default function ConfirmModal({
    open,
    title = 'Confirm',
    description,
    confirmText,
    cancelText,
    danger = false,
    onConfirm,
    onCancel,
    disabled = false,
}: ConfirmModalProps) {
    useEffect(() => {
        if (!open) return

        const onKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onCancel()
        }

        window.addEventListener('keydown', onKeyDown)
        return () => window.removeEventListener('keydown', onKeyDown)
    }, [open, onCancel])

    if (!open) return null

    return (
        <div className="cm-overlay" onMouseDown={onCancel} role="dialog" aria-modal="true">
            <div className="cm-modal" onMouseDown={(e) => e.stopPropagation()}>
                <div className="cm-header">
                    <h3 className="cm-title">{title}</h3>
                </div>

                {description ? <div className="cm-body">{description}</div> : null}

                <div className="cm-footer">
                    <button type="button" className="cm-btn" onClick={onCancel} disabled={disabled}>
                        {cancelText}
                    </button>

                    <button
                        type="button"
                        className={`cm-btn cm-danger ${danger ? 'is-danger' : ''}`}
                        onClick={onConfirm}
                        disabled={disabled}
                    >
                        {confirmText}
                    </button>
                </div>
            </div>
        </div>
    )
}

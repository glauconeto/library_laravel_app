import React, { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/Types';

interface FlashData {
    success?: string;
    error?: string;
}

interface FlashMessageState {
    visible: boolean;
    message: string;
    type: 'success' | 'error';
}

export default function FlashMessage() {
    const { flash } = usePage<PageProps>().props;
    const [state, setState] = useState<FlashMessageState>({
        visible: false,
        message: '',
        type: 'success',
    });

    useEffect(() => {
        if (flash?.success) {
            setState({ visible: true, message: flash.success, type: 'success' });
        } else if (flash?.error) {
            setState({ visible: true, message: flash.error, type: 'error' });
        }

        if (flash?.success || flash?.error) {
            const timer = setTimeout(() => {
                setState((prev) => ({ ...prev, visible: false }));
            }, 5000);

            return () => clearTimeout(timer);
        }
    }, [flash]);

    if (!state.visible) return null;

    const bgColor = state.type === 'success'
        ? 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-100'
        : 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-100';

    const icon = state.type === 'success' ? '✅' : '❌';

    return (
        <div className={`fixed top-4 right-4 z-50 max-w-sm ${bgColor} border px-4 py-3 rounded shadow-lg transition-opacity duration-300`}>
            <div className="flex items-start">
                <span className="mr-2">{icon}</span>
                <div className="flex-1">
                    <p className="text-sm font-medium">{state.message}</p>
                </div>
                <button
                    onClick={() => setState((prev) => ({ ...prev, visible: false }))}
                    className="ml-4 text-lg leading-none hover:opacity-70"
                >
                    x
                </button>
            </div>
        </div>
    );
}
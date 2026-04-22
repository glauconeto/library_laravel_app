import React, { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';

export default function FlashMessage() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(false);
    const [message, setMessage] = useState('');
    const [type, setType] = useState('');

    useEffect(() => {
        if (flash?.success) {
            setMessage(flash.success);
            setType('success');
            setVisible(true);
        } else if (flash?.error) {
            setMessage(flash.error);
            setType('error');
            setVisible(true);
        }

        // Auto-hide after 5 seconds
        if (flash?.success || flash?.error) {
            const timer = setTimeout(() => {
                setVisible(false);
            }, 5000);

            return () => clearTimeout(timer);
        }
    }, [flash]);

    if (!visible) return null;

    const bgColor = type === 'success' 
        ? 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-100'
        : 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-100';

    const icon = type === 'success' ? '✅' : '❌';

    return (
        <div className={`fixed top-4 right-4 z-50 max-w-sm ${bgColor} border px-4 py-3 rounded shadow-lg transition-opacity duration-300`}>
            <div className="flex items-start">
                <span className="mr-2">{icon}</span>
                <div className="flex-1">
                    <p className="text-sm font-medium">{message}</p>
                </div>
                <button
                    onClick={() => setVisible(false)}
                    className="ml-4 text-lg leading-none hover:opacity-70"
                >
                    x
                </button>
            </div>
        </div>
    );
}
